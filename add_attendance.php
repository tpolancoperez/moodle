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
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    if (!file_exists('./config.php')) {
        header('Location: install.php');
        die;
    }

    require_once('config.php');
    require_once($CFG->dirroot .'/course/lib.php');
    require_once($CFG->libdir .'/filelib.php');
    $urlparams = array();
    $PAGE->set_context(context_system::instance());
    $PAGE->set_other_editing_capability('moodle/course:manageactivities');
    $PAGE->set_docs_path('');
    $PAGE->set_pagelayout('frontpage');
    $editing = $PAGE->user_is_editing();
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    $PAGE->set_url('/add_attendance.php', $urlparams);
    $PAGE->set_course($SITE);
    // Prevent caching of this page to stop confusion when changing page after making AJAX changes
    $PAGE->set_cacheable(false);
    if ($CFG->forcelogin) {
        require_login();
    } else {
        user_accesstime_log();
    }

    $hassiteconfig = has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
    echo $OUTPUT->header();
$admins = get_admins();
$adminator = false;
foreach($admins as $admin)
{
	if($admin->id == $USER->id)
	{
		$adminator = true;
		break;
	}
}
if($adminator == true)
{
/////////////////////////////////////////////
$list = $DB->get_records_select('course_categories','', array(), $sort='id DESC',$fields='id,name');
echo "<h1>Add Attendance Activity (Global)</h1>";
echo "<p>Choose the categories ('quarters') from the list below to which you would like to add the Attendance Module.</p>";
echo "<p><font color='red'><strong>Please note:</strong></font> This will run the process that adds this module <em>globally</em>.</p><p>Run this process only once per quarter to avoide duplicate attendance modules.</p>";
echo "<em>(Use CTRL + 'click') to select more than one category)</em><br>";
echo "<form id='add_attendance_module_global' action='add_attendance_process.php' method='post'>";
echo "<select id='course_categories[]' name='course_categories[]' multiple size='10' style=\"width:350\">";
foreach($list as $cat)
{
	$course_cat = (array) $cat;
	echo "<option value=\"".$course_cat['id']."\">".$course_cat['name']."</option>";
}
echo "</select><br>";
echo "<input type='submit' value='-- Cross Fingers and hope nothing goes wrong --'>";
echo "</form>";
////////////////////////////////////////////
    echo $OUTPUT->footer();
}
else
{
	echo "<h1>Login Required</h1>";
	echo "<p>You must login as an administrator in order to view this resource.</p>";
}
?>
