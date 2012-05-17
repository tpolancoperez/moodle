<?php // $Id: attendance.php,v 1.1.2.4 2009/10/22 14:28:23 jfilip Exp $

/**
 * Displays an attendance report for a meeting configured to track attendance.
 *
 * @version $Id: attendance.php,v 1.1.2.4 2009/10/22 14:28:23 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';
    require_once $CFG->libdir . '/tablelib.php';

    global $DB;

    $id = required_param('id', PARAM_INT);

    if (!$meeting = $DB->get_record('elluminatelive', array("id" => $id))) {
        error('Incorrect meeting ID (' . $id . ')');
    }

    if (!$course = $DB->get_record('course', array("id" => $meeting->course))) {
        error('Invalid course!');
    }

    if (!$cm = get_coursemodule_from_instance("elluminatelive", $meeting->id, $course->id)) {
        error('Invalid course module.');
    }

    $meeting->cmidnumber = $cm->id;

/// Some capability checks.
    require_course_login($course, true, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/elluminatelive:viewattendance', $context);
    $canmanage = has_capability('mod/elluminatelive:manageattendance', $context);

    require_course_login($course, true, $cm);


/// Check to see if groups are being used here
    $groupmode    = groups_get_activity_groupmode($cm);
    $currentgroup = groups_get_activity_group($cm, true);

    if (empty($currentgroup)) {
        $currentgroup = 0;
    }

/// Process any attendance modifications.
    if ($canmanage && ($data = data_submitted($CFG->wwwroot . '/mod/elluminatelive/attendance.php')) && confirm_sesskey()) {
        foreach ($data->userids as $idx => $userid) {
            if ($data->attendance[$idx] > 0) {
                if ($ea = $DB->get_record('elluminatelive_attendance', array("userid"=>$userid,"elluminateliveid"=>$meeting->id))) {
                    if (empty($ea->grade)) {
                        $ea->grade = $meeting->grade;

                        $DB->update_record('elluminatelive_attendance', $ea);
                        elluminatelive_update_grades($meeting, $userid);
                    }

                } else {
                    $ea = new Object();
                    $ea->userid           = $userid;
                    $ea->elluminateliveid = $meeting->id;
                    $ea->grade            = $meeting->grade;

                    $DB->insert_record('elluminatelive_attendance', $ea);
                    elluminatelive_update_grades($meeting, $userid);
                }
            } else {
                if ($ea = $DB->get_record('elluminatelive_attendance', array("userid"=>$userid,"elluminateliveid"=>$meeting->id))) {
                    if (!empty($ea->grade)) {
                        $ea->grade = 0;

                        $DB->update_record('elluminatelive_attendance', $ea);
                        elluminatelive_update_grades($meeting, $userid);
                    }
                }
            }
        }
    }


    $strattendancefor   = get_string('attendancefor', 'elluminatelive', stripslashes($meeting->name));
    $strelluminatelives = get_string('modulenameplural', 'elluminatelive');
    $strelluminatelive  = get_string('modulename', 'elluminatelive');

/// Print header.
    $navigation = build_navigation($strattendancefor, $cm);
    print_header_simple(format_string($meeting->name), "",
                        $navigation, "", "", true, '');

    groups_print_activity_menu($cm, 'attendance.php?id=' . $meeting->id, false, true);

/// Get a list of user IDs for students who are allowed to participate in this meeting.
    $userids = array();
    if ($meeting->private) {
    /// Get meeting participants.
        if ($participants = get_users_by_capability($context, 'mod/elluminatelive:joinmeeting', 'u.id, u.username',
                                                    'u.lastname, u.firstname', '', '', '', '', false)) {

            $userids = array_keys($participants);
        }
    } else {
        $allusers = get_users_by_capability($context, 'mod/elluminatelive:view', 'u.id',
                                            'u.id', '', '', $currentgroup, '', false);
        $userids = array_keys($allusers);
    }

/// If groupmembersonly used, remove users who are not in any group
    if (!empty($userids) and !empty($CFG->enablegroupings) and $cm->groupmembersonly) {
        $userids = array_intersect($userids, array_keys(groups_get_grouping_members($cm->groupingid, 'distinct u.id', 'u.id')));
    }

/// Only care about non-moderators of the activity.
    if ($moderators = get_users_by_capability($context, 'mod/elluminatelive:moderatemeeting', 'u.id',
                                              'u.id', '', '', '', '', false)) {

        $userids = implode(',', array_diff($userids, array_keys($moderators)));
    } else {
        $userids = implode(', ', $userids);
    }

    // Build the select statement. Start where as blank
    $selectfrom = 'SELECT u.id, u.firstname, u.lastname FROM {user} u ';
    $order  = 'ORDER BY u.lastname ASC, u.firstname ASC ';
    // If there are userids, build the where statement to find those ids then run the final SQL
    if (!empty($userids)) {
        $depends_on = array($userids);
        list($usql, $params) = $DB->get_in_or_equal($depends_on);
		$where=' WHERE u.id $usql ';
        $sql="$selectfrom$where$order";
        $usersavail = $DB->get_records_sql($sql,$params);
	} else {
        $sql="$selectfrom$order";
        $usersavail = $DB->get_records_sql($sql);
    }


    $table = new flexible_table('meeting-attendance-', $meeting->id);

    $tablecolumns = array('fullname', 'attended');
    $tableheaders = array(get_string('fullname'), get_string('attended', 'elluminatelive'));

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($CFG->wwwroot . '/mod/elluminatelive/attendance.php?id=' . $meeting->id);

    $table->set_attribute('cellspacing', '1');
    $table->set_attribute('cellpadding', '8');
    $table->set_attribute('align', 'center');
    $table->set_attribute('class', 'generaltable generalbox');

    $table->setup();

    if (!empty($usersavail)) {
        $stryes = get_string('yes');
        $strno  = get_string('no');
        $yesno  = array(0 => $strno, 1 => $stryes);

        foreach ($usersavail as $useravail) {
			$params=array($useravail->id,$meeting->id);
            $sql = "SELECT a.*
                    FROM {elluminatelive_attendance} a
                    WHERE a.userid = ?
                    AND a.elluminateliveid = ?
                    AND a.grade > 0";

        /// Display different form items depending on whether we're using a scale
        /// or numerical value for an attendance grade.
            $attended = $DB->get_record_sql($sql, $params);

            if ($canmanage) {
                if ($attended) {
                    if ($meeting->grade > 0) {
                        $select = choose_from_menu($yesno, 'attendance[]', 1, NULL, '', '', true);
                    } else {
                        $select = choose_from_menu(make_grades_menu($meeting->grade), 'attendance[]', $attended->grade, get_string('no'), '', -1, true);
                    }
                } else {
                    if ($meeting->grade > 0) {
                        $select = choose_from_menu($yesno, 'attendance[]', 0, NULL, '', '', true);
                    } else {
                        $select = choose_from_menu(make_grades_menu($meeting->grade), 'attendance[]', -1, get_string('no'), '', -1, true);
                    }
                }
            } else {
                if ($attended) {
                    $select = $stryes;
                } else {
                    $select = $strno;
                }
            }

            $formelem = $canmanage ? '<input type="hidden" name="userids[]" value="' . $useravail->id . '" />' : '';
            $table->add_data(array($formelem . fullname($useravail), $select));
        }
    }

    if ($meeting->grade < 0) {
        print_heading(get_string('attendancescalenotice', 'elluminatelive'), 'center', '3');
    }

    $sesskey = !empty($USER->sesskey) ? $USER->sesskey : '';

    print_simple_box_start('center', '50%');

    if ($canmanage && !empty($usersavail)) {
        echo '<form input action="' . $CFG->wwwroot . '/mod/elluminatelive/attendance.php" method="post">';
        echo '<input type="hidden" name="id" value="' . $meeting->id . '"/>';
        echo '<input type="hidden" name="sesskey" value="' . $sesskey . '" />';

        $table->print_html();

        echo '<center><input type="submit" value="' . get_string('updateattendance', 'elluminatelive') . '" />';
        echo '</form>';
    } else {
        $table->print_html();
    }

    print_simple_box_end();

    print_footer($course);

?>
