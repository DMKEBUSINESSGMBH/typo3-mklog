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

/**
 * RateLimiterUtility.
 *
 * @author Hannes Müller-Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class RateLimiterUtility
{
    private RateLimiterFactory $perMessageLimiterFactory;
    private RateLimiterFactory $allMessagesLimiterFactory;

    public function __construct(
        RateLimiterFactory $perMessageLimiterFactory,
        RateLimiterFactory $allMessagesLimiterFactory
    ) {
        $this->perMessageLimiterFactory = $perMessageLimiterFactory;
        $this->allMessagesLimiterFactory = $allMessagesLimiterFactory;
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
