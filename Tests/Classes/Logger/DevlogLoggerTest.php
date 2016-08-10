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
 * Devlog Logger test
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogLoggerTest
	extends \DMK\Mklog\Tests\BaseTestCase
{
	/**
	 * Test the getTableName method
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testIsLoggingEnabled()
	{
		self::markTestIncomplete();
	}
	/**
	 * Test the storeLog method
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testStoreLog()
	{
		$msg = 'msg';
		$extKey = 'mklog';
		$severity = 7;
		$extraData = array('foo' => 1, 'bar' => array('baz'));

		$logger = $this->getDevlogLoggerMock(array('isLoggingEnabled'));

		$logger
			->expects(self::any())
			->method('isLoggingEnabled')
			->will(self::returnValue(true))
		;

		$repo = $this->callInaccessibleMethod($logger, 'getDevlogEntryRepository');
		$connection = $this->callInaccessibleMethod($repo, 'getConnection');
		$connection
			->expects(self::once())
			->method('doInsert')
			->with(
				$this->callback(
					function($tablename) {
						return $tablename === 'tx_mklog_devlog_entry';
					}
				),
				$this->callback(
					function($data) use ($msg, $extKey, $severity, $extraData)
					{
						self::assertSame(
							\DMK\Mklog\Factory::getConfigUtility()->getCurrentRunId(),
							$data['run_id']
						);

						self::assertGreaterThan(time() - 60, $data['crdate']);
						self::assertSame(0, $data['pid']);
						self::assertSame($msg, $data['message']);
						self::assertSame($extKey, $data['ext_key']);
						self::assertSame($severity, $data['severity']);
						// how to check? on cli it is 0, on be runs the current user id!
						self::assertArrayHasKey('cruser_id', $data);
						self::assertSame(
							json_encode($extraData, JSON_FORCE_OBJECT),
							$data['extra_data']
						);

						return true;
					}
				)
			)
		;


		$this->callInaccessibleMethod(
			array($logger, 'storeLog'),
			array($msg, $extKey, $severity, $extraData)
		);

	}

	/**
	 * Returns the logger mock
	 *
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject|DMK\Mklog\Logger\DevlogLogger
	 */
	protected function getDevlogLoggerMock(
		array $methods = array()
	) {
		$logger = $this->getMock(
			'DMK\\Mklog\\Logger\\DevlogLogger',
			array_merge(
				array('getDevlogEntryRepository'),
				$methods
			)
		);

		$logger
			->expects(self::any())
			->method('getDevlogEntryRepository')
			->will(self::returnValue($this->getDevlogEntryRepository()))
		;

		return $logger;

	}
}
