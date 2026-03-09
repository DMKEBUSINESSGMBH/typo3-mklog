<?php

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

use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * RateLimiterUtility.
 *
 * @author Hannes Müller-Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class RateLimiterUtility
{
    /**
     * Allow null only so this can be reset in unit test.
     *
     * @todo find a better way to reset in unit tests. backupStaticProperties is not working.
     */
    protected static ?RateLimiterUtility $instance = null;

    public function __construct(
        private RateLimiterFactory $perMessageLimiterFactory,
        private RateLimiterFactory $allMessagesLimiterFactory,
    ) {
    }

    /**
     * Following problem. It might happen that a log is written inside the install tool and the DI container is
     * different at some times in contrast to normal BE or FE requests and so DI for this class does not work all
     * the times. At all occasions it seems that the first instantiation works correct but only later ones fail.
     * So we make sure there is always only one instance. Actually symfony does that but it seems like this is based
     * on the DI container instance which differs in case of the install tool.
     */
    public static function getInstance(): static
    {
        if (is_null(self::$instance)) {
            self::$instance = GeneralUtility::makeInstance(static::class);
        }

        return self::$instance;
    }

    public function isRateLimitExceeded(LogRecord $record): bool
    {
        if (!$this->getPerMessageRateLimiter($record)->consume()->isAccepted()) {
            return true;
        }

        return !$this->getGlobalRateLimiter()->consume()->isAccepted();
    }

    protected function getPerMessageRateLimiter(LogRecord $record): LimiterInterface
    {
        return $this->perMessageLimiterFactory->create($record->getMessage().$record->getComponent().$record->getLevel());
    }

    protected function getGlobalRateLimiter(): LimiterInterface
    {
        return $this->allMessagesLimiterFactory->create();
    }
}
