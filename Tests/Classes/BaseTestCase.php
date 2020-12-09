<?php

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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Basis Testcase.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class BaseTestCase extends \tx_rnbase_tests_BaseTestCase
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
    protected function setUp()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklog']['nolog'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = true;

        \DMK\Mklog\Factory::getStorage()->unsLoggingActive();
        $configStorage = $this->callInaccessibleMethod([\DMK\Mklog\Factory::getConfigUtility(), 'getStorage'], []);
        // just call to create the initial config
        $this->callInaccessibleMethod([\DMK\Mklog\Factory::getConfigUtility(), 'getExtConf'], ['enable_devlog']);
        $configStorage->setExtConf(
            array_merge(
                $configStorage->getExtConf(),
                [
                    'min_log_level' => 7,
                    'exclude_ext_keys' => '',
                    'from_mail' => 'John Dohe<john@dohe.org>',
                ]
            )
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        // reset extconf cache
        $configStorage = $this->callInaccessibleMethod([\DMK\Mklog\Factory::getConfigUtility(), 'getStorage'], []);
        $configStorage->unsExtConf();
    }

    /**
     * Returns the database connection.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Tx_Rnbase_Database_Connection
     */
    protected function getDatabaseConnection()
    {
        \tx_rnbase::load('Tx_Rnbase_Database_Connection');

        return $this->getMock(
            'Tx_Rnbase_Database_Connection',
            get_class_methods('Tx_Rnbase_Database_Connection')
        );
    }

    /**
     * Returns a devlog entry model mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\DMK\Mklog\Domain\Model\DevlogEntry
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
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor();
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor();

        $repo = $this->getMock(
            'DMK\\Mklog\\Domain\\Repository\\DevlogEntryRepository',
            ['countAll', 'persist', 'createQueryBuilder', 'getConnection', 'createNewModel']
        );

        $repo
            ->expects(self::any())
            ->method('createNewModel')
            ->will(self::returnValue(Factory::makeInstance(DevlogEntry::class)));
        $repo
            ->expects(self::any())
            ->method('createQueryBuilder')
            ->will(self::returnValue($queryBuilder->getMock()));
        $repo
            ->expects(self::any())
            ->method('getConnection')
            ->will(self::returnValue($connection->getMock()));

        return $repo;
    }
}
