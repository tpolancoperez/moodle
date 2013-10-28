<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Graphic Wrap (Background Image)
    $name = 'theme_aardvark_postit/graphicwrap';
    $title=get_string('graphicwrap','theme_aardvark_postit');
    $description = get_string('graphicwrapdesc', 'theme_aardvark_postit');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // Logo file setting
    $name = 'theme_aardvark_postit/logo';
    $title = get_string('logo','theme_aardvark_postit');
    $description = get_string('logodesc', 'theme_aardvark_postit');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // Profile Bar (Background Image)
    $name = 'theme_aardvark_postit/profilebarbg';
    $title=get_string('profilebarbg','theme_aardvark_postit');
    $description = get_string('profilebarbgdesc', 'theme_aardvark_postit');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $settings->add($setting);

    // Menu select background colour setting
    $name = 'theme_aardvark_postit/menuhovercolor';
    $title = get_string('menuhovercolor','theme_aardvark_postit');
    $description = get_string('menuhovercolordesc', 'theme_aardvark_postit');
    $default = '#FFCC00';
    $previewconfig = NULL;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Profilebar postit-note(1) setting...
    $name = 'theme_aardvark_postit/postitnote1';
    $title = get_string('postitnote1','theme_aardvark_postit');
    $description = get_string('postitnotedesc', 'theme_aardvark_postit');
    $default = get_string('postitnotetxt', 'theme_aardvark_postit');
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    // Profilebar postit-note(2) setting...
    $name = 'theme_aardvark_postit/postitnote2';
    $title = get_string('postitnote2','theme_aardvark_postit');
    $description = get_string('postitnotedesc', 'theme_aardvark_postit');
    $default = get_string('postitnotetxt', 'theme_aardvark_postit');
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    // Email url setting

    $name = 'theme_aardvark_postit/emailurl';
    $title = get_string('emailurl','theme_aardvark_postit');
    $description = get_string('emailurldesc', 'theme_aardvark_postit');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    // Foot note setting
    $name = 'theme_aardvark_postit/footnote';
    $title = get_string('footnote', 'theme_aardvark_postit');
    $description = get_string('footnotedesc', 'theme_aardvark_postit');
    $default = get_string('footnotetxt', 'theme_aardvark_postit');
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $settings->add($setting);

    // Custom CSS file
    $name = 'theme_aardvark_postit/customcss';
    $title = get_string('customcss','theme_aardvark_postit');
    $description = get_string('customcssdesc', 'theme_aardvark_postit');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $settings->add($setting);
}
