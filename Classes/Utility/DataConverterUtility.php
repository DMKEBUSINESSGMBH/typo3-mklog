<?php

namespace DMK\Mklog\Utility;

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
 * MK Log Severity Utility.
 *
 * Some code is taken from \TYPO3\CMS\Core\Log\LogLevel for backward compatibility
 *
 * @author Ingo Renner
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DataConverterUtility implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Converts data into a string.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function encode($data)
    {
        return json_encode($data, JSON_FORCE_OBJECT);
    }

    /**
     * Decodes an extra data string into an array.
     *
     * @param string $data
     *
     * @return array
     */
    public function decode($data)
    {
        if (is_array($data)) {
            return $data;
        }

        // @TODO: what todo with non json data?
        if ('{' !== $data[0]) {
            return ['data' => $data];
        }

        $data = json_decode($data);

        return (array) $data;
    }
}
