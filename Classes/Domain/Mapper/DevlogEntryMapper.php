<?php

/*
 * Copyright notice
 *
 * (c) 2011-2023 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\Domain\Mapper;

use DMK\Mklog\Domain\Model\DevlogEntry;
use DMK\Mklog\Factory;

/**
 * Devlog entry mapper.
 *
 * @author  Michael Wagner
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
     */
    public function __construct(DevlogEntry $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry(): DevlogEntry
    {
        return $this->entry;
    }

    public static function fromResults(array $result): array
    {
        $items = [];

        foreach ($result as $record) {
            $items[] = static::fromRecord($record)->getEntry();
        }

        return $items;
    }

    public static function fromRecord(array $record): DevlogEntryMapper
    {
        $entry = Factory::makeInstance(DevlogEntry::class)
            ->setUid($record['uid'] ?? 0)
            ->setPid($record['pid'] ?? 0)
            ->setRunId($record['run_id'] ?? 0)
            ->setSeverity($record['severity'] ?? 0)
            ->setExtKey($record['ext_key'] ?? '')
            ->setHost($record['host'] ?? '')
            ->setMessage($record['message'] ?? '')
            ->setExtraDataEncoded($record['extra_data'] ?? '')
            ->setCrdate($record['crdate'] ?? 0)
            ->setCruserId($record['cruser_id'] ?? 0)
            ->setTransportIdsRaw($record['transport_ids'] ?? '');

        return Factory::makeInstance(DevlogEntryMapper::class, $entry);
    }

    public static function fromEntry(DevlogEntry $entry): DevlogEntryMapper
    {
        return Factory::makeInstance(DevlogEntryMapper::class, $entry);
    }
}
