<?php

/*
 * Copyright notice
 *
 * (c) 2011-2024 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\WatchDog\Transport\Gelf;

use Gelf\Transport\AbstractTransport;
use Gelf\Transport\UdpTransport;

/**
 * MK Log watchdog gelf transporter.
 *
 * All chunks MUST arrive within 5 seconds
 * or the server will discard all already arrived and still arriving chunks.
 * A message MUST NOT consist of more than 128 chunks.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class UdpGelf extends AbstractGelf
{
    /**
     * Creates the Transport.
     *
     * @return AbstractTransport
     */
    protected function getTransport(): UdpTransport
    {
        [$host, $port] = explode(
            ':',
            $this->getOptions()->getCredentials()
        );

        return new UdpTransport(
            trim($host),
            trim($port),
            UdpTransport::CHUNK_SIZE_LAN
        );
    }
}
