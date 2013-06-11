<?php

global $CFG,$COURSE;

// Capabilities
$string['smartblock:edit'] = "Edit MySmark EDU settings";
$string['smartblock:vote'] = "Vote with MySmark EDU";
$string['smartblock:supervise'] = "Read MySmark EDU global report";

// Generic
$string['pluginname'] = 'MySmark EDU';
$string['smartblock'] = 'MySmark EDU';
$string['close'] = '[close window]';

// Professor
$string['configure'] = 'Course configuration';
$string['manage'] = 'Course management';
$string['report'] = 'Get report';
$string['professor_greeting'] = "Hello, Professor!";

// Student
$string['student_greeting'] = "Hi, Student!";
$string['student_noupcoming'] = "There are no upcoming events to vote";
$string['student_upcoming'] = "Leave a feedback on:<br>";
$string['student_grant_vote'] = "Granting vote permission...";

// Errors
$string['error_not_authorized'] = "You are not authorized";
$string['error_invalid_course'] = "Invalid course";
$string['error_invalid_event'] = "Invalid event";
$string['error_remove_event_ondelete'] = "Please remove all enabled events ".
    "before deleting the course";
$string['error_remove_event_onupdate'] = "Please remove all enabled events ".
    "before changing the client credentials";
$string['error_remove_student_ondelete'] = "Please disable all students for ".
    "this event before deleting";
$string['error_invalid_presence'] = "Invalid presence";
$string['error_invalid_client_credentials'] = "Invalid client credentials";
$string['error_invalid_policy'] = "Invalid end time policy";
$string['error_invalid_hours'] = "Invalid time/hour modifier";
$string['error_invalid_course_start'] = "Invalid course start date";
$string['error_mysmark'] = "Error communicating with MySmark";
$string['error_cant_delete_presence'] = "Can't disable this user. User may ".
    "have already voted";
$string['error_invalid_scale'] = "The selected parameter scale is invalid. ".
    "Please remember that the scale must have at least 2 values and must be ".
    "numeric";
$string['error_invalid_scale_values'] = "Parameters values must be unique";
$string['error_invalid_parameters'] = "The selected number of parameter is ".
    "invalid. Please remember the minimum number of parameter for the NP is 3 ".
    "and the maximum is 8";

// Course configuration
$string['configure_title'] = "Course configuration - MySmark EDU";
$string['configure_h1'] = "Course configuration";
$string['configure_policy'] = "Please select the timeframe in which an user ".
    "can vote events";
$string['configure_policy_same'] = "Voting ends at given time the same day ".
    "the event ends";
$string['configure_policy_after'] = "Voting ends at given time the day after ".
    "the event ends";
$string['configure_policy_hours_count'] = "Voting ends after given hours have ".
    "passed from the event end";
$string['configure_policy_hours_number'] = "Time/hours for selected option";
$string['configure_couser_start'] = "Ignore events before";
$string['configure_submit'] = "Submit";
$string['configure_configured'] = "Configured";
$string['configure_params_enable'] = "Enable NP (n-parametric) voting system";
$string['configure_params_title'] = "Parameters";
$string['configure_params_scale'] = "Parameter values";
$string['configure_params_label'] = "Value labels";
$string['configure_params_name'] = "Parameter names";
$string['configure_params_value_addmore'] = "Add more values...";
$string['configure_params_name_addmore'] = "Add more parameters...";
$string['configure_param_config_file'] = "Upload parameters configuration";
// Course configuration tooltips
$string['tooltip_client_cred'] = "Get it from www.mysmark.com -> tools -> ".
    "account -> API Access";
$string['tooltip_course_start'] = "This option allow you to reuse this moodle ".
    "course in the future ignoring events relative to previous years";
$string['tooltip_param_config_file'] = "Upload a file that was previously ".
    "exported from another MySmark EDU course (via Course Management) ".
    "to use the same parameters";
$string['tooltip_param_scale'] = "Must be numeric. Will be reordered";
$string['tooltip_param_label'] = "Can be any string you like";
$string['tooltip_params_name'] = "Minimum 3 maximum 8";

// Coruse management
$string['manage_title'] = "Course management - MySmark EDU";
$string['manage_h1'] = "Course management";
$string['manage_get_param_config'] = "Download parameters configuration";
$string['manage_events'] = "Events";
$string['manage_event_name'] = "Name";
$string['manage_event_start'] = "Start";
$string['manage_event_end'] = "End";
$string['manage_event_enabled'] = "Enabled";
$string['manage_policy'] = "Voting end time policy";
$string['manage_policy_default'] = "Default";
$string['manage_policy_same'] = "Same day";
$string['manage_policy_after'] = "Day after";
$string['manage_policy_hours_count'] = "Hours count";
$string['manage_policy_hours_number'] = "Time/Hours";
$string['manage_submit'] = "Apply changes";

// Event management
$string['event_title'] = "Event management - MySmark EDU";
$string['event_h1'] = "Event management";
$string['event_name'] = "Event name:";
$string['event_start'] = "Start date:";
$string['event_end'] = "End date:";
$string['event_student_name'] = "Name";
$string['event_student_enroll_date'] = "Enrolled";
$string['event_student_enabled'] = "Enabled";
$string['event_student_enable_all'] = "all";
$string['event_student_enable_none'] = "none";
$string['event_submit'] = "Apply";

// Report
$string['report_title'] = "Report - MySmark EDU";
$string['report_h1'] = "Report";
$string['report_event_table'] = "Events table";
$string['report_event_name'] = "Event name";
$string['report_event_start'] = "Event start";
$string['report_event_end'] = "Event end";
$string['report_event_enabled_students'] = "Enabled Students";
$string['report_event_enabled_students_on_enrolled'] = "% on enrolled students";
$string['report_event_voting_students'] = "Voting Students";
$string['report_event_voting_students_on_enabled'] = "% on enabled";
$string['report_event_voting_students_on_enrolled'] = "% on enrolled";
$string['report_event_value'] = "iSmark";

$string['report_event_graph'] = "Events graph";
$string['report_event_graph_parameters'] = "Average parameters value";
$string['report_event_graph_course'] = "Average iSmark and Parameters value";
$string['report_event_graph_both'] = "Average iSmark/parameters value *";

$string['report_student_table'] = "Students table";
$string['report_student_name'] = "Student name";
$string['report_student_presence'] = "Presence";
$string['report_student_presence_on_enabled'] = "% on enabled events";
$string['report_student_vote'] = "Votes";
$string['report_student_vote_on_presence'] = "% on presence";
$string['report_student_vote_on_enabled'] = "% on enabled events";

// Supervisor
$string['supervisor_greeting'] = "Greetings Supervisor!";
$string['supervise'] = "Get global MySmarkEDU stats";
$string['supervise_title'] = "Global MySmarkEDU stats";
$string['supervise_course_count'] = "Total courses";
$string['supervise_course_name'] = "Course name";
$string['supervise_events'] = "Total events";
$string['supervise_enabled_events'] = "Enabled events";
$string['supervise_enrolled_students'] = "Enrolled Students";
$string['supervise_enabled_votes'] = "Total enabled feedbacks";
$string['supervise_votes'] = "Total feedbacks";

?>