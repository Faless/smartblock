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

$format = "EU";
if( array_key_exists( 'format', $_GET ) && $_GET['format'] == "US" ) {
    $format = "US";
}

require_login();

if( !array_key_exists( 'course', $_GET ) )
    die( get_string( "error_invalid_course",
                     "block_smartblock" ) );

$course = Course::retrieve( $_GET['course'] );

if( $course == null )
    die( get_string( "error_invalid_course",
                     "block_smartblock" ) );

if( !User::isProfessor( null, $_GET['course'] ) )
    die( get_string( "error_not_authorized", "block_smartblock" ) );

try {
    $MyReport = new CourseReport($course, $format);
} catch( Exception $e ) {
    die( $e->getMessage() );
}

if( array_key_exists('type', $_GET) && $_GET['type'] == "csv" ) {
    $MyReport->renderCSV();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css"
      href="css/report.css" media="screen"/>
<title><?php echo get_string( "report_title", "block_smartblock" ) ?></title>
<script type="text/javascript" src="js/report.js"></script>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/highcharts.js"></script>
</head>
<body>
<script type="text/javascript">
<?php
    $MyReport->renderEventGraphs();
?>
</script>
<h1><?php
echo get_string( "report_h1", "block_smartblock" )
    ." - ".Course::getData( $_GET['course'] )->fullname;
?> (<a href="report.php?course=<?php
echo $_GET['course'] ?>&type=csv">csv</a> / <a href="report.php?course=<?php
echo $_GET['course'] ?>&type=csv&format=US">US format</a>) <a href="javascript:void(0)" 
  onclick="window.close()" style="font-size: 10px"><?php
echo get_string('close','block_smartblock');
?></a></h1>

<!-- Tabs -->
<div id="report_tabs">
<a id="event_graph_tab" href="#event_graph"><?php
echo get_string( "report_event_graph", "block_smartblock" )
?></a>
<a id="event_table_tab" href="#event_table"><?php
echo get_string( "report_event_table", "block_smartblock" )
?></a>
<a id="student_table_tab" href="#student_table"><?php
echo get_string( "report_student_table", "block_smartblock" )
?></a>
</div>
    <?php ini_set("display_errors", true);
    //echo phpinfo() 
?>
<!-- Event Table -->
<div id="report_event_table">
<h2><?php
    echo get_string( "report_event_table", "block_smartblock" )
?></h2>
<?php
    $MyReport->renderEventTable();
?>
</div>

<!-- Event Graph -->
<div id="report_event_graph">
<h2><?php
echo get_string( "report_event_graph", "block_smartblock" )
?>
</h2>
<div id="report_event_graph_parm">
</div>
<div id="report_event_graph_ismark">
</div>
<div id="report_event_graph_both">
</div>
</div>

<!-- Student Table -->
<div id="report_student_table">
<h2><?php
echo get_string( "report_student_table", "block_smartblock" )
?></h2>
<?php
    $MyReport->renderStudentTable();
?>
</div>
</body>
</html>
