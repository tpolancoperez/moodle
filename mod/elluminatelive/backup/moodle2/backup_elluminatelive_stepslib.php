<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Define all the backup steps that will be used by the backup_elluminatelive_activity_task
 */
class backup_elluminatelive_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $elluminatelive = new backup_nested_element('elluminatelive', array('id'), array(
            'course', 'creator', 'name', 'sessionname', 'description', 'customname',
			'customdescription', 'timestart', 'timeend', 'recordingmode', 'boundarytime',
			'boundarytimedisplay', 'seats', 'private', 'grade', 'timemodified'));

        $attendance = new backup_nested_element('attendance');
        $attendee = new backup_nested_element('attendee', array('id'), array(
            'userid', 'elluminateliveid', 'grade', 'timemodified'));

        $sessions = new backup_nested_element('sessions');
        $session = new backup_nested_element('elluminatelive_session', array('id'), array(
            'groupid','elluminatelive', 'meetingid', 'timemodified'));

        $recordings = new backup_nested_element('recordings');
        $recording = new backup_nested_element('elluminatelive_recordings', array('id'), array(
            'meetingid', 'recordingid', 'description', 'visible', 'groupvisible', 'created', 'size'));

        // Build the tree
        $elluminatelive->add_child($attendance);
        $attendance->add_child($attendee);

        $elluminatelive->add_child($sessions);
        $sessions->add_child($session);

        $elluminatelive->add_child($recordings);
        $recordings->add_child($recording);

        // Define sources
        $elluminatelive->set_source_table('elluminatelive', array('id' => backup::VAR_ACTIVITYID));

        // Only happen if we are including user info
        if ($userinfo) {
            $attendance->set_source_table('elluminatelive_attendance', array('elluminateliveid'=>backup::VAR_PARENTID));
            $sessions->set_source_table('elluminatelive_session', array('elluminatelive'=>backup::VAR_PARENTID));
            $recordings->set_source_sql("SELECT er.*
                    FROM {elluminatelive_recordings} er
                    INNER JOIN {elluminatelive_session} es ON es.meetingid = er.meetingid
                    INNER JOIN {elluminatelive} e ON e.id = es.elluminatelive
                    WHERE e.course = ?", array(backup::VAR_COURSEID));
        }

        // Define id annotations
        //$attendee->annotate_ids('user', 'userid');
        //$session->annotate_ids('group', 'groupid');

        // Annotate the file areas in elluminatelive module
        //$elluminatelive->annotate_files('mod_elluminatelive', 'descrption', null); // ellumainte_intro area don't use itemid

        // Return the root element (elluminatelive), wrapped into standard activity structure
        return $this->prepare_activity_structure($elluminatelive);
    }
}
