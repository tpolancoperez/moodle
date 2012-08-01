<?php // $Id: lib.php,v 1.1.2.10 2009/10/22 14:28:23 jfilip Exp $

/**
 * Elluminate Live! Module
 *
 * Allows Elluminate Live! meetings to be created and managed on an
 * Elluminate Live! server via a Moodle activity module.
 *
 * @version $Id: lib.php,v 1.1.2.10 2009/10/22 14:28:23 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


/**
 * Elluminate Live! role types.
 */
    define('ELLUMINATELIVE_ROLE_SERVER_ADIMINISTRATOR', 0);
    define('ELLUMINATELIVE_ROLE_APPADMIN',              1);
    define('ELLUMINATELIVE_ROLE_MODERATOR',             2);
    define('ELLUMINATELIVE_ROLE_PARTICIPANT',           3);


/**
 * Elluminate Live! boundary time values (in minutes).
 */
    $elluminatelive_boundary_times = array(
        0  => get_string('choose'),
        15 => '15',
        30 => '30',
        45 => '45',
        60 => '60'
    );


/**
 * Elluminate Live! boundary time default value.
 */
    define('ELLUMINATELIVE_BOUNDARY_DEFAULT', 15);


/**
 * Elluminate Live! seat reservation enabled string.
 */
    define('ELLUMINATELIVE_SEAT_RESERVATION_ENABLED', 'preferred');


/**
 * Elluminate Live! recording values.
 */
    define('ELLUMINATELIVE_RECORDING_NONE',      0);
    define('ELLUMINATELIVE_RECORDING_MANUAL',    1);
    define('ELLUMINATELIVE_RECORDING_AUTOMATIC', 3);

    define('ELLUMINATELIVE_RECORDING_NONE_NAME',      'off');
    define('ELLUMINATELIVE_RECORDING_MANUAL_NAME',    'remote');
    define('ELLUMINATELIVE_RECORDING_AUTOMATIC_NAME', 'on');


/**
 * The Elluminate Live! XML namespace.
 */
    define('ELLUMINATELIVE_XMLNS', 'http://www.soapware.org/');


/**
 * How many times should we attempt to create an Elluminate Live! user account by adding an
 * increasing integer on the end of a user name?
 */
    define('ELLUMINATELIVE_CREATE_USER_TRIES', 20);


/**
 * The amount of time after which we consider a meeting creation attempt to have failed.
 */
    define('ELLUMINATELIVE_SYNC_TIMEOUT', MINSECS * 10);


/**
 * Define the content types for preload files.
 */
    define('ELLUMINATELIVE_PRELOAD_WHITEBOARD', 'whiteboard');
    define('ELLUMINATELIVE_PRELOAD_MEDIA',      'media');


/**
 * Set to true so that any reminded added will have its time value set to trigger a message sent
 * at the next cron run.
 */
    define('REMINDER_DEBUG', false);


/**
 * Reminder types
 */
    define('REMINDER_TYPE_DELTA',    0);
    define('REMINDER_TYPE_INTERVAL', 1);


/**
 * When adding a new event, we can't calculate the number of available days so
 * we just give a fairly large number of days to choose from.  The actual
 * value can be edited with the event later and then only an appropriate number
 * of days will be avaiable for chosing.
 */
    define('REMINDER_DELTA_DEFAULT', DAYSECS);

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_DESCRIPTION
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function elluminatelive_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        default: return null;
    }
}


    function elluminatelive_install() {
        global $DB;
        $result  = true;
        $timenow = time();
        $sysctx  = get_context_instance(CONTEXT_SYSTEM);
        $adminrid          = $DB->get_field('role', 'id', 'shortname', 'admin');
        $coursecreatorrid  = $DB->get_field('role', 'id', 'shortname', 'coursecreator');
        $editingteacherrid = $DB->get_field('role', 'id', 'shortname', 'editingteacher');
        $teacherrid        = $DB->get_field('role', 'id', 'shortname', 'teacher');

    /// Fully setup the Elluminate Moderator role.
        if ($result && !$mrole = $DB->get_record('role', array('shortname'=>'elluminatemoderator'))) {
            if ($rid = create_role(get_string('elluminatemoderator', 'elluminatelive'), 'elluminatemoderator',
                                   get_string('elluminatemoderatordescription', 'elluminatelive'))) {

                $mrole  = $DB->get_record('role', array('id'=>$rid));
                $result = $result && assign_capability('mod/elluminatelive:moderatemeeting', CAP_ALLOW, $mrole->id, $sysctx->id);
            } else {
                $result = false;
            }
        }

        if (!count_records('role_allow_assign', 'allowassign', $mrole->id)) {
            $result = $result && allow_assign($adminrid, $mrole->id);
            $result = $result && allow_assign($coursecreatorrid, $mrole->id);
            $result = $result && allow_assign($editingteacherrid, $mrole->id);
            $result = $result && allow_assign($teacherrid, $mrole->id);
        }


    /// Fully setup the Elluminate Participant role.
        if ($result && !$prole = $DB->get_record('role', array('shortname'=>'elluminateparticipant'))) {
            if ($rid = create_role(get_string('elluminateparticipant', 'elluminatelive'), 'elluminateparticipant',
                                   get_string('elluminateparticipantdescription', 'elluminatelive'))) {

                $prole  = $DB->get_record('role', array('id'=>$rid));
                $result = $result && assign_capability('mod/elluminatelive:joinmeeting', CAP_ALLOW, $prole->id, $sysctx->id);
            } else {
                $result = false;
            }
        }

        if (!count_records('role_allow_assign', 'allowassign', $prole->id)) {
            $result = $result && allow_assign($adminrid, $prole->id);
            $result = $result && allow_assign($coursecreatorrid, $prole->id);
            $result = $result && allow_assign($editingteacherrid, $prole->id);
            $result = $result && allow_assign($teacherrid, $prole->id);
        }

        return $result;
    }


    function elluminatelive_add_instance($elluminatelive, $facilitatorid = false) {
        global $CFG, $USER, $DB;

        if (!$facilitatorid) {
            $facilitatorid = $USER->id;
        }

    /// The start and end times don't make sense.
        if ($elluminatelive->timestart > $elluminatelive->timeend) {
            $a = new stdClass;
            $a->timestart = userdate($elluminatelive->timestart);
            $a->timeend   = userdate($elluminatelive->timeend);

            redirect($CFG->wwwroot . '/course/mod.php?id=' . $elluminatelive->course . '&amp;section=' .
                     $elluminatelive->section . '&amp;sesskey=' . $USER->sesskey . '&amp;add=elluminatelive',
                     get_string('invalidsessiontimes', 'elluminatelive', $a), 5);
        }

        //$elluminatelive->timemodified = time();
        //$elluminatelive->id           = $elluminatelive->instance;
        $timenow = time();	

	if($elluminatelive->timestart == $elluminatelive->timeend) {
		$a = new stdClass;
		$a->timestart = userdate($elluminatelive->timestart);
		$a->timeend = userdate($elluminatelive->timeend);

              redirect($CFG->wwwroot . '/course/mod.php?id=' . $elluminatelive->course . '&amp;section=' .
                     $elluminatelive->section . '&amp;sesskey=' . $USER->sesskey . '&amp;add=elluminatelive',
                     get_string('samesessiontimes', 'elluminatelive', $a), 5);
	}

       // remarked this out because if a user sits on the entry screen more than a little while this error will always occur.
	//If the start time has changed, check that it's  not before now
	//if($elluminatelive->timestart != $meeting->timestart) {
	//	if($elluminatelive->timestart < $timenow) {
	//		$a = new stdClass;
	//		$a->timestart = userdate($elluminatelive->timestart);
	//		$a->timeend = userdate($elluminatelive->timeend);
	//
       //            redirect($CFG->wwwroot . '/course/mod.php?id=' . $elluminatelive->course . '&amp;section=' .
       //                  $elluminatelive->section . '&amp;sesskey=' . $USER->sesskey . '&amp;add=elluminatelive',
       //                 get_string('starttimebeforenow', 'elluminatelive', $a), 5);
	//	}
	//}

	$yearinseconds = 31536000;
	$timedif = $elluminatelive->timeend - $elluminatelive->timestart;		
	if($timedif > $yearinseconds) {
		$a = new stdClass;
		$a->timestart = userdate($elluminatelive->timestart);
		$a->timeend = userdate($elluminatelive->timeend);

              redirect($CFG->wwwroot . '/course/mod.php?id=' . $elluminatelive->course . '&amp;section=' .
                  $elluminatelive->section . '&amp;sesskey=' . $USER->sesskey . '&amp;add=elluminatelive',
                  get_string('meetinglessthanyear', 'elluminatelive', $a), 5);

	}
	
	$year_later = $timenow + $yearinseconds;
	if($elluminatelive->timestart > $year_later) {
		$a = new stdClass;
		$a->timestart = userdate($elluminatelive->timestart);
		$a->timeend = userdate($elluminatelive->timeend);

              redirect($CFG->wwwroot . '/course/mod.php?id=' . $elluminatelive->course . '&amp;section=' .
                  $elluminatelive->section . '&amp;sesskey=' . $USER->sesskey . '&amp;add=elluminatelive',
                  get_string('meetingstartoverayear', 'elluminatelive', $a), 5);
		
	}

        if (empty($elluminatelive->sessionname) && empty($elluminatelive->customname)) {
            $elluminatelive->sessionname = $elluminatelive->name;
        } else if (!empty($elluminatelive->customname)) {
            $elluminatelive->sessionname = '';
        }

        if (empty($elluminatelive->creator)) {
            $elluminatelive->creator = $USER->id;
        }

        if ($elluminatelive->groupmode == NOGROUPS || empty($ellumiantelive->customname)) {
            $ellumiantelive->customname = 0;
        }
        if ($elluminatelive->groupmode == NOGROUPS || empty($ellumiantelive->customdescription)) {
            $ellumiantelive->customdescription = 0;
        }
        if (empty($elluminatelive->boundarytimedisplay)) {
            $elluminatelive->boundarytimedisplay = 0;
        }

        $elluminatelive->timemodified = time();

        if (!$elluminatelive->id = $DB->insert_record('elluminatelive', $elluminatelive)) {
            return false;
        }

        if (!elluminatelive_cal_edit($elluminatelive->id)) {
            return false;
        }

        elluminatelive_grade_item_update($elluminatelive);

        return $elluminatelive->id;
    }


    function elluminatelive_update_instance($elluminatelive) {
        global $CFG, $USER, $DB;

        $meeting = $DB->get_record('elluminatelive', array('id'=>$elluminatelive->instance));

        if (empty($elluminatelive->sessionname) && empty($elluminatelive->customname)) {
            $elluminatelive->sessionname = $elluminatelive->name;
        } else if (!empty($elluminatelive->customname)) {
            $elluminatelive->sessionname = '';
        }

        $elluminatelive->timemodified = time();
        $elluminatelive->id           = $elluminatelive->instance;
        $timenow = time();	

    /// The start and end times don't make sense.
        if ($elluminatelive->timestart > $elluminatelive->timeend) {
		$a = new stdClass;
		$a->timestart = userdate($elluminatelive->timestart);
		$a->timeend = userdate($elluminatelive->timeend);
		
		redirect($CFG->wwwroot . '/course/modedit.php?update=' . $elluminatelive->coursemodule . '&amp;return=1' 
					, get_string('invalidsessiontimes', 'elluminatelive', $a), 5);
        }

	if($elluminatelive->timestart == $elluminatelive->timeend) {
		$a = new stdClass;
		$a->timestart = userdate($elluminatelive->timestart);
		$a->timeend = userdate($elluminatelive->timeend);

		redirect($CFG->wwwroot . '/course/modedit.php?update=' . $elluminatelive->coursemodule . '&amp;return=1' 
					, get_string('samesessiontimes', 'elluminatelive', $a), 5);		
	}

	//If the start time has changed, check that it's  not before now
	if($elluminatelive->timestart != $meeting->timestart) {
		if($elluminatelive->timestart < $timenow) {
			$a = new stdClass;
			$a->timestart = userdate($elluminatelive->timestart);
			$a->timeend = userdate($elluminatelive->timeend);
	
			redirect($CFG->wwwroot . '/course/mod.php?id=' . $elluminatelive->course . '&amp;section=' .
			$elluminatelive->section . '&amp;sesskey=' . $USER->sesskey . '&amp;add=elluminatelive', get_string('starttimebeforenow', 'elluminate', $a), 5);		
		}
	}

	$yearinseconds = 31536000;
	$timedif = $elluminatelive->timeend - $elluminatelive->timestart;		
	if($timedif > $yearinseconds) {
		$a = new stdClass;
		$a->timestart = userdate($elluminatelive->timestart);
		$a->timeend = userdate($elluminatelive->timeend);

		redirect($CFG->wwwroot . '/course/modedit.php?update=' . $elluminatelive->coursemodule . '&amp;return=1' 
				, get_string('meetinglessthanyear', 'elluminatelive', $a), 5);			
	}
	
	$year_later = $timenow + $yearinseconds;
	if($elluminatelive->timestart > $year_later) {
		$a = new stdClass;
		$a->timestart = userdate($elluminatelive->timestart);
		$a->timeend = userdate($elluminatelive->timeend);
		
		redirect($CFG->wwwroot . '/course/mod.php?id=' . $elluminatelive->course . '&amp;section=' .
		$elluminatelive->section . '&amp;sesskey=' . $USER->sesskey . '&amp;add=elluminatelive', get_string('meetingstartoverayear', 'elluminatelive', $a), 5);	
	}


        // All these conditions handled by the improved code above.
        /// Get the course module ID for this instance.
	 //		$params=array($elluminatelive->id);
        //  $sql = "SELECT cm.id
        //          FROM {modules} m,
        //            {course_modules} cm
        //          WHERE m.name = 'elluminatelive'
        //          AND cm.module = m.id
        //          AND cm.instance = '?'";

        //  if (!$cmid = get_field_sql($sql,$params)) {
        //      redirect($CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $elluminatelive->id,
        //               'The meeting start time of ' . userdate($elluminatelive->timestart) .
        //               ' is after the meeting end time of ' . userdate($elluminatelive->timeend), 5);
        //  }

        //  redirect($CFG->wwwroot . '/course/mod.php?update=' . $cmid . '&amp;return=true&amp;' .
        //           'sesskey=' . $USER->sesskey,
        //           'The meeting start time of ' . userdate($elluminatelive->timestart) .
        //           ' is after the meeting end time of ' . userdate($elluminatelive->timeend), 5);
        //}

    /// If the grade value for attendance has changed, modify any existing attendance records.
        if ($elluminatelive->grade != $meeting->grade) {
            if ($attendance = $DB->get_records('elluminatelive_attendance', array('elluminateliveid'=>$meeting->id))) {
                foreach ($attendance as $attendee) {
                    if ($attendee->grade > 0) {
                    /// We're using a scale.
                        if ($elluminatelive->grade < 0) {
                            $grades = make_grades_menu($elluminatelive->grade);
                            $attendee->grade = key($grades);

                    /// We're using a numerical value.
                        } else {
                            $attendee->grade = $elluminatelive->grade;
                        }

                        $DB->update_record('elluminatelive_attendance', $attendee);
                        elluminatelive_update_grades($elluminatelive, $attendee->userid);
                    }
                }
            }
        }

        if ($elluminatelive->groupmode == NOGROUPS || empty($elluminatelive->customname)) {
            $elluminatelive->customname = 0;
        }
        if ($elluminatelive->groupmode == NOGROUPS || empty($elluminatelive->customdescription)) {
            $elluminatelive->customdescription = 0;
        }
        if (empty($elluminatelive->boundarytimedisplay)) {
            $elluminatelive->boundarytimedisplay = 0;
        }

        $DB->update_record('elluminatelive', $elluminatelive);

        if (!elluminatelive_cal_edit($elluminatelive->id)) {
            return false;
        }

        elluminatelive_grade_item_update($elluminatelive);

        return true;
    }


    function elluminatelive_delete_instance($id) {
    /// Given an ID of an instance of this module,
    /// this function will permanently delete the instance
    /// and any data that depends on it.
		global $DB;

        if (!$elluminatelive = $DB->get_record('elluminatelive', array('id'=>$id))) {
            return false;
        }

        if ($meetings = $DB->get_records('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id))) {
            foreach ($meetings as $meeting) {
                if (elluminatelive_delete_meeting($meeting->meetingid)) {
                /// Delete all the event records assosciated with this meeting.
                    elluminatelive_cal_edit($meeting->meetingid, true);

                /// Clean up any recordings stored for this meeting.
                    $DB->delete_records('elluminatelive_recordings', array('meetingid'=>$meeting->meetingid));
                }
            }

        /// Clean up all of the existing sessions.
            $DB->delete_records('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id));
        }

    /// Delete attendance records.
        $DB->delete_records('elluminatelive_attendance', array('elluminateliveid'=>$elluminatelive->id));

        if (!$DB->delete_records('elluminatelive', array('id'=>$id))) {
            return false;
        }

        elluminatelive_grade_item_delete($elluminatelive);

        return true;
    }


    function display_grade($grade, $elluminatelive) {

        global $DB;

        static $scalegrades = array();   // Cache scales for each assignment - they might have different scales!!

        if ($elluminatelive->grade >= 0) {    // Normal number
            if ($grade == -1) {
                return '-';
            } else {
                return $grade.' / '.$elluminatelive->grade;
            }

        } else {                                // Scale
            if (empty($scalegrades[$elluminatelive->id])) {
                if ($scale = $DB->get_record('scale', 'id', -($elluminatelive->grade))) {
                    $scalegrades[$elluminatelive->id] = make_menu_from_list($scale->scale);
                } else {
                    return '-';
                }
            }
            if (isset($scalegrades[$elluminatelive->id][$grade])) {
                return $scalegrades[$elluminatelive->id][$grade];
            }
            return '-';
        }
    }


    function elluminatelive_user_outline($course, $user, $mod, $elluminatelive) {
    /// Return a small object with summary information about what a
    /// user has done with a given particular instance of this module
    /// Used for user activity reports.
    /// $return->time = the time they did it
    /// $return->info = a short text description

	global $DB;

        if ($attendance = $DB->get_record('elluminatelive_attendance', array('userid'=>$user->id,'elluminateliveid'=>$elluminatelive->id))) {
            $result = new stdClass;
            $result->info = get_string('grade').': '.display_grade($attendance->grade, $elluminatelive);
            $result->time = $attendance->timemodified;
            return $result;
        }
        return NULL;
    }


    function elluminatelive_user_complete($course, $user, $mod, $elluminatelive) {
    /// Print a detailed representation of what a  user has done with
    /// a given particular instance of this module, for user activity reports.
	global $DB;
        if ($attendance = $DB->get_record('elluminatelive_attendance', array('userid'=>$user->id,'elluminateliveid'=>$elluminatelive->id))) {
            print_simple_box_start();
            echo get_string('attended', 'elluminatelive'). ': ';
            echo userdate($attendance->timemodified);
            print_simple_box_end();
        } else {
            print_string('notattendedyet', 'elluminatelive');
        }
    }


    function elluminatelive_print_recent_activity($course, $isteacher, $timestart) {
    /// Given a course and a time, this module should find recent activity
    /// that has occurred in elluminatelive activities and print it out.
    /// Return true if there was output, or false is there was none.

        global $CFG, $DB;

        $content  = false;
        $meetings = NULL;

		$select = "time > ? AND course = ? AND module = ? AND action = ?";
		$sqlparams=array($timestart,$course->id,'elluminatelive','view.meeting');

        if (!$logs = $DB->get_records_select('log', $select, $sqlparams, 'time ASC')) {
            return false;
        }

        foreach ($logs as $log) {
            //Create a temp valid module structure (course,id)
            $tempmod = new stdClass;
            $tempmod->course = $log->course;
            $tempmod->id     = $log->info;
            //Obtain the visible property from the instance
            $modvisible = instance_is_visible($log->module,$tempmod);

            //Only if the mod is visible
            if ($modvisible) {
				$params=array($log->info,$log->userid);
                $sql = "SELECT e.name, u.firstname, u.lastname
                        FROM {elluminatelive} e,
                        {user} u
                        WHERE e.id = '?'
                        AND u.id = ?";
                $meetings[$log->info] = $DB->get_record_sql($sql,$params);
                $meetings[$log->info]->time = $log->time;
                $meetings[$log->info]->url  = str_replace('&', '&amp;', $log->url);
            }
        }

        if ($meetings) {
            print_headline(get_string('newsubmissions', 'assignment').':');
            foreach ($meetings as $meeting) {
                print_recent_activity_note($meeting->time, $meeting, $isteacher, stripslashes($meeting->name),
                                           $CFG->wwwroot.'/mod/elluminatelive/'.$meeting->url);
            }
            $content = true;
        }

        return $content;
    }


    function elluminatelive_cron () {
    /// Function to be run periodically according to the moodle cron
    /// This function searches for things that need to be done, such
    /// as sending out mail, toggling flags etc ...

        global $CFG,$DB;

    /// If the plug-in is not configured to coonect to Elluminate, return.
        if (empty($CFG->elluminatelive_auth_username) || empty($CFG->elluminatelive_auth_username)) {
            return true;
        }

        $timenow = time();

		$params=array($timenow);
        $sql = "SELECT es.id, es.meetingid
                FROM {elluminatelive} el
                INNER JOIN {elluminatelive_session} es ON es.elluminatelive = el.id
                WHERE el.timestart <= ?";

    /// Ensure that any new recordings on the server are stored for meetings created by Moodle.
        if ($sessions = $DB->get_records_sql($sql,$params)) {
            $min = date('i');
            $count = count($sessions);
        	$split = ceil($count/6);
            if ($min < 20) {
                    $start = 0;
                    $end = ($split*2)-1;
            } else if ($min < 40) {
                    $start = $split*2;
                    $end = ($split*4)-1;
            } else {
                    $start = $split*4;
                    $end = $count;
            }
            
            
            $i = 0;
            
            foreach ($sessions as $session) {
                if ($i >= $start && $i <= $end) {
                    $filter = 'meetingId = ' . $session->meetingid;
    
                    if ($recordings = elluminatelive_list_recordings($filter)) {
                        foreach ($recordings as $recording) {
                            if ($DB->record_exists('elluminatelive_session', array('meetingid'=>$recording->meetingid))) {
                                if (!$DB->record_exists('elluminatelive_recordings', array('recordingid'=>$recording->recordingid))) {
                                    $er = new stdClass;
                                    $er->meetingid   = $recording->meetingid;
                                    $er->recordingid = $recording->recordingid;
                                    $er->created     = $recording->created;
    
                                    $DB->insert_record('elluminatelive_recordings', $er);
                                }
                            }
                        }
                    }
                }
                $i++;
            }
        }

    /// If a user account duration was specified, delete any student accounts that have
    /// passed the duration deadline.
        if (!empty($CFG->elluminatelive_user_duration)) {
            $duration = $CFG->elluminatelive_user_duration * 24 * 60 * 60;
            $select   = '(' . $timenow . ' - timecreated) > ' . $duration;

            if ($users = $DB->get_records_select('elluminatelive_users', $select)) {
                foreach ($users as $user) {
                    elluminatelive_delete_user($user->elm_id);
                }
            }
        }

        return true;
    }


    function elluminatelive_grades($elluminateliveid) {
		global $DB;
        if (!$elluminatelive = $DB->get_record('elluminatelive', array('id'=>$elluminateliveid))) {
            return NULL;
        }

        if ($elluminatelive->grade == 0) { // No grading
            return NULL;
        }

        $return = new stdClass;

        $grades = $DB->get_records_menu('elluminatelive_attendance', array('elluminateliveid'=>$elluminateliveid), '', 'userid,grade');

        if ($elluminatelive->grade > 0) {
            if ($grades) {
                foreach ($grades as $userid => $grade) {
                    if ($grade == -1) {
                        $grades[$userid] = '-';
                    }
                }
            }
            $return->grades   = $grades;
            $return->maxgrade = $elluminatelive->grade;

        } else { // Scale
            if ($grades) {
                $scaleid = - ($elluminatelive->grade);
                $maxgrade = "";
                if ($scale = $DB->get_record('scale', array('id'=>$scaleid))) {
                    $scalegrades = make_menu_from_list($scale->scale);
                    foreach ($grades as $userid => $grade) {
                        if (empty($scalegrades[$grade])) {
                            $grades[$userid] = '-';
                        } else {
                            $grades[$userid] = $scalegrades[$grade];
                        }
                    }
                    $maxgrade = $scale->name;
                }
            }
            $return->grades   = $grades;
            $return->maxgrade = $maxgrade;
        }

        return $return;
    }

    /**
     * Update grades by firing grade_updated event
     *
     * @param object $elluminatelive null means all elluminatelives
     * @param int $userid specific user only, 0 mean all
     */
    function elluminatelive_update_grades($elluminatelive=null, $userid=0, $nullifnone=true) {
        global $CFG,$DB;

        if (!function_exists('grade_update')) { //workaround for buggy PHP versions
            require_once($CFG->libdir . '/gradelib.php');
        }

        if ($elluminatelive != null) {
            if ($grades = elluminatelive_get_user_grades($elluminatelive, $userid)) {
                foreach($grades as $k=>$v) {
                    if ($v->rawgrade == -1) {
                        $grades[$k]->rawgrade = null;
                    }
                }
                elluminatelive_grade_item_update($elluminatelive, $grades);
            } else {
                elluminatelive_grade_item_update($elluminatelive);
            }

        } else {
            $sql = "SELECT a.*, cm.idnumber as cmidnumber, a.course as courseid
                    FROM {elluminatelive} a
                    INNER JOIN {course_modules} cm ON cm.instance = a.id
                    INNER JOIN {modules} m ON m.id = cm.module
                    WHERE m.name='elluminatelive'";

            if ($rs = $DB->get_recordset_sql($sql)) {
                foreach ($rs as $elluminatelive) {
                    if ($elluminatelive->grade != 0) {
                        elluminatelive_update_grades($elluminatelive);
                    } else {
                        elluminatelive_grade_item_update($elluminatelive);
                    }
                }
				$rs->close();
            }
        }
    }

    /**
     * Return grade for given user or all users.
     *
     * @param int $elluminateliveid id of elluminatelive
     * @param int $userid optional user id, 0 means all users
     * @return array array of grades, false if none
     */
    function elluminatelive_get_user_grades($elluminatelive, $userid=0) {
        global $CFG,$DB;

		$params=array($elluminatelive->id);
        $sql = "SELECT u.id, u.id AS userid, ea.grade AS rawgrade, ea.timemodified AS dategraded
                FROM {user} u
                INNER JOIN {elluminatelive_attendance} ea ON ea.userid = u.id
                WHERE ea.elluminateliveid = ?";

		if ($userid) {
			$sql=$sql." AND u.id = ?";
			$params=array($elluminatelive->id,$userid);
		}

        return $DB->get_records_sql($sql,$params);
    }

    /**
     * Create grade item for given elluminatelive
     *
     * @param object $elluminatelive object with extra cmidnumber
     * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
     * @return int 0 if ok, error code otherwise
     */
    function elluminatelive_grade_item_update($elluminatelive, $grades = NULL) {
        global $CFG;

        if (!function_exists('grade_update')) { //workaround for buggy PHP versions
            require_once $CFG->libdir . '/gradelib.php';
        }

        if (!isset($elluminatelive->courseid)) {
            $elluminatelive->courseid = $elluminatelive->course;
        }

        if (empty($elluminatelive->cmidnumber)) {
            if ($cm = get_coursemodule_from_instance('elluminatelive', $elluminatelive->id)) {
                $elluminatelive->cmidnumber = $cm->id;
            }
        }

        $params = array('itemname' => $elluminatelive->name);

        if (!empty($elluminatelive->cmidnumber)) {
            $params['idnumber'] = $elluminatelive->cmidnumber;
        }

        if ($elluminatelive->grade > 0) {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = $elluminatelive->grade;
            $params['grademin']  = 0;

        } else if ($elluminatelive->grade < 0) {
            $params['gradetype'] = GRADE_TYPE_SCALE;
            $params['scaleid']   = -$elluminatelive->grade;

        } else {
            $params['gradetype'] = GRADE_TYPE_TEXT; // allow text comments only
        }

        if ($grades  === 'reset') {
            $params['reset'] = true;
            $grades = NULL;
        }

        return grade_update('mod/elluminatelive', $elluminatelive->courseid, 'mod', 'elluminatelive',
                            $elluminatelive->id, 0, $grades, $params);
    }

    /**
     * Delete grade item for given elluminatelive
     *
     * @param object $elluminatelive object
     * @return object elluminatelive
     */
    function elluminatelive_grade_item_delete($elluminatelive) {
        global $CFG;
        require_once($CFG->libdir.'/gradelib.php');

        if (!isset($elluminatelive->courseid)) {
            $elluminatelive->courseid = $elluminatelive->course;
        }

        return grade_update('mod/elluminatelive', $elluminatelive->courseid, 'mod', 'elluminatelive',
                            $elluminatelive->id, 0, NULL, array('deleted' => 1));
    }

    function elluminatelive_get_participants($elluminateliveid) {
    //Must return an array of user records (all data) who are participants
    //for a given instance of elluminatelive. Must include every user involved
    //in the instance, independient of his role (student, teacher, admin...)
    //See other modules as example.

		global $DB;

        if (!$meeting = $DB->get_record('elluminatelive', array('id'=>$elluminateliveid))) {
            return false;
        }

        $participants = array();
        $cm           = get_coursemodule_from_instance('elluminatelive', $meeting->id, $meeting->course);
        $ctx          = get_context_instance(CONTEXT_MODULE, $cm->id);

    /// Get meeting moderators.
        if ($users = get_users_by_capability($ctx, 'mod/elluminatelive:moderatemeeting', '',
                                             'u.lastname, u.firstname', '', '', '', '', false)) {

            $participants = $users;
        }

    /// Get meeting participants.
        if ($users = get_users_by_capability($ctx, 'mod/elluminatelive:joinmeeting', '',
                                             'u.lastname, u.firstname', '', '', '', '', false)) {

            foreach ($users as $uid => $user) {
                if (!isset($participants[$uid])) {
                    $participants[$uid] = $user;
                }
            }
        }

    /// Make sure we have the meeting creator as well.
        if (!isset($participants[$meeting->creator])) {
            $participants[$meeting->creator] = $DB->get_record('user', array('id'=>$meeting->creator));
        }

        if (!empty($participants)) {
            return $participants;
        }

        return false;
    }


    function elluminatelive_scale_used ($elluminateliveid,$scaleid) {
    //This function returns if a scale is being used by one elluminatelive
    //it it has support for grading and scales. Commented code should be
    //modified if necessary. See forum, glossary or journal modules
    //as reference.

		global $DB;

        $return = false;

        $rec = $DB->get_record("elluminatelive",array("id"=>$elluminateliveid,"grade"=>-$scaleid));

        if (!empty($rec)  && !empty($scaleid)) {
            $return = true;
        }

        return $return;
    }


/**
 * Checks if scale is being used by any instance of elluminatelive
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any elluminatelive
 */
function elluminatelive_scale_used_anywhere($scaleid) {
	global $DB;
    if ($scaleid and $DB->record_exists('elluminatelive', array('grade'=>$scaleid))) {
        return true;
    } else {
        return false;
    }
}


/**
 * Process the module config options from the main settings page to remove
 * any spaces from the beginning of end of the string input fields.
 *
 * @param &$config Reference to the form config data.
 * @return none
 */
    function elluminatelive_process_options(&$config) {
        $config->server        = trim($config->server);
        $config->adapter       = trim($config->adapter);
        $config->auth_username = trim($config->auth_username);
        $config->auth_password = trim($config->auth_password);
    }


/**
 * Clean up the roles we created during install.
 *
 * @param none
 * @return bool True on success, False otherwise.
 */
    function elluminatelive_uninstall() {
        $result = true;
		global $DB;

        if ($mrole = $DB->get_record('role', array('shortname'=>'elluminatemoderator'))) {
            $result = $result && delete_role($mrole->id);
            $result = $result && $DB->delete_records('role_allow_assign', array('allowassign'=>$mrole->id));
        }

        if ($prole = $DB->get_record('role', array('shortname'=>'elluminateparticipant'))) {
            $result = $result && delete_role($prole->id);
            $result = $result && $DB->delete_records('role_allow_assign', array('allowassign'=>$prole->id));
        }

        return $result;
    }


/**
 * Returns an array of user objects reprsenting the participants for a given
 * meeting.
 *
 * @uses $CFG
 * @param object $meeting   The meeting record to get the participants list for.
 * @param int    $groupid   A comma-separated list of fields to return.
 * @param bool   $moderator Set to True to get moderators for users.
 * @return array An aray of user objects.
 */
    function elluminatelive_get_meeting_participants($meeting, $groupid = 0, $moderator = false) {
        global $CFG,$DB;

        $cm      = get_coursemodule_from_instance('elluminatelive', $meeting->id, $meeting->course);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

        $users = array();

        if ($moderator) {
            $participants = get_users_by_capability($context, 'mod/elluminatelive:moderatemeeting',
                                                    'u.id, u.username', 'u.lastname, u.firstname',
                                                    '', '', $groupid, '', false);

            if (!isset($participants[$meeting->creator])) {
                $participants[$meeting->creator] = $DB->get_field('user', 'username', array('id'=>$meeting->creator));
            }
        } else {
            $participants = get_users_by_capability($context, 'mod/elluminatelive:joinmeeting',
                                                    'u.id, u.username', 'u.lastname, u.firstname',
                                                    '', '', $groupid, '', false);
        }

        if (!empty($participants)) {
            $sql = "SELECT mu.*, eu.elm_id
                    FROM {user} mu
                    LEFT JOIN {elluminatelive_users} eu ON eu.userid = mu.id
                    WHERE mu.id IN (" .  implode(', ', array_keys($participants)) . ")";

            if (!$users = $DB->get_records_sql($sql)) {
                $users = array();
            }
        }

        return $users;
    }


/**
 * Find and return all the events assosciated with a given meeting.
 *
 * An event for a specific meeting has the meeting ID in a SPAN tag
 * surrounding the title of the format:
 *
 * <span id="elm-ID"> ... </span>
 *
 * Where ID is the meeting ID in question.
 *
 * @uses $CFG
 * @param long $meetingid The Elluminate Live! meeting ID.
 * @return array An array of events.
 */
    function elluminatelive_get_events($meetingid) {
        global $CFG,$DB;

		$params=array($meetingid);
        $sql = "SELECT e.*
                FROM {event} e
                WHERE e.modulename = 'elluminatelive'
                AND e.instance = ?";

        $events = $DB->get_records_sql($sql,$params);
        return $events;
    }


    function elluminatelive_has_course_event($meetingid) {
        global $CFG,$DB;

        if (!$meeting = $DB->get_record('elluminatelive', array('id'=>$meetingid))) {
            return false;
        }

		$params=array($meeting->id,$meeting->course);
        $sql = "SELECT *
                FROM {event}
                WHERE modulename = 'elluminatelive'
                AND instance = ?
                AND courseid = ?";

        return $DB->record_exists_sql($sql,$params);
    }


/**
 * Find and return a list of users who currently have events assosciated
 * with a given meeting.  This is useful when either adding new users or
 * deleting users from a private event.
 *
 * @uses $CFG
 * @param long $meetingid The Elluminate Live! meeting record ID.
 * @return array An array of user IDs.
 */
    function elluminatelive_get_event_users($meetingid) {
        global $CFG,$DB;

        if (!$meeting = $DB->get_record('elluminatelive', array('meetingid'=>$meetingid))) {
            return false;
        }

		$params=array($meeting->id,$meeting->course);
        $sql = "SELECT u.id
                FROM {user} u
                LEFT JOIN {event} ON e.userid = u.id
                WHERE e.modulename = 'elluminatelive'
                AND e.instance = ?
                AND e.courseid = ?";

        return $DB->get_records_sql($sql,$params);
    }


/**
 * Add a list of users to a given meeting.  Also handles adding the calendar
 * event and any assosciated reminders for the user.  The latter action can
 * only occur with a private meeting.
 *
 * If $moderators is not set to true, the users will be added as participants.
 *
 * @param object  $meeting    The Elluminate Live! activity database record.
 * @param array   $userids    A list of Moodle user IDs to add to the meeting.
 * @param int     $groupid    The group ID this is specifically for.
 * @param boolean $moderators True if the users being added are moderators.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_add_users($meeting, $userids, $groupid = 0, $moderators = false) {
		global $DB;
        $cm      = get_coursemodule_from_instance('elluminatelive', $meeting->id, $meeting->course);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $session = $DB->get_record('elluminatelive_session', array('elluminatelive'=>$meeting->id,'groupid'=>$groupid));
        $timenow = time();

    /// Basic event record for the database.
        $event = new stdClass;
        $event->name        = get_string('calendarname', 'elluminatelive', stripslashes($meeting->name));
        $event->description = $meeting->description;
        $event->format      = 1;
        $event->courseid    = 0;
        $event->groupid     = 0;
        $event->modulename  = 'elluminatelive';
        $event->instance    = $meeting->id;
        $event->eventtype   = '';
        $event->visible     = 1;
        $event->timestart   = $meeting->timestart;
        $duration           = $meeting->timeend - $meeting->timestart;
        if ($duration < 0) {
            $event->timeduration = 0;
        } else {
            $event->timeduration = $duration;
        }

        if ($moderators) {
            $role = $DB->get_record('role', array('shortname'=>'elluminatemoderator'));
        } else {
            $role = $DB->get_record('role', array('shortname'=>'elluminateparticipant'));
        }

        foreach ($userids as $userid) {

            if (!role_assign($role->id, $userid, $context->id)) {
            //if (!role_assign($role->id, $userid, $groupid, $context->id, $timenow)) {
                return false;
            }

        /// If this meeting already has a session created, make sure this user is added to it.
            if (!empty($session->meetingid)) {
            /// If this user doesn't already have an ELM account, create one for them.
	         if (!$elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$userid))) {
                    if (!elluminatelive_new_user($userid, random_string(10))) {
                        debugging('Could not create new Elluminate Live! user account!', DEBUG_DEVELOPER);
                    }

                    $elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$userid));
                }

                if ($moderators) {
                    if (!elluminatelive_add_participant($session->meetingid, $elmuser->elm_id, true)) {
                        return false;
                    }
                } else {
                    if (!elluminatelive_add_participant($session->meetingid, $elmuser->elm_id, false)) {
                        return false;
                    }

                }
            }

        /// Add a new event for this user.
            $event->userid       = $userid;
            $event->timemodified = time();
            $event = $event;
            $event->id = $DB->insert_record('event', $event);
        }

        return true;
    }


/**
 * Remove a list of users from a given meeting.  Also handles removing the
 * calendar event and any assosciated reminders for the user.  This action
 * can only occur with a private meeting.
 *
 * If $moderators is not set to true, the users will be removed from the
 *
 * @param object  $meeting    The Elluminate Live! activity database record.
 * @param array   $userids    A list of Moodle user IDs to remove from the meeting.
 * @param int     $groupid    The group ID this is specifically for.
 * @param boolean $moderators True if the users being removed are moderators.
 * @param boolean $force      Whether to force an override of the behviour for not deleting the meeting creator.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_del_users($meeting, $userids, $groupid = 0, $moderators = false, $force = false) {
		global $DB;
        $cm      = get_coursemodule_from_instance('elluminatelive', $meeting->id, $meeting->course);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $session = $DB->get_record('elluminatelive_session', array('elluminatelive'=>$meeting->id,'groupid'=>$groupid));
        $timenow = time();

        $muserids = array();

        if ($moderators) {
            $role = $DB->get_record('role', array('shortname'=>'elluminatemoderator'));
        } else {
            $role = $DB->get_record('role', array('shortname'=>'elluminateparticipant'));
        }

    /// Remove each user from the meeting on the Elluminate Live! server.
        foreach ($userids as $userid) {
            if ($userid != $meeting->creator || ($userid == $meeting->creator && $force)) {
                //if (!role_unassign($role->id, $userid, $groupid, $context->id)) {
                if (!role_unassign($role->id, $userid, $context->id)) {
                    continue;
                }

                $muserids[] = $userid;

                if (!empty($session->meetingid) && ($elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$userid)))) {
                    if (!elluminatelive_delete_participant($session->meetingid, $elmuser->elm_id)) {
                        return false;
                    }
                }
            }
        }

        if (empty($muserids)) {
            return true;
        }

    /// Delete user events.
        if (count($muserids) > 1) {
            $select = "modulename = 'elluminatelive' AND instance = {$meeting->id} AND " .
                      "userid IN (" . implode(', ', $muserids) . ")";

            return $DB->delete_records_select('event', $select);
        } else {
            return $DB->delete_records('event', array('modulename'=>'elluminatelive','instance'=>$meeting->id,'userid'=>$userid));
        }
    }


/**
 * Adds or edits an existing calendar event for an assosciated meeting.
 *
 * There aqre two possible meeting configurations:
 * 1. A private meeting where only the people chosen to be particpants
 *    are allowed access.
 * 2. A public meeting where anyone in a given course is allowed to
 *    access to meeting.
 *
 * We must handle adding and removing users to a private meeting and also
 * deleteing unnecessary events when a meeting changes from private to
 * public and vice versa.
 *
 * @uses $CFG
 * @param int $meetingid The meeting ID to edit the calendar event for.
 * @param boolean $delete Whether the meeting is being deleted.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_cal_edit($meetingid, $delete = false) {
        global $CFG,$DB;

        if (!$meeting = $DB->get_record('elluminatelive', array('id'=>$meetingid))) {
            return false;
        }

    /// Special action if we're deleting a meeting.
        if ($delete) {
            if ($events = elluminatelive_get_events($meeting->id)) {
                foreach ($events as $event) {
                    $DB->delete_records('event', array('id'=>$event->id));
                }
            }

            return true;
        }

        if ($meeting->private) {
        /// If this meeting has been newly marked private, delete the old, public,
        /// event record.
            $admin = get_admin();

	//     $params=array($meeting->id,$meeting->course,$admin->id);
       //     $sql = "DELETE FROM {event}
       //             WHERE modulename = 'elluminatelive'
       //             AND instance = ?
       //             AND courseid = ?
       //             AND userid = ?";
       //execute_sql($sql, $params, false);

            $DB->delete_records('event', array('modulename'=>'elluminatelive','instance'=>$meeting->id,'courseid'=>$meeting->course,'userid'=>$admin->id));


        } else if (!$meeting->private && !elluminatelive_has_course_event($meetingid)) {
        /// Create the new course event.
            $admin = get_admin();

            $event = new stdClass;
            $event->name         = get_string('calendarname', 'elluminatelive', $meeting->name);
            $event->description  = $meeting->description;
            $event->format       = 1;
            $event->courseid     = $meeting->course;
            $event->groupid      = 0;
            $event->userid       = $admin->id;
            $event->modulename   = 'elluminatelive';
            $event->instance     = $meeting->id;
            $event->eventtype    = '';
            $event->visible      = 1;
            $event->timestart    = $meeting->timestart;
            $duration            = $meeting->timeend - $meeting->timestart;
            if ($duration < 0){
                $event->timeduration = 0;
            } else {
                $event->timeduration = $duration;
            }
            $event->timemodified = time();
            $event = $event;
            $event->id = $DB->insert_record('event', $event);

    	    return true;
        }
//print_object('$meeting:');
//print_object($meeting);
    /// Make sure that the course_module record actually exists at this point.
        if (!$cm = get_coursemodule_from_instance('elluminatelive', $meeting->id, $meeting->course)) {
            return true;
        }
//print_object('$cm:');
//print_object($cm);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    /// Modifying any existing events.
        if ($events = elluminatelive_get_events($meeting->id)) {
            foreach($events as $event) {
            /// Delete any non-moderator events if this meeting is public...
                $deleted = false;

                if (empty($meeting->private) && empty($event->userid)) {
                    if (!has_capability('mod/elluminatelive:moderatemeeting', $context, $USER->id, false)) {
                        $deleted = $DB->delete_records('event', array('id'=>$event->id));
                    }
                }

                if (!$deleted) {
                    $event->name        = get_string('calendarname', 'elluminatelive', $meeting->name);
                    $event->description = $meeting->description;

                    $event->timestart   = $meeting->timestart;
                    $duration           = $meeting->timeend - $meeting->timestart;

                    if ($duration < 0){
                        $event->timeduration = 0;
                    } else {
                        $event->timeduration = $duration;
                    }

                    $eventtimemodified = time();

                    if (!$DB->update_record('event', $event)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }



/**
 * ===========================================================================
 * The following are all functions dealing with handling reminders attached
 * to a calendar event.
 * ===========================================================================
 */



/**
 * Adds a reminder for a calendar event.
 *
 * Please note that the last three parameters are only necessary when using
 * the REMINDER_TYPE_INTERVAL (1).
 *
 * @param int $eventid ID of the event to add a reminder for.
 * @param int $rtype The type of reminder.
 * @param int $timedelta The time before the event to send the reminder.
 * @param int $timeinterval The time between reminders being sent.
 * @param int $timeend The point past which no reminders will be send (the
 *                     default ending time is the event itself).
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_reminder_add_reminder($eventid, $rtype = 0, $timedelta = 0,
                                                  $timeinterval = 0, $timeend = 0) {
		global $DB;
        // Make sure the event exists
        if (!$event = $DB->get_record('event', array('id'=>intval($eventid)))) {
            print_error('Invalid event ID: ' . $eventid);
        }

        // Just check to make sure we have a valid reminder type
        switch($rtype) {
            case REMINDER_TYPE_DELTA:
            case REMINDER_TYPE_INTERVAL:
                break;
            default:
                return false;
                break;
        }

        // Create the new reminder object for the database
        $reminder = new stdClass;
        $reminder->event        = intval($event->id);
        $reminder->type         = intval($rtype);
        $reminder->timedelta    = intval($timedelta);
        $reminder->timeinterval = intval($timeinterval);
        $reminder->timeend      = intval($timeend);
        $reminder->id = $DB->insert_record('event_reminder', $reminder);

        if (!$reminder->id) {
            return false;
        }

        // Send the reminder immediately (for testing purposes)
        if (REMINDER_DEBUG) {
            elluminatelive_reminder_send($reminder->id);
            elluminatelive_reminder_remove($reminder->id);
        }

        return true;
    }


/**
 * Removes a calendar event reminder.
 *
 * @param int $reminderid ID of the reminder to delete.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_reminder_remove($reminderid) {
		global $DB;
        if (!$DB->delete_records('event_reminder', array('id'=>intval($reminderid)))) {
            return false;
        }

        return true;
    }


/**
 * Edits an existing calendar event reminder.
 *
 * @param int $reminderid ID of the reminder to edit.
 * @param int $rtype The type of reminder.
 * @param int $timedelta The time before the event to send the reminder.
 * @param int $timeinterval The time between reminders being sent.
 * @param int $timeend The point past which no reminders will be send (the
 *                     default ending time is the event itself).
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_reminder_edit($reminderid, $rtype, $timedelta = 0,
                                          $timeinterval = 0, $timeend = 0) {

		global $DB;
        $reminderid   = intval($reminderid);
        $rtype        = intval($rtype);
        $timedelta    = intval($timedelta);
        $timeinterval = intval($timeinterval);
        $timeend      = intval($timeend);

        // Make sure the reminder exists
        if (!$reminder = $DB->get_record('event_reminder', array('id'=>$reminderid))) {
            return false;
        }

        // Modify any parameters that have changed
        if ($rtype and $rtype != $reminder->type) {
            $reminder->type = $rtype;
        }
        if ($timedelta and $timedelta != $reminder->timedelta) {
            $reminder->timedelta = $timedelta;
        }
        if ($timeinterval and $timeinterval != $reminder->timeinterval) {
            $reminder->timeinterval = $timeinterval;
        }
        if ($timeend and $reminder->timeend != $timeend) {
            $reminder->timeend = $timeend;
        }

        // Attempt to update the database record
        if (!$DB->update_record('event_reminder', $reminder)) {
            return false;
        }

        return true;
    }


/**
 * Checks if a calendar event has any reminders assosicated with it and
 * returns them as an array of objects.  If there are no reminders returns
 * NULL instead.
 *
 * @param int $meetingid ID of the event to check for reminders.
 * @return array An array of reminder objects or NULL.
 */
    function elluminatelive_reminder_get_reminders($meetingid) {
		global $DB;
        // Make sure the event exists
        if (!$meeting = $DB->get_record('event', array('id'=>intval($meetingid)))) {
            print_error('Invalid meeting ID: ' . $meetingid);
        }


        // Get records
        if (!$reminders = $DB->get_records('event_reminder', array('event'=>$event->id), 'timedelta ASC')) {
            return NULL;
        }

        return $reminders;
    }


/**
 * Displays the HTML to edit / delete existing reminders and / or to add a new
 * reminder to an Elluminate Live! meeting.
 *
 * @param int $meetingid ID of the meeting HTML form we are drawing into (leave
 *                       blank to just draw the form elements to add a new
 *                       reminder.
 * @return none
 */
    function elluminatelive_reminder_draw_form($meetingid = 0) {
		global $DB;
        if ($meetingid) {
            if (!$meeting = $DB->get_record('elluminatelive', array('id'=>intval($meetingid)))) {
                return;
            }
        }

        // Setup available number of days and hours
        $days  = array();
        $hours = array();

        // How many days can we choose from before the event in question?
        if ($meetingid) {
            $delta = $elluminatelive->meetingtimebegin - time();
        } else {
            $delta = REMINDER_DELTA_DEFAULT;
        }

        // Setup values for the select lists on the form
        $dayscount = floor($delta / (24 * 60 * 60));

        for ($i = 0; $i <= $dayscount; $i++) {
            $days[] = $i;
        }
        for ($i = 0; $i < 24; $i++) {
            $hours[] = $i;
        }

        // Print out any existing event reminders
        if($meetingid) {
            if ($reminders = elluminatelive_reminder_get_reminders($meeting->id)) {
?>
      <tr>
        <td></td>
        <td>
          <fieldset>
            <legend><?php print_string('formreminders', 'event_reminder'); ?></legend>
<?php
                foreach($reminders as $reminder) {
                    $remindername = 'reminder' . $reminder->id;

?>
            <div>
              <input type="checkbox" name="reminderdeleteids[]" value="<?php echo $reminder->id; ?>" />
              <?php print_string('formtimebeforeevent', 'event_reminder'); ?>
<?php

                $day  = floor($reminder->timedelta / (24 * 60 * 60));
                $hour = ($reminder->timedelta - ($day * 24 * 60 * 60)) / (60 * 60);

                choose_from_menu($days, $remindername . '_days', $day, '');
                print_string('formdays', 'event_reminder');
                choose_from_menu($hours, $remindername . '_hours', $hour, '');
                print_string('formhours', 'event_reminder');

?>
            </div>
            <br />
<?php

                    }

?>
            <input type="submit" name="reminder_delete" value="<?php print_string('formdeleteselectedreminders', 'event_reminder'); ?>" />
<?php

                }
            }

?>
          </fieldset>
        </td>
      </tr>
      <tr>
        <td colspan="2">
            <hr />
            <p><?php print_string('formaddnewreminder', 'event_reminder'); ?></p>
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <div>
            <?php print_string('formtimebeforeevent', 'event_reminder'); ?>
<?php

            choose_from_menu($days, 'remindernew_days', '', '');
            print_string('formdays', 'event_reminder');
            choose_from_menu($hours, 'remindernew_hours', '', '');
            print_string('formhours', 'event_reminder');

?>
          </div>
        </td>
      </tr>
<?php

    }


/**
 * Determines the type of event and returns that type as a string.
 *
 * @param int $eventid The ID of the event.
 * @return string The type of the event.
 */
    function elluminatelive_reminder_event_type($eventid) {
		global $DB;
        // Make sure the event exists
        if (!$event = $DB->get_record('event', array('id'=>intval($eventid)))) {
            print_error('Invalid event ID: ' . $eventid);
        }

        $type = 'none';

        // Determine the type of event
        if (!$event->courseid and !$event->groupid and $event->userid) {
            $type = 'user';
        } elseif ($event->courseid and !$event->groupid and $event->userid) {
            if ($event->courseid != SITEID) {
                $type = 'course';
            }
        } elseif ($event->courseid and $event->groupid and $event->userid) {
            $type = 'group';
        } else {
            $type = 'none';
        }

        return $type;
    }


/**
 * Update the interval start time for a record of the interval type.
 *
 * @param int $reminderid ID of the reminder to update the interval for.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_reminder_interval_update($reminderid) {
		global $DB;
        if (!$reminder = $DB->get_record('event_reminder', array('id'=>intval($reminderid)))) {
            return false;
        }

        // If the reminder type isn't of the Interval variety, we can't udpate
        // the interval, can we?
        if ($reminder->type != REMINDER_TYPE_INTERVAL) {
            return false;
        }

        // Update the value for the next interval
        $reminder->timedelta += $reminder->timeinterval;

        if (!$DB->update_record('event_reminder', $reminder)) {
            return false;
        }

        return true;
    }


/**
 * Checks a calendar event to see if any reminders assosciated with it should
 * have a message sent out.
 *
 * @param int $eventid ID of the event to check the reminder times for.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_reminder_check($eventid) {
		global $DB;
        // Make sure the event exists
        if (!$event = $DB->get_record('event', array('id'=>intval($eventid)))) {
            print_error('Invalid event ID: ' . $eventid);
        }

        // Get records
        if (!$reminders = $DB->get_records('event_reminder', array('event'=>$event->id))) {
            return false;
        }

        // Check each record to see if the time has passed to issue a reminder
        foreach ($reminders as $reminder) {
            switch($reminder->type) {
                case REMINDER_TYPE_DELTA:
                    // If the current time is past the delta before the event,
                    // send the message.
                    if (time() > $event->timestart - $reminder->timedelta) {
                    //notify(userdate(time()) . ' ' . userdate($reminder->timedelta));
                    //if (time() > $reminder->timedelta) {
//                        notify('sending reminder!');
                        elluminatelive_reminder_send($reminder->id);
                        elluminatelive_reminder_remove($reminder->id);
                    }
                    break;

                case REMINDER_TYPE_INTERVAL:
                    if (time() > $event->timeend) {
                        // If we are passed the cutoff (end) time for this reminder,
                        // delete the reminder from the system.
                        reminder_remove_reminder($reminder->id);
                    } elseif (time() > $event->timedelta) {
                        // If we are passed an interval, send a reminder and update
                        // the interval start time.
//                        notify('sending reminder!');
                        elluminatelive_reminder_send($reminder->id);
                        elluminatelive_reminder_interval_udpdate($reminder->id);
                    }
                    break;

                default:
                    return false;
                    break;
            }
        }

        return true;
    }


/**
 * Sends the reminder message for the specified reminder.
 *
 * @param int $reminderid ID of the reminder to send a message for.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_reminder_send($reminderid) {
		global $DB;
        // Make sure the reminder exists
        if (!$reminder = $DB->get_record('event_reminder', array('id'=>intval($reminderid)))) {
            return false;
        }

        // Get the event record that this reminder belongs to
        if (!$event = $DB->get_record('event', array('id'=>$reminder->event))) {
            return false;
        }

        // Determine the type of event
        $type = elluminatelive_reminder_event_type($event->id);

        // General message information.
        $userfrom = get_admin();
        $site     = get_site();
        $subject  = get_string('remindersubject', 'event_reminder', $site->fullname);
        $message  = elluminatelive_reminder_message($event->id, $type);

        // Send the reminders to user(s) based on the type of event.
        switch ($type) {
            case 'user':
                // Send a reminder to the user
                if (!empty($CFG->messaging)) {
                    // use message system
                } else {
                    $user = $DB->get_record('user', array('id'=>$event->userid));
                    email_to_user($user, $userfrom, $subject, $message);
                    //email_to_user($user, $userfrom, 'Reminder Test', 'Testing!');
                }
                break;

            case 'course':
                // Get all the users in the course and send them the reminder
                $users = get_course_users($event->courseid);

                foreach ($users as $user) {
                    if (!empty($CFG->messaging)) {
                        // use message system
                    } else {
                        email_to_user($user, $userfrom, $subject, $message);
                    }
                }
                break;

            case 'group':
                // Get all the users in the group and send them the reminder
                $users = get_group_users($event->groupid);

                foreach ($users as $user) {
                    if (!empty($CFG->messaging)) {
                        // use message system
                    } else {
                        email_to_user($user, $userfrom, $subject, $message);
                    }
                }
                break;

            default:
                return false;
                break;
        }

        return true;
    }


/**
 * Returns a formatted upcoming event reminder message.
 *
 * @uses $CFG
 * @param int $eventid The ID of the event to format a message for.
 * @param string $type The type of event to format the message for.
 */
    function elluminatelive_reminder_message($eventid, $type) {
        global $CFG,$DB;

        // Make sure the event exists
        if (!$event = $DB->get_record('event', array('id'=>intval($eventid)))) {
            print_error('Invalid event ID: ' . $eventid);
        }

        switch($type) {
            case 'user':
                $message = get_string('remindermessageuser', 'event_reminder');
                break;

            case 'course':
                // Get the course record to format message variables
                $course = $DB->get_record('course', array('id'=>$event->courseid));
                $message = get_string('remindermessagecourse', 'event_reminder', $course->fullname);
                break;

            case 'group':
                // Get the group record to format message variables
                $group = $DB->get_record('groups', array('id'=>$event->groupid));
                $message = get_string('remindermessagegroup', 'event_reminder', $group->name);
                break;

            default:
                return NULL;
                break;
        }

        require_once($CFG->libdir . '/html2text.php');

        // Add the date for the event and the description for the event to the end of the message.
        $event_description = new html2text($event->description);

        $message .= userdate($event->timestart);
        $message .= get_string('remindereventdescription', 'event_reminder', $event_description->get_text());

        return $message;
    }



/**
 * ===========================================================================
 * The following are all functions that deal with sending web services calls
 * to an Elluminate Live! server.
 * ===========================================================================
 */



/**
 * Sends a command to an Elluminate Live! server via the web services interface.
 *
 * The structure of the command arguments array is a two-dimensional array in
 * the following format:
 *   $args[]['name']  = argument name;
 *   $args[]['value'] = argument value;
 *   $args[]['type']  = argument type (i.e. 'xsd:string');
 *
 * @uses $CFG
 * @param string $command The name of the command.
 * @param array $args Command arguments.
 * @return mixed|boolean The result object/array or False on failure.
 */
    function elluminatelive_send_command($command, $args = NULL) {
        global $CFG;

        if (empty($CFG->elluminatelive_server) || empty($CFG->elluminatelive_adapter) ||
            empty($CFG->elluminatelive_auth_username) || empty($CFG->elluminatelive_auth_password)) {

            debugging('Module not correctly configured');
            return false;
        }

        //if (file_exists($CFG->libdir . '/soap/nusoap.php')) {
        //    require_once($CFG->libdir . '/soap/nusoap.php');
        //} else if (file_exists($CFG->libdir . '/nusoap/nusoap.php')) {
        //    require_once($CFG->libdir . '/nusoap/nusoap.php');
        //} else {
        //    print_error('No SOAP library files found!');
        //}

	 if (file_exists($CFG->dirroot . '/mod/elluminatelive/soap/nusoap.php')) {
            require_once ($CFG->dirroot . '/mod/elluminatelive/soap/nusoap.php');
        } else {
            print_error('No SOAP library files found!');
        }

    /// Create the correct URL of the endpoint based upon the configured server address.
        $serverurl = $CFG->elluminatelive_server;

        if (substr($serverurl, strlen($serverurl) - 1, 1) != '/') {
            $serverurl .= '/webservice.event';
        } else {
            $serverurl .= 'webservice.event';
        }

    /// Connect to the server.
        $client = new soap_client($serverurl);
        $client->xml_encoding = 'UTF-8';

    /// Encode parameters and the command and adapter name.
        $params = '';

        if (!empty($args)) {
            foreach ($args as $arg) {
                $params .= $client->serialize_val($arg['value'], $arg['name'],
                                                  str_replace('xsd:', '', $arg['type']),
                                                  false, false, false, 'encoded');
            }
        }

        $params .= $client->serialize_val($command, 'command', false, false,
                                          false, false, 'encoded');

        $params .= $client->serialize_val($CFG->elluminatelive_adapter, 'adapter',
                                          false, false, false, false, 'encoded');

    /// Add authentication headers.
        $client->setHeaders(
            '<h:BasicAuth xmlns:h="http://soap-authentication.org/basic/2001/10" mustUnderstand="1">
              <Name>' . $CFG->elluminatelive_auth_username . '</Name>
              <Password>' . $CFG->elluminatelive_auth_password . '</Password>
            </h:BasicAuth>'
        );


    /// Send the call to the server.
        $result = $client->call('request', $params);


    /// If there is an error, notify the user.
        if (!empty($client->error_str) || !empty($client->fault)) {
        /// Check for an HTML 404 error.
            if (!empty($client->response) && ((strstr($client->response, 'HTTP') !== false) &&
                  strstr($client->response, '404') !== false)) {

                debugging('Elluminate Live! Server not found');
                return false;
            }

            echo '<p align="center"><b>Fault:</b></p>';
            $str = '<b>Elluminate Live! error:<br /><br />Call:</b> <i>' . $command . '</i>';
            if (!empty($CFG->elluminatelive_ws_debug) && !empty($client->debug_str)) {
                $str .= '<br /><br /><b>Debug string:</b> <i>' . $client->debug_str . '</i>';
            }

            if (!empty($client->response)) {
                $str .= '<br /><br /><b>Client response:</b> <i>' . $client->response . '</i>';
            }

            if (!empty($result->faultcode)) {
                $str .= '<br /><br /><b>Result->faultcode:</b> <i>' . $result->faultcode . '</i>';
            }

            if (!empty($result->faultstring)) {
                $str .= '<br /><br /><b>Result->faultstring:</b> <i>' . $result->faultstring . '</i>';
            }

            if (!empty($result->faultdetail)) {
                $str .= '<br /><br /><b>Result->faultdetail:</b><br /><i>' . $result->faultdetail . '</i>';
            }

            debugging($str, DEBUG_DEVELOPER);
            return false;
        }

        $result = elluminatelive_fix_object($result);
        return $result;
    }

/**
 * Fix objects being returned as associative arrays (to fit with PHP5 SOAP support)
 *
 * @link /lib/soaplib.php - SEE FOR MORE INFO
 */
    function elluminatelive_fix_object($value) {
        if (is_array($value)) {
            $value = array_map('elluminatelive_fix_object', $value);
            $keys = array_keys($value);
            /* check for arrays of length 1 (they get given the key "item"
            rather than 0 by nusoap) */
            if (1 === count($value) && 'item' === $keys[0]) {
               $value = array_values($value);
            }
            else {
                /* cast to object if it is an associative array with at least
                one string key */
                foreach ($keys as $key) {
                    if (is_string($key)) {
                        $value = (object) $value;
                        break;
                    }
                }
            }
        }
        return $value;
    }


/**
 * This tests for a valid connection to the configured Elluminate Live! server's
 * web service interface.
 *
 * @uses $CFG
 * @param string $serverurl The URL pointing to the Elluminate Live! manager (optional).
 * @param string $adapter   The adapter name (optional).
 * @param string $username  The authentication username (optional).
 * @param string $password  The authentication password (optional).
 * @return boolean True on successful test, False otherwise.
 */
    function elluminatelive_test_connection($serverurl = '', $adapter = '',
                                            $username = '', $password = '') {
        global $CFG;

        //if (file_exists($CFG->libdir . '/soap/nusoap.php')) {
        //    require_once($CFG->libdir . '/soap/nusoap.php');
        //} else if (file_exists($CFG->libdir . '/nusoap/nusoap.php')) {
        //    require_once($CFG->libdir . '/nusoap/nusoap.php');
        //} else {
        //    print_error('No SOAP library files found!');
        //}

		if (file_exists($CFG->dirroot . '/mod/elluminatelive/soap/nusoap.php')) {
			require_once ($CFG->dirroot . '/mod/elluminatelive/soap/nusoap.php');
		} else {
			print_error('No SOAP library files found!');
		}


        if (empty($serverurl)) {
            $serverurl = $CFG->elluminatelive_server;
        }
        if (empty($adapter)) {
            $adapter = $CFG->elluminatelive_adapter;
        }
        if (empty($username)) {
            $username = $CFG->elluminatelive_auth_username;
        }
        if (empty($password)) {
            $password = $CFG->elluminatelive_auth_password;
        }

        $params = array();
        $params['command'] = 'getServerDetails';
        $params['adapter'] = $adapter;

    /// Create the correct URL of the endpoint based upon the configured server address.
        if (substr($serverurl, strlen($serverurl) - 1, 1) != '/') {
            $serverurl .= '/webservice.event';
        } else {
            $serverurl .= 'webservice.event';
        }

    /// Connect to the server.
        $client = new soap_client($serverurl);
        $client->xml_encoding = "UTF-8";

    /// Add authentication headers.
        $client->setHeaders(
            '<h:BasicAuth xmlns:h="http://soap-authentication.org/basic/2001/10" mustUnderstand="1">
              <Name>' . $username . '</Name>
              <Password>' . $password . '</Password>
            </h:BasicAuth>'
        );

    /// Send the call to the server.
        $result = $client->call('request', $params);

    /// If there is an error, notify the user.
        if (!empty($client->error_str) || !empty($client->fault)) {
            return false;
        }

        $result = elluminatelive_fix_object($result);

        if (!is_object($result) && is_string($result)) {
            return false;
        } else if (!isset($result->Map)) {
            return false;
        }

        return true;
    }


/**
 * Given a returned user object from an Elluminate Live! server, process
 * the object into a new, Moodle-useable object.
 *
 * The return object (on success) is of the following format:
 *   $user->userid
 *   $user->loginname
 *   $user->email
 *   $user->firstname
 *   $user->lastname
 *   $user->role
 *   $user->deleted
 *
 * @param object $useradapter The returned 'User Adapter' from the server.
 * @return object An object representing the usser.
 */
    function elluminatelive_process_user($useradapter) {
        $user = new stdClass;
        $user->userid    = $useradapter->Id;
        $user->loginname = $useradapter->LoginName;
        $user->email     = $useradapter->Email;
        $user->firstname = $useradapter->FirstName;
        $user->lastname  = $useradapter->LastName;
        $user->role      = $useradapter->Role->RoleAdapter->RoleId;

        switch ($useradapter->Deleted) {
            case 'true':
                $meeting->deleted = true;
                break;
            case 'false':
                $meeting->deleted = false;
                break;
        }

        return $user;
    }


/**
 * Given a returned meeting object from an Elluminate Live! server, process
 * the object into a new, Moodle-useable object.
 *
 * The return object (on sucess) is of the following format:
 *   $meeting->meetingid
 *   $meeting->facilitatorid
 *   $meeting->privatemeeting
 *   $meeting->name
 *   $meeting->password
 *   $meeting->start
 *   $meeting->end
 *   $meeting->deleted
 *
 * @param object $meetingadapter The returned 'Meeting Adapter' from the server.
 * @return object An object representing the meeting.
 */
    function elluminatelive_process_meeting($meetingadapter) {
        $meeting = new stdClass;

        $meeting->meetingid      = $meetingadapter->Id;
        $meeting->facilitatorid  = $meetingadapter->FacilitatorId;

        switch ($meetingadapter->PrivateMeeting) {
            case 'true':
                $meeting->privatemeeting = true;
                break;
            case 'false':
                $meeting->privatemeeting = false;
                break;
        }

        $meeting->name           = $meetingadapter->Name;
        $meeting->password       = $meetingadapter->Password;
        $meeting->start          = substr($meetingadapter->Start, 0, 10);
        $meeting->end            = substr($meetingadapter->End, 0, 10);

        switch ($meetingadapter->Deleted) {
            case 'true':
                $meeting->deleted = true;
                break;
            case 'false':
                $meeting->deleted = false;
                break;
        }

        return $meeting;
    }


/**
 * Given a returned participant list object from an Elluminate Live! server,
 * process the object into a new, Moodle-useable array of objects.
 *
 * The return array (on sucess) is of the following format:
 *   $participants['user'] = user object
 *   $participants['role'] = user role value
 *
 * @param object $plist The returned 'Participant List Adapter' from the server.
 * @return array An array representing the list of participants and their meeting roles.
 */
    function elluminatelive_process_participant_list($plist) {
        $retusers = array();
        $i = 0;

    /// Process the array of participants.
        if (is_array($plist->Participants->Map->Entry)) {
            foreach ($plist->Participants->Map->Entry as $entry) {
                if (isset($entry->Value->ParticipantAdapter->Participant->UserAdapter)) {
                    $retusers[$i]['user'] = elluminatelive_process_user($entry->Value->ParticipantAdapter->Participant->UserAdapter);
                } else {
                    $retusers[$i]['user'] = elluminatelive_process_user($entry->User->UserAdapter);
                }

                $retusers[$i]['role'] = $entry->Value->ParticipantAdapter->Role->RoleAdapter->RoleId;
                $i++;
            }

    /// Process the single participant.
        } else if (is_object($plist->Participants->Map->Entry)) {
            $entry = $plist->Participants->Map->Entry->Value->ParticipantAdapter;
            if (isset($entry->Participant->UserAdapter)) {
                $retusers[$i]['user'] = elluminatelive_process_user($entry->Participant->UserAdapter);
            } else {
                $retusers[$i]['user'] = elluminatelive_process_user($entry->User->UserAdapter);
            }

            $retusers[$i]['role'] = $entry->Role->RoleAdapter->RoleId;
        }

        return $retusers;
    }


/**
 * Process a collection of preload objects from the ELM server.
 * @param object $obj The return object from the web services call.
 * @return array An array of preload objects.
 */
    function elluminatelive_process_preload_list($obj) {
        $preloads = array();

        if (!empty($obj->Collection->Entry) && is_object($obj->Collection->Entry)) {
            $obj = $obj->Collection->Entry->PreloadAdapter;

            $preload = new stdClass;
            $preload->preloadid = $obj->Key;
            $preload->ownerid   = $obj->OwnerId;
            $preload->type      = $obj->Type;
            $preload->name      = $obj->Name;
            $preload->mimetype  = $obj->MimeType;
            $preload->size      = $obj->Size;

            $preloads[] = $preload;
        } else if (!empty($obj->Collection->Entry) && is_array($obj->Collection->Entry)) {
            foreach ($obj->Collection->Entry as $entry) {
                $obj = $entry->PreloadAdapter;

                $preload = new stdClass;
                $preload->preloadid = $obj->Key;
                $preload->ownerid   = $obj->OwnerId;
                $preload->type      = $obj->Type;
                $preload->name      = $obj->Name;
                $preload->mimetype  = $obj->MimeType;
                $preload->size      = $obj->Size;

                $preloads[] = $preload;
            }
        }

        return $preloads;
    }


/**
 * Create a new Elluminate Live! account for the supplied Moodle user ID.
 *
 * @param int $userid The Moodle user ID.
 * @return object|boolean The Elluminate Live! user object on success, False otherwise.
 */
	function elluminatelive_new_user($userid, $password) {
		global $DB,$COURSE;
		if (!$user = $DB->get_record('user', array('id'=>$userid))) {
		    return false;
		}

    /// Determine what role to create this user as.
        $role = ELLUMINATELIVE_ROLE_PARTICIPANT;

    /// Admin = Application Administrator
        //if (isadmin($user->id)) {
        //    $role = ELLUMINATELIVE_ROLE_APPADMIN;

    /// Editing Teachers = Moderator
        //} else if (isteacherinanycourse($user->id)) {
        //    $role = ELLUMINATELIVE_ROLE_MODERATOR;
        //}

        $currentcontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        //if(has_capability('moodle/site:doanything', $currentcontext, $userid)) {
        if (is_siteadmin($user->id)) {
            $role = ELLUMINATELIVE_ROLE_APPADMIN;
        } elseif (has_capability('moodle/course:viewhiddenactivities', $currentcontext, $user->id)) {
            $role = ELLUMINATELIVE_ROLE_MODERATOR;
        }

    /// Let's give it a whirl!

        $result = elluminatelive_create_user($user->username, $password,
                                             $user->email, $user->firstname,
                                             $user->lastname, $role);

        if (!empty($result)) {
            return $result;
        }

        debugging('Could not create ELM user account for ' . fullname($user), DEBUG_DEVELOPER);

        return false;
	}


/**
 * Map an Elluminate Live! role value to it's string name.
 *
 * @param int $role An Elluminate Live! role value.
 * @return string The string name of the role value.
 */
    function elluminatelive_role_name($role) {
        switch($role) {
            case ELLUMINATELIVE_ROLE_APPADMIN:
                $string = 'Application Administrator';
                break;
            case ELLUMINATELIVE_ROLE_MODERATOR:
                $string = 'Moderator';
                break;
            case ELLUMINATELIVE_ROLE_PARTICIPANT:
                $string = 'Participant';
                break;
            default:
                $string = '';
                break;
        }

        return $string;
    }


/**
 * Get a list of users from the Elluminate Live! server.  You can return users
 * with a certain role type or, default, return all users.
 *
 * See the comments for the elluminate_process_user() function for the format of the
 * returned user records returned in the array.
 *
 * @param int $role The Elluminate Live! role type to fetch.
 * @return mixed|boolean An array of user objects or False on failure.
 */
    function elluminatelive_list_users($role = 0) {
    /// Make sure the $role given is correct.
        switch ($role) {
            case 0:
            case ELLUMINATELIVE_ROLE_APPADMIN:
            case ELLUMINATELIVE_ROLE_MODERATOR:
            case ELLUMINATELIVE_ROLE_PARTICIPANT:
                break;
            default:
                return false;
                break;
        }

        $result = elluminatelive_send_command('listUsers');

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->Collection->Entry)) {
                $retusers = array();
                foreach($result->Collection->Entry as $entry) {
                /// Don't return the default Elluminate Live! server users created during install.
                    if (($entry->UserAdapter->Id == 0) ||
                        ($entry->UserAdapter->LoginName == 'serversupport') ||
                        ($entry->UserAdapter->Id == 1) ||
                        ($entry->UserAdapter->LoginName == 'legacyadapter')) {
                        continue;
                    }

                /// If we're filtering users by role, don't include a user with a role we don't want.
                    if (($role != 0) && ($entry->UserAdapter->Role->RoleAdapter->RoleId != $role)) {
                        continue;
                    }
                    $retusers[] = elluminatelive_process_user($entry->UserAdapter);
                }

                return $retusers;
            }
        }

        return false;
    }


