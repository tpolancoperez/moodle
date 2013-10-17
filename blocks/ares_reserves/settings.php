<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	
	// User agent
	$userAgent = get_cfg_var( "user_agent" );
  	$defaultUserAgent = ( $userAgent == null ) ? "Moodle" : $userAgent;  
    $settings->add(new admin_setting_configtext('block_ares_reserves_userAgent', get_string('userAgentLabel', 'block_ares_reserves'), '' , $defaultUserAgent, PARAM_MULTILANG));

	// Web service location
    $settings->add(new admin_setting_configtext('block_ares_reserves_serviceLoc', get_string('webServiceLocation', 'block_ares_reserves'), '', '', PARAM_MULTILANG));

	// Web site location
    $settings->add(new admin_setting_configtext('block_ares_reserves_siteLoc', get_string('aresSitelocation', 'block_ares_reserves'), '', '', PARAM_MULTILANG));
    
    // Default display format
    include_once("ItemDisplayFormats.php");
	global $formatTypes;
	$settings->add(new admin_setting_configselect('block_ares_reserves_defaultDisplayFormat', get_string('defaultDisplayFormatLabel', 'block_ares_reserves'), '', '', $formatTypes));
}
