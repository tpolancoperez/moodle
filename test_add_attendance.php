<?php 
error_reporting(0);
ini_set("display_errors",E_ALL);

//  adds or updates modules in a course using new formslib
define('CLI_SCRIPT', true);
require_once('config.php');
require_once('lib/datalib.php');
require_once('mod/attforblock/locallib.php');
require_once('mod/attforblock/lib.php');
require_once('mod/attforblock/mod_form.php');
require_once("course/lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/conditionlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
//require_once("./lib/moodlelib.php");
$USER->id='3';//<-----IMPORTANT!!! CHANGE TO MOODLEADMIN ID IN DATABASE
$CFG->forcelogin=0;
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
// THIS SCRIPT ADDS THE ATTENDANCE MODULE AND FIRST SESSION
// BASED ON THE INFORMATION COMING FROM THE mdl_course TABLE
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

$add = "attforblock";
$section = 0; // Attendence block always goes in the first section, section zero is not affiliated with a week.
	
//SELECT ALL COURSE ID'S FROM MOODLE DATABASE
$category_list = array("19");
foreach($category_list as $cat_id)
{
	$course_list = get_courses($categoryid=$cat_id,$sort="c.sortorder ASC",$fields="c.id");

	foreach($course_list as $course)
	{
		$cw = $DB->get_record('course', array('id'=>$course->id), '*', MUST_EXIST);

		$context = get_context_instance(CONTEXT_COURSE, $course->id);
		//
		if (! $module = $DB->get_record('modules', array('name'=>$add), '*', MUST_EXIST) ) {
			error("This module type doesn't exist");
		}

		if (!course_allowed_module($course, $module->name)) {
			error("This module has been disabled for this particular course");
		}
		//
		
		$form->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
		$form->visible          = 0;//$cw->visible; // TEST making this 'hidden' and see what happens. MS 10/11/12
		$form->course           = $course->id;
		$form->module           = $module->id;
		$form->modulename       = $module->name;
		$form->groupmode        = 0; 
		$form->groupingid       = $cw->defaultgroupingid;
		$form->groupmembersonly = 0;
		$form->instance         = '';
		$form->coursemodule     = '';
		$form->add              = $add;
		$form->return           = 0; //must be false if this is an add, go back to course view on cancel

		// Add in the form elements for the attendance block, this will be passed to the block 
		// code as configuration
		$form->name         	= "Attendance";
		$form->grade         	= 0;
		$form->groupmode        = 0;
		$form->gradecat         = 1620; // In Moodltest this is "Attendance"
		// Would need to be changed for prod
		// Hmm what is this
		$form->_qf__mod_attforblock_mod_form = 1;

		if($add=='resource' || $add=='glossary' || $add=='label') {
			$form->groupingid=0;
		}
		
		if (!empty($type)) {
			$form->type = $type;
		}

		//$sectionname = get_section_name($course,$cw);
		$fullmodulename = get_string("modulename", $module->name);
		
		$navlinksinstancename = '';
		$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
		if (file_exists($modmoodleform)) {
			require_once($modmoodleform);

		} else {
			error('No formslib form description file found for this activity.');
		}

		$modlib = "$CFG->dirroot/mod/$module->name/lib.php";
		if (file_exists($modlib)) {
			include_once($modlib);
		} else {
			error("This module is missing important code! ($modlib)");
		}

		if (! $course = $DB->get_record("course", array('id'=>$form->course), '*',MUST_EXIST)) {
			error("This course doesn't exist");
		}
		$form->instance = '';
		$form->coursemodule = '';

		// require_login($course->id); // needed to setup proper $COURSE

		if (!empty($form->coursemodule)) {
			$context = get_context_instance(CONTEXT_MODULE, $form->coursemodule);
		} else {
			$context = get_context_instance(CONTEXT_COURSE, $course->id);
		}
		// require_capability('moodle/course:manageactivities', $context);

		$form->course = $course->id;
		$form->modulename = clean_param($form->modulename, PARAM_SAFEDIR);  // For safety

		$addinstancefunction    = $form->modulename."_add_instance";
		$updateinstancefunction = $form->modulename."_update_instance";

		if (!isset($form->groupingid)) {
			$form->groupingid = 0;
		}

		if (!isset($form->groupmembersonly)) {
			$form->groupmembersonly = 0;
		}

		if (!isset($form->name)) { //label
			$form->name = $form->modulename;
		}

		if (!empty($form->add)) {

			if (!empty($course->groupmodeforce) or !isset($form->groupmode)) {
				$form->groupmode = 0; // do not set groupmode
			}

			if (!course_allowed_module($course,$form->modulename)) {
				error("This module ($form->modulename) has been disabled for this particular course");
			}

			$returnfromfunc = $addinstancefunction($form);
			if (!$returnfromfunc) {
				error("Could not add a new instance of $form->modulename", "view.php?id=$course->id");
			}
			if (is_string($returnfromfunc)) {
				error($returnfromfunc, "view.php?id=$course->id");
			}

			$form->instance = $returnfromfunc;

			//////////////////////////////////////////////////////////////////
			// IMPORTANT NOTE THAT MOODLE PROGRAMMERS KIND OF LEFT IN THE
			// HINTERLANDS, ONLY SLIGHTLY MENTIONED.  YAY FOR THEM.
			//////////////////////////////////////////////////////////////////
			// course_modules and course_sections each contain a reference
			// to each other, so we have to update one of them twice.

			if (! $form->coursemodule = add_course_module($form) ) {
				error("Could not add a new course module");
			}
			if (! $sectionid = add_mod_to_section($form) ) {
				error("Could not add the new course module to that section");
			}

			if (! $DB->set_field("course_modules", "section", $sectionid, array("id"=>$form->coursemodule))) {
				error("Could not update the course module with the correct section");
			}

			// make sure visibility is set correctly (in particular in calendar)
			///////////////////////////////////////////////////////////////////
			// set the course module to 0 (zero) and it will be hidden
			// in the table mdl_course_modules (assigned module to a course, hence
			// $form->visible which shows the courses visibility)
			// --at least that's the plan - MS 10/11/12
			set_coursemodule_visible($form->coursemodule, $form->visible);

			if (isset($form->cmidnumber)) { //label
				// set cm idnumber
				set_coursemodule_idnumber($form->coursemodule, $form->cmidnumber);
			}

			add_to_log($course->id, "course", "add mod",
					   "../mod/$form->modulename/view.php?id=$form->coursemodule",
					   "$form->modulename $form->instance");
			add_to_log($course->id, $form->modulename, "add",
					   "view.php?id=$form->coursemodule",
					   "$form->instance", $form->coursemodule);
		} else {
			error("Data submitted is invalid.");
		}
		/*
		/////////////////////////////////////////////////////////////////////////////
		// We are assuming here that nothing will go haywire by removing grade 
		// related tables from the activity module entitled 'attendance' because
		// we assume that there aren't more connections that are affiliated but
		// not mentioned in the documentation for the attforblock - MS 10/11/12
		// 
		// sync idnumber with grade_item
		if ($grade_item = grade_item::fetch(array('itemtype'=>'mod', 'itemmodule'=>$form->modulename,
					 'iteminstance'=>$form->instance, 'itemnumber'=>0, 'courseid'=>$COURSE->id))) {
			if ($grade_item->idnumber != $form->cmidnumber) {
				$grade_item->idnumber = $form->cmidnumber;
				$grade_item->update();
			}
		}

		$items = grade_item::fetch_all(array('itemtype'=>'mod', 'itemmodule'=>$form->modulename,
											 'iteminstance'=>$form->instance, 'courseid'=>$COURSE->id));

		// create parent category if requested and move to correct parent category
		if ($items and isset($form->gradecat)) {
			if ($form->gradecat == -1) {
				$grade_category = new grade_category();
				$grade_category->courseid = $COURSE->id;
				$grade_category->fullname = stripslashes($form->name);
				$grade_category->insert();
				if ($grade_item) {
					$parent = $grade_item->get_parent_category();
					$grade_category->set_parent($parent->id);
				}
				$form->gradecat = $grade_category->id;
			}
			foreach ($items as $itemid=>$unused) {
				$items[$itemid]->set_parent($form->gradecat);
				if ($itemid == $grade_item->id) {
					// use updated grade_item
					$grade_item = $items[$itemid];
				}
			}
		}
		*/
		rebuild_course_cache($course->id);
		// grade_regrade_final_grades($course->id);
		// ADD A SESSION FOR THE ATTENDANCE, NOW THAT WE HAVE
		// course_id & sessdate and have made the attendance
		// activity a standard item.
		// insert one session
		//
		echo "Added MODULE 'ATTENDANCE' to $course->shortname (# $course->id)\n";
		$rec->attendanceid = $form->instance;//$course->id; NEED THE
		$rec->groupid = 0; 
		$rec->sessdate = $course->startdate;
		$rec->duration = 7200;
		$timeModded = time();
		$rec->lasttaken = '';
		$rec->lasttakenby = '';
		$rec->timemodified = $timeModded;
		$rec->description = "$course->summary";
		$rec->descriptionformat = 1;
		// MAKE A NEW SESSION DATE BIT
		if($DB->insert_record('attendance_sessions', $rec))
		{
			echo "SUCCESS! - Attendance Session for COURSE #".$course->id." and Instance # $rec->attendanceid Inserted\n";
		}
		else
		{
			echo "I REALLY DON'T LIKE YOUR SQL!\n";
		}//
		echo "---------------NEXT----------------------\n";
		//
		//*/
	}// END OF LOOP ABOVE

} // END OF COURSE CATEGORY ID ARRAY
	print ";o) Done with everything.  Whew!\n";//
    exit;
	//

?>
