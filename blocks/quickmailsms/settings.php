<?php

// Written at Louisiana State University

defined('MOODLE_INTERNAL') || die;

if($ADMIN->fulltree) {
    require_once $CFG->dirroot . '/blocks/quickmailsms/lib.php';

    $select = array(0 => get_string('no'), 1 => get_string('yes'));

    $allow = quickmailsms::_s('allowstudents');
    $settings->add(
        new admin_setting_configselect('block_quickmailsms_allowstudents',
            $allow, $allow, 0, $select
        )
    );

    $roles = $DB->get_records('role', null, 'sortorder ASC');

    $default_sns = array('editingteacher', 'teacher', 'student');
    $defaults = array_filter($roles, function ($role) use ($default_sns) {
        return in_array($role->shortname, $default_sns);
    });

    $only_names = function ($role) { return $role->shortname; };

    $select_roles = quickmailsms::_s('select_roles');
    $settings->add(
        new admin_setting_configmultiselect('block_quickmailsms_roleselection',
            $select_roles, $select_roles,
            array_keys($defaults),
            array_map($only_names, $roles)
        )
    );

    $settings->add(
        new admin_setting_configselect('block_quickmailsms_receipt',
        quickmailsms::_s('receipt'), quickmailsms::_s('receipt_help'),
        0, $select
        )
    );

    $options = array(
        0 => get_string('none'),
        'idnumber' => get_string('idnumber'),
        'shortname' => get_string('shortname')
    );

    $settings->add(
        new admin_setting_configselect('block_quickmailsms_prepend_class',
            quickmailsms::_s('prepend_class'), quickmailsms::_s('prepend_class_desc'),
            0, $options
        )
    );
}
