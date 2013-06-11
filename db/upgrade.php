<?php
/*
 * This file is part of MySmarkEDU.
 *
 * MySmarkEDU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MySmarkEDU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MySmarkEDU. If not, see <http://www.gnu.org/licenses/>.
 */

global $DB;
if( $DB == null ) {
    include_once dirname(__FILE__).'../include/class/DbEmulation.php';
    $DB = new DbEmulation();
}

function xmldb_block_smartblock_upgrade($oldversion = 0) {
    global $DB;
    $result = true;
    
    // 2.x
    if(!($DB instanceof DbEmulation)) {
        if ($oldversion < 2012100800) {
            // Define table block_smartblock_nfc to be created
            $table = new xmldb_table('block_smartblock_nfc');
            // Adding fields to table block_smartblock_nfc
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10',
                              XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, 
                              null);
            $table->add_field('studentid', XMLDB_TYPE_INTEGER, '11',
                              XMLDB_UNSIGNED, XMLDB_NOTNULL, null,
                              null);
            $table->add_field('hash', XMLDB_TYPE_CHAR, '64',
                              null, XMLDB_NOTNULL, null,
                              null);
            // Adding keys to table block_smartblock_nfc
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('hash', XMLDB_KEY_UNIQUE, array('hash'));
            // Conditionally launch create table for block_smartblock_nfc
            $dbman = $DB->get_manager();
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }
            // smartblock savepoint reached
            upgrade_block_savepoint(true, 2012100800, 'smartblock');
        }
        if ($oldversion < 2013031900) {
            $dbman = $DB->get_manager();
            // Define field course_start to be added to block_smartblock_course
            $table = new xmldb_table('block_smartblock_course');
            $field = new xmldb_field('course_start', XMLDB_TYPE_INTEGER, 
                                     '8', null, XMLDB_NOTNULL, null, 
                                     '0', 'params_enable');
            
            // Conditionally launch add field course_start
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Changing precision of field end_hour on table 
            // block_smartblock_course to (11)
            $table = new xmldb_table('block_smartblock_course');
            $field = new xmldb_field('end_hour', XMLDB_TYPE_INTEGER, 
                                     '11', null, XMLDB_NOTNULL, null, 
                                     '0', 'end_policy');
            // Launch change of precision for field end_hour
            $dbman->change_field_precision($table, $field);
            
            // smartblock savepoint reached
            upgrade_block_savepoint(true, 2013031900, 'smartblock');
        }
    }
    // 1.9
    else {
        if ($result && $oldversion < 2012100800) {
            // Define table block_smartblock_nfc to be created
            $table = new XMLDBTable('block_smartblock_nfc');
            // Adding fields to table block_smartblock_nfc
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10',
                                 XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,
                                 null, null, null);
            $table->addFieldInfo('studentid', XMLDB_TYPE_INTEGER, '11',
                                 XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 
                                 null, null, null);
            $table->addFieldInfo('hash', XMLDB_TYPE_CHAR, '64',
                                 null, XMLDB_NOTNULL, null,
                                 null, null, null);
            // Adding keys to table block_smartblock_nfc
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('hash', XMLDB_KEY_UNIQUE, array('hash'));
            // Launch create table for block_smartblock_nfc
            $result = $result && create_table($table);
        }
        if ($result && $oldversion < 2013031900) {
            
            /// Add field course_start
            $table = new XMLDBTable('block_smartblock_course');
            $field = new XMLDBField('course_start');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '8', null, 
                                  XMLDB_NOTNULL, null, null, null, 
                                  '0', 'params_enable');
            /// Launch add field course_start
            $result = $result && add_field($table, $field);
            
            /// Changing precision of field end_hour on
            /// table block_smartblock_course to (11)
            $table = new XMLDBTable('block_smartblock_course');
            $field = new XMLDBField('end_hour');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, null, null, null, 
                                  '0', 'end_policy');
            /// Launch change of precision for field end_hour
            $result = $result && change_field_precision($table, $field);
        }
    }
    
    return $result;
}
?>
