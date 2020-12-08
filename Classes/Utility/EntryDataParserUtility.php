<?php

namespace DMK\Mklog\Utility;

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

use DMK\Mklog\Domain\Model\DevlogEntry;
use DMK\Mklog\Factory;

/**
 * MK Log devlog message parser utility.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class EntryDataParserUtility
{
    const SIZE_512KB = 524288;
    const SIZE_1MB = 1048576;
    const SIZE_8MB = 8388608;

    /**
     * @var DevlogEntry
     */
    protected $devlogEntry;

    /**
     * @var DataConverterUtility
     */
    protected $converter;

    public function __construct(DevlogEntry $devlogEntry)
    {
        $this->devlogEntry = $devlogEntry;
        $this->converter = Factory::getDataConverterUtility();
    }

    /**
     * Returns the shortened raw extra data as json string.
     *
     * @param int $maxLen
     *
     * @return string
     */
    public function getShortenedRaw($maxLen = null)
    {
        return $this->converter->encode(
            $this->stripJson(
                $this->converter->decode(
                    $this->devlogEntry->getExtraDataRaw()
                ),
                $maxLen
            )
        );
    }

    /**
     * Returns the shortened external data as json sting.
     *
     * @param int $maxLen
     *
     * @return array
     */
    public function getShortenedInternalExtraData($maxLen = null)
    {
        return $this->stripJson(
            $this->devlogEntry->getInternalExtraData(),
            $maxLen
        );
    }

    /**
     * Returns the shortened external data as json sting.
     *
     * @param int $maxLen
     *
     * @return array
     */
    public function getShortenedExternalExtraData($maxLen = null)
    {
        return $this->stripJson(
            $this->devlogEntry->getExternalExtraData(),
            $maxLen
        );
    }

    /**
     * Strip json data to fit in max len.
     *
     * Data entries will be removed from the end while the max len matches.
     *
     * @param int $maxLen
     *
     * @return array
     */
    protected function stripJson(array $jsonData, $maxLen = null)
    {
        $striped = 0;
        $rawLen = $this->getStringSize($this->converter->encode($jsonData));
        // reduce max len by 30 char length stripped comment
        $maxLen = abs($maxLen ?: Factory::getConfigUtility()->getMaxTransportExtraDataSize()) - 30;

        // we remove data entries from the end while we have the maxlength
        while (true) {
            $jsonString = $this->converter->encode($jsonData);
            if ($this->getStringSize($jsonString) <= $maxLen) {
                break;
            }
            array_pop($jsonData);
            ++$striped;
        }

        if ($striped > 0) {
            $jsonData['...'] = 'Striped by '.$striped.' elements.';
        }

        return $jsonData;
    }

    /**
     * Calculates the String size in byte.
     *
     * @param string $data
     *
     * @return int
     */
    protected function getStringSize($data)
    {
        return strlen($data);
    }
}
