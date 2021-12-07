<?php

/*
 * Copyright notice
 *
 * (c) 2011-2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\Logger;

/*
 *  Copyright notice.
 *
 *  (c) 2020 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mklog\Factory;
use DMK\Mklog\Hooks\DataHandler;
use DMK\Mklog\Tests\BaseTestCase;

/**
 * Tx_Mklib_Database_ConnectionTest.
 *
 * @author Hannes Bochmann
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DataHandlerTest extends BaseTestCase
{
    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedNotWhenCommandCopyButTableNotPages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['removeLogTablesFromTablesThatCanBeCopied']);

        $dataHandler->expects(self::never())
            ->method('removeLogTablesFromTablesThatCanBeCopied');

        $dataHandler->processCmdmap_preProcess('copy', 'pages_not', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedNotWhenCommandNotCopyButTablePages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['removeLogTablesFromTablesThatCanBeCopied']);

        $dataHandler->expects(self::never())
            ->method('removeLogTablesFromTablesThatCanBeCopied');

        $dataHandler->processCmdmap_preProcess('not_copy', 'pages', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedNotWhenCommandNotCopyAndTableNotPages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['removeLogTablesFromTablesThatCanBeCopied']);

        $dataHandler->expects(self::never())
            ->method('removeLogTablesFromTablesThatCanBeCopied');

        $dataHandler->processCmdmap_preProcess('not_copy', 'pages_not', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsRemoveLogTablesFromTablesThatCanBeCopiedWhenCommandCopyAndTablePages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['removeLogTablesFromTablesThatCanBeCopied']);

        $dataHandlerParent = 'test';
        $dataHandler->expects(self::once())
            ->method('removeLogTablesFromTablesThatCanBeCopied')
            ->with($dataHandlerParent);

        $dataHandler->processCmdmap_preProcess('copy', 'pages', 123, [], $dataHandlerParent);
    }

    /**
     * @group unit
     */
    public function testRemoveLogTablesFromTablesThatCanBeCopied()
    {
        $mklogTable = 'tx_mklog_devlog_entry';

        $dataHandler = Factory::makeInstance(DataHandler::class);

        $dataHandlerParent = $this->getMock(
            \TYPO3\CMS\Core\DataHandling\DataHandler::class,
            ['compileAdminTables'],
            [],
            '',
            false
        );
        $dataHandlerParent
            ->expects(self::once())
            ->method('compileAdminTables')
            ->will(
                self::returnValue(
                    [
                        'pages',
                        'tt_content',
                        'sys_template',
                        $mklogTable,
                    ]
                )
            );

        $this->callInaccessibleMethod($dataHandler, 'removeLogTablesFromTablesThatCanBeCopied', $dataHandlerParent);

        self::assertNotSame('*', $dataHandlerParent->copyWhichTables, 'es sollte nicht per default auf * stehen');

        $copyWhichTables = array_flip(explode(',', $dataHandlerParent->copyWhichTables));
        self::assertTrue(is_array($copyWhichTables), 'das sollte ein array sein');

        self::assertGreaterThan(2, count($copyWhichTables), 'es sollte mehr als eine Tabelle enthalten sein');

        self::assertArrayNotHasKey(
            $mklogTable,
            $copyWhichTables,
            'mklog Tabelle noch enthalten'
        );
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdNotWhenCommandDeleteButTableNotPages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::never())
            ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('delete', 'pages_not', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdNotWhenCommandNotDeleteButTablePages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::never())
        ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdNotWhenCommandNotDeleteAndTableNotPages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::never())
        ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages_not', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdWhenCommandDeleteAndTablePages()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::once())
        ->method('deleteLogEntriesByPageId')
        ->with(123);

        $dataHandler->processCmdmap_preProcess('delete', 'pages', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testdeleteLogEntriesByPageId()
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::once())
            ->method('deleteLogEntriesByPageId')
            ->with(123);

        $this->callInaccessibleMethod($dataHandler, 'deleteLogEntriesByPageId', 123);
    }
}
