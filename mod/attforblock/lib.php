<?PHP  // $Id: lib.php,v 1.4.2.5 2009/03/11 18:21:08 dlnsk Exp $

/// Library of functions and constants for module attforblock

$attforblock_CONSTANT = 7;     /// for example

function attforblock_add_instance($attforblock) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.
    global $DB;
    
    $attforblock->timemodified = time();

    if ($att = $DB->get_record('attforblock', array('course'=>$attforblock->course))) {
    	$modnum = $DB->get_field('modules', 'id', array('name'=>'attforblock'));
    	if (!$DB->get_record('course_modules', array('course'=>$attforblock->course, 'module'=>$modnum))) {
    		$DB->delete_records('attforblock', array('course'=>$attforblock->course));
    		$attforblock->id = $DB->insert_record('attforblock', $attforblock);
    	} else {
    		return false;
    	}
    } else {
    	$attforblock->id = $DB->insert_record('attforblock', $attforblock);
    }

    //Copy statuses for new instance from defaults
    if (!$DB->get_records('attendance_statuses', array('courseid'=>$attforblock->course))) {
        $statuses = $DB->get_records('attendance_statuses', array('courseid'=>0), 'id');
        foreach($statuses as $stat) {
            $rec = $stat;
            $rec->courseid = $attforblock->course;
            $DB->insert_record('attendance_statuses', $rec);
        }
    }
						
//    attforblock_grade_item_update($attforblock);
//	attforblock_update_grades($attforblock);
    return $attforblock->id;
}


function attforblock_update_instance($attforblock) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.
    global $DB;

    $attforblock->timemodified = time();
    $attforblock->id = $attforblock->instance;

    if (! $DB->update_record('attforblock', $attforblock)) {
        return false;
    }

    attforblock_grade_item_update($attforblock);

    return true;
}


function attforblock_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  
    global $DB;

    if (! $attforblock = $DB->get_record('attforblock', array('id'=>$id))) {
        return false;
    }
    
    $result = $DB->delete_records('attforblock', array('id'=>$id));

    attforblock_grade_item_delete($attforblock);

    return $result;
}

function attforblock_delete_course($course, $feedback=true){
    global $DB;

    if ($sess = $DB->get_records('attendance_sessions', array('courseid'=>$course->id), '', 'id')) {
        list($slist, $params) = $DB->get_in_or_equal(array_keys($sess));
        $DB->delete_records_select('attendance_log', "sessionid $slist", $params);
        $DB->delete_records('attendance_sessions', array('courseid'=>$course->id));
    }

    $DB->delete_records('attendance_statuses', array('courseid'=>$course->id));
	
    return true;
}

/**
 * Called by course/reset.php
 * @param $mform form passed by reference
 */
function attforblock_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'attendanceheader', get_string('modulename', 'attforblock'));

	$mform->addElement('static', 'description', get_string('description', 'attforblock'),
								get_string('resetdescription', 'attforblock'));    
    $mform->addElement('checkbox', 'reset_attendance_log', get_string('deletelogs','attforblock'));

    $mform->addElement('checkbox', 'reset_attendance_sessions', get_string('deletesessions','attforblock'));
    $mform->disabledIf('reset_attendance_sessions', 'reset_attendance_log', 'notchecked');

    $mform->addElement('checkbox', 'reset_attendance_statuses', get_string('resetstatuses','attforblock'));
    $mform->setAdvanced('reset_attendance_statuses');
    $mform->disabledIf('reset_attendance_statuses', 'reset_attendance_log', 'notchecked');
}

/**
 * Course reset form defaults.
 */
function attforblock_reset_course_form_defaults($course) {
    return array('reset_attendance_log'=>0, 'reset_attendance_statuses'=>0, 'reset_attendance_sessions'=>0);
}

function attforblock_reset_userdata($data) {
    global $DB;
    if (!empty($data->reset_attendance_log)) {
        $sess = $DB->get_records('attendance_sessions', array('courseid'=>$data->courseid), '', 'id');
        list($slist, $params) = $DB->get_in_or_equal(array_keys($sess));
    	$DB->delete_records_select('attendance_log', "sessionid $slist", $params);
        $DB->set_field('attendance_sessions', 'lasttaken', 0, array('courseid'=>$data->courseid));
    }
    if (!empty($data->reset_attendance_statuses)) {
    	$DB->delete_records('attendance_statuses', array('courseid'=>$data->courseid));
        $statuses = $DB->get_records('attendance_statuses', array('courseid'=>0), 'id');
        foreach($statuses as $stat) {
            $rec = $stat;
            $rec->courseid = $data->courseid;
            $DB->insert_record('attendance_statuses', $rec);
        }
    }
    if (!empty($data->reset_attendance_sessions)) {
    	$DB->delete_records('attendance_sessions', array('courseid'=>$data->courseid));
    }
}

function attforblock_user_outline($course, $user, $mod, $attforblock) {
/// Return a small object with summary information about what a 
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description
	global $CFG;
	require_once($CFG->dirroot . '/mod/attforblock/locallib.php');
	
  	$currentcontext = get_context_instance(CONTEXT_COURSE, $course->id);
        $isstudent = has_capability('moodle/course:viewparticipants', $currentcontext, $user->id);
  	if ($isstudent) {
	  	if ($sescount = get_attendance($user->id,$course)) {
	  		$strgrade = get_string('grade');
	  		$maxgrade = get_maxgrade($user->id, $course);
	  		$usergrade = get_grade($user->id, $course);
	  		$percent = get_percent($user->id,$course);
	  		$result->info = "$strgrade: $usergrade / $maxgrade ($percent%)";
	  	}
  	}
  	
	return $result;
}

