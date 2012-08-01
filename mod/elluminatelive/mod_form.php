<?php // $Id: mod_form.php,v 1.1.2.3 2009/10/22 14:28:24 jfilip Exp $

/**
 * Standard activity module configuration form.
 *
 * @version $Id: mod_form.php,v 1.1.2.3 2009/10/22 14:28:24 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */

require_once $CFG->dirroot . '/course/moodleform_mod.php';
require_once dirname(__FILE__) . '/lib.php';

class mod_elluminatelive_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $USER;

        $elluminatelive_boundary_times = array (
			0 => '0',
			15 => '15',
			30 => '30',
			45 => '45',		
			60 => '60'
		);

//-------------------------------------------------------------------------------

        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('title', 'elluminatelive'), array('size' => '64', 'maxlength' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'sessionname', get_string('sessionname', 'elluminatelive'), array('size' => '64', 'maxlength' => '64'));
        $mform->setType('sessionname', PARAM_TEXT);

        $mform->addElement('checkbox', 'customname', get_string('customname', 'elluminatelive'));

    /// Don't allow editing of the session name as it will be automatically generated if the 'customname'
    /// option is enabled.
        $mform->disabledIf('sessionname', 'customname', 'checked', '1');

        $mform->addElement('htmleditor', 'description', get_string('elum_session_description', 'elluminatelive'));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('checkbox', 'customdescription', get_string('customdescription', 'elluminatelive'));

        $mform->addElement('date_time_selector', 'timestart', get_string('meetingbegins', 'elluminatelive'));
        $mform->addElement('date_time_selector', 'timeend', get_string('meetingends', 'elluminatelive'));

        $recording_options = array(
            ELLUMINATELIVE_RECORDING_NONE      => get_string('disabled', 'elluminatelive'),
            ELLUMINATELIVE_RECORDING_MANUAL    => get_string('manual', 'elluminatelive'),
            ELLUMINATELIVE_RECORDING_AUTOMATIC => get_string('automatic', 'elluminatelive')
        );

        $mform->addElement('select', 'recordingmode', get_string('recordmeeting', 'elluminatelive') , $recording_options);
        $mform->setDefault('recordingmode', ELLUMINATELIVE_RECORDING_MANUAL);
        //$mform->setHelpButton('recordingmode', array('recording', get_string('helprecording', 'elluminatelive'), 'elluminatelive'));
        $mform->addHelpButton('recordingmode', 'recordingmode', 'elluminatelive');

    /// Don't allow choosing a boundary time if there is a globally defined default time.
        if (!empty($CFG->elluminatelive_boundary_default)) {
            $attributes = array('disabled' => 'true');
        } else {
            $attributes = '';
        }

        $boundaryselect = $mform->addElement('select', 'boundarytime', get_string('boundarytime', 'elluminatelive') ,
                                             $elluminatelive_boundary_times, $attributes);

        if (!empty($CFG->elluminatelive_boundary_default)) {
            $mform->setConstant('boundarytime', $CFG->elluminatelive_boundary_default);
        }

        $mform->setDefault('boundarytime', ELLUMINATELIVE_BOUNDARY_DEFAULT);
        //$mform->setHelpButton('boundarytime', array('boundarytime', get_string('helpboundarytime', 'elluminatelive'), 'elluminatelive'));
        $mform->addHelpButton('boundarytime', 'boundarytime', 'elluminatelive');

        $mform->addElement('checkbox', 'boundarytimedisplay', get_string('boundarytimedisplay', 'elluminatelive'));

        $mform->addElement('modgrade', 'grade', get_string('gradeattendance', 'elluminatelive'));
        $mform->setDefault('grade', 0);

        $mform->addElement('select', 'private', get_string('privatemeeting', 'elluminatelive'),
                           array(0 => get_string('no'), 1 => get_string('yes')));
        $mform->setDefault('private', 0);
        //$mform->setHelpButton('private', array('private', get_string('helpprivatemeeting', 'elluminatelive'), 'elluminatelive'));
        $mform->addHelpButton('private', 'private', 'elluminatelive');

    /// Make sure the ELM server has seat reservation enabled.
        if (elluminatelive_seat_reservation_check()) {
            $seatelements = array();

            $attributes = array(
                'size'      => '6',
                'maxlength' => '6'
            );

        /// Disable this option if we have globally disabled seat reservation for the activity module.
            if (empty($CFG->elluminatelive_seat_reservation)) {
                $attributes['disabled'] = 'true';
            }

            $seatelements[] =& $mform->createElement('text', 'seats', get_string('reservedseats', 'elluminatelive'), $attributes);
            $mform->setType('seats', PARAM_INT);

            if (!empty($CFG->elluminatelive_seat_reservation)) {
                $seatelements[] =& $mform->createElement('static', 'checkseats', '', '<a onclick="checkAvailability(\'mform1\');" href="#">'.
                                                         get_string('checkavailability', 'elluminatelive').'</a>');
            }

            $mform->addGroup($seatelements, 'seatgroup', get_string('reservedseats', 'elluminatelive'), array(' '), false);

            //$mform->setHelpButton('seatgroup', array('seats', get_string('helpseats', 'elluminatelive'), 'elluminatelive'));
	     $mform->addHelpButton('seatgroup', 'seatgroup', 'elluminatelive');
        }

//-------------------------------------------------------------------------------
        $features = new stdClass;
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);
//-------------------------------------------------------------------------------

/// Add rules for group name dependent options defined earlier.
        $mform->disabledIf('customname', 'groupmode', 'eq', NOGROUPS);
        $mform->disabledIf('customdescription', 'groupmode', 'eq', NOGROUPS);

// buttons
        $this->add_action_buttons();

    }

    function display() {
        global $CFG, $PAGE;

        $PAGE->requires->js('/mod/elluminatelive/checkavailability.js');
        //require_js($CFG->wwwroot . '/mod/elluminatelive/checkavailability.js');

        return parent::display();
    }
}

?>
