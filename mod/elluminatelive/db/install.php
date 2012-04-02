<?php
// post install tasks for elluminate live

defined('MOODLE_INTERNAL') || die();

function xmldb_elluminatelive_install() {
    global $DB;
    $result  = true;
    $timenow = time();
    $sysctx  = get_context_instance(CONTEXT_SYSTEM);
    $adminrid          = $DB->get_field('role', 'id', array('shortname'=>'manager'));
    $coursecreatorrid  = $DB->get_field('role', 'id', array('shortname'=>'coursecreator'));
    $editingteacherrid = $DB->get_field('role', 'id', array('shortname'=>'editingteacher'));
    $teacherrid        = $DB->get_field('role', 'id', array('shortname'=>'teacher'));

/// Fully setup the Elluminate Moderator role.
    if ($result && !$mrole = $DB->get_record('role', array('shortname'=>'elluminatemoderator'))) {
        if ($rid = create_role(get_string('elluminatemoderator', 'elluminatelive'), 'elluminatemoderator',
                               get_string('elluminatemoderatordescription', 'elluminatelive'))) {

            $mrole  = $DB->get_record('role', array('id'=>$rid));
            $result = $result && assign_capability('mod/elluminatelive:moderatemeeting', CAP_ALLOW, $mrole->id, $sysctx->id);
        } else {
            $result = false;
        }
    }

    if (!$DB->count_records('role_allow_assign', array('allowassign'=>$mrole->id))) {
        // the old allow_assign function would return an int of the record added, in 2.0 it returns void, so we had to touch this up a little.
        allow_assign($adminrid, $mrole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$adminrid,'allowassign'=>$mrole->id));
        allow_assign($coursecreatorrid, $mrole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$coursecreatorrid,'allowassign'=>$mrole->id));
        allow_assign($editingteacherrid, $mrole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$editingteacherrid,'allowassign'=>$mrole->id));
        allow_assign($teacherrid, $mrole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$teacherrid,'allowassign'=>$mrole->id));
    }

/// Fully setup the Elluminate Participant role.
    if ($result && !$prole = $DB->get_record('role', array('shortname'=>'elluminateparticipant'))) {
        if ($rid = create_role(get_string('elluminateparticipant', 'elluminatelive'), 'elluminateparticipant',
                               get_string('elluminateparticipantdescription', 'elluminatelive'))) {
            $prole  = $DB->get_record('role', array('id'=>$rid));
            $result = $result && assign_capability('mod/elluminatelive:joinmeeting', CAP_ALLOW, $prole->id, $sysctx->id);
        } else {
            $result = false;
        }
    }

    if (!$DB->count_records('role_allow_assign', array('allowassign'=>$prole->id))) {
        allow_assign($adminrid, $prole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$adminrid,'allowassign'=>$prole->id));
        allow_assign($coursecreatorrid, $prole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$coursecreatorrid,'allowassign'=>$prole->id));
        allow_assign($editingteacherrid, $prole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$editingteacherrid,'allowassign'=>$prole->id));
        allow_assign($teacherrid, $prole->id);
        $result = $result && $DB->get_record('role_allow_assign', array('roleid'=>$teacherrid,'allowassign'=>$prole->id));
    }

    return $result;
}