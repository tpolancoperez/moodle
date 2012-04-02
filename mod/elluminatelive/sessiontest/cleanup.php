<?php // $Id: cleanup.php,v 1.1.2.2 2009/10/22 14:28:24 jfilip Exp $

/**
 * Cleanup any data created from the session creation testing.
 *
 * @version $Id: cleanup.php,v 1.1.2.2 2009/10/22 14:28:24 jfilip Exp $
 * @author Remote Learner - http://www.remote-learner.net/
 * @author Justin Filip <jfilip@remote-learner.net>
 */

    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
    require_once dirname(dirname(__FILE__)) . '/lib.php';
    require_once dirname(__FILE__) . '/lib.php';

    if (isset($_SERVER['REMOTE_ADDR'])) {
        die('no web access');
    }

    cleanup();

?>
