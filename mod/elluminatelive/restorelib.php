<?php //$Id: restorelib.php,v 1.1.2.3 2009/10/22 14:28:23 jfilip Exp $

/**
 * This php script contains all the stuff to backup/restore
 * elluminatelive mods
 *
 * @version $Id: restorelib.php,v 1.1.2.3 2009/10/22 14:28:23 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    //This is the "graphical" structure of the elluminatelive mod:
    //
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

    function elluminatelive_restore_mods($mod,$restore) {

        global $CFG,$DB;
        require_once($CFG->dirroot.'/mod/elluminatelive/lib.php');

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;

            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the elluminatelive record structure
            $elluminatelive = new stdClass;
            $elluminatelive->course            = $restore->course_id;
            $elluminatelive->creator           = backup_todb($info['MOD']['#']['CREATOR']['0']['#']);
            $elluminatelive->name              = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $elluminatelive->sessionname       = backup_todb($info['MOD']['#']['SESSIONNAME']['0']['#']);
            $elluminatelive->description       = backup_todb($info['MOD']['#']['DESCRIPTION']['0']['#']);
            $elluminatelive->customname        = backup_todb($info['MOD']['#']['CUSTOMNAME']['0']['#']);
            $elluminatelive->customdescription = backup_todb($info['MOD']['#']['CUSTOMDESCRIPTION']['0']['#']);
            $elluminatelive->timestart         = backup_todb($info['MOD']['#']['TIMESTART']['0']['#']);
            $elluminatelive->timeend           = backup_todb($info['MOD']['#']['TIMEEND']['0']['#']);
            $elluminatelive->recordingmode     = backup_todb($info['MOD']['#']['RECORDINGMODE']['0']['#']);
            $elluminatelive->boundarytime      = backup_todb($info['MOD']['#']['BOUNDARYTIME']['0']['#']);
            $elluminatelive->boundarytimedisplay = backup_todb($info['MOD']['#']['BOUNDARYTIMEDISPLAY']['0']['#']);
            $elluminatelive->seats             = backup_todb($info['MOD']['#']['SEATS']['0']['#']);
            $elluminatelive->private           = backup_todb($info['MOD']['#']['PRIVATE']['0']['#']);
            $elluminatelive->grade             = backup_todb($info['MOD']['#']['GRADE']['0']['#']);
            $elluminatelive->timemodified      = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);

        /// Look to see if the creator user ID needs to be re-mapped.
            if ($userid = backup_getid($restore->backup_unique_code, 'user', $elluminatelive->creator)) {
                $elluminatelive->creator = $userid->new_id;
            }

            //We have to recode the scale field if it's <0 (positive is a grade, not a scale)
            if ($elluminatelive->grade < 0) {
                $scale = backup_getid($restore->backup_unique_code, 'scale', abs($elluminatelive->grade));
                if ($scale) {
                    $elluminatelive->grade = -($scale->new_id);
                }
            }

            //The structure is equal to the db, so insert the assignment
            $newid = $DB->insert_record('elluminatelive',$elluminatelive);

            //Do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo '<li>' . get_string('modulename', 'elluminatelive') . ' "' .
                     format_string(stripslashes($elluminatelive->name), true) . '"</li>';
            }
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, $mod->modtype, $mod->id, $newid);


                if (isset($info['MOD']['#']['SESSIONS'])) {
                    $sessions = $info['MOD']['#']['SESSIONS']['0']['#']['SESSION'];

                    foreach ($sessions as $session) {
                        $nsess = new stdClass;
                        $nsess->elluminatelive = $newid;
                        $nsess->groupid        = backup_todb($session['#']['GROUPID']['0']['#']);
                        $nsess->meetingid      = backup_todb($session['#']['MEETINGID']['0']['#']);
                        $nsess->timemodified   = backup_todb($session['#']['TIMEMODIFIED']['0']['#']);

                        if ($groupid = backup_getid($restore->backup_unique_code, 'group', $nsess->groupid)) {
                            $nsess->groupid = $groupid->new_id;
                        }

                    /// If the meeting already exists on the elluminate server, but not on the Moodle server, connect to it.
                        if (!record_exists('elluminatelive_session', 'meetingid', $nsess->meetingid) &&
                            (elluminatelive_get_meeting($nsess->meetingid) !== false)) {

                            $newsessid = $DB->insert_record('elluminatelive_session',$nsess);
                            backup_putid($restore->backup_unique_code, 'elluminatelive_session', $session['#']['ID']['0']['#'], $newsessid);
                        }

                // Restore any recordings if restoring an old meeting.
                        if (isset($session['#']['RECORDINGS'])) {
                            $recordings = $session['#']['RECORDINGS']['0']['#']['RECORDING'];

                    foreach ($recordings as $recording) {
                        $recrecord = new stdClass();
                                $recrecord->meetingid    = backup_todb($recording['#']['MEETINGID']['0']['#']);
                                $recrecord->recordingid  = backup_todb($recording['#']['RECORDINGID']['0']['#']);
                                $recrecord->description  = backup_todb($recording['#']['DESCRIPTION']['0']['#']);
                                $recrecord->visible      = backup_todb($recording['#']['VISIBLE']['0']['#']);
                                $recrecord->groupvisible = backup_todb($recording['#']['GROUPVISIBLE']['0']['#']);
                                $recrecord->created      = backup_todb($recording['#']['CREATED']['0']['#']);
                                if (isset($recording['#']['SIZE']['0']['#'])) {
                                    $recrecord->size     = backup_todb($recording['#']['SIZE']['0']['#']);
                                }

                            /// See if this recording ID actually exists on the ELM server.
                                $recordingfound = false;
                                $filter         = 'meetingId = ' . $recrecord->meetingid;

                                if ($recordingslist = elluminatelive_list_recordings($filter)) {
                                    foreach ($recordingslist as $recordingitem) {
                                        if ($recordingfound) {
                                            continue;
                                        }

                                        if ($recordingitem->recordingid == $recrecord->recordingid) {
                                            $recordingfound = true;
                                        }
                                    }
                                }

                            /// If this recording ID doesn't exist in Moodle but is on the ELM server,
                            /// create the new record now.
                                if (!record_exists('elluminatelive_recordings', 'recordingid', $recrecord->recordingid) &&
                                    $recordingfound) {

                                    $newrecid = $DB->insert_record('elluminatelive_recordings',$elluminatelive);
                                    backup_putid($restore->backup_unique_code, 'elluminatelive_recordings', $recording['#']['ID']['0']['#'], $newrecid);
                                }
                            }
                        }
                    }
                }
/*

                //Now check if want to restore user data and do it.
                if (restore_userdata_selected($restore,'assignment',$mod->id)) {
                    //Restore assignmet_submissions
                    $status = assignment_submissions_restore_mods($mod->id, $newid,$info,$restore) && $status;
                }
*/
                if (restore_userdata_selected($restore,'elluminatelive',$mod->id)) {
                    //Restore attendance

                    /// Only restore attendance if restoring an existing meeting...
                    $status &= elluminatelive_attendance_restore_mods($newid, $info, $restore);

                    /// Always restore the user account connections.
                    $status &= elluminatelive_users_restore_mods ($newid,$info,$restore);
                }
            } else {
                $status = false;
            }

