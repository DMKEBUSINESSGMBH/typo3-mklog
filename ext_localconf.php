<?php
/**
 * lokale Config laden.
 * @package tx_mklog
 * @subpackage tx_mklog
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined ('TYPO3_MODE')) {
   die ('Access denied.');
}

$_EXTKEY = 'mklog';

//require_once(tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/ext_localconf.php');


tx_rnbase_util_Extensions::addService($_EXTKEY,  $_EXTKEY /* sv type */,  'tx_mklog_srv_WatchDog' /* sv key */,
  array(
    'title' => 'WatchDog services', 'description' => 'Service functions WatchDog',
    'subtype' => 'WatchDog',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/class.tx_mklog_srv_WatchDog.php',
    'className' => 'tx_mklog_srv_WatchDog',
  )
);

// Register information for the test and sleep tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mklog_scheduler_WatchDog'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:scheduler_watchdog_name',
	'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:scheduler_watchdog_description',
	'additionalFields' => 'tx_mklog_scheduler_WatchDogAddFieldProvider'
);
