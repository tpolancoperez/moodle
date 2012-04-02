<?php

require_once('../../config.php');
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/course/lib.php');
//require_once('pagelib.php');

require_login();

if (isguestuser()) {  // Force them to see system default, no editing allowed
    exit();
} else {        // We are trying to view or edit our own My Moodle page
    $userid = $USER->id;  // Owner of the page
    $context = get_context_instance(CONTEXT_USER, $USER->id);
    $PAGE->set_context($context);
}


$mymoodlestr = get_string('mymoodle','my');

/*if (isguest()) {
    $wwwroot = $CFG->wwwroot.'/login/index.php';
    if (!empty($CFG->loginhttps)) {
        $wwwroot = str_replace('http:','https:', $wwwroot);
    }

    print_header($mymoodlestr);
    notice_yesno(get_string('noguest', 'my').'<br /><br />'.get_string('liketologin'),
                 $wwwroot, $CFG->wwwroot);
    print_footer();
    die();
}*/


$catlimit = optional_param('categories', 0, PARAM_INT);

if ($mid = optional_param('message', 0, PARAM_INT)) {
	if ($mymoodle = $DB->get_record('elis_mymoodle', array('userid' => $USER->id))) {
		if ($mymoodle->messages) {
			$messages = unserialize($mymoodle->messages);
		} else {
			$messages = array();
		}
		$messages[$mid] = 0;
		$mymoodle->messages = serialize($messages);
		$DB->update_record('elis_mymoodle', $mymoodle);
	} else {
		$mymoodle = new Object();
		$messages = array();
		$messages[$mid] = 0;
		$mymoodle->messages = serialize($messages);
		$mymoodle->options = serialize(array());
		$mymoodle->userid = $USER->id;

		$DB->insert_record('elis_mymoodle', $mymoodle);
	}
	return '';
}

if (!$catlimit) {
	// limits the number of courses showing up
    $tmpcourses = enrol_get_my_courses('*', 'category DESC,visible DESC,sortorder ASC');
    $courses = array();
    
    $cat = -99;
    $catcnt = 0;
    foreach ($tmpcourses as $key => $crs) {
    	if ($crs->category != $cat) {
    		$cat = $crs->category;
    		$catcnt++;
    		
    	}
    	if ($catcnt < 3 || $crs->category == 17) {
    		$courses[$key] = $crs;
    	}
    }
} elseif (is_numeric($catlimit)) {
	$action = optional_param('action', false, PARAM_ALPHANUM);
	
	$state = 1;
	if ($action && $action == 'close') {
		$state = 0;
	}
	
	$auto = false;
	if (optional_param('auto', 'false', PARAM_ALPHANUM) == 'true') {
	   $auto = true;
	}

	if ($mymoodle = $DB->get_record('elis_mymoodle', array('userid' => $USER->id))) {
		if ($mymoodle->options) {
			$options = unserialize($mymoodle->options);
		} else {
			$options = array();
		}
		
		$options[$catlimit] = $state;
		
		$mymoodle->options = serialize($options);
		if ($mymoodle->userchanged || !$auto) {
		  $mymoodle->userchanged = 1;
		} else {
		  $mymoodle->userchanged = 0;
		}
		$DB->update_record('elis_mymoodle', $mymoodle);
	} else {
		$mymoodle = new Object();
		$options = array();
		$options[$catlimit] = $state;
		$mymoodle->options = serialize($options);
		$mymoodle->userid = $USER->id;
		if ($mymoodle->userchanged || !$auto) {
		  $mymoodle->userchanged = 1;
		} else {
		  $mymoodle->userchanged = 0;
		}
		$DB->insert_record('elis_mymoodle', $mymoodle);
	}
	
	if (!$state) {
		return;
	}

	$tmpcourses = enrol_get_my_courses('*', 'category DESC,visible DESC,sortorder ASC');
    $courses = array();
    

    foreach ($tmpcourses as $key => $crs) {
    	if ($crs->category == $catlimit) {
    		$courses[$key] = $crs;
    	}
    }
} elseif ($catlimit == 'all') {
	$courses = enrol_get_my_courses('*', 'category DESC,visible DESC,sortorder ASC');
}

foreach ($courses as $c) {
    if (isset($USER->lastcourseaccess[$c->id])) {
        $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
    } else {
        $courses[$c->id]->lastaccess = 0;
    }
}
print $catlimit.'|||';
print_overview($courses);


?>