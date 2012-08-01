<?php
//
//  Copyright (c) 2011, Maths for More S.L. http://www.wiris.com
//  This file is part of Moodle WIRIS Plugin.
//
//  Moodle WIRIS Plugin is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Moodle WIRIS Plugin is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Moodle WIRIS Plugin. If not, see <http://www.gnu.org/licenses/>.
//

defined('MOODLE_INTERNAL') || die();

require_once('tinyversion.php');
$tiny_version = getTinyMceVersion();
include_once($CFG->dirroot . '/lib/editor/tinymce/tiny_mce/' . $tiny_version . '/plugins/tiny_mce_wiris/integration/libwiris.php');

global $DB;

/*
//Editor and CAS checkbox
$output = '';

$wiris_plugin_installed = opendir($CFG->dirroot . '/lib/editor/tinymce/tiny_mce/' . $tiny_version . '/plugins/tiny_mce_wiris');
if (!$wiris_plugin_installed)
	$output = 'WIRIS plugin is not installed on TinyMce. Go to http://www.wiris.com/en/plugins/moodle to download and install the plugin.';

	
$ini = wrs_loadConfig(WRS_CONFIG_FILE);
$formula = $ini['wirisformulaeditorenabled'];
$cas = $ini['wiriscasenabled'];

if (!$formula && !$cas) {
	$output = 'WIRIS editor and WIRIS cas are not installed';
} else if (!$formula) {
	$output = 'WIRIS editor is not installed';
} else if (!$cas) {
	$output = 'WIRIS cas is not installed';
} else {
	$output = '';
}

$settings->add(new admin_setting_heading('filter_wirisheading', 'WIRIS Filter Settings', $output));

if ($formula) {
	$settings->add(new admin_setting_configcheckbox('filter_wiris_editor_enable', 'WIRIS editor', '', '1'));
}

if ($cas) {
	$settings->add(new admin_setting_configcheckbox('filter_wiris_cas_enable', 'WIRIS cas', '', '1'));
}
*/

// Clearing cache.
if (isset($CFG->filter_wiris_clear_cache) && $CFG->filter_wiris_clear_cache) {

	$cache = wrs_getCacheDirectory(wrs_loadConfig(WRS_CONFIG_FILE));
	$directory = opendir($cache);
	
	if ($directory !== false) {
		$file = readdir($directory);
		
		while ($file !== false) {
			$filePath = $cache . '/' . $file;
			if (is_file($filePath)) {
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				if ($ext == 'png'){
					unlink($filePath);
				}
			}
			$file = readdir($directory);
		}
		closedir($directory);
	}
	
	// Disabling the cache clearing for the next request.
	try{	
		$record = $DB->get_record('config', array('name' => 'filter_wiris_clear_cache'));
		if ($record){
			$dataObject = new stdClass();
			$dataObject->id = $record->id;
			$dataObject->value = 0;
			$DB->update_record('config', $dataObject);
		}
	}catch(Exception $ex) {
		echo "Error retrieving or updating the table config";
	}
	$CFG->filter_wiris_clear_cache = false;
}

$settings->add(new admin_setting_configcheckbox('filter_wiris_clear_cache', 'Clear cache', '', '0'));