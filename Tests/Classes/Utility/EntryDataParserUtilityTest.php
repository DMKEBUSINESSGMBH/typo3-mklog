<?php

namespace DMK\Mklog\WatchDog;

/***************************************************************
 * Copyright notice
 *
 * (c) 2019 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

if (!\class_exists('tx_rnbase')) {
    require_once \tx_rnbase_util_Extensions::extPath(
        'rn_base',
        'class.tx_rnbase.php'
    );
}
if (!\class_exists('DMK\\Mklog\\Tests\\BaseTestCase')) {
    require_once \tx_rnbase_util_Extensions::extPath(
        'mklog',
        'Tests/Classes/BaseTestCase.php'
    );
}

use DMK\Mklog\Domain\Model\DevlogEntryModel;
use DMK\Mklog\Factory;
use DMK\Mklog\Utility\EntryDataParserUtility;

/**
 * Scheduler WatchDog test.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class EntryDataParserUtilityTest extends \DMK\Mklog\Tests\BaseTestCase
{
    private const FIXTURE_EXTRA_DATA_JSON = '{"foo":"bar","bar":"baz","baz":"foo"}';
    private const FIXTURE_EXTRA_DATA_JSON_ARRAY = ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'foo'];

    /**
     * Test the getShortenedRaw method.
     *
     * @group unit
     * @test
     */
    public function getShortenedRawReturnsCompleteData()
    {
        $this->assertEquals(
            self::FIXTURE_EXTRA_DATA_JSON,
            $this->getEntryDataParserUtility()->getShortenedRaw()
        );
    }

    /**
     * Test the getShortenedRaw method.
     *
     * @group unit
     * @test
     */
    public function getShortenedRawReturnsShortData()
    {
        $this->assertEquals(
            '{"foo":"bar","bar":"baz","...":"Striped by 1 elements."}',
            $this->getEntryDataParserUtility()->getShortenedRaw(60)
        );
    }

    /**
     * Test the getShortenedRaw method.
     *
     * @group unit
     * @test
     */
    public function getShortenedRawReturnsEmptyData()
    {
        $this->assertEquals(
            '{"...":"Striped by 3 elements."}',
            $this->getEntryDataParserUtility()->getShortenedRaw(40)
        );
    }

    /**
     * Test the getShortenedExternalExtraData method.
     *
     * @group unit
     * @test
     */
    public function getShortenedExternalExtraDataReturnsCompleteData()
    {
        $this->assertEquals(
            self::FIXTURE_EXTRA_DATA_JSON_ARRAY,
            $this->getEntryDataParserUtility()->getShortenedExternalExtraData()
        );
    }

    /**
     * Test the getShortenedExternalExtraData method.
     *
     * @group unit
     * @test
     */
    public function getShortenedExternalExtraDataReturnsShortData()
    {
        $this->assertEquals(
            ['foo' => 'bar', '...' => 'Striped by 2 elements.'],
            $this->getEntryDataParserUtility()->getShortenedExternalExtraData(50)
        );
    }

    /**
     * Test the getShortenedExternalExtraData method.
     *
     * @group unit
     * @test
     */
    public function getShortenedExternalExtraDataReturnsEmptyData()
    {
        $this->assertEquals(
            ['...' => 'Striped by 3 elements.'],
            $this->getEntryDataParserUtility()->getShortenedExternalExtraData(40)
        );
    }

    /**
     * Creates an parser instance.
     *
     * @param DevlogEntryModel|null $devLogEntry
     *
     * @return EntryDataParserUtility
     */
    protected function getEntryDataParserUtility(DevlogEntryModel $devLogEntry = null)
    {
        if (null === $devLogEntry) {
            $devLogEntry = $this->getModel(
                ['extra_data' => self::FIXTURE_EXTRA_DATA_JSON],
                DevlogEntryModel::class
            );
        }

        return Factory::getEntryDataParserUtility($devLogEntry);
    }
}
