<?php
ini_set( 'display_errors', '1' );
error_reporting( E_ALL );

require_once (dirname(__FILE__).'/../../settings.ini.php');

require_once $SETTINGS['path']['PHP_UTIL'].'/opalcurl.class.php';
require_once $SETTINGS['path']['PHP_UTIL'].'/database.class.php';
require_once $SETTINGS['path']['PHP_UTIL'].'/util.class.php';
require_once $SETTINGS['path']['PHP_UTIL'].'/phplot-6.2.0/phplot.php';

//$semaphore = sem_get( getmyinode() );
//sem_acquire( $semaphore ) or die( 'Unable to acquire semaphore.' );

ini_set( 'date.timezone', 'US/Eastern' );

$db = new database(
  $SETTINGS['db']['DB_SERV'],
  $SETTINGS['db']['DB_USER'],
  $SETTINGS['db']['DB_PASS'],
  $SETTINGS['db']['DB_NAME'] );