/**
 * Get a specific user record from the Elluminate Live! server.
 *
 * See the comments for the elluminate_process_user() function for the format of the
 * returned user records returned in the array.
 *
 * @param int $userid The Elluminate Live! user ID.
 * @return object|boolean The Elluminate Live! user record or False on failure.
 */
    function elluminatelive_get_user($userid) {
        $args = array();

        $args[0]['name']  = 'userId';
        $args[0]['value'] = $userid;
        $args[0]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('getUser', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
        /// If successful, the server returns the user record, so let's just pull out
        /// the useful information and return that.
            if (!empty($result->UserAdapter)) {
                return elluminatelive_process_user($result->UserAdapter);
            }
        }

        return false;
    }


/**
 * Creates a user on the configured Elluminate Live! server and, if successful,
 * stores the mapping between the Moodle user ID and the Elluminate information.
 *
 * See the comments for the elluminate_process_user() function for the format of the
 * returned user records returned in the array.
 *
 * @param string $loginname Login name for this user.
 * @param string $loginpassword Login password for this user.
 * @param string $email The email address for this user.
 * @param string $firstname The first name for this user.
 * @param string $lastname The last name for this user.
 * @param int $role The Elluminate Live! Manager role.
 * @param int $tries Used to append an integer to a username if the username
 *                   we tried to create already exists on the server.
 * @return object|boolean The user object or False on failure.
 */
    function elluminatelive_create_user($loginname, $loginpassword, $email,
                                       $firstname, $lastname, $role, $tries = 0) {

        global $CFG,$DB;

        if ($tries > ELLUMINATELIVE_CREATE_USER_TRIES) {
            return false;
        }

        $args = array();

        if ($tries > 0) {
            $args[0]['name']  = 'loginName';
            $args[0]['value'] = $loginname . $tries;
            $args[0]['type']  = 'xsd:string';

        } else {
            $args[0]['name']  = 'loginName';
            $args[0]['value'] = $loginname;
            $args[0]['type']  = 'xsd:string';

        }

        $args[1]['name']  = 'loginPassword';
        $args[1]['value'] = $loginpassword;
        $args[1]['type']  = 'xsd:string';

        $args[2]['name']  = 'email';
        $args[2]['value'] = $email;
        $args[2]['type']  = 'xsd:string';

        $args[3]['name']  = 'firstName';
        $args[3]['value'] = $firstname;
        $args[3]['type']  = 'xsd:string';

        $args[4]['name']  = 'lastName';
        $args[4]['value'] = $lastname;
        $args[4]['type']  = 'xsd:string';

        $args[5]['name']  = 'role';
        $args[5]['value'] = $role;
        $args[5]['type']  = 'xsd:integer';

	
		if ($elmuser = elluminatelive_get_user($loginname)) {
			if ($user = $DB->get_record('user', array('username'=>$loginname,'email'=>$email))) {
				$elluminate_user = new Object();
				$elluminate_user->userid       = $user->id;
				$elluminate_user->elm_id       = $loginname;
				$elluminate_user->elm_username = $loginname;
				$elluminate_user->elm_password = $loginpassword;
				
				switch($role) {
					case ELLUMINATELIVE_ROLE_APPADMIN:
						$elluminate_user->elm_role = 'Application Administrator';
						break;
					case ELLUMINATELIVE_ROLE_MODERATOR:
						$elluminate_user->elm_role = 'Moderator';
						break;
					case ELLUMINATELIVE_ROLE_PARTICIPANT:
						$elluminate_user->elm_role = 'Participant';
						break;
				}
	
				$elluminate_user->timecreated = time();
				
				if (!$elluminate_user->id = $DB->insert_record('elluminatelive_users', $elluminate_user)) {
					return false;
				}
				
				$newuser = new Object();
				$newuser->userid    = $elluminate_user->elm_id;
				$newuser->loginname = $elluminate_user->elm_username;
				$newuser->email     = $user->email;
				$newuser->firstname = $user->firstname;
				$newuser->lastname  = $user->lastname;
				$newuser->role      = $role;
	
		
				return $newuser;
			}
		} else { 
	    //// Send the command to the Elluminate Live! server.
	        $result = elluminatelive_send_command('createUser', $args);
              //this is where the epic fail happens if the account does not exist on the elluminate server.
		//print_r($result);
		//exit();

	        if (is_object($result)) {
	        /// On failure the server returns an object with trace information.
	            if (isset($result->Detail->Stack->Trace)) {
	                return elluminatelive_create_user($loginname, $loginpassword, $email,
	                                                  $firstname, $lastname, $role, $tries + 1);
	
	        /// If successful, the server returns the newly created user record, so we
	        /// need to record the new Elluminate user locally against the Moodle user ID.
	            } else if (!empty($result->UserAdapter)) {
	                if ($user = $DB->get_record('user', array('username'=>$loginname,'email'=>$email,'mnethostid'=>$CFG->mnet_localhost_id))) {
	
	                    $elluminate_user = new stdClass;
	                    $elluminate_user->userid       = $user->id;
	                    $elluminate_user->elm_id       = $result->UserAdapter->Id;
	                    $elluminate_user->elm_username = $result->UserAdapter->LoginName;
	                    $elluminate_user->elm_password = $loginpassword;
	
	                    switch($result->UserAdapter->Role->RoleAdapter->RoleId) {
	                        case ELLUMINATELIVE_ROLE_APPADMIN:
	                            $elluminate_user->elm_role = 'Application Administrator';
	                            break;
	                        case ELLUMINATELIVE_ROLE_MODERATOR:
	                            $elluminate_user->elm_role = 'Moderator';
	                            break;
	                        case ELLUMINATELIVE_ROLE_PARTICIPANT:
	                            $elluminate_user->elm_role = 'Participant';
	                            break;
	                    }
	
	                    $elluminate_user->timecreated = time();
	
	                    if (!$elluminate_user->id = $DB->insert_record('elluminatelive_users', $elluminate_user)) {
	                        return false;
	                    }
	
	                    return elluminatelive_process_user($result->UserAdapter);
	                }
	            }
	        } else {
	            return false;
	        }
        }

        return $result;
    }


