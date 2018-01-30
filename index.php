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
 * Display all ricjmedia instances
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot . '/mod/richmedia/locallib.php');

$id = required_param('id', PARAM_INT);   // course id

$PAGE->set_url('/mod/richmedia/index.php', array('id' => $id));

if (!empty($id)) {
    if (!$course = $DB->get_record('course', array('id' => $id))) {
        print_error('invalidcourseid');
    }
} else {
    print_error('missingparameter');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

$event = \mod_richmedia\event\course_module_instance_list_viewed::create(array('context' => context_course::instance($course->id)));
$event->add_record_snapshot('course', $course);
$event->trigger();

$strrichmedias = get_string("modulenameplural", "richmedia");
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strname = get_string("name");
$strsummary = get_string("summary");
$strreport = get_string("report", 'richmedia');
$strlastmodified = get_string("lastmodified");

$PAGE->set_title($strrichmedias);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strrichmedias);
echo $OUTPUT->header();

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

if ($usesections) {
    $sortorder = "cw.section ASC";
} else {
    $sortorder = "m.timemodified DESC";
}

if (!$richmedias = get_all_instances_in_course("richmedia", $course)) {
    notice(get_string('thereareno', 'moodle', $strrichmedias), "../../course/view.php?id=$course->id");
    exit;
}

$table = new html_table();

if ($usesections) {
    $table->head = array($strsectionname, $strname, $strsummary, $strreport);
    $table->align = array("center", "left", "left", "left");
} else {
    $table->head = array($strlastmodified, $strname, $strsummary, $strreport);
    $table->align = array("left", "left", "left", "left");
}

foreach ($richmedias as $richmedia) {
    $tt = "";
    if ($usesections) {
        if ($richmedia->section) {
            $tt = get_section_name($course, $sections[$richmedia->section]);
        }
    } else {
        $tt = userdate($richmedia->timemodified);
    }
    $reportshow = '&nbsp;';

    if (!$richmedia->visible) {
        //Show dimmed if the mod is hidden
        $table->data[] = array($tt, "<a class=\"dimmed\" href=\"view.php?id=$richmedia->coursemodule\">" . format_string($richmedia->name) . "</a>",
            format_module_intro('richmedia', $richmedia, $richmedia->coursemodule), $reportshow);
    } else {
        //Show normal if the mod is visible
        $table->data[] = array($tt, "<a href=\"view.php?id=$richmedia->coursemodule\">" . format_string($richmedia->name) . "</a>",
            format_module_intro('richmedia', $richmedia, $richmedia->coursemodule), $reportshow);
    }
}

echo "<br />";

echo html_writer::table($table);

echo $OUTPUT->footer();
