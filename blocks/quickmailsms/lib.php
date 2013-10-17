<?php

// Written at Louisiana State University

abstract class quickmailsms {
    public static function _s($key, $a = null) {
        return get_string($key, 'block_quickmailsms', $a);
    }

    static function format_time($time) {
        return userdate($time, '%A, %d %B %Y, %I:%M %P');
    }

    static function cleanup($table, $contextid, $itemid) {
        global $DB;

        // Clean up the files associated with this email
        // Fortunately, they are only db references, but
        // they shouldn't be there, nonetheless.
        $filearea = end(explode('_', $table));

        $fs = get_file_storage();

        $fs->delete_area_files(
            $contextid, 'block_quickmailsms',
            'attachment_' . $filearea, $itemid
        );

        $fs->delete_area_files(
            $contextid, 'block_quickmailsms',
            $filearea, $itemid
        );

        return $DB->delete_records($table, array('id' => $itemid));
    }

    static function history_cleanup($contextid, $itemid) {
        return quickmailsms::cleanup('block_quickmailsms_log', $contextid, $itemid);
    }

    static function draft_cleanup($contextid, $itemid) {
        return quickmailsms::cleanup('block_quickmailsms_drafts', $contextid, $itemid);
    }

    static function process_attachments($context, $email, $table, $id) {
        global $CFG, $USER;

        $base_path = "block_quickmailsms/{$USER->id}";
        $moodle_base = "$CFG->tempdir/$base_path";

        if (!file_exists($moodle_base)) {
            mkdir($moodle_base, $CFG->directorypermissions, true);
        }

        $zipname = $zip = $actual_zip = '';

        if (!empty($email->attachment)) {
            $zipname = "attachment.zip";
            $actual_zip = "$moodle_base/$zipname";

            $safe_path = preg_replace('/\//', "\\/", $CFG->dataroot);
            $zip = preg_replace("/$safe_path\\//", '', $actual_zip);

            $packer = get_file_packer();
            $fs = get_file_storage();

            $files = $fs->get_area_files(
                $context->id,
                'block_quickmailsms',
                'attachment_' . $table,
                $id,
                'id'
            );

            $stored_files = array();

            foreach ($files as $file) {
                if($file->is_directory() and $file->get_filename() == '.')
                    continue;

                $stored_files[$file->get_filepath().$file->get_filename()] = $file;
            }

            $packer->archive_to_pathname($stored_files, $actual_zip);
        }

        return array($zipname, $zip, $actual_zip);
    }

    static function attachment_names($draft) {
        global $USER;

        $usercontext = get_context_instance(CONTEXT_USER, $USER->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draft, 'id');

        $only_files = array_filter($files, function($file) {
            return !$file->is_directory() and $file->get_filename() != '.';
        });

        $only_names = function ($file) { return $file->get_filename(); };

        $only_named_files = array_map($only_names, $only_files);

        return implode(',', $only_named_files);
    }

    static function filter_roles($user_roles, $master_roles) {
        return array_uintersect($master_roles, $user_roles, function($a, $b) {
            return strcmp($a->shortname, $b->shortname);
        });
    }

    static function load_config($courseid) {
        global $DB;

        $fields = 'name,value';
        $params = array('coursesid' => $courseid);
        $table = 'block_quickmailsms_config';

        $config = $DB->get_records_menu($table, $params, '', $fields);

        if (empty($config)) {
            $m = 'moodle';
            $allowstudents = get_config($m, 'block_quickmailsms_allowstudents');
            $roleselection = get_config($m, 'block_quickmailsms_roleselection');
            $prepender = get_config($m, 'block_quickmailsms_prepend_class');
            $receipt = get_config($m, 'block_quickmailsms_receipt');

            $config = array(
                'allowstudents' => $allowstudents,
                'roleselection' => $roleselection,
                'prepend_class' => $prepender,
                'receipt' => $receipt
            );
        }

        return $config;
    }

    static function default_config($courseid) {
        global $DB;

        $params = array('coursesid' => $courseid);
        $DB->delete_records('block_quickmailsms_config', $params);
    }

