<?php

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
use DMK\Mklog\Utility\SeverityUtility;
use DMK\Mklog\WatchDog\Transport\Gelf\HttpGelf;
use DMK\Mklog\WatchDog\Transport\Gelf\UdpGelf;
use DMK\Mklog\WatchDog\Transport\MailTransport;
use PHPUnit\Framework\Attributes\DataProvider;

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
    public function testExecuteWithoutMessages(): void
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
            ->willReturn([]);
        $task
            ->method('getTransport')
            ->willReturn($transport);

        $task->execute();
    }

    /**
     * Test the execute method.
     *
     * @group unit
     *
     * @test
     */
    public function testExecuteWithMessages(): void
    {
        self::markTestIncomplete();
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testGetDemandRespects(): void
    {
        $task = $this->getSchedulerMock(['getTransportId']);

        $task
            ->expects(self::once())
            ->method('getTransportId')
            ->willReturn('identifier:uid');

        $task->getOptions()->setSeverity(5);
        $task->getOptions()->setMessageLimit(10);
        $task->getOptions()->setExtensionWhitelist('mklog');
        $task->getOptions()->setExtensionBlacklist('rn_base');
        /* @var DevlogEntryDemand $demand */
        $demand = $this->callInaccessibleMethod($task, 'getDevlogEntryDemand');

        self::assertSame('identifier:uid', $demand->getTransportId());
        self::assertSame(5, $demand->getSeverity());
        self::assertSame(['mklog'], $demand->getExtensionWhitelist());
        self::assertSame(['rn_base'], $demand->getExtensionBlacklist());
        self::assertSame(10, $demand->getMaxResults());
        self::assertSame('crdate', $demand->getOrderByField());
        self::assertSame('ASC', $demand->getOrderByDirection());
    }

    /**
     * @group unit
     */
    public function testGetTaskParametersReturnsDefaultsForNewTask(): void
    {
        self::assertSame(
            [
                'tx_mklog_watchdog_transport' => '',
                'tx_mklog_watchdog_credentials' => '',
                'tx_mklog_watchdog_severity' => SeverityUtility::DEBUG,
                'tx_mklog_watchdog_message_limit' => 100,
                'tx_mklog_watchdog_extension_whitelist' => '',
                'tx_mklog_watchdog_extension_blacklist' => '',
                'tx_mklog_watchdog_mail_subject' => '',
            ],
            $this->createTask()->getTaskParameters()
        );
    }

    /**
     * @group unit
     */
    public function testSetTaskParametersFromNativeFields(): void
    {
        $task = $this->createTask();
        $task->setTaskParameters([
            'tx_mklog_watchdog_transport' => MailTransport::class,
            'tx_mklog_watchdog_credentials' => 'john@dohe.org',
            // database values are strings
            'tx_mklog_watchdog_severity' => '3',
            'tx_mklog_watchdog_message_limit' => '25',
            'tx_mklog_watchdog_extension_whitelist' => 'mklog',
            'tx_mklog_watchdog_extension_blacklist' => 'core',
            'tx_mklog_watchdog_mail_subject' => 'Errors on %s',
        ]);

        $options = $task->getOptions();
        self::assertSame(MailTransport::class, $options->getTransport());
        self::assertSame('john@dohe.org', $options->getCredentials());
        self::assertSame(3, $options->getSeverity());
        self::assertSame(25, $options->getMessageLimit());
        self::assertSame('mklog', $options->getExtensionWhitelist());
        self::assertSame('core', $options->getExtensionBlacklist());
        self::assertSame('Errors on %s', $options->getMailSubject());
    }

    /**
     * Tasks created before the native task types are stored as serialized object.
     *
     * @group unit
     */
    public function testLegacySerializedTaskIsMappedToNativeFields(): void
    {
        $task = $this->createTask();
        $task->getOptions()
            ->setTransport(UdpGelf::class)
            ->setCredentials('127.0.0.1:12201')
            ->setSeverity(4)
            ->setMessageLimit(50);

        $migratedTask = unserialize(serialize($task));

        self::assertInstanceOf(SchedulerWatchDog::class, $migratedTask);
        $parameters = $migratedTask->getTaskParameters();
        self::assertSame(UdpGelf::class, $parameters['tx_mklog_watchdog_transport']);
        self::assertSame('127.0.0.1:12201', $parameters['tx_mklog_watchdog_credentials']);
        self::assertSame(4, $parameters['tx_mklog_watchdog_severity']);
        self::assertSame(50, $parameters['tx_mklog_watchdog_message_limit']);
    }

    /**
     * @group unit
     */
    public function testSetTaskParametersFromLegacyOptions(): void
    {
        $task = $this->createTask();
        $task->setTaskParameters([
            'schedulerOptions' => [
                'transport' => HttpGelf::class,
                'credentials' => 'https://graylog.local/gelf',
                'severity' => 2,
            ],
        ]);

        self::assertSame(HttpGelf::class, $task->getOptions()->getTransport());
        self::assertSame('https://graylog.local/gelf', $task->getOptions()->getCredentials());
        self::assertSame(2, $task->getOptions()->getSeverity());
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function validateTaskParametersDataProvider(): array
    {
        return [
            'mail address' => [MailTransport::class, 'john@dohe.org', true],
            'mail address with name' => [MailTransport::class, 'John Dohe<john@dohe.org>', true],
            'invalid mail address' => [MailTransport::class, 'john', false],
            'udp host and port' => [UdpGelf::class, '127.0.0.1:12201', true],
            'udp without port' => [UdpGelf::class, '127.0.0.1', false],
            'http url' => [HttpGelf::class, 'https://admin:admin@127.0.0.1:2345/gelf', true],
            'http without url' => [HttpGelf::class, '127.0.0.1:2345', false],
            'empty credentials' => [MailTransport::class, ' ', false],
        ];
    }

    /**
     * @group unit
     */
    #[DataProvider('validateTaskParametersDataProvider')]
    public function testValidateTaskParameters(string $transport, string $credentials, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->createTask()->validateTaskParameters([
                'tx_mklog_watchdog_transport' => $transport,
                'tx_mklog_watchdog_credentials' => $credentials,
            ])
        );
    }

    /**
     * Creates the task without constructor, as the TYPO3 13 constructor requires a complete scheduler setup.
     */
    private function createTask(): SchedulerWatchDog
    {
        return (new \ReflectionClass(SchedulerWatchDog::class))->newInstanceWithoutConstructor();
    }

    /**
     * Returns the logger mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|SchedulerWatchDog
     */
    protected function getSchedulerMock(
        array $methods = [],
    ) {
        $logger = self::createStub(\TYPO3\CMS\Core\Log\Logger::class);
        $task = $this->getMock(
            SchedulerWatchDog::class,
            array_merge(
                ['getLogger'],
                $methods
            ),
            [],
            '',
            false,
            false
        );

        $task
            ->method('getLogger')
            ->willReturn($logger);

        return $task;
    }
}
