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

class Event {
    
    private $id;
    private $eventid;
    private $end_policy;
    private $end_hour;
    private $data;
    private $mysm_ref;
    
    protected function __construct( $config ) {
        if( isset( $config->id ) )
            $this->id = $config->id;
        $this->eventid = $config->eventid;
        $this->end_hour = $config->end_hour !== null ? 
            intval($config->end_hour) : null;
        $this->end_policy = $config->end_policy;
        $this->end_policy = $config->end_policy !== null ? 
            intval($config->end_policy) : null;
        if( isset( $config->mysm_ref ) )
            $this->mysm_ref = $config->mysm_ref;
        global $DB;
        $this->data = $DB->get_record( "event", 
                                       array( "id" => $this->eventid ) );
    }

    static function retrieve( $eventid ) {
        global $DB;
        $config = $DB->get_record( "block_smartblock_event", 
                                   array( "eventid" => $eventid ) );
        
        if( $config )
            return new Event( $config );
        return null;
    }
    
    static function create( $config ) {
        global $DB;
        $event = Event::retrieve( $config->eventid );
        if( $event != null ) {
            return $event;
        }
        else {
            $event = new Event( $config );
            $event->insert();
        }
        return $event;
    }

    static function manage( $data ) {
        if( !isset( $data->eventid ) )
            return Event::getEmptyConfig();
        $event = Event::retrieve( $data->eventid );
        if( $data->enable ) {
            if( $event ) $event->update( $data );
            else $event = Event::create( $data );
            return $event->getConfig();
        }
        else {
            if( $event ) $event->delete();
            return Event::getEmptyConfig();
        }
    }

    protected function insert() {
        global $DB;
        $course = Course::retrieve( $this->data->courseid );
        $sdk = $course->getSDK();
        $ref = $sdk->api( "/me/objects", "POST", $this->factoryMysmObject() );
        if( $ref == null ) 
            throw new Exception( get_string( "error_mysmark",
                                             "block_smartblock" ) );
        $config = array(
                        "eventid" => $this->eventid,
                        "end_policy" => $this->end_policy,
                        "end_hour" => $this->end_hour,
                        "mysm_ref" => $ref['id']
                        );
        $ret = $DB->insert_record( "block_smartblock_event", $config );
        $this->id = $ret;
        return $ret;
    }

    protected function update( $config ) {
        global $DB;
        $config->id = $this->id;
        $config->eventid = $this->eventid;
        $this->end_hour = $config->end_hour !== null ? 
            intval($config->end_hour) : null;
        $this->end_policy = $config->end_policy !== null ? 
            intval($config->end_policy) : null;
        $config->mysm_ref = $this->mysm_ref;
        
        if( $this->mysm_ref ) {
            $course = Course::retrieve( $this->data->courseid );
            $course->getSDK()->api( "/me/objects/".$this->mysm_ref, "POST", 
                                    $this->factoryMysmObject() );
        }
        return $DB->update_record( "block_smartblock_event", $config );
    }

    protected function delete() {
        global $DB;
        if( count( $this->getEnabledStudents() ) > 0 )
            throw new Exception( get_string( "error_remove_student_ondelete",
                                             "block_smartblock" ) );
        if( $this->mysm_ref != NULL ) {
            $course = Course::retrieve( $this->data->courseid );
            $course->getSDK()->api( "/me/objects/".$this->mysm_ref, "DELETE" );
        }
        $DB->delete_records_select( "block_smartblock_presence", "eventid = ?",
                                    array( $this->eventid ) );
        return $DB->delete_records_select( "block_smartblock_event", "id = ?",
                                           array( $this->id ) );
    }

    static function isEnabled( $eventid ) {
        global $DB;
        $out = $DB->get_record( "block_smartblock_event", 
                                array( "eventid" => $eventid ) );
        if( $out )
            return TRUE;
        return FALSE;
    }

    public function getData() {
        return $this->data;
    }

    public function getExpires() {
        $expires = $this->data->timestart + $this->data->timeduration;
        if( $this->end_policy === 0 )
            $expires += $this->end_hour * 60 * 60;
        else if( $this->end_policy === 1 ) {
            $date = getdate( $expires );
            $expires = mktime( $this->end_hour, 0, 0, $date['mon'], 
                               $date['mday'], $date['year'] );
        }
        else if( $this->end_policy === 2 ) {
            $date = getdate( strtotime("+1 day", $expires ) );
            $expires = mktime( $this->end_hour, 0, 0, $date['mon'], 
                               $date['mday'], $date['year'] );
        }
        else {
            $course = Course::retrieve( $this->data->courseid );
            $expires = $course->getExpires( $expires );
        }
        return $expires;
    }

