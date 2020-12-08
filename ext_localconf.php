<?php
/**
 * lokale Config laden.
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

tx_rnbase::load('DMK\Mklog\Factory');

$_EXTKEY = isset($_EXTKEY) ? $_EXTKEY : 'mklog';
$_EXTCONF = isset($_EXTCONF) ? $_EXTCONF : \Tx_Rnbase_Configuration_Processor::getExtensionCfgValue($_EXTKEY);
// Konfiguration umwandeln
$_EXTCONF = is_array($_EXTCONF) ? $_EXTCONF : unserialize($_EXTCONF);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    $_EXTKEY /* sv type */,
    'tx_mklog_srv_WatchDog' /* sv key */,
    [
        'title' => 'WatchDog services', 'description' => 'Service functions WatchDog',
        'subtype' => 'WatchDog',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/class.tx_mklog_srv_WatchDog.php',
        'className' => 'tx_mklog_srv_WatchDog',
    ]
);

// Register information for the test and sleep tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mklog_scheduler_WatchDog'] = [
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:scheduler_watchdog_name',
    'description' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:scheduler_watchdog_description',
    'additionalFields' => 'tx_mklog_scheduler_WatchDogAddFieldProvider',
];

tx_rnbase::load('DMK\\Mklog\\WatchDog\\SchedulerWatchDog');
tx_rnbase::load('DMK\\Mklog\\WatchDog\\SchedulerFieldProviderWatchDog');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['DMK\\Mklog\\WatchDog\\SchedulerWatchDog'] = [
    'extension' => $_EXTKEY,
    'title' => 'Watchdog',
    'description' => '',
    'additionalFields' => 'DMK\\Mklog\\WatchDog\\SchedulerFieldProviderWatchDog',
];

//cleanup task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['DMK\\Mklog\\Task\\CleanupLogTableTask'] = [
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:scheduler_cleanup_log_table_name',
    'description' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:scheduler_cleanup_log_table_description',
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['mklog'] = 'Tx_Mklog_Hooks_DataHandler';

// is the devlog enabled?
if (!empty($_EXTCONF['enable_devlog'])) {
    // the old devlog hook to log into tx_mklog_devlog_entry
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog']['mklog'] = \DMK\Mklog\Logger\DevlogLogger::class.'->devLogHook';
    // register logger writer
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][($_EXTCONF['min_log_level'] ?: \DMK\Mklog\Utility\SeverityUtility::DEBUG)]['DMK\\Mklog\\Logger\\DevlogLogger'] = [];
}
// is the gelf enabled?
if (!empty($_EXTCONF['gelf_enable'])) {
    // register logger writer
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][($_EXTCONF['gelf_min_log_level'] ?: \DMK\Mklog\Utility\SeverityUtility::ALERT)]['DMK\\Mklog\\Logger\\GelfLogger'] = [];
    // add system log hook, to log some critical logs directly
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog']['MklogGelfLoggerSysLogHook'] = \DMK\Mklog\Logger\GelfLogger::class.'->sysLogHook';
}
