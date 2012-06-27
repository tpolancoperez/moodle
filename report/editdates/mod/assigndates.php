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

require_once($CFG->dirroot.'/mod/assign/locallib.php');

class report_editdates_mod_assign_date_extractor
extends report_editdates_mod_date_extractor {

    //constructor
    public function __construct($course) {
        parent::__construct($course, 'assign');
        parent::load_data();
    }

    //overriden abstract method
    public function get_settings(cm_info $cm) {
        $ass = $this->mods[$cm->instance];
        //availability and due date settings for an assignment
        return array('allowsubmissionsfromdate' => new report_editdates_date_setting(
        get_string('availabledate', 'assignment'),
        $ass->allowsubmissionsfromdate,
        self::DATETIME, true, 5),

                      'duedate' => new report_editdates_date_setting(
        get_string('duedate', 'assignment'),
        $ass->duedate,
        self::DATETIME, true, 5)
        );
    }

    //overriden abstract method
    public function validate_dates(cm_info $cm, array $dates) {
        $errors = array();
        if ($dates['allowsubmissionsfromdate'] != 0 && $dates['duedate'] != 0
        && $dates['duedate'] < $dates['allowsubmissionsfromdate']) {
            $errors['duedate'] = get_string('timedue', 'report_editdates');
        }
        return $errors;
    }

    //overriden abstract method
    public function save_dates(cm_info $cm, array $dates) {
        global $DB, $COURSE;

        //fetch module instance from $mods array
        /*$assign = $this->mods[$cm->instance];

        $assign->instance = $cm->instance;
        $assign->coursemodule = $cm->id;
        $assign->cmidnumber = $cm->id;
	
        //updating date values
        foreach ($dates as $datetype => $datevalue) {
            $assign->$datetype = $datevalue;
        }*/

//print "<pre>";print_r($assign);print "</pre>";

        // Instantiate new instance and save changes
        //$module = new assign(context_module::instance($cm->id), null, null);

//$d = new stdClass();
//$module->plugin_data_preprocessing($d);
//print "<pre>";print_r($d);print "</pre>";

        $update = new stdClass();
        $update->id = $cm->instance;
        $update->duedate = $dates['duedate'];
        $update->allowsubmissionsfromdate = $dates['allowsubmissionsfromdate'];

        $result = $DB->update_record('assign', $update);

        $module = new assign(context_module::instance($cm->id), null, null);

        // update all the calendar events
        $module->update_calendar($cm->id);

        $module->update_gradebook(false, $cm->id);


        //$module->update_instance($assign);
    }
}
