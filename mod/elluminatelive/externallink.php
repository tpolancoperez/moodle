<?php // $Id: externallink.php,v 1.3 2008/08/20 14:16:14 mchurch Exp $

/**
 * Used to post a link to the elluminate moderators that they can give to external participants.
 */

    require_once('../../config.php');
    require_once($CFG->dirroot . '/mod/elluminatelive/lib.php');

    global $DB;

    $id           = required_param('id', PARAM_INT);

    $PAGE->set_url('/mod/elluminatelive/externallink.php');

    if (!$elluminatelive = $DB->get_record('elluminatelive', array('id'=>$id))) {
        error('Missing elluminate live record.');
    }
    
    if (!$course = $DB->get_record('course', array('id' => $elluminatelive->course))) {
        error('Invalid course.');
    }

    if (! $cm = get_coursemodule_from_instance("elluminatelive", $elluminatelive->id, $course->id)) {
        error('Invalid course module.');
    }

    if (!elluminatelive_sync_meeting($elluminatelive, $cm)) {
        error(get_string('errormeetingsync', 'elluminatelive'));
    }

    if (!$meeting = $DB->get_record('elluminatelive_session', array('elluminatelive' => $id))) {
        print_error('You must specify a valid meeting ID.');
    }
    

/// Some capability checks.
    require_course_login($course, true, $cm);
    $modcontext = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminatelive:view', $modcontext);
    require_capability('mod/elluminatelive:managemoderators', $modcontext);

/// Print header.

    $cronreturn=elluminatelive_cron();
    $navigation = build_navigation('External Link', $cm);
    print_header_simple(format_string($elluminatelive->name), "", $navigation, "", "", true, '');

    print "<p>This link is for participants who do not have a Oakland University NetID account, or who are not participants in this course. Students in the course will not need to be emailed this link, as they can just click on the Join meeting link on the previous page. This link should be distributed carefully - anybody with this link will be able to access this meeting.  To invite others to the meeting, copy the URL below and send it to them.  If this is a public meeting, this link can also be placed on a web site.</p>";
    print "<p>https://elluminate.oakland.edu/join_meeting.html?meetingId=".$meeting->meetingid."</p>";

    echo $OUTPUT->footer($course);

?>
