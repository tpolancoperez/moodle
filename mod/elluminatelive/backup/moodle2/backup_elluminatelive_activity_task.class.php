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
 * This is the "graphical" structure of the elluminate mod:
 * elluminatelive.id->elluminatelive_attendance.elluminateliveid
 * course.id->elluminatelive.course
 * user.id->elluminatelive.creator
 * user.id->elluminatelive_attendance.userid
 * elluminatelive_recordings.meetingid->(the id of the meeting on the elluminate server)
 * elluminatelive_recordings.recordingid->(the id of the recording on the elluminate server)
 * elluminatelive_session.elluminateliv->elluminatelive.id
 * elluminatelive_session.meetingid->(the id of the meeting on the elluminate server)
 * elluminatelive_session.groupid->groups.id
 * elluminatelive_users.userid=user.id
 *
 */
require_once($CFG->dirroot . '/mod/elluminatelive/backup/moodle2/backup_elluminatelive_stepslib.php'); // Because it exists (must)

/**
 * elluminatelive backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_elluminatelive_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // elluminatelive only has one structure step
        $this->add_step(new backup_elluminatelive_activity_structure_step('elluminatelive_structure', 'elluminatelive.xml'));
    }

    /**
     * Code the transformations to perform in the elluminatelive activity in
     * order to get transportable (encoded) links
     *
     * @param string $content
     * @return string
     */
    static public function encode_content_links($content) {
	return $content;
       // global $CFG;
       //$base = preg_quote($CFG->wwwroot.'/mod/elluminatelive','#');
       //Link to the list of elluminatelives
       //$pattern = "#(".$base."\/index.php\?id\=)([0-9]+)#";
       //$content = preg_replace($pattern, '$@ELLUMINATELIVEINDEX*$2@$', $content);
       //Link to elluminatelive view by moduleid
       //$pattern = "#(".$base."\/view.php\?id\=)([0-9]+)#";
       //$content = preg_replace($pattern, '$@ELLUMINATELIVEVIEWBYID*$2@$', $content);
       //return $content;
    }
}
