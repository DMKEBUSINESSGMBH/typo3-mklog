<?php

/*
 * Copyright notice
 *
 * (c) 2011-2025 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use PHPUnit\Util\Test;
use TYPO3\CMS\Core\Core\Environment;
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
 * Devlog Logger abstract test.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class AbstractLoggerTest extends \DMK\Mklog\Tests\BaseTestCase
{
    /**
     * @var string
     */
    protected $lockFile;

    protected AbstractLogger $abstractLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $lockFilesFolder = Environment::getVarPath().'/lock/';
        GeneralUtility::mkdir_deep($lockFilesFolder);
        $this->lockFile = $lockFilesFolder.'mklog_exception_during_logging.lock';

        $this->abstractLogger = $this->getMockForAbstractClass(AbstractLogger::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        GeneralUtility::rmdir(Environment::getVarPath().'/lock/', true);
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testCreateDevlogEntry()
    {
        $logEntry = $this->callInaccessibleMethod(
            $this->abstractLogger,
            'createDevlogEntry',
            'This is a {placeholder_one} {placeholder_two}',
            'mklog',
            0,
            [
                'placeholder_one' => 'test',
                'placeholder_two' => 'message',
            ]
        );

        self::assertSame('This is a test message', $logEntry->getMessage());
    }

    /**
     * @group unit
     *
     * @test
     *
     * @dataProvider canMailBeSendDataProvider
     */
    public function canMailBeSend(?int $timeOffset, bool $canMailBeSend)
    {
        if (!is_null($timeOffset)) {
            file_put_contents($this->lockFile, time() - $timeOffset);
        }

        self::assertSame($canMailBeSend, $this->callInaccessibleMethod($this->abstractLogger, 'canMailBeSend'));
    }

    public function canMailBeSendDataProvider(): array
    {
        return [
            [null, true],
            [time(), true],
            [time() + 1, true],
            [time() + 100, true],
            [0, false],
            [50, false],
            [59, false],
            [60, false],
            [61, true],
            [610, true],
        ];
    }

    /**
     * @group unit
     *
     * @test
     */
    public function getExceptionTraceWithoutArguments()
    {
        try {
            throw new \Exception('An error occured');
        } catch (\Exception $exception) {
            self::assertStringNotContainsString(
                'Object(DMK\Mklog\Logger\AbstractLoggerTest)',
                $this->callInaccessibleMethod($this->abstractLogger, 'getExceptionTraceWithoutArguments', $exception)
            );
        }
    }
}
