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

include_once( 'include/basic.inc.php' );

require_login();

if(!User::isSupervisor())
    die(get_string('error_not_authorized',
                   'block_smartblock'));

$format = "EU";
if( array_key_exists( 'format', $_GET ) && $_GET['format'] == "US" ) {
    $format = "US";
}

$raw_courses = $DB->get_records("block_smartblock_course", array());
try {
    $globalReport = new GlobalReport($raw_courses, $format);
} catch (Exception $e) {
    die($e->getMessage());
}

if( array_key_exists('type', $_GET) && $_GET['type'] == "csv" ) {
    $globalReport->renderCSV();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<title><?php echo get_string( "supervise_title", "block_smartblock" ) ?></title>
</head>
<body>
<h1><?php echo get_string("supervise_title", "block_smartblock");
?> (<a href="supervise.php?type=csv">csv</a> / <a href="supervise.php?type=csv&format=US">US format</a>) <a href="javascript:void(0)" 
  onclick="window.close()" style="font-size: 10px"><?php
echo get_string('close','block_smartblock');
?></a></h1>
<p><?php 
echo get_string("supervise_course_count", "block_smartblock");?>: <?php
echo count($globalReport->getTableData());
?></p>
<?php
echo $globalReport->renderTable();
?>
</body>
</html>
