<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * zebra theme settings page
 *
 * @package    theme_zebra
 * @copyright  2011 Danny Wahl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

//This is the descriptor for the following header settings
$name = 'theme_zebra/headerinfo';
$heading = get_string('headerinfo', 'theme_zebra');
$information = get_string('headerinfodesc', 'theme_zebra');
$setting = new admin_setting_heading($name, $heading, $information);
$settings->add($setting);

//Set the path to the logo image
$name = 'theme_zebra/logourl';
$title = get_string('logourl','theme_zebra');
$description = get_string('logourldesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, 'logo/logo', PARAM_URL);
$settings->add($setting);

//Set the minimum height for the logo image
$name = 'theme_zebra/logourlheight';
$title = get_string('logourlheight','theme_zebra');
$description = get_string('logourlheightdesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '120px', PARAM_CLEAN, 5);
$settings->add($setting);

//Set alternate text for headermain
$name = 'theme_zebra/headeralt';
$title = get_string('headeralt','theme_zebra');
$description = get_string('headeraltdesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_CLEAN, 20);
$settings->add($setting);

//Set body background image url
$name = 'theme_zebra/backgroundurl';
$title = get_string('backgroundurl','theme_zebra');
$description = get_string('backgroundurldesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, 'core/background', PARAM_URL);
$settings->add($setting);

//This is the descriptor for the following color settings
$name = 'theme_zebra/colorsinfo';
$heading = get_string('colorsinfo', 'theme_zebra');
$information = get_string('colorsinfodesc', 'theme_zebra');
$setting = new admin_setting_heading($name, $heading, $information);
$settings->add($setting);

//Set body background color
$name = 'theme_zebra/backgroundcolor';
$title = get_string('backgroundcolor','theme_zebra');
$description = get_string('backgroundcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '#DDDDDD', PARAM_CLEAN, 10);
$settings->add($setting);

//Set links and menu color
$name = 'theme_zebra/firstcolor';
$title = get_string('firstcolor','theme_zebra');
$description = get_string('firstcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '#234B6F', PARAM_CLEAN, 10);
$settings->add($setting);

//Set hovering color
$name = 'theme_zebra/secondcolor';
$title = get_string('secondcolor','theme_zebra');
$description = get_string('secondcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '#4E7BA3', PARAM_CLEAN, 10);
$settings->add($setting);

//Set font color
$name = 'theme_zebra/thirdcolor';
$title = get_string('thirdcolor','theme_zebra');
$description = get_string('thirdcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '#2F2F2F', PARAM_CLEAN, 10);
$settings->add($setting);

//Set main content background color
$name = 'theme_zebra/fourthcolor';
$title = get_string('fourthcolor','theme_zebra');
$description = get_string('fourthcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '#F4F6F8', PARAM_CLEAN, 10);
$settings->add($setting);

//Set column background color
$name = 'theme_zebra/fifthcolor';
$title = get_string('fifthcolor','theme_zebra');
$description = get_string('fifthcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '#F4F6F8', PARAM_CLEAN, 10);
$settings->add($setting);

//Set page-header background color
$name = 'theme_zebra/sixthcolor';
$title = get_string('sixthcolor','theme_zebra');
$description = get_string('sixthcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, 'transparent', PARAM_CLEAN, 10);
$settings->add($setting);

//Set page-footer background color
$name = 'theme_zebra/seventhcolor';
$title = get_string('seventhcolor','theme_zebra');
$description = get_string('seventhcolordesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '#DDDDDD', PARAM_CLEAN, 10);
$settings->add($setting);

//This is the descriptor for the following color scheme settings
$name = 'theme_zebra/schemeinfo';
$heading = get_string('schemeinfo', 'theme_zebra');
$information = get_string('schemeinfodesc', 'theme_zebra');
$setting = new admin_setting_heading($name, $heading, $information);
$settings->add($setting);

//Set gradient style for blocks, navbar, etc...
$name = 'theme_zebra/colorscheme';
$title = get_string('colorscheme','theme_zebra');
$description = get_string('colorschemedesc', 'theme_zebra');
$default = 'none';
$choices = array('none'=>'None', 'dark'=>'Dark', 'light'=>'Light');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

//Set gradient style for custommenu
$name = 'theme_zebra/menucolorscheme';
$title = get_string('menucolorscheme','theme_zebra');
$description = get_string('menucolorschemedesc', 'theme_zebra');
$default = 'none';
$choices = array('none'=>'None', 'dark'=>'Dark', 'light'=>'Light');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$settings->add($setting);

//This is the descriptor for the following page layout settings
$name = 'theme_zebra/columninfo';
$heading = get_string('columninfo', 'theme_zebra');
$information = get_string('columninfodesc', 'theme_zebra');
$setting = new admin_setting_heading($name, $heading, $information);
$settings->add($setting);

//Set max width for one column layout
$name = 'theme_zebra/onecolmax';
$title = get_string('onecolmax','theme_zebra');
$description = get_string('onecolmaxdesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '599px', PARAM_CLEAN, 5);
$settings->add($setting);

//Set min width for two column layout: should be onecolmax +1
$name = 'theme_zebra/twocolmin';
$title = get_string('twocolmin','theme_zebra');
$description = get_string('twocolmindesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '600px', PARAM_CLEAN, 5);
$settings->add($setting);

//Set max width for two column layout
$name = 'theme_zebra/twocolmax';
$title = get_string('twocolmax','theme_zebra');
$description = get_string('twocolmaxdesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '799px', PARAM_CLEAN, 5);
$settings->add($setting);

//Set max width for two column layout: should be twocolmax +1
$name = 'theme_zebra/threecolmin';
$title = get_string('threecolmin','theme_zebra');
$description = get_string('threecolmindesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '800px', PARAM_CLEAN, 5);
$settings->add($setting);

//Set max width for three column layout
$name = 'theme_zebra/threecolmax';
$title = get_string('threecolmax','theme_zebra');
$description = get_string('threecolmaxdesc', 'theme_zebra');
$setting = new admin_setting_configtext($name, $title, $description, '100%', PARAM_CLEAN, 5);
$settings->add($setting);

//Enable page zooming (mobile devices)
$name = 'theme_zebra/enablezoom';
$visiblename = get_string('enablezoom', 'theme_zebra');
$title = get_string('enablezoom', 'theme_zebra');
$description = get_string('enablezoomdesc', 'theme_zebra');
$setting = new admin_setting_configcheckbox($name, $visiblename, $description, 0);
$settings->add($setting);

//This is the descriptor for the following mzebraellaneous settings
$name = 'theme_zebra/miscinfo';
$heading = get_string('miscinfo', 'theme_zebra');
$information = get_string('miscinfodesc', 'theme_zebra');
$setting = new admin_setting_heading($name, $heading, $information);
$settings->add($setting);

//Set custom css for theme
$name = 'theme_zebra/customcss';
$title = get_string('customcss', 'theme_zebra');
$description = get_string('customcssdesc', 'theme_zebra');
$setting = new admin_setting_configtextarea($name, $title, $description, null);
$settings->add($setting);

//Display branded footer logos
$name = 'theme_zebra/branding';
$visiblename = get_string('branding', 'theme_zebra');
$title = get_string('branding', 'theme_zebra');
$description = get_string('brandingdesc', 'theme_zebra');
$setting = new admin_setting_configcheckbox($name, $visiblename, $description, 0);
$settings->add($setting);
}