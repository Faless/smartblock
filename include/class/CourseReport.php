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

class CourseReport extends Report {

    private $sdk_report = array();
    private $course = null;

    private $student_table = array();
    private $event_table = array();
    private $params = array();
    private $params_avg = array();
    private $enabled_event_count = 0;
    private $event_count = 0;
    private $student_count = 0;
    private $params_max = 0;
    private $params_min = 0;
    
    public function __construct($course, $format = "EU") {
        parent::__construct($format);
        $this->course = $course;
        $this->sdk_report = $course->getReport();
        $this->_parse_report();
    }
    
    private function _parse_report() {
        $events = $this->course->getEnabledEvents();
        $students = $this->course->getStudents();
        
        $this->student_count = count( $students );
        $this->event_count = count( $this->course->getEvents() );
        $this->enabled_event_count = count( $events );

        $eventTable = array();
        $studentTable = array();

        // Prepare student table
        foreach( $students as $student ) {
            $temp = new StdClass();
            $temp->data = $student->getData();
            $temp->enabled = 0;
            $temp->voted = 0;
            $temp->enabledOnTotal = 0;
            $temp->votedOnEnabled = 0;
            $studentTable[$temp->data->id] = $temp;
        }
        unset($temp);
        
        // Prepare parameters list
        $params = array();
        $temp = $this->course->getParamsJSON();
        if( $temp != null ) {
            foreach( $temp['names'] as $name ) {
                $params[$name] = "-";
            }
            $this->params_min = $temp['values'][0];
            $this->params_max = $temp['values'][count($temp['values'])-1];
        }
        unset($temp);
        
        // Populate event table
        foreach( $events as $event ) {
            $temp = new StdClass();
            $temp->data = $event->getData();
            $enabledStudents = $event->getEnabledStudents();
            $temp->enabledStudents = count( $enabledStudents );
            $temp->votingStudents = 0;
            $temp->value = "-";
            $temp->enabledStudentsOnEnrolled = 0;
            $temp->votingStudentsOnEnabled = 0;
            $temp->votingStudentsOnEnrolled = 0;
            $temp->params = $params;
            $mysmRef = $event->getMysmRef();
            $temp->link = "https://www.mysmark.com/business.php"
                ."?view=stats&account=&event=".$mysmRef;
            if( array_key_exists( $mysmRef, $this->sdk_report ) &&
                array_key_exists( "voted_hashs", $this->sdk_report[$mysmRef] ) ) {
                $currReport = $this->sdk_report[$mysmRef];
                foreach( $enabledStudents as $student ) {
                    $sid = $student->getData()->id;
                    if( array_key_exists( $sid, $studentTable ) ) {
                        $studentTable[$sid]->enabled += 1;
                        if( in_array( $event->getHash( $sid ), 
                                      $currReport["voted_hashs"] ) ) {
                            $temp->votingStudents += 1;
                            $studentTable[$sid]->voted += 1;
                        }
                    }
                }
                $temp->value = $currReport['value'] !== null ?
                    $currReport['value'] : "-";
                $temp->enabledStudentsOnEnrolled = $this->student_count > 0 ?
                    round($temp->enabledStudents / $this->student_count * 100) :
                    0;
                $temp->votingStudentsOnEnabled = $temp->enabledStudents > 0 ?
                    round($temp->votingStudents / $temp->enabledStudents * 100 ):
                    0;
                $temp->votingStudentsOnEnrolled = $this->student_count > 0 ?
                    round($temp->votingStudents / $this->student_count * 100) :
                    0;
                // Setup parameters values
                if( array_key_exists( 'parameters', $currReport ) &&
                    is_array($currReport['parameters']) ) 
                    foreach( $temp->params as $key => $value ) {
                        if( array_key_exists( $key, $currReport['parameters'] ) 
                            && is_numeric($currReport['parameters'][$key]) ) {
                            
                            $val = floatval($currReport['parameters'][$key]);
                            $temp->params[$key] = $val;
                        }
                    }
            }
            $eventTable[$temp->data->id] = $temp;
        }

        // Calculate avarage values
        $AVG = array();
        foreach( $params as $p => $v ) {
            $AVG[$p] = 0;
            $i = 0;
            foreach( $eventTable as $eventRow ) {
                $temp = $eventRow->params[$p];
                if( is_numeric($temp) ) {
                    $AVG[$p] += $temp;
                    $i++;
                }
            }
            $AVG[$p] = $AVG[$p] > 0 && $i > 0 ? $AVG[$p]/$i : 0;
        }

        $this->event_table = $eventTable;
        $this->params = $params;
        $this->student_table = $studentTable;
        $this->params_avg = $AVG;
    }

    function renderEventTable() {
        ?>
        <table cellpadding="10px" border="1px">
        <thead>
        <tr>
        <th><?php
            echo get_string( "report_event_name", "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_event_start", "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_event_end", "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_event_enabled_students", 
                             "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_event_enabled_students_on_enrolled",
                             "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_event_voting_students", 
                             "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_event_voting_students_on_enabled",
                             "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_event_voting_students_on_enrolled",
                             "block_smartblock" )
        ?></th>
        <?php 
            foreach( $this->params as $p => $v ) {
                echo "<th>".$p."</th>\n";
            }
        ?><th><?php
            echo get_string( "report_event_value", "block_smartblock" )
        ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $this->event_table as $eventRow ) { 
            $start = $eventRow->data->timestart;
            $end = $eventRow->data->timestart + $eventRow->data->timeduration;
        ?>
        <tr>
        <td><?php 
             echo "<a href='".$eventRow->link."'>".$eventRow->data->name 
             ?></td>
        <td><?php
             echo date("Y-m-d H:i", $start); 
            ?></td>
        <td><?php
             echo date("Y-m-d H:i", $end);
            ?></td>
        <td style="text-align: center;"><?php
             echo $eventRow->enabledStudents
        ?></td>
        <td style="text-align: center;"><?php
             echo $eventRow->enabledStudentsOnEnrolled."%"
        ?></td>
        <td style="text-align: center;"><?php
             echo $eventRow->votingStudents
        ?></td>
        <td style="text-align: center;"><?php 
             echo $eventRow->votingStudentsOnEnabled."%"
        ?></td>
        <td style="text-align: center;"><?php 
             echo $eventRow->votingStudentsOnEnrolled."%"
        ?></td><?php 
             foreach( $this->params as $p => $v ) {
                echo '<td style="text-align: center;">'.
                    $this->_format($eventRow->params[$p])."</td>\n";
             }
        ?>
        <td style="text-align: center;"><?php 
             echo $this->_format($eventRow->value);
        ?></td>
        </tr>
        <?php } ?>
        </tbody>
        </table><?php
    }
    
    function renderStudentTable() {
        ?>
        <table cellpadding="10px" border="1px">
        <thead>
        <tr>
        <th><?php
            echo get_string( "report_student_name", 
                             "block_smartblock" )
         ?></th>
        <th><?php
            echo get_string( "report_student_presence", 
                             "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_student_presence_on_enabled", 
                             "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_student_vote", 
                             "block_smartblock" )
        ?></th>
        <th><?php
            echo get_string( "report_student_vote_on_presence",
                             "block_smartblock" );
        ?></th>
        <th><?php
             echo get_string( "report_student_vote_on_enabled",
                              "block_smartblock" );
        ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
            foreach( $this->student_table as $row ) {
                $row->enabledOnTotal = $this->enabled_event_count > 0 ?
                    round( $row->enabled / $this->enabled_event_count * 100 ) :
                    0;
                $row->votedOnPresence = $row->enabled > 0 ?
                    round( $row->voted / $row->enabled * 100 ) :
                    0;
                $row->votedOnEnabled = count($this->event_table) > 0 ?
                    round( $row->voted / count( $this->event_table ) * 100 ) :
                    0;
        ?>
        <tr>
        <td><?php 
             echo $row->data->firstname." ".$row->data->lastname;
        ?></td>
        <td style="text-align: center;"><?php
             echo $row->enabled 
        ?></td>
        <td style="text-align: center;"><?php
             echo $row->enabledOnTotal."%" 
        ?></td>
        <td style="text-align: center;"><?php
             echo $row->voted
        ?></td>
        <td style="text-align: center;"><?php
             echo $row->votedOnPresence."%"
        ?></td>
        <td style="text-align: center;"><?php
             echo $row->votedOnEnabled."%" 
        ?></td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
        <?php
    }

    function renderEventGraphs() {
        ?>
        $(document).ready(function() {
        <?php if(!empty($this->params)) {?>
        var chart1 = new Highcharts.Chart({
            chart: {
                renderTo: 'report_event_graph_parm',
                type: 'column'
            },
            title: {
                text: <?php 
                    echo js_esc(get_string("report_event_graph_parameters",
                                           "block_smartblock"))
                    ?>
            },
            xAxis: {
                categories: [""]
            },
            yAxis: {
                min: <?php echo $this->params_min ?>,
                max: <?php echo $this->params_max ?>,
                title: {
                    text: <?php
                    echo js_esc(get_string("report_event_graph_parameters",
                                           "block_smartblock")) ?>
                }
            },
            series: [<?php
                     $series = "";
                     foreach($this->params_avg as $p => $v) {
                         $series .= "\n{'name':".js_esc($p).
                             ",'data':[".$this->_format($v,true)."]},";
                     }
                     if( strlen($series) > 0  )
                         echo substr($series, 0, -1)."\n";
                     ?>
                    ],
            tooltip:{ useHTML:true }
        });
        <?php } ?>
        var chart2 = new Highcharts.Chart({
            chart: {
                renderTo: 'report_event_graph_ismark',
                type: 'column'
            },
            title: {
                text: <?php 
                    echo js_esc(get_string("report_event_graph_course",
                                           "block_smartblock")) ?>
            },
            xAxis: {
                categories: [""]
            },
            yAxis: {
                min: 0,
                max: 100,
                title: {
                    text: <?php 
                        echo js_esc(get_string("report_event_graph_course",
                                               "block_smartblock")) ?>
                }
            },
            series: [<?php
                     $series = "";
                     $i = $ival = 0;
                     foreach($this->event_table as $row) {
                         if( is_numeric($row->value) ) {
                             $ival += $row->value;
                             $i += 1;
                         }
                     }
                     $ival = $ival > 0 && $i > 0 ? $ival / $i : 0; 
                     echo "{'name': 'iSmark', 'data': [".
                         $this->_format($ival, true)."]}";
                     if( !empty($this->params) ) {
                         echo ",";
                         $i = $pval = 0;
                         foreach( $this->params_avg as $p => $v ) {
                             if( is_numeric($v) ) {
                                 $pval += $v;
                                 $i += 1;
                             }
                         }
                         $pval = $pval > 0 && $i > 0 ? $pval / $i : 0;
                         $pval = $pval > 0 ? 
                             $pval / ($this->params_max - 
                                      $this->params_min + 1 ) * 100 :
                             0;
                         echo "{'name': '".
                             get_string("configure_params_title",
                                        "block_smartblock").
                             "', 'data': [".
                             $this->_format($pval, true)."]},";
                         
                         echo "{'name': 'iSmark & ".
                             get_string("configure_params_title",
                                        "block_smartblock").
                             "', 'data': [".
                             $this->_format(($pval + $ival) / 2, true)."]},";
                     }
                     ?>
                 ],
            tooltip:{ useHTML:true }
            });
        })

        <?php
    }

    function renderCSV() {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');
        $string = "";
        $string .= csv_esc(get_string("report_event_table",
                                      "block_smartblock"))."\n";
        $string .= csv_esc(get_string("report_event_name",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_event_start",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_event_end",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_event_enabled_students",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_event_enabled_students_on_enrolled",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_event_voting_students",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_event_voting_students_on_enabled",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_event_voting_students_on_enrolled",
                                      "block_smartblock")).",";
        foreach( $this->params as $p => $v ) {
            $string .= csv_esc( $p ).",";
        }
        $string .= csv_esc(get_string("report_event_value",
                                      "block_smartblock"))."\n";
        foreach( $this->event_table as $eventRow ) { 
            $start = $eventRow->data->timestart;
            $end = $eventRow->data->timestart + $eventRow->data->timeduration;
            $string .= csv_esc( $eventRow->data->name.
                                " (".$eventRow->link.")" ).",";
            $string .= csv_esc( date("Y-m-d H:i", $start) ).",";
            $string .= csv_esc( date("Y-m-d H:i", $end ) ).",";
            $string .= csv_esc( $eventRow->enabledStudents ).",";
            $string .= csv_esc( $eventRow->enabledStudentsOnEnrolled."%" ).",";
            $string .= csv_esc( $eventRow->votingStudents ).",";
            $string .= csv_esc( $eventRow->votingStudentsOnEnabled."%" ).",";
            $string .= csv_esc( $eventRow->votingStudentsOnEnrolled."%" ).",";
            foreach( $this->params as $p => $v ) {
                $string .= csv_esc( $eventRow->params[$p] ).",";
            }
            $string .= csv_esc( $eventRow->value )."\n";
        }
        $string .= "\n\n";
        $string .= csv_esc(get_string("report_student_table",
                                      "block_smartblock"))."\n";
        $string .= csv_esc(get_string("report_student_name",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_student_presence",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_student_presence_on_enabled",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_student_vote",
                                  "block_smartblock")).",";
        $string .= csv_esc(get_string("report_student_vote_on_presence",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("report_student_vote_on_enabled",
                                      "block_smartblock"))."\n";
        foreach( $this->student_table as $row ) {
            $row->enabledOnTotal = $this->enabled_event_count > 0 ?
                round( $row->enabled / $this->enabled_event_count * 100 ) :
                0;
            $row->votedOnPresence = $row->enabled > 0 ?
                round( $row->voted / $row->enabled * 100 ) :
                0;
            $row->votedOnEnabled = count($this->event_table) > 0 ?
                round( $row->voted / count( $this->event_table ) * 100 ) :
                0;
            $string .= csv_esc($row->data->firstname." ".$row->data->lastname)
                .",";
            $string .= csv_esc($row->enabled).",";
            $string .= csv_esc($row->enabledOnTotal."%").",";
            $string .= csv_esc($row->voted).",";
            $string .= csv_esc($row->votedOnPresence."%").",";
            $string .= csv_esc($row->votedOnEnabled."%")."\n";
        }
        echo $string;
        exit(0);
    }

}

?>