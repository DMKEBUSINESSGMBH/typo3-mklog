<?php

/*
 * Copyright notice
 *
 * (c) 2011-2024 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mklog/Configuration/TypoScript/Backend/pageTSconfig.txt">'
);

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'web',
    'MklogBackend',
    'bottom',
    null,
    [
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'routeTarget' => DMK\Mklog\Controller\BackendModuleController::class.'::handleRequest',
        'access' => 'user,group',
        'name' => 'web_MklogBackend',
        'icon' => 'EXT:mklog/Resources/Public/Icons/Extension.png',
        'labels' => 'LLL:EXT:mklog/Resources/Private/Language/Backend.xlf',
    ]
);
