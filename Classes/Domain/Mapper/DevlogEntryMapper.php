<?php

namespace DMK\Mklog\Domain\Mapper;

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

use DMK\Mklog\Domain\Model\DevlogEntry;
use DMK\Mklog\Factory;

/**
 * Devlog entry mapper.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryMapper
{
    /**
     * @var DevlogEntry
     */
    protected $entry;

    /**
     * DevlogEntryMapper constructor.
     *
     * @param DevlogEntry $entry
     */
    public function __construct(DevlogEntry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * @return DevlogEntry
     */
    public function getEntry(): DevlogEntry
    {
        return $this->entry;
    }

    /**
     * @param array $result
     *
     * @return array
     */
    public static function fromResults(array $result): array
    {
        $items = [];

        foreach ($result as $record) {
            $items[] = static::fromRecord($record)->getEntry();
        }

        return $items;
    }

    /**
     * @param array $record
     *
     * @return array
     */
    public static function fromRecord(array $record): DevlogEntryMapper
    {
        $entry = Factory::makeInstance(DevlogEntry::class)
            ->setUid($record['uid'])
            ->setPid($record['pid'])
            ->setRunId($record['run_id'])
            ->setExtKey($record['ext_key'])
            ->setHost($record['host'])
            ->setMessage($record['message'])
            ->setExtraDataEncoded($record['extra_data'])
            ->setCrdate($record['crdate'])
            ->setCruserId($record['cruser_id'])
            ->setTransportIdsRaw($record['transport_ids']);

        return Factory::makeInstance(DevlogEntryMapper::class, $entry);
    }
}
