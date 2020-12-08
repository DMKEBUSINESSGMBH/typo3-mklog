<?php

namespace DMK\Mklog\WatchDog\Transport;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mklog\Domain\Model\GenericArrayObject;

/**
 * MK Log watchdog abstract transport.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractTransport implements InterfaceTransport
{
    /**
     * Internal options storage.
     *
     * @var GenericArrayObject
     */
    private $options = null;

    /**
     * Returns a storage.
     *
     * @return GenericArrayObject
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Initializes the Transport.
     */
    public function initialize(
        GenericArrayObject $options
    ) {
        $this->options = GenericArrayObject::getInstance($options);
    }

    /**
     * Deinitializes the Transport.
     */
    public function shutdown()
    {
    }
}