    static function save_config($courseid, $data) {
        global $DB;

        quickmailsms::default_config($courseid);

        foreach ($data as $name => $value) {
            $config = new stdClass;
            $config->coursesid = $courseid;
            $config->name = $name;
            $config->value = $value;

            $DB->insert_record('block_quickmailsms_config', $config);
        }
    }

    function delete_dialog($courseid, $type, $typeid) {
        global $CFG, $DB, $USER, $OUTPUT;

        $email = $DB->get_record('block_quickmailsms_'.$type, array('id' => $typeid));

        if (empty($email))
            print_error('not_valid_typeid', 'block_quickmailsms', '', $typeid);

        $params = array('courseid' => $courseid, 'type' => $type);
        $yes_params = $params + array('typeid' => $typeid, 'action' => 'confirm');

        $optionyes = new moodle_url('/blocks/quickmailsms/emaillog.php', $yes_params);
        $optionno = new moodle_url('/blocks/quickmailsms/emaillog.php', $params);

        $table = new html_table();
        $table->head = array(get_string('date'), quickmailsms::_s('subject'));
        $table->data = array(
            new html_table_row(array(
                new html_table_cell(quickmailsms::format_time($email->time)),
                new html_table_cell($email->subject))
            )
        );

        $msg = quickmailsms::_s('delete_confirm', html_writer::table($table));

        $html = $OUTPUT->confirm($msg, $optionyes, $optionno);
        return $html;
    }

    static function list_entries($courseid, $type, $page, $perpage, $userid, $count, $can_delete) {
        global $CFG, $DB, $OUTPUT;

        $dbtable = 'block_quickmailsms_'.$type;

        $table = new html_table();

        $params = array('courseid' => $courseid, 'userid' => $userid);
        $logs = $DB->get_records($dbtable, $params,
            'time DESC', '*', $page * $perpage, $perpage * ($page + 1));

        $table->head= array(get_string('date'), quickmailsms::_s('subject'),
            quickmailsms::_s('attachment'), get_string('action'));

        $table->data = array();

        foreach ($logs as $log) {
            $date = quickmailsms::format_time($log->time);
            $subject = $log->subject;
            $attachments = $log->attachment;

            $params = array(
                'courseid' => $log->courseid,
                'type' => $type,
                'typeid' => $log->id
            );

            $actions = array();

            $open_link = html_writer::link(
                new moodle_url('/blocks/quickmailsms/email.php', $params),
                $OUTPUT->pix_icon('i/search', 'Open Email')
            );
            $actions[] = $open_link;

            if ($can_delete) {
                $delete_params = $params + array(
                    'userid' => $userid,
                    'action' => 'delete'
                );

                $delete_link = html_writer::link (
                    new moodle_url('/blocks/quickmailsms/emaillog.php', $delete_params),
                    $OUTPUT->pix_icon("i/cross_red_big", "Delete Email")
                );

                $actions[] = $delete_link;
            }

            $action_links = implode(' ', $actions);

            $table->data[] = array($date, $subject, $attachments, $action_links);
        }

        $paging = $OUTPUT->paging_bar($count, $page, $perpage,
            '/blocks/quickmailsms/emaillog.php?type='.$type.'&amp;courseid='.$courseid);

        $html = $paging;
        $html .= html_writer::table($table);
        $html .= $paging;
        return $html;
    }
}

function block_quickmailsms_pluginfile($course, $record, $context, $filearea, $args, $forcedownload) {
    $fs = get_file_storage();
    global $DB;

    list($itemid, $filename) = $args;
    $params = array(
        'component' => 'block_quickmailsms',
        'filearea' => $filearea,
        'itemid' => $itemid,
        'filename' => $filename
    );

    $instanceid = $DB->get_field('files', 'id', $params);

    if (empty($instanceid)) {
        send_file_not_found();
    } else {
        $file = $fs->get_file_by_id($instanceid);
        send_stored_file($file);
    }
}
