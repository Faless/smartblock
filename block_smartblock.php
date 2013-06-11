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

include 'include/basic.inc.php';

class block_smartblock extends block_base {
    
    private $sb_config;
    private $sb_manage;
    private $sb_report;
    private $sb_url;
    private $sb_logo;
    
    function init() {
        global $CFG, $COURSE;
        $this->title   = get_string('smartblock', 'block_smartblock');
        $this->version = 2013031900;
        $this->sb_url = $CFG->wwwroot."/blocks/smartblock";
        $this->sb_logo = '<img src="'.$this->sb_url.'/img/logo.png">';
        $this->sb_config = "<a href='".$this->sb_url."/form_config.php?course="
            .$COURSE->id."' target='config'>".get_string('configure', 
                                                         'block_smartblock')
            ."</a>";
        $this->sb_manage = "<a href='".$this->sb_url."/form_manage.php?course="
            .$COURSE->id."' target='manage'>".get_string('manage',
                                                         'block_smartblock')
            ."</a>";
        $this->sb_report = "<a href='".$this->sb_url."/report.php?course="
            .$COURSE->id."' target='report'>".get_string('report',
                                                         'block_smartblock')
            ."</a> / <a href='".$this->sb_url."/report.php?course="
            .$COURSE->id."&format=US' target='report'>(US format)</a>";
        $this->sb_supervise = "<a href='".$this->sb_url."/supervise.php"
            ."' target='manage'>".get_string('supervise',
                                             'block_smartblock')
            ."</a> / <a href='".$this->sb_url
            ."/supervise.php?format=US' target='report'>(US format)</a>";
    }

    function get_content() {
        global $USER, $CFG, $SESSION, $COURSE, $SITE;
        if ($this->content !== NULL) {
            return $this->content;
        }
        if( $COURSE->id == $SITE->id) {
            if( User::isSupervisor() ) {
                $this->supervisorView();
            }
        } else if( User::isProfessor() ) {
            $this->professorView();
        } else if( User::isStudent( $USER->id, $COURSE->id ) ) {
            $this->studentView();
        }
        if($this->content !== NULL)
            $this->content->footer = '<p>by <a href="https://www.mysmark.com" '
                .'target="mysmark">MySmark</a></p>';
        return $this->content;
    }
    
    function applicable_formats() {
        return array(
                     'course-view' => true,
                     'site-index'  => true
        );
    }
        
    public function instance_allow_multiple() {
        return FALSE;
    }

    function supervisorView() {
        $this->content = new stdClass;
        $this->content->text  = $this->sb_logo;
        $this->content->text .= '<p>'.get_string( "supervisor_greeting", 
                                                  "block_smartblock" ).'</p>';
        $this->content->text .= '<p>'.$this->sb_supervise.'<p>';
    }

    function professorView()
    {
        $course = Course::retrieve();
        if( $course == null )
            return $this->configureView();
        global $CFG, $COURSE;
        $this->content         = new stdClass;
        $this->content->text   = $this->sb_logo;
        $this->content->text  .= '<p>'.get_string("professor_greeting",
                                                  "block_smartblock").'</p>';
        $this->content->text  .= "<hr>";
        $this->content->text  .= '<p>'.$this->sb_config."</p>";
        $this->content->text  .= '<p>'.$this->sb_manage.'</p>';
        $this->content->text  .= '<p>'.$this->sb_report.'</p>';
    }
    
    function studentView()
    {
        if( !Course::isConfigured() )
            return null;
        global $COURSE, $CFG, $USER;
        $course = Course::retrieve( $COURSE->id );
        $student = Student::retrieve( $USER->id );
        if( $course == null || $student == null ) {
            $this->content = null;
            return null;
        }
        $this->content        = new stdClass;
        $this->content->text  = $this->sb_logo;
        $this->content->text .= '<p>'.get_string( "student_greeting", 
                                                  "block_smartblock" ).'</p>';
        $this->content->text .= "<hr>";
        $this->content->text .= "<p>";
        $events = $course->getEnabledEvents();

        $text = "";
        foreach( $events as $event ) {
            $data = $event->getData();
            if( Event::isEnabled( $data->id ) &&
                $data->timestart + $data->timeduration < time() &&
                $event->getExpires() > time() &&
                $student->isEnabled( $data->id ) )
                $text .= "<a href='".$this->sb_url."/vote.php?".
                    "event=".$data->id."' target='vote'>".$data->name.
                    " - <span style='font-size: 12px;'>"
                    .date( "Y-m-d H:i", $data->timestart )."</span>"
                    ."</a><br>";
        }

        if( $text == "" )
            $this->content->text .= get_string( "student_noupcoming",
                                                "block_smartblock" );
        else $this->content->text .= get_string( "student_upcoming",
                                                 "block_smartblock" );
        $this->content->text .= $text;
        $this->content->text .= "</p>";
    }

    function configureView()
    {
        global $COURSE, $CFG, $USER;
        $this->content        = new stdClass;
        $this->content->text  = $this->sb_logo;
        $this->content->text .= '<hr>';
        $this->content->text .= '<p>'.$this->sb_config.'</p>';
    }
}

?>
