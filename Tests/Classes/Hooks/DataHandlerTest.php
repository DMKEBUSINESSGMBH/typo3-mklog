<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */
tx_rnbase::load('Tx_Mklog_Hooks_DataHandler');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');

/**
 * Tx_Mklib_Database_ConnectionTest
 *
 * @package 		TYPO3
 * @subpackage	 	mklog
 * @author 			Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklog_Hooks_DataHandlerTest extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @group unit
	 */
	public function testGetDatabaseConnection(){
		self::assertInstanceOf(
			'Tx_Rnbase_Database_Connection',
			$this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Mklog_Hooks_DataHandler'), 'getDatabaseConnection')
		);
	}

	/**
	 * @group unit
	 */
	public function testGetDevLogTableName(){
		self::assertNotNull(
			$this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Mklog_Hooks_DataHandler'), 'getDevLogTableName')
		);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdNotWhenCommandDeleteButTableNotPages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId'));

		$dataHandler->expects(self::never())
			->method('deleteDevlogEntriesByPageId');

		$dataHandler->processCmdmap_preProcess('delete', 'pages_not', 123, array(), NULL);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdNotWhenCommandNotDeleteButTablePages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId'));

		$dataHandler->expects(self::never())
			->method('deleteDevlogEntriesByPageId');

		$dataHandler->processCmdmap_preProcess('not_delete', 'pages', 123, array(), NULL);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdNotWhenCommandNotDeleteAndTableNotPages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId'));

		$dataHandler->expects(self::never())
			->method('deleteDevlogEntriesByPageId');

		$dataHandler->processCmdmap_preProcess('not_delete', 'pages_not', 123, array(), NULL);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdWhenCommandDeleteAndTablePages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId'));

		$dataHandler->expects(self::once())
			->method('deleteDevlogEntriesByPageId')
			->with(123);

		$dataHandler->processCmdmap_preProcess('delete', 'pages', 123, array(), NULL);
	}

	/**
	 * @group unit
	 */
	public function testDeleteDevlogEntriesByPageId() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('getDatabaseConnection', 'getDevLogTableName'));
		$databaseConnection = $this->getMock('Tx_Rnbase_Database_Connection', array('doDelete'));

		$databaseConnection->expects(self::once())
			->method('doDelete')
			->with('devlog_table', 'pid = 123');

		$dataHandler->expects(self::once())
			->method('getDatabaseConnection')
			->will(self::returnValue($databaseConnection));

		$dataHandler->expects(self::once())
			->method('getDevLogTableName')
			->will(self::returnValue('devlog_table'));

		$this->callInaccessibleMethod($dataHandler, 'deleteDevlogEntriesByPageId', 123);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCallsRemoveDevlogTableFromTablesThatCanBeCopiedNotWhenCommandCopyButTableNotPages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeDevlogTableFromTablesThatCanBeCopied'));

		$dataHandler->expects(self::never())
			->method('removeDevlogTableFromTablesThatCanBeCopied');

		$dataHandler->processCmdmap_preProcess('copy', 'pages_not', 123, array(), NULL);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCallsRemoveDevlogTableFromTablesThatCanBeCopiedNotWhenCommandNotCopyButTablePages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeDevlogTableFromTablesThatCanBeCopied'));

		$dataHandler->expects(self::never())
			->method('removeDevlogTableFromTablesThatCanBeCopied');

		$dataHandler->processCmdmap_preProcess('not_copy', 'pages', 123, array(), NULL);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCallsRemoveDevlogTableFromTablesThatCanBeCopiedNotWhenCommandNotCopyAndTableNotPages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeDevlogTableFromTablesThatCanBeCopied'));

		$dataHandler->expects(self::never())
			->method('removeDevlogTableFromTablesThatCanBeCopied');

		$dataHandler->processCmdmap_preProcess('not_copy', 'pages_not', 123, array(), NULL);
	}

	/**
	 * @group unit
	 */
	public function testProcessCmdmapPreProcessCCallsRemoveDevlogTableFromTablesThatCanBeCopiedWhenCommandCopyAndTablePages() {
		$dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeDevlogTableFromTablesThatCanBeCopied'));

		$dataHandlerParent = 'test';
		$dataHandler->expects(self::once())
			->method('removeDevlogTableFromTablesThatCanBeCopied')
			->with($dataHandlerParent);


		$dataHandler->processCmdmap_preProcess('copy', 'pages', 123, array(), $dataHandlerParent);
	}

	/**
	 * @group unit
	 */
	public function testRemoveDevlogTableFromTablesThatCanBeCopied() {
		$dataHandler = tx_rnbase::makeInstance('Tx_Mklog_Hooks_DataHandler');

		$dataHandlerParent = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getDataHandlerClass());
		$dataHandlerParent = $this->getMock(
			tx_rnbase_util_Typo3Classes::getDataHandlerClass(),
			array('compileAdminTables')
		);
		$dataHandlerParent
			->expects(self::once())
			->method('compileAdminTables')
			->will(
				self::returnValue(
					array(
						'pages',
						'tt_content',
						Tx_Mklog_Utility_Devlog::getTableName(),
					)
				)
			);

		$this->callInaccessibleMethod($dataHandler, 'removeDevlogTableFromTablesThatCanBeCopied', $dataHandlerParent);

		self::assertNotSame('*', $dataHandlerParent->copyWhichTables, 'es sollte nicht per default auf * stehen');

		$copyWhichTables = array_flip(explode(',', $dataHandlerParent->copyWhichTables));
		self::assertTrue(is_array($copyWhichTables), 'das sollte ein array sein');

		self::assertGreaterThan(2, count($copyWhichTables), 'es sollte mehr als eine Tabelle enthalten sein');

		self::assertArrayNotHasKey(
			Tx_Mklog_Utility_Devlog::getTableName(),
			$copyWhichTables,
			'devlog Tabelle noch enthalten'
		);
	}
}
