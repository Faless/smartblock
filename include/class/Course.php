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

class Course {
    
    private $end_policy;
    private $end_hour;
    private $client_id;
    private $client_secret;
    private $id;
    private $courseid;
    private $params_enable;
    private $params_scale;
    private $params_label;
    private $params_name;
    private $course_start;

    protected function __construct( $config ) {
        $this->end_policy = intval( $config->end_policy );
        $this->end_hour = intval( $config->end_hour );
        $this->client_id = $config->client_id;
        $this->client_secret = $config->client_secret;
        $this->courseid = $config->courseid;
        $this->id = $config->id;
        $this->params_enable = $config->params_enable;
        $this->params_name = json_decode($config->params_name);
        $this->params_scale = json_decode($config->params_scale);
        $this->params_label = json_decode($config->params_label);
        $this->course_start = $config->course_start;
    }

    static function retrieve( $courseid = null ) {
        if( $courseid === null ) {
            global $COURSE;
            $courseid = $COURSE->id;
        }
        global $DB;
        $config = $DB->get_record( "block_smartblock_course",
                                  array('courseid' => $courseid ) );
        if( $config === false )
            return null;
        return new Course( $config );
    }

    static function create( $config, $courseid = null ) {
        global $DB;
        $ret = $DB->insert_record('block_smartblock_course', $config);
        $config->id = $ret;
        return new Course( $config );
    }

    static function manage( $config, $courseid ) {
        if( $courseid === null )
            throw new Exception( get_string( "error_generic",
                                             "block_smartblock" ) );
        global $DB;
        if( $DB instanceof DbEmulation ) {
            // Fix more madness on slash stripping in moodle < 2.0
            $config->params_name = addslashes(json_encode($config
                                                          ->params_name));
            $config->params_label = addslashes(json_encode($config
                                                           ->params_label));
            $config->params_scale = addslashes(json_encode($config
                                                           ->params_scale));
        } else {
            $config->params_name = json_encode($config->params_name);
            $config->params_label = json_encode($config->params_label);
            $config->params_scale = json_encode($config->params_scale);
        }
        $course = Course::retrieve( $courseid );
        if( $course ) {
            $course->update( $config );
        } else {
            $course = Course::create( $config );
        }
        return $course;
    }

    protected function delete() {
        global $DB;
        if( count( $this->getEnabledEvents() ) > 0 )
            throw new Exception( get_string( "error_remove_event_ondelete", 
                                             "block_smartblock" ) );
        $DB->delete_records_select( 'block_smartblock_course', 'courseid = ?',
                                    array( $this->id ) );
    }

    protected function update( $config ) {
        global $DB;
        if( ($this->client_id != $config->client_id ||
            $this->client_secret != $config->client_secret) &&
            count( $this->getEnabledEvents() ) > 0 )
            throw new Exception( get_string( "error_remove_event_onupdate",
                                             "block_smartblock" ) );
        $config->id = $this->id;
        return $DB->update_record( 'block_smartblock_course', $config );
    }

    static function isConfigured( $courseid = null ) {
        if( $courseid == null ) {
            global $COURSE;
            $courseid = $COURSE->id;
        }
        global $DB;
        $out = $DB->get_record( "block_smartblock_course", 
                                array( 'courseid' => $courseid ) );
        return $out !== false;
    }

    public function getSDK() {
         return new MysmSDK( $this->client_id, $this->client_secret );
    }

    public function getExpires( $endtime ) {
        if( $this->end_policy === 0 )
            $endtime += $this->end_hour * 60 * 60;
        else if( $this->end_policy === 1 ) {
            $date = getdate( $endtime );
            $endtime = mktime( $this->end_hour, 0, 0, $date['mon'], 
                               $date['mday'], $date['year'] );
        }
        else if( $this->end_policy === 2 ) {
            $date = getdate( strtotime("+1 day",$endtime ) );
            $endtime = mktime( $this->end_hour, 0, 0, $date['mon'], 
                               $date['mday'], $date['year'] );
        }
        return $endtime;
    }
    
