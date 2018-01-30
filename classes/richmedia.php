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
 * Class richmedia
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class richmedia {

    public $id;
    public $name;
    public $referencesslides;
    public $referencesvideo;
    public $referencesfond;
    public $referencesxml;
    public $referencessynchro;
    public $sha1hash;
    public $intro;
    public $introformat;
    public $width;
    public $height;
    public $theme;
    public $html5;
    public $font;
    public $fontcolor;
    public $defaultview;
    public $autoplay;
    public $presentor;
    public $keywords;
    public $quizid;
    public $recovery;
    public $videourl;
    public $referencessubtitles;
    public $context;
    public $cm;
    public $db_record;
    public $course;

    /**
     * 
     * @global type $DB
     * @param int $id from table richmedia
     */
    public function __construct($id) {
        global $DB;

        $this->id = $id;

        if (!$richmedia = $DB->get_record("richmedia", array("id" => $id))) {
            print_error('invalidcoursemodule');
        }

        $this->db_record = $richmedia;

        foreach ($richmedia as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * 
     * @return bool
     */
    public function is_audio() {
        $extensionExplode = explode('.', $this->referencesvideo);
        $extension        = end($extensionExplode);
        return $extension == 'mp3';
    }

    /**
     * 
     * @return stdClass course module
     */
    public function get_cm() {
        if (empty($this->cm)) {
            $this->cm = get_coursemodule_from_instance('richmedia', $this->id);
        }
        return $this->cm;
    }

    /**
     * 
     * @return context_module
     */
    public function get_context() {
        if (empty($this->context)) {
            $this->context = context_module::instance($this->get_cm()->id);
        }
        return $this->context;
    }

    /**
     * 
     * @global type $CFG
     * @return string
     */
    public function get_video_url() {
        global $CFG;
        if (isset($this->videourl) && !empty($this->videourl)) {
            return $this->videourl;
        }
        else {
            $fs                       = get_file_storage();
            $fileinfovideo            = new stdClass();
            $fileinfovideo->component = 'mod_richmedia';
            $fileinfovideo->filearea  = 'content';
            $fileinfovideo->contextid = $this->get_context()->id;
            $fileinfovideo->filepath  = '/video/';
            $fileinfovideo->itemid    = 0;
            $fileinfovideo->filename  = $this->referencesvideo;
            // Get file
            if ($fs->get_file($fileinfovideo->contextid, $fileinfovideo->component, $fileinfovideo->filearea, $fileinfovideo->itemid, $fileinfovideo->filepath, $fileinfovideo->filename)) {
                return "{$CFG->wwwroot}/pluginfile.php/{$this->get_context()->id}/mod_richmedia/content/video/" . $this->referencesvideo;
            }
        }

        return false;
    }

    /**
     * Get sync file
     * @return stored_file
     */
    public function get_xml() {
        $fs                  = get_file_storage();
        $fileinfo            = new stdClass();
        $fileinfo->component = 'mod_richmedia';
        $fileinfo->filearea  = 'content';
        $fileinfo->contextid = $this->get_context()->id;
        $fileinfo->filepath  = '/';
        $fileinfo->itemid    = 0;
        $fileinfo->filename  = $this->referencesxml;
        return $fs->get_file($fileinfo->contextid, $fileinfo->component, $fileinfo->filearea, $fileinfo->itemid, $fileinfo->filepath, $fileinfo->filename);
    }

    /**
     * Get richmedia data
     * @global type $CFG
     * @return \stdClass
     */
    public function get_infos() {
        global $CFG;
        $context   = $this->get_context();
        $repslides = "{$CFG->wwwroot}/pluginfile.php/{$context->id}/mod_richmedia/content/slides/";

        $file = $this->get_xml();

        $this_infos             = new stdClass();
        $this_infos->haspicture = 0;

        if ($file) {
            $contenuxml = $file->get_content();
            $contenuxml = str_replace('&', '&amp;', $contenuxml);

            $xml = simplexml_load_string($contenuxml);

            foreach ($xml->titles[0]->title[0]->attributes() as $attribute => $value) {
                if ($attribute == 'label') {
                    $value             = str_replace("&rsquo;", iconv("CP1252", "UTF-8", "’"), $value);
                    $value             = str_replace("â€™", "’", $value);
                    $value             = str_replace("’", "'", $value);
                    $value             = richmedia_convert_to_html($value);
                    $this_infos->title = $value;
                    break;
                }
            }
            foreach ($xml->presenter[0]->attributes() as $attribute => $value) {
                $value = str_replace("&rsquo;", iconv("CP1252", "UTF-8", "’"), $value);
                $value = str_replace("â€™", "’", $value);
                $value = str_replace("’", "'", $value);
                if ($attribute == 'biography') {
                    $presenterbio = richmedia_convert_to_html($value);
                }
            }
            $tabstep   = array();
            $i         = 0;
            $tabslides = array();
            foreach ($xml->steps[0]->children() as $childname => $childnode) {
                foreach ($childnode->attributes() as $attribute => $value) {
                    $tabstep[$i][$attribute] = (String) $value;
                }
                $time = $tabstep[$i]['framein'];

                $tabslides[$i]['framein'] = $time;
                $tabslides[$i]['slide']   = $tabstep[$i]['label'];

                if (!array_key_exists('view', $tabstep[$i]) || $tabstep[$i]['view'] == '') {
                    $tabstep[$i]['view'] = $this->defaultview;
                }
                $tabslides[$i]['view'] = $tabstep[$i]['view'];

                if ($f = $this->get_slide($tabstep[$i]['slide'])) {
                    $tabslides[$i]['src']  = $repslides . $tabstep[$i]['slide'];
                    $tabslides[$i]['html'] = '<img src="' . $repslides . $tabstep[$i]['slide'] . '" width="100%" view="' . $tabstep[$i]['view'] . '"/><br/><span class="slide-title">' . $tabstep[$i]['label'] . '</span>&nbsp;';

                    $this_infos->haspicture = 1;
                }
                else {
                    $tabslides[$i]['src'] = '';
                }
                $i++;
            }
        }

        $this_infos->time = $this->get_last_time();

        $this_infos->tabslides = $tabslides;

        $this_infos->recovery      = $this->recovery;
        $this_infos->richmediaid   = $this->id;
        $this_infos->defaultview   = $this->defaultview;
        $this_infos->autoplay      = $this->autoplay;
        $this_infos->background    = $this->get_background();
        $this_infos->logo          = $this->get_logo();
        $this_infos->theme         = $this->theme;
        $this_infos->fontcolor     = $this->fontcolor;
        $this_infos->font          = $this->font;
        $this_infos->filevideo     = $this->get_video_url();
        $this_infos->presentername = $this->presentor;
        $this_infos->presenterbio  = $presenterbio;

        if ($subtitles = $this->get_subtitles_url()) {
            $this_infos->subtitles = $subtitles;
        }

        return $this_infos;
    }

    /**
     * Get a slide file
     * @param type $slidename
     * @return stored_file | bool
     */
    public function get_slide($slidename) {
        $fs = get_file_storage();
        return $fs->get_file($this->get_context()->id, 'mod_richmedia', 'content', 0, '/slides/', $slidename);
    }

    /**
     * 
     * @global type $CFG
     * @return string | false
     */
    public function get_subtitles_url() {
        global $CFG;
        if (isset($this->referencessubtitles) && !empty($this->referencessubtitles) && !is_numeric($this->referencessubtitles)) {
            return "{$CFG->wwwroot}/pluginfile.php/{$this->get_context()->id}/mod_richmedia/subtitles/" . $this->referencessubtitles;
        }
        return false;
    }

    /**
     * 
     * @global type $CFG
     * @return string | false
     */
    public function get_logo() {
        global $CFG;

        if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/logo.jpg')) {
            return $this->theme . '/logo.jpg';
        }
        else if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/logo.png')) {
            return $this->theme . '/logo.png';
        }
        return false;
    }

    /**
     * 
     * @global type $CFG
     * @return string | false
     */
    public function get_background() {
        global $CFG;
        if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/background.jpg')) {
            return $this->theme . '/background.jpg';
        }
        else if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/background.png')) {
            return $this->theme . '/background.png';
        }
        return false;
    }

    /**
     * 
     * @global type $DB
     * @global type $USER
     * @param int $userid - optional default current userid
     * @return int
     */
    public function get_last_time($userid = null) {
        global $DB;

        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }

        if ($lastTime = $DB->get_record('richmedia_track', array('userid' => $userid, 'richmediaid' => $this->id))) {
            return $lastTime->time;
        }

        return 0;
    }

    /**
     * Get steps
     * @global type $CFG
     * @return array
     */
    public function get_steps() {
        global $CFG;

        if ($file = $this->get_xml()) {

            $fs         = get_file_storage();
            $xmlcontent = $file->get_content();
            $xmlcontent = str_replace('&', '&amp;', $xmlcontent);

            $xml = simplexml_load_string($xmlcontent);

            $tabstep = array();

            $i = 0;

            $context = $this->get_context();

            $urlslide = "{$CFG->wwwroot}/pluginfile.php/{$context->id}/mod_richmedia/content/slides/";

            foreach ($xml->steps[0]->children() as $childname => $childnode) {
                foreach ($childnode->attributes() as $attribute => $value) {
                    if ($attribute == 'framein') {
                        $tabstep[$i][$attribute] = richmedia_convert_time($value); // convert time
                    }
                    else if ($attribute == 'slide') {
                        // Prepare video record object
                        $filename  = (String) $value;
                        // Get file
                        $fileslide = $this->get_slide($filename);

                        if ($fileslide) {
                            $fileslidename      = $fileslide->get_filename();
                            $tabstep[$i]['url'] = $urlslide . $fileslidename;
                        }
                        else {
                            $tabstep[$i]['url'] = '';
                        }
                        $tabstep[$i][$attribute] = (String) $value;
                    }
                    else {
                        $tabstep[$i][$attribute] = (String) $value;
                    }
                }
                $i++;
            }

            return $tabstep;
        }
    }

    /**
     * Get formatted name
     * @return string
     */
    public function get_name() {
        return format_string($this->name);
    }

    /**
     * Add track for a given user
     * @global type $DB
     * @param stdClass $user
     * @return int richmedia_track id
     */
    public function add_track($user) {
        global $DB;
        if ($track = $DB->get_record('richmedia_track', array('userid' => $user->id, 'richmediaid' => $this->id))) {
            $track->attempt = $track->attempt + 1;
            $track->last    = time();
            $DB->update_record('richmedia_track', $track);
            $id             = $track->id;
        }
        else {
            $track              = new stdClass();
            $track->userid      = $user->id;
            $track->richmediaid = $this->id;
            $track->attempt     = 1;
            $track->start       = time();
            $id                 = $DB->insert_record('richmedia_track', $track);
        }
        return $id;
    }

    /**
     * Delete a user track
     * @global type $DB
     * @param int $userid
     * @return bool 
     */
    public function delete_user_tracks($userid) {
        global $DB;
        return $DB->delete_records('richmedia_track', array('userid' => $userid, 'richmediaid' => $this->id));
    }

    /**
     * Download report as xls
     * @global type $CFG
     */
    public function download_reports() {
        global $CFG;
        require_once("$CFG->libdir/excellib.class.php");
        $filename   = 'richmedia_track_' . time();
        $filename .= ".xls";
        // Creating a workbook
        $workbook   = new MoodleExcelWorkbook("-");
        // Sending HTTP headers
        $workbook->send($filename);
        // Creating the first worksheet
        $sheettitle = get_string('report', 'richmedia');
        $myxls      = $workbook->add_worksheet($sheettitle);
        // format types
        $format     = $workbook->add_format();
        $format->set_bold(0);
        $formatbc   = $workbook->add_format();
        $formatbc->set_bold(1);
        $formatbc->set_align('center');
        $formatb    = $workbook->add_format();
        $formatb->set_bold(1);
        $formaty    = $workbook->add_format();
        $formaty->set_bg_color('yellow');
        $formatc    = $workbook->add_format();
        $formatc->set_align('center');
        $formatr    = $workbook->add_format();
        $formatr->set_bold(1);
        $formatr->set_color('red');
        $formatr->set_align('center');
        $formatg    = $workbook->add_format();
        $formatg->set_bold(1);
        $formatg->set_color('green');
        $formatg->set_align('center');

        $headers   = array();
        $headers[] = get_string('name');
        $headers[] = get_string('attempts', 'richmedia');
        $headers[] = get_string('started', 'richmedia');
        $headers[] = get_string('last', 'richmedia');
        $colnum    = 0;
        foreach ($headers as $item) {
            $myxls->write(0, $colnum, $item, $formatbc);
            $colnum++;
        }

        $rownum = 1;
        $tracks = $this->get_tracks();

        foreach ($tracks as $track) {
            $colnum     = 0;
            $rowname    = $track->firstname . ' ' . $track->lastname;
            $rowattempt = $track->attempt;
            $rowstart   = userdate($track->start, get_string("strftimedatetime", "langconfig"));
            $rowlast    = userdate($track->last, get_string("strftimedatetime", "langconfig"));

            $myxls->write($rownum, $colnum, $rowname, $format);
            $colnum++;
            $myxls->write($rownum, $colnum, $rowattempt, $format);
            $colnum++;
            $myxls->write($rownum, $colnum, $rowstart, $format);
            $colnum++;
            $myxls->write($rownum, $colnum, $rowlast, $format);
            $colnum++;
            $rownum++;
        }
        $workbook->close();
        exit;
    }

    /**
     * 
     * @global type $DB
     * @return array user tracks
     */
    public function get_tracks() {
        global $DB;
        return $DB->get_records_sql('SELECT rt.*, u.firstname, u.lastname FROM {richmedia_track} rt, {user} u WHERE richmediaid = ? AND rt.userid = u.id', array($this->id));
    }

    /**
     * Get available pictures already uploaded
     * @return array
     */
    public function get_available_pictures() {
        $context              = $this->get_context();
        $fs                   = get_file_storage();
        $files                = $fs->get_area_files($context->id, 'mod_richmedia', 'content', 0, "itemid, filepath, filename", false);
        $available            = array();
        $notAllowedExtensions = array(
            'xml', 'mp4', 'ogg', 'ogv', 'flv', 'mp3', 'db', 'zip'
        );
        foreach ($files as $f) {
            $filename               = $f->get_filename();
            if ($f->get_filepath() == '/slides/') {
                $arrayfilenameextension = explode('.', $filename);
                $extension              = end($arrayfilenameextension);
                if (!in_array($extension, $notAllowedExtensions)) {
                    $available[] = $filename;
                }
            }
        }
        return $available;
    }

    /**
     * Delete current instance
     * @global type $DB
     * @return boolean
     */
    public function delete() {
        global $DB;
        $context = $this->get_context();

        $fs = get_file_storage();

        $fs->delete_area_files($context->id);

        $DB->delete_records('richmedia_track', array('richmediaid' => $this->id));
        $DB->delete_records('richmedia', array('id' => $this->id));
        return true;
    }

    /**
     * Get db record from richmedia table
     * @global type $DB
     * @return stdClass
     */
    public function get_db_record() {
        if (empty($this->db_record)) {
            global $DB;
            $this->db_record = $DB->get_record('richmedia', array('id' => $this->id));
        }
        return $this->db_record;
    }

    /**
     * Trigger report_view event
     */
    public function trigger_report_view() {
        // Trigger a report viewed event.
        $event = \mod_richmedia\event\report_viewed::create(array(
                    'context' => $this->get_context(),
                    'other'   => array(
                        'richmediaid' => $this->id
                    )
        ));
        $event->add_record_snapshot('course_modules', $this->get_cm());
        $event->add_record_snapshot('richmedia', $this->get_db_record());
        $event->trigger();
    }

    /**
     * Get instance course
     * @return stdClass
     */
    public function get_course() {
        return get_course($this->course);
    }

    /**
     * Trigger course module viewed
     * @global type $CFG
     */
    public function trigger_module_view() {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $course = $this->get_course();
        $cm     = $this->get_cm();

        //log
        $params = array(
            'context'  => $this->get_context(),
            'objectid' => $this->id
        );
        $event  = \mod_richmedia\event\course_module_viewed::create($params);
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('richmedia', $this->get_db_record());
        $event->trigger();

        //completion
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);
    }
    
    public function save() {
        global $DB;
        return $DB->update_record('richmedia', $this);
    }

    public function set_referencesxml($mform, $update = true) {
        $fs = get_file_storage();

        $filenamexml = $mform->get_new_filename('referencesxml');

        if ($filenamexml !== false) {
            $contextid           = $this->get_context()->id;
            $this->referencesxml = $filenamexml;
            $fs->delete_area_files($contextid, 'mod_richmedia', 'content');
            $mform->save_stored_file('referencesxml', $contextid, 'mod_richmedia', 'content', 0, '/', $filenamexml);

            if ($update) {
                $this->save();
            }
        }
    }
    
    public function set_referencesvideo($mform, $update = true) {
        $fs = get_file_storage();
        
        $filenamevideo = $mform->get_new_filename('referencesvideo');
        if ($filenamevideo !== false) {
            
            $contextid = $this->get_context()->id;
            
            $this->referencesvideo = $filenamevideo;
            $fs->delete_area_files($contextid, 'mod_richmedia', 'video');
            $mform->save_stored_file('referencesvideo', $contextid, 'mod_richmedia', 'content', 0, '/video/', $filenamevideo);
            
            if ($update) {
                $this->save();
            }
        }
    }
    
    public function set_referencesfond($mform, $update = true) {
        $fs = get_file_storage();
        
        $filenamepicture = $mform->get_new_filename('referencesfond');
        if ($filenamepicture !== false) {
            $contextid = $this->get_context()->id;
            
            $this->referencesfond = $filenamepicture;
            $fs->delete_area_files($contextid, 'mod_richmedia', 'picture');
            $mform->save_stored_file('referencesfond', $contextid, 'mod_richmedia', 'picture', 0, '/', $filenamepicture);
            
            if ($update) {
                $this->save();
            }
        }        
    }

    public function set_referenceslides($mform, $update = true) {
        $fs = get_file_storage();

        $filenameslides = $mform->get_new_filename('referenceslides');

        if ($filenameslides !== false) {
            $contextid = $this->get_context()->id;

            $this->referenceslides = $filenameslides;
            $fs->delete_area_files($contextid, 'mod_richmedia', 'package');
            $package = $mform->save_stored_file('referenceslides', $contextid, 'mod_richmedia', 'package', 0, '/', $filenameslides);
            
            // now extract files
            $packer = get_file_packer('application/zip');
            $package->extract_to_storage($packer, $contextid, 'mod_richmedia', 'content', 0, '/');

            if ($update) {
                $this->save();
            }
        }
    }
    
    public function set_referencessubtitles($mform, $update = true) {
        $fs = get_file_storage();
        
        $filenamesubtitles = $mform->get_new_filename('referencessubtitles');
        
        if ($filenamesubtitles !== false) {
            
            $contextid = $this->get_context()->id;
            
            $this->referencessubtitles = $filenamesubtitles;
            $fs->delete_area_files($contextid, 'mod_richmedia', 'subtitles');
            $mform->save_stored_file('referencessubtitles', $contextid, 'mod_richmedia', 'subtitles', 0, '/', $filenamesubtitles);
            
            if ($update) {
                $this->save();
            }
        }
    }
    
    /**
     * Generate the sync file
     * @global type $CFG
     */
    public function generate_xml() {
        global $CFG;
        $context       = $this->get_context();
        $referencesxml = $this->referencesxml;
        $extension     = explode('.', $referencesxml);
        if (!$referencesxml || end($extension) != 'xml') {
            $this->referencesxml = "settings.xml";
        }
        $fs = get_file_storage();

        // Prepare file record object
        $fileinfo            = new stdClass();
        $fileinfo->component = 'mod_richmedia';
        $fileinfo->filearea  = 'content';
        $fileinfo->contextid = $context->id;
        $fileinfo->filepath  = '/';
        $fileinfo->itemid    = 0;
        $fileinfo->filename  = $referencesxml;
        // Get file
        $file                = $fs->get_file($fileinfo->contextid, $fileinfo->component, $fileinfo->filearea, $fileinfo->itemid, $fileinfo->filepath, $fileinfo->filename);

        // Read contents
        if ($file) {
            $contenuxml = $file->get_content();
            $contenuxml = str_replace('&', '&amp;', $contenuxml);

            $oldxml = \simplexml_load_string($contenuxml);
        }
        $xml   = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><settings></settings>');
        $movie = $xml->addChild('movie');
        if (isset($this->videourl) && !empty($this->videourl)) {
            $movie->addAttribute('src', $this->videourl);
        }
        else {
            $movie->addAttribute('src', 'contents/content/video/' . $this->referencesvideo);
        }

        if (isset($this->referencessubtitles) && !empty($this->referencessubtitles) && !is_numeric($this->referencessubtitles)) {
            $subtitles = $xml->addChild('subtitles');
            $subtitles->addAttribute('src', 'subtitles/' . $this->referencessubtitles);
        }

        $design = $xml->addChild('design');

        if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/logo.png')) {
            $design->addAttribute('logo', 'logo.png');
        }
        else if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/logo.jpg')) {
            $design->addAttribute('logo', 'logo.jpg');
        }

        $design->addAttribute('font', $this->font);

        if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/background.png')) {
            $design->addAttribute('background', 'background.png');
        }
        else if (file_exists($CFG->dirroot . '/mod/richmedia/themes/' . $this->theme . '/background.jpg')) {
            $design->addAttribute('background', 'background.jpg');
        }

        $design->addAttribute('theme', $this->theme);

        if ($this->fontcolor[0] == '#') {
            $this->fontcolor = substr($this->fontcolor, 1);
        }
        $design->addAttribute('fontcolor', '0x' . $this->fontcolor);
        if ($this->autoplay == 0) {
            $this->autoplay = 'false';
        }
        else {
            $this->autoplay = 'true';
        }

        $options = $xml->addChild('options');
        $options->addAttribute('presenter', '1');
        $options->addAttribute('comment', '0');
        $options->addAttribute('defaultview', $this->defaultview);
        $options->addAttribute('btnfullscreen', 'true');
        $options->addAttribute('btninverse', 'false');
        $options->addAttribute('autoplay', $this->autoplay);

        $presenter = $xml->addChild('presenter');
        $presenter->addAttribute('name', html_entity_decode($this->presentor));
        $presenter->addAttribute('biography', strip_tags(html_entity_decode($this->intro)));

        $titles = $xml->addChild('titles');
        $title1 = $titles->addChild('title');
        $title1->addAttribute('target', 'fdPresentationTitle');
        $title1->addAttribute('label', html_entity_decode($this->name));
        $title2 = $titles->addChild('title');
        $title2->addAttribute('target', 'fdMovieTitle');
        $title2->addAttribute('label', '');
        $title3 = $titles->addChild('title');
        $title3->addAttribute('target', 'fdSlideTitle');
        $title3->addAttribute('label', '');

        //traitement des steps
        $steps = $xml->addChild('steps');
        if ($file) {
            if ($oldxml) {
                foreach ($oldxml->steps[0]->children() as $childname => $childnode) {
                    $step = $steps->addChild('step');
                    foreach ($childnode->attributes() as $attribute => $value) {
                        $step->addAttribute($attribute, $value);
                    }
                }
            }
        }

        if ($file) {
            $file->delete();
        }
        $fs->create_file_from_string($fileinfo, $xml->asXML());
    }
    
    /**
     * Export the richmedia as a zip
     * @global type $CFG
     */
    public function export($data) {
        global $CFG;
        
        $scorm = $data->exportscorm ? true : false;
        
        $context = $this->get_context();
        
        require_capability('moodle/course:manageactivities', $context);

        $zipper = get_file_packer('application/zip');

        $fs = get_file_storage();

        //VIDEO
        // Prepare video record object
        $fileinfovideo            = new stdClass();
        $fileinfovideo->component = 'mod_richmedia';
        $fileinfovideo->filearea  = 'content';
        $fileinfovideo->contextid = $context->id;
        $fileinfovideo->filepath  = '/video/';
        $fileinfovideo->itemid    = 0;
        $fileinfovideo->filename  = $this->referencesvideo;
        // Get file
        $filevideo                = $fs->get_file($fileinfovideo->contextid, $fileinfovideo->component, $fileinfovideo->filearea, $fileinfovideo->itemid, $fileinfovideo->filepath, $fileinfovideo->filename);
        if ($filevideo) {
            $filevideoname                                               = $filevideo->get_filename();
            $files['richmedia/contents/content/video/' . $filevideoname] = $filevideo;
        }

        // Get subtitles 
        if (!empty($this->referencessubtitles)) {
            $fileinfosubtitles            = new stdClass();
            $fileinfosubtitles->component = 'mod_richmedia';
            $fileinfosubtitles->filearea  = 'subtitles';
            $fileinfosubtitles->contextid = $context->id;
            $fileinfosubtitles->filepath  = '/';
            $fileinfosubtitles->itemid    = 0;
            $fileinfosubtitles->filename  = $this->referencessubtitles;
            // Get file
            $filesubtitles                = $fs->get_file($fileinfosubtitles->contextid, $fileinfosubtitles->component, $fileinfosubtitles->filearea, $fileinfosubtitles->itemid, $fileinfosubtitles->filepath, $fileinfosubtitles->filename);
            if ($filesubtitles) {
                $filesubtitlesname                                                   = $filesubtitles->get_filename();
                $files['richmedia/contents/content/subtitles/' . $filesubtitlesname] = $filesubtitles;
            }
        }

        //XML
        $filexml = $this->get_xml();

        $files['richmedia/contents/content/' . $filexml->get_filename()] = $filexml;

        // SLIDES
        $slides = $fs->get_directory_files($context->id, 'mod_richmedia', 'content', 0, '/slides/');
        foreach ($slides as $slide) {
            $files['richmedia/contents/content/slides/' . $slide->get_filename()] = $slide;
        }

        //theme
        if (file_exists('themes/' . $this->theme . '/logo.png')) {
            $files['richmedia/themes/' . $this->theme . '/logo.png'] = 'themes/' . $this->theme . '/logo.png';
        }
        if (file_exists('themes/' . $this->theme . '/background.png')) {
            $files['richmedia/themes/' . $this->theme . '/background.png'] = 'themes/' . $this->theme . '/background.png';
        }
        if (file_exists('themes/' . $this->theme . '/logo.jpg')) {
            $files['richmedia/themes/' . $this->theme . '/logo.jpg'] = 'themes/' . $this->theme . '/logo.jpg';
        }
        if (file_exists('themes/' . $this->theme . '/background.jpg')) {
            $files['richmedia/themes/' . $this->theme . '/background.jpg'] = 'themes/' . $this->theme . '/background.jpg';
        }
        if (file_exists('themes/' . $this->theme . '/styles.css')) {
            $files['richmedia/themes/' . $this->theme . '/styles.css'] = 'themes/' . $this->theme . '/styles.css';
        }

        $files['richmedia/playerhtml5/pix']                 = 'playerhtml5/pix/';
        $files['richmedia/playerhtml5/js']                  = 'export/html5/js/';
        $files['richmedia/playerhtml5/js/settings.js']      = $this->generate_js();
        $files['richmedia/playerhtml5/css']                 = 'export/html5/css/';
        $files['richmedia/playerhtml5/css/playerhtml5.css'] = 'playerhtml5/css/playerhtml5.css';
        $files['richmedia/playerhtml5/js/player.js']        = 'playerhtml5/js/player.js';
        $files['richmedia/playerhtml5/js/cuepoint.js']      = 'playerhtml5/js/cuepoint.js';
        $files['richmedia/playerhtml5/js/jquery.srt.js']    = 'playerhtml5/js/jquery.srt.js';
        $files['richmedia/index.html']                      = 'export/html5/index.html';

        if ($scorm) {
            //js files
            $files['richmedia/js/communicationAPI.js'] = 'export/include/communicationAPI.js';
            $files['richmedia/js/scorm12.js']          = 'export/include/scorm12.js';
            // SCORM FILES
            $files['adlcp_rootv1p2.xsd']               = 'export/include/adlcp_rootv1p2.xsd';
            $files['ims_xml.xsd']                      = 'export/include/ims_xml.xsd';
            $files['imscp_rootv1p1p2.xsd']             = 'export/include/imscp_rootv1p1p2.xsd';
            $files['imsmanifest.xml']                  = 'export/include/imsmanifest.xml';
            $files['imsmd_rootv1p2p1.xsd']             = 'export/include/imsmd_rootv1p2p1.xsd';
        }

        //create the zip
        if ($newfile = $zipper->archive_to_storage($files, $fileinfovideo->contextid, 'mod_richmedia', 'zip', '0', '/', $data->name . '.zip')) {
            $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;
            send_stored_file($newfile, $lifetime, 0, false);
        }
        else {
            echo 'Une erreur s\'est produite'; // TODO : translate
        }
    }
    
    /**
     * Return settings.js file
     * @return stored_file
     */
    public function generate_js() {
        $filexml = $this->get_xml();
        $xmlContent = $filexml->get_content();
        $xmlContent = preg_replace("/(\r\n|\n|\r)/", " ", $xmlContent);
        $xmlContent = 'var xmlContent = \'' . $xmlContent . '\';';
        $fs         = get_file_storage();
        // Prepare file record object
        $fileinfo   = array(
            'contextid' => $this->get_context()->id, // ID of context
            'component' => 'mod_richmedia', // usually = table name
            'filearea'  => 'html', // usually = table name
            'itemid'    => 0, // usually = ID of row in table
            'filepath'  => '/', // any path beginning and ending in /
            'filename'  => 'settings.js'); // any filename


        $filejs = $fs->get_file(
            $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']
        );

        if ($filejs) {
            $filejs->delete();
        }
        return $fs->create_file_from_string($fileinfo, (String) $xmlContent);
    }

}
