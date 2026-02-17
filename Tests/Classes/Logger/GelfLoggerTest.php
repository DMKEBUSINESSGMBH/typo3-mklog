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

namespace DMK\Mklog\Logger;

use DMK\Mklog\Utility\RateLimiterUtility;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\RateLimiter\Storage\CachingFrameworkStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

/**
 * Gelf Logger test.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class GelfLoggerTest extends \DMK\Mklog\Tests\BaseTestCase
{
    /**
     * Test the writeLog method.
     *
     * @group unit
     *
     * @test
     */
    public function testWriteLog(): void
    {
        $this->getGelfLoggerMock();

        self::markTestIncomplete();
    }

    /**
     * Returns the logger mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|GelfLogger
     */
    protected function getGelfLoggerMock(array $methods = [])
    {
        $logger = $this->getMock(
            GelfLogger::class,
            array_merge(
                ['getDevlogEntryRepository'],
                $methods
            )
        );

        $logger
            ->expects(self::any())
            ->method('getDevlogEntryRepository')
            ->willReturn($this->getDevlogEntryRepository());

        return $logger;
    }

    /**
     * @test
     */
    public function testWriteLogMindsRateLimiting(): void
    {
        $extKey = 'mklog';
        $severity = LogLevel::DEBUG;
        $extraData = ['foo' => 1, 'bar' => ['baz']];

        $logger = $this->getGelfLoggerMock(['storeLog']);

        $matcher = self::exactly(6);
        $logger
            ->expects($matcher)
            ->method('storeLog')
            ->with(
                $this->callback(function (string $message) use ($matcher): bool {
                    self::assertSame(
                        match ($matcher->numberOfInvocations()) {
                            1 => 'msg',
                            2 => 'msg',
                            3 => 'msg',
                            4 => 'otherMsg',
                            5 => 'otherMsg',
                            6 => 'andAnotherMsg',
                        },
                        $message
                    );

                    return true;
                }),
                $extKey,
                $severity,
                $extraData
            );

        $cache = new VariableFrontend('ratelimiter', new TransientMemoryBackend('testing'));
        $cacheManager = new CacheManager();
        $cacheManager->registerCache($cache);

        $rateLimiterStorage = new CachingFrameworkStorage($cacheManager);

        $perMessageFactory = GeneralUtility::makeInstance(
            RateLimiterFactory::class,
            [
                'id' => 'test-gelf-per-message',
                'policy' => 'sliding_window',
                'limit' => 3,
                'interval' => '1 minutes',
            ],
            $rateLimiterStorage
        );
        $allMessagesFactory = GeneralUtility::makeInstance(
            RateLimiterFactory::class,
            [
                'id' => 'test-gelf-all-messages',
                'policy' => 'sliding_window',
                'limit' => 6,
                'interval' => '1 minutes',
            ],
            $rateLimiterStorage
        );

        $rateLimiterUtility = new RateLimiterUtility($perMessageFactory, $allMessagesFactory);

        $logRecord = new LogRecord($extKey, $severity, 'msg', $extraData);

        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);
        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);
        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);
        // Ignored
        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);

        $logRecord = new LogRecord($extKey, $severity, 'otherMsg', $extraData);
        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);
        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);

        $logRecord = new LogRecord($extKey, $severity, 'andAnotherMsg', $extraData);
        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);
        // Ignored
        GeneralUtility::addInstance(RateLimiterUtility::class, $rateLimiterUtility);
        $logger->writeLog($logRecord);
    }
}
