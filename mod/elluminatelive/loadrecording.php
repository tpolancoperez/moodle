<?php // $Id: loadrecording.php,v 1.1.2.3 2009/10/22 14:28:23 jfilip Exp $

/**
 * Elluminate Live! recording load script.
 *
 * @version $Id: loadrecording.php,v 1.1.2.3 2009/10/22 14:28:23 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

    global $DB;

    $id = required_param('id', PARAM_INT);


    if (!$recording = $DB->get_record('elluminatelive_recordings', array('id'=>$id))) {
        error('Could not get recording (' . $id . ')');
    }

    if (!$meeting = $DB->get_record('elluminatelive_session', array('meetingid'=>$recording->meetingid))) {
        error('Could not get meeting (' . $recording->meetingid . ')');
    }

    if (!$elluminatelive = $DB->get_record('elluminatelive', array('id'=>$meeting->elluminatelive))) {
        error('Could not load activity record.');
    }

    if (!$course = $DB->get_record('course', array('id'=>$elluminatelive->course))) {
        error('Invalid course.');
    }

    if (!$cm = get_coursemodule_from_instance('elluminatelive', $elluminatelive->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

/// Some capability checks.
    require_course_login($course, true, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminatelive:viewrecordings', $context);


    if (!$elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$USER->id))) {
    /// If this is a public meeting and the user is a member of this course,
    /// they can join the meeting.
        if (empty($elluminatelive->private) && has_capability('moodle/course:view', $context)) {
            if (!elluminatelive_new_user($USER->id, random_string(10))) {
               error('Could not create new Elluminate Live! user account!');
            }

            $elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$USER->id));

            if (!elluminatelive_add_participant($meeting->meetingid, $elmuser->elm_id)) {
                error('Could not add you as a participant to this meeting.');
            }
        } else {
            error('You must have an Elluminate Live! user account to access this resource.');
        }
    }

    if (!elluminatelive_is_participant($meeting->meetingid, $elmuser->elm_id, true) &&
        !elluminatelive_is_participant($meeting->meetingid, $elmuser->elm_id)) {
        if ($elluminatelive->private) {
            error('You must be a participant of the given meeting to access this resource.');
        } else if (has_capability('moodle/course:view', $context)) {
            if (!elluminatelive_add_participant($meeting->meetingid, $elmuser->elm_id)) {
                error('Could not add you as a participant to this meeting.');
            }
        }
    }

    if (!empty($cm)) {
        $cmid = $cm->id;
    } else {
        $cmid = 0;
    }

    add_to_log($elluminatelive->course, 'elluminatelive', 'view recording', 'loadrecording.php?id=' .
               $recording->id, $elluminatelive->id, $cmid, $USER->id);

/// Load the recording.
    if (!elluminatelive_build_recording_jnlp($recording->recordingid, $USER->id)) {
        error('Could not load Elluminate Live! recording');
    }

?>
