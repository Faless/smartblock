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

// Always load moodle config
global $CFG;
if( $CFG == null ) {
    $moodleArr = explode( "/blocks/", 
                          $_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'] );
    $moodleDir = "";
    for ( $i = 0; $i < count($moodleArr)-1; $i++ ) {
        $moodleDir .= $moodleArr[$i];
    }
    include_once $moodleDir."/config.php";
    require_once($CFG->dirroot.'/lib/moodlelib.php');
}

include_once 'utils.php';
include_once 'sdk/MysmSDK.php';
include_once 'class/User.php';
include_once 'class/Course.php';
include_once 'class/Student.php';
include_once 'class/Professor.php';
include_once 'class/Event.php';
include_once 'class/Report.php';
include_once 'class/CourseReport.php';
include_once 'class/GlobalReport.php';

global $DB;
if( $DB == null ) {
    include_once 'class/DbEmulation.php';
    $DB = new DbEmulation();
}

function cmp_raw_event_date($a, $b)
{
    $aVal = $a->timestart + $a->timeduration;
    $bVal = $b->timestart + $b->timeduration;
    if ($aVal == $bVal) {
        return 0;
    }
    return ($aVal > $bVal) ? -1 : 1;
}

function csv_esc( $string ) {
    return '"'.str_replace( '"', '""', $string ).'"';
}

function js_esc( $string ) {
    return "'".str_replace( "'", "\'", $string )."'";
}

?>
