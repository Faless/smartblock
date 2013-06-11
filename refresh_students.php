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

include_once "include/basic.inc.php";

require_login();

$MESSAGE = "";

if( !isset($_GET['event']) )
    die( json_encode( get_string( "error_invalid_event",
                                  "block_smartblock" ) ) );

$event = Event::retrieve( $_GET['event'] );

if( $event == null )
    die( json_encode( get_string( "error_invalid_event",
                                  "block_smartblock" ) ) );

$edata = $event->getData();

$_GET['course'] = $edata->courseid;

$course = Course::retrieve( $edata->courseid );

if( !$course || !User::isProfessor( null, $edata->courseid ) )
    die( json_encode( get_string( "error_not_authorized", 
                                  "block_smartblock" ) ) );

$students = $course->getStudents();
$slen = count($students);
$json = array();
if($slen > 0)
  $json = array_fill(0, $slen, null);
$i = 0;
foreach($students as $stud) {
    $sdata = $stud->getData();
    $json[$i] = array(
                      "id" => $sdata->id,
                      "name" => $sdata->firstname." ".$sdata->lastname,
                      "enrolled" => $stud->getEnrollmentDate($edata->courseid),
                      "enabled" => $stud->isEnabled($edata->id)
                      );
    $i++;
}

echo json_encode( $json );

?>
