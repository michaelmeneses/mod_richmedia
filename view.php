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
 * Display the richmedia
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once($CFG->dirroot . '/mod/richmedia/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID

if (!$cm = get_coursemodule_from_id('richmedia', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}
if (!$richmedia = new richmedia($cm->instance)) {
    print_error('invalidcoursemodule');
}

$url = new moodle_url('/mod/richmedia/view.php', array('id' => $cm->id));

$PAGE->set_url($url);
$PAGE->requires->jquery();
$PAGE->requires->js_init_call('M.mod_richmedia.init', array($richmedia->id));
require_login($course->id, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/richmedia:view', $context);

$pagetitle = strip_tags($course->shortname . ': ' . $richmedia->get_name());

//Log
$richmedia->trigger_module_view();

$richmediainfos = $richmedia->get_infos();

$audioMode = $richmedia->is_audio() ? 1 : 0;

$PAGE->requires->css('/mod/richmedia/playerhtml5/css/playerhtml5.css');
if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $richmedia->theme . '/styles.css')) {
    $PAGE->requires->css('/mod/richmedia/themes/' . $richmedia->theme . '/styles.css');
}
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js('/mod/richmedia/playerhtml5/js/jquery.punch.js');
$PAGE->requires->js('/mod/richmedia/playerhtml5/js/cuepoint.js');
$PAGE->requires->js('/mod/richmedia/playerhtml5/js/jquery.srt.js');
$PAGE->requires->js('/mod/richmedia/playerhtml5/js/player.js');
$PAGE->requires->strings_for_js(array(
    'summary',
    'close',
    'prev',
    'next',
    'srt',
    'display',
    'tile',
    'slide',
    'video'), 'mod_richmedia');

$PAGE->requires->js_init_call('M.mod_richmedia.initPlayerHTML5', array($richmediainfos, $audioMode, $cm->id));

$richmedia->add_track($USER);

$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

$renderer = $PAGE->get_renderer('mod_richmedia');

echo $OUTPUT->header();

if (has_capability('mod/richmedia:viewreport', $context)) {
    echo '<div class="viewreports">';
    echo '<a href="report.php?id=' . $id . '">' . get_string('showresults', 'richmedia') . '</a>';
    echo '</div>';
}

// Print the main part of the page
echo $OUTPUT->heading($richmedia->get_name());

if (has_capability('moodle/course:manageactivities', $context) && empty($richmedia->get_steps())) {
    echo '<p>Aucune synchronisation trouvée. <a href="'.$CFG->wwwroot .'/mod/richmedia/xmleditor.php?update='.$cm->id.'">Créer une synchronisation</a></p>';
}

echo $renderer->intro($richmedia);

require_once($CFG->dirroot . '/mod/richmedia/playerhtml5/playerhtml5_template.php');

echo $OUTPUT->footer();


