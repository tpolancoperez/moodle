<?php // $Id: view.php,v 1.1.2.4 2009/12/22 16:05:06 jfilip Exp $

/**
 * This page prints a particular instance of elluminatelive.
 *
 * @version $Id: view.php,v 1.1.2.4 2009/12/22 16:05:06 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

    global $DB;
    $id                 = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a                  = optional_param('a', 0, PARAM_INT);  // elluminatelive ID
    $editrecordingdesc  = optional_param('editrecordingdesc', 0, PARAM_INT);
    $delrecording       = optional_param('delrecording', 0, PARAM_INT);
    $hiderecording      = optional_param('hiderecording', 0, PARAM_INT);
    $showrecording      = optional_param('showrecording', 0, PARAM_INT);
    $hidegrouprecording = optional_param('hidegrouprecording', 0, PARAM_INT);
    $showgrouprecording = optional_param('showgrouprecording', 0, PARAM_INT);

    $PAGE->set_url('/mod/elluminatelive/view.php');

    if ($id) {
        if (!$cm = get_coursemodule_from_id('elluminatelive', $id)) {
            error("Course Module ID was incorrect");
        }

        if (!$course = $DB->get_record("course", array("id"=>$cm->course))) {
            error("Course is misconfigured");
        }

        if (!$elluminatelive = $DB->get_record("elluminatelive", array("id"=>$cm->instance))) {
            error("Course module is incorrect");
        }

    } else {
        if (!$elluminatelive = $DB->get_record("elluminatelive", array("id"=>$a))) {
            error("Course module is incorrect");
        }
        if (!$course = $DB->get_record("course", array("id"=>$elluminatelive->course))) {
            error("Course is misconfigured");
        }
        if (!$cm = get_coursemodule_from_instance("elluminatelive", $elluminatelive->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    $timenow = time();

/// Some capability checks.
    require_course_login($course, true, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminatelive:view', $context);

    if (!$cm->visible){
        require_capability('moodle/course:viewhiddenactivities', $context);
    }

    $PAGE->requires->js('/mod/elluminatelive/checkseats.js');
    //require_js($CFG->wwwroot . '/mod/elluminatelive/checkseats.js');

    $candeleterecordings    = has_capability('mod/elluminatelive:deleterecordings', $context);
    $candeleteanyrecordings = has_capability('mod/elluminatelive:deleteanyrecordings', $context);
    $canmanageanyrecordings = has_capability('mod/elluminatelive:manageanyrecordings', $context);
    $canmanageseats         = has_capability('mod/elluminatelive:manageseats', $context);
    $canmanagemoderators    = has_capability('mod/elluminatelive:managemoderators', $context);
    $canmanageparticipants  = has_capability('mod/elluminatelive:manageparticipants', $context);
    $canviewrecordings      = has_capability('mod/elluminatelive:viewrecordings', $context);
    $canviewattendance      = has_capability('mod/elluminatelive:viewattendance', $context);
    $canmanageattendance    = has_capability('mod/elluminatelive:manageattendance', $context);
    $canmanagepreloads      = has_capability('mod/elluminatelive:managepreloads', $context);
    $ismoderator            = has_capability('mod/elluminatelive:moderatemeeting', $context, $USER->id, false);
    $isparticipant          = has_capability('mod/elluminatelive:joinmeeting', $context, $USER->id, false);


/// Check to see if groups are being used here
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

    if (empty($currentgroup)) {
        $currentgroup = 0;
    }

    if ($elluminatelive->creator == $USER->id ||
        ($groupmode && groups_is_member($currentgroup) &&
         has_capability('mod/elluminatelive:managerecordings', $context))) {

        $canmanagerecordings = true;
    } else {
        $canmanagerecordings = false;
     }

/// Get the meeting object from the Elluminate Live! server.
    $thegroupid=$currentgroup;
    if ($groupmode == 0) {$thegroupid=0;}
    $meeting = $DB->get_record('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id,'groupid'=>$thegroupid));

/// Calculate the actual number of seconds for the boundary time.
    if (!empty($CFG->elluminatelive_boundary_default)) {
        $boundary = $CFG->elluminatelive_boundary_default;
    } else if (!empty($elluminatelive->boundaryminutes)) {
        $boundary = $elluminatelive->boundaryminutes;
    } else {
        $boundary = ELLUMINATELIVE_BOUNDARY_DEFAULT;
    }

    $boundaryminutes = $boundary * MINSECS;

/// Determine if the meeting has started yet and also if the meeting has finished yet.
    $hasstarted  = (($elluminatelive->timestart - $boundaryminutes) <= $timenow);
    $hasfinished = ($elluminatelive->timeend < $timenow);


/// Print the page header
    $strelluminatelives   = get_string('modulenameplural', 'elluminatelive');
    $strelluminatelive    = get_string('modulename', 'elluminatelive');
    $elluminatelive->name = stripslashes($elluminatelive->name);
    $strelllive           = get_string('modulename', 'elluminatelive');
    $straddpreload        = get_string('addwhiteboardpreload', 'elluminatelive');

    $buttontext = update_module_button($cm->id, $course->id, $strelllive);
    $navigation = build_navigation('', $cm);

    print_header_simple(format_string($elluminatelive->name), "", $navigation, "", "", true, $buttontext, navmenu($course, $cm));

    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id, false, true);

    $sesskey = !empty($USER->sesskey) ? $USER->sesskey : '';

/// Print the main part of the page
    echo $OUTPUT->box_start('generalbox', 'notice');


/// Check for data submission.
    if (($data = data_submitted($CFG->wwwroot . '/mod/elluminatelive/view.php')) && confirm_sesskey()) {
    /// Handle a request to change the number of seats allocated to this meeting.
        if (isset($data->seats) && $canmanageseats && !$groupmode) {
            $elluminatelive = trim($data->seats);

            if (elluminatelive_update_instance($elluminatelive)) {
                debugging(get_string('couldnotchangeseatreservation', 'elluminatelive'));
            } else {
                $meeting = $umeeting;
            }

            $elluminatelive->seats = $sparams->seats = $seats;
            $elluminatelive->name  = $elluminatelive->name;

            if (!$DB->update_record('elluminatelive', $elluminatelive)) {
                error('Could not update elluminatelive record.');
            }
        }

    /// Handle the editing of a recording description field.
        if (isset($data->descsave) && !empty($data->recordingid) &&
            ($canmanageanyrecordings || $canmanagerecordings)) {

            if ($recording = $DB->get_record('elluminatelive_recordings', array('id'=>$data->recordingid))) {
                $recording->description = $data->recordingdesc;
                $recording = $recording;

                if (!$DB->update_record('elluminatelive_recordings', $recording)) {
                    debugging('Unable to edit recording description!');
                }
            }
        }
    }

/// Handle a request to delete a recording.
    if (!empty($delrecording) &&
        ($candeleteanyrecordings || ($candeleterecordings && ($elluminatelive->creator == $USER->id)))) {

        if (!$recording = $DB->get_record('elluminatelive_recordings', array('id'=>$delrecording))) {
            error('Could not find meeting recording record');
        }

        if (optional_param('confirm', '', PARAM_ALPHANUM) == $sesskey) {
            if (elluminatelive_delete_recording($recording->recordingid)) {
                $DB->delete_records('elluminatelive_recordings', array('id'=>$recording->id));
                redirect($CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id,
                         get_string('deleterecordingsuccess', 'elluminatelive'), 4);
            } else {
                redirect($CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id,
                         get_string('deleterecordingfailure', 'elluminatelive'), 4);
            }

        } else {
            $buttonyes=$CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id . '&amp;delrecording=' . $recording->id . '&amp;confirm=' . $sesskey;
            $buttonno=$CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id;
            $deleterecordingmsg=get_string('deleterecordingconfirm', 'elluminatelive', userdate($recording->created));
            echo $OUTPUT->confirm($deleterecordingmsg, $buttonyes, $buttonno);
        }

		echo $OUTPUT->box_end();
        echo $OUTPUT->footer($course);
        exit;
    }

/// Hide a recording.
    if (!empty($hiderecording) && ($canmanageanyrecordings || $canmanagerecordings)) {
        if ($recording = $DB->get_record('elluminatelive_recordings', array('id'=>$hiderecording))) {
            $recording->visible = 0;
            $recording = $recording;

            if (!$DB->update_record('elluminatelive_recordings', $recording)) {
                debugging('Unable to hide recording!');
            }
        }
    }

/// Unhide a recording.
    if (!empty($showrecording) && ($canmanageanyrecordings || $canmanagerecordings)) {
        if ($recording = $DB->get_record('elluminatelive_recordings', array('id'=>$showrecording))) {
            $recording->visible = 1;
            $recording = $recording;

            if (!$DB->update_record('elluminatelive_recordings', $recording)) {
                debugging('Unable to change recording group visibility!');
            }
        }
    }

/// Make a recording visible to only group members.
    if (!empty($hidegrouprecording) && ($groupmode == VISIBLEGROUPS) &&
        ($canmanageanyrecordings || $canmanagerecordings)) {

        if ($recording = $DB->get_record('elluminatelive_recordings', array('id'=>$hidegrouprecording))) {
            $recording->groupvisible = 0;
            $recording = $recording;

            if (!$DB->update_record('elluminatelive_recordings', $recording)) {
                debugging('Unable to change recording group visibility!');
            }
        }
    }

/// Make a recording visible to only group members.
    if (!empty($showgrouprecording) && ($groupmode == VISIBLEGROUPS) &&
        ($canmanageanyrecordings || $canmanagerecordings)) {

        if ($recording = $DB->get_record('elluminatelive_recordings', array('id'=>$showgrouprecording))) {
            $recording->groupvisible = 1;
            $recording = $recording;

            if (!$DB->update_record('elluminatelive_recordings', $recording)) {
                debugging('Unable to hide recording!');
            }
        }
    }

    add_to_log($course->id, "elluminatelive", "view", "view.php?id=$cm->id", "$elluminatelive->id");

/// Determine if the current user can participate in this meeting.
    $participant = false;

    if ($elluminatelive->private) {
        $mctx = get_context_instance(CONTEXT_MODULE, $cm->id);
        $participant = ($elluminatelive->creator == $USER->id || $ismoderator || $isparticipant);
    } else {
        $participant = true;
    }

    $groupname = $DB->get_field('groups', 'name', array('id'=>$currentgroup));

    if (!empty($currentgroup)) {
        if (!empty($elluminatelive->customname)) {
            $elluminatelive->name = $elluminatelive->name . ' - ' . $course->shortname . ' - ' . $groupname;
        }
        if (!empty($elluminatelive->customdescription)) {
            $elluminatelive->description = $groupname . ' - ' . $elluminatelive->description;
        }
    }

    $formelements = array(
        get_string('name')                                 => $elluminatelive->name,
        get_string('elum_session_description','elluminatelive')  => $elluminatelive->description,
        get_string('meetingbegins', 'elluminatelive')      => userdate($elluminatelive->timestart),
        get_string('meetingends', 'elluminatelive')        => userdate($elluminatelive->timeend)
    );

    echo '<table align="center" cellpadding="5">' . "\n";

    foreach ($formelements as $key => $val) {
       echo '<tr valign="top">' . "\n";
       echo '<td align="right"><b>' . $key . ':</b></td><td align="left">' . $val . '</td>' . "\n";
       echo '</tr>' . "\n";
    }

/// Only handle seat reservation if groupmode is disabled for this activity.
    if (!empty($CFG->elluminatelive_seat_reservation) && !$groupmode && elluminatelive_seat_reservation_check()) {
        echo '<td align="right"><b>' . get_string('reservedseats', 'elluminatelive') . ':</b></td>' . "\n";

        if (!$canmanageseats || $hasfinished || empty($meeting)) {
            echo '<td align="left">' . $elluminatelive->seats . '</td>' . "\n";
        } else {
            $formelements = array(
                'id'        => $id,
                'course'    => $course->id,
                'meetingid' => $meeting->meetingid,
                'starttime' => $elluminatelive->timestart,
                'endtime'   => $elluminatelive->timeend,
                'sesskey'   => $sesskey
            );

            echo '<form name="editmeeting" action="' . $CFG->wwwroot . '/mod/elluminatelive/view.php" method="post">' . "\n";

            foreach ($formelements as $key => $val) {
                echo '<input type="hidden" name="' . $key . '" value="' . $val . '">' . "\n";
            }

            echo '<td align="left">' . "\n";
            echo '<input type="text" name="seats" size="6" maxlength="6" value="' . $elluminatelive->seats . '" />' . "\n";
            echo '<input type="submit" value="' . get_string('changeseats', 'elluminatelive') . '" />' . "\n";
            echo '<a onclick="checkAvailability(document.editmeeting);" href="#">' .
                 get_string('checkavailability', 'elluminatelive') . '</a>' . "\n";
            echo '</td>' . "\n";
            echo '</tr><tr valign="top">' . "\n";
            echo '</form>' . "\n";
        }
    }

    echo '</tr><tr>';
    echo '</table>';


    if ($canmanagemoderators && !$hasfinished) {
        $link = '<a href="' . $CFG->wwwroot . '/mod/elluminatelive/moderators.php?id=' . $elluminatelive->id .
                '">' . get_string('editmoderatorsforthismeeting', 'elluminatelive') . '</a>';

        echo '<p class="elluminateliveeditmoderators">' . $link . '</p>';
    }

    if ($elluminatelive->private && $canmanageparticipants && !$hasfinished) {
        $link = '<a href="' . $CFG->wwwroot . '/mod/elluminatelive/participants.php?id=' . $elluminatelive->id .
                '">' . get_string('editparticipantsforthismeeting', 'elluminatelive') . '</a>';

        echo '<p class="elluminateliveeditparticipants">' . $link . '</p>';
    }

    $link = '';

/// Deal with meeting preload files if the current user has the capability to do so.
    if ($canmanagepreloads && !$hasfinished) {
        $haspreload = false;

    /// Only display information about the preload file if the meeting hasn't finished yet.
        if (!empty($meeting->meetingid)) {
            if ($preloads = elluminatelive_list_meeting_preloads($meeting->meetingid)) {
                foreach ($preloads as $preload) {
                    if (!empty($haspreload)) {
                        continue;
                    }

                    if ($preload->type == ELLUMINATELIVE_PRELOAD_WHITEBOARD) {
                        $haspreload = $preload;
                    }
                }
            }
        }

        if ($haspreload) {
            $tooltip = get_string('deletepreloadfile', 'elluminatelive');

            $link = get_string('whiteboardpreloadfile', 'elluminatelive') . ': ' . $haspreload->name;
        }

        /// If the meeting hasn't even started yet, allow the user to delete this file.
        if ($haspreload) {
                //$link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/preload.php?id=' . $elluminatelive->id .
                //         '&amp;delete=' . $haspreload->preloadid . '" title="' . $tooltip .'"><img src="' .
                //         $CFG->pixpath . '/t/delete.gif" width="11" height="11" alt="' . $tooltip . '" /></a>';

                $link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/preload.php?id=' . $elluminatelive->id .
                         '&amp;delete=' . $haspreload->preloadid . '" title="' . $tooltip .'"><img src="' .
                         $OUTPUT->pix_url('/t/delete') . '" width="11" height="11" alt="' . $tooltip . '" /></a>';


    /// Display a link to upload a preload file if the meeting hasn't started yet.
        } else {
            $link = '<a href="' . $CFG->wwwroot . '/mod/elluminatelive/preload.php?id=' . $elluminatelive->id .
                    '">' . get_string('addwhiteboardpreload', 'elluminatelive') . '</a>';
        }

        if (!empty($link)) {
            echo '<p class="elluminatelivepreload">' . $link . '</p>';
        }
    }

/// Check if we need to override the boundary time.
    if (!empty($CFG->elluminatelive_boundary_default)) {
        $boundarytime = $CFG->elluminatelive_boundary_default;
    } else {
        $boundarytime = $elluminatelive->boundarytime;
    }

/// Only display a link to join the meeting if the current user is a participant
/// or moderator for this meeting and it is currently happening right now.
    if ($participant && $hasstarted && !$hasfinished) {
        if (!empty($elluminatelive->boundarytimedisplay) && !empty($boundarytime)) {
            echo '<p class="elluminateliveboundarytime">' . get_string('boundarytimemessage', 'elluminatelive', $boundarytime) . '</p>';//, 'center', '5', 'main elluminateliveboundarytime');
        }

        if (!empty($elluminatelive->recordingmode) && ($elluminatelive->timeend > $timenow)) {
            $recordingstring = '';

            switch ($elluminatelive->recordingmode) {
                case ELLUMINATELIVE_RECORDING_MANUAL:
                    $recordingstring = get_string('recordingmanual', 'elluminatelive');
                    break;
                case ELLUMINATELIVE_RECORDING_AUTOMATIC:
                    $recordingstring = get_string('recordingautomatic', 'elluminatelive');
                    break;
                case ELLUMINATELIVE_RECORDING_NONE:
                    $recordingstring = get_string('recordingnone', 'elluminatelive');
                    break;
            }

            if (!empty($recordingstring)) {
                echo '<p class="elluminateliverecordingmode">' . $recordingstring . '</p>';
            }
        }


        $link = '<a href="' . $CFG->wwwroot . '/mod/elluminatelive/loadmeeting.php?id=' . $elluminatelive->id .
                '" target="meeting">' . get_string('joinmeeting', 'elluminatelive') . '</a>';

        echo '<p class="elluminatelivejoinmeeting">' . $link . '</p>';

        echo get_string('supportlinktext', 'elluminatelive');
        echo '<a href="' . elluminatelive_support_link() . '" target="_blank"> here </a>';
        //echo '<p class="elluminateliveverifysetup">' . get_string('supportlinktext', 'elluminatelive', elluminatelive_support_link()) . '</a>';
    }

/// Display a link to play the recording if one exists.
    if (!empty($meeting) && $participant && $canviewrecordings &&
        ($recordings = $DB->get_records('elluminatelive_recordings', array('meetingid'=>$meeting->meetingid), 'created ASC'))) {

        $displayrecordings = array();

        foreach ($recordings as $recording) {
        /// Is this recording visible for non-managing users?
            if (!$canmanageanyrecordings && !$canmanagerecordings && !$recording->visible) {
                continue;
            }

        /// Check if we are hiding recordings of 0 bytes.
            if (!$canmanageanyrecordings &&
                (!empty($CFG->elluminatelive_hide_empty_recordings) && $recording->size === 0)) {

                continue;
            }

        /// If the activity is using separate groups and this user isn't a member of the specific
        /// group the recording is for, has this recording been made available to them?
            if ($groupmode == VISIBLEGROUPS && (
                    $currentgroup == 0 ||
                    ($currentgroup != 0 && !groups_is_member($currentgroup, $USER->id))
                ) && empty($recording->groupvisible)) {
                continue;
            }

            $link = '<a href="' . $CFG->wwwroot . '/mod/elluminatelive/loadrecording.php?id=' .
                    $recording->id . '" target="new">' . get_string('playrecording', 'elluminatelive') .
                    '</a> - ' . userdate($recording->created) . ' ' . display_size($recording->size);

        /// Include the recording description, if not empty.
            if (!empty($recording->description)) {
                $link .= ' - <span class="description">' . $recording->description . '</span>';
            }

            if ($canmanageanyrecordings || $canmanagerecordings) {
            /// Display an icon to allow editing the extra description field for this recording.
                $tooltip = get_string('editrecordingdescription', 'elluminatelive');

                //$link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/view.php?id= ' . $cm->id .
                //         '&amp;editrecordingdesc=' . $recording->id . '" title="' . $tooltip .
                //         '"><img src="' . $CFG->pixpath . '/i/edit.gif" width="11" height="11" alt="' .
                //         $tooltip .'" /></a>';
                $link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/view.php?id= ' . $cm->id .
                         '&amp;editrecordingdesc=' . $recording->id . '" title="' . $tooltip .
                         '"><img src="' . $OUTPUT->pix_url('/t/edit') . '" width="11" height="11" alt="' .
                         $tooltip .'" /></a>';
            }

            if ($candeleteanyrecordings || ($candeleterecordings && ($elluminatelive->creator == $USER->id))) {
                $tooltip = get_string('deletethisrecording', 'elluminatelive');

                $link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id .
                         '&amp;delrecording=' . $recording->id . '" title="' . $tooltip . '"><img src="' .
                         $OUTPUT->pix_url('/t/delete') . '" width="11" height="11" alt="' . $tooltip .
                         '"></a>';
            }

            if ($canmanageanyrecordings || $canmanagerecordings) {
            /// Display an icon to change the overall recording visibility.
                if ($recording->visible) {
                    $tooltip = get_string('hidethisrecording', 'elluminatelive');

                    $link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id .
                             '&amp;hiderecording=' . $recording->id . '" title="' . $tooltip . '"><img src="' .
                             $OUTPUT->pix_url('/t/hide') . '" width="11" ' . 'height="11" alt="' . $tooltip .
                             '"></a>';
                } else {
                    $tooltip = get_string('showthisrecording', 'elluminatelive');

                    $link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id .
                             '&amp;showrecording=' . $recording->id . '" title="' . $tooltip . '"><img src="' .
                             $OUTPUT->pix_url('/t/show') . '" width="11" height="11" alt="' . $tooltip .
                             '"></a>';
                }

            /// Display a group visibility setting if separate groups was used.
                if ($groupmode == VISIBLEGROUPS) {
                    if ($recording->groupvisible) {
                        $tooltip = get_string('recordinggroupvisibleall', 'elluminatelive');

                        $link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/view.php?id=' .
                                 $cm->id . '&amp;hidegrouprecording=' . $recording->id . '" title="' .
                                  $tooltip . '"><img  src="' . $OUTPUT->pix_url('/t/groupv') . '" width="11" height="11" alt="' . $tooltip . '" /></a>';
                    } else {
                        $tooltip = get_string('recordinggroupvisiblesingle', 'elluminatelive');

                        $link .= ' <a href="' . $CFG->wwwroot . '/mod/elluminatelive/view.php?id=' .
                                 $cm->id . '&amp;showgrouprecording=' . $recording->id . '" title="' .
                                  $tooltip . '"><img  src="' . $OUTPUT->pix_url('/t/groups') . '" width="11" height="11" alt="' . $tooltip . '" /></a>';
                    }
                }
            }

        /// Display the form to edit a recording description, if selected.
            if (!empty($editrecordingdesc) && ($editrecordingdesc == $recording->id) &&
                ($canmanageanyrecordings || $canmanagerecordings)) {

                $description = !empty($recording->description) ? $recording->description : '';

                $descform  = '<div class="elluminateliverecordingdescriptionedit">';
                $descform .= '<form action="view.php" method="post">';
                $descform .= '<input type="hidden" name="id" value="' . $cm->id . '" />';
                $descform .= '<input type="hidden" name="recordingid" value="' . $recording->id . '" />';
                $descform .= '<input type="hidden" name="sesskey" value="' . $sesskey . '" />';
                $descform .= get_string('description') . ': <input type="text" name="recordingdesc" size="50" maxlength="255" value="' .
                             $description  .'" />';
                $descform .= ' <input type="submit" name="descsave" value="' . get_string('savechanges') . '" />';
                $descform .= ' <input type="submit" name="cancel" value="' . get_string('cancel') . '" />';
                $descform .= '</form>';
                $descform .= '</div>';
            }

            $displayrecordings[] = '<p class="elluminateliverecording">' . $link . '</p>' .
                                   (!empty($descform) ? $descform : '');
            unset($descform);
        }

        if (!empty($displayrecordings)) {
            echo '<hr />';

            echo implode('', $displayrecordings);
        }
    }

/// Display an attendance page if attendance was recorded for this meeting.
    if (($canviewattendance || $canmanageattendance) && $elluminatelive->grade && $hasfinished) {
        $link =  '<a href="' . $CFG->wwwroot . '/mod/elluminatelive/attendance.php?id=' .
                 $elluminatelive->id . '">' . get_string('meetingattendance', 'elluminatelive') .
                 '</a>';

        echo '<p class="elluminateliveattendance">' . $link . '</p>';
    }
    if ($canmanagemoderators) {
    //if (($parameters = elluminatelive_get_meeting_parameters($meeting->meetingid)) && $canmanagemoderators) {
    	//echo '<tr>';
        //echo '<td align="center" colspan="2">';
    	print '<p class="elluminateliverecording"><a href="' . $CFG->wwwroot . '/mod/elluminatelive/externallink.php?id=' . $elluminatelive->id . '">Invite external users to meeting</a></p>';
    	//echo '</td></tr>';
    }

/// Finish the page

	echo $OUTPUT->box_end();
	echo $OUTPUT->footer($course);

?>
