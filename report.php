<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Display view report
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/mod/richmedia/lib.php');
require_once($CFG->libdir . '/formslib.php');
define('RICHMEDIA_REPORT_DEFAULT_PAGE_SIZE', 10);

$id       = optional_param('id', '', PARAM_INT);    // Course Module ID, or
$user     = optional_param('user', '', PARAM_INT);  // User ID
$action   = optional_param('action', '', PARAM_ALPHA);
$download = optional_param('download', '', PARAM_RAW);

if (!empty($id)) {
    if (!$cm = get_coursemodule_from_id('richmedia', $id)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (!$richmedia = new richmedia($cm->instance)) {
        print_error('invalidcoursemodule');
    }
}

$url = new moodle_url('/mod/richmedia/report.php', array('id' => $id));
$PAGE->set_url($url);

require_login($course->id, false, $cm);

$contextmodule = context_module::instance($cm->id);

require_capability('mod/richmedia:viewreport', $contextmodule);

$richmedia->trigger_report_view();

if ($action == "delete") {

    $joined      = optional_param('joined', '', PARAM_RAW);
    $richmediaid = optional_param('richmediaid', null, PARAM_INT);
    $users       = explode(',', $joined);
    foreach ($users as $userid) {
        $richmedia->delete_user_tracks($userid);
    }
    exit;
}
/// Print the page header
if (empty($download)) {

    $PAGE->set_title("$course->shortname: " . $richmedia->get_name());
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add(get_string('report', 'richmedia'), new moodle_url('/mod/richmedia/report.php', array('id' => $cm->id)));
    $PAGE->requires->jquery();
    $PAGE->requires->js_init_call('M.mod_richmedia.initReport', array($cm->id, $richmedia->id));
    $PAGE->requires->string_for_js('noselectedline', 'mod_richmedia');

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string("report") . " : " . $richmedia->get_name());
}

if ($download == 'excel') {
    $richmedia->download_reports();
}

if (empty($download)) {

    $table = new flexible_table('mod-richmedia-report');

    $columns                     = array("checkbox", "picture", "fullname", "attempt", "start", "last");
    $headers                     = array(" ", " ", get_string('name'), get_string('attempts', 'richmedia'), get_string('started', 'richmedia'), get_string('last', 'richmedia'));
    $displayoptions              = array();
    $displayoptions['id']        = $cm->id;
    $reporturlwithdisplayoptions = new moodle_url($CFG->wwwroot . '/mod/richmedia/report.php', $displayoptions);

    $table->define_columns($columns);
    $table->define_headers($headers);
    $table->define_baseurl($reporturlwithdisplayoptions->out());

    $table->sortable(true);
    $table->collapsible(true);

    $table->column_suppress('picture');
    $table->column_suppress('fullname');

    $table->no_sorting('checkbox');
    $table->no_sorting('picture');

    $table->column_class('picture', 'picture');
    $table->column_class('fullname', 'bold');

    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('class', 'generaltable generalbox');

    $table->setup();
    
    $alltracks = $richmedia->get_tracks();
    $total    = count($alltracks);
    $table->pagesize(RICHMEDIA_REPORT_DEFAULT_PAGE_SIZE, $total);


    if (!$download) {
        $sort = $table->get_sql_sort();
    }
    else {
        $sort = '';
    }
    if (empty($sort)) {
        $sort = ' ORDER BY id';
    }
    else {
        $sort = ' ORDER BY ' . $sort;
    }

    // Start working -- this is necessary as soon as the niceties are over
    $sql = 'SELECT u.id,userid,picture,imagealt,email,firstname,lastname,attempt,start,last FROM {richmedia_track} as r,{user} as u WHERE r.userid=u.id AND richmediaid = ' . $richmedia->id . $sort;

    $params = array();
    list($twhere, $tparams) = $table->get_sql_where();
    if ($twhere) {
        $params = array_merge($params, $tparams);
    }

    $tracks = $DB->get_records_sql($sql, $params, $table->get_page_start(), $table->get_page_size());
    $total  = count($tracks);
    if ($total <= 1) {
        $user = get_string('user', 'richmedia');
    }
    else {
        $user = get_string('users', 'richmedia');
    }
    echo '<div style="text-align : center;">' . $total . ' ' . $user . '</div>';
    foreach ($tracks as $track) {
        $row   = array();
        $row[] = '<input type="checkbox" id="' . $track->userid . '" />';
        $user  = $DB->get_record('user', array('id' => $track->userid));
        $row[] = $OUTPUT->user_picture($user, array('courseid' => $course->id));
        $row[] = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $track->userid . '&course=' . $course->id . '">' . $track->firstname . ' ' . $track->lastname . '</a>';
        $row[] = $track->attempt;
        $row[] = userdate($track->start, get_string("strftimedatetime", "langconfig"));
        $row[] = userdate($track->last, get_string("strftimedatetime", "langconfig"));
        $table->add_data($row);
    }
    echo '<a href="' . $CFG->wwwroot . '/mod/richmedia/report.php?id=' . $id . '&download=excel">' . get_string('downloadexcel', 'richmedia') . '</a><br /><br />';


    $table->finish_output();
    echo '<input type="button" id="checkall" value="' . get_string('selectall', 'richmedia') . '" />';
    echo '/';
    echo '<input type="button" id="uncheckall" value="' . get_string('deselectall', 'richmedia') . '" />';
    echo '<input type="button" id="deleterows" value="' . get_string('deleteselection', 'richmedia') . '" />';

    echo $OUTPUT->footer();
}
    

