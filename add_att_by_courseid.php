<?php 
ini_set("display_errors",E_ALL);
define('CLI_SCRIPT',true);
//  adds or updates modules in a course using new formslib
require_once("./config.php");
require_once("./course/lib.php");
require_once("./lib/dml/moodle_database.php");
require_once($CFG->libdir.'/gradelib.php');
require_once("./lib/moodlelib.php");
require_once("./lib/deprecatedlib.php");
global $DB;
$USER->id='2';// This is the address of the moodleadmin user
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
$course_id_list = Array ('2919','20050','20057','20037','3031','20051','1695','20044','20049','3008','20008','20026','20046','20035','20020','20031','20024','20025','20016','1581','2926','20045','20040','3013','20019','2166','20012','20005','20038','2946','3034','2999','3027','2996','2993','2836','20023','20054','3012','2934','1647','2837','20048','2992','1698','2994','20036','20002','20006','20015','20010','3002','3014','2198','20018','2995','20022','20014','2998','20041','2200','3009','2199','2197','3007','2163','2997','3001','20011','3033','20013','20027','1697','3032','20032','20042','20021','20007','20028','3029','3003','20009','20001','2931','3006','1569','1611','2161');

foreach($course_id_list as $course_id)
{
	// POPULATE A COURSE OBJECT
	// STUPID WORKAROUND because second param must be an array...
	$course = $DB->get_record('course',array('id'=>$course_id),$fields='*');
	var_dump($course);

	$cw = get_course_section($section,$course->id);
	
	$context = get_context_instance(CONTEXT_COURSE, $course->id);
	
	if (! $module = $DB->get_record("modules", array('name'=>$add),$fields='*')) {
		error("This module type doesn't exist");
	}
	
	if (!course_allowed_module($course, $module->name)) {
		error("This module has been disabled for this particular course");
	}
	
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
	$form->_qf__mod_attforblock_mod_form = 1;

	// Turn off default grouping for modules that don't provide group mode
	if($add=='resource' || $add=='glossary' || $add=='label') {
		$form->groupingid=0;
	}
	
	if (!empty($type)) {
		$form->type = $type;
	}

	$sectionname = get_section_name($course,$section);
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

	if (!empty($form->coursemodule)) {
		$context = get_context_instance(CONTEXT_MODULE, $form->coursemodule);
	} else {
		$context = get_context_instance(CONTEXT_COURSE, $course->id);
	}

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
	
	echo "---------------NEXT----------------------\n";
//*/
}
	print ";o) Done with everything.  Whew!\n";
    exit;
?>
