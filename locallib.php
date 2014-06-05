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
 * Private link module utility functions
 *
 * @package    mod
 * @subpackage link
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/link/lib.php");

/**
 * This methods does weak link validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $link
 * @return bool true is seems valid, false if definitely not valid LINK
 */
function link_appears_valid_link($link) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $link)) {
        // note: this is not exact validation, we look for severely malformed LINKs only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $link);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $link);
    }
}

/**
 * Fix common LINK problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $link
 * @return string
 */
function link_fix_submitted_link($link) {
    // note: empty links are prevented in form validation
    $link = trim($link);

    // remove encoded entities - we want the raw URI here
    $link = html_entity_decode($link, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $link) and !preg_match('|^/|', $link)) {
        // invalid URI, try to fix it by making it normal LINK,
        // please note relative links are not allowed, /xx/yy links are ok
        $link = 'http://'.$link;
    }

    return $link;
}

/**
 * Return full link with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $link
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string link with & encoded as &amp;
 */
function link_get_full_link($link, $cm, $course, $config=null) {

    $parameters = empty($link->parameters) ? array() : unserialize($link->parameters);

    // make sure there are no encoded entities, it is ok to do this twice
    $fulllink = html_entity_decode($link->externalurl, ENT_QUOTES, 'UTF-8');

    if (preg_match('/^(\/|https?:|ftp:)/i', $fulllink) or preg_match('|^/|', $fulllink)) {
        // encode extra chars in LINKs - this does not make it always valid, but it helps with some UTF-8 problems
        $allowed = "a-zA-Z0-9".preg_quote(';/?:@=&$_.+!*(),-#%', '/');
        $fulllink = preg_replace_callback("/[^$allowed]/", 'link_filter_callback', $fulllink);
    } else {
        // encode special chars only
    	$fulllink = str_replace('"', '%22', $fulllink);
    	$fulllink = str_replace('\'', '%27', $fulllink);
    	$fulllink = str_replace(' ', '%20', $fulllink);
    	$fulllink = str_replace('<', '%3C', $fulllink);
    	$fulllink = str_replace('>', '%3E', $fulllink);
    }

    // add variable link parameters
    if (!empty($parameters)) {
        if (!$config) {
            $config = get_config('link');
        }
        $paramvalues = link_get_variable_values($link, $cm, $course, $config);

        foreach ($parameters as $parse=>$parameter) {
            if (isset($paramvalues[$parameter])) {
                $parameters[$parse] = rawurlencode($parse).'='.rawurlencode($paramvalues[$parameter]);
            } else {
                unset($parameters[$parse]);
            }
        }

        if (!empty($parameters)) {
            if (stripos($fulllink, 'teamspeak://') === 0) {
            	$fulllink = $fulllink.'?'.implode('?', $parameters);
            } else {
                $join = (strpos($fulllink, '?') === false) ? '?' : '&';
                $fulllink = $fulllink.$join.implode('&', $parameters);
            }
        }
    }

    // encode all & to &amp; entity
    $fulllink = str_replace('&', '&amp;', $fulllink);

    return $fulllink;
}

/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function link_filter_callback($matches) {
    return rawurlencode($matches[0]);
}

/**
 * Print link header.
 * @param object $link
 * @param object $cm
 * @param object $course
 * @return void
 */
function link_print_header($link, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$link->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($link);
    echo $OUTPUT->header();
}