/**
 * Changes the password on the Elluminate Live! server for a given user.
 *
 * @param int $userid The Elluminate Live! user ID.
 * @param string $password The new password.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_change_password($userid, $password) {
		global $DB;
        $args = array();

        $args[0]['name']  = 'userId';
        $args[0]['value'] = $userid;
        $args[0]['type']  = 'xsd:string';

        $args[1]['name']  = 'loginPassword';
        $args[1]['value'] = $password;
        $args[1]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('changePassword', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->UserAdapter)) {
                $elmuser = $DB->get_record('elluminatelive_users', array('elm_id'=>$userid));
                $elmuser->password = $password;

                return $DB->update_record('elluminatelive_users', $elmuser);
            }
        }

        return false;
    }


/**
 * Delete's a user account on the Elluminate Live! server for a given user.
 *
 * @param int $userid The Elluminate Live! user ID.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_delete_user($userid) {
		global $DB;
        $args = array();

        $args[0]['name']  = 'userId';
        $args[0]['value'] = $userid;
        $args[0]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('deleteUser', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->UserAdapter)) {
                return $DB->delete_records('elluminatelive_users', array('elm_id'=>$userid));
            }
        }

        return false;
    }


/**
 * Get a list of participants for a given meeting.
 *
 * The returned array is of the following structure:
 * array[]['user'] = user object
 * array[]['role'] = user role as a string
 *
 * @param int $meetingid The Elluminate Live! meeting ID.
 * @param int $role The role type to return. (Default 0: return all)
 * @return array|boolean An array of users and roles or False on failure.
 */
    function elluminatelive_list_participants($meetingid, $role = 0) {
    /// Make sure the supplied role value is valid.
        switch ($role) {
            case 0:
            case ELLUMINATELIVE_ROLE_MODERATOR:
            case ELLUMINATELIVE_ROLE_PARTICIPANT:
                break;
            default:
                return false;
        }

        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('listParticipants', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->ParticipantListAdapter)) {
                $participants = elluminatelive_process_participant_list($result->ParticipantListAdapter);

            /// Return all participants for this meeting.
                if ($role === 0) {
                    return $participants;

            /// Return only the selected role type for this meeting.
                } else {
                    $retusers = array();

                    foreach($participants as $participant) {
                        if ($participant['role'] == $role) {
                            $retusers[] = $participant;
                        }
                    }

                    return $retusers;
                }
            }
        }

        return false;
    }


