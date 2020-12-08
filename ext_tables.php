<?php

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
        [
        ],
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
