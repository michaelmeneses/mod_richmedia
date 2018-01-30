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
 * Save the synchro
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../../config.php");

$video          = optional_param('movie', null, PARAM_RAW);
$presentertitle = optional_param('presentertitle', null, PARAM_RAW);
$presentername  = optional_param('presentername', null, PARAM_RAW);
$tabsteps       = optional_param('steps', null, PARAM_RAW);
$contextid      = optional_param('contextid', null, PARAM_RAW);
$update         = optional_param('update', null, PARAM_RAW);
$color          = optional_param('fontcolor', null, PARAM_RAW);
$font           = optional_param('font', null, PARAM_RAW);
$defaultview    = optional_param('defaultview', null, PARAM_RAW);
$autoplay       = optional_param('autoplay', null, PARAM_RAW);
$title          = optional_param('title', null, PARAM_RAW);
$subtitles      = optional_param('subtitles', null, PARAM_RAW);

$context = context::instance_by_id($contextid, MUST_EXIST);

require_capability('moodle/course:manageactivities', $context);

if (!empty($video) && !empty($presentername) && !empty($tabsteps) && !empty($title) && !empty($contextid)) {
    $module          = $DB->get_record('course_modules', array('id' => $update));
    $courserichmedia = $DB->get_record('richmedia', array('id' => $module->instance));

    $xml   = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><settings></settings>');
    $movie = $xml->addChild('movie');
    $movie->addAttribute('src', 'contents/content/video/' . $video);

    if (isset($richmedia->referencessubtitles) && !empty($richmedia->referencessubtitles)) {
        $subtitles = $xml->addChild('subtitles');
        $subtitles->addAttribute('src', 'subtitles/' . $richmedia->referencessubtitles);
    }

    $design = $xml->addChild('design');
    $design->addAttribute('logo', 'logo.jpg');
    $design->addAttribute('font', $font);
    $design->addAttribute('background', 'background.jpg');
    $design->addAttribute('fontcolor', '0x' . $color);

    $options = $xml->addChild('options');
    $options->addAttribute('presenter', '1');
    $options->addAttribute('comment', '0');
    $options->addAttribute('defaultview', $defaultview);
    $options->addAttribute('btnfullscreen', 'true');
    $options->addAttribute('btninverse', 'false');
    if (!$autoplay || $autoplay == 0) {
        $autoplayxml = "false";
    }
    else {
        $autoplayxml = "true";
    }
    $options->addAttribute('autoplay', $autoplayxml);

    $presenter = $xml->addChild('presenter');
    $presenter->addAttribute('name', html_entity_decode($presentername));
    $presenter->addAttribute('biography', strip_tags(html_entity_decode($courserichmedia->intro)));
    $presenter->addAttribute('title', html_entity_decode($presentertitle));

    $titles = $xml->addChild('titles');
    $title1 = $titles->addChild('title');
    $title1->addAttribute('target', 'fdPresentationTitle');
    $title1->addAttribute('label', html_entity_decode($title));
    $title2 = $titles->addChild('title');
    $title2->addAttribute('target', 'fdMovieTitle');
    $title2->addAttribute('label', '');
    $title3 = $titles->addChild('title');
    $title3->addAttribute('target', 'fdSlideTitle');
    $title3->addAttribute('label', '');

    $steps    = $xml->addChild('steps');
    //traitement des steps
    $tabsteps = substr($tabsteps, 1); // on enleve le 1er caractere
    $tabsteps = substr($tabsteps, 0, -1); // on enleve le dernier caractere
    $tabsteps = str_replace('\"', '', $tabsteps);
    $tabsteps = str_replace(',[', '', $tabsteps);
    $tabsteps = str_replace('[', '', $tabsteps);
    $tabsteps = explode("]", $tabsteps);

    $attrNames = array(
        'id',
        'label',
        'framein',
        'slide',
        'question',
        'view'
    );
    for ($i = 0; $i < count($tabsteps) - 1; $i++) {
        $step       = $steps->addChild('step');
        $attributes = explode(',', $tabsteps[$i]);
        $j          = 0;

        foreach ($attributes as $attribute) {
            $attribute = str_replace('"', '', $attribute);
            if ($attrNames[$j] == 'framein') {
                $tabframein = explode(':', $attribute);
                $attribute  = 60 * $tabframein[0] + $tabframein[1];
            }
            $step->addAttribute($attrNames[$j], $attribute);
            $j++;
        }
    }
    $fs = get_file_storage();

    // Prepare file record object
    $fileinfo            = new stdClass();
    $fileinfo->component = 'mod_richmedia';
    $fileinfo->filearea  = 'content';
    $fileinfo->contextid = $contextid;
    $fileinfo->filepath  = '/';
    $fileinfo->itemid    = 0;
    $fileinfo->filename  = 'settings.xml';
    // Get file
    $file                = $fs->get_file($fileinfo->contextid, $fileinfo->component, $fileinfo->filearea, $fileinfo->itemid, $fileinfo->filepath, $fileinfo->filename);
    if ($file) {
        $file->delete();
    }
    $fs->create_file_from_string($fileinfo, $xml->asXML());

    if (!strpos($courserichmedia->referencesxml, '.xml')) {
        $courserichmedia->referencesxml = 'settings.xml';
    }
    $DB->update_record('richmedia', $courserichmedia);
}
else {
    echo 1;
}
