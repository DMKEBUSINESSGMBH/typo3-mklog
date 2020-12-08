<?php

namespace DMK\Mklog\Logger;

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
        $connection = $this->callInaccessibleMethod($repo, 'getConnection');
        $connection
            ->expects(self::once())
            ->method('doInsert')
            ->with(
                $this->callback(
                    function ($tablename) {
                        return 'tx_mklog_devlog_entry' === $tablename;
                    }
                ),
                $this->callback(
                    function ($data) use ($that, $msg, $extKey, $severity) {
                        $that->assertSame(
                            \DMK\Mklog\Factory::getConfigUtility()->getCurrentRunId(),
                            $data['run_id']
                        );

                        $that->assertGreaterThan(time() - 60, $data['crdate']);
                        $that->assertSame(0, $data['pid']);
                        $that->assertSame($msg, $data['message']);
                        $that->assertSame($extKey, $data['ext_key']);
                        $that->assertSame($severity, $data['severity']);
                        // how to check? on cli it is 0, on be runs the current user id!
                        $that->assertArrayHasKey('cruser_id', $data);
                        $that->assertArrayHasKey('extra_data', $data);
                        $that->assertTrue(is_string($data['extra_data']));
                        $logData = json_decode($data['extra_data'], true);
                        $that->assertSame(1, $logData['foo']);
                        $that->assertSame(['baz'], $logData['bar']);
                        $that->assertArrayHasKey('__feuser', $logData);
                        $that->assertArrayHasKey('__beuser', $logData);
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
     * @return PHPUnit_Framework_MockObject_MockObject|DMK\Mklog\Logger\DevlogLogger
     */
    protected function getDevlogLoggerMock(
        array $methods = []
    ) {
        $logger = $this->getMock(
            'DMK\\Mklog\\Logger\\DevlogLogger',
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
