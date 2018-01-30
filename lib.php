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
 * Moodle mandatory functions
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/filestorage/zip_archive.php");
require_once($CFG->dirroot . '/mod/richmedia/locallib.php');
require_once($CFG->dirroot . '/mod/richmedia/classes/richmedia.php');

define('RICHMEDIA_TYPE_LOCAL', 'local');


/**
 * Add a richmedia instance
 * @global type $CFG
 * @global type $DB
 * @param type $data
 * @param type $mform
 * @return type
 */
function richmedia_add_instance($data, $mform = null) {
    global $DB;

    if (is_int($data->referenceslides)) {
        $data->referenceslides = '';
    }
    if (is_int($data->referencesxml)) {
        $data->referencesxml = '';
    }
    if (is_int($data->referencesvideo)) {
        $data->referencesvideo = '';
    }
    if (is_int($data->referencesfond)) {
        $data->referencesfond = '';
    }
    if (is_int($data->referencessubtitles)) {
        $data->referencessubtitles = '';
    }
        
    $id = $DB->insert_record('richmedia', $data);
    
    $DB->set_field('course_modules', 'instance', $id, array('id' => $data->coursemodule));
    
    $richmedia = new richmedia($id);

    if ($mform) {
        $richmedia->set_referenceslides($mform, false);
        
        $richmedia->set_referencesxml($mform, false);

        $richmedia->set_referencesvideo($mform, false);

        $richmedia->set_referencesfond($mform, false);

        $richmedia->set_referencessubtitles($mform, false);
    }
    
    $richmedia->save();

    $richmedia->generate_xml();

    return $richmedia->id;
}

/**
 * Update an instance
 * @global type $DB
 * @param type $data
 * @param type $mform
 * @return boolean
 */
function richmedia_update_instance($data, $mform = null) {
    global $DB;
    $data->id = $data->instance;
    $DB->update_record('richmedia', $data);
    
    $richmedia = new richmedia($data->instance);

    if (is_numeric($data->referenceslides)) {
        $richmedia->referenceslides = '';
    }
    if (is_numeric($data->referencesxml)) {
        $richmedia->referencesxml = '';
    }
    if (is_numeric($data->referencesvideo)) {
        $richmedia->referencesvideo = '';
    }
    if (is_numeric($data->referencesfond)) {
        $richmedia->referencesfond = '';
    }
    if (is_numeric($data->referencessubtitles)) {
        $richmedia->referencessubtitles = '';
    }
    
    if ($mform) {
        $richmedia->set_referenceslides($mform, false);
        
        $richmedia->set_referencesxml($mform, false);

        $richmedia->set_referencesvideo($mform, false);

        $richmedia->set_referencesfond($mform, false);

        $richmedia->set_referencessubtitles($mform, false);
    }
    
    $richmedia->save();

    $richmedia->generate_xml();

    return true;
}

/**
 * Delete an instance
 * @param int $id
 * @return boolean
 */
function richmedia_delete_instance($id) {
    if (!$richmedia = new richmedia($id)) {
        return false;
    }
    return $richmedia->delete();
}

/**
 * Get file infos
 * @global type $CFG
 * @param type $browser
 * @param type $areas
 * @param type $course
 * @param type $cm
 * @param type $context
 * @param type $filearea
 * @param type $itemid
 * @param type $filepath
 * @param type $filename
 * @return boolean|\richmedia_package_file_info
 */
function richmedia_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'video') {

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase    = $CFG->wwwroot . '/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_richmedia', $filearea, 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_richmedia', $filearea, 0);
            }
            else {
                // not found
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/richmedia/locallib.php");
        return new richmedia_package_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, false, false);
    }
    return false;
}

function richmedia_user_outline() {
    //not implemented yet
}

function richmedia_get_view_actions() {
    return array('view', 'view all');
}

function richmedia_get_post_actions() {
    //not implemented yet
}

/**
 * Get a richmedia file
 * @global type $CFG
 * @param type $course
 * @param type $cm
 * @param type $context
 * @param type $filearea
 * @param type $args
 * @param type $forcedownload
 * @return boolean
 */
function richmedia_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG;
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }
    require_login($course, true, $cm);
    $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;
    if ($filearea === 'content') {
        $relativepath = implode('/', $args);
        $fullpath     = "/$context->id/mod_richmedia/content/0/$relativepath";
    }
    else if ($filearea === 'video') {
        $relativepath = implode('/', $args);
        $fullpath     = "/$context->id/mod_richmedia/content/video/0/$relativepath";
    }
    else if ($filearea === 'subtitles') {
        $relativepath = implode('/', $args);
        $fullpath     = "/$context->id/mod_richmedia/subtitles/0/$relativepath";
    }
    else if ($filearea === 'picture') {
        $relativepath = implode('/', $args);
        $fullpath     = "/$context->id/mod_richmedia/picture/0/$relativepath";
    }
    else if ($filearea === 'package') {
        if (!has_capability('moodle/course:manageactivities', $context)) {
            return false;
        }
        $relativepath = implode('/', $args);
        $fullpath     = "/$context->id/mod_richmedia/package/0/$relativepath";
        $lifetime     = 0;
    }
    else if ($filearea === 'zip') {
        $relativepath = implode('/', $args);
        $fullpath     = "/$context->id/mod_richmedia/zip/0/$relativepath";
    }
    else {
        return false;
    }
    $fs   = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // finally send the file
    send_stored_file($file, $lifetime, 0, false);
}

/**
 * Define available functionnalites
 * @param type $feature
 * @return boolean
 */
function richmedia_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS: return false;
        case FEATURE_GROUPINGS: return false;
        case FEATURE_GROUPMEMBERSONLY: return false;
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE: return false;
        case FEATURE_GRADE_OUTCOMES: return false;
        case FEATURE_BACKUP_MOODLE2: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;

        default: return null;
    }
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $richmedianode The node to add module settings to
 */
function richmedia_extend_settings_navigation(settings_navigation $settings, navigation_node $richmedianode) {
    global $PAGE;
    $context = context_module::instance($PAGE->cm->id);
    if (has_capability('mod/richmedia:addinstance', $context)) {
        $richmedianode->add(get_string('importexport', 'mod_richmedia'), new moodle_url('/mod/richmedia/importexport.php', array('id' => $PAGE->cm->id)));
        $richmedianode->add(get_string('createedit', 'mod_richmedia'), new moodle_url('/mod/richmedia/xmleditor.php', array('update' => $PAGE->cm->id)));
    }
}

function richmedia_user_complete($course, $user, $mod, $richmedia) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid' => $user->id, 'module' => 'richmedia',
        'action' => 'view', 'info'   => $richmedia->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog  = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews     = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently " . userdate($lastlog->time);
    }
    else {
        print_string('neverseen', 'richmedia');
    }
}

class richmedia_package_file_info extends file_info_stored {

    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }

    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }

}
