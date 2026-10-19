<?php

declare(strict_types=1);

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

namespace DMK\Mklog\Tests\Utility;

use DMK\Mklog\Tests\BaseTestCase;
use DMK\Mklog\Utility\Typo3Utility;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;

class Typo3UtilityTest extends BaseTestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    /**
     * @group unit
     */
    public function testRequestInformationWithoutRequest(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        self::assertNull(Typo3Utility::getNormalizedParams());
        self::assertSame('', Typo3Utility::getRequestUrl());
        self::assertSame('', Typo3Utility::getRequestHostOnly());
    }

    /**
     * @group unit
     */
    public function testRequestInformationFromNormalizedParams(): void
    {
        $serverParams = [
            'HTTP_HOST' => 'www.example.org:8080',
            'SERVER_PORT' => '8080',
            'REQUEST_URI' => '/some/page?foo=bar',
            'SCRIPT_NAME' => '/index.php',
        ];
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('http://www.example.org:8080/some/page?foo=bar', 'GET', null, [], $serverParams))
            ->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($serverParams));

        self::assertSame('http://www.example.org:8080/some/page?foo=bar', Typo3Utility::getRequestUrl());
        self::assertSame('www.example.org', Typo3Utility::getRequestHostOnly());
    }
}
