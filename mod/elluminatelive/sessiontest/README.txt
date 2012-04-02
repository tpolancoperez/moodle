  $Id: README.txt,v 1.2.2.2 2009/10/22 14:28:25 jfilip Exp $

 -----------------------------------------------------------------------------
  Ellumiante Live! Session Test Suite

  Justin Filip <jfilip@remote-learner.net>
  Remote Learner, Inc - http://www.remote-learner.net/
 -----------------------------------------------------------------------------

Usage: php ./sessiontest.php

PHP requirements:
 - command-line PHP executable binary
 - cURL
 - pcntl
 - shmop

This script test bulk session creation to simulate load generated from the new
group mode support in this activity module.  It works by creating a test course,
populating it with test groups, and assigning a single test user to each of
those groups and then creating a number of Elluminate Live! activity modules.

It needs to be run from the Moodle site with the Elluminate Live! module
actually configured to connect with an ELM server as it takes configuration
information from Moodle itself and uses the Moodle install's database connection
directly.

The script will fork to create child processes that will run in parallel, using
cURL to login to Moodle and access the load meeting link for a batch of groups.
This simulates, correctly, the action of students clicking on the link while
belonging to different groups.  The child tracks the time taken from sending
the request with cURL to receiving a JNLP file response.

Each child reports some data back to the parent process:
 - total time taken
 - worst session creation time
 - best session creation time
 - average session creation time

The parent then takes these values and presents them for an overall picture of
the time taken 

There are some controls available to change script behaviour in lib.php via some
PHP 'define()' calls near the top of the file.

 PHP_CLI        - the name of the PHP CLI binary (if the full path is needed,
                  modify it here).
 CHILD_PROCS    - the number of child processes to fork and run in parallel
                  (default: 10)
 ACTIVITY_COUNT - the number of Elluminate Live! activity modules to create in
                  the test course.  This number should be the same as CHILD_PROCS.
 GROUP_COUNT    - the number of groups to create and test.  This number must be
                  cleanly divisible by CHILD_PROCS (default: 2000).

The default behaviour of the script is to simulate a class with 10 individual
Elluminate Live! activities and 2000 individual groups where users from each
group would be attempting to join meetings at roughly the same time.