/*
            /// If the 'meetingid' already exists, then we can't reuse it - create a new one for this activity.
            if (!record_exists('elluminatelive', 'meetingid', $elluminatelive->meetingid) &&
                (elluminatelive_get_meeting($elluminatelive->meetingid) !== false)) {
                /// If the meeting already exists on the elluminate server, but not on the Moodle server, connect to it.
                $sessionexists = false;
                $newid = $DB->insert_record("elluminatelive",$elluminatelive);
            } else {
                /// If an activity with this meeting already exists, or this meeting does not exist on the Elluminate
                /// sever, create a new activity using the library function.
                /// This will also create a meeting on the Elluminate server.
                /// Use the administrator as the creator of the meeting.
                $sessionexists = true;
                $admin = get_admin();
                /// Need to set some default start and end times... Say, a week from now.
                $elluminatelive->meetingtimebegin = time() + (7*24*60*60);
                $elluminatelive->meetingtimeend = $elluminatelive->meetingtimebegin + (60*60);
                $elluminatelive->recorded = ELLUMINATELIVE_RECORDING_MANUAL;
                $elluminatelive->cmidnumber = $restore->mods['elluminatelive']->instances[$mod->id]->restored_as_course_module;

                if ($newid = elluminatelive_add_instance($elluminatelive, $admin->id)) {
                    /// Get all of the newly created data.
                    $elluminatelive = $DB->get_record('elluminatelive', array('id'=>$newid));
                }
            }
*/
/*
            //The structure is equal to the db, so insert the assignment
            $newid = $DB->insert_record('elluminatelive', $elluminatelive);

            //Do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo '<li>' . get_string('modulename', 'elluminatelive') . ' "' .
                     format_string(stripslashes($elluminatelive->name), true) . '"</li>';
            }
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newid);

                $elluminatelive->id = $newid;

                //Now check if want to restore user data and do it.
                if (restore_userdata_selected($restore,'elluminatelive',$mod->id)) {
                    //Restore attendance

                    /// Only restore attendance if restoring an existing meeting...
                    if (!$sessionexists) {
                        $status  = elluminatelive_attendance_restore_mods ($newid,$info,$restore);
                    }

                    /// Always restore the user account connections.
                    $status &= elluminatelive_users_restore_mods ($newid,$info,$restore);
                }

            } else {
                $status = false;
            }
*/
        } else {
            $status = false;
        }

        return $status;
    }

    //This function restores the elluminatelive_attendance
    function elluminatelive_attendance_restore_mods($elluminatelive_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the attendance array
        $attendances = array();
        if (isset($info['MOD']['#']['ATTENDANCES']['0']['#']['ATTENDANCE'])) {
            $attendances = $info['MOD']['#']['ATTENDANCES']['0']['#']['ATTENDANCE'];
        }

        //Iterate over attendances
        for($i = 0; $i < sizeof($attendances); $i++) {
            $sus_info = $attendances[$i];
            //traverse_xmlize($sus_info);                                                                 //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($sus_info['#']['ID']['0']['#']);
            $olduserid = backup_todb($sus_info['#']['USERID']['0']['#']);

            //Now, build the elluminatelive_attendance record structure
            $attendance = new Object();
            $attendance->elluminateliveid = $elluminatelive_id;
            $attendance->userid = backup_todb($sus_info['#']['USERID']['0']['#']);
            $attendance->grade = $sus_info['#']['GRADE']['0']['#'];
            $attendance->timemodified = $sus_info['#']['TIMEMODIFIED']['0']['#'];

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$attendance->userid);
            if ($user) {
                $attendance->userid = $user->new_id;
            }

            //The structure is equal to the db, so insert the elluminatelive_subscription
            $newid = $DB->insert_record ("elluminatelive_attendance", $attendance);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,"elluminatelive_attendance",$oldid,
                             $newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

    //This function restores the elluminatelive_attendance
    function elluminatelive_users_restore_mods($elluminatelive_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the user array
        $users = array();
        if (isset($info['MOD']['#']['USERS']['0']['#']['USER'])) {
            $users = $info['MOD']['#']['USERS']['0']['#']['USER'];
        }

        //Iterate over users
        for($i = 0; $i < sizeof($users); $i++) {
            $sus_info = $users[$i];
            //traverse_xmlize($sus_info);                                                                 //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($sus_info['#']['ID']['0']['#']);
            $olduserid = backup_todb($sus_info['#']['USERID']['0']['#']);

            //Now, build the elluminatelive_user record structure
            $user = new Object();
            $user->userid = backup_todb($sus_info['#']['USERID']['0']['#']);
            $user->elm_id       = backup_todb($sus_info['#']['ELM_ID']['0']['#']);
            $user->elm_username = backup_todb($sus_info['#']['ELM_USERNAME']['0']['#']);
            $user->elm_password = backup_todb($sus_info['#']['ELM_PASSWORD']['0']['#']);
            $user->timecreated  = backup_todb($sus_info['#']['TIMECREATED']['0']['#']);

            //We have to recode the userid field
            $moodle_user = backup_getid($restore->backup_unique_code,"user",$user->userid);
            if ($moodle_user) {
                $user->userid = $moodle_user->new_id;
            }

            //The structure is equal to the db, so insert the elluminatelive_subscription
            // (only need to do this if the user doesn't currently exist.)
            if (record_exists("elluminatelive_users", 'userid', $user->userid)) {
                return true;
            }
            $newid = $DB->insert_record("elluminatelive_users", $user);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,"elluminatelive_users",$oldid,
                             $newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

    //This function returns a log record with all the necessay transformations
    //done. It's used by restore_log_module() to restore modules log.
    function elluminatelive_restore_logs($restore,$log) {

        $status = false;

        //Depending of the action, we recode different things
        switch ($log->action) {
        case "add":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "update":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view all":
            $log->url = "index.php?id=".$log->course;
            $status = true;
            break;
        case "view meeting":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "loadmeeting.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view recording":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "loadrecording.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        default:
            if (!defined('RESTORE_SILENTLY')) {
                echo "action (".$log->module."-".$log->action.") unknown. Not restored<br />";                 //Debug
            }
            break;
        }

        if ($status) {
            $status = $log;
        }
        return $status;
    }

    //Return a content decoded to support interactivities linking. Every module
    //should have its own. They are called automatically from
    //assignment_decode_content_links_caller() function in each module
    //in the restore process
    function elluminatelive_decode_content_links ($content,$restore) {

        global $CFG;

        $result = $content;

        //Link to the list of assignments

        $searchstring='/\$@(ELLUMINATELIVEINDEX)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$content,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course id)
                $rec = backup_getid($restore->backup_unique_code,"course",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(ELLUMINATELIVEINDEX)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/elluminatelive/index.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/elluminatelive/index.php?id='.$old_id,$result);
                }
            }
        }

        //Link to assignment view by moduleid

        $searchstring='/\$@(ELLUMINATELIVEVIEWBYID)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$result,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course_modules id)
                $rec = backup_getid($restore->backup_unique_code,"course_modules",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(ELLUMINATELIVEVIEWBYID)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/elluminatelive/view.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/elluminatelive/view.php?id='.$old_id,$result);
                }
            }
        }

        return $result;
    }

    //This function makes all the necessary calls to xxxx_decode_content_links()
    //function in each module, passing them the desired contents to be decoded
    //from backup format to destination site/course in order to mantain inter-activities
    //working in the backup/restore process. It's called from restore_decode_content_links()
    //function in restore process
    function elluminatelive_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;

        if ($elluminatelives = $DB->get_records_sql("SELECT e.id, e.description
                                   FROM {elluminatelive} e
                                   WHERE e.course = $restore->course_id")) {
            //Iterate over each elluminatelive->description
            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($elluminatelives as $elluminatelive) {
                //Increment counter
                $i++;
                $content = $elluminatelive->description;
                $result = restore_decode_content_links_worker($content,$restore);
                if ($result != $content) {
                    //Update record
                    $elluminatelive->description = $result;
                    $status = $DB->update_record("elluminatelive",$elluminatelive);
                    if (debugging()) {
                        if (!defined('RESTORE_SILENTLY')) {
                            echo '<br /><hr />'.s($content).'<br />changed to<br />'.s($result).'<hr /><br />';
                        }
                    }
                }
                //Do some output
                if (($i+1) % 5 == 0) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo ".";
                        if (($i+1) % 100 == 0) {
                            echo "<br />";
                        }
                    }
                    backup_flush(300);
                }
            }
        }
        return $status;
    }

?>
