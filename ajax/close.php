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
 * File called when a module page is closed
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../../config.php");

$richmediaid = required_param('richmediaid', PARAM_INT);
$time        = optional_param('time', 0, PARAM_INT);

require_login();

if ($track = $DB->get_record('richmedia_track', array('userid' => $USER->id, 'richmediaid' => $richmediaid))) {
    $track->last = time();
    $track->time = $time;
    $DB->update_record('richmedia_track', $track);
}
else {
    $track              = new stdClass();
    $track->userid      = $USER->id;
    $track->richmediaid = $richmediaid;
    $track->attempt     = 1;
    $track->start       = time();
    $track->last        = $track->start;
    $track->time        = $time;
    $DB->insert_record('richmedia_track', $track);
}

echo 1;