<?php

namespace DMK\Mklog\Tests;

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

\tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
\tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');

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
        $extConf = \DMK\Mklog\Factory::getConfigUtility()->getExtConf();
        $extConf->setMinLogLevel(7);
        $extConf->setExcludeExtKeys('');
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        // reset extconf cache
        \DMK\Mklog\Factory::getConfigUtility()->getExtConf()->setProperty([]);
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\DMK\Mklog\Domain\Model\DevlogEntryModel
     */
    protected function getDevlogEntryModel()
    {
        return $this->getModel(
            [
                'uid' => 5,
                'pid' => 7,
            ],
            'DMK\\Mklog\\Domain\\Model\\DevlogEntryModel'
        );
    }

    /**
     * Creates the repo mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|DMK\Mklog\Domain\Repository\DevlogEntryRepository
     */
    protected function getDevlogEntryRepository()
    {
        \tx_rnbase::load('tx_rnbase_util_SearchGeneric');
        $searcher = $this->getMock(
            'tx_rnbase_util_SearchGeneric',
            ['search']
        );

        \tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');
        \tx_rnbase::load('DMK\\Mklog\\Domain\\Repository\\DevlogEntryRepository');
        $repo = $this->getMock(
            'DMK\\Mklog\\Domain\\Repository\\DevlogEntryRepository',
            ['getSearcher', 'getConnection', 'getEmptyModel']
        );

        $repo
            ->expects(self::any())
            ->method('getEmptyModel')
            ->will(self::returnValue($this->getModel(null, 'DMK\\Mklog\\Domain\\Model\\DevlogEntryModel')));
        $repo
            ->expects(self::any())
            ->method('getSearcher')
            ->will(self::returnValue($searcher));
        $repo
            ->expects(self::any())
            ->method('getConnection')
            ->will(self::returnValue($this->getDatabaseConnection()));

        return $repo;
    }
}
