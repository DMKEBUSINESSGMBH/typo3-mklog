<?php

namespace DMK\Mklog\WatchDog;

use DMK\Mklog\Domain\Repository\DevlogEntryRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Scheduler WatchDog test.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SchedulerWatchDogTest extends \DMK\Mklog\Tests\BaseTestCase
{
    /**
     * Test the execute method.
     *
     * @group unit
     * @test
     */
    public function testExecuteWithoutMessages()
    {
        $transport = $this->getMock(
            'DMK\Mklog\WatchDog\Transport\InterfaceTransport'
        );

        $transport
            ->expects(self::once())
            ->method('initialize')
            ->with(self::isInstanceOf('Tx_Rnbase_Domain_Model_Data'));
        $transport
            ->expects(self::never())
            ->method('publish');
        $transport
            ->expects(self::once())
            ->method('shutdown');

        $task = $this->getSchedulerMock(['findMessages', 'getTransport']);

        $task
            ->expects(self::once())
            ->method('findMessages')
            ->will(self::returnValue([]));
        $task
            ->expects(self::any())
            ->method('getTransport')
            ->will(self::returnValue($transport));

        $task->execute();
    }

    /**
     * Test the execute method.
     *
     * @group unit
     * @test
     */
    public function testExecuteWithMessages()
    {
        self::markTestIncomplete();
    }

    /**
     * @group unit
     * @test
     */
    public function testFindMessagesRespectsWhitelistAndBlacklist()
    {
        $expectedFields = [
            'CUSTOM' => 'NOT FIND_IN_SET(\'\', `transport_ids`)',
            'DEVLOGENTRY.ext_key' => [
                'IN STR' => 'mklog',
                'NOTIN STR' => 'rn_base',
            ],
        ];
        $expectedOptions = [
            'limit' => 100,
            'orderby' => ['DEVLOGENTRY.crdate' => 'ASC'],
        ];
        $repository = $this->getAccessibleMock(DevlogEntryRepository::class, ['search']);
        $repository->expects(self::once())
            ->method('search')
            ->with($expectedFields, $expectedOptions);
        GeneralUtility::setSingletonInstance(DevlogEntryRepository::class, $repository);

        $task = $this->getSchedulerMock(['getTransportId']);
        $task->getOptions()->setExtensionWhitelist('mklog');
        $task->getOptions()->setExtensionBlacklist('rn_base');
        $this->callInaccessibleMethod($task, 'findMessages');
    }

    /**
     * Returns the logger mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|\DMK\Mklog\WatchDog\SchedulerWatchDog
     */
    protected function getSchedulerMock(
        array $methods = []
    ) {
        $logger = $this->getMock(
            'DMK\\Mklog\\WatchDog\\SchedulerWatchDog',
            array_merge(
                ['getDevlogEntryRepository'],
                $methods
            ),
            [],
            '',
            false,
            false
        );

        $logger
            ->expects(self::any())
            ->method('getDevlogEntryRepository')
            ->will(self::returnValue($this->getDevlogEntryRepository()));

        return $logger;
    }
}
