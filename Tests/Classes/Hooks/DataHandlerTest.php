<?php
/**
 * @author Michael Wagner
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
 * Tx_Mklib_Database_ConnectionTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklog_Hooks_DataHandlerTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetDatabaseConnection()
    {
        self::assertInstanceOf(
            'Tx_Rnbase_Database_Connection',
            $this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Mklog_Hooks_DataHandler'), 'getDatabaseConnection')
        );
    }

    /**
     * @group unit
     */
    public function testGetDevLogTableName()
    {
        self::assertNotNull(
            $this->callInaccessibleMethod(tx_rnbase::makeInstance('Tx_Mklog_Hooks_DataHandler'), 'getDevLogTableName')
        );
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdNotWhenCommandDeleteButTableNotPages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::never())
            ->method('deleteDevlogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('delete', 'pages_not', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdNotWhenCommandNotDeleteButTablePages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::never())
            ->method('deleteDevlogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdNotWhenCommandNotDeleteAndTableNotPages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::never())
            ->method('deleteDevlogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages_not', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteDevlogEntriesByPageIdWhenCommandDeleteAndTablePages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::once())
            ->method('deleteDevlogEntriesByPageId')
            ->with(123);

        $dataHandler->processCmdmap_preProcess('delete', 'pages', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testDeleteDevlogEntriesByPageId()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('devlog')) {
            self::markTestSkipped('devlog muss installiert sein');
        }

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
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedNotWhenCommandCopyButTableNotPages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeLogTablesFromTablesThatCanBeCopied'));

        $dataHandler->expects(self::never())
            ->method('removeLogTablesFromTablesThatCanBeCopied');

        $dataHandler->processCmdmap_preProcess('copy', 'pages_not', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedNotWhenCommandNotCopyButTablePages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeLogTablesFromTablesThatCanBeCopied'));

        $dataHandler->expects(self::never())
            ->method('removeLogTablesFromTablesThatCanBeCopied');

        $dataHandler->processCmdmap_preProcess('not_copy', 'pages', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedNotWhenCommandNotCopyAndTableNotPages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeLogTablesFromTablesThatCanBeCopied'));

        $dataHandler->expects(self::never())
            ->method('removeLogTablesFromTablesThatCanBeCopied');

        $dataHandler->processCmdmap_preProcess('not_copy', 'pages_not', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedWhenCommandCopyAndTablePages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('removeLogTablesFromTablesThatCanBeCopied'));

        $dataHandlerParent = 'test';
        $dataHandler->expects(self::once())
            ->method('removeLogTablesFromTablesThatCanBeCopied')
            ->with($dataHandlerParent);

        $dataHandler->processCmdmap_preProcess('copy', 'pages', 123, array(), $dataHandlerParent);
    }

    /**
     * @group unit
     */
    public function testRemoveLogTablesFromTablesThatCanBeCopied()
    {
        $mklogTable = 'tx_mklog_devlog_entry';
        $devLogTable = Tx_Mklog_Utility_Devlog::getTableName();

        $dataHandler = tx_rnbase::makeInstance('Tx_Mklog_Hooks_DataHandler');

        $dataHandlerParent = $this->getMock(
            tx_rnbase_util_Typo3Classes::getDataHandlerClass(),
            array('compileAdminTables'),
            array(),
            '',
            false
        );
        $dataHandlerParent
            ->expects(self::once())
            ->method('compileAdminTables')
            ->will(
                self::returnValue(
                    array(
                        'pages',
                        'tt_content',
                        'sys_template',
                        $devLogTable,
                        $mklogTable,
                    )
                )
            );

        $this->callInaccessibleMethod($dataHandler, 'removeLogTablesFromTablesThatCanBeCopied', $dataHandlerParent);

        self::assertNotSame('*', $dataHandlerParent->copyWhichTables, 'es sollte nicht per default auf * stehen');

        $copyWhichTables = array_flip(explode(',', $dataHandlerParent->copyWhichTables));
        self::assertTrue(is_array($copyWhichTables), 'das sollte ein array sein');

        self::assertGreaterThan(2, count($copyWhichTables), 'es sollte mehr als eine Tabelle enthalten sein');

        self::assertArrayNotHasKey(
            $devLogTable,
            $copyWhichTables,
            'devlog Tabelle noch enthalten'
        );
        self::assertArrayNotHasKey(
            $mklogTable,
            $copyWhichTables,
            'mklog Tabelle noch enthalten'
        );
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteMklogEntriesByPageIdNotWhenCommandDeleteButTableNotPages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::never())
            ->method('deleteMklogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('delete', 'pages_not', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteMklogEntriesByPageIdNotWhenCommandNotDeleteButTablePages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::never())
        ->method('deleteMklogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteMklogEntriesByPageIdNotWhenCommandNotDeleteAndTableNotPages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::never())
        ->method('deleteMklogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages_not', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsDeleteMklogEntriesByPageIdWhenCommandDeleteAndTablePages()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('deleteDevlogEntriesByPageId', 'deleteMklogEntriesByPageId'));

        $dataHandler->expects(self::once())
        ->method('deleteMklogEntriesByPageId')
        ->with(123);

        $dataHandler->processCmdmap_preProcess('delete', 'pages', 123, array(), null);
    }

    /**
     * @group unit
     */
    public function testDeleteMklogEntriesByPageId()
    {
        $dataHandler = $this->getMock('Tx_Mklog_Hooks_DataHandler', array('getDatabaseConnection'));
        $databaseConnection = $this->getMock('Tx_Rnbase_Database_Connection', array('doDelete'));

        $databaseConnection->expects(self::once())
            ->method('doDelete')
            ->with('tx_mklog_devlog_entry', 'pid = 123');

        $dataHandler->expects(self::once())
            ->method('getDatabaseConnection')
            ->will(self::returnValue($databaseConnection));

        $this->callInaccessibleMethod($dataHandler, 'deleteMklogEntriesByPageId', 123);
    }
}
