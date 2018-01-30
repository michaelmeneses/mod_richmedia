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
 * PLugin settings
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/richmedia/locallib.php');

    $settings->add(new admin_setting_configtext('richmedia/width', get_string('width', 'richmedia'), get_string('defaultwidth', 'richmedia'), 700));

    $settings->add(new admin_setting_configtext('richmedia/height', get_string('height', 'richmedia'), get_string('defaultheight', 'richmedia'), 451));

    $settings->add(new admin_setting_configselect('richmedia/font', get_string('police', 'richmedia'), get_string('defaultfont', 'richmedia'), 'Arial', array("Arial" => "Arial", "Courier new" => "Courier new", "Georgia" => "Georgia", "Times New Roman" => "Times New Roman", "Verdana" => "Verdana")));

    $settings->add(new admin_setting_configtext('richmedia/fontcolor', get_string('fontcolor', 'richmedia'), get_string('defaultfontcolor', 'richmedia'), '#000000'));

    $settings->add(new admin_setting_configselect('richmedia/autoplay', get_string('autoplay', 'richmedia'), get_string('defaultautoplay', 'richmedia'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));

    $settings->add(new admin_setting_configselect('richmedia/defaultview', get_string('defaultview', 'richmedia'), get_string('defaultdefaultview', 'richmedia'), 0, array(1 => get_string('tile', 'richmedia'), 2 => get_string('presentation', 'richmedia'), 3 => get_string('video', 'richmedia'))));
}
