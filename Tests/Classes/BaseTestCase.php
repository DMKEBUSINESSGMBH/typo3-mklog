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

require_once \t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
\tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * Basis Testcase
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class BaseTestCase
	extends \tx_rnbase_tests_BaseTestCase
{
	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklog']['nolog'] = true;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'] = true;

		$extConf = \DMK\Mklog\Factory::getConfigUtility()->getExtConf();
		$extConf->setMinLogLevel(7);
		$extConf->setExcludeExtKeys('');
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		// reset extconf cache
		\DMK\Mklog\Factory::getConfigUtility()->getExtConf()->setProperty(array());
	}

	/**
	 * Returns the database connection
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
	 * Returns a devlog entry model mock
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject|\DMK\Mklog\Domain\Model\DevlogEntryModel
	 */
	protected function getDevlogEntryModel()
	{
		return $this->getModel(
			array(
				'uid' => 5,
				'pid' => 7,
			),
			'DMK\\Mklog\\Domain\\Model\\DevlogEntryModel'
		);
	}

	/**
	 * Creates the repo mock
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|DMK\Mklog\Domain\Repository\DevlogEntryRepository
	 */
	protected function getDevlogEntryRepository()
	{
		\tx_rnbase::load('tx_rnbase_util_SearchGeneric');
		$searcher = $this->getMock(
			'tx_rnbase_util_SearchGeneric',
			array('search')
		);


		\tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');
		\tx_rnbase::load('DMK\\Mklog\\Domain\\Repository\\DevlogEntryRepository');
		$repo = $this->getMock(
			'DMK\\Mklog\\Domain\\Repository\\DevlogEntryRepository',
			array('getSearcher', 'getConnection')
		);

		$repo
			->expects(self::any())
			->method('getSearcher')
			->will(self::returnValue($searcher))
		;
		$repo
			->expects(self::any())
			->method('getConnection')
			->will(self::returnValue($this->getDatabaseConnection()))
		;

		return $repo;
	}
}
