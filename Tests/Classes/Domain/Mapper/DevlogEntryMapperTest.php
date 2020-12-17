<?php

namespace DMK\Mklog\Domain\Model;

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
     * @test
     */
    public function getEntryReturnsCorrectEntry()
    {
        $model = new DevlogEntry();
        $mapper = DevlogEntryMapper::fromEntry($model);
        $this->assertSame($model, $mapper->getEntry());
    }

    /**
     * Test the fromRecord method.
     *
     * @group unit
     * @test
     */
    public function fromRecordReturnsCorrectEntry()
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
        $this->assertSame(5, $entry->getUid());
        $this->assertSame(7, $entry->getPid());
        $this->assertSame('0123456789', $entry->getRunId());
        $this->assertSame('mklog', $entry->getExtKey());
        $this->assertSame('test.localhost.net', $entry->getHost());
        $this->assertSame('Just a test.', $entry->getMessage());
        $this->assertSame('testdata', $entry->getExtraDataRaw());
        $this->assertSame(987654321, $entry->getCrdate());
        $this->assertSame([], $entry->getTransportIds());
    }

    /**
     * Test the fromResults method.
     *
     * @group unit
     * @test
     */
    public function fromResultsReturnsCorrectEntries()
    {
        $record0 = ['uid' => 5, 'run_id' => '6', 'crdate' => time()];
        $record1 = ['uid' => 7, 'run_id' => '8', 'crdate' => time()];
        $results = DevlogEntryMapper::fromResults([$record0, $record1]);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(DevlogEntry::class, $results[0]);
        $this->assertSame(5, $results[0]->getUid());
        $this->assertInstanceOf(DevlogEntry::class, $results[1]);
        $this->assertSame(7, $results[1]->getUid());
    }
}
