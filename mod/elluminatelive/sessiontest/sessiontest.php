<?php // $Id: sessiontest.php,v 1.2.2.2 2009/10/22 14:28:25 jfilip Exp $

/**
 * Test bulk session creation.
 *
 * NOTE: requires --enable-pcntl and --enable-shmop support in your PHP
 *       binary and can only be run from the commandline.
 *
 * @version $Id: sessiontest.php,v 1.2.2.2 2009/10/22 14:28:25 jfilip Exp $
 * @author Remote Learner - http://www.remote-learner.net/
 * @author Justin Filip <jfilip@remote-learner.net>
 */


    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
    require_once dirname(dirname(__FILE__)) . '/lib.php';
    require_once dirname(__FILE__) . '/lib.php';


    if (isset($_SERVER['REMOTE_ADDR'])) {
        die('no web access');
    }

/// If there is anything lying around from a pervious partial or incomplete test,
/// clean up that data now.
    cleanup();

    $pids = array();

    $shm_key = ftok(__FILE__, 't');
    if (($shm = shmop_open($shm_key, 'c', 0644, SHM_BLOCK_SIZE * CHILD_PROCS)) === false) {
        die('Could not create new shared memory segment');
    }
    shmop_close($shm);

    $course = new stdClass;
    $user   = new stdClass;
    $groups = array();

    $activities = setup_testcourse($course, $user, $groups);

    $groupnum = GROUP_COUNT / CHILD_PROCS;

    for ($i = 0; $i < CHILD_PROCS; $i++) {
        $pids[$i] = pcntl_fork();

        if ($pids[$i] == -1) {
            die('Could not fork child ' . $i + 1 . '!');
        } else if (!$pids[$i]) {
            $groupstart = $i * $groupnum;
            $groupend   = $groupstart + $groupnum - 1;

            if (($shm = shmop_open($shm_key, 'w', 0, 0)) === false) {
                die('Could not open shared memory segment');
            }

            $times = proc_child($activities[$i], $groups, $groupstart, $groupend);
            $times = serialize($times);

            if (shmop_write($shm, $times, SHM_BLOCK_SIZE * $i) === false) {
                die('Error writing to shared memory segment');
            }
            shmop_close($shm);
            exit(0);
        }
    }

    $times = array();

    if (($shm = shmop_open($shm_key, 'w', 0, 0)) === false) {
        die('Could not open shared memory segment');
    }

    for ($i = 0; $i < CHILD_PROCS; $i++) {
        pcntl_waitpid($pids[$i], $status, WUNTRACED);
        $times[$i] = shmop_read($shm, SHM_BLOCK_SIZE * $i, SHM_BLOCK_SIZE);
        $times[$i] = unserialize($times[$i]);
    }

/// Actually process the per-child timing info.
    $timetotal   = 0;
    $timeworst   = 0;
    $timebest    = 0;
    $timeaverage = 0;

    if (!empty($times)) {
        foreach ($times as $time) {
            $timetotal += $time['timetotal'];

            if ($timeworst == 0 || $timeworst > $time['timeworst']) {
                $timeworst = $time['timeworst'];
            }

            if ($timebest < $time['timebest']) {
                $timebest = $time['timebest'];
            }
        }

        $timeaverage = $timetotal / ($groupnum * CHILD_PROCS);

        print_r('Groups:               ' . GROUP_COUNT . "\n");
        print_r('Number of activities: ' . ACTIVITY_COUNT . "\n");
        print_r('Child processes:      ' . CHILD_PROCS . "\n\n");

        print_r("All times are in seconds:\n\n");
        print_r("Total execution time:     $timetotal\n");
        print_r("Worst per-session time:   $timeworst\n");
        print_r("Best per-session time:    $timebest\n");
        print_r("Average per-session time: $timeaverage\n\n");
    }

/// Clean up shared memory.
    shmop_delete($shm);
    shmop_close($shm);

/// Clean up after ourselves.
    exec(PHP_CLI . ' ./cleanup.php');

?>
