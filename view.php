<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * LINK module main user interface
 *
 * @package    mod
 * @subpackage link
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/link/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // LINK instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $link = $DB->get_record('link', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('link', $link->id, $link->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('link', $id, 0, false, MUST_EXIST);
    $link = $DB->get_record('link', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);

if ($CFG->version >= 2011120500) {
	$context = context_module::instance($cm->id);//2.2和以上
}else{
	$context = get_context_instance(CONTEXT_MODULE, $cm->id);//2.0 and 2.1
}

require_capability('mod/link:view', $context);

add_to_log($course->id, 'link', 'view', 'view.php?id='.$cm->id, $link->id, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/link/view.php', array('id' => $cm->id));

// Make sure LINK exists before generating output - some older sites may contain empty links
// Do not use PARAM_URL here, it is too strict and does not support general URIs!
$extlink = trim($link->externalurl);
if (empty($extlink) or $extlink === 'http://') {
    link_print_header($link, $cm, $course);
    link_print_heading($link, $cm, $course);
    link_print_intro($link, $cm, $course);
    notice(get_string('invalidstoredlink', 'url'), new moodle_link('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($extlink);

// weather print time limit
$timenow = time();
if ($link->timeclose !=0) {
	if ($link->timeopen > $timenow || $timenow > $link->timeclose) {
		link_print_header($link, $cm, $course);
		link_print_heading($link, $cm, $course);
		link_print_intro($link, $cm, $course);
		echo '<p>'.get_string('expired', 'link').'</p>';
		echo $OUTPUT->footer();
		die;
	}    
}

$displaytype = link_get_final_display_type($link);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    // For 'open' links, we always redirect to the content - except if the user
    // just chose 'save and display' from the form then that would be confusing
    if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
    // coming from course page or link index page,
    // the redirection is needed for completion tracking and logging
    $fulllink = link_get_full_link($link, $cm, $course);
    redirect(str_replace('&amp;', '&', $fulllink));
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        link_display_embed($link, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        link_display_frame($link, $cm, $course);
        break;
    default:
        link_print_workaround($link, $cm, $course);
        break;
}