/**
 * Print link heading.
 * @param object $link
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function link_print_heading($link, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($link->displayoptions) ? array() : unserialize($link->displayoptions);

    if ($ignoresettings or !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($link->name), 2, 'main', 'linkheading');
    }
}

/**
 * Print link introduction.
 * @param object $link
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function link_print_intro($link, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($link->displayoptions) ? array() : unserialize($link->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($link->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'linkintro');
            echo format_module_intro('link', $link, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display link frames.
 * @param object $link
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function link_display_frame($link, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        link_print_header($link, $cm, $course);
        link_print_heading($link, $cm, $course);
        link_print_intro($link, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('link');
        $context = context_module::instance($cm->id);
        $extelink = link_get_full_link($link, $cm, $course, $config);
        $navlink = "$CFG->wwwroot/mod/link/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($link->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','link'));
        $contentframetitle = format_string($link->name);
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navlink" title="$modulename"/>
    <frame src="$extelink" title="$contentframetitle"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');

		// print time limit
		if($link->timeopen != 0 && $link->timeclose != 0) {
			//echo '<p>'.get_string('expired_tip', 'link', userdate($link->timeopen)).userdate($link->timeclose).'</p>';
		}

        echo $extframe;
        die;
    }
}

/**
 * Print link info and link.
 * @param object $link
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function link_print_workaround($link, $cm, $course) {
    global $OUTPUT;

    link_print_header($link, $cm, $course);
    link_print_heading($link, $cm, $course, true);
    link_print_intro($link, $cm, $course, true);

    $fulllink = link_get_full_link($link, $cm, $course);

    $display = link_get_final_display_type($link);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfulllink = addslashes_js($fulllink);
        $options = empty($link->displayoptions) ? array() : unserialize($link->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfulllink', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="linkworkaround">';
    print_string('clicktoopen', 'url', "<a href=\"$fulllink\" $extra>$fulllink</a>");
    echo '</div>';

	// print time limit
	if($link->timeopen != 0 && $link->timeclose != 0) {
		echo '<p>'.get_string('expired_tip', 'link', userdate($link->timeopen)).userdate($link->timeclose).'</p>';
	}

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded link file.
 * @param object $link
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function link_display_embed($link, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_url_mimetype($link->externalurl);
    $fulllink  = link_get_full_link($link, $cm, $course);
    $title    = $link->name;

    $link = html_writer::tag('a', $fulllink, array('href'=>str_replace('&amp;', '&', $fulllink)));
    $clicktoopen = get_string('clicktoopen', 'url', $link);
    $moodlelink = new moodle_link($fulllink);

    $extension = resourcelib_get_extension($link->externalurl);

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true
    );

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fulllink, $title);

    } else if ($mediarenderer->can_embed_link($moodlelink, $embedoptions)) {
        // Media (audio/video) file.
        $code = $mediarenderer->embed_link($moodlelink, $title, 0, 0, $embedoptions);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fulllink, $title, $clicktoopen, $mimetype);
    }

    link_print_header($link, $cm, $course);
    link_print_heading($link, $cm, $course);

    echo $code;

	// print time limit
	if($link->timeopen != 0 && $link->timeclose != 0) {
		echo '<p>'.get_string('expired_tip', 'link', userdate($link->timeopen)).userdate($link->timeclose).'</p>';
	}

    link_print_intro($link, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $link
 * @return int display type constant
 */
function link_get_final_display_type($link) {
    global $CFG;

    if ($link->display != RESOURCELIB_DISPLAY_AUTO) {
        return $link->display;
    }

    // detect links to local moodle pages
    if (strpos($link->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($link->externalurl, 'file.php') === false and strpos($link->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url_mimetype($link->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Get the parameters that may be appended to LINK
 * @param object $config link module config options
 * @return array array describing opt groups
 */
function link_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'url'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'url')] = array(
        'linkinstance'     => 'id',
        'linkcmid'         => 'cmid',
        'linkname'         => get_string('name'),
        'linkidnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverurl'       => get_string('serverurl', 'url'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone').' 1',
        'userphone2'      => get_string('phone2').' 2',
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userurl'        => get_string('webpage'),
    );

    if ($config->rolesinparams) {
        $roles = role_fix_names(get_all_roles());
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->localname);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to LINK
 * @param object $link module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function link_get_variable_values($link, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverurl'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'linkinstance'     => $link->id,
        'linkcmid'         => $cm->id,
        'linkname'         => format_string($link->name),
        'linkidnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $values['usertimezone']    = get_user_timezone_offset();
        $values['userurl']         = $USER->url;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = link_get_encrypted_parameter($link, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $coursecontext = context_course::instance($course->id);
        $roles = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}

/**
 * BC internal function
 * @param object $link
 * @param object $config
 * @return string
 */
function link_get_encrypted_parameter($link, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($link, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * Optimised mimetype detection from general LINK
 * @param $fulllink
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function link_guess_icon($fulllink, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fulllink, '/') < 3 or substr($fulllink, -1) === '/') {
        // Most probably default directory - index.php, index.html, etc. Return null because
        // we want to use the default module icon instead of the HTML file icon.
        return null;
    }

    $icon = file_extension_icon($fulllink, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}
