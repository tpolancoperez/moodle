<?php // $Id: participants.php,v 1.1.2.4 2009/10/22 14:28:23 jfilip Exp $

/**
 * Used to update the participants for a given meeting.
 *
 * @version $Id: participants.php,v 1.1.2.4 2009/10/22 14:28:23 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

    global $DB;

    $PAGE->set_url('/mod/elluminatelive/participants.php');

    $id           = required_param('id', PARAM_INT);
    $firstinitial = optional_param('firstinitial', '', PARAM_ALPHA);
    $lastinitial  = optional_param('lastinitial', '', PARAM_ALPHA);
    $sort         = optional_param('sort', '', PARAM_ALPHA);
    $dir          = optional_param('dir', '', PARAM_ALPHA);

    if (!$meeting = $DB->get_record('elluminatelive', array('id'=>$id))) {
        error('You must specify a valid meeting ID.');
    }

    if (!$course = $DB->get_record('course', array('id'=>$meeting->course))) {
        error('Invalid course.');
    }

    if (! $cm = get_coursemodule_from_instance("elluminatelive", $meeting->id, $course->id)) {
        error('Invalid course module.');
    }

/// Some capability checks.
    require_course_login($course, true, $cm);
    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
    $crscontext = get_context_instance(CONTEXT_COURSE, $course->id);
    require_capability('mod/elluminatelive:view', $modcontext);
    require_capability('mod/elluminatelive:manageparticipants', $modcontext);

    $meeting->name = stripslashes($meeting->name);
    $notice        = '';

/// Check to see if groups are being used here
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

    if (empty($currentgroup)) {
        $currentgroup = 0;
    }

/// Process data submission.
    if (($data = data_submitted($CFG->wwwroot . '/mod/elluminatelive/participants.php')) && confirm_sesskey()) {
    /// Delete records for selected participants chosen to be removed.
        if (!empty($data->userscur)) {
            if (!elluminatelive_del_users($meeting, $data->userscur, $currentgroup)) {
                $notice = get_string('couldnotremoveusersfrommeeting', 'elluminatelive');
            }
        }

    /// Add records for selected participants chosen to be added.
        if (!empty($data->usersavail)) {
            if (!elluminatelive_add_users($meeting, $data->usersavail, $currentgroup)) {
                $notice = get_string('couldnotadduserstomeeting', 'elluminatelive');
            }
        }
    }

/// Get a list of existing moderators for this meeting (if any) and assosciated
/// information.
    $curmods = elluminatelive_get_meeting_participants($meeting, $currentgroup, true);

/// Get a list of existing participants for this meeting (if any) and assosciated
/// information.
    $curusers = elluminatelive_get_meeting_participants($meeting, $currentgroup);

    $usersexist = array();
    if (!empty($curusers)) {
        foreach ($curusers as $curuser) {
            $usersexist[] = $curuser->id;
        }
        reset($curusers);
    }

    if (!empty($curmods)) {
        foreach ($curmods as $curmod) {
            $usersexist[] = $curmod->id;
        }
    }

/// Particpants can be teachers or students in this course who have an account on
/// the Elluminate server.
    $allusers = get_users_by_capability($modcontext, 'mod/elluminatelive:view',
                                        'u.id, u.firstname, u.lastname, u.username', 'u.lastname, u.firstname',
                                        '', '', $currentgroup, '', false);

    $ausers = array_keys($allusers);
    // if groupmembersonly used, only include members of the appropriate groups.
    if ($allusers and !empty($CFG->enablegroupings) and $cm->groupmembersonly) {
        if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
            $ausers = array_intersect($ausers, array_keys($groupingusers));
        }
    }

    $ausers = array_diff($ausers, $usersexist);

    $availusers = array();
    foreach ($ausers as $uid) {
        if (!in_array($uid, $usersexist)) {
            $availusers[$uid] = $allusers[$uid];
        }
    }
    unset($allusers);

    $cavailusers = empty($availusers) ? 0 : count($availusers);
    $ccurusers   = empty($curusers) ? 0 : count($curusers);

    $sesskey         = !empty($USER->sesskey) ? $USER->sesskey : '';
    $strmeeting      = get_string('modulename', 'elluminatelive');
    $strmeetings     = get_string('modulenameplural', 'elluminatelive');
    $strparticipants = get_string('editingparticipants', 'elluminatelive');
    $struserscur     = ($ccurusers == 1) ? get_string('existingparticipant', 'elluminatelive') :
                                           get_string('existingparticipants', 'elluminatelive', $ccurusers);
    $strusersavail   = ($cavailusers == 1) ? get_string('availableparticipant', 'elluminatelive') :
                                             get_string('availableparticipants', 'elluminatelive', $cavailusers);
    $strfilterdesc   = get_string('participantfilterdesc', 'elluminatelive');
    $strall          = get_string('all');
    //$alphabet        = explode(',', get_string('alphabet'));

/// Print header.
    $navigation = build_navigation($strparticipants, $cm);
    print_header_simple($strparticipants, "", $navigation, "", "", true, '');

    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/elluminatelive/participants.php?id=' . $meeting->id, false, true);

    //print_simple_box_start('center', '50%');
    echo $OUTPUT->box_start('generalbox', 'notice');

    if (!empty($notice)) {
        notify($notice);
    }

    include($CFG->dirroot . '/mod/elluminatelive/participants-edit.html');

    //print_simple_box_end();
    //print_footer();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer($course);

?>
