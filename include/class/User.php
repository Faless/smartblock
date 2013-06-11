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

abstract class User {
    
    static function isProfessor( $user = null, $course = null ) {
        if( $course === null ) {
            global $COURSE;
            $course = $COURSE->id;
        }
        $context = get_context_instance(CONTEXT_COURSE, $course, false);
        if( !$context ) return FALSE;
        if ( has_capability('block/smartblock:edit', $context, $user) ) {
            return TRUE;
        }
        return FALSE;
    }

    static function isStudent( $user = null, $course = null ) {
        if( $course === null ) {
            global $COURSE;
            $course = $COURSE->id;
        }
        $context = get_context_instance(CONTEXT_COURSE, $course, false);
        if( !$context ) return FALSE;
        if ( has_capability('block/smartblock:vote', $context, $user) ) {
            return TRUE;
        }
        return FALSE;
    }

    static function isSupervisor( $user = null ) {
        global $SITE;
        $context = get_context_instance(CONTEXT_SYSTEM, 0, false);
        if( !$context ) return FALSE;
        if ( has_capability('block/smartblock:supervise', $context, $user) ) {
            return TRUE;
        }
        return FALSE;
    }

    static function retrieve( $id = null, $course = null ) {
        if( self::isStudent( $id, $course ) ) return new Student();
        if( self::isProfessor( $id, $course ) ) return new Professor();
        return null;
    }
    
}
