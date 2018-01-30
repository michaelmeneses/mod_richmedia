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
 * Plugin renderer
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/richmedia/lib.php');

class mod_richmedia_renderer extends plugin_renderer_base {

    /**
     * Print richmedia intro + keywords
     * @global type $CFG
     * @param richmedia $richmedia
     * @return string
     */
    public function intro($richmedia) {
        $output = '<div id="intro-div" >';
        $output .= $richmedia->intro;
        $output .= '</div>';
        return $output;
    }

}
