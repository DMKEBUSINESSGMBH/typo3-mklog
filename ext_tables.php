<?php

/*
 * Copyright notice
 *
 * (c) 2011-2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

defined('TYPO3_MODE') || exit('Access denied.');

// register be module if rn_base is installed
if (TYPO3_MODE == 'BE' && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rn_base')) {
    // add be module ts
    tx_rnbase_util_Extensions::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mklog/Configuration/TypoScript/Backend/pageTSconfig.txt">'
    );

    // register web_MklogBackend
    tx_rnbase::load('DMK\\Mklog\\Backend\\ModuleBackend');
    tx_rnbase_util_Extensions::registerModule(
        'mklog',
        'web',
        'backend',
        'bottom',
        [],
        [
            'access' => 'user,group',
            'routeTarget' => 'DMK\\Mklog\\Backend\\ModuleBackend',
            'icon' => 'EXT:mklog/ext_icon.png',
            'labels' => 'LLL:EXT:mklog/Resources/Private/Language/Backend.xlf',
        ]
    );

    // register devlog be module
    tx_rnbase::load('DMK\\Mklog\\Backend\\Module\\DevlogModule');
    tx_rnbase_util_Extensions::insertModuleFunction(
        'web_MklogBackend',
        'DMK\\Mklog\\Backend\\Module\\DevlogModule',
        null,
        'LLL:EXT:mklog/Resources/Private/Language/Backend.xlf:label_func_devlog'
    );
}
