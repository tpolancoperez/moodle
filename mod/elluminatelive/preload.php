<?php // $Id: preload.php,v 1.2.2.2 2009/10/22 14:28:24 jfilip Exp $

/**
 * Manage load a whiteboard preload file onto the ELM server.
 *
 * @version $Id: preload.php,v 1.2.2.2 2009/10/22 14:28:24 jfilip Exp $
 * @author Remote Learner - http://www.remote-learner.net/
 * @author Justin Filip <jfilip@remote-learner.net>
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

    global $DB;

    $PAGE->set_url('/mod/elluminatelive/preload.php');

    $id     = required_param('id', PARAM_INT);
    $delete = optional_param('delete', 0, PARAM_ALPHANUM);


    if (!$elluminatelive = $DB->get_record('elluminatelive', array('id'=>$id))) {
        error('Course module is incorrect');
    }
    if (!$course = $DB->get_record('course', array('id'=>$elluminatelive->course))) {
        error('Course is misconfigured');
    }
    if (!$cm = get_coursemodule_from_instance('elluminatelive', $elluminatelive->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

    require_course_login($course, true, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminatelive:managepreloads', $context);

/// Check to see if groups are being used here
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

    if (empty($currentgroup)) {
        $currentgroup = 0;
    }


    $baseurl = $CFG->wwwroot . '/mod/elluminatelive/preload.php?id=' . $elluminatelive->id;


/// Print the page header
    $strelluminatelives = get_string('modulenameplural', 'elluminatelive');
    $strelluminatelive  = get_string('modulename', 'elluminatelive');
    $straddpreload      = get_string('addwhiteboardpreload', 'elluminatelive');
    $strdelpreload      = get_string('deletewhiteboardpreload', 'elluminatelive');

    $buttontext = update_module_button($cm->id, $course->id, $strelluminatelive);
    $navigation = build_navigation(empty($delete) ? $straddpreload : $strdelpreload, $cm);

    print_header_simple(format_string($elluminatelive->name), '', $navigation, '', '', true,
                        $buttontext, navmenu($course, $cm));

/// Delete a preload file for this meeting.
    if (!empty($delete)) {
        if ($meeting = $DB->get_record('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id,'groupid'=>$currentgroup))) {

            if (!empty($meeting->meetingid)) {
                if ($preloads = elluminatelive_list_meeting_preloads($meeting->meetingid)) {
                    foreach ($preloads as $preload) {
                        if ($preload->preloadid == $delete) {
                        /// Delete the preload from the meeting.
                            //if (!elluminatelive_delete_meeting_preload($preload->preloadid, $meeting->meetingid)) {
                            //    print_error('preloaddeletemeetingerror', 'elluminatelive', $baseurl);
                            //}

                        /// Delete the preload itself.
                            if (!elluminatelive_delete_preload($preload->preloadid)) {
                                print_error('preloaddeleteerror', 'elluminatelive', $baseurl);
                            }

                            redirect($CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id,
                                     get_string('preloaddeletesuccess', 'elluminatelive'), 5);
                        }
                    }
                }
            }
        }
    }


    if (($data = data_submitted($CFG->wwwroot . '/mod/elluminatelive/preload.php')) && confirm_sesskey()) {
        if (!empty($_FILES['whiteboard'])) {
            $filename = $_FILES['whiteboard']['name'];
            $filepath = $_FILES['whiteboard']['tmp_name'];
            $filemime = $_FILES['whiteboard']['type'];
            $filesize = $_FILES['whiteboard']['size'];

            if (empty($filesize)) {
                print_error('preloademptyfile', 'elluminatelive', $baseurl);
            }

        /// Make sure the file uses a valid whiteboard preload file extension.
            if (!eregi('\.([a-zA-Z0-9]+)$', $filename, $match)) {
                print_error('preloadnofileextension', 'elluminatelive', $baseurl);
            }

            if (!isset($match[1])) {
                print_error('preloadnofileextension', 'elluminatelive', $baseurl);
            }

            if (strtolower($match[1]) != 'wbd') {
                print_error('preloadinvalidfileextension', 'elluminatelive', $baesurl);
            }

        /// Ensure that the document actually contains XML.
            if (!simplexml_load_file($filepath)) {
                print_error('preloadinvalidfilecontents', 'elluminatelive', $baseurl);
            }

        /// The file is valid, let's proceed with syncing the meeting and creating the preload.
            if (!elluminatelive_sync_meeting($elluminatelive, $cm)) {
                print_error('couldnotsyncmeeting', 'elluminatelive', $baseurl);
            }

            if (!$elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$USER->id))) {
                print_error('couldnotsyncmeeting', 'elluminatelive', $baseurl);
            }

            if (!$meeting = $DB->get_record('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id,'groupid'=>$currentgroup))) {

                print_error('couldnotsyncmeeting', 'elluminatelive', $baseurl);
            }

        /// Create the preload object on the ELM server.
            $preload = elluminatelive_create_preload('whiteboard', $filename, $filemime, $filesize, $elmuser->elm_id);

            if (empty($preload->preloadid)) {
                print_error('preloadcouldnotcreatepreload', 'elluminatelive', $baseurl);
            }

        /// Read the file contents into memory.
            if (!$filedata = file_get_contents($filepath)) {
                print_error('preloadcouldnotreadfilecontents', 'elluminatelive', $baseurl);
            }

        /// Send the contents of the file to the ELM server to associate with the preload object.
            if (!elluminatelive_stream_preload($preload->preloadid, $filesize, $filedata)) {
                print_error('preloadcouldnotstreamepreload', 'elluminatelive', $baseurl);
            }

        /// Associate the preload object with the meeting.
            if (!elluminatelive_add_meeting_preload($preload->preloadid, $meeting->meetingid)) {
                print_error('preloadcouldnotaddpreloadtomeeting', 'elluminatelive', $baseurl);
            }

            redirect($CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id,
                     get_string('preloaduploadsuccess', 'elluminatelive'), 5);
        }
    }


    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/elluminatelive/preload.php?id=' . $elluminatelive->id, false, true);

    $sesskey = !empty($USER->sesskey) ? $USER->sesskey : '';

/// Print the main part of the page
    //print_simple_box_start('center', '50%');
    echo $OUTPUT->box_start('generalbox', 'notice');

    echo '<p>'. get_string('preloadchoosewhiteboardfile', 'elluminatelive') . '</p>';
    echo '<form action="preload.php" method="post" enctype="multipart/form-data">';
    echo '<input type="hidden" name="sesskey" value="' . $sesskey . '" />';
    echo '<input type="hidden" name="id" value="' . $elluminatelive->id . '" />';
    echo '<input type="file" name="whiteboard" alt="whiteboard" size="50" /><br />';
    echo '<input type="submit" value="' . get_string('uploadthisfile') . '" /><br />';
    echo '<input type="button" value="' . get_string('cancel') . '" onclick="document.location = \'' .
         $CFG->wwwroot . '/mod/elluminatelive/view.php?id=' . $cm->id . '\'" />';
    echo '</form>';

    print_simple_box_end();

/// Finish the page
    //print_footer($course);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer($course);

?>
