<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mklog".
 *
 * Auto generated 11-09-2014 12:29
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/
$EM_CONF['mklog'] = [
    'title' => 'MK Logging',
    'description' => 'Keep track of developer logs. Provides automatic email notification about important errors.',
    'category' => 'be',
    'author' => 'René Nitzsche, Michael Wagner, Hannes Bochmann',
    'author_company' => 'DMK E-BUSINESS',
    'author_email' => 'dev@dmk-ebusiness.de',
    'shy' => '',
    'dependencies' => 'rn_base,devlog,scheduler',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '10.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.24-10.4.99',
            'scheduler' => '',
        ],
        'conflicts' => [],
        'suggests' => [
            'rn_base' => '1.12.4-',
        ],
    ],
    'suggests' => [],
    'autoload' => [
        'classmap' => [
            'Classes/',
        ],
    ],
];