/**
 * Determine if the user is a participant of the given meeting.
 *
 * @param int $meetingid The Elluminate Live! meeting ID.
 * @param int $userid The Elluminate Live! user ID.
 * @param boolean $moderator Is the user being added as a moderator? (default False)
 * @return boolean True if the user is a participant, False otherwise.
 */
    function elluminatelive_is_participant($meetingid, $userid, $moderator = false) {
        $args = array();

        $args[0]['name']  = 'userId';
        $args[0]['value'] = $userid;
        $args[0]['type']  = 'xsd:string';

        $args[1]['name']  = 'meetingId';
        $args[1]['value'] = $meetingid;
        $args[1]['type']  = 'xsd:long';

        if ($moderator) {
            $result = elluminatelive_send_command('isModerator', $args);
        } else {
            $result = elluminatelive_send_command('isParticipant', $args);
        }

        switch ($result) {
            case 'true':
                return true;
                break;
            case 'false':
                return false;
                break;
            default:
                return false;
                break;
        }

        return false;
    }


/**
 * Add a user as a participant to a given meeting.
 *
 * @param int $meetingid The Elluminate Live! meeting ID.
 * @param int $userid The Elluminate Live! user ID.
 * @param boolean $moderator Is the user being added as a moderator? (default False)
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_add_participant($meetingid, $userid, $moderator = false) {
    /// Make sure this user is not already a moderator for this meeting.
        if (elluminatelive_is_participant($meetingid, $userid) ||
            elluminatelive_is_participant($meetingid, $userid, true)) {
            return true;
        }

    /// Make sure any existing participants are included in the list.
        $participants = elluminatelive_list_participants($meetingid);

        $userlist  = '';
        $usercount = 0;

        if (!empty($participants)) {
            foreach ($participants as $participant) {
                if ($usercount) {
                    $userlist .= ';';
                }

                $userlist .= $participant['user']->userid . '=' . $participant['role'];
                $usercount++;
            }
        }

        if ($usercount) {
            $userlist .= ';';
        }

    /// Append the new user we're adding.
        if ($moderator) {
            $userlist .= $userid . '=' . ELLUMINATELIVE_ROLE_MODERATOR;
        } else {
            $userlist .= $userid . '=' . ELLUMINATELIVE_ROLE_PARTICIPANT;
        }

        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'users';
        $args[1]['value'] = $userlist;
        $args[1]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('addParticipant', $args);

    /// Apparently this command returns NOTHING on success ?
        if (empty($result)) {
            return true;
        }

        return false;
    }


/**
 * Delete a participant from a given meeting.
 *
 * @param int $meetingid The Elluminate Live! meeting ID.
 * @param int $userid The Elluminate Live! user ID.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_delete_participant($meetingid, $userid) {
        $args = array();

        $args[0]['name']  = 'userId';
        $args[0]['value'] = $userid;
        $args[0]['type']  = 'xsd:string';

        $args[1]['name']  = 'meetingId';
        $args[1]['value'] = $meetingid;
        $args[1]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('deleteParticipant', $args);

        switch ($result) {
            case 'true':
                return true;
            default:
                return false;
        }

        return false;
    }


/**
 * Get a list of meetings from the Elluminate Live! server.
 *
 * See the comments for the elluminate_processmeeting() function for the format
 * of the returned meeting records returned in the array.
 *
 * @param int $role The Elluminate Live! role type to fetch.
 * @return mixed|boolean An array of user objects or False on failure.
 */
    function elluminatelive_list_meetings() {
        $result = elluminatelive_send_command('listMeetings');

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->Collection->Entry)) {
                $retmeetings = array();
                if (is_array($result->Collection->Entry)) {
                    foreach($result->Collection->Entry as $entry) {
                        $retmeetings[] = elluminatelive_process_meeting($entry->MeetingAdapter);
                    }
                } else {
                    $retmeetings[] = elluminatelive_process_meeting($result->Collection->Entry->MeetingAdapter);
                }

                return $retmeetings;
            }
        }

        return false;
    }


