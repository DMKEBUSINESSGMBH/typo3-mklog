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

namespace DMK\Mklog\WatchDog;

use DMK\Mklog\Factory;
use DMK\Mklog\Tests\BaseTestCase;
use DMK\Mklog\Utility\LogMessageUtility;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LogMessageUtilityTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class LogMessageUtilityTest extends BaseTestCase
{
    #[Test]
    #[DataProvider('isExcludedLogMessageDataProvider')]
    public function isExcludedLogMessage(array|string $excludedLogMessageRegularExpressions, bool $isExcluded): void
    {
        $configStorage = $this->callInaccessibleMethod([Factory::getConfigUtility(), 'getStorage'], []);
        $configStorage->setExtConf(
            [
                'excluded_log_message_regular_expressions' => $excludedLogMessageRegularExpressions,
            ]
        );
        $this->assertEquals(
            $isExcluded,
            GeneralUtility::makeInstance(LogMessageUtility::class)->isExcludedLogMessage('test')
        );
    }

    public static function isExcludedLogMessageDataProvider(): array
    {
        return [
            [['/test/'], true],
            ['/test/', true],
            [['wrong', '/test/'], true],
            ['wrong,/test/', true],
            [['wrong', '/[a-z]/'], true],
            ['wrong,/[a-z]/', true],
            [[], false],
            ['', false],
            [['wrong'], false],
            ['wrong', false],
            [['test'], false],
            ['test', false],
            [['/tost/'], false],
            ['/tost/', false],
            [['wrong', '/\d/'], false],
            ['wrong,/[0-9]/', false],
        ];
    }
}
