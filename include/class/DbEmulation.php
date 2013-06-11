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

class DbEmulation {

    function __construct()
    {

    }
	
    function get_records( $table, $condition )
    {
	$array = array( $table );
        $this->getArray( $array, $condition );
        return call_user_func_array( "get_records", $array );
    }

    function get_record( $table, $condition )
    {
        $array = array( $table );
        $this->getArray( $array, $condition );
        return call_user_func_array( "get_record", $array );
    }
    
    function update_record( $table, $array )
    {
        $object = (object) $array;
        return update_record( $table, $object );
    }
    
    function get_record_sql( $sql, $array )
    {
        $sql = $this->fix_table_names($sql);
        $sql = $this->emulate_bound_params( $sql, $array );
        $out = get_records_sql( $sql );
        if( is_array( $out ) ){
            sort($out);
            return $out[0];
        }
        return $out;
    }
    
    function get_records_sql( $sql, $array )
    {
        $sql = $this->fix_table_names($sql);
        $sql = $this->emulate_bound_params( $sql, $array );
        $out = get_records_sql( $sql );
        if( is_array( $out ) ) 
            sort($out);
        return $out;
    }
    
    function delete_records_select( $table, $sql, $array=null )
    {
        if( $array !== null )
            $sql = $this->emulate_bound_params( $sql, $array );
        delete_records_select( $table, $sql );
    }
    
    function execute( $sql, $params )
    {
        $sql = $this->fix_table_names($sql);
        $sql = $this->emulate_bound_params( $sql, $params );
        execute_sql( $sql, false );
    }
    
    function insert_record( $table, $array, $return = true, $trash = null )
    {
        $object = (object) $array;
        return insert_record( $table, $object, $return );
    }
    
    private function getArray( &$out, $in )
    {
        foreach ($in as $key => $value) {
            $out[] = $key;
            $out[] = $value;
        }
        return $out;
    }
    
    private function emulate_bound_params( $sql, $params )
    {
        if (empty($params)) {
            return $sql;
        }
        /// ok, we have verified sql statement with ? and correct number of params
        $return = strtok($sql, '?');
        foreach ($params as $param) {
            if ( is_bool( $param ) ) {
                $return .= (int)$param;
            } else if ( $param == NULL ) {
                $return .= 'NULL';
            } else if ( is_int( $param ) ) {
                $return .= $param;
            } else if ( is_float( $param ) ) {
                $return .= $param;
            } else {
            	$param = addslashes( $param );
                $return .= "'$param'";
            }
            $return .= strtok('?');
        }
        return $return;
    }
    
    private function fix_table_names($sql) {
    	global $CFG;
        return preg_replace('/\{([a-z][a-z0-9_]*)\}/', $CFG->prefix.'$1', $sql);
    }

}

?>
