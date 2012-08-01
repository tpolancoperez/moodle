<?php


function xmldb_block_course_overview_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012040202) {

        // Define table elis_mymoodle to be created
        $table = new xmldb_table('elis_mymoodle');

        // Adding fields to table elis_mymoodle
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('options', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('userchanged', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('messages', XMLDB_TYPE_CHAR, '255', null, null, null, null);

        // Adding keys to table elis_mymoodle
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for elis_mymoodle
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        
        // Define table elis_messages to be created
        $table = new xmldb_table('elis_messages');

        // Adding fields to table elis_messages
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, 'big', null, null, null, null);
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('endtime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('users', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, null);

        // Adding keys to table elis_messages
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for elis_messages
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        

        /// community savepoint reached
        upgrade_block_savepoint(true, 2012040202, 'course_overview');
    }


    return true;
}