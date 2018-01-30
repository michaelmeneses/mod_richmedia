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
 * Display the synchro editor
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once("lib.php");

$update = required_param('update', PARAM_INT); //cmid

$context = context_module::instance($update);

$url = new moodle_url('/mod/richmedia/xmleditor.php', array('update' => $update));

$PAGE->set_url('/mod/richmedia/xmleditor.php');

if (!$module = get_coursemodule_from_id('richmedia', $update)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", array("id" => $module->course))) {
    print_error('coursemisconf');
}

if (!$richmedia = new richmedia($module->instance)) {
    print_error('invalidid', 'richmedia');
}

$PAGE->set_cm($module);

require_login($course->id, true, $module);

require_capability('moodle/course:manageactivities', $context);

$streditxml = get_string('editxml', 'richmedia');

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/richmedia/lib/adapter/ext/ext-base.js');
$PAGE->requires->css('/mod/richmedia/lib/resources/css/ext-all.css');
$PAGE->requires->js('/mod/richmedia/lib/ext-all.js');
$PAGE->requires->js('/mod/richmedia/xmleditor.js');

$PAGE->set_title($richmedia->get_name());
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($course->shortname, $CFG->wwwroot . "/course/view.php?id=" . $course->id);
$PAGE->navbar->add($richmedia->name, $CFG->wwwroot . "/mod/richmedia/view.php?id=" . $update);
$PAGE->navbar->add($streditxml);

$fs = get_file_storage();

$videourl = $richmedia->get_video_url();
$xmlfile  = $richmedia->get_xml();

$urlslide = "{$CFG->wwwroot}/pluginfile.php/{$context->id}/mod_richmedia/content/slides/";
// Read contents
if ($xmlfile) {
    $contenuxml = $xmlfile->get_content();
    $contenuxml = str_replace('&', '&amp;', $contenuxml);

    $xml = simplexml_load_string($contenuxml);

    foreach ($xml->titles[0]->title[0]->attributes() as $attribute => $value) {
        if ($attribute == 'label') {
            $title = $richmedia->name;
            if (!$title) {
                $title = richmedia_encode_string($value);
            }
            break;
        }
    }
    $presentertitle = '';
    foreach ($xml->presenter[0]->attributes() as $attribute => $value) {
        if ($attribute == 'name') {
            $presentername = richmedia_encode_string($richmedia->presentor);
            if (!$presentername) {
                $presentername = $USER->firstname . ' ' . $USER->lastname;
            }
        }
        else if ($attribute == 'title') {
            $presentertitle = richmedia_encode_string($value);
        }
    }
    $defaultview = $richmedia->defaultview;
    $autoplay    = $richmedia->autoplay;
    $fontcolor   = $richmedia->fontcolor;
    $font        = $richmedia->font;
    $tabstep     = $richmedia->get_steps();
}
else {
    // file doesn't exist - do something
    $contenuxml            = '';
    $title                 = get_string('title', 'richmedia');
    $presentername         = '';
    $presentertitle        = '';
    $fontcolor             = 'FFFFFF';
    $font                  = 'Arial';
    $tabstep               = array();
    $tabstep[0]['id']      = 0;
    $tabstep[0]['label']   = get_string('slidetitle', 'richmedia');
    $tabstep[0]['comment'] = '';
    $tabstep[0]['framein'] = '00:00';
    $tabstep[0]['slide']   = 'Diapositive1.JPG';
    $defaultview           = 1;
    $autoplay              = 1;
}

//AVAILABLE FILES
$available = $richmedia->get_available_pictures();

$urlsubmit   = $CFG->wwwroot . '/mod/richmedia/ajax/xmleditor_save.php';
$urlLocation = $CFG->wwwroot . '/course/modedit.php?update=' . $update;
$urlView     = $CFG->wwwroot . '/mod/richmedia/view.php?id=' . $update;
$defaultview = (string) $defaultview;

$PAGE->requires->js_init_call(
        'M.mod_richmedia_xmleditor.init', array(
    $available,
    $tabstep,
    $videourl,
    $richmedia->referencesvideo,
    $title,
    $presentername,
    $presentertitle,
    $context->id,
    $update,
    $fontcolor,
    $font,
    $urlslide,
    $defaultview,
    $autoplay,
    $urlsubmit,
    $urlLocation,
    $urlView
        )
);

$PAGE->requires->strings_for_js(array(
    'down',
    'wait',
    'currentsave',
    'saveandreturn',
    'view',
    'savedone',
    'information',
    'test',
    'filenotavailable',
    'up',
    'delete',
    'addline',
    'cancel',
    'slidetitle',
    'save',
    'title',
    'video',
    'slidecomment',
    'presentation',
    'tile',
    'actions',
    'newline',
    'confirmdeleteline',
    'warning',
    'gettime',
    'slide',
    'delete',
    'up',
    'defaultview',
    'samesteps'
        ), 'mod_richmedia');

echo $OUTPUT->header();
echo $OUTPUT->heading($streditxml);

echo html_writer::tag('div', '', array('id' => 'tab', 'style' => 'margin:auto;'));
echo $OUTPUT->footer();
