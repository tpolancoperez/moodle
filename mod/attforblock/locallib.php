<?php
global $CFG;
require_once($CFG->libdir.'/gradelib.php');

define('ONE_DAY', 86400);   // Seconds in one day
define('ONE_WEEK', 604800);   // Seconds in one week

function show_tabs($cm, $context, $currenttab='sessions')
{
	$toprow = array();
    if (has_capability('mod/attforblock:manageattendances', $context) or
            has_capability('mod/attforblock:takeattendances', $context) or
            has_capability('mod/attforblock:changeattendances', $context)) {
        $toprow[] = new tabobject('sessions', 'manage.php?id='.$cm->id,
                    get_string('sessions','attforblock'));
    }

    if (has_capability('mod/attforblock:manageattendances', $context)) {
        $toprow[] = new tabobject('add', "sessions.php?id=$cm->id&amp;action=add",
                    get_string('add','attforblock'));
    }
    if (has_capability('mod/attforblock:viewreports', $context)) {
	    $toprow[] = new tabobject('report', 'report.php?id='.$cm->id,
	                get_string('report','attforblock'));
    }
    if (has_capability('mod/attforblock:export', $context)) {
	    $toprow[] = new tabobject('export', 'export.php?id='.$cm->id,
	                get_string('export','quiz'));
    }
    if (has_capability('mod/attforblock:changepreferences', $context)) {
	    $toprow[] = new tabobject('settings', 'attsettings.php?id='.$cm->id,
                    get_string('settings','attforblock'));
    }

    $tabs = array($toprow);
    print_tabs($tabs, $currenttab);
}


//getting settings for course

function get_statuses($courseid, $onlyvisible = true)
{
    global $DB;
    if ($onlyvisible) {
        $result = $DB->get_records_select('attendance_statuses', "courseid = :courseid AND visible = 1 AND deleted = 0", array("courseid"=>$courseid), 'grade DESC');
    } else {
        $result = $DB->get_records_select('attendance_statuses', "courseid = :courseid AND deleted = 0", array("courseid"=>$courseid), 'grade DESC');
    }
    return $result;
}	

//gets attendance status for a student, returns count

function get_attendance($userid, $course, $statusid=0)
{
    global $CFG, $DB;
    $qry = "SELECT count(*) as cnt";
    $qry.= " FROM {attendance_log} al";
    $qry.= " JOIN {attendance_sessions} ats";
        $qry.= " ON al.sessionid = ats.id";
    $qry.= " WHERE ats.courseid = :courseid";
        $qry.= " AND al.studentid = :userid";

    $params = array("courseid" => $course->id
                , "userid" => $userid
                );

    if ($statusid) {
        $qry .= " AND al.statusid = :statusid";
        $params["statusid"] = $statusid;
    }

    return $DB->count_records_sql($qry,$params);
}

function get_grade($userid, $course)
{
    global $CFG, $DB;
    $sql = "SELECT l.id";
    $sql.= ", l.statusid";
    $sql.= ", l.statusset";
    $sql.= " FROM {attendance_log} l";
    $sql.= " JOIN {attendance_sessions} s";
        $sql.= " ON l.sessionid = s.id";
    $sql.= " WHERE l.studentid = :userid";
        $sql.= " AND s.courseid  = :courseid";
        

    $params = array("userid"=>$userid
                , "courseid"=>$course->id
                );
    
    $logs = $DB->get_records_sql($sql, $params);
    $result = 0;
    if ($logs) {
        $records = $DB->get_records('attendance_statuses', array('courseid'=>$course->id));
        $stat_grades = recordstomenu($records, 'id', 'grade');  //bandaid for deprecated function records_to_menu(), there should be a better way than defininh recordstomenu() in locallib.php
        foreach ($logs as $log) {
            $result += $stat_grades[$log->statusid];
        }
    }

    return $result;
}

//temporary solution, for support PHP 4.3.0 which minimal requirement for Moodle 1.9.x
function local_array_intersect_key($array1, $array2) {
    $result = array();
    foreach ($array1 as $key => $value) {
        if (isset($array2[$key])) {
            $result[$key] = $value;
        }
    }
    return $result;
}

function get_maxgrade($userid, $course)
{
	global $CFG, $DB;
        $sql = "SELECT l.id, l.statusid, l.statusset";
        $sql.= " FROM {attendance_log} l";
        $sql.= " JOIN {attendance_sessions} s";
            $sql.= " ON l.sessionid = s.id";
        $sql.= " WHERE l.studentid = :userid";
            $sql.= " AND s.courseid  = :courseid";

        $params = array("userid"=>$userid
                        , "courseid"=>$course->id
                        );

	$logs = $DB->get_records_sql($sql, $params);
	$maxgrade = 0;
	if ($logs) {
                $records = $DB->get_records('attendance_statuses', array('courseid'=>$course->id));
		$stat_grades = recordstomenu($records, 'id', 'grade'); //bandaid for deprecated function records_to_menu(), there should be a better way than defininh recordstomenu() in locallib.php
		foreach ($logs as $log) {
			$ids = array_flip(explode(',', $log->statusset));
//			$grades = array_intersect_key($stat_grades, $ids); // require PHP 5.1.0 and higher
			$grades = local_array_intersect_key($stat_grades, $ids); //temporary solution, for support PHP 4.3.0 which minimal requirement for Moodle 1.9.x
			$maxgrade += max($grades);
		}
	}
	
	return $maxgrade;
}

function get_percent_adaptive($userid, $course) // NOT USED
{
	global $CFG, $DB;
        $sql = "SELECT l.id, l.statusid, l.statusset";
        $sql.= " FROM {attendance_log} l";
        $sql.= " JOIN {attendance_sessions} s";
            $sql.= " ON l.sessionid = s.id";
        $sql.= " WHERE l.studentid = :userid";
            $sql.= " AND s.courseid  = :courseid";
        $params = array("userid"=>$userid
                    , "courseid"=>$course->id
                    );
	$logs = $DB->get_records_sql($sql, $params);
	$result = 0;
	if ($logs) {
                $records = $DB->get_records('attendance_statuses', array('courseid'=>$course->id));
		$stat_grades = recordstomenu($records, 'id', 'grade'); //bandaid for deprecated function records_to_menu(), there should be a better way than defininh recordstomenu() in locallib.php
		
		$percent = 0;
		foreach ($logs as $log) {
			$ids = array_flip(explode(',', $log->statusset));
			$grades = array_intersect_key($stat_grades, $ids);
			$delta = max($grades) - min($grades);
			$percent += $stat_grades[$log->statusid] / $delta;
		}
		$result = $percent / count($logs) * 100;
	}
	if (!$dp = grade_get_setting($course->id, 'decimalpoints')) {
		$dp = $CFG->grade_decimalpoints;
	}
	
	return sprintf("%0.{$dp}f", $result);
}

function get_percent($userid, $course)
{
    global $CFG;
    
    $maxgrd = get_maxgrade($userid, $course);
    if ($maxgrd == 0) {
    	$result = 0;
    } else {
    	$result = get_grade($userid, $course) / $maxgrd * 100;
    }
    if ($result < 0) {
        $result = 0;
    }
	if (!$dp = grade_get_setting($course->id, 'decimalpoints')) {
		$dp = $CFG->grade_decimalpoints;
	}

	return sprintf("%0.{$dp}f", $result);
}

function set_current_view($courseid, $view) {
    global $SESSION;

    return $SESSION->currentattview[$courseid] = $view;
}

function get_current_view($courseid) {
    global $SESSION;

    if (isset($SESSION->currentattview[$courseid]))
        return $SESSION->currentattview[$courseid];
    else
        return 'all';
}

function print_attendance_table_row($left, $right) {
    echo "\n<tr><td nowrap=\"nowrap\" align=\"right\" valign=\"top\" class=\"cell c0\">$left</td><td align=\"left\" valign=\"top\" class=\"info c1\">$right</td></tr>\n";
}

function print_attendance_table($user,  $course) {

	$complete = get_attendance($user->id, $course);
	$percent = get_percent($user->id, $course).'&nbsp;%';
	$grade = get_grade($user->id, $course);
	
    echo '<table border="0" cellpadding="0" cellspacing="0" class="list">';
    print_attendance_table_row(get_string('sessionscompleted','attforblock').':', "<strong>$complete</strong>");
    $statuses = get_statuses($course->id);
	foreach($statuses as $st) {
		print_attendance_table_row($st->description.': ', '<strong>'.get_attendance($user->id, $course, $st->id).'</strong>');
	}
    print_attendance_table_row(get_string('attendancepercent','attforblock').':', "<strong>$percent</strong>");
    print_attendance_table_row(get_string('attendancegrade','attforblock').':', "<strong>$grade</strong> / ".get_maxgrade($user->id, $course));
    print_attendance_table_row('&nbsp;', '&nbsp;');
  	echo '</table>';
	
}

function print_user_attendaces($user, $cm,  $course = 0, $printing = null) {
    global $CFG, $COURSE, $mode, $DB, $OUTPUT;
		
    echo '<table class="userinfobox">';
    if (!$printing) {
            echo '<tr>';
	    echo '<td colspan="2" class="generalboxcontent"><div align="right">';
            echo $OUTPUT->help_icon('studentview', 'attforblock');
            echo "<a href=\"view.php?id={$cm->id}&amp;student={$user->id}&amp;mode=$mode&amp;printing=yes\" target=\"_blank\">[".get_string('versionforprinting','attforblock').']</a></div></td>';
	    echo '</tr>';
    }

    echo '<tr>';
    echo '<td class="left side">';
    echo $OUTPUT->user_picture($user, array("courseid"=>$COURSE->id));
    echo '</td>';
    echo '<td class="generalboxcontent">';
    echo '<font size="+1"><b>'.fullname($user).'</b></font>';
	if ($course) {
		echo '<hr />';
		$complete = get_attendance($user->id, $course);
		if($complete) {
			print_attendance_table($user,  $course);
		} else {
			echo get_string('attendancenotstarted','attforblock');
		}
	} else {
            $stqry = "SELECT ats.courseid";
            $stqry.= " FROM {attendance_log} al";
            $stqry.= " JOIN {attendance_sessions} ats";
                $stqry.= " ON al.sessionid = ats.id";
            $stqry.= " WHERE al.studentid = :userid";
            $stqry.= " GROUP BY ats.courseid";
            $stqry.= " ORDER BY ats.courseid asc";
            $recs = $DB->get_records_sql($stqry, array("userid" => $user->id));

            foreach ($recs as $id => $rec) {
                $nextcourse = $DB->get_record('course', array('id'=>$rec->courseid));

                echo '<hr />';
                echo '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="list1">';
                echo '<tr><td valign="top"><strong>'.$nextcourse->fullname.'</strong></td>';
                echo '<td align="right">';
                $complete = get_attendance($user->id, $nextcourse);
                if($complete) {
                        print_attendance_table($user,  $nextcourse);
                } else {
                        echo get_string('attendancenotstarted','attforblock');
                }
                echo '</td></tr>';
                echo '</table>';
            }
	}

	
	if ($course) {
		$stqry = "SELECT ats.id,ats.sessdate,ats.description,al.statusid,al.remarks";
                $stqry.= " FROM {attendance_log} al";
                $stqry.= " JOIN {attendance_sessions} ats";
                    $stqry.= " ON al.sessionid = ats.id";
                $stqry.= " WHERE ats.courseid = :courseid";
                    $stqry.= " AND al.studentid = :userid";
                $stqry.= " ORDER BY ats.sessdate asc";

                $params = array("courseid"=>$course->id
                                , "userid"=>$user->id);


		if ($sessions = $DB->get_records_sql($stqry, $params)) {
	     	$statuses = get_statuses($course->id);
	     	?>
			<div id="mod-assignment-submissions">
			<table align="left" cellpadding="3" cellspacing="0" class="submissions">
			  <tr>
				<th>#</th>
				<th align="center"><?php print_string('date')?></th>
				<th align="center"><?php print_string('time')?></th>
				<th align="center"><?php print_string('description','attforblock')?></th>
				<th align="center"><?php print_string('status','attforblock')?></th>
				<th align="center"><?php print_string('remarks','attforblock')?></th>
			  </tr>
			  <?php 
		  	$i = 1;
			foreach($sessions as $key=>$session)
			{
			  ?>
			  <tr>
				<td align="center"><?php echo $i++;?></td>
				<td><?php echo userdate($session->sessdate, get_string('strftimedmyw', 'attforblock')); //userdate($students->sessdate,'%d.%m.%y&nbsp;(%a)', 99, false);?></td>
				<td><?php echo userdate($session->sessdate, get_string('strftimehm', 'attforblock')); ?></td>
				<td><?php echo empty($session->description) ? get_string('nodescription', 'attforblock') : $session->description;  ?></td>
				<td><?php echo $statuses[$session->statusid]->description ?></td>
				<td><?php echo $session->remarks;?></td>
			  </tr>
			  <?php
	  		}
	  		echo '</table>';
		} else {
			echo $OUTPUT->heading(get_string('noattforuser','attforblock'));
		}
	}
	echo '</td></tr><tr><td>&nbsp;</td></tr></table></div>';
}

/*
 *  function records_to_menu() was deprecated, this replaces it only for the attendance module plugin
 */
function recordstomenu($records, $field1, $field2) {
    $menu = array();
    foreach ($records as $record) {
        $menu[$record->$field1] = $record->$field2;
    }

    if (!empty($menu)) {
        return $menu;
    } else {
        return false;
    }
}


/**
 * Because getting a list of users to display for attendance should no longer
 * use the 'moodle/legacy:student' capability.  Also depending on how Moodle has
 * it's capabilities setup the exact capabilities here may need to be changed.
 *
 * @param $context 
 * @param $currentgroup limit users to current group
 * @return array of user objects
 */
function get_users_for_attendance($context, $sort = null, $currentgroup = null) {
    if(empty($sort)){
        $sort =  'lastname';
    }

    $attendance_takers = array_keys(get_users_by_capability($context, 'mod/attforblock:takeattendances', 'u.id', '', '', '', '', '', false));

    if ($currentgroup) {
        $students = get_users_by_capability($context, 'mod/attforblock:view', '', "u.$sort ASC", '', '', $currentgroup, $attendance_takers, false);
    } else {
        $students = get_users_by_capability($context, 'mod/attforblock:view', '', "u.$sort ASC", '', '', '', $attendance_takers, false);
    }
    
    return $students;
}
?>
