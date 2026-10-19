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

namespace DMK\Mklog\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getFeUser(): ?FrontendUserAuthentication
    {
        $frontendUser = null;
        if ($GLOBALS['TYPO3_REQUEST'] ?? null) {
            $frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
        }

        return is_object($frontendUser) ? $frontendUser : null;
    }

    /**
     * Get the current frontend user uid.
     */
    public static function getFeUserId(): int
    {
        $feuser = self::getFeUser();

        if (!$feuser instanceof FrontendUserAuthentication) {
            return 0;
        }

        return (int) ($feuser->user['uid'] ?? 0);
    }

    /**
     * Get the current backend user if available.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getBeUser(): ?BackendUserAuthentication
    {
        return is_object($GLOBALS['BE_USER'] ?? null) ? $GLOBALS['BE_USER'] : null;
    }

    /**
     * Get the current backend user uid if available.
     */
    public static function getBeUserId(): int
    {
        $beuser = self::getBeUser();

        if (!$beuser instanceof BackendUserAuthentication) {
            return 0;
        }

        return (int) ($beuser->user['uid'] ?? 0);
    }

    /**
     * Adds a flash message to the default queue.
     */
    public static function addFlashMessage(
        string $message,
        string $title,
        ContextualFeedbackSeverity $severity,
    ): void {
        GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier()
            ->enqueue(
                GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $severity)
            );
    }

    /**
     * Get the normalized params of the current request, if available.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getNormalizedParams(): ?NormalizedParams
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return null;
        }

        $normalizedParams = $request->getAttribute('normalizedParams');

        return $normalizedParams instanceof NormalizedParams ? $normalizedParams : null;
    }

    /**
     * Get the url of the current request (former TYPO3_REQUEST_URL).
     */
    public static function getRequestUrl(): string
    {
        return self::getNormalizedParams()?->getRequestUrl() ?? '';
    }

    /**
     * Get the host of the current request without port (former TYPO3_HOST_ONLY).
     */
    public static function getRequestHostOnly(): string
    {
        return self::getNormalizedParams()?->getRequestHostOnly() ?? '';
    }
}