/**
 * Create a new Elluminate Live! meeting on the server.
 *
 * @param int $start The start date and time of the meeting.
 * @param int $end The end date and time of the meeting.
 * @param string $name The name of the meeting.
 * @param string $facilitator The user ID of the creator of this meeting.
 * @param string $password The password for this meeting.
 * @param boolean $private Is this meeting a private or public meeting?
 * @param int $seats The number of seats to reserve for his meeting.
 * @return object|boolean The newly created meeting object or False on failure.
 */
    function elluminatelive_create_meeting($start, $end, $name, $facilitator,
                                           $password = '', $private = false, $seats = 0) {
        $args = array();
        $i    = 0;

        $args[0]['name']  = 'start';
        $args[0]['value'] = $start . '000';
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'end';
        $args[1]['value'] = $end . '000';
        $args[1]['type']  = 'xsd:long';

        $args[2]['name']  = 'name';
        $args[2]['value'] = $name;
        $args[2]['type']  = 'xsd:string';

        $args[3]['name']  = 'facilitator';
        $args[3]['value'] = $facilitator;
        $args[3]['type']  = 'xsd:string';

        $args[4]['name']  = 'password';
        $args[4]['value'] = $password;
        $args[4]['type']  = 'xsd:string';

        $args[5]['name']  = 'private';
        $args[5]['value'] = ($private) ? 'true' : 'false';
        $args[5]['type']  = 'xsd:boolean';

        $args[6]['name']  = 'seats';
        $args[6]['value'] = $seats;
        $args[6]['type']  = 'xsd:integer';

        $result = elluminatelive_send_command('createMeeting', $args);

        if (is_string($result)) {
            debugging('Response from ELM server: ' . $result, DEBUG_DEVELOPER);
            return false;
        } else if (is_object($result)) {
            if (!empty($result->Collection->Entry->MeetingAdapter)) {
                return elluminatelive_process_meeting($result->Collection->Entry->MeetingAdapter);
            }
        }

        return false;
    }


/**
 * Modify an existing Elluminate Live! meeting on the server.
 *
 * @param int $meetingid The Elluminate Live! meeting ID.
 * @param int $start The start date and time of the meeting.
 * @param int $end The end date and time of the meeting.
 * @param string $name The name of the meeting.
 * @param string $facilitator The user ID of the creator of this meeting.
 * @param string $password The password for this meeting.
 * @param boolean $private Is this meeting a private or public meeting?
 * @param int $seats The number of seats to reserve for his meeting.
 * @return object|boolean The newly created meeting object or False on failure.
 */
    function elluminatelive_update_meeting($meetingid, $start, $end, $name, $facilitator,
                                           $password = '', $private = false, $seats = 0) {
        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'start';
        $args[1]['value'] = $start . '000';
        $args[1]['type']  = 'xsd:long';

        $args[2]['name']  = 'end';
        $args[2]['value'] = $end . '000';
        $args[2]['type']  = 'xsd:long';

        $args[3]['name']  = 'name';
        $args[3]['value'] = $name;
        $args[3]['type']  = 'xsd:string';

        $args[4]['name']  = 'facilitator';
        $args[4]['value'] = $facilitator;
        $args[4]['type']  = 'xsd:string';

        $args[5]['name']  = 'password';
        $args[5]['value'] = $password;
        $args[5]['type']  = 'xsd:string';

        $args[6]['name']  = 'private';
        $args[6]['value'] = ($private) ? 'true' : 'false';
        $args[6]['type']  = 'xsd:boolean';

        $args[7]['name']  = 'seats';
        $args[7]['value'] = $seats;
        $args[7]['type']  = 'xsd:integer';

        $result = elluminatelive_send_command('updateMeeting', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->MeetingAdapter)) {
                return elluminatelive_process_meeting($result->MeetingAdapter);
            }
        }

    /// Check for an error.
        if (isset($result->Detail->Stack->Trace)) {
            $return = '';

            foreach ($result->Detail->Stack->Trace as $trace) {
                $return .= $trace . "\n";
            }

            return $return;
        }

        return false;
    }


/**
 * Delete a meeting on the Elluminate Live! server.
 *
 * @param long $meetingid The Elluminate Live! meeting ID.
 * @return boolean True on success, False otherwise.
 */
    function elluminatelive_delete_meeting($meetingid) {
        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('deleteMeeting', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->MeetingAdapter)) {
                if (elluminatelive_process_meeting($result->MeetingAdapter)) {
                    return true;
                }
            }
        }

        return false;
    }


/**
 * Get a meeting object from the Elluminate Live! server.
 *
 * @param int $meetingid The Elluminate Live! meeting ID.
 * @return object|boolean The meeting object or False on failure.
 */
    function elluminatelive_get_meeting($meetingid) {
        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('getMeeting', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result)) {
            if (!empty($result->MeetingAdapter)) {
                return elluminatelive_process_meeting($result->MeetingAdapter);
            }
        }

        return false;
    }


/**
 * Set parameters for an Elluminate Live! meeting.
 *
 * Only really useful for setting the forced recording status.
 *
 * @param long $meetingid The Elluminate Live! meeting ID.
 * @param string $costcenter The cost center.
 * @param string $moderatornotes The moderator teleconference notes.
 * @param string $usernotes The user/participant teleconference notes.
 * @param string $recordingstatus The default recording mode for the meeting (ON/OFF/REMOTE).
 * @return object|boolean A meeting parameters object or False on failure.
 */
    function elluminatelive_set_meeting_parameters($meetingid, $costcenter = '',
                                                   $moderatornotes = '', $usernotes = '',
                                                   $recordingstatus = '') {
        $args = array();
        $i    = 0;

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'costCenter';
        $args[1]['value'] = $costcenter;
        $args[1]['type']  = 'xsd:string';

        $args[2]['name']  = 'moderatorNotes';
        $args[2]['value'] = $moderatornotes;
        $args[2]['type']  = 'xsd:string';

        $args[3]['name']  = 'userNotes';
        $args[3]['value'] = $usernotes;
        $args[3]['type']  = 'xsd:string';

        $args[4]['name']  = 'recordingStatus';
        $args[4]['value'] = $recordingstatus;
        $args[4]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('updateMeetingParameters', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && isset($result->MeetingParametersAdapter)) {
            $parameters = new stdClass;

            $parameters->meetingid       = $result->MeetingParametersAdapter->MeetingId;
            $parameters->costcenter      = $result->MeetingParametersAdapter->CostCenter;
            $parameters->moderatornotes  = $result->MeetingParametersAdapter->ModeratorNotes;
            $parameters->usernotes       = $result->MeetingParametersAdapter->UserNotes;
            $parameters->recordingstatus = $result->MeetingParametersAdapter->RecordingStatus;

            return $parameters;
        }

        return false;
    }


