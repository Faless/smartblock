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

// Check permissions
if( !isset($_GET['course']) )
    die( get_string( "error_invalid_course", "block_smartblock" ) );
$course = Course::retrieve( $_GET['course'] );
if( !User::isProfessor( null, $_GET['course'] ) ||
    $course == null )
    die( get_string( "error_not_authorized", "block_smartblock" ) );

$CConfig = $course->getConfig();
if( array_key_exists("getParam", $_GET) ) {
    if( $CConfig->params_enable ) {
        // We'll be outputting a JSON
        header('Content-type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="params.json"');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        echo json_encode(array( 
                               "params_name" => $CConfig->params_name, 
                               "params_label" => $CConfig->params_label,
                               "params_scale" => $CConfig->params_scale ));
        die;
    }
}

// Storage for error message
$MESSAGE = "";

// Get all events config
$events = $course->getEvents();
$configs = array();
foreach( $events as $event ) {
    $e = Event::retrieve( $event->id );
    if( $e != null ) {
        $configs[$event->id] = $e->getConfig();
    } else {
        $configs[$event->id] = Event::getEmptyConfig($event->id);
    }
}
// Parse post config
if( array_key_exists( "event", $_POST ) &&
    is_array( $_POST ) ) {
    
    $filter = array(
                    "enable" => array( "filter" => FILTER_VALIDATE_BOOLEAN ),
                    "end_policy" => array( "filter" => FILTER_VALIDATE_INT ),
                    "end_hour" => array( "filter" => FILTER_VALIDATE_INT )
                    );
    
    foreach( $_POST['event'] as $key => $data ) {
        $data = filter_var_array( $data, $filter );
        $data['eventid'] = intval($key);
        $config = null;
        try {
            $config = Event::validateConfig( $data );
            Event::manage( $config );
        } catch( Exception $e ) {
            $config = (object) $data;
            $config->error = true;
            $MESSAGE .= $e->getMessage()."<br>";
        }
        $configs[$key] = $config;
    }
    
}

// Start html output
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<title><?php
echo get_string('manage_title','block_smartblock');
?></title>
<script type="text/javascript">
function toggle_row(event) {
     var that = event.srcElement ? event.srcElement : event.target;
     var name = that.getAttribute( "name" );
     var enabled = that.checked;
     var policy = true;
     var row = document.getElementById( name.replace( "[enable]", "" ) );
     if( row ) {
         var elems = row.getElementsByTagName( "input" );
         var length = elems.length;
         for (var i = 0; i < length; ++i)
             {
                 if( enabled ) elems[i].removeAttribute( "disabled" );
                 else if( name != elems[i].getAttribute('name') )
                     elems[i].setAttribute( "disabled", "disabled" );
                 if( elems[i].type == "radio" &&
                     elems[i].checked == true &&
                     elems[i].value == "null" ) {
                     policy = false;
                 }
                 if( elems[i].getAttribute("name") == 
                     name.replace( "[enable]", "[end_hour]" ) &&
                     policy == false &&
                     enabled == true) {
                     elems[i].setAttribute( "disabled" );
                 }
             }
     }
}

function toggle_hour( event ) {
    var that = event.srcElement ? event.srcElement : event.target;
    var name = that.getAttribute( "name" );
    var row = document.getElementById( name.replace( "[end_policy]",
                                                     "[end_hour]" ) );
    if( that.value == "null" && that.checked ) {
        row.setAttribute( "disabled", "disabled" );
        row.value = "";
    } else if( that.checked ) {
        row.removeAttribute( "disabled" );
    }
}
</script>
</head>
<body>
<h2><?php
echo get_string('manage_h1','block_smartblock')
    ." - ".Course::getData( $_GET['course'] )->fullname;
?> <a href="javascript:void(0)" onclick="window.close()"
  style="font-size: 10px"><?php
echo get_string('close','block_smartblock');
?></a></h2>
<p>
<h3><?php
echo get_string('manage_events','block_smartblock');
?></h3>
<?php
if( $CConfig->params_enable ) {
    echo '<a href="form_manage.php?course='.$_GET['course'].'&getParam=1">'.
        get_string("manage_get_param_config", "block_smartblock").
        "</a>";
}

