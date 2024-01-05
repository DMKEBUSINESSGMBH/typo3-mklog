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

namespace DMK\Mklog\WatchDog;

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

use DMK\Mklog\Domain\Model\DevlogEntryDemand;
use DMK\Mklog\Domain\Model\GenericArrayObject;

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
     *
     * @test
     */
    public function testExecuteWithoutMessages()
    {
        $transport = $this->getMock(
            Transport\InterfaceTransport::class
        );

        $transport
            ->expects(self::once())
            ->method('initialize')
            ->with(self::isInstanceOf(GenericArrayObject::class));
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
     *
     * @test
     */
    public function testExecuteWithMessages()
    {
        self::markTestIncomplete();
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testGetDemandRespects()
    {
        $task = $this->getSchedulerMock(['getTransportId']);

        $task
            ->expects(self::once())
            ->method('getTransportId')
            ->will(self::returnValue('identifier:uid'));

        $task->getOptions()->setSeverity(5);
        $task->getOptions()->setMessageLimit(10);
        $task->getOptions()->setExtensionWhitelist('mklog');
        $task->getOptions()->setExtensionBlacklist('rn_base');
        /* @var DevlogEntryDemand $demand */
        $demand = $this->callInaccessibleMethod($task, 'getDevlogEntryDemand');

        $this->assertSame('identifier:uid', $demand->getTransportId());
        $this->assertSame(5, $demand->getSeverity());
        $this->assertSame(['mklog'], $demand->getExtensionWhitelist());
        $this->assertSame(['rn_base'], $demand->getExtensionBlacklist());
        $this->assertSame(10, $demand->getMaxResults());
        $this->assertSame('crdate', $demand->getOrderByField());
        $this->assertSame('ASC', $demand->getOrderByDirection());
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
            \TYPO3\CMS\Core\Log\Logger::class,
            [],
            [],
            '',
            false,
            false
        );
        $task = $this->getMock(
            SchedulerWatchDog::class,
            array_merge(
                ['getDevlogEntryRepository', 'getLogger'],
                $methods
            ),
            [],
            '',
            false,
            false
        );

        $task
            ->expects(self::any())
            ->method('getDevlogEntryRepository')
            ->will(self::returnValue($this->getDevlogEntryRepository()));

        $task
            ->expects(self::any())
            ->method('getLogger')
            ->will(self::returnValue($logger));

        return $task;
    }
}
