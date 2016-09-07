<?php
namespace DMK\Mklog\Domain\Model;

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
 * Devlog entry model test
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryModelTest
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
	public function testGetTableName()
	{
		self::assertSame(
			'tx_mklog_devlog_entry',
			$this->getDevlogEntryModel()->getTableName()
		);
	}

	/**
	 * Test the getTransportIds method
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetAndAddTransportIds()
	{
		$model = $this->getDevlogEntryModel();

		self::assertTrue(is_array($model->getTransportIds()));
		self::assertEmpty($model->getTransportIds());

		$model->addTransportId('mkLogGelf:5');
		$model->addTransportId('mkLogMail:7');

		self::assertTrue(is_array($model->getTransportIds()));
		self::assertCount(2, $model->getTransportIds());

		$ids = $model->getTransportIds();
		self::assertSame('mkLogGelf:5', $ids[0]);
		self::assertSame('mkLogMail:7', $ids[1]);
	}

	/**
	 * Test the getFullMessage method
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetFullMessage()
	{
		$model = $this->getDevlogEntryModel();
		$model->setExtraData(
			array(
				'foo' => 'bar',
				'__beuser' => 13,
			)
		);

		$data = $model->getFullMessage();

		self::assertTrue(is_string($data));
		self::assertEquals('{"foo":"bar"}', $data);
	}

	/**
	 * Test the getAdditionalData method
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetAdditionalData()
	{
		$model = $this->getDevlogEntryModel();
		$model->setExtraData(
			array(
				'foo' => 'bar',
				'__feuser' => 40,
				'__beuser' => 13,
			)
		);

		$data = $model->getAdditionalData();

		self::assertTrue(is_array($data));
		self::assertCount(2, $data);
		self::assertArrayHasKey('feuser', $data);
		self::assertEquals(40, $data['feuser']);
		self::assertArrayHasKey('beuser', $data);
		self::assertEquals(13, $data['beuser']);
	}
}
