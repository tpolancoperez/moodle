<?php // $Id: lib.php,v 1.2.2.2 2009/10/22 14:28:25 jfilip Exp $

/**
 * Bulk session creation testing code.
 *
 * @version $Id: lib.php,v 1.2.2.2 2009/10/22 14:28:25 jfilip Exp $
 * @author Remote Learner - http://www.remote-learner.net/
 * @author Justin Filip <jfilip@remote-learner.net>
 */


    require_once $CFG->dirroot . '/course/lib.php';
    require_once dirname(__FILE__) . '/lib.php';


/**
 * Configure these values to modify test behaviour.
 */
    define('PHP_CLI',        'php');
    define('CHILD_PROCS',    10);
    define('ACTIVITY_COUNT', 10);
    define('GROUP_COUNT',    2000);

/**
 * Don't change these values.
 */
    define('SHM_BLOCK_SIZE', 500);
    define('USERNAME',      'elmtestuser');
    define('PASSWORD',      'elmtestuser');


/**
 * Code to run a child process.
 *
 * @uses $CFG
 * @param none
 * @return array Array of timing info.
 */
    function proc_child($meeting, $groups, $groupstart, $groupend) {
        global $CFG;

        $cookies      = array();
        $cookiestring = '';

        $cc = curl_init();

    /// First we need to get some session info from the login form.
        curl_setopt_array($cc, array(
            CURLOPT_URL            => $CFG->wwwroot . '/login/index.php',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_RETURNTRANSFER => true
        ));

        if (!$return = curl_exec($cc)) {
            die('Curl connection error : ' . curl_error($cc) . "\n");
        }

    /// Get the 'SESSKEY' value from the HTML form.
        preg_match('/name="sesskey" value="([a-zA-Z0-9]+)"/', $return, $matches);

        if (!empty($matches[1])) {
            $sesskey = $matches[1];
        }

    /// Get the cookies from the HTTP header.
        preg_match_all('/Set-Cookie: ([a-zA-z0-9_]+=[^;]+; )/', $return, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $eqpos = strpos($match, '=');
                $cname = substr($match, 0, $eqpos);

                $cookies[$cname] = $match;
            }
        }

        $cookiestring = implode('', $cookies);


    /// Now we'll actually supply the login info.
        curl_setopt_array($cc, array(
            CURLOPT_URL            => $CFG->wwwroot . '/login/index.php',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIE         => $cookiestring,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => array(
                'username' => USERNAME,
                'password' => PASSWORD,
                'sesskey'  => $sesskey
            )
        ));

        if (!$return = curl_exec($cc)) {
            die('Curl connection error on login: ' . curl_error($cc) . "\n");
        }

    /// Get the cookies from the HTTP header.
        preg_match_all('/Set-Cookie: ([a-zA-z0-9_]+=[^;]+; )/', $return, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $eqpos = strpos($match, '=');
                $cname = substr($match, 0, $eqpos);

                $cookies[$cname] = $match;
            }
        }

        $cookiestring = implode('', $cookies);

    /// We're logged in, let's go ahead and start hitting the meeting pages.
        $timebest  = 0;
        $timeworst = 0;
        $timetotal = 0;

        for ($i = $groupstart; $i<= $groupend; $i++) {
            $group = $groups[$i];

            $timestart = microtime(true);

            curl_setopt_array($cc, array(
                CURLOPT_URL            => $CFG->wwwroot . '/mod/elluminatelive/loadmeeting.php?id=' .
                                          $meeting->instance . '&group=' . $group->id,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FRESH_CONNECT  => true,
                CURLOPT_COOKIE         => $cookiestring,
                CURLOPT_POST           => false,
                CURLOPT_POSTFIELDS     => false,
                CURLOPT_HTTPGET        => true
            ));

            if (!$return = curl_exec($cc)) {
                die('Curl meeting load error: ' . curl_error($cc) . "\n");
            }

            $timeend = microtime(true);
            $time    = $timeend - $timestart;

            $timetotal += $time;

            if ($timebest == 0 && $timeworst == 0) {
                $timebest  = $time;
                $timeworst = $time;
            }

            if ($time < $timebest) {
                $timebest = $time;
            }

            if ($time > $timeworst) {
                $timeworst = $time;
            }
        }

        curl_close($cc);

        $times = array(
            'timetotal'   => $timetotal,
            'timeworst'   => $timeworst,
            'timebest'    => $timebest,
            'timeaverage' => $timetotal / ($groupend - $groupstart + 1)
        );

        return $times;
    }


