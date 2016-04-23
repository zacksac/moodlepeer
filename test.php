<?php


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/gradelib.php');
$dbconnect = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die('unable to connect to sql server');
$dbselect  = mysql_select_db($CFG->dbname) or die('unable to select the moodle database');



$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... peer instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('peer', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $peer  = $DB->get_record('peer', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $peer  = $DB->get_record('peer', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $peer->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('peer', $peer->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
echo $OUTPUT->header();
echo 'test page';
print_r($course);
echo $OUTPUT->footer();
?>
