<?php
/**
 * Structure step to restore one attforblock activity
 */
class restore_attforblock_activity_structure_step extends restore_activity_structure_step {
 
    protected function define_structure() {
 
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
 
        $paths[] = new restore_path_element('attendance', '/activity/attendance');
        $paths[] = new restore_path_element('statuses', '/activity/attendance/statuses/status');
        $paths[] = new restore_path_element('sessions', '/activity/attendance/sessions/session');
        
        if ($userinfo) {
            $paths[] = new restore_path_element('logs', '/activity/attendance/sessions/session/logs/log');
        }
 
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }
 
    protected function process_attendance($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
 
        // insert the attforblock record
        $newitemid = $DB->insert_record('attforblock', $data);
        
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }
 
    protected function process_statuses($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;
 
        $data->courseid = $this->get_courseid();
        
        $newitemid = $DB->insert_record('attendance_statuses', $data);
        $this->set_mapping('attendance_statuses', $oldid, $newitemid);
    }
 
    protected function process_sessions($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
 
        $data->courseid = $this->get_courseid();
        $data->sessdate = $this->apply_date_offset($data->sessdate);
        $data->lasttaken = $this->apply_date_offset($data->lasttaken);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('attendance_sessions', $data);
        $this->set_mapping('attendance_sessions', $oldid, $newitemid);
    }
    
    protected function process_logs($data){
        global $DB;
        $data = (object)$data;
        
        $data->sessionid = $this->get_mappingid('attendance_sessions', $data->sessionid);
        $data->studentid = $this->get_mappingid('user', $data->studentid);
        $data->statusid = $this->get_mappingid('attendance_statuses', $data->statusid);
        $arr_statusset = explode(",",$data->statusset);
        $str_newset = "";
        foreach($arr_statusset as $s){
            $str_newset .= ",".$this->get_mappingid('attendance_statuses', $s);
        }
        if(strlen($str_newset)>0){
            $str_newset = substr($str_newset,1);
        }
        $data->statusset = $str_newset;
        
        $data->timetaken = $this->apply_date_offset($data->timetaken);
        
        $data->takenby = $this->get_mappingid('user', $data->takenby);
        
        $newitemid = $DB->insert_record('attendance_log', $data);
    }

    protected function after_execute() {
        // Add attforblock related files, no need to match by itemname (just internally handled context)
        //$this->add_related_files('mod_attforblock', 'intro', null);
    }
}
?>
