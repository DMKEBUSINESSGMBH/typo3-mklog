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

namespace DMK\Mklog\Tests;

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
use DMK\Mklog\Factory;
use DMK\Mklog\Utility\RateLimiterUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Basis Testcase.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class BaseTestCase extends UnitTestCase
{
    /**
     * Property to store backups for set up and tear down.
     *
     * @var array
     */
    protected $backup = [];

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklog']['nolog'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = true;

        Factory::getStorage()->unsLoggingActive();
        $configStorage = $this->callInaccessibleMethod([Factory::getConfigUtility(), 'getStorage'], []);
        $configStorage->setExtConf(
            [
                'enable_devlog' => 1,
                'host' => '',
                'from_mail' => 'John Dohe<john@dohe.org>',
                'min_log_level' => 7,
                'exclude_ext_keys' => '',
                'max_logs' => 10000,
                'max_transport_extra_data_size' => 8388608,
                'gelf_enable' => 1,
                'gelf_transport' => '',
                'gelf_credentials' => '',
                'gelf_min_log_level' => 1,
                'excluded_log_message_regular_expressions' => ['/excluded-msg/'],
            ]
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        // reset extconf cache
        $configStorage = $this->callInaccessibleMethod([Factory::getConfigUtility(), 'getStorage'], []);
        $configStorage->unsExtConf();

        $instance = new \ReflectionProperty(RateLimiterUtility::class, 'instance');
        $instance->setValue(null, null);

        GeneralUtility::purgeInstances();
    }

    /**
     * Returns a devlog entry model mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|DevlogEntry
     */
    protected function getDevlogEntry()
    {
        /* @var DevlogEntry $entity */
        $entity = Factory::makeInstance(DevlogEntry::class);

        $entity->setUid(5);
        $entity->setPid(7);

        return $entity;
    }

    /**
     * Creates the repo mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|DMK\Mklog\Backend\Repository\DevlogEntryRepository
     */
    protected function getDevlogEntryRepository()
    {
        $repo = $this->getMock(
            \DMK\Mklog\Domain\Repository\DevlogEntryRepository::class,
            ['countAll', 'persist', 'createQueryBuilder', 'getConnection', 'createNewModel']
        );
        $repo
            ->method('createNewModel')
            ->willReturnCallback(static fn (): mixed => Factory::makeInstance(DevlogEntry::class));
        $repo
            ->method('createQueryBuilder')
            ->willReturn(self::createStub(QueryBuilder::class));
        $repo
            ->method('getConnection')
            ->willReturn(self::createStub(Connection::class));

        return $repo;
    }

    /**
     * Helper function to call protected methods.
     * This method is taken from TYPO3 BaseTestCase initialy.
     *
     * The classic way:
     *   ->callInaccessibleMethod($object, $methodname, $arg1, $arg2)
     *
     * The new way, with support for arguments as reference:
     *   ->callInaccessibleMethod(array($object, $methodname), array($arg1, $arg2))
     *
     * @param object|array $object The object to be invoked or an a array with object and $name
     * @param string|array $name   the name of the method to call or the arguments array
     */
    protected function callInaccessibleMethod($object, $name)
    {
        if (is_array($object)) {
            // the new way (supports arguments as references)
            // $object is a array (with object and name) and $name a arguments array!
            $arguments = $name;
            [$object, $name] = $object;
        } else {
            // the classic way to read the arguments
            // Remove first two arguments ($object and $name)
            // phpcs:disable -- $object and $name never changes
            $arguments = func_get_args();
            array_splice($arguments, 0, 2);
        }

        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethod = $reflectionObject->getMethod($name);

        return $reflectionMethod->invokeArgs($object, $arguments);
    }

    /**
     * Wrapper for the removed getMock method.
     *
     * Taken From nimut/testing-framework
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function getMock(
        string $originalClassName,
        array $methods = [],
        array $arguments = [],
        string $mockClassName = '',
        bool $callOriginalConstructor = true,
        bool $callOriginalClone = true,
    ) {
        $mockBuilder = $this->getMockBuilder($originalClassName)
            ->onlyMethods($methods)
            ->setConstructorArgs($arguments);
        if ('' !== $mockClassName) {
            $mockBuilder->setMockClassName($mockClassName);
        }

        if (!$callOriginalConstructor) {
            $mockBuilder->disableOriginalConstructor();
        }

        if (!$callOriginalClone) {
            $mockBuilder->disableOriginalClone();
        }

        return $mockBuilder->getMock();
    }
}
