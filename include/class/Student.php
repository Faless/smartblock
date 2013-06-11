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

class Student extends User {
    
    private $data;
    private $id;
    
    public function __construct( $data ) {
        $this->data = $data;
        $this->id = $data->id;
    }
    
    static function isStudent( $user = null, $course = null ) {
        return TRUE;
    }

    static function isProfessor( $user = null, $course = null ) {
        return FALSE;
    }
    
    static function retrieve( $id = null, $course = null ) {
        if( $id == null ) {
            global $USER;
            $id = $USER->id;
        }
        global $DB;
        $data = $DB->get_record( 'user', array( "id" => $id ) );
        if( $data != null )
            return new Student( $data );
        return null;
    }

    public function enable( $eventid ) {
        global $DB;
        $config = $DB->get_record( "block_smartblock_presence",
                                   array(
                                         "eventid" => $eventid,
                                         "studentid" => $this->data->id
                                         ) );
        if( !$config )
            $DB->insert_record( 'block_smartblock_presence', 
                                array( 
                                      "eventid" => $eventid,
                                      "studentid" => $this->data->id
                                       ) );
    }

    public function disable( $eventid ) {
        global $DB;
        $config = $DB->get_record( "block_smartblock_presence",
                                   array(
                                         "eventid" => $eventid,
                                         "studentid" => $this->data->id
                                         ) );
        if( $config ) {
            if( $config->hash != null )
                throw new Exception( get_string( "error_cant_delete_presence",
                                                 "block_smartblock" ) );
            $DB->delete_records_select( "block_smartblock_presence", "id = ?",
                                        array( $config->id ) );
        }
    }

    public function getEnrollmentDate( $courseid ) {
        global $DB;
        $sql = "SELECT timemodified FROM {role_assignments} WHERE 
                contextid = ? AND userid = ?";
        $context = get_context_instance( CONTEXT_COURSE, $courseid );
        $record = $DB->get_record_sql( $sql,
                                       array( $context->id, $this->data->id ) );
        return $record->timemodified;
    }

    public function isEnabled( $eventid ) {
        global $DB;
        $config = $DB->get_record( "block_smartblock_presence", 
                                   array( "studentid" => $this->data->id, 
                                          "eventid" => $eventid  ) );
        if( $config != null )
            return TRUE;
        else return FALSE;
    }

    public function getData() {
        return $this->data;
    }
    
}

?>
