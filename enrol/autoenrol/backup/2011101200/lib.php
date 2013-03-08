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
 * autoenrol enrolment plugin.
 *
 * This plugin automatically enrols a user onto a course the first time they try to access it.
 *
 * @package    enrol
 * @subpackage autoenrol
 * @author  2011 Matthew Cannings - based on code by Martin Dougiamas, Petr Skoda, Eugene Venter and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_autoenrol_plugin extends enrol_plugin {

    public function get_info_icons(array $instances) {
        return array(new pix_icon('icon', get_string('pluginname', 'enrol_autoenrol'), 'enrol_autoenrol'));
    }

    public function roles_protected() {
        // users with role assign cap may tweak the roles later
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually - requires enrol/autoenrol:unenrol
        return false;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status - requires enrol/autoenrol:manage
        return false;
    }

    public function show_enrolme_link(stdClass $instance) {
        return false;
		//return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Sets up navigation entries.
     *
     * @param object $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
		if ($instance->enrol !== 'autoenrol') {
             throw new coding_exception('Invalid enrol instance type!');
        }
   		if ($instance->customint1 != 1){
			$this->enrol_on_access($instance);
		}		

        $context = get_context_instance(CONTEXT_COURSE, $instance->courseid);
        if (has_capability('enrol/autoenrol:config', $context)) {
            $managelink = new moodle_url('/enrol/autoenrol/edit.php', array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'autoenrol') {
            throw new coding_exception('invalid enrol instance!');
        }
		$context = get_context_instance(CONTEXT_COURSE, $instance->courseid);
        $icons = array();
		
        if (has_capability('enrol/self:config', $context)) {
            $editlink = new moodle_url("/enrol/autoenrol/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('i/edit', get_string('edit'), 'core', array('class'=>'icon')));
        }

        return $icons;
    }

    /**
     * Returns localised name of enrol instance
     *
     * @param object $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;      
        return get_string('pluginname', 'enrol_autoenrol');
    }    
    
    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
		global $DB;
		
        $context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/autoenrol:config', $context)) {
            return NULL;
        }
		
		//Should return null if the plugin is already installed
		if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'autoenrol'))) {
            return NULL;
        }

        // multiple instances supported - different cost for different roles
        return new moodle_url('/enrol/autoenrol/edit.php', array('courseid'=>$courseid));
    }
	
    /**
     * Intercepts the instance deletion call and gives some
	 * custom instructions before resuming the parent function
     */
   public function delete_instance($instance) {
        global $DB;		
		require_once("../group/lib.php");		
		$group = $DB->get_record('groups', array('name'=>'auto_staff', 'courseid'=>$instance->courseid));
		if(isset($group)){
			groups_delete_group($group);		
		}
		$group = $DB->get_record('groups', array('name'=>'auto_student', 'courseid'=>$instance->courseid));
		if(isset($group)){
			groups_delete_group($group);		
		}		
 		parent::delete_instance($instance);		
    }
		

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $OUTPUT;

        ob_start();
        //$form->display();
        $output = ob_get_clean();
		
		if ($instance->customint1 != 1){
			$this->enrol_on_access($instance);
		}

        return $OUTPUT->box($output);
    }

    /**
     * Custom function, checks to see if user fulfills
     * our requirements before enrolling them.
     */
	public function enrol_on_access(stdClass $instance){
        global $CFG, $OUTPUT, $SESSION, $USER, $DB;		
	    if (isguestuser()) {
            // can not enrol guest!!
            return null;
        }
        if ($DB->record_exists('user_enrolments', array('userid'=>$USER->id, 'enrolid'=>$instance->id))) {
            return null;
        }

        if ($instance->enrolstartdate != 0 and $instance->enrolstartdate > time()) {
             return null;
        }

        if ($instance->enrolenddate != 0 and $instance->enrolenddate < time()) {
            return null;
        }
		
		if (isloggedin()){  
			//debugging("trying to enrol user now");
            $timestart = time();
            $timeend = 0;        
            $this->enrol_user($instance, $USER->id, $this->get_config('defaultrole'), $timestart, $timeend);
			
			$type = $USER->institution;
			require_once("../group/lib.php");	
					
			if($type=='STAFF'){
				$group = $DB->get_record('groups', array('name'=>'auto_staff', 'courseid'=>$instance->courseid));
				if($group == NULL){
					//somebody probably deleted the group
					$newgroupdata -> courseid = $instance->courseid;
					$newgroupdata -> name = 'auto_staff';
					$newgroupdata -> description = "College Staff";		
					groups_create_group($newgroupdata);	
					$group = $DB->get_record('groups', array('name'=>'auto_staff', 'courseid'=>$instance->courseid));	
				}
				groups_add_member($group->id, $USER->id);	
			}
			
			if($type=='STUDENT'){
				$group = $DB->get_record('groups', array('name'=>'auto_student', 'courseid'=>$instance->courseid));
				if($group == NULL){
					//somebody probably deleted the group
					$newgroupdata -> courseid = $instance->courseid;
					$newgroupdata -> name = 'auto_student';
					$newgroupdata -> description = "College Students";		
					groups_create_group($newgroupdata);
					$group = $DB->get_record('groups', array('name'=>'auto_student', 'courseid'=>$instance->courseid));	
				}
				groups_add_member($group->id, $USER->id);	
			}			
			
            add_to_log($instance->courseid, 'course', 'enrol', '../enrol/users.php?id='.$instance->courseid, $instance->courseid);
			
        } else {
			//this shouldnt be possible!
         return null;
        }
	}
}
