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
 * Plugin additional functions
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * 
 * @param string $value
 * @return type
 */
function richmedia_convert_to_html($value) {
    $items = array(
        "é" => "&eacute;",
        "è" => "&egrave;",
        "ê" => "&ecirc;",
        "à" => "&agrave;",
        "ç" => "&ccedil;",
        "û" => "&ucirc;"
    );
    $value = str_replace(array_keys($items), array_values($items), $value);
    return $value;
}

/**
 * 
 * @param int $nbsecondes
 * @return type
 */
function richmedia_convert_time($nbsecondes) {
    $temp    = $nbsecondes % 3600;
    $time[0] = ( $nbsecondes - $temp ) / 3600;
    $time[2] = $temp % 60;
    $time[1] = ( $temp - $time[2] ) / 60;

    if ($time[1] == 0 || (is_int($time[1]) && $time[1] < 10)) {
        $time[1] = '0' . $time[1];
    }
    if (is_int($time[2]) && $time[2] < 10) {
        $time[2] = '0' . $time[2];
    }
    return $time[1] . ':' . $time[2];
}

/**
 * Encode a string to be read by the richmedia player
 * @param string $value
 * @return type
 */
function richmedia_encode_string($value) {
    $value = str_replace("&rsquo;", iconv("CP1252", "UTF-8", "’"), $value);
    $value = str_replace("â€™", "’", $value);
    $value = str_replace("’", "'", $value);
    return $value;
}