/**
 * Get parameters for a specific meeting.
 *
 * Only really useful for checking the forced recording status.
 *
 * @param long $meetingid The Elluminate Live! meeting ID.
 * @return object|boolean A meeting parameters object or False on failure.
 */
    function elluminatelive_get_meeting_parameters($meetingid) {
        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('getMeetingParameters', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && isset($result->MeetingParametersAdapter)) {
            $parameters = new stdClass;

            $parameters->meetingid       = $result->MeetingParametersAdapter->MeetingId;
            $parameters->costcenter      = $result->MeetingParametersAdapter->CostCenter;
            $parameters->moderatornotes  = $result->MeetingParametersAdapter->ModeratorNotes;
            $parameters->usernotes       = $result->MeetingParametersAdapter->UserNotes;
            $parameters->recordingstatus = $result->MeetingParametersAdapter->RecordingStatus;

            return $parameters;
        }

        return false;
    }


/**
 * Set server parameters for an Elluminate Live! meeting.
 *
 * @param long $meetingid The Elluminate Live! meeting ID.
 * @param int $boundary The boundary (threshold) time of this meeting (i.e.
 *                      how long before the meeting participants can join).
 * @param bool $permissionson Whether to enable all permissions for all participants
 *                            in the session / meeting.
 * @param bool $supervised Whether to set the session / meeting supervision flag.
 * @return object|boolean A server parameters object or False on failure.
 */
    function elluminatelive_set_server_parameters($meetingid, $boundary = 15,
                                                  $permissionson = true, $supervised = false) {
        $args = array();
        $i    = 0;

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'boundary';
        $args[1]['value'] = $boundary;
        $args[1]['type']  = 'xsd:integer';

        $args[2]['name']  = 'permissionsOn';
        $args[2]['value'] = ($permissionson) ? 'true' : 'false';
        $args[2]['type']  = 'xsd:boolean';

        $args[3]['name']  = 'supervised';
        $args[3]['value'] = ($supervised) ? 'true' : 'false';
        $args[3]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('updateServerParameters', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && isset($result->ServerParametersAdapter)) {
            $parameters = new stdClass;

            $parameters->meetingid = $result->ServerParametersAdapter->MeetingId;

            if (isset($result->ServerParametersAdapter->BoundaryMinutes)) {
                $parameters->boundaryminutes = $result->ServerParametersAdapter->BoundaryMinutes;
            } else if (isset($result->ServerParametersAdapter->BoundaryTime)) {  // ELM 3.0
                $parameters->boundaryminutes = $result->ServerParametersAdapter->BoundaryTime;
            }

            if (isset($result->ServerParametersAdapter->Seats)) {
                $parameters->seats = $result->ServerParametersAdapter->Seats;
            } else  if (isset($result->ServerParametersAdapter->RequiredSeats)) {  // ELM 3.0
                $parameters->seats = $result->ServerParametersAdapter->RequiredSeats;
            }

            $parameters->supervised      = $result->ServerParametersAdapter->Supervised;
            $parameters->fullpermissions = $result->ServerParametersAdapter->FullPermissions;

            return $parameters;
        }


        return false;
    }


/**
 * Get server parameters for a specific meeting.
 *
 * @param long $meetingid The Elluminate Live! meeting ID.
 * @return object|boolean A server parameters object or False on failure.
 */
    function elluminatelive_get_server_parameters($meetingid) {
        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('getServerParameters', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && isset($result->ServerParametersAdapter)) {
            $parameters = new stdClass;

            $parameters->meetingid       = $result->ServerParametersAdapter->MeetingId;

            if (isset($result->ServerParametersAdapter->BoundaryMinutes)) {
            $parameters->boundaryminutes = $result->ServerParametersAdapter->BoundaryMinutes;
            } else if (isset($result->ServerParametersAdapter->BoundaryTime)) {  // ELM 3.0
                $parameters->boundaryminutes = $result->ServerParametersAdapter->BoundaryTime;
            }

            if (isset($result->ServerParametersAdapter->Seats)) {
            $parameters->seats           = $result->ServerParametersAdapter->Seats;
            } else  if (isset($result->ServerParametersAdapter->RequiredSeats)) {  // ELM 3.0
                $parameters->seats = $result->ServerParametersAdapter->RequiredSeats;
            }

            $parameters->supervised      = $result->ServerParametersAdapter->Supervised;
            $parameters->fullpermissions = $result->ServerParametersAdapter->FullPermissions;

            return $parameters;
        }

        return false;
    }


/**
 * Delete the recording for the given recording ID.
 *
 * @param $string $recordingid Recording ID to identify the recording.
 * @return bool True on success, False otherwise.
 */
    function elluminatelive_delete_recording($recordingid) {
        $args = array();

        $args[0]['name']  = 'recordingId';
        $args[0]['value'] = $recordingid;
        $args[0]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('deleteRecording', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && isset($result->RecordingAdapter)) {
            if ($result->RecordingAdapter->Id == $recordingid) {
                return true;
            }
        }

        return false;
    }


/**
 * Get a list of recordings from the Elluminate Live! server and return them in
 * a Moodle object format:
 *
 *  $recording->recordingid - Recording ID.
 *  $recording->meetingid   - Meeting ID.
 *  $recording->roomname    - Meeting name.
 *  $recording->facilitator - Facilitator user ID.
 *  $recording->created     - Date/time recording created.
 *
 * @param $string filter
 * @return array|boolean An array of recording object or False on failure.
 */
    function elluminatelive_list_recordings($filter = '') {
        if (!empty($filter)) {
            $args = array();

            $args[0]['name']  = 'filter';
            $args[0]['value'] = $filter;
            $args[0]['type']  = 'xsd:string';

            $result = elluminatelive_send_command('listRecordings', $args);
        } else {
            $result = elluminatelive_send_command('listRecordings');
        }

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && !empty($result->Collection)) {
            $entries    = array();
            $recordings = array();

            if (is_array($result->Collection->Entry)) {
                $entries = $result->Collection->Entry;
            } else if (is_object($result->Collection->Entry)) {
                $entries[] = $result->Collection->Entry;
            }

            foreach ($entries as $entry) {
                $recording = new stdClass;
                $recording->recordingid = $entry->RecordingAdapter->Id;
                $recording->meetingid   = $entry->RecordingAdapter->MeetingRoomId;
                $recording->roomname    = $entry->RecordingAdapter->RoomName;
                $recording->facilitator = $entry->RecordingAdapter->Facilitator;
                $recording->size        = $entry->RecordingAdapter->Size;
                $recording->created     = substr($entry->RecordingAdapter->CreationDate, 0, 10);

                $recordings[] = $recording;
            }

            return $recordings;
        }

        return false;
    }


/**
 * Get a list of recent recorded meetings based upon the user's system authority:
 *  - admins can see all recent meeting recordings
 *  - teachers see recent recordings in their courses
 *  - students see recent recordings they participated in
 *
 * The return array is of the format where each entry is an object that consists
 * of the following information:
 *
 *  $entry->name        = meeting name
 *  $entry->recordingid = recording ID
 *
 * @uses $CFG
 * @uses $USER
 * @param none
 * @return array An array of recorded meeting information.
 */
    function elluminatelive_recent_recordings() {
        global $CFG, $USER, $DB;

        $return = array();

        $type = 'student';

        $context = get_context_instance(CONTEXT_SYSTEM);

        $teacherroles = array();

    /// Get a list of the roles associated with an editing teacher.
        if ($roles = get_roles_with_capability('moodle/legacy:editingteacher', CAP_ALLOW)) {
            $teacherroles = array_merge($teacherroles, $roles);
        }

    /// Get a list of the roles associated with a non-editing teacher.
        if ($roles = get_roles_with_capability('moodle/legacy:teacher', CAP_ALLOW)) {
            $teacherroles = array_merge($teacherroles, $roles);
        }

        if (has_capability('moodle/legacy:admin', $context, $USER->id, false)) {
            $type = 'admin';
        } else {
            foreach ($teacherroles as $teacherrole) {
                if ($type != 'student') {
                    continue;
                }

                if (record_exists('role_assignments', 'roleid', $teacherrole->id, 'userid', $USER->id)) {
                    $type = 'teacher';
                }
            }
        }

    /// Get the most recent recordings.
        $sql = "SELECT er.id, er.recordingid, er.created, er.size, e.id as meetingid, e.course as courseid,
                e.sessionname
                FROM {elluminatelive_recordings} er
                INNER JOIN {elluminatelive_session} es ON er.meetingid = es.meetingid
                INNER JOIN {elluminatelive} e ON es.elluminatelive = e.id
                ORDER BY er.created DESC";

        if (!$rs = $DB->get_recordset_sql($sql)) {
            return NULL;
        }

        $mids       = array();
        $coursectxs = array();
        $modulectxs = array();

        while (!rs_eof($rs) && count($return) < 5) {
            $recording = rs_fetch_next_record($rs);

            if (isset($mids[$recording->meetingid])) {
                continue;
            }

            switch ($type) {
                case 'admin':
                    $entry = new stdClass;
                    $entry->meetingid   = $recording->meetingid;
                    $entry->name        = $recording->sessionname;
                    $entry->recordingid = $recording->id;
                    $entry->created     = $recording->created;
                    $entry->size        = $recording->size;

                    $mids[$recording->meetingid] = true;
                    $return[] = $entry;

                    break;

                case 'teacher':
                    if (!isset($coursectxs[$recording->courseid])) {
                        $context = get_context_instance(CONTEXT_COURSE, $recording->courseid);
                        $coursectxs[$recording->courseid] = $context;
                    } else {
                        $context = $coursectxs[$recording->courseid];
                    }

                    if (has_capability('moodle/legacy:editingteacher', $context) ||
                        has_capability('moodle/legacy:teacher', $context)) {

                        $entry = new stdClass;
                        $entry->meetingid   = $recording->meetingid;
                        $entry->name        = $recording->sessionname;
                        $entry->recordingid = $recording->id;
                        $entry->created     = $recording->created;
                        $entry->size        = $recording->size;

                        $mids[$recording->meetingid] = true;
                        $return[] = $entry;
                    }

                    break;

                case 'student':
                    if ($cm = get_coursemodule_from_instance('elluminatelive', $recording->meetingid, $recording->courseid)) {
                        if (!isset($modulectxs[$cm->id])) {
                            $context = get_context_instance(CONTEXT_MODULE, $cm->id);
                            $modulectxs[$cm->id] = $context;
                        } else {
                            $context = $modulectxs[$cm->id];
                        }

                        if (has_capability('mod/elluminatelive:view', $context)) {
                            $entry = new stdClass;
                            $entry->meetingid   = $recording->meetingid;
                            $entry->name        = $recording->sessionname;
                            $entry->recordingid = $recording->id;
                            $entry->created     = $recording->created;
                            $entry->size        = $recording->size;

                            $mids[$recording->meetingid] = true;
                            $return[] = $entry;
                        }
                    }

                    break;

                default:
                    break;
            }
        }

		$rs->close();
        unset($mids);
        unset($coursectxs);
        unset($modulectxs);

        return $return;
    }


/**
 * Login a user and load a meeting object from the Elluminate Live! server.
 *
 * @param long $meetingid The Elluminate Live! meeting ID.
 * @param int $userid A Moodle user ID.
 * @return file|boolean A meeting.jnlp attachment to load a meeting or False on failure.
 */
    function elluminatelive_build_meeting_jnlp($meetingid, $userid) {
		global $DB;
        if (!$user = $DB->get_record('user', array('id'=>$userid))) {
            return false;
        }

        if (!$elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$user->id))) {
            return false;
        }

        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'userName';
        $args[1]['value'] = $elmuser->elm_username;
        $args[1]['type']  = 'xsd:string';

        $args[2]['name']  = 'userPassword';
        $args[2]['value'] = $elmuser->elm_password;
        $args[2]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('buildMeetingJNLP', $args);

        if (!is_string($result)) {
            return false;
        } else if ($result == 'Unable to complete the adapter process() successfully.') {
            return false;
        }

    /// Return the JNLP file as the 'meeting.jnlp' attachment.
        header('Content-Type: application/x-java-jnlp-file; charset=UTF-8');
        header('Content-Disposition: attachment; filename="meeting.jnlp"');
        header('Cache-Control: maxage=3600');  // Cache the file in IE
        header('Pragma: public');              // Fix submitted by Neil Streeter

        echo $result;

        return true;
    }


/**
 * Login a user and load a recording object from the Elluminate Live! server.
 *
 * @param long $recordingid The Elluminate Live! recording ID.
 * @param int $userid A Moodle user ID.
 * @return file|boolean A meeting.jnlp attachment to load a meeting or False on failure.
 */
    function elluminatelive_build_recording_jnlp($recordingid, $userid) {
		global $DB;
        if (!$user = $DB->get_record('user', array('id'=>$userid))) {
            return false;
        }

        if (!$elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$user->id))) {
            return false;
        }

        $args = array();

        $args[0]['name']  = 'recordingId';
        $args[0]['value'] = $recordingid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'userName';
        $args[1]['value'] = $elmuser->elm_username;
        $args[1]['type']  = 'xsd:string';

        $args[2]['name']  = 'userPassword';
        $args[2]['value'] = $elmuser->elm_password;
        $args[2]['type']  = 'xsd:string';

        $args[3]['name']  = 'userIP';
        $args[3]['value'] = $user->lastip;
        $args[3]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('buildRecordingJNLP', $args);

        if (!is_string($result)) {
            return false;
        } else if ($result == 'Unable to complete the adapter process() successfully.') {
            return false;
        }

    /// Return the JNLP file as the 'recording.jnlp' attachment.
        header('Content-Type: application/x-java-jnlp-file; charset=UTF-8');
        header('Content-Disposition: attachment; filename="recording.jnlp"');
        header('Cache-Control: maxage=3600');  // Cache the file in IE
        header('Pragma: public');              // Fix submitted by Neil Streeter

        echo $result;

        return true;
    }


/**
 * Get the maximum number of seats available with the current server license.
 *
 * @param none
 * @return int|boolean The maximum number of seats available with the current
 *                     license or false on failure.
 */
    function elluminatelive_get_max_seats() {
        $result = elluminatelive_send_command('getSeatMaximum', NULL);

        if (!is_numeric($result)) {
            return false;
        }

        return $result;
    }


/**
 * Get the maximum number of seats available across the specified time span.
 *
 * @param int $start The beginning time.
 * @param int $end The ending time.
 * @param string $exclude A comma-separated list of meeting ID's to exclude from this search.
 * @return int|boolean The maximum number of seats avaialble or false on failure.
 */
    function elluminatelive_get_max_available_seats($start, $end, $exclude = '') {
        $args = array();

        $args[0]['name']  = 'start';
        $args[0]['value'] = $start . '000';
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'end';
        $args[1]['value'] = $end . '000';
        $args[1]['type']  = 'xsd:long';

        $args[2]['name']  = 'exclusionList';
        $args[2]['value'] = $exclude;
        $args[2]['type']  = 'xsd:string';

        $result = elluminatelive_send_command('maxAvailableSeats', $args);

        if (!is_numeric($result)) {
            return false;
        }

        return $result;
    }

/**
 * Get the server configuration parameters in object form.
 *
 * @param none
 * @return object|boolean The configuration object or False on failure.
 */
    function elluminatelive_get_server_configuration() {
        $config = new stdClass;

        $result = elluminatelive_send_command('getServerConfiguration');

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && !empty($result->Map)) {
            if (is_array($result->Map->Entry)) {
                foreach ($result->Map->Entry as $entry) {
                    if (isset($entry->Key) && isset($entry->Value)) {
                        $key   = $entry->Key;
                        $value = $entry->Value;

                        $config->$key = $value;
                    }
                }
            }
        }

        return $config;
    }




/**
 * Check to see if seat reservation is enabled on the Elluminate Live! server.
 * ## Currently there is no way to determine seat reservation checking through
 * ## the SAS default adapter.  Currently we simply return the value 'false'
 *
 * @param none
 * @return bool True if seat reservation is enabled on the server, False otherwise.
 */
