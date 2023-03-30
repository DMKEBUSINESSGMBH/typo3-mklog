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

namespace DMK\Mklog\Logger;

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

/**
 * Devlog Logger test.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogLoggerTest extends \DMK\Mklog\Tests\BaseTestCase
{
    /**
     * Test the isLoggingEnabled method.
     *
     * @group unit
     * @test
     */
    public function testIsLoggingEnabledWithoutDbShouldBeFalse()
    {
        // activate logging
        \DMK\Mklog\Factory::getStorage()->setLoggingActive(true);

        self::assertFalse(
            $this->callInaccessibleMethod(
                $this->getDevlogLoggerMock(),
                'isLoggingEnabled'
            )
        );
    }

    /**
     * Test the isLoggingEnabled method.
     *
     * @group unit
     * @test
     */
    public function testIsLoggingEnabledWithDisabledLogInGlobals()
    {
        // activate logging
        \DMK\Mklog\Factory::getStorage()->setLoggingActive(true);

        // create an dummy db object for unittests outside of a typo3 env
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklog']['nolog'] = true;
        $logger = $this->getDevlogLoggerMock();
        self::assertFalse($this->callInaccessibleMethod($logger, 'isLoggingEnabled'));

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = false;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklog']['nolog'] = false;
        $logger->expects(self::once())
            ->method('isDatabaseConnected')
            ->will(self::returnValue(true));
        self::assertTrue($this->callInaccessibleMethod($logger, 'isLoggingEnabled'));
    }

    /**
     * Test the isLoggingEnabled method.
     *
     * @group unit
     * @test
     */
    public function testIsLoggingEnabledByConfig()
    {
        self::markTestIncomplete();
    }

    /**
     * Test the storeLog method.
     *
     * @group unit
     * @test
     */
    public function testStoreLog()
    {
        $msg = 'msg';
        $extKey = 'mklog';
        $severity = 7;
        $extraData = ['foo' => 1, 'bar' => ['baz']];

        $logger = $this->getDevlogLoggerMock(['isLoggingEnabled']);

        $logger
            ->expects(self::any())
            ->method('isLoggingEnabled')
            ->will(self::returnValue(true));

        $that = $this; // workaround for php 5.3
        $repo = $this->callInaccessibleMethod($logger, 'getDevlogEntryRepository');
        $repo
            ->expects(self::once())
            ->method('persist')
            ->with(
                $this->callback(
                    function (DevlogEntry $model) use ($that) {
                        $this->assertSame(0, $model->getPid());
                        $that->assertGreaterThan(time() - 60, $model->getCrdate());
                        $this->assertSame('msg', $model->getMessage());
                        $this->assertSame('mklog', $model->getExtKey());
                        $this->assertSame(7, $model->getSeverity());
                        // how to check? on cli it is 0, on be runs the current user id!
                        // $this->assertSame(0, $model->getCruserId());

                        $extradata = $model->getExternalExtraData();
                        $that->assertArrayHasKey('foo', $extradata);
                        $that->assertArrayHasKey('bar', $extradata);
                        $extradata = $model->getInternalExtraData();
                        $that->assertArrayHasKey('feuser', $extradata);
                        $that->assertArrayHasKey('beuser', $extradata);
                        $that->assertArrayHasKey('requesturl', $extradata);
                        $that->assertArrayHasKey('trace', $extradata);

                        $logData = json_decode($model->getExtraDataRaw(), true);
                        $that->assertSame(1, $logData['foo']);
                        $that->assertSame(['baz'], $logData['bar']);
                        $that->assertArrayHasKey('__feuser', $logData);
                        $that->assertArrayHasKey('__beuser', $logData);
                        $that->assertArrayHasKey('__requesturl', $logData);
                        $that->assertArrayHasKey('__trace', $logData);

                        return true;
                    }
                )
            );

        $this->callInaccessibleMethod(
            [$logger, 'storeLog'],
            [$msg, $extKey, $severity, $extraData]
        );
    }

    /**
     * Returns the logger mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|DevlogLogger
     */
    protected function getDevlogLoggerMock(
        array $methods = []
    ) {
        $logger = $this->getMock(
            DevlogLogger::class,
            array_merge(
                ['getDevlogEntryRepository', 'isDatabaseConnected'],
                $methods
            )
        );

        $logger
            ->expects(self::any())
            ->method('getDevlogEntryRepository')
            ->will(self::returnValue($this->getDevlogEntryRepository()));

        return $logger;
    }
}