    static function validateConfig( array $array ) {
        $filter = array(
                        "client_id"     => array( "filter" => 
                                                  FILTER_UNSAFE_RAW ),
                        "client_secret" => array( "filter" => 
                                                  FILTER_UNSAFE_RAW ),
                        "end_policy"    => array( "filter" => 
                                                  FILTER_VALIDATE_INT ),
                        "end_hour"      => array( "filter" => 
                                                  FILTER_VALIDATE_INT ),
                        "params_enable" => array( "filter" =>
                                                  FILTER_VALIDATE_BOOLEAN ),
                        "course_start" => array( "filter" => 
                                                 FILTER_UNSAFE_RAW )

                    );
        $config = (object) filter_var_array( $array, $filter );
        
        if( $config->params_enable ) {
            $config->params_scale = array_key_exists('params_scale', $array) &&
                is_array($array['params_scale']) ? 
                $array['params_scale'] : array();
            $config->params_label = array_key_exists('params_label', $array) &&
                is_array($array['params_label']) ? 
                $array['params_label'] : array();
            $config->params_name = array_key_exists('params_name', $array) && 
                is_array($array['params_name']) ? 
                $array['params_name'] : array();

            $l = count( $config->params_scale );
            if( $l < 2 )
                throw new Exception( get_string('error_invalid_scale',
                                                'block_smartblock') );
            for ($i = 0; $i < $l; $i++) {
                $value = floatval( $config->params_scale[$i] );
                if( $value == null )
                    throw new Exception( get_string('error_invalid_scale',
                                                    'block_smartblock') );
                $config->params_scale[$i] = $value;
            }
            if( count( array_unique( $config->params_scale ) ) != $l )
                throw new Exception( get_string('error_invalid_scale_values',
                                                'block_smartblock' ) );
            if( count( $config->params_label ) != $l )
                throw new Exception( get_string('error_invalid_label',
                                                'block_smartblock') );
            // Sort values
            $tempValues = $config->params_scale;
            $tempLabels = $config->params_label;
            sort($config->params_scale);
            for( $i = 0; $i < $l; $i++ ) {
                $pos = array_search( $config->params_scale[$i], $tempValues );
                $config->params_label[$i] = $tempLabels[$pos];
            }
            if( count( $config->params_name ) < 3 || 
                count( $config->params_name ) > 8 )
                throw new Exception( get_string('error_invalid_parameters',
                                                'block_smartblock') );
        } else {
            $config->params_scale = array();
            $config->params_label = array();
            $config->params_name = array();
        }
        // client credentials
        $sdk = new MysmSDK( $config->client_id, $config->client_secret );
        try {
            $sdk->api( "me", "GET" ) === null;
        } catch( Exception $e ) {
            throw new Exception( get_string( "error_invalid_client_credentials",
                                             "block_smartblock") );
        }
        // end_policy
        if( $config->end_policy < 0 || $config->end_policy > 2 ) {
            throw new Exception( get_string( "error_invalid_policy",
                                             "block_smartblock" ) );
        }
        // end_hour
        if( !is_numeric($config->end_hour) )
            throw new Exception( get_string( "error_invalid_hours",
                                             "block_smartblock" ) );
        if( !is_numeric($config->end_policy) ||
            ($config->end_policy > 0 && $config->end_hour > 24 ) )
            throw new Exception( get_string( "error_invalid_hours",
                                             "block_smartblock" ) );
        // course_start
        if( !is_numeric($config->course_start) ) {
            $temp = strtotime($config->course_start);
            if( $temp === false ) {
                throw new Exception( get_string( "error_invalid_course_start",
                                                 "block_smartblock") );
            }
            $config->course_start = $temp;
        }
        return $config;
    }

    public function getConfig() {
        $config = new StdClass();
        $config->client_id = $this->client_id;
        $config->client_secret = $this->client_secret;
        $config->end_policy = $this->end_policy;
        $config->end_hour = $this->end_hour;
        $config->params_enable = $this->params_enable;
        $config->params_name = $this->params_name;
        $config->params_label = $this->params_label;
        $config->params_scale = $this->params_scale;
        $config->course_start = $this->course_start;
        return $config;
    }

    public static function getEmptyConfig() {
        $config = new StdClass();
        $config->client_id = "";
        $config->client_secret = "";
        $config->end_policy = "";
        $config->end_hour = "";
        $config->params_enable = false;
        $config->params_scale = array();
        $config->params_name = array();
        $config->params_label = array();
        $config->course_start = 0;
        return $config;
    }

    public function getParamsJSON() {
        if( !$this->params_enable ) return null;
        $out = array();
        $out['values'] = $this->params_scale;
        $out['labels'] = $this->params_label;
        $out['names'] = $this->params_name;
        return $out;
    }

    public function getStudents() {
        global $DB;
        $context = get_context_instance(CONTEXT_COURSE, $this->courseid);
        $studs = get_role_users(array(5), $context);
	if( !$studs ) $studs = array();
        $students = array();
        foreach( $studs as $s ) {
            $students[$s->id] = new Student( $s );
        }
        return $students;
    }

    public function getEvents() {
        global $DB;
        $condition = array( "courseid" => $this->courseid );
        $temp = $DB->get_records( "event", $condition );
        if( is_array( $temp ) )
            usort( $temp, "cmp_raw_event_date" );
        else 
            $temp = array();
        $output = array();
        foreach( $temp as $event ) {
            if( $event->timestart > $this->course_start )
                array_push($output, $event);
        }
        return $output;
    }

    public function getEnabledEvents() {
        $enabled = array();
        $events = $this->getEvents();
        foreach( $events as $event ) {
            $temp = Event::retrieve( $event->id );
            if( $temp != null )
                array_push( $enabled, $temp );
        }
        return $enabled;
    }

    public static function getData( $id ) {
        global $DB;
        $data = $DB->get_records( "course", 
                                  array( "id" => $id ) );
        sort( $data );
        return $data[0];
    }

    public function getReport() {
        $sdk = $this->getSDK();
        $events = $this->getEnabledEvents();
        $ids = array();
        foreach( $events as $e ) {
            array_push( $ids, $e->getMysmRef() );
        }
        return $sdk->api( "/report/me/objects", "GET", array( "ids" => $ids ) );
    }

}

?>
