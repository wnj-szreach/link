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
 * Strings for component 'link', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod
 * @subpackage link
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Click {$a} link to open resource.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Content';
$string['createlink'] = 'Create a LINK';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the LINK file type and whether the browser allows embedding, determines how the LINK is displayed. Options may include:

* Automatic - The best display option for the LINK is selected automatically
* Embed - The LINK is displayed within the page below the navigation bar together with the LINK description and any blocks
* Open - Only the LINK is displayed in the browser window
* In pop-up - The LINK is displayed in a new browser window without menus or an address bar
* In frame - The LINK is displayed within a frame below the the navigation bar and LINK description
* New window - The LINK is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all LINKs.';
$string['externalurl'] = '123';
$string['framesize'] = 'Frame height';
$string['invalidstoredlink'] = 'Cannot display this resource, LINK is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['invalidlink'] = 'Entered LINK is invalid';
$string['modulename'] = 'LINK';
$string['modulename_help'] = 'The LINK module enables a teacher to provide a web link as a course resource. Anything that is freely available online, such as documents or images, can be linked to; the LINK doesn’t have to be the home page of a website. The LINK of a particular web page may be copied and pasted or a teacher can use the file picker and choose a link from a repository such as Flickr, YouTube or Wikimedia (depending upon which repositories are enabled for the site).

There are a number of display options for the LINK, such as embedded or opening in a new window and advanced options for passing information, such as a student\'s name, to the LINK if required.

Note that LINKs can also be added to any other resource or activity type through the text editor.';
$string['modulename_link'] = 'mod/link/view';
$string['modulenameplural'] = 'LINKs';
$string['neverseen'] = 'Never seen';
$string['page-mod-link-x'] = 'Any LINK module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'LINK variables';
$string['parametersheader_help'] = 'Some internal Moodle variables may be automatically appended to the LINK. Type your name for the parameter into each text box(es) and then select the required matching variable.';
$string['pluginadministration'] = 'LINK module administration';
$string['pluginname'] = 'LINK';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printheading'] = 'Display LINK name';
$string['printheadingexplain'] = 'Display LINK name above content? Some display types may not display LINK name even if enabled.';
$string['printintro'] = 'Display LINK description';
$string['printintroexplain'] = 'Display LINK description below content? Some display types may not display description even if enabled.';
$string['rolesinparams'] = 'Include role names in parameters';
$string['serverlink'] = 'Server LINK';
$string['link:addinstance'] = 'Add a new LINK resource';
$string['link:view'] = 'View LINK';
$string['expired'] = '不在限定时间内';
$string['expired_tip'] = '时间限制从{$a}到';
$string['timerestrict_open'] = '打开时间限制';
$string['appearance'] = '外观';
