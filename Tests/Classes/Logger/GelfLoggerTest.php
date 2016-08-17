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

require_once \tx_rnbase_util_Extensions::extPath('rn_base', 'class.tx_rnbase.php');
require_once \tx_rnbase_util_Extensions::extPath('mklog', 'Tests/Classes/BaseTestCase.php');

/**
 * Gelf Logger test
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class GelfLoggerTest
	extends \DMK\Mklog\Tests\BaseTestCase
{
	/**
	 * Test the writeLog method
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testWriteLog()
	{
		$logger = $this->getGelfLoggerMock();

		self::markTestIncomplete();
	}

	/**
	 * Returns the logger mock
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|\DMK\Mklog\Logger\GelfLogger
	 */
	protected function getGelfLoggerMock()
	{
		$logger = $this->getMock(
			'DMK\\Mklog\\Logger\\GelfLogger',
			array('getDevlogEntryRepository')
		);

		$logger
			->expects(self::any())
			->method('getDevlogEntryRepository')
			->will(self::returnValue($this->getDevlogEntryRepository()))
		;

		return $logger;

	}
}
