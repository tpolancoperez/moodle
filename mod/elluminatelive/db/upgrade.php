<?php // $Id: upgrade.php,v 1.1.2.4 2010/01/27 20:48:26 jfilip Exp $

/**
 * Database upgrade code.
 *
 * @version $Id: upgrade.php,v 1.1.2.4 2010/01/27 20:48:26 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.ca>
 * @author Remote Learner - http://www.remote-learner.net/
 */

    function xmldb_elluminatelive_upgrade($oldversion = 0) {
        global $CFG, $THEME, $DB;
        $dbman = $DB->get_manager(); /// loads ddl manager and xmldb classes

        if ($oldversion < 2006062102) {
        /// This should not be necessary but it's included just in case.
            install_from_xmldb_file($CFG->dirroot . '/mod/elluminatelive/db/install.xml');
            upgrade_mod_savepoint(true, 2006062102, 'elluminate_installxml');
        }

        if ($oldversion < 2008070104) {
        /// Get any existing activity records.
            $meetings = $DB->get_records('elluminatelive');

        /// Modify the 'elluminatelive' table.
            $table = new xmldb_table('elluminatelive');
            $dbman->drop_field($table, new  xmldb_field('meetingid'));
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_meetingid');

            $field = new xmldb_field('sessionname');
            $field->set_attributes(XMLDB_TYPE_CHAR, '64', false, false, false, false, 'name');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_sessionname');

            $field = new xmldb_field('customname');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'description');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_customname');

            $field = new xmldb_field('customdescription');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'customname');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_customdescription');

            $field = new xmldb_field('timestart');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'customdescription');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_timestart');

            $field = new xmldb_field('timeend');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'timestart');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_timeend');

            $field = new xmldb_field('recordingmode');
            $field->set_attributes(XMLDB_TYPE_CHAR, '10', false, false, false, false, 'timeend');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_recordingmode');

            $field = new xmldb_field('boundarytime');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'recordingmode');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_boundarytime');

            $index = new xmldb_index('course');
            $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('course'));
            $dbman->add_index($table, $index);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_course');

            $index = new xmldb_index('creator');
            $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('creator'));
            $dbman->add_index($table, $index);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_creator');


        /// Create the 'elluminatelive_session' table.
            $table = new xmldb_table('elluminatelive_session');

            $field = new xmldb_field('id');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->addField($field);

            $field = new xmldb_field('elluminatelive');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'id');
            $table->addField($field);

            $field = new xmldb_field('groupid');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'elluminatelive');
            $table->addField($field);

            $field = new xmldb_field('meetingid');
            $field->set_attributes(XMLDB_TYPE_CHAR, '20', false, false, false, false, 'groupid');
            $table->addField($field);

            $field = new xmldb_field('timemodified');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'meetingid');
            $table->addField($field);

            $key = new xmldb_key('primary');
            $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $index = new xmldb_index('elluminatelive_groupid');
            $index->set_attributes(XMLDB_INDEX_UNIQUE, array('elluminatelive', 'groupid'));
            $table->addIndex($index);

            $index = new xmldb_index('meetingid');
            $index->set_attributes(XMLDB_INDEX_UNIQUE, array('meetingid'));
            $table->addIndex($index);

            $dbman->create_table($table);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_elluminatetable');

        /// Modify all of the existing meetings, if any.
            if (!empty($meetings)) {
                $timenow = time();

                foreach ($meetings as $meeting) {
                /// Update the meeting by storing values from the ELM server in the local DB.
                    if (!$elmmeeting = elluminatelive_get_meeting($meeting->meetingid)) {
                        continue;
                    }

                    $mparams = elluminatelive_get_meeting_parameters($meeting->meetingid);
                    $sparams = elluminatelive_get_server_parameters($meeting->meetingid);

                    $umeeting = new stdClass;
                    $umeeting->id          = $meeting->id;
                    $umeeting->sessionname = addslashes($meeting->name);
                    $umeeting->timestart   = $elmmeeting->start;
                    $umeeting->timeend     = $elmmeeting->end;

                    if (!empty($mparams->recordingstatus) &&
                        ($mparams->recordingstatus == ELLUMINATELIVE_RECORDING_MANUAL_NAME ||
                         $mparams->recordingstatus == ELLUMINATELIVE_RECORDING_AUTOMATIC_NAME ||
                         $mparams->recordingstatus == ELLUMINATELIVE_RECORDING_NONE_NAME)) {

                        $umeeting->recordingmode = $mparams->recordingstatus;
                    }

                    if (!empty($sparams->seats)) {
                        $umeeting->seats = $sparams->seats;
                    }

                    if (!empty($sparams->boundarytime)) {
                        $umeeting->boundarytime = $sparams->boundaryminutes;
                    }

                    if (update_record('elluminatelive', $umeeting)) {
                        $a = new stdClass;
                        $a->meetingid   = $meeting->id;
                        $a->meetingname = $meeting->name;
                        echo '<p>' . get_string('activityupgradedone', 'elluminatelive', $a) . '</p>';
                    }

                    if (record_exists('elluminatelive_session', 'elluminatelive', $meeting->id, 'groupid', 0)) {
                        continue;
                    }

                /// Create the new session record for this meeting instance.
                    $elmsession = new stdClass;
                    $elmsession->elluminatelive = $meeting->id;
                    $elmsession->groupid        = 0;
                    $elmsession->meetingid      = $meeting->meetingid;
                    $elmsession->timemodified   = $timenow;
                    $elmsession->id = $DB->insert_record('elluminatelive_session', $elmsession);
                }
            }
        }

        if ($oldversion < 2008070105) {
            $timenow = time();
            $sysctx  = get_context_instance(CONTEXT_SYSTEM);

            $adminrid          = $DB->get_field('role', 'id', 'shortname', 'admin');
            $coursecreatorrid  = $DB->get_field('role', 'id', 'shortname', 'coursecreator');
            $editingteacherrid = $DB->get_field('role', 'id', 'shortname', 'editingteacher');
            $teacherrid        = $DB->get_field('role', 'id', 'shortname', 'teacher');

        /// Fully setup the Elluminate Moderator role.
            if (!$mrole = $DB->get_record('role', array('shortname'=>'elluminatemoderator'))) {
                if ($rid = create_role(get_string('elluminatemoderator', 'elluminatelive'), 'elluminatemoderator',
                                       get_string('elluminatemoderatordescription', 'elluminatelive'))) {

                    $mrole  = $DB->get_record('role', array('id'=>$rid));
                    assign_capability('mod/elluminatelive:moderatemeeting', CAP_ALLOW, $mrole->id, $sysctx->id);
                } 
            }

            if (!$DB->count_records('role_allow_assign', array('allowassign'=>$mrole->id))) {
                allow_assign($adminrid, $mrole->id);
                allow_assign($coursecreatorrid, $mrole->id);
                allow_assign($editingteacherrid, $mrole->id);
                allow_assign($teacherrid, $mrole->id);
            }


        /// Fully setup the Elluminate Participant role.
            if (!$prole = $DB->get_record('role', array('shortname'=>'elluminateparticipant'))) {
                if ($rid = create_role(get_string('elluminateparticipant', 'elluminatelive'), 'elluminateparticipant',
                                       get_string('elluminateparticipantdescription', 'elluminatelive'))) {

                    $prole  = $DB->get_record('role', array('id'=>$rid));
                    assign_capability('mod/elluminatelive:joinmeeting', CAP_ALLOW, $prole->id, $sysctx->id);
                }
            }

            if (!$DB->count_records('role_allow_assign', array('allowassign'=>$prole->id))) {
                allow_assign($adminrid, $prole->id);
                allow_assign($coursecreatorrid, $prole->id);
                allow_assign($editingteacherrid, $prole->id);
                allow_assign($teacherrid, $prole->id);
            }
        }

        if ($oldversion < 2008070106) {
        /// Modify the 'elluminatelive' table.
            $table = new xmldb_table('elluminatelive');

            $field = new xmldb_field('boundarytimedisplay');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '1', 'boundarytime');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070106, 'elluminate_boundarytimedisplay');
        }

        if ($oldversion < 2008070107) {
        /// Modify the 'elluminatelive_recordings' table.
            $table = new xmldb_table('elluminatelive_recordings');

            $field = new xmldb_field('description');
            $field->set_attributes(XMLDB_TYPE_CHAR, '255', false, false, false, false, 'recordingid');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_recording_description');

            $field = new xmldb_field('visible');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '1', 'description');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_recording_visible');

            $field = new xmldb_field('groupvisible');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, '0', 'visible');
            $dbman->add_field($table, $field);
            upgrade_mod_savepoint(true, 2008070104, 'elluminate_recording_groupvisible');
        }

        if ($oldversion < 2009062200) { 
            $table = new xmldb_table('elluminatelive_recordings');
            $field = new xmldb_field('size');
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'created');

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
                upgrade_mod_savepoint(true, 2009062200, 'elluminatelive_recordings');
            }

            if ($recordings = $DB->get_recordset('elluminatelive_recordings','size','0')) {
                foreach ($recordings as $recording) {
                    $filter = 'recordingId = ' . $recording->recordingid;
                    if ($er = elluminatelive_list_recordings($filter)) {
                        $recording->size = $er[0]->size;
                        $recording = addslashes_object($recording);
                        $DB->update_record('elluminatelive_recordings', $recording);
                    }
                }
                $recordings->close();
            }
        }

    }

?>
