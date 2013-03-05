<?php 
ini_set("display_errors",E_ALL);
// $Id: modedit.php,v 1.34.2.12 2008/07/21 11:46:00 sam_marshall Exp $

//  adds or updates modules in a course using new formslib

require_once("./config.php");
require_once("./course/lib.php");
require_once($CFG->libdir.'/gradelib.php');
//require_once("./lib/moodlelib.php");
$USER->id='3';
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
$category->id = 173; // this will have to be manually entered, in moodlenewtest, spring2012 is category "2"
$course_list = get_courses($categoryid=$category->id,$sort="c.sortorder ASC",$fields="c.id");

foreach($course_list as $course)
{
	$cw = get_course_section($section,$course->id);
	//*/
	// Make sure that the course exists
	/*if (! $course = get_record("course", "id", $course)) {
		error("This course doesn't exist");
	}//*/
	// we now have the data we can pull from at LINE #265
	
	$context = get_context_instance(CONTEXT_COURSE, $course->id);
	//*/
	
	if (! $module = get_record("modules", "name", $add)) {
		error("This module type doesn't exist");
	}
	
	if (!course_allowed_module($course, $module->id)) {
		error("This module has been disabled for this particular course");
	}
	//*/
	
	$form->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
	$form->visible          = $cw->visible;
	$form->course           = $course->id;
	$form->module           = $module->id;
	$form->modulename       = $module->name;
	$form->groupmode        = $course->groupmode;
	$form->groupingid       = $course->defaultgroupingid;
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

	/*	
	print "Form is\n";
	var_dump($form);
	print "Module is\n";
	var_dump($module);
	print "cw is\n";
	var_dump($cw);
	//*/
	// Turn off default grouping for modules that don't provide group mode
	if($add=='resource' || $add=='glossary' || $add=='label') {
		$form->groupingid=0;
	}
	
	if (!empty($type)) {
		$form->type = $type;
	}

	$sectionname = get_section_name($course->format);
	$fullmodulename = get_string("modulename", $module->name);

	if ($form->section && $course->format != 'site') {
		$heading->what = $fullmodulename;
		$heading->to   = "$sectionname $form->section";
		$pageheading = get_string("addinganewto", "moodle", $heading);
	} else {
		$pageheading = get_string("addinganew", "moodle", $fullmodulename);
	}

	$CFG->pagepath = 'mod/'.$module->name;
	if (!empty($type)) {
		$CFG->pagepath .= '/'.$type;
	} else {
		$CFG->pagepath .= '/mod';
	}

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

	if (! $course = get_record("course", "id", $form->course)) {
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

		if (! set_field("course_modules", "section", $sectionid, "id", $form->coursemodule)) {
			error("Could not update the course module with the correct section");
		}

		// make sure visibility is set correctly (in particular in calendar)
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

	rebuild_course_cache($course->id);
	grade_regrade_final_grades($course->id);
	// ADD A SESSION FOR THE ATTENDANCE, NOW THAT WE HAVE
	// course_id & sessdate and have made the attendance
	// activity a standard item.
	// insert one session
	//*/
	echo "Added MODULE 'ATTENDANCE' to $course->shortname (# $course->id)\n";
	$rec->courseid = $course->id;
	$rec->sessdate = $course->startdate;
	$rec->duration = 7200;
	$timeModded = time();
	$rec->lasttaken = '';
	$rec->lasttakenby = '';
	$rec->timemodified = $timeModded;
	$rec->description = "$course->summary";
	if(insert_record('attendance_sessions', $rec))
	{
		echo "SUCCESS! - Attendance Session for COURSE #".$rec->courseid." Inserted\n";
	}
	else
	{
		echo "I REALLY DON'T LIKE YOUR SQL!\n";
	}
	/* // don't need this anymore as we only want one
	// ADD A SECOND SESSION THAT IS 2 DAYS OUT.
	$rec1->courseid = $course->id;
	$two_days_after = $course->startdate + (2 * 24 * 60 * 60);
	$rec1->sessdate = $two_days_after;
	$rec1->duration = "7200";
	$rec1->timemodified = time();
	$rec1->description = $course->summary;

	if(insert_record('attendance_sessions', $rec1))
	{
		echo "SUCCESS! - Attendance 2ND Session for COURSE #".$rec->courseid." Inserted\n";
	}
	else
	{
		echo "I REALLY DON'T LIKE YOUR SQL!\n";
	}
	*/	
	
	echo "---------------NEXT----------------------\n";
	//*/
}
	print ";o) Done with everything.  Whew!\n";//*/
    exit;
	//*/
?>
