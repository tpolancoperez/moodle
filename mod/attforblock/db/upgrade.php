<?php  //$Id: upgrade.php,v 1.1.2.2 2009/02/23 19:22:42 dlnsk Exp $

// This file keeps track of upgrades to 
// the forum module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_attforblock_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager();
    
    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

	if ($result && $oldversion < 2008021904) { //New version in version.php
		global $USER;
		if ($sessions = $DB->get_records('attendance_sessions', array('takenby'=>0))) {
			foreach ($sessions as $sess) {
				if ($DB->count_records('attendance_log', array('attsid'=>$sess->id)) > 0) {
					$sess->takenby = $USER->id;
					$sess->timetaken = $sess->timemodified ? $sess->timemodified : time();
					$result = $DB->update_record('attendance_sessions', $sess) and $result;
				}
			}
		}
	}

    if ($oldversion < 2008102401 and $result) {
    	
        $table = new xmldb_table('attforblock');
        
        $field = new xmldb_field('grade');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100', 'name');
        $result = $result && $dbman->add_field($table, $field);
    	
        
        $table = new xmldb_table('attendance_sessions');
        
        $field = new xmldb_field('courseid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
        $result = $result && $dbman->change_field_unsigned($table, $field);
    	
    	
        $field = new xmldb_field('sessdate');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'creator');
        $result = $result && $dbman->change_field_unsigned($table, $field);
    	
        $field = new xmldb_field('duration');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'sessdate');
        $result = $result && $dbman->add_field($table, $field);
        
        $field = new xmldb_field('timetaken');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'takenby');
        $result = $result && $dbman->change_field_unsigned($table, $field);
    	$result = $result && $dbman->rename_field($table, $field, 'lasttaken');

        $field = new xmldb_field('takenby');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'lasttaken');
        $result = $result && $dbman->change_field_unsigned($table, $field);
        $result = $result && $dbman->rename_field($table, $field, 'lasttakenby');
    	
        $field = new xmldb_field('timemodified');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null,  null, 'lasttaken');
        $result = $result && $dbman->change_field_unsigned($table, $field);
    	
        
    	$table = new xmldb_table('attendance_log');
        
        $field = new xmldb_field('attsid');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
    	$result = $result && $dbman->change_field_unsigned($table, $field);
    	
        $field = new xmldb_field('studentid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'attsid');
    	$result = $result && $dbman->change_field_unsigned($table, $field);
    	
    	$field = new xmldb_field('statusid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'status');
    	$result = $result && $dbman->add_field($table, $field);
    	
        $field = new xmldb_field('statusset');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null, null, null, null, 'statusid');
        $result = $result && $dbman->add_field($table, $field);
    	
        $field = new xmldb_field('timetaken');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'statusid');
    	$result = $result && $dbman->add_field($table, $field);
    	
        $field = new xmldb_field('takenby');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'timetaken');
    	$result = $result && $dbman->add_field($table, $field);
    	
        //Indexes
        $index = new xmldb_index('statusid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('statusid'));
    	$result = $result && $dbman->add_index($table, $index);
    	
        $index = new xmldb_index('attsid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('attsid'));
        $result = $result && $dbman->drop_index($table, $index);
    	
        $field = new xmldb_field('attsid'); //Rename field
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
        $result = $result && $dbman->rename_field($table, $field, 'sessionid');
        
        $index = new xmldb_index('sessionid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('sessionid'));
        $result = $result && $dbman->add_index($table, $index);
        
    	
    	$table = new xmldb_table('attendance_settings');
        
        $field = new xmldb_field('courseid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
        $result = $result && $dbman->change_field_unsigned($table, $field);
    	
        $field = new xmldb_field('visible');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'grade');
        $result = $result && $dbman->add_field($table, $field);
        
        $field = new xmldb_field('deleted');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'visible');
        $result = $result && $dbman->add_field($table, $field);
        
        //Indexes
        $index = new xmldb_index('visible');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('visible'));
        $result = $result && $dbman->add_index($table, $index);
        
        $index = new xmldb_index('deleted');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('deleted'));
        $result = $result && $dbman->add_index($table, $index);
        
    	$result = $result && $dbman->rename_table($table, 'attendance_statuses');
    }
    
    if ($oldversion < 2008102406 and $result) {
    	
        if ($courses = $DB->get_records_sql("SELECT courseid FROM {attendance_sessions} GROUP BY courseid")) {
            foreach ($courses as $c) {
                //Adding own status for course (now it must have own)
                if (!$DB->count_records('attendance_statuses', array('courseid'=>$c->courseid))) {
                    $statuses = $DB->get_records('attendance_statuses', array('courseid'=>0));
                    foreach($statuses as $stat) {
                            $rec = $stat;
                            $rec->courseid = $c->courseid;
                            $DB->insert_record('attendance_statuses', $rec);
                    }
                }
                $statuses = $DB->get_records('attendance_statuses', array('courseid'=>$c->courseid));
                $statlist = implode(',', array_keys($statuses));
                $sess = $DB->get_records_select_menu('attendance_sessions', "courseid = $c->courseid AND lasttakenby > 0");
                $arrlist = array_keys($sess);
                $sesslist = implode(',', $arrlist);
                list($usql, $params) = $DB->get_in_or_equal($arrlist);
                foreach($statuses as $stat) {
                    $sql = "UPDATE {$attendance_log}";
                    $sql.= " SET statusid = {$stat->id}";
                        $sql.= ", statusset = '$statlist'";
                    $sql.= " WHERE sessionid $usql";
                        $sql.= " AND status = '$stat->status'";

                    $DB->execute($sql, $params);
                }
                $sessions = $DB->get_records_list('attendance_sessions', 'id', $arrlist);
                foreach($sessions as $sess) {
                    $sql = "UPDATE {attendance_log}";
                    $sql.= " SET timetaken = {$sess->lasttaken}";
                    $sql.= ",  takenby = {$sess->lasttakenby}";
                    $sql.= " WHERE sessionid = {$sess->id}";
                    $DB->execute($sql);
                }
            }
        }
     }
     
    if ($oldversion < 2008102409 and $result) {
        $table = new xmldb_table('attendance_statuses');
        
        $field = new xmldb_field('status');
        $result = $result && $dbman->drop_field($table, $field);
        
        $index = new xmldb_index('status');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('status'));
        $result = $result && $dbman->drop_index($table, $index);

        
        $table = new xmldb_table('attendance_log');
        
        $field = new xmldb_field('status');
        $result = $result && $dbman->drop_field($table, $field);
        
        $index = new xmldb_index('status');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('status'));
        $result = $result && $dbman->drop_index($table, $index);
        
        $table = new xmldb_table('attendance_sessions');

        $field = new xmldb_field('creator');
        $result = $result && $dbman->drop_field($table, $field);
        
    } 
    return $result;
}

?>
