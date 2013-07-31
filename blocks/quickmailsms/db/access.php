<?php

// Written at Louisiana State University
/**
* QuickmailSMS block caps.
*
* @package quickmailsms
* @copyright 
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
 

    'block/quickmailsms:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
    
      'block/quickmailsms:cansend' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array('manager' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'coursecreator' => CAP_ALLOW, 'teacher' => CAP_ALLOW), 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
    
    'block/quickmailsms:allowalternate' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array('manager' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'teacher' => CAP_ALLOW, 'coursecreator' => CAP_ALLOW), 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
    
    'block/quickmailsms:canconfig' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array('manager' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW)
    ),
    'block/quickmailsms:canimpersonate' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array('manager' => CAP_ALLOW), 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
 
);
