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

namespace DMK\Mklog\Tests\Domain\Mapper;

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

use DMK\Mklog\Domain\Mapper\DevlogEntryMapper;
use DMK\Mklog\Domain\Model\DevlogEntry;
use PHPUnit\Framework\Attributes\Test;

/**
 * Devlog entry mapper testcase.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryMapperTest extends \DMK\Mklog\Tests\BaseTestCase
{
    /**
     * Test the getEntry and fromEntry method.
     *
     * @group unit
     *
     * @test
     */
    #[Test]
    public function getEntryReturnsCorrectEntry(): void
    {
        $model = new DevlogEntry();
        $mapper = DevlogEntryMapper::fromEntry($model);
        self::assertSame($model, $mapper->getEntry());
    }

    /**
     * Test the fromRecord method.
     *
     * @group unit
     *
     * @test
     */
    #[Test]
    public function fromRecordReturnsCorrectEntry(): void
    {
        $record = [
            'uid' => 5,
            'pid' => 7,
            'run_id' => '0123456789',
            'ext_key' => 'mklog',
            'host' => 'test.localhost.net',
            'message' => 'Just a test.',
            'extra_data' => 'testdata',
            'tstamp' => 987654321,
            'crdate' => 987654321,
            'cruser_id' => 0,
            'transport_ids' => '',
        ];
        $entry = DevlogEntryMapper::fromRecord($record)->getEntry();
        self::assertSame(5, $entry->getUid());
        self::assertSame(7, $entry->getPid());
        self::assertSame('0123456789', $entry->getRunId());
        self::assertSame('mklog', $entry->getExtKey());
        self::assertSame('test.localhost.net', $entry->getHost());
        self::assertSame('Just a test.', $entry->getMessage());
        self::assertSame('testdata', $entry->getExtraDataRaw());
        self::assertSame(987654321, $entry->getCrdate());
        self::assertSame([], $entry->getTransportIds());
        self::assertSame(7, $entry->getPid());
    }

    /**
     * Test the fromResults method.
     *
     * @group unit
     *
     * @test
     */
    #[Test]
    public function fromResultsReturnsCorrectEntries(): void
    {
        $record0 = ['uid' => 5, 'run_id' => '6', 'crdate' => time()];
        $record1 = ['uid' => 7, 'run_id' => '8', 'crdate' => time()];
        $results = DevlogEntryMapper::fromResults([$record0, $record1]);

        self::assertCount(2, $results);
        self::assertInstanceOf(DevlogEntry::class, $results[0]);
        self::assertSame(5, $results[0]->getUid());
        self::assertInstanceOf(DevlogEntry::class, $results[1]);
        self::assertSame(7, $results[1]->getUid());
    }
}
