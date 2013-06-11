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

include_once 'include/basic.inc.php';

require_login();

if( !array_key_exists( 'event', $_GET ) )
    die( get_string( "error_invalid_event",
                     "block_smartblock" ) );

global $USER;
$student = Student::retrieve( $USER->id );
$event = Event::retrieve( $_GET['event'] );
if( $student == null || $event == null ||
    !$student->isEnabled( $_GET['event'] ) )
    die( get_string( "error_invalid_event",
                     "block_smartblock" ) );


try {
    $hash = $event->createHash( $USER->id );
    header( "refresh:1;url=https://www.mysmark.com/embed.php?id=".
            $event->getMysmRef()."&hash=".$hash );
    echo get_string( "student_grant_vote", "block_smartblock" );
} catch( Exception $e ) {
    die( $e->getMessage() );
}
?>
