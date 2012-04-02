<?php // $Id: loadmeeting.php,v 1.1.2.4 2009/10/22 14:28:23 jfilip Exp $

/**
 * Elluminate Live! meeting load script.
 *
 * @version $Id: loadmeeting.php,v 1.1.2.4 2009/10/22 14:28:23 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

    global $DB;

    $id = required_param('id', PARAM_INT);

    if (!$elluminatelive = $DB->get_record('elluminatelive', array('id'=>$id))) {
        print_error('Could not get meeting (' . $id . ')');
    }

    if (!$course = $DB->get_record('course', array('id'=>$elluminatelive->course))) {
        print_error('Invalid course.');
    }

    if (!$cm = get_coursemodule_from_instance('elluminatelive', $elluminatelive->id, $course->id)) {
        print_error('Course Module ID was incorrect');
    }

    require_course_login($course, true, $cm);

/// Check to see if groups are being used here
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

    if (empty($currentgroup)) {
        $currentgroup = 0;
    }

/// Synchronize local settings with the server.
    if (!elluminatelive_sync_meeting($elluminatelive, $cm)) {
        print_error('errormeetingsync','elluminatelive');
    }

    if (!$meeting = $DB->get_record('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id,'groupid'=>$currentgroup))) {
        print_error('errormeetinginstancenotexist','elluminatelive');
    }

    $elluminatelive->cmidnumber = $cm->id;

/// Some capability checks.
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminatelive:view', $context);
    if (!$cm->visible){
        require_capability('moodle/course:viewhiddenactivities', $context);
    }

/// Determine level of access to this meeting.
    $ismoderator = $USER->id == $elluminatelive->creator ||
                   has_capability('mod/elluminatelive:moderatemeeting', $context, $USER->id, false);

    $isparticipant = has_capability('mod/elluminatelive:joinmeeting', $context, $USER->id, false);


    if ($elluminatelive->private && !$ismoderator && !$isparticipant) {
        print_error('errornotinvited','elluminatelive');
    }

    if (!$session = elluminatelive_get_meeting($meeting->meetingid)) {
        print_error('Incorrect meeting ID value (' . $meeting->meetingid . ')');
    }

/// Make sure the user has an Elluminate server account before we proceed.
    if (!$elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$USER->id))) {
        if (!elluminatelive_new_user($USER->id, random_string(10))) {
           print_error('errorcouldnotcreateuseraccount','elluminatelive');
        }

        $elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$USER->id));
    }

/// Add the user with the proper permissions to this session.
    if ($ismoderator) {
        if (!elluminatelive_add_participant($session->meetingid, $elmuser->elm_id, true)) {
            print_error('errorcouldnotaddyouasmoderator','elluminatelive');
        }
    } else if ($isparticipant) {
        if (!elluminatelive_add_participant($session->meetingid, $elmuser->elm_id)) {
            print_error('errorcouldnotaddyouasparticipant','elluminatelive');
        }
    }

/// Do we need to assign a grade for this meeting?
    if (($elluminatelive->grade !== 0) && !$ismoderator) {
    /// Get the grade value for this meeting (either scale or numerical value).
        if ($elluminatelive->grade < 0) {
            $grades = make_grades_menu($elluminatelive->grade);
            $ugrade = key($grades);
        } else {
            $ugrade = $elluminatelive->grade;
        }

        if (!$grade = $DB->get_record('elluminatelive_attendance', array('elluminateliveid'=>$elluminatelive->id,'userid'=>$USER->id))) {

            $grade = new stdClass;
            $grade->elluminateliveid = $elluminatelive->id;
            $grade->userid           = $USER->id;
            $grade->grade            = $ugrade;
            $grade->timemodified     = time();

            $DB->insert_record('elluminatelive_attendance', $grade);
            elluminatelive_update_grades($elluminatelive, $USER->id);
        } else {
            $grade->grade = $ugrade;

            $DB->update_record('elluminatelive_attendance', $grade);
            elluminatelive_update_grades($elluminatelive, $USER->id);
        }
    }

    if (!empty($cm)) {
        $cmid = $cm->id;
    } else {
        $cmid = 0;
    }

    add_to_log($elluminatelive->course, 'elluminatelive', 'view meeting', 'loadmeeting.php?id=' .
               $elluminatelive->id, $elluminatelive->id, $cmid, $USER->id);

/// Load the meeting.

    if (!elluminatelive_build_meeting_jnlp($session->meetingid, $USER->id)) {
        print_error('errorcouldnotlaunchmeeting','elluminatelive');
    }

?>
