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

namespace DMK\Mklog\Utility;

/**
 * MK Log composer util.
 *
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
final class ComposerUtility
{
    /**
     * Preloads the composer with the build in dependencies.
     */
    public static function autoload()
    {
        static $hasToLoad = true;

        if (false === $hasToLoad) {
            return;
        }

        if (static::isComposerMode()) {
            $hasToLoad = false;

            return;
        }

        require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
            'mklog',
            'Resources/Private/PHP/Composer/autoload.php'
        );
        $hasToLoad = false;
    }

    public static function isComposerMode(): bool
    {
        return \TYPO3\CMS\Core\Core\Environment::isComposerMode();
    }
}
