<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

call_user_func(
    function () {
        $config = \DMK\Mklog\Factory::getConfigUtility();

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['DMK\\Mklog\\WatchDog\\SchedulerWatchDog'] = [
            'extension' => 'mklog',
            'title' => 'Watchdog',
            'description' => '',
            'additionalFields' => 'DMK\\Mklog\\WatchDog\\SchedulerFieldProviderWatchDog',
        ];

        //cleanup task
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['DMK\\Mklog\\Task\\CleanupLogTableTask'] = [
            'extension' => 'mklog',
            'title' => 'LLL:EXT:'.'mklog'.'/locallang_db.xml:scheduler_cleanup_log_table_name',
            'description' => 'LLL:EXT:'.'mklog'.'/locallang_db.xml:scheduler_cleanup_log_table_description',
        ];

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['mklog'] = 'Tx_Mklog_Hooks_DataHandler';

        // is the devlog enabled?
        if ($config->isEnableDevLog()) {
            // the old devlog hook to log into tx_mklog_devlog_entry
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog']['mklog'] = \DMK\Mklog\Logger\DevlogLogger::class.'->devLogHook';
            // register logger writer
            $loglevel = ($config->getMinLogLevel() ?: \DMK\Mklog\Utility\SeverityUtility::DEBUG);
            if (\DMK\Mklog\Utility\VersionUtility::isTypo3Version10OrHigher()) {
                $loglevel = \DMK\Mklog\Utility\SeverityUtility::getPsrLevelConstant($loglevel);
            }
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][$loglevel][\DMK\Mklog\Logger\DevlogLogger::class] = [];
        }
        // is the gelf enabled?
        if ($config->isGelfEnable()) {
            // add system log hook, to log some critical logs directly
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog']['MklogGelfLoggerSysLogHook'] = \DMK\Mklog\Logger\GelfLogger::class.'->sysLogHook';
            // register logger writer
            $loglevel = ($config->getGelfMinLogLevel() ?: \DMK\Mklog\Utility\SeverityUtility::ALERT);
            if (\DMK\Mklog\Utility\VersionUtility::isTypo3Version10OrHigher()) {
                $loglevel = \DMK\Mklog\Utility\SeverityUtility::getPsrLevelConstant($loglevel);
            }
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][$loglevel][\DMK\Mklog\Logger\DevlogLogger::class] = [];
        }
    }
);
