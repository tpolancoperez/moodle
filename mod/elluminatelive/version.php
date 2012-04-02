<?php // $Id: version.php,v 1.1.2.3 2009/10/22 14:28:23 jfilip Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of elluminatelive
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version    = 2009062200;           // The current module version (Date: YYYYMMDDXX)
$module->requires   = 2011120502;           // Requires this Moodle version
$module->compontent = 'mod_elluminatelive'; // The module name
$module->cron       = 900;                  // Period for cron to check this module (secs)
?>