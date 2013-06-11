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

class Report {
    
    // Decimal and thousand character
    private $format = "EU";
    private $dc = ",";
    private $tc = ".";
    
    function __construct($format = "EU") {
        if( $format == "US" ) {
            $this->dc = ".";
            $this->tc = ",";
        }
    }
    
    protected function _format($integer, $for_js = false) {
        if( $for_js )
            return number_format($integer, 2, ".", "");
        return is_numeric($integer) ? 
            number_format($integer, 2, $this->dc, $this->tc) :
            $integer;
    }

}

?>