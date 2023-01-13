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

namespace DMK\Mklog\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * MK Log TYPO3 Utility class.
 *
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
final class Typo3Utility
{
    /**
     * Get the current frontend user.
     *
     * @return TypoScriptFrontendController
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getTsFe(): ?TypoScriptFrontendController
    {
        return isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : null;
    }

    /**
     * Get the current frontend user.
     *
     * @return FrontendUserAuthentication
     */
    public static function getFeUser(): ?FrontendUserAuthentication
    {
        $tsfe = self::getTsFe();

        return null !== $tsfe && is_object($tsfe->fe_user) ? $tsfe->fe_user : null;
    }

    /**
     * Get the current frontend user uid.
     */
    public static function getFeUserId(): int
    {
        $feuser = self::getFeUser();

        if (null === $feuser) {
            return 0;
        }

        return (int) ($feuser->user['uid'] ?? 0);
    }

    /**
     * Get the current backend user if available.
     *
     * @return BackendUserAuthentication
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getBeUser(): ?BackendUserAuthentication
    {
        return isset($GLOBALS['TSFE']) && is_object($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER'] : null;
    }

    /**
     * Get the current backend user uid if available.
     */
    public static function getBeUserId(): int
    {
        $beuser = self::getBeUser();

        if (null === $beuser) {
            return 0;
        }

        return (int) ($beuser->user['uid'] ?? 0);
    }
}