function attforblock_user_complete($course, $user, $mod, $attforblock) {
/// Print a detailed representation of what a  user has done with 
/// a given particular instance of this module, for user activity reports.
        global $CFG;
        require_once($CFG->dirroot . '/mod/attforblock/locallib.php');
	
	$currentcontext = get_context_instance(CONTEXT_COURSE, $course->id);
        $isstudent = has_capability('moodle/course:viewparticipants', $currentcontext, $user->id);
  	if ($isstudent) {
//        if (! $cm = get_coursemodule_from_instance("attforblock", $attforblock->id, $course->id)) {
//            error("Course Module ID was incorrect");
//        }
		print_user_attendaces($user, $mod, $course);
	}

    //return true;
}

function attforblock_print_recent_activity($course, $isteacher, $timestart) {
/// Given a course and a time, this module should find recent activity 
/// that has occurred in attforblock activities and print it out. 
/// Return true if there was output, or false is there was none.

    return false;  //  True if anything was printed, otherwise false 
}

function attforblock_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such 
/// as sending out mail, toggling flags etc ... 

    return true;
}

/**
 * Return grade for given user or all users.
 *
 * @param int $attforblockid id of attforblock
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function attforblock_get_user_grades($attforblock, $userid=0) {
    global $CFG, $DB;
    
    require_once($CFG->dirroot . '/mod/attforblock/locallib.php');
	
    if (! $course = $DB->get_record('course', array('id'=>$attforblock->course))) {
        error("Course is misconfigured");
    }

    $result = false;
    if ($userid) {
    	$result = array();
    	$result[$userid]->userid = $userid;
    	$result[$userid]->rawgrade = $attforblock->grade * get_percent($userid, $course) / 100;
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
    	$students = get_users_for_attendance($context);
        if (!empty($students)) {
    		$result = array();
    		foreach ($students as $student) {
		    	$result[$student->id]->userid = $student->id;
		    	$result[$student->id]->rawgrade = $attforblock->grade * get_percent($student->id, $course) / 100;
    		}
    	}
    }

    return $result;
}

/**
 * Update grades by firing grade_updated event
 *
 * @param object $attforblock null means all attforblocks
 * @param int $userid specific user only, 0 mean all
 */
function attforblock_update_grades($attforblock=null, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    if ($attforblock != null) {
        if ($grades = attforblock_get_user_grades($attforblock, $userid)) {
            foreach($grades as $k=>$v) {
                if ($v->rawgrade == -1) {
                    $grades[$k]->rawgrade = null;
                }
            }
            attforblock_grade_item_update($attforblock, $grades);
        } else {
            attforblock_grade_item_update($attforblock);
        }

    } else {
        $sql = "SELECT a.*";
            $sql.= ", cm.idnumber as cmidnumber";
            $sql.= ", a.course as courseid";
        $sql.= " FROM {attforblock} a";
            $sql.= ", {course_modules} cm";
            $sql.= ", {modules} m";
        $sql.= " WHERE m.name='attforblock'";
            $sql.= " AND m.id=cm.module";
            $sql.= " AND cm.instance=a.id";
        $rs = $DB->get_recordset_sql($sql);
        foreach($rs as $record) {
            attforblock_update_grades($record);
        }
        $rs->close();
    }
}

/**
 * Create grade item for given attforblock
 *
 * @param object $attforblock object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function attforblock_grade_item_update($attforblock, $grades=NULL) {
    global $CFG, $DB;
    
    require_once($CFG->dirroot . '/mod/attforblock/locallib.php');
	
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    if (!isset($attforblock->courseid)) {
        $attforblock->courseid = $attforblock->course;
    }
    if (! $course = $DB->get_record('course', array('id'=>$attforblock->course))) {
        error("Course is misconfigured");
    }
    //$attforblock->grade = get_maxgrade($course);

    if(!empty($attforblock->cmidnumber)){
        $params = array('itemname'=>$attforblock->name, 'idnumber'=>$attforblock->cmidnumber);
    }else{
        // MDL-14303
        $cm = get_coursemodule_from_instance('attforblock', $attforblock->id);
        $params = array('itemname'=>$attforblock->name, 'idnumber'=>$cm->id);
    }
    
    if ($attforblock->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $attforblock->grade;
        $params['grademin']  = 0;

    } 
    else if ($attforblock->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$attforblock->grade;

    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/attforblock', $attforblock->courseid, 'mod', 'attforblock', $attforblock->id, 0, $grades, $params);
}

/**
 * Delete grade item for given attforblock
 *
 * @param object $attforblock object
 * @return object attforblock
 */
function attforblock_grade_item_delete($attforblock) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if (!isset($attforblock->courseid)) {
        $attforblock->courseid = $attforblock->course;
    }

    return grade_update('mod/attforblock', $attforblock->courseid, 'mod', 'attforblock', $attforblock->id, 0, NULL, array('deleted'=>1));
}

function attforblock_get_participants($attforblockid) {
//Must return an array of user records (all data) who are participants
//for a given instance of attforblock. Must include every user involved
//in the instance, independient of his role (student, teacher, admin...)
//See other modules as example.

    return false;
}

function attforblock_scale_used ($attforblockid, $scaleid) {
//This function returns if a scale is being used by one attforblock
//it it has support for grading and scales. Commented code should be
//modified if necessary. See forum, glossary or journal modules
//as reference.
   
    $return = false;
   
    return $return;
}


function attforblock_scale_used_anywhere ($scaleid) {
//This function returns if a scale is being used by one attforblock
//it it has support for grading and scales. Commented code should be
//modified if necessary. See forum, glossary or journal modules
//as reference.
   
   
    return false;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other attforblock functions go here.  Each of them must have a name that 
/// starts with attforblock_


/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function attforblock_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:               return false;

        case FEATURE_BACKUP_MOODLE2:          return true;
            
        default: return null;
    }
}

?>