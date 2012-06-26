<?php  //$Id: settings.php,v 1.1.2.2 2009/10/22 14:28:23 jfilip Exp $

global $PAGE;

require_once $CFG->dirroot . '/mod/elluminatelive/lib.php';
$PAGE->requires->js('/mod/elluminatelive/testconnection.js');


$settings->add(new admin_setting_configtext('elluminatelive_server', get_string('elluminatelive_server', 'elluminatelive'),
                   get_string('configserver', 'elluminatelive'), 'http://localhost:8080', PARAM_URL));

$settings->add(new admin_setting_configtext('elluminatelive_adapter', get_string('elluminatelive_adapter', 'elluminatelive'),
                   get_string('configadapter', 'elluminatelive'), 'moodle', PARAM_ALPHA));

$settings->add(new admin_setting_configtext('elluminatelive_auth_username', get_string('elluminatelive_auth_username', 'elluminatelive'),
                   get_string('configauthusername', 'elluminatelive'), '', PARAM_RAW));

$settings->add(new admin_setting_configpasswordunmask('elluminatelive_auth_password', get_string('elluminatelive_auth_password', 'elluminatelive'),
                   get_string('configauthpassword', 'elluminatelive'), ''));

$duration    = array();
$duration[0] = get_string('disabled', 'elluminatelive');

for ($i = 1; $i <= 365; $i++) {
    $duration[$i] = $i;
}

$settings->add(new admin_setting_configselect('elluminatelive_user_duration', get_string('elluminatelive_user_duration', 'elluminatelive'),
                   get_string('configuserduration', 'elluminatelive'), 0, $duration));

$boundary_times = array(
    0  => get_string('choose'),
    15 => '15',
    30 => '30',
    45 => '45',
    60 => '60'
);

$settings->add(new admin_setting_configselect('elluminatelive_boundary_default', get_string('elluminatelive_boundary_default', 'elluminatelive'),
                   get_string('configboundarydefault', 'elluminatelive'), ELLUMINATELIVE_BOUNDARY_DEFAULT, $boundary_times));

$settings->add(new admin_setting_configselect('elluminatelive_seat_reservation', get_string('elluminatelive_seat_reservation', 'elluminatelive'),
                   get_string('configseatreservation', 'elluminatelive'), 1, array(0 => get_string('no'), 1 => get_string('yes'))));

$settings->add(new admin_setting_configselect('elluminatelive_hide_empty_recordings', get_string('elluminatelive_hide_empty_recordigs', 'elluminatelive'),
                   get_string('confighideemptyrecordings', 'elluminatelive'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));

$settings->add(new admin_setting_configselect('elluminatelive_ws_debug', get_string('elluminatelive_ws_debug', 'elluminatelive'),
                   get_string('configwsdebug', 'elluminatelive'), 0, array(0 => get_string('no'), 1 => get_string('yes'))));

//$str = '<center><input type="button" onclick="return testConnection(document.getElementById(\'adminsettings\'));" value="' . get_string('testconnection', 'elluminatelive') . '" /></center>';

$str = '<center><input type="button" onclick="return testConnection(document.getElementById(\'adminsettings\'), \''. $CFG->wwwroot . '\');" value="' . get_string('testconnection', 'elluminatelive') . '" /></center>';

$settings->add(new admin_setting_heading('elluminatelive_test', '', $str));

?>