/**
 * Setup the Elluminate Live! activities.
 *
 * @return array An array of activity objects.
 */
    function setup_testcourse(&$course, &$user, &$groups) {
        global $CFG,$DB;;

        $timenow    = time();
        $activities = array();
        $admin      = get_admin();

    /// Create the course database record.
        $cdata = new stdClass;
        $cdata->category    = $CFG->defaultrequestcategory;
        $cdata->fullname    = 'Elluminate Live! Test Course';
        $cdata->shortname   = 'ELM_TEST';
        $cdata->format      = 'topics';
        $cdata->startdate   = $timenow;
        $cdata->numsections = 1;

        if (!$course = create_course($cdata)) {
            die('Could not create test course.');
        }

    /// Create our test user in the system.
        $user = new stdClass;
        $user->confirmed    = 1;
        $user->mnethostid   = $CFG->mnet_localhost_id;
        $user->username     = USERNAME;
        $user->password     = hash_internal_user_password(PASSWORD);
        $user->firstname    = 'ELM';
        $user->lastname     = 'Testuser';
        $user->email        = 'elmtestuser@localhost.org';
        $user->city         = '*';
        $user->timemodified = $timenow;
        $user->id = $DB->insert_record('user', $user);

        if (empty($user->id)) {
            die('Could not create test user.');
        }

        $user = $DB->get_record('user', array('id'=>$user->id));

        enrol_into_course($course, $user, 'manual');

    /// Create the test groups and add our user to all of them.
        for ($i = 0; $i < GROUP_COUNT; $i++) {
            $group = new stdClass;
            $group->courseid     = $course->id;
            $group->name         = sprintf("Test Group %03d", $i + 1);
            $group->timecreated  = $timenow;
            $group->timemodified = $timenow;
            $group->id = $DB->insert_record('groups', $group);

            if (empty($group->id)) {
                die('Could not create test group ' . $i);
            }

            $groups[$i] = $DB->get_record('groups', array('id'=>$group->id));

            groups_add_member($group->id, $user->id);
        }

        $module = $DB->get_record('modules', array('name'=>'elluminatelive'));

    /// Create the test activities in section 0 of the course.
        for ($i = 0; $i < ACTIVITY_COUNT; $i++) {
            $mod = new stdClass;
            $mod->course      = $course->id;
            $mod->section     = 0;
            $mod->module      = $module->id;
            $mod->modulename  = $module->name;
            $mod->creator     = $admin->id;
            $mod->name        = sprintf("Test Meeting %02d", $i + 1);
            $mod->description = sprintf("Test Meeting %02d", $i + 1);
            $mod->timestart   = $timenow;
            $mod->timeend     = $timenow + 30 * MINSECS;
            $mod->grade       = 100;
            $mod->groupmode   = SEPARATEGROUPS;
            $mod->visible     = 1;
            $mod->instance = elluminatelive_add_instance($mod);

            if (empty($mod->instance) && !is_numeric($mod->instance)) {
                die('Could not create test activity ' . $i);
            }

            $mod->coursemodule = add_course_module($mod);
            $sectionid         = add_mod_to_section($mod);

            if (!set_field('course_modules', 'section', $sectionid, 'id', $mod->coursemodule)) {
                error('Could not update the course module with the correct section');
            }

            set_coursemodule_visible($mod->coursemodule, $mod->visible);

            if (isset($mod->cmidnumber)) {
                set_coursemodule_idnumber($mod->coursemodule, $mod->cmidnumber);
            }

            $activities[$i] = $DB->get_record('course_modules', array('id'=>$mod->coursemodule));
        }

        return $activities;
    }


/**
 * Cleanup any course and user testing data lying around.
 *
 * @param none
 * @return none
 */
    function cleanup() {
        global $CFG, $DB;

        if ($user = $DB->get_record('user', array('username'=>USERNAME,'mnethostid'=>$CFG->mnet_localhost_id))) {
            if ($elmuser = $DB->get_record('elluminatelive_users', array('userid'=>$user->id))) {
                elluminatelive_delete_user($elmuser->elm_id);
            }

            delete_user($user);
        }

        if ($course = $DB->get_record('course', array('fullname'=>'Elluminate Live! Test Course','shortname'=>'ELM_TEST'))) {
            delete_course($course, false);
            fix_course_sortorder();
        }

        $cc = curl_init();

        curl_setopt_array($cc, array(
            CURLOPT_URL            => $CFG->wwwroot . '/login/logout.php',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_RETURNTRANSFER => true
        ));

        if (!$return = curl_exec($cc)) {
            die('Curl connection error on logout: ' . curl_error($cc) . "\n");
        }

        curl_close($cc);
    }

?>