    protected function factorymysmobject() {
        $expires = $this->getexpires();
        $course = Course::retrieve( $this->data->courseid );
        $parameters = $course->getParamsJSON();
        return array( 
                     "type" => "Object",
                     "name" => $this->data->name,
                     "description" => null,
                     "url" => null,
                     "image" => null,
                     "singleVote" => true,
                     "protected" => true,
                     "category" => "lesson",
                     "external_ref" => $this->data->id,
                     "json" => null,
                     "location" => null,
                     "start" => $this->data->timestart +
                                $this->data->timeduration,
                     "expires" => $expires,
                     "parameters" => $parameters
                      );
    }

    static function validateConfig( array $data ) {
        $filter = array(
                        "enable"     => array( "filter" => 
                                               FILTER_VALIDATE_BOOLEAN ),
                        "end_policy" => array( "filter" => 
                                               FILTER_VALIDATE_INT,
                                               "flags" => 
                                               FILTER_NULL_ON_FAILURE ),
                        "end_hour"   => array( "filter" => 
                                               FILTER_VALIDATE_INT,
                                               "flags" => 
                                               FILTER_NULL_ON_FAILURE ),
                        "eventid"    => array( "filter" =>
                                               FILTER_VALIDATE_INT ),
                        "enable"     => array( "filter" =>
                                               FILTER_VALIDATE_BOOLEAN )
                        );
        $config = (object) filter_var_array( $data, $filter );
        $config->error = false;
        if( !$config->enable )
            return $config;
        // Check policy
        if( $config->end_policy < 0 || $config->end_policy > 2 )
            throw new Exception( get_string( "error_invalid_policy",
                                             "block_smartblock") );
        // Check end hour for 1,2 end policy
        if( $config->end_policy > 0 && $config->end_policy <= 2 &&
            ($config->end_hour <= 0 || $config->end_hour > 24) )
            throw new Exception( get_string( "error_invalid_hours",
                                             "block_smartblock" ) );
        // Check for 0 end policy
        if( $config->end_policy === 0 && $config->end_hour <= 0 )
            throw new Exception( get_string( "error_invalid_hours",
                                             "block_smartblock" ) );
        // Store end hour
        if( $config->end_policy === NULL )
            $config->end_hour = NULL;
        return $config;
    }

    public function getConfig() {
        $config = self::getEmptyConfig();
        $config->enable = true;
        $config->end_policy = $this->end_policy;
        $config->end_hour = $this->end_hour;
        $config->msg = "";
        $config->id = $this->id;
        $config->eventid = $this->eventid;
        return $config;
    }

    static function getEmptyConfig( $eventid = null ) {
        $config = new StdClass();
        $config->enable = false;
        $config->end_policy = null;
        $config->end_hour = null;
        $config->msg = "";
        $config->id = null;
        $config->eventid = $eventid;
        $config->error = false;
        return $config;
    }

    public function getHash( $studentid ) {
        global $DB;
        $presence = $DB->get_record( "block_smartblock_presence", 
                                     array( 
                                           "eventid" => $this->eventid,
                                           "studentid" => $studentid ) );
        if( $presence == null || $this->mysm_ref == null )
            throw new Exception( get_string( "error_invalid_presence",
                                             "block_smartblock" ) );
        return $presence->hash;
    }

    public function createHash( $studentid ) {
        global $DB;
        $presence = $DB->get_record( "block_smartblock_presence", 
                                     array( 
                                           "eventid" => $this->eventid,
                                           "studentid" => $studentid ) );
        if( $presence == null || $this->mysm_ref == null )
            throw new Exception( get_string( "error_invalid_presence",
                                             "block_smartblock" ) );
        $course = Course::retrieve( $this->data->courseid );
        $sdk = $course->getSDK();
        if( $presence->hash == null ) {
            $hash = md5(mt_rand());
            $ret = $sdk->api( "/hash/me/objects/".$this->mysm_ref, "POST",
                              array( "hash" => $hash ) );
            if( $ret == null )
                throw new Exception( get_string( "error_mysmark",
                                                 "block_smartblock" ) );
            $presence->hash = $hash;
            $DB->update_record( "block_smartblock_presence", $presence );
        }
        else {
            $hashs = $sdk->api( "/hash/me/objects/".$this->mysm_ref, "GET" );
            if( !is_array( $hashs ) ||
                !in_array( $presence->hash, $hashs ) ) {
                $ret = $sdk->api( "/hash/me/objects/".$this->mysm_ref, "POST",
                                  array( "hash" => $presence->hash ) );
                if( $ret == null )
                    throw new Exception( get_string( "error_mysmark",
                                                     "block_smartblock" ) );
            }
        }
        return $presence->hash;
    }

    public function getEnabledStudents() {
        global $DB;
        $students = array();
        $studs = $DB->get_records( "block_smartblock_presence", 
                                   array( "eventid" => $this->eventid ) );
        if( $studs == NULL ) return $students;
        foreach( $studs as $stud ) {
            $student = Student::retrieve( $stud->studentid );
            if( $student )
                array_push( $students, $student );
        }
        return $students;
    }

    public function getMysmRef() {
        return $this->mysm_ref;
    }
}

?>
