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
 * Mandatory public API of link module
 *
 * @package    mod
 * @subpackage link
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in LINK module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function link_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        //case FEATURE_SHOW_DESCRIPTION:        return true;
        case 'showdescription':				  return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function link_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function link_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function link_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function link_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add link instance.
 * @param object $data
 * @param object $mform
 * @return int new link instance id
 */
function link_add_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/link/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->externalurl = link_fix_submitted_link($data->externalurl);

    $data->timemodified = time();

    if (empty($data->timerestrict)) {
        $data->timeopen = 0;
        $data->timeclose = 0;
    }

    $data->id = $DB->insert_record('link', $data);

    return $data->id;
}

/**
 * Update link instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function link_update_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/link/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->externalurl = link_fix_submitted_link($data->externalurl);

    $data->timemodified = time();

    if (empty($data->timerestrict)) {
        $data->timeopen = 0;
        $data->timeclose = 0;
    }

    $data->id           = $data->instance;

    $DB->update_record('link', $data);

    return true;
}

/**
 * Delete link instance.
 * @param int $id
 * @return bool true
 */
function link_delete_instance($id) {
    global $DB;

    if (!$link = $DB->get_record('link', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('link', array('id'=>$link->id));

    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $link
 * @return object|null
 */
function link_user_outline($course, $user, $mod, $link) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'link',
                                              'action'=>'view', 'info'=>$link->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $link
 */
function link_user_complete($course, $user, $mod, $link) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'link',
                                              'action'=>'view', 'info'=>$link->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'link');
    }
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function link_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/link/locallib.php");

    if (!$link = $DB->get_record('link', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, externalurl, parameters, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $link->name;

    //note: there should be a way to differentiate links from normal resources
    $info->icon = link_guess_icon($link->externalurl, 24);

    $display = link_get_final_display_type($link);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fulllink = "$CFG->wwwroot/mod/link/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($link->displayoptions) ? array() : unserialize($link->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fulllink', '', '$wh'); return false;";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fulllink = "$CFG->wwwroot/mod/link/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fulllink'); return false;";

    }

	if(isset($coursemodule->showdescription)) {
		if ($coursemodule->showdescription) {
			// Convert intro to html. Do not filter cached version, filters run at display time.
			$info->content = format_module_intro('link', $link, $coursemodule->id, false);
		}
	}

    return $info;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function link_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-link-*'=>get_string('page-mod-link-x', 'link'));
    return $module_pagetype;
}

/**
 * Export LINK resource contents
 *
 * @return array of file content
 */
function link_export_contents($cm, $baselink) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/link/locallib.php");
    $contents = array();
    $context = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $link = $DB->get_record('link', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fulllink = str_replace('&amp;', '&', link_get_full_link($link, $cm, $course));
    $islink = clean_param($fulllink, PARAM_URL);
    if (empty($islink)) {
        return null;
    }

    $link = array();
    $link['type'] = 'link';
    $link['filename']     = $link->name;
    $link['filepath']     = null;
    $link['filesize']     = 0;
    $link['filelink']      = $fulllink;
    $link['timecreated']  = null;
    $link['timemodified'] = $link->timemodified;
    $link['sortorder']    = null;
    $link['userid']       = null;
    $link['author']       = null;
    $link['license']      = null;
    $contents[] = $link;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function link_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'url', 'message' => get_string('createlink', 'link'))
                 ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function link_dndupload_handle($uploadinfo) {
    // Gather all the required data.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    $data->externalurl = clean_param($uploadinfo->content, PARAM_URL);
    $data->timemodified = time();

    // Set the display options to the site defaults.
    $config = get_config('link');
    $data->display = $config->display;
    $data->popupwidth = $config->popupwidth;
    $data->popupheight = $config->popupheight;
    $data->printheading = $config->printheading;
    $data->printintro = $config->printintro;

    return link_add_instance($data, null);
}
