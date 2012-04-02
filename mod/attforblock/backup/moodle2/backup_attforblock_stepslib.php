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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards YOUR_NAME_GOES_HERE {@link YOUR_URL_GOES_HERE}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 // This activity has not particular settings but the inherited from the generic
 // backup_activity_task so here there isn't any class definition, like the ones
 // existing in /backup/moodle2/backup_settingslib.php (activities section)


/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */


/**
 * Define the complete choice structure for backup, with file and id annotations
 */
class backup_attforblock_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $attendance = new backup_nested_element('attendance'
                , array('id')
                , array('course', 'name', 'grade')
                );

        $statuses = new backup_nested_element('statuses');
        $status = new backup_nested_element('status'
            , array('id')
            , array('acronym', 'description', 'grade', 'visible', 'deleted')
            );
        
        $sessions = new backup_nested_element('sessions');    
        $session = new backup_nested_element('session'
            , array('id')
            , array('sessdate', 'duration', 'lasttaken', 'lasttakenby', 'timemodified', 'description')
            );

        $logs = new backup_nested_element('logs');
        $log = new backup_nested_element('log'
                , array('id')
                , array('sessionid', 'studentid', 'statusid', 'statusset', 'timetaken', 'takenby', 'remarks')
                );
        
        
        // Build the tree
        $attendance->add_child($statuses);
        $statuses->add_child($status);
        $attendance->add_child($sessions);
        $sessions->add_child($session);
        $session->add_child($logs);
        $logs->add_child($log);
        
        
        // Define sources
        $attendance->set_source_table('attforblock', array('id' => backup::VAR_ACTIVITYID));
        $status->set_source_table('attendance_statuses', array('courseid' => backup::VAR_COURSEID));
        $session->set_source_table('attendance_sessions', array('courseid' => backup::VAR_COURSEID));
        
        if($userinfo){
            $log->set_source_table('attendance_log', array('sessionid' => '../../id'));            
        }

        // Define id annotations
        $session->annotate_ids('user', 'lasttakenby');
        $log->annotate_ids('user', 'studentid');
        $log->annotate_ids('user', 'takenby');
        
        // Define file annotations

        // Return the root element (choice), wrapped into standard activity structure
        return $this->prepare_activity_structure($attendance);
    }
}