function elluminatelive_seat_reservation_check() {
	return false;
}


/**
 * Create a new preload file.
 *
 * @param string $type     The type of preload file: 'whiteboard' or 'media'
 * @param string $name     The preload file name.
 * @param string $mimetype The file mime type.
 * @param int    $length   The file length, in bytes.
 * @param int    $ownerid  The ELM user ID who is adding this file (optional).
 * @return object|bool The created preload object or, False on error.
 */
    function elluminatelive_create_preload($type, $name, $mimetype, $length, $ownerid = '') {
        $args = array();

        if ($type != ELLUMINATELIVE_PRELOAD_WHITEBOARD && $type != ELLUMINATELIVE_PRELOAD_MEDIA) {
            return false;
        }

        $args[0]['name']  = 'type';
        $args[0]['value'] = $type;
        $args[0]['type']  = 'xsd:string';

        $args[1]['name']  = 'name';
        $args[1]['value'] = $name;
        $args[1]['type']  = 'xsd:string';

        $args[2]['name']  = 'mimeType';
        $args[2]['value'] = $mimetype;
        $args[2]['type']  = 'xsd:string';

        $args[3]['name']  = 'length';
        $args[3]['value'] = $length;
        $args[3]['type']  = 'xsd:long';

        if (!empty($ownerid)) {
            $args[4]['name']  = 'ownerId';
            $args[4]['value'] = $ownerid;
            $args[4]['type']  = 'xsd:string';
        }

        $result = elluminatelive_send_command('createPreload', $args);

        if (is_string($result)) {
            return false;
        } else if (is_object($result) && !empty($result->PreloadAdapter)) {
            $preload = new stdClass;
            $preload->preloadid = $result->PreloadAdapter->Key;
            $preload->ownerid   = $result->PreloadAdapter->OwnerId;
            $preload->type      = $result->PreloadAdapter->Type;
            $preload->name      = $result->PreloadAdapter->Name;
            $preload->mimetype  = $result->PreloadAdapter->MimeType;
            $preload->size      = $result->PreloadAdapter->Size;

            return $preload;
        }

        return false;
    }


/**
 * Delete a preload file from the ELM server.
 *
 * @param long $preloadid The preload ID.
 * @return bool True on success, False otherwise.
 */
    function elluminatelive_delete_preload($preloadid) {

        $args = array();

        $args[0]['name']  = 'preloadId';
        $args[0]['value'] = $preloadid;
        $args[0]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('deletePreload', $args);

        if (is_object($result) && !empty($result->PreloadAdapter)) {
            return true;
        }

        return false;
    }


/**
 * Associate the file contents with the preload record on the server.
 *
 * @param long  $preloadid The preload ID.
 * @param int   $length    The length of the file, in bytes.  NOTE: must match the preload record.
 * @param mixed $data      The actual file contents, either a string or binary data.
 * @return bool True on sucess, False otherwise.
 */
    function elluminatelive_stream_preload($preloadid, $length, $data) {
        $args = array();

        $args[0]['name']  = 'preloadId';
        $args[0]['value'] = $preloadid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'length';
        $args[1]['value'] = $length;
        $args[1]['type']  = 'xsd:string';

        $args[2]['name']  = 'stream';
        $args[2]['value'] = bin2hex($data);
        $args[2]['type']  = 'xsd:hexBinary';

        $result = elluminatelive_send_command('streamPreload', $args);

        if (!empty($result)) {
            return false;
        }

        return true;
    }


/**
 * Associate a preload with a specific meeting.
 *
 * @param long $preloadid The preload ID.
 * @param long $meetingid The meeting ID.
 * @return bool True on success, False otherwise.
 */
    function elluminatelive_add_meeting_preload($preloadid, $meetingid) {
        $args = array();

        $args[0]['name']  = 'preloadId';
        $args[0]['value'] = $preloadid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'meetingId';
        $args[1]['value'] = $meetingid;
        $args[1]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('addMeetingPreload', $args);

        if (!empty($result)) {
            return false;
        }

        return true;
    }


/**
 * Delete a preload from a specific meeting instance.
 *
 * @param long $preloadid The preload ID.
 * @param long $meetingid The meeting ID.
 * @return bool True on success, False otherwise.
 */
    function elluminatelive_delete_meeting_preload($preloadid, $meetingid) {
        $args = array();

        $args[0]['name']  = 'preloadId';
        $args[0]['value'] = $preloadid;
        $args[0]['type']  = 'xsd:long';

        $args[1]['name']  = 'meetingId';
        $args[1]['value'] = $meetingid;
        $args[1]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('deleteMeetingPreload', $args);
	print_r($result);
	exit("111");

        if (!empty($result)) {
            return false;
        }

        return true;
    }


/**
 * Get a list of all the preloads associated with a given meeting.
 *
 * @param long $meetingid The meeting ID.
 * @return array|bool An array of preload objects or, False on error.
 */
    function elluminatelive_list_meeting_preloads($meetingid) {
        $args = array();

        $args[0]['name']  = 'meetingId';
        $args[0]['value'] = $meetingid;
        $args[0]['type']  = 'xsd:long';

        $result = elluminatelive_send_command('listMeetingPreloads', $args);

        $preloads = array();

        if (empty($result) || is_string($result)) {
            return false;
        } else if (is_object($result)) {
            return elluminatelive_process_preload_list($result);
        }

        return false;
    }


/**
 * Return the URL linking to the support page on the configured Elluminagte Live! server.
 *
 * @param none
 * @return string The URL pointing to the support page.
 */
//    function elluminatelive_support_link() {
//        global $CFG;
/// Create the correct URL of the endpoint based upon the configured server address.
//        $serverurl = $CFG->elluminatelive_server;
//        if (substr($serverurl, strlen($serverurl) - 1, 1) != '/') {
//            $serverurl .= '/support.help';
//        } else {
//            $serverurl .= 'support.help';
//        }
//        return $serverurl;
//    }

/**
 * Return the URL linking to the support page on the configured Elluminate Live! server.
 *
 * @param none
 * @return string The URL pointing to the support page.
 */
function elluminatelive_support_link() {
	return 'http://www.elluminate.com/support'; 
}


/**
 * Attempt to synchronize the local settings with the ELM server.
 *
 * @uses $CFG
 * @param object $elluminatelive A complete activity database record.
 * @param object $cm             A coursemodule record.
 * @return bool True on success, False otherwise.
 */
    function elluminatelive_sync_meeting($elluminatelive, $cm) {
        global $CFG, $DB;

    /// Make sure the user has an Elluminate Live! account.
        if (!$elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$elluminatelive->creator))) {
            if (!elluminatelive_new_user($elluminatelive->creator, random_string(10))) {
               print_error('Could not create new Elluminate Live! user account!');
            }

            $elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$elluminatelive->creator));
        }

        if (empty($elmuser)) {
            debugging('You must have an Elluminate Live! account to create or editing a meeting.');
            return false;
        }

    /// Check to see if groups are being used here
        $groupmode    = groups_get_activity_groupmode($cm);
        $currentgroup = groups_get_activity_group($cm, true);

        if (empty($currentgroup)) {
            $currentgroup = 0;
        }

        if (empty($currentgroup) && !empty($elluminatelive->sessionname)) {
            $sessionname = $elluminatelive->sessionname;
        } else if (!empty($elluminatelive->customname) && !empty($currentgroup)) {
            $sessionname = $elluminatelive->name . ' - ' .
                           $DB->get_field('course', 'shortname', 'id', $elluminatelive->course) .
                           ' - ' . $DB->get_field('groups', 'name', 'id', $currentgroup);
        } else {
            $sessionname = $elluminatelive->name;
        }

        $needsupdate = false;

    /// If groupmode is enabled or seat reservation is disabled, seat reservation is disabled.
        if ($groupmode != NOGROUPS ||
            (empty($CFG->elluminatelive_seat_reservation) && !empty($elluminatelive->seats))) {

            $elluminatelive->seats = 0;
            $needsupdate = true;
        }

        if (!empty($CFG->elluminatelive_boundary_default)) {
            $elluminatelive->boundarytime = $CFG->elluminatelive_boundary_default;
            $needsupdate = true;
        }

        if ($needsupdate) {
            $elluminatelive = $elluminatelive;
            $DB->update_record('elluminatelive', $elluminatelive);
            $elluminatelive = $DB->get_record('elluminatelive', array('id'=>$elluminatelive->id));
        }

    /// Get some needed variables setup.
        $search     = '!@#$%^?&/\';",.:<>';
        $searcharr  = array();
        $replacearr = array();

        for ($i = 0; $i < strlen($search); $i++) {
            $searcharr[]  = $search[$i];
            $replacearr[] = '';
        }

        $creatorid = $DB->get_field('elluminatelive_users', 'elm_id', array('userid'=>$elluminatelive->creator));

        $name = str_replace($searcharr, $replacearr, stripslashes($sessionname));

        $created = false;

    /// Check if a session exists for this activity already.
        if (!$meeting = $DB->get_record('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id,'groupid'=>$currentgroup))) {

            $meeting = new stdClass;
            $meeting->elluminatelive = $elluminatelive->id;
            $meeting->groupid        = $currentgroup;
            $meeting->meetingid      = '';
            $meeting->timemodified   = time();
            $meeting->id = $DB->insert_record('elluminatelive_session', $meeting);

            if (empty($meeting->id)) {
                debugging('Error inserting new record into elluminatelive_session table.');
                return false;
            }

            $result = elluminatelive_create_meeting($elluminatelive->timestart, $elluminatelive->timeend, $name,
                                                    $elmuser->elm_id, '', true, $elluminatelive->seats);

       /// Do an extra check if we're trying to set a seat reservation to make sure the number
        /// of seats is actually available for the time period.
            if ((is_string($result) || !$result) && !empty($elluminatelive->seats)) {
                if (strstr($result, 'Not enough seats available to create meeting!')) {
                    $seats = elluminatelive_get_max_available_seats($elluminatelive->timestart, $elluminatelive->timeend,
                                                                    $meeting->meetingid);

                    if (!elluminatelive_update_meeting($meeting->meetingid, $elluminatelive->timestart,
                                                       $elluminatelive->timeend, $name, $creatorid,
                                                       '', true, $seats)) {

                        $DB->delete_records('elluminatelive_session', array('id'=>$meeting->id));
                        debugging('Could not update Elluminate Live! session for activity ID ' .
                                  $elluminatelive->id . ' group ID ' . $currentgroup);
                        return false;
                    } else {
                    /// Change the local seat reservation value to reflect this.
                        $elluminatelive->seats = $seats;

                        $elluminatelive = $elluminatelive;
                        $DB->update_record('elluminatelive', $elluminatelive);
                        $elluminatelive = $DB->get_record('elluminatelive', array('id'=>$elluminatelive->id));
                    }
                }
            }

            $created = true;

            $meeting->meetingid    = $result->meetingid;
            $meeting->timemodified = time();

            if (!$DB->update_record('elluminatelive_session', $meeting)) {
                $DB->delete_records('elluminatelive_session', array('id'=>$meeting->id));
                debugging('Error updating record in elluminatelive_session table.');
                return false;
            }
        }

    /// Check to see if the meeting creation failed a while ago for this session or wait for the
    /// session creation to return a meeting id.
        if (empty($meeting->meetingid)) {
            if ($meeting->timemodified - time() > ELLUMINATELIVE_SYNC_TIMEOUT) {
            /// Attempt to create the session on the server again.
                $result = elluminatelive_create_meeting($elluminatelive->timestart, $elluminatelive->timeend, $name,
                                                        $elmuser->elm_id, '', true, $elluminatelive->seats);

                if (!$result) {
                    debugging('Could not create a new Elluminate Live! session for activity ID ' .
                              $elluminatelive->id . ' group ID ' . $currentgroup);
                    return false;
                }

                $created = true;

                $meeting->meetingid    = $result->meetingid;
                $meeting->timemodified = time();

                if (!$DB->update_record('elluminatelive_session', $meeting)) {
                    debugging('Error updating record in elluminatelive_session table.');
                    return false;
                }
            } else {
                while(empty($meeting->meetingid)) {
                    $meeting = $DB->get_record('elluminatelive_session', array('id'=>$meeting->id));
                    sleep(2);
                }
            }
        }

        $needsupdate = false;

    /// Check the session name and start and end times.
        $elmsession = elluminatelive_get_meeting($meeting->meetingid);

        if ($elmsession->name != $name || $elmsession->start != $elluminatelive->timestart ||
            $elmsession->end != $elluminatelive->timeend) {

            $needsupdate = true;
        }

    /// Check the session boundary time and seat reservation (where applicable)
        $sparameters = elluminatelive_get_server_parameters($meeting->meetingid);

        if ((!empty($CFG->elluminatelive_seat_reservation) && $elluminatelive->seats != $sparameters->seats) ||
            empty($CFG->elluminatelive_seat_reservation) && !empty($sparameters->seats)) {

            $needsupdate = true;
        }

        if ($needsupdate) {
            $result = elluminatelive_update_meeting($meeting->meetingid, $elluminatelive->timestart,
                                                    $elluminatelive->timeend, $name, $creatorid, '',
                                                    true, $elluminatelive->seats);

        /// Do an extra check if we're trying to set a seat reservation to make sure the number
        /// of seats is actually available for the time period.
            if ((is_string($result) || !$result) && !empty($elluminatelive->seats)) {
                if (strstr($result, 'Not enough seats available to create meeting!')) {
                    $seats = elluminatelive_get_max_available_seats($elluminatelive->timestart, $elluminatelive->timeend,
                                                                    $meeting->meetingid);

                    if (!elluminatelive_update_meeting($meeting->meetingid, $elluminatelive->timestart,
                                                       $elluminatelive->timeend, $name, $creatorid,
                                                       '', true, $seats)) {

                        debugging('Could not update Elluminate Live! session for activity ID ' .
                                  $elluminatelive->id . ' group ID ' . $currentgroup);
                        return false;
                    } else {
                    /// Change the local seat reservation value to reflect this.
                        $elluminatelive->seats = $seats;

                        $elluminatelive = $elluminatelive;
                        $DB->update_record('elluminatelive', $elluminatelive);
                        $elluminatelive = $DB->get_record('elluminatelive', array('id'=>$elluminatelive->id));
                    }
                }
            }
        }

    /// Check the boundary time against what the server says.
        if ($elluminatelive->boundarytime != $sparameters->boundaryminutes) {
            if (!elluminatelive_set_server_parameters($meeting->meetingid, $elluminatelive->boundarytime, true, true)) {

                debugging('Could not update Elluminate Live! server parameters for activity ID ' .
                          $elluminatelive->id . ' group ID ' . $currentgroup);
                return false;
            }
        }

        $mparameters = elluminatelive_get_meeting_parameters($meeting->meetingid);

    /// Adjust recording setting for this meeting.
        switch($elluminatelive->recordingmode) {
            case ELLUMINATELIVE_RECORDING_MANUAL:
                $recording = ELLUMINATELIVE_RECORDING_MANUAL_NAME;
                break;

            case ELLUMINATELIVE_RECORDING_AUTOMATIC:
                $recording = ELLUMINATELIVE_RECORDING_AUTOMATIC_NAME;
                break;

            case ELLUMINATELIVE_RECORDING_NONE:
            default:
                $recording = ELLUMINATELIVE_RECORDING_NONE_NAME;
                break;
        }

        if (($recording != $mparameters->recordingstatus) &&
            !elluminatelive_set_meeting_parameters($meeting->meetingid, '', '', '', $recording)) {

            debugging('Could not update Elluminate Live! meeting parameters for activity ID ' .
                      $elluminatelive->id . ' group ID ' . $currentgroup);
            return false;
        }

        return true;
    }

?>
