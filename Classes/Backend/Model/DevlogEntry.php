<?php

/*
 * Copyright notice
 *
 * (c) 2011-2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\Backend\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Devlog entry Model.
 *
 * @todo    legacy rnbase model support, remove if rn_base isn't used anymore
 *
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntry extends \DMK\Mklog\Domain\Model\DevlogEntry implements \Sys25\RnBase\Domain\Model\RecordInterface
{
    /**
     * DevlogEntry constructor.
     *
     * @TODO: legacy rn_base make instance model support, remove if rn_base isn't used anymore
     */
    public function __construct(array $record = null)
    {
        if (null !== $record && is_array($record)) {
            foreach ($record as $columnName => $value) {
                $propertyName = GeneralUtility::underscoredToLowerCamelCase($columnName);
                if (property_exists($this, $propertyName)) {
                    $this->{$propertyName} = $value;
                }
            }
        }
    }

    /**
     * @return array
     *
     * @TODO: legacy rnbase model support, remove if rn_base isn't used anymore
     */
    public function getProperty($property)
    {
        $properties = $this->getRecord();

        if (isset($properties[$property])) {
            return $properties[$property];
        }

        return null;
    }
}
