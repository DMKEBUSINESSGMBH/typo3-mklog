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
     * As logging might be used at early or not so common stages it might happen that the DI container is not
     * setup as in normal FE, BE and CLI requests. An example is the install tool or the database:export command.
     * In those cases the injection of the rate limiter factories does not work.
     */
    public static function getInstance(): ?static
    {
        try {
            $container = GeneralUtility::getContainer();
        } catch (\LogicException) {
            $container = null;
        }

        if (is_null(self::$instance) && ($container?->has(static::class) ?? false)) {
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
