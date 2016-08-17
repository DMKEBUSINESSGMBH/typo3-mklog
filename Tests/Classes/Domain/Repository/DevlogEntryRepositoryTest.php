<?php
namespace DMK\Mklog\Domain\Repository;

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
 * Devlog entry repository test
 *
 * @package TYPO3
 * @subpackage Tx_Hpsplaner
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryRepositoryTest
	extends \DMK\Mklog\Tests\BaseTestCase
{
	/**
	 * Test the getSearchClass method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetSearchClassShouldBeGeneric() {
		self::assertEquals(
			'tx_rnbase_util_SearchGeneric',
			$this->callInaccessibleMethod(
				$this->getDevlogEntryRepository(),
				'getSearchClass'
			)
		);
	}

	/**
	 * Test the getEmptyModel method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetEmptyModelShouldBeBaseModelWithRightTable() {
		$model = $this->callInaccessibleMethod(
			$this->getDevlogEntryRepository(),
			'getEmptyModel'
		);
		self::assertInstanceOf(
			'DMK\\Mklog\\Domain\\Model\\DevlogEntryModel',
			$model
		);
		self::assertSame(
			'tx_mklog_devlog_entry',
			$model->getTablename()
		);
	}

	/**
	 * Test the prepareGenericSearcher method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testPrepareGenericSearcherShouldBeTheRightSearchdefConfig()
	{
		$repo = $this->getDevlogEntryRepository();
		$searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

		$searcher
			->expects(self::once())
			->method('search')
			->with(
				$this->callback(
					function($fields)
					{
						return is_array($fields) && empty($fields);
					}
				),
				$this->callback(
					function($options) use ($repo)
					{
						$tablename = $repo->getEmptyModel()->getTableName();
						self::assertTrue(is_array($options));

						self::assertArrayHasKey('searchdef', $options);
						self::assertTrue(is_array($options['searchdef']));

						$sd = $options['searchdef'];
						self::assertArrayHasKey('usealias', $sd);
						self::assertSame($sd['usealias'], 1);
						self::assertArrayHasKey('basetable', $sd);
						self::assertSame($sd['basetable'], $tablename);
						self::assertArrayHasKey('basetablealias', $sd);
						self::assertSame($sd['basetablealias'], 'DEVLOGENTRY');
						self::assertArrayHasKey('wrapperclass', $sd);
						self::assertSame($sd['wrapperclass'], get_class($repo->getEmptyModel()));

						self::assertArrayHasKey('alias', $sd);
						self::assertTrue(is_array($sd['alias']));
						self::assertArrayHasKey('DEVLOGENTRY', $sd['alias']);
						self::assertTrue(is_array($sd['alias']['DEVLOGENTRY']));
						self::assertArrayHasKey('table', $sd['alias']['DEVLOGENTRY']);
						self::assertSame($sd['alias']['DEVLOGENTRY']['table'], $tablename);

						return true;
					}
				)
			)
			->will(self::returnValue(new \ArrayObject()))
		;

		self::assertInstanceOf('ArrayObject', $repo->findAll());
	}

	/**
	 * Test the prepareGenericSearcher method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testPrepareGenericSearcherShouldUseCollection()
	{
		$repo = $this->getDevlogEntryRepository();
		$searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

		$searcher
			->expects(self::once())
			->method('search')
			->with(
				$this->callback(
					function($fields)
					{
						return is_array($fields);
					}
				),
				$this->callback(
					function($options)
					{
						self::assertTrue(is_array($options));

						self::assertArrayHasKey('collection', $options);
						self::assertEquals(
							'Tx_Rnbase_Domain_Collection_Base',
							$options['collection']
						);

						return true;
					}
				)
			)
			->will(self::returnValue(new \ArrayObject()))
		;

		self::assertInstanceOf('ArrayObject', $repo->findAll());
	}

	/**
	 * Test the getLatestRuns method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetLatestRuns()
	{
		$repo = $this->getDevlogEntryRepository();
		$searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

		$searcher
			->expects(self::once())
			->method('search')
			->with(
				$this->callback(
					function($fields)
					{
						return is_array($fields);
					}
				),
				$this->callback(
					function($options)
					{
						self::assertTrue(is_array($options));

						self::assertArrayHasKey('groupby', $options);
						self::assertEquals(
							'DEVLOGENTRY.run_id',
							$options['groupby']
						);

						self::assertArrayHasKey('orderby', $options);
						self::assertCount(1, $options['orderby']);
						self::assertEquals(
							'DESC',
							$options['orderby']['DEVLOGENTRY.run_id']
						);

						self::assertArrayHasKey('limit', $options);
						self::assertEquals(
							57,
							$options['limit']
						);

						self::assertArrayHasKey('forcewrapper', $options);
						self::assertEquals(
							1,
							$options['forcewrapper']
						);

						return true;
					}
				)
			)
		;

		$repo->getLatestRuns(57);
	}

	/**
	 * Test the getLoggedExtensions method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetLoggedExtensions()
	{
		$repo = $this->getDevlogEntryRepository();
		$searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

		$searcher
			->expects(self::once())
			->method('search')
			->with(
				$this->callback(
					function($fields)
					{
						return is_array($fields);
					}
				),
				$this->callback(
					function($options)
					{
						self::assertTrue(is_array($options));

						self::assertArrayHasKey('groupby', $options);
						self::assertEquals(
							'DEVLOGENTRY.ext_key',
							$options['groupby']
						);

						self::assertArrayHasKey('orderby', $options);
						self::assertCount(1, $options['orderby']);
						self::assertEquals(
							'DESC',
							$options['orderby']['DEVLOGENTRY.ext_key']
						);

						self::assertArrayHasKey('forcewrapper', $options);
						self::assertEquals(
							1,
							$options['forcewrapper']
						);

						return true;
					}
				)
			)
		;

		$repo->getLoggedExtensions(57);
	}

	/**
	 * Test the isTableAvailable method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testIsTableAvailable()
	{
		$repo = $this->getDevlogEntryRepository();

		$db = $this->getMock(
			'\TYPO3\CMS\Core\Database\DatabaseConnection',
			array('admin_get_fields')
		);
		$db
			->expects(self::once())
			->method('admin_get_fields')
			->with(self::equalTo($this->getDevlogEntryModel()->getTableName()))
			->will(self::returnValue(array()))
		;

		$connection = $this->callInaccessibleMethod($repo, 'getConnection');

		$connection
			->expects(self::once())
			->method('getDatabaseConnection')
			->will(self::returnValue($db))
		;

		self::assertFalse($repo->isTableAvailable());
	}

	/**
	 * Test the optimize method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testOptimize()
	{
		self::markTestIncomplete();
	}
}
