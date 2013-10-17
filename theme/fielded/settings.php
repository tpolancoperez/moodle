<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	// Background image setting
	$name = 'theme_fielded/background';
	$title = get_string('background','theme_fielded');
	$description = get_string('backgrounddesc', 'theme_fielded');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
	$settings->add($setting);

	// logo image setting
	$name = 'theme_fielded/logo';
	$title = get_string('logo','theme_fielded');
	$description = get_string('logodesc', 'theme_fielded');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
	$settings->add($setting);

	// link color setting
	$name = 'theme_fielded/linkcolor';
	$title = get_string('linkcolor','theme_fielded');
	$description = get_string('linkcolordesc', 'theme_fielded');
	$default = '#32529a';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// link hover color setting
	$name = 'theme_fielded/linkhover';
	$title = get_string('linkhover','theme_fielded');
	$description = get_string('linkhoverdesc', 'theme_fielded');
	$default = '#4e2300';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// main color setting
	$name = 'theme_fielded/maincolor';
	$title = get_string('maincolor','theme_fielded');
	$description = get_string('maincolordesc', 'theme_fielded');
	$default = '#002f2f';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// main color accent setting
	$name = 'theme_fielded/maincoloraccent';
	$title = get_string('maincoloraccent','theme_fielded');
	$description = get_string('maincoloraccentdesc', 'theme_fielded');
	$default = '#092323';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// heading color setting
	$name = 'theme_fielded/headingcolor';
	$title = get_string('headingcolor','theme_fielded');
	$description = get_string('headingcolordesc', 'theme_fielded');
	$default = '#4e0000';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// block heading color setting
	$name = 'theme_fielded/blockcolor';
	$title = get_string('blockcolor','theme_fielded');
	$description = get_string('blockcolordesc', 'theme_fielded');
	$default = '#002f2f';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// forum subject background color setting
	$name = 'theme_fielded/forumback';
	$title = get_string('forumback','theme_fielded');
	$description = get_string('forumbackdesc', 'theme_fielded');
	$default = '#e6e2af';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

}