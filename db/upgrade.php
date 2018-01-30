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
 * Plugins upgrade file
 *
 * @package   mod_richmedia
 * @copyright 2018 Adrien Jamot <adrien@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_richmedia_upgrade($oldversion = 0) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012062100) {
        $table     = new xmldb_table('richmedia');
        $presentor = new xmldb_field('presentor');
        $presentor->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null);

        /// Launch add field 
        if (!$dbman->field_exists($table, $presentor)) {
            $dbman->add_field($table, $presentor);
        }
        $font = new xmldb_field('font');
        $font->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null);

        /// Launch add field 
        if (!$dbman->field_exists($table, $font)) {
            $dbman->add_field($table, $font);
        }
        $fontcolor = new xmldb_field('fontcolor');
        $fontcolor->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null);

        /// Launch add field 
        if (!$dbman->field_exists($table, $fontcolor)) {
            $dbman->add_field($table, $fontcolor);
        }
        $defaultview = new xmldb_field('defaultview');
        $defaultview->set_attributes(XMLDB_TYPE_INTEGER, 1, null, null, null, null, null);

        /// Launch add field 
        if (!$dbman->field_exists($table, $defaultview)) {
            $dbman->add_field($table, $defaultview);
        }
        $referencessynchro = new xmldb_field('referencessynchro');
        $referencessynchro->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null);

        /// Launch add field 
        if (!$dbman->field_exists($table, $referencessynchro)) {
            $dbman->add_field($table, $referencessynchro);
        }
        $autoplay = new xmldb_field('autoplay');
        $autoplay->set_attributes(XMLDB_TYPE_INTEGER, 1, null, null, null, null, null);

        /// Launch add field 
        if (!$dbman->field_exists($table, $autoplay)) {
            $dbman->add_field($table, $autoplay);
        }
        upgrade_mod_savepoint(true, 2012062100, 'richmedia');
    }
    else if ($oldversion < 2012071600) {
        $table = new xmldb_table('richmedia');
        $field = new xmldb_field('keywords');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null);
        /// Launch add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2012071600, 'richmedia');
    }

    if ($oldversion < 2014030300) {
        $table = new xmldb_table('richmedia');
        $field = new xmldb_field('quizid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, null, null, null, null, null);
        /// Launch add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2014030300, 'richmedia');
    }
    if ($oldversion < 2014040200) {
        $table = new xmldb_table('richmedia');
        $field = new xmldb_field('recovery');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, null, null, null, 0, null);
        /// Launch add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('richmedia_track');
        $field = new xmldb_field('time');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, null, null, null, 0, null);
        /// Launch add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2014040200, 'richmedia');
    }

    if ($oldversion < 2014102900) {
        $table   = new xmldb_table('richmedia');
        $catalog = new xmldb_field('catalog');
        $catalog->set_attributes(XMLDB_TYPE_INTEGER, 1, null, null, null, 0, null);

        /// Launch add field 
        if (!$dbman->field_exists($table, $catalog)) {
            $dbman->add_field($table, $catalog);
        }
        upgrade_mod_savepoint(true, 2014102900, 'richmedia');
    }


    if ($oldversion < 2015020600) {
        $table = new xmldb_table('richmedia');
        $field = new xmldb_field('videourl');
        $field->set_attributes(XMLDB_TYPE_CHAR, 255, null, null, null, null, null);
        /// Launch add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2015021000) {
        $table = new xmldb_table('richmedia');
        $field = new xmldb_field('referencessubtitles');
        $field->set_attributes(XMLDB_TYPE_CHAR, 255, null, null, null, null, null);
        /// Launch add field 
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
