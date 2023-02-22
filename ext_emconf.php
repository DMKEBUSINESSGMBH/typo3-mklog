<?php

/*
 * Copyright notice
 *
 * (c) 2011-2023 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
    'version' => '11.0.5',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'scheduler' => '',
        ],
        'conflicts' => [],
        'suggests' => [
            'rn_base' => '1.15.0-',
        ],
    ],
    'suggests' => [],
    'autoload' => [
        'classmap' => [
            'Classes/',
        ],
    ],
];
