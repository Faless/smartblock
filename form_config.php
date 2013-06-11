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

if(count($_FILES) > 0 && 
   array_key_exists("param_config_file", $_FILES) &&
   file_exists($_FILES["param_config_file"]['tmp_name']) ) {
    $content = file_get_contents($_FILES["param_config_file"]['tmp_name']);
    $json = json_decode( $content, true );
    if( $json != null &&
        array_key_exists("params_name", $json) &&
        array_key_exists("params_label", $json) &&
        array_key_exists("params_scale", $json) ) {
        
        $_POST["params_enable"] = true;
        $_POST["params_name"] = $json['params_name'];
        $_POST["params_label"] = $json['params_label'];
        $_POST["params_scale"] = $json['params_scale'];
    }
}

$MESSAGE = "";

if( !isset($_GET['course']) )
    die( get_string('error_invalid_course','block_smartblock') );

if( !User::isProfessor( null, $_GET['course'] ) )
    die( get_string( "error_not_authorized", "block_smartblock" ) );

$course = Course::retrieve( $_GET['course'] );
$config = new StdClass();
// User is submitting
if( count($_POST) != 0 ) {
    $filter = array(
                    "client_id" => array( "filter" => FILTER_UNSAFE_RAW ),
                    "client_secret" => array( "filter" => 
                                              FILTER_UNSAFE_RAW ),
                    "end_policy" => array( "filter" => FILTER_VALIDATE_INT ),
                    "end_hour" => array( "filter" => FILTER_VALIDATE_INT ),
                    "params_enable" => array( "filter" => 
                                              FILTER_VALIDATE_BOOLEAN ),
                    "course_start" => array( "filter" => FILTER_UNSAFE_RAW )
                    );
    $array = filter_var_array( $_POST, $filter );
    $array['params_scale'] = array_key_exists('params_scale', $_POST) && 
        is_array($_POST['params_scale']) ? $_POST['params_scale'] : array();
    $array['params_label'] = array_key_exists('params_label', $_POST) && 
        is_array($_POST['params_label']) ? $_POST['params_label'] : array();
    $array['params_name'] = array_key_exists('params_name', $_POST) && 
        is_array($_POST['params_name']) ? $_POST['params_name'] : array();
    try {
        $config = Course::validateConfig( $array );
        $config->courseid = $_GET['course'];
        $temp = clone $config;
        $course = Course::manage( $temp, $_GET['course'] );
        $MESSAGE = get_string('configure_configured','block_smartblock');
    } catch ( Exception $e ) {
        $MESSAGE = $e->getMessage()."<br>";
        $config = (object) $array;
    }
}
// User is not submitting but the course was already configured
else if( $course != null ) {
    $config = $course->getConfig();
}
// User is not submitting and the course was not configured
else {
    $config = Course::getEmptyConfig();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css"
      href="css/ui-lightness/jquery-ui-1.10.2.custom.min.css" media="screen"/>
<link rel="stylesheet" type="text/css" 
      href="css/form_config.css" media="screen"/>
<title><?php
echo get_string('configure_title','block_smartblock');
?></title>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/jquery-ui-1.10.2.custom.min.js"></script>
<script type="text/javascript" src="js/config.js"></script>
<script>
$(function() {
$( "#course_start_input" ).datepicker({ dateFormat: 'yy-mm-dd' });
$( document ).tooltip();
});
</script>
</head>
<body>
<h3><?php
echo get_string('configure_h1','block_smartblock')
    ." - ".Course::getData( $_GET['course'] )->fullname;
?> <a href="javascript:void(0)" onclick="window.close()"
  style="font-size: 10px"><?php
echo get_string('close','block_smartblock');
?></a></h3>
<p>
<?php echo $MESSAGE ?>
<form method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<table id="global_config">
<tbody>
<tr>
  <td class="global_config_key">
      <label for="client_id_input">client_id <?php 
      renderTipImg("tooltip_client_cred")?></label>
  </td>
  <td class="global_config_value">
    <input value="<?php echo $config->client_id ?>" type="text" 
      id="client_id_input" name="client_id">
  </td>
</tr>
<tr>
  <td class="global_config_key">
    <label for="client_secret_input">client_secret <?php
      renderTipImg("tooltip_client_cred")?></label>
  <td class="global_config_value">
    <input value="<?php echo $config->client_secret ?>" type="text" 
      id="client_secret_input" name="client_secret">
  </td>
</tr>
<tr>
  <td class="global_config_key">
    <label><?php
      echo get_string('configure_policy','block_smartblock');
    ?></label>
  </td>
  <td class="global_config_value timeframe_value">
    <input <?php if( $config->end_policy == 1 ) echo "checked='checked'" ?> 
      value="1" type="radio" 
      name="end_policy" id="end_policy_day_radio">
    <label for="end_policy_day_radio"><?php
      echo get_string('configure_policy_same','block_smartblock');
    ?></label>
    <br>
    <input <?php if( $config->end_policy == 2 ) echo "checked='checked'" ?>  
      value="2" type="radio"
      name="end_policy" id="end_policy_nextday_radio">
    <label for="end_policy_nextday_radio"><?php
      echo get_string('configure_policy_after','block_smartblock');
    ?></label>
    <br>
    <input <?php if( $config->end_policy == 0 ) echo "checked='checked'" ?>
      value="0" type="radio"
      name="end_policy" id="end_policy_hours_radio">
    <label for="end_policy_hours_radio"><?php
      echo get_string('configure_policy_hours_count','block_smartblock');
    ?></label>
    <br>
  </td>
</tr>
<tr>
  <td class="global_config_key">
    <label for="end_hour_input"><?php
      echo get_string('configure_policy_hours_number','block_smartblock');
    ?></label>
  </td>
  <td class="global_config_value">
    <input value="<?php echo $config->end_hour ?>" type="numeric" 
      name="end_hour" id="end_hour_input">
  </td>
</tr>
<tr>
  <td class="global_config_key">
    <label for="course_start_input"><?php
      echo get_string('configure_couser_start', 'block_smartblock');
      renderTipImg("tooltip_course_start");
    ?></label>
  </td>
  <td class="global_config_value">
    <input value="<?php echo is_numeric($config->course_start) ? 
                             date("Y-m-d", $config->course_start) :
                             date("Y-m-d", 0) ?>"
      type="text" name="course_start" id="course_start_input">
  </td>
</tr>
</tbody>
</table>
</p>
<h2><?php
echo get_string('configure_params_title','block_smartblock');
?></h2>
<p>
<label for="param_config_file"><?php
echo get_string("configure_param_config_file", "block_smartblock");
renderTipImg("tooltip_param_config_file");
?></label>
<input type="file" name="param_config_file" id="param_config_file">
</p>
<p>
<input id="params_enable" type="checkbox" value="1" name="params_enable"
    onchange="toggleParams(this)" <?php 
if($config->params_enable) echo 'checked="checked"'; ?>>
<label for="params_enable"><?php
echo get_string('configure_params_enable','block_smartblock');
?></label></p>
<div id="params_div" style="<?php 
if(!$config->params_enable) echo 'display:none'; ?>">
<table id="params_table_1">
<thead>
<tr><th><?php
echo get_string('configure_params_scale','block_smartblock');
renderTipImg("tooltip_param_scale");
?></th>
<th><?php
echo get_string('configure_params_label', 'block_smartblock');
renderTipImg("tooltip_param_label");
?></th>
</thead>
<tbody>
<?php for ($i = 0; $i < count( $config->params_scale ); $i++) { ?>    
<tr>
<td><input type="text" name="params_scale[]" value="<?php
if(isset($config->params_scale[$i])) echo $config->params_scale[$i];
?>" onkeydown="return numbersOnly(event)"></td>
<td><input type="text" name="params_label[]" value="<?php
if(isset($config->params_scale[$i])) echo $config->params_label[$i];
?>"></td>
<td><a href="javascript:void(0)" onclick="delValue(this)">-</a></td>
</tr>
<?php } ?>
<?php if( count( $config->params_scale ) == 0 ) { ?>
<tr>
    <td><input type="text" name="params_scale[]" value="" 
     onkeydown="return numbersOnly(event)"></td>
     <td><input type="text" name="params_label[]" value=""></td>
     <td><a href="javascript:void(0)" onclick="delValue(this)">-</a></td>
</tr>
<?php } ?>
<tr id="fakeValue" style="display:none;">
    <td><input type="text" name="_params_scale[]" value="" 
      onkeydown="return numbersOnly(event)"></td>
    <td><input type="text" name="_params_label[]" value=""></td>
    <td><a href="javascript:void(0)" onclick="delValue(this)">-</a></td>
</tr>
<tr><td colspan="2" style="text-align: center;"><a
    href="javascript:void(0)" 
    onclick="addValue(document.getElementById('fakeValue'))"><?php
    echo get_string('configure_params_value_addmore', 'block_smartblock'); 
?></a></td></tr>
</tbody>
</table>
<table id="params_table_2">
<thead>
<tr><th><?php
echo get_string('configure_params_name','block_smartblock');
renderTipImg("tooltip_params_name");
?></th></tr>
</thead>
<tbody>
<?php for ($i = 0; $i < count( $config->params_name ); $i++) { ?> 
<tr>
    <td><input type="text" name="params_name[]" value="<?php
echo $config->params_name[$i];
?>"></td>
    <td><a href="javascript:void(0)" onclick="delParam(this)">-</a></td>
</tr>
<?php } ?>
<?php if( count( $config->params_name ) == 0 ) { ?>
<tr>
    <td><input type="text" name="params_name[]" value=""></td>
    <td><a href="javascript:void(0)" onclick="delParam(this)">-</a></td>
</tr>
<?php } ?>
<tr id="fakeParam" style="display:none;">
    <td><input type="text" name="_params_name[]" value=""></td>
    <td><a href="javascript:void(0)" onclick="delParam(this)">-</a></td>
</tr>
<tr><td colspan="2" style="text-align: center;"><a
    href="javascript:void(0)" 
    onclick="addParam(document.getElementById('fakeParam'))"><?php
    echo get_string('configure_params_name_addmore', 'block_smartblock'); 
?></a></td></tr>
</tbody>
</table>
</div>
<input type="submit" value="<?php
echo get_string('configure_submit','block_smartblock');
?>">
</form>
</body>
</html>