if( $MESSAGE != null )
    echo "<p>".$MESSAGE."</p>";
?>
<form method="POST" accept-charset="UTF-8">
<table cellpadding="10px" border="1px">
<thead>
<tr><th rowspan="2"><?php
echo get_string('manage_event_name','block_smartblock');
?></th><th rowspan="2"><?php
echo get_string('manage_event_start','block_smartblock');
?></th><th rowspan="2"><?php
echo get_string('manage_event_end','block_smartblock');
?></th>
    <th rowspan="2"><?php
echo get_string('manage_event_enabled','block_smartblock');
?></th><th colspan="4"><?php
echo get_string('manage_policy','block_smartblock');
?></th>
    <th rowspan="2"><?php
echo get_string('manage_policy_hours_number','block_smartblock');
?></th>
</tr>
<tr><th><?php
echo get_string('manage_policy_default','block_smartblock');
?></th><th><?php
echo get_string('manage_policy_same','block_smartblock');
?></th><th><?php
echo get_string('manage_policy_after','block_smartblock');
?></th><th><?php
echo get_string('manage_policy_hours_count','block_smartblock');
?></th></tr>
</thead>
<tbody>
<?php
foreach( $events as $event ) {
    $id = $event->id;
    $disabled = $configs[$id]->enable ? "" : ' disabled="disabled"'; 
    $red = $configs[$id]->error ? 'style="background-color: red;"' : "";
?>
<tr id="event[<?php echo $id ?>]" <?php echo $red ?>>
<td><?php
    if( ($configs[$id]->enable && $red == "") ||
        (!$configs[$id]->enable && $red != "")) {
        echo '<a target="_blank" href="'.$CFG->wwwroot.
             '/blocks/smartblock/form_event.php?event='.$id.'">'.
        $event->name.'</a>';
    } else echo $event->name;
?></td>
<td><?php 
    echo date("Y-m-d H:i", $event->timestart );
?></td>
<td><?php 
    echo date("Y-m-d H:i", $event->timestart  + $event->timeduration );
?></td>
<td>
<input type="hidden" name="event[<?php echo $id?>][enable]" value="0" />
<input type="checkbox" name="event[<?php echo $id ?>][enable]"<?php 
if( $configs[$id]->enable ) echo ' checked="checked"'; ?> 
  onclick="toggle_row(event)" value="1">
</td>
<td>
<input type="radio" value="null" 
    name="event[<?php echo $id ?>][end_policy]"<?php
if( $configs[$id]->end_policy === null ) echo ' checked="checked"';
echo $disabled ?> onchange="toggle_hour(event);">
</td>
<td>
<input type="radio" value="1" name="event[<?php echo $id ?>][end_policy]"<?php 
if( $configs[$id]->end_policy === 1 ) echo ' checked="checked"';
echo $disabled ?> onchange="toggle_hour(event);">
</td>
<td>
<input type="radio" value="2" name="event[<?php echo $id ?>][end_policy]"<?php 
if( $configs[$id]->end_policy === 2 ) echo ' checked="checked"';
echo $disabled ?> onchange="toggle_hour(event);">
</td>
<td>
<input type="radio" value="0" name="event[<?php echo $id ?>][end_policy]"<?php 
if( $configs[$id]->end_policy === 0 )
    echo ' checked="checked"';
echo $disabled ?> onchange="toggle_hour(event);">
</td>
<td>
<input type="numeric" name="event[<?php echo $id ?>][end_hour]" value="<?php
if( $configs[$id]->end_hour !== null ) echo $configs[$id]->end_hour;
?>" <?php 
    if( !$configs[$id]->enable ) echo $disabled;
    else if( $configs[$id]->end_policy === NULL ) echo 'disabled="disabled"';
?> id="event[<?php echo $id ?>][end_hour]">
</td>
</tr><?php } ?>
</tbody>
</table>
<br>
<input type="submit" value="<?php
echo get_string('manage_submit','block_smartblock');
?>"</input>
</form>
</p>
</body>
</html>
