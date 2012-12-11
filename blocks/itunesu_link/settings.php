<?php 

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configcheckbox(
           'block_itunesu_link_strict',
           'Strict',
           'Set Strict',
           '0'
       ));

?>
