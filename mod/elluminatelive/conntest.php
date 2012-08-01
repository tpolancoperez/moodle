<?php // $Id: conntest.php,v 1.1.2.3 2009/10/22 14:28:24 jfilip Exp $

/**
 * A simple Web Services connection test script for the configured Elluminate Live! server.
 *
 * @version $Id: conntest.php,v 1.1.2.3 2009/10/22 14:28:24 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */

    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

    require_login(SITEID, false);

//    if (!isadmin()) {
//        redirect($CFG->wwwroot);
//    }

    if (!$site = get_site()) {
        redirect($CFG->wwwroot);
    }

    $PAGE->set_url('/mod/elluminatelive/conntest.php');

    $serverurl = required_param('serverURL', PARAM_NOTAGS);
    $adapter   = required_param('adapter', PARAM_ALPHA);
    $username  = required_param('authUsername', PARAM_NOTAGS);
    $password  = required_param('authPassword', PARAM_NOTAGS);

    $strtitle = get_string('elluminateliveconnectiontest', 'elluminatelive');

/// Print header.
    print_header_simple(format_string($strtitle));
    //print_simple_box_start('center', '100%');
    echo $OUTPUT->box_start('generalbox', 'notice');

    if (!elluminatelive_test_connection($serverurl, $adapter, $username, $password)) {
        notify(get_string('connectiontestfailure', 'elluminatelive'));
    } else {
        notify(get_string('connectiontestsuccessful', 'elluminatelive'), 'notifysuccess');
    }

    echo '<center><input type="button" onclick="self.close();" value="' . get_string('closewindow') . '" /></center>';

    //print_simple_box_end();
    //print_footer('none');
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

?>
