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

class GlobalReport extends Report {
    
    private $sdk = null;
    private $raw_courses = array();
    private $table_data = array();
    
    public function __construct($courses, $format = "EU") {
        parent::__construct($format);
        $this->raw_courses = $courses;
        $this->_parse();
    }

    private function _parse() {
        foreach($this->raw_courses as $raw_course ) {
            global $CFG;
            $course = Course::retrieve($raw_course->courseid);
            $tempData = Course::getData($raw_course->courseid);
            try {
                $report = $course->getReport();
            } catch(Exception $e) {
                continue;
            }
            $enEvents = $course->getEnabledEvents();
            $table_row = new StdClass;
            $table_row->link = $CFG->wwwroot."/course/view.php?id=".$tempData->id;
            $table_row->name = $tempData->fullname;
            $table_row->eventCount = count($course->getEvents());
            $table_row->enabledEvents = count($enEvents);
            $table_row->enrolledStudents = count($course->getStudents());
            $table_row->votes = 0;
            // For each event
            foreach( $enEvents as $event ) {
                $mysmRef = $event->getMysmRef();
                // If event is in report
                if( array_key_exists( $mysmRef, $report ) &&
                    array_key_exists( "voted_hashs", $report[$mysmRef] )) {
                    $enStudents = $event->getEnabledStudents();
                    // For each enabled student
                    foreach( $enStudents as $student ) {
                        $sid = $student->getData()->id;
                        // If student has voted
                        if( in_array( $event->getHash( $sid ), 
                                      $report[$mysmRef]["voted_hashs"] ) ) {
                            // Add vote
                            $table_row->votes += 1;
                        }
                    }
                }
            }
            $votesEnabled = 0;
            foreach($enEvents as $event) {
                $votesEnabled += count($event->getEnabledStudents());
            }
            $table_row->votesEnabled = $votesEnabled;
            array_push($this->table_data, $table_row);
        }
    }

    public function renderCSV() {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="global-stats.csv"');
        $string = "";
        $string .= csv_esc(get_string("supervise_title","block_smartblock"))
            ."\n";
        $string .= csv_esc(get_string("supervise_course_name",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("supervise_events","block_smartblock")).",";
        $string .= csv_esc(get_string("supervise_enabled_events",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("supervise_enrolled_students",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("supervise_enabled_votes",
                                      "block_smartblock")).",";
        $string .= csv_esc(get_string("supervise_votes","block_smartblock"))."\n";
        
        foreach( $this->table_data as $row ) {
            $string .= csv_esc($row->name).",".
                csv_esc($row->eventCount).",".
                csv_esc($row->enabledEvents).",".
                csv_esc($row->enrolledStudents).",".
                csv_esc($row->votesEnabled).",".
                csv_esc($row->votes)."\n";
        }
        echo $string;
        exit(0);
    }
    
    public function getTableData() {
        return $this->table_data;
    }

    public function renderTable() {
        ?>
        <table cellpadding="10px" border="1px">
        <thead><tr>
        <th><?php echo get_string("supervise_course_name", 
                                  "block_smartblock")?></th>
        <th><?php echo get_string("supervise_events",
                                  "block_smartblock")?></th>
        <th><?php echo get_string("supervise_enabled_events",
                                  "block_smartblock")?></th>
        <th><?php echo get_string("supervise_enrolled_students",
                                  "block_smartblock")?></th>
        <th><?php echo get_string("supervise_enabled_votes",
                                  "block_smartblock")?></th>
        <th><?php echo get_string("supervise_votes",
                                  "block_smartblock")?></th>
        </tr></thead>
        <tbody><?php
        foreach( $this->table_data as $row ) {
        ?><tr>
        <td><a href="<?php echo $row->link ?>"><?php echo $row->name ?></a></td>
        <td><?php echo $row->eventCount ?></td>
        <td><?php echo $row->enabledEvents ?></td>
        <td><?php echo $row->enrolledStudents ?></td>
        <td><?php echo $row->votesEnabled ?></td>
        <td><?php echo $row->votes ?></td>
        </tr><?php
        }
        ?></tbody>
        </table>
        <?php
    }
}

?>