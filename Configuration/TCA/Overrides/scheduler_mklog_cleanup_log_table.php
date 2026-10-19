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

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addRecordType(
    [
        'label' => 'LLL:EXT:mklog/Resources/Private/Language/locallang_db.xlf:scheduler_cleanup_log_table_name',
        'description' => 'LLL:EXT:mklog/Resources/Private/Language/locallang_db.xlf:scheduler_cleanup_log_table_description',
        'value' => DMK\Mklog\Task\CleanupLogTableTask::class,
        'icon' => 'mimetypes-x-tx_scheduler_task_group',
        'group' => 'mklog',
    ],
    '
        --div--;core.form.tabs:general,
            tasktype,
            task_group,
            description,
        --div--;core.form.tabs:timing,
            --palette--;;execution,
        --div--;core.form.tabs:access,
            disable,
        --div--;core.form.tabs:extended,',
    [],
    '',
    'tx_scheduler_task'
);
