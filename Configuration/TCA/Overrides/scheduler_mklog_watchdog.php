<?php

declare(strict_types=1);

/*
 * Copyright notice
 *
 * (c) 2011-2026 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

defined('TYPO3') || exit('Access denied.');

// TYPO3 13 has no native scheduler task types, see ext_localconf.php
if (!isset($GLOBALS['TCA']['tx_scheduler_task']) || !DMK\Mklog\Utility\VersionUtility::isTypo3Version14OrHigher()) {
    return;
}

$ll = 'LLL:EXT:mklog/Resources/Private/Language/locallang_db.xlf:';

$severityItems = [];
foreach (DMK\Mklog\Utility\SeverityUtility::getItems() as $severity => $name) {
    $severityItems[] = ['label' => $severity.' - '.ucfirst(strtolower((string) $name)), 'value' => $severity];
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_scheduler_task',
    [
        'tx_mklog_watchdog_transport' => [
            'label' => $ll.'scheduler_watchdog.transport',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'Mail', 'value' => '--div--'],
                    ['label' => 'Mail Message', 'value' => DMK\Mklog\WatchDog\Transport\MailTransport::class],
                    ['label' => 'Gelf (GrayLog)', 'value' => '--div--'],
                    ['label' => 'Gelf HTTP', 'value' => DMK\Mklog\WatchDog\Transport\Gelf\HttpGelf::class],
                    ['label' => 'Gelf UDP', 'value' => DMK\Mklog\WatchDog\Transport\Gelf\UdpGelf::class],
                ],
                'default' => DMK\Mklog\WatchDog\Transport\MailTransport::class,
            ],
        ],
        'tx_mklog_watchdog_credentials' => [
            'label' => $ll.'scheduler_watchdog.credentials',
            'description' => $ll.'scheduler_watchdog.credentials.description',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'required' => true,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'tx_mklog_watchdog_severity' => [
            'label' => $ll.'scheduler_watchdog.severity',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => $severityItems,
                'default' => DMK\Mklog\Utility\SeverityUtility::DEBUG,
            ],
        ],
        'tx_mklog_watchdog_message_limit' => [
            'label' => $ll.'scheduler_watchdog.message_limit',
            'description' => $ll.'scheduler_watchdog.message_limit.description',
            'config' => [
                'type' => 'number',
                'size' => 10,
                'default' => 100,
                'range' => [
                    'lower' => 0,
                ],
            ],
        ],
        'tx_mklog_watchdog_extension_whitelist' => [
            'label' => $ll.'scheduler_watchdog.extension_whitelist',
            'description' => $ll.'scheduler_watchdog.extension_list.description',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'tx_mklog_watchdog_extension_blacklist' => [
            'label' => $ll.'scheduler_watchdog.extension_blacklist',
            'description' => $ll.'scheduler_watchdog.extension_list.description',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'tx_mklog_watchdog_mail_subject' => [
            'label' => $ll.'scheduler_watchdog.mail_subject',
            'description' => $ll.'scheduler_watchdog.mail_subject.description',
            'displayCond' => 'FIELD:tx_mklog_watchdog_transport:=:'.DMK\Mklog\WatchDog\Transport\MailTransport::class,
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
    ]
);

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addRecordType(
    [
        'label' => $ll.'scheduler_watchdog_name',
        'description' => $ll.'scheduler_watchdog_description',
        'value' => DMK\Mklog\WatchDog\SchedulerWatchDog::class,
        'icon' => 'mimetypes-x-tx_scheduler_task_group',
        'group' => 'mklog',
    ],
    '
        --div--;core.form.tabs:general,
            tasktype,
            task_group,
            description,
            tx_mklog_watchdog_transport,
            tx_mklog_watchdog_credentials,
            tx_mklog_watchdog_severity,
            tx_mklog_watchdog_message_limit,
            tx_mklog_watchdog_extension_whitelist,
            tx_mklog_watchdog_extension_blacklist,
            tx_mklog_watchdog_mail_subject,
        --div--;core.form.tabs:timing,
            --palette--;;execution,
        --div--;core.form.tabs:access,
            disable,
        --div--;core.form.tabs:extended,',
    [],
    '',
    'tx_scheduler_task'
);
