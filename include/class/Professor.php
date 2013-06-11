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

class Professor extends User {
    
    protected function __construct() {

    }
    
    static function isStudent( $user = null, $course = null ) {
        return FALSE;
    }

    static function isProfessor( $user = null, $course = null ) {
        return TRUE;
    }
}

?>
