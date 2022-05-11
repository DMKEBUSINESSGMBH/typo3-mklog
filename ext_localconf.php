<?php

/*
 * Copyright notice
 *
 * (c) 2011-2022 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklog" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

if (!defined('TYPO3')) {
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

        // cleanup task
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['DMK\\Mklog\\Task\\CleanupLogTableTask'] = [
            'extension' => 'mklog',
            'title' => 'LLL:EXT:'.'mklog'.'/locallang_db.xlf:scheduler_cleanup_log_table_name',
            'description' => 'LLL:EXT:'.'mklog'.'/locallang_db.xlf:scheduler_cleanup_log_table_description',
        ];

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['mklog'] = \DMK\Mklog\Hooks\DataHandler::class;

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
