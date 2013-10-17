<?php

// Written at Louisiana State University

require_once($CFG->dirroot . '/blocks/quickmailsms/lib.php');

class block_quickmailsms extends block_list {
    function init() {
        $this->title = quickmailsms::_s('pluginname');
    }

    function applicable_formats() {
        return array('site' => false, 'my' => false, 'course-view' => true);
    }
    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $COURSE, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

        $config = quickmailsms::load_config($COURSE->id);
        $permission = has_capability('block/quickmailsms:cansend', $context);

        $can_send = ($permission or !empty($config['allowstudents']));

        $icon_class = array('class' => 'icon');

            $cparam = array('courseid' => $COURSE->id);

         if ($can_send) {
           $send_email_str = quickmailsms::_s('composenew');
            $send_email = html_writer::link(
                new moodle_url('/blocks/quickmailsms/email.php', $cparam),
                $send_email_str
            );
            $this->content->items[] = $send_email;
            $this->content->icons[] = $OUTPUT->pix_icon('i/email', $send_email_str, 'moodle', $icon_class);
/*
            $signature_str = quickmailsms::_s('signature');
            $signature = html_writer::link(
                new moodle_url('/blocks/quickmailsms/signature.php', $cparam),
                $signature_str
            );
            $this->content->items[] = $signature;
            $this->content->icons[] = $OUTPUT->pix_icon('i/edit', $signature_str, 'moodle', $icon_class);
*/
            $draft_params = $cparam + array('type' => 'drafts');
            $drafts_email_str = quickmailsms::_s('drafts');
            $drafts = html_writer::link(
                new moodle_url('/blocks/quickmailsms/emaillog.php', $draft_params),
                $drafts_email_str
            );
            $this->content->items[] = $drafts;
            $this->content->icons[] = $OUTPUT->pix_icon('i/settings', $drafts_email_str, 'moodle', $icon_class);

            $history_str = quickmailsms::_s('history');
            $history = html_writer::link(
                new moodle_url('/blocks/quickmailsms/emaillog.php', $cparam),
                $history_str
            );
            $this->content->items[] = $history;
            $this->content->icons[] = $OUTPUT->pix_icon('i/settings', $history_str, 'moodle', $icon_class);
        }
/*
        if (has_capability('block/quickmailsms:allowalternate', $context)) {
            $alt_str = quickmailsms::_s('alternate');
            $alt = html_writer::link(
                new moodle_url('/blocks/quickmailsms/alternate.php', $cparam),
                $alt_str
            );

            $this->content->items[] = $alt;
            $this->content->icons[] = $OUTPUT->pix_icon('i/edit', $alt_str, 'moodle', $icon_class);
        }
*/
        if (has_capability('block/quickmailsms:canconfig', $context)) {
            $config_str = quickmailsms::_s('config');
            $config = html_writer::link(
                new moodle_url('/blocks/quickmailsms/config.php', $cparam),
                $config_str
            );
            $this->content->items[] = $config;
            $this->content->icons[] = $OUTPUT->pix_icon('i/settings', $config_str, 'moodle', $icon_class);
        }

        return $this->content;
    }
}
