<?php

namespace DMK\Mklog\Utility;

/***************************************************************
 * Copyright notice
 *
 * (c) 2020 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * MK Log TYPO3 Utility class.
 *
 * @author Michael Wagner
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
        return is_object($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : null;
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
     *
     * @return int
     */
    public static function getFeUserId(): int
    {
        $feuser = self::getFeUser();

        return null === $feuser ? 0 : (int) $feuser->user['uid'];
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
        return is_object($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER'] : null;
    }

    /**
     * Get the current backend user uid if available.
     *
     * @return int
     */
    public static function getBeUserId(): int
    {
        $beuser = self::getBeUser();

        return null === $beuser ? 0 : (int) $beuser->user['uid'];
    }
}
