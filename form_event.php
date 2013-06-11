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
    die( get_string( "error_invalid_event", "block_smartblock" ) );

$event = Event::retrieve( $_GET['event'] );

if( $event == null )
    die( get_string( "error_invalid_event", "block_smartblock" ) );

$edata = $event->getData();

$_GET['course'] = $edata->courseid;

$course = Course::retrieve( $edata->courseid );

if( !$course || !User::isProfessor( null, $edata->courseid ) )
    die( get_string( "error_not_authorized", "block_smartblock" ) );

$students = $course->getStudents();
$errors = array();
if( array_key_exists( 'students', $_POST ) ) {
    foreach( $students as $key => $student ) {
        try {
            if( array_key_exists( $key, $_POST['students'] ) ) {
                $bool = filter_var( $_POST['students'][$key], 
                                    FILTER_VALIDATE_BOOLEAN );
                if( $bool )
                    $student->enable( $_GET['event'] );
                else
                    $student->disable( $_GET['event'] );
            }
            else
                $student->disable( $_GET['event'] );
        } catch ( Exception $e ) {
            $MESSAGE .= $e->getMessage()."<br>";
            $errors[$key] = true;
        }
    }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<title><?php echo get_string("event_title", "block_smartblock") ?></title>
<script type="text/javascript" src="js/event.js"></script>
<script type="text/javascript">
function select_all() {
    var input = document.getElementsByTagName( "input" );
    for (var i = 0; i < input.length; i++) {
        if( input[i].getAttribute( "type" ) == "checkbox" &&
            input[i].checked == false)
            input[i].click();
    }
}

function select_none() {
    var input = document.getElementsByTagName( "input" );
    for (var i = 0; i < input.length; i++) {
        if( input[i].getAttribute( "type" ) == "checkbox"  &&
            input[i].checked == true)
            input[i].click();
    }
}
</script>
<style>
td.changed {
    background-color: yellow;
  }
</style>
</head>
<body>
<h1><?php
echo get_string("event_h1", "block_smartblock")
    ." - ".Course::getData( $_GET['course'] )->fullname;
?> <a href="javascript:void(0)" onclick="window.close()"
  style="font-size: 10px"><?php
echo get_string('close','block_smartblock');
?></a></h1>
<?php
if( $MESSAGE != "" ) echo "<p style='color: red;'>".$MESSAGE."<br>";
$startdate = $edata->timestart;
$enddate = $edata->timestart + $edata->timeduration;
?>
<p><?php
echo get_string('event_name','block_smartblock')." ".$event->getData()->name;
?></p>
<p><?php
echo get_string('event_start','block_smartblock')." ".
       date( "Y-m-d H:i", $startdate );
?></p>
<p><?php
echo get_string('event_end','block_smartblock')." ".
       date( "Y-m-d H:i", $enddate );
?></p>
<form method="POST" id="EVENT_FORM" accept-charset="UTF-8">
<input type="hidden" name="students[]" value=""/>
<table border="1px" cellpadding="10px">
<thead>
<tr><th><?php
echo get_string('event_student_name','block_smartblock');
?></th><th><?php
echo get_string('event_student_enroll_date','block_smartblock');
?></th>
<th><?php
echo get_string('event_student_enabled','block_smartblock');
?><br><a href="javascript:void(0)"
    onclick="select_all()"><?php
echo get_string('event_student_enable_all','block_smartblock');
?></a> <a href="javascript:void(0)"
    onclick="select_none()"><?php
echo get_string('event_student_enable_none','block_smartblock');
?></a></th></tr>
</thead>
<tbody>
<?php
foreach( $students as $key => $student ) {
    $sdata = $student->getData();
    $red = array_key_exists( $key, $errors ) ?
           " style='background-color: red;'" : "";
?>
<tr<?php echo $red; ?>><td><?php 
echo $sdata->firstname." ".$sdata->lastname; ?></td>
<td><?php
$enrolled = $student->getEnrollmentDate( $edata->courseid );
echo date( "Y-m-d H:i", $enrolled );
?></td>
<td style="text-align: center;"><?php 
    $checked = $student->isEnabled( $edata->id ) ? 'checked="checked"' : "";?>
<input type="checkbox" name="students[<?php echo $sdata->id ?>]" <?php
    echo $checked ?> value="1"/></td></tr>
<?php } ?>
</tbody>
</table>
<br>
<input type="submit" value="<?php
echo get_string('event_submit','block_smartblock');
?>"/>
</form>
</body>
</html>
