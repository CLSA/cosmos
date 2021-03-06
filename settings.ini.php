<?php
/**
 * settings.ini.php
 *
 * Defines initialization settings.
 * DO NOT edit this file, to override these settings use settings.local.ini.php instead.
 * Any changes in the local ini file will override the settings found here.
 */

global $SETTINGS;

// tagged version
$SETTINGS['general']['application_name'] = 'cosmos';
$SETTINGS['general']['instance_name'] = $SETTINGS['general']['application_name'];
$SETTINGS['general']['version'] = '2.8';
$SETTINGS['general']['build'] = 'f12ee75';

// the location of the application's internal path
$SETTINGS['path']['APPLICATION'] = str_replace( '/settings.ini.php', '', __FILE__ );

// the location of the php_util repository
$SETTINGS['path']['PHP_UTIL'] = $SETTINGS['path']['APPLICATION'].'/../php_util';

// the location of new forms which need to be processed
$SETTINGS['path']['OPAL_VIEWS'] = $SETTINGS['path']['APPLICATION'].'/aux/opal_views';

// how many outliers/month a technician can have before it is considered an issue
$SETTINGS['general']['issue_threshold'] = 5;
