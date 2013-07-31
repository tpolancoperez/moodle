<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	// Background image setting
	$name = 'theme_fulleronline/background';
	$title = get_string('background','theme_fulleronline');
	$description = get_string('backgrounddesc', 'theme_fulleronline');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
	$settings->add($setting);

	// logo image setting
	$name = 'theme_fulleronline/logo';
	$title = get_string('logo','theme_fulleronline');
	$description = get_string('logodesc', 'theme_fulleronline');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
	$settings->add($setting);

	// link color setting
	$name = 'theme_fulleronline/linkcolor';
	$title = get_string('linkcolor','theme_fulleronline');
	$description = get_string('linkcolordesc', 'theme_fulleronline');
	$default = '#32529a';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// link hover color setting
	$name = 'theme_fulleronline/linkhover';
	$title = get_string('linkhover','theme_fulleronline');
	$description = get_string('linkhoverdesc', 'theme_fulleronline');
	$default = '#4e2300';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// main color setting
	$name = 'theme_fulleronline/maincolor';
	$title = get_string('maincolor','theme_fulleronline');
	$description = get_string('maincolordesc', 'theme_fulleronline');
	$default = '#002f2f';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// main color accent setting
	$name = 'theme_fulleronline/maincoloraccent';
	$title = get_string('maincoloraccent','theme_fulleronline');
	$description = get_string('maincoloraccentdesc', 'theme_fulleronline');
	$default = '#092323';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// heading color setting
	$name = 'theme_fulleronline/headingcolor';
	$title = get_string('headingcolor','theme_fulleronline');
	$description = get_string('headingcolordesc', 'theme_fulleronline');
	$default = '#4e0000';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// block heading color setting
	$name = 'theme_fulleronline/blockcolor';
	$title = get_string('blockcolor','theme_fulleronline');
	$description = get_string('blockcolordesc', 'theme_fulleronline');
	$default = '#002f2f';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// forum subject background color setting
	$name = 'theme_fulleronline/forumback';
	$title = get_string('forumback','theme_fulleronline');
	$description = get_string('forumbackdesc', 'theme_fulleronline');
	$default = '#e6e2af';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

}