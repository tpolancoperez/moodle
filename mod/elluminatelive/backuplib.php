<?php //$Id: backuplib.php,v 1.1.2.3 2009/10/22 14:28:24 jfilip Exp $

/**
 * This php script contains all the stuff to backup/restore
 * elluminatelive mods
 *
 * @version $Id: backuplib.php,v 1.1.2.3 2009/10/22 14:28:24 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */

    //This is the "graphical" structure of the elluminatelive mod:
    //
    //                               elluminatelive
    //                            (CL,pk->id)
    //                                 |
    //         ---------------------------------------------------
    //         |                                                 |
    //    elluminatelive_attendance                    elluminatelive_recordings
    //(UL,pk->id, fk->elluminateliveid)-------------(UL,pk->id, fk->meetingid)
    //
    //
    //                          elluminatelive_users
    //                         (UL,pk->id,fk->userid)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

   ////Return an array of info (name,value)
   function elluminatelive_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {

       global $DB;

       if (!empty($instances) && is_array($instances) && count($instances)) {
           $info = array();
           foreach ($instances as $id => $instance) {
               $info += elluminatelive_check_backup_mods_instances($instance,$backup_unique_code);
           }
           return $info;
       }
        //First the course data
        $info[0][0] = get_string("modulenameplural","elluminatelive");
        $info[0][1] = $DB->count_records('elluminatelive', array('course'=>$course));

        //Now, if requested, the user_data
        if ($user_data) {
        /// Sessions
            $info[1][0] = get_string('sessions', 'elluminatelive');

			$params=array($course);
            $sql = "SELECT COUNT(es.id)
                    FROM {elluminatelive_session} es
                    INNER JOIN {elluminatelive} e ON e.id = es.elluminatelive
                    WHERE e.course = ?";
            $info[1][1] = $DB->count_records_sql($sql,$params);

        /// Attendance
            $info[2][0] = get_string('attendance', 'elluminatelive');
			$params=array($course);
            $sql = "SELECT COUNT(ea.id)
                    FROM {elluminatelive_attendance} ea
                    INNER JOIN {elluminatelive} e ON e.id = ea.elluminateliveid
                    WHERE e.course = ?";
            $info[2][1] = $DB->count_records_sql($sql,$params);

        /// Recordings
            $info[3][0] = get_string('recordings', 'elluminatelive');
			$params=array($course);
            $sql = "SELECT COUNT(er.id)
                    FROM {elluminatelive_recordings} er
                    INNER JOIN {elluminatelive_session} es ON es.meetingid = er.meetingid
                    INNER JOIN {elluminatelive} e ON e.id = es.elluminatelive
                    WHERE e.course = ?";
            $info[3][1] = $DB->count_records_sql($sql,$params);

        /// Elluminatelive users
            $info[4][0] = get_string('elluminateliveusers', 'elluminatelive');
            $ids = elluminatelive_user_ids_by_course ($course);
            $info[4][1] = count($ids);
        }
        return $info;
    }

    function elluminatelive_check_backup_mods_instances($instance,$backup_unique_code) {
        global $CFG, $DB;

        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';
        if (!empty($instance->userdata)) {

        /// Sessions
            $info[$instance->id.'1'][0] = get_string('sessions', 'elluminatelive');
			$params=array($instance->id);
            $sql = "SELECT COUNT(es.id)
                    FROM {elluminatelive_session} es
                    WHERE es.elluminatelive = ?";
            $info[$instance->id.'1'][1] = $DB->count_records_sql($sql,$params);

        /// Attendance
            $info[$instance->id.'2'][0] = get_string('attendance', 'elluminatelive');
			$params=array($instance->id);
            $sql = "SELECT COUNT(ea.id)
                    FROM {elluminatelive_attendance} ea
                    WHERE ea.elluminateliveid = ?";
            $info[$instance->id.'2'][1] = $DB->count_records_sql($sql,$params);

        /// Recordings
            $info[$instance->id.'3'][0] = get_string('recordings', 'elluminatelive');
			$params=array($instance->id);
            $sql = "SELECT COUNT(er.id)
                    FROM {elluminatelive_recordings} er
                    INNER JOIN {elluminatelive_session} es ON es.meetingid = er.meetingid
                    WHERE es.elluminatelive = ?";
            $info[$instance->id.'3'][1] = $DB->count_records_sql($sql,$params);

        /// Elluminatelive users
            $info[$instance->id.'4'][0] = get_string('elluminateliveusers', 'elluminatelive');
            $ids = elluminatelive_user_ids_by_instance($instance->id);
            $info[$instance->id.'4'][1] = count($ids);
        }
        return $info;
    }

    function elluminatelive_backup_mods($bf,$preferences) {

        global $CFG, $DB;

        $status = true;

        //Iterate over elluminatelive table
        $elluminatelives = $DB->get_records("elluminatelive",array("course"=>$preferences->backup_course),"id");
        if ($elluminatelives) {
            foreach ($elluminatelives as $elluminatelive) {
                if (backup_mod_selected($preferences,'elluminatelive',$elluminatelive->id)) {
                    $status = elluminatelive_backup_one_mod($bf,$preferences,$elluminatelive);
                    // backup files happens in backup_one_mod now too.
                }
            }
        }
        return $status;
    }


    function elluminatelive_backup_one_mod($bf,$preferences,$elluminatelive) {

        global $CFG, $DB;

        if (is_numeric($elluminatelive)) {
            $elluminatelive = $DB->get_record('elluminatelive',array('id'=>$elluminatelive));
        }
        $instanceid = $elluminatelive->id;

        $status = true;

        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));

        //Print elluminatelive data
        fwrite ($bf,full_tag("ID",4,false,$elluminatelive->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"elluminatelive"));
        fwrite ($bf,full_tag("CREATOR",4,false,$elluminatelive->creator));
        fwrite ($bf,full_tag("NAME",4,false,$elluminatelive->name));
        fwrite ($bf,full_tag("SESSIONNAME",4,false,$elluminatelive->sessionname));
        fwrite ($bf,full_tag("DESCRIPTION",4,false,$elluminatelive->description));
        fwrite ($bf,full_tag("CUSTOMNAME",4,false,$elluminatelive->customname));
        fwrite ($bf,full_tag("CUSTOMDESCRIPTION",4,false,$elluminatelive->customdescription));
        fwrite ($bf,full_tag("TIMESTART",4,false,$elluminatelive->timestart));
        fwrite ($bf,full_tag("TIMEEND",4,false,$elluminatelive->timeend));
        fwrite ($bf,full_tag("RECORDINGMODE",4,false,$elluminatelive->recordingmode));
        fwrite ($bf,full_tag("BOUNDARYTIME",4,false,$elluminatelive->boundarytime));
        fwrite ($bf,full_tag("BOUNDARYTIMEDISPLAY",4,false,$elluminatelive->boundarytimedisplay));
        fwrite ($bf,full_tag("SEATS",4,false,$elluminatelive->seats));
        fwrite ($bf,full_tag("PRIVATE",4,false,$elluminatelive->private));
        fwrite ($bf,full_tag("GRADE",4,false,$elluminatelive->grade));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$elluminatelive->timemodified));

        // Backup any sessions
        if ($sessions = $DB->get_records('elluminatelive_session', array('elluminatelive'=>$elluminatelive->id))) {
            fwrite ($bf,start_tag('SESSIONS',4,true));
            foreach ($sessions as $session) {
                fwrite ($bf,start_tag('SESSION',5,true));
                fwrite ($bf,full_tag('ID',6,false,$session->id));
                fwrite ($bf,full_tag('ELLUMINATELIVE',6,false,$session->elluminatelive));
                fwrite ($bf,full_tag('GROUPID',6,false,$session->groupid));
                fwrite ($bf,full_tag('MEETINGID',6,false,$session->meetingid));
                fwrite ($bf,full_tag('TIMEMODIFIED',6,false,$session->timemodified));

                // Backup any recordings
                if ($recordings = $DB->get_records('elluminatelive_recordings', array('meetingid'=>$session->meetingid))) {
                    fwrite ($bf,start_tag('RECORDINGS',6,true));
                    foreach ($recordings as $recording) {
                        fwrite ($bf,start_tag('RECORDING',7,true));
                        fwrite ($bf,full_tag('ID',8,false,$recording->id));
                        fwrite ($bf,full_tag('MEETINGID',8,false,$recording->meetingid));
                        fwrite ($bf,full_tag('RECORDINGID',8,false,$recording->recordingid));
                        fwrite ($bf,full_tag('DESCRIPTION',8,false,$recording->description));
                        fwrite ($bf,full_tag('VISIBLE',8,false,$recording->visible));
                        fwrite ($bf,full_tag('GROUPVISIBLE',8,false,$recording->groupvisible));
                        fwrite ($bf,full_tag('CREATED',8,false,$recording->created));
                        fwrite ($bf,full_tag('SIZE',8,false,$recording->size));
                        fwrite ($bf,end_tag('RECORDING',7,true));
                    }
                    fwrite ($bf,end_tag('RECORDINGS',6,true));
                }

                fwrite ($bf,end_tag('SESSION',5,true));
            }
            fwrite ($bf,end_tag('SESSIONS',4,true));
        }

        //if we've selected to backup users info...
        if (backup_userdata_selected($preferences,'elluminatelive',$elluminatelive->id)) {
            $status = backup_elluminatelive_users($bf,$preferences,$elluminatelive);
            if ($status) {
                $status = backup_elluminatelive_attendance($bf,$preferences,$elluminatelive);
            }
        }
        //End mod
        $status =fwrite ($bf,end_tag("MOD",3,true));
        return $status;
    }

    //Backup elluminatelive user records
    function backup_elluminatelive_users($bf, $preferences, $elluminatelive) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/mod/elluminatelive/lib.php');

        $status = true;

        //If there are users
        if ($uids = elluminatelive_user_ids_by_instance($elluminatelive->id)) {
            //Write start tag
            $status =fwrite ($bf,start_tag("USERS",4,true));
            //Iterate over each user
            foreach ($uids as $uid) {
                if (!($euser = $DB->get_record('elluminatelive_users', array('userid'=>$uid)))) {
                    continue;
                }
                //Start answer
                $status =fwrite ($bf,start_tag("USER",5,true));
                //Print answer contents
                fwrite ($bf,full_tag("ID",6,false,$euser->id));
                fwrite ($bf,full_tag("USERID",6,false,$euser->userid));
                fwrite ($bf,full_tag("ELM_ID",6,false,$euser->elm_id));
                fwrite ($bf,full_tag("ELM_USERNAME",6,false,$euser->elm_username));
                fwrite ($bf,full_tag("ELM_PASSWORD",6,false,$euser->elm_password));
                fwrite ($bf,full_tag("TIMECREATED",6,false,$euser->timecreated));
                //End answer
                $status =fwrite ($bf,end_tag("USER",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("USERS",4,true));
        }
        return $status;
    }


    //Backup elluminate attendance records
    function backup_elluminatelive_attendance($bf, $preferences, $elluminatelive) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/mod/elluminatelive/lib.php');

        $status = true;

        $attendances = $DB->get_records('elluminatelive_attendance', array('elluminateliveid'=>$elluminatelive->id));
        //If there are records
        if ($attendances) {
            //Write start tag
            $status =fwrite ($bf, start_tag("ATTENDANCES",4,true));
            //Iterate over each answer
            foreach ($attendances as $attendance) {
                //Start answer
                $status =fwrite ($bf,start_tag("ATTENDANCE",5,true));
                //Print answer contents
                fwrite ($bf,full_tag("ID",6,false,$attendance->id));
                fwrite ($bf,full_tag("USERID",6,false,$attendance->userid));
                fwrite ($bf,full_tag("ELLUMINATELIVEID",6,false,$attendance->elluminateliveid));
                fwrite ($bf,full_tag("GRADE",6,false,$attendance->grade));
                fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$attendance->timemodified));
                //End answer
                $status =fwrite ($bf,end_tag("ATTENDANCE",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("ATTENDANCES",4,true));
        }
        return $status;
    }


    //Returns an array of user ids
    function elluminatelive_user_ids_by_course ($course) {
        global $CFG, $DB;

        $uids = array();

        if ($activities = $DB->get_records('elluminatelive', 'course', $course, '', 'id, creator')) {
            foreach ($activities as $activity) {
                $cm  = get_coursemodule_from_instance('elluminatelive', $activity->id, $activity->course);
                $ctx = get_context_instance(CONTEXT_MODULE, $cm->id);

            /// Get meeting moderators.
                if ($users = get_users_by_capability($ctx, 'mod/elluminatelive:moderatemeeting', 'u.id, u.username',
                                                     'u.lastname, u.firstname', '', '', '', '', false)) {

                    $uids = array_merge($uids, array_diff(array_keys($users), $uids));
                }

            /// Get meeting participants.
                if ($users = get_users_by_capability($ctx, 'mod/elluminatelive:joinmeeting', 'u.id, u.username',
                                                     'u.lastname, u.firstname', '', '', '', '', false)) {

                    $uids = array_merge($uids, array_diff(array_keys($users), $uids));
                }

            /// Make sure we have the meeting creator as well.
                if (!in_array($activity->creator, $uids)) {
                    $uids[] = $activity->creator;
                }
            }
        }

        if (!empty($uids)) {
			$depends_on = array($uids);
			list($usql, $params) = $DB->get_in_or_equal($depends_on);
			$where = ' WHERE userid $usql ';
            $sql = "SELECT userid, elm_id FROM {elluminatelive_users}$where"'
            if ($euids = $DB->get_records_sql($sql,$params)) {
                $uids = array_intersect($uids, array_keys($euids));
            }
        }

        return $uids;
    }

    //Returns an array of user ids
    function elluminatelive_user_ids_by_instance ($instanceid) {
        global $CFG, $DB;

        $activity = $DB->get_record('elluminatelive', array('id'=>$instanceid));
        $cm       = get_coursemodule_from_instance('elluminatelive', $activity->id, $activity->course);

        $uids = array();

        $ctx = get_context_instance(CONTEXT_MODULE, $cm->id);

    /// Get meeting moderators.
        if ($users = get_users_by_capability($ctx, 'mod/elluminatelive:moderatemeeting', 'u.id, u.username',
                                             'u.lastname, u.firstname', '', '', '', '', false)) {

            $uids = array_merge($uids, array_diff(array_keys($users), $uids));
        }

    /// Get meeting participants.
        if ($users = get_users_by_capability($ctx, 'mod/elluminatelive:joinmeeting', 'u.id, u.username',
                                             'u.lastname, u.firstname', '', '', '', '', false)) {

            $uids = array_merge($uids, array_diff(array_keys($users), $uids));
        }

    /// Make sure we have the meeting creator as well.
        if (!in_array($activity->creator, $uids)) {
            $uids[] = $activity->creator;
        }


        if (!empty($uids)) {

			$depends_on = array($uids);
			list($usql, $params) = $DB->get_in_or_equal($depends_on);
			$where = ' WHERE userid $usql ';
            $sql = "SELECT userid, elm_id FROM {elluminatelive_users}$where"'
            if ($euids = $DB->get_records_sql($sql,$params)) {
                $uids = array_intersect($uids, array_keys($euids));
            }
        }

        return $uids;
    }

?>
