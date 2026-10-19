<?php

/*
 * Copyright notice
 *
 * (c) 2011-2026 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mklog\Domain\Repository\DevlogEntryRepository;
use DMK\Mklog\Factory;
use DMK\Mklog\Hooks\DataHandler;
use DMK\Mklog\Tests\BaseTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    public function testProcessCmdmapPostProcessDeletesLogEntriesOfCopiedPage(): void
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);
        $dataHandler->expects(self::once())
            ->method('deleteLogEntriesByPageId')
            ->with(456);

        $dataHandler->processCmdmap_postProcess('copy', 'pages', 123, 1, $this->getCoreDataHandler([123 => 456]));
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPostProcessDeletesNothingWhenCopiedPageIsUnknown(): void
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);
        $dataHandler->expects(self::never())
            ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_postProcess('copy', 'pages', 123, 1, $this->getCoreDataHandler([]));
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPostProcessDeletesNothingWhenCommandNotCopy(): void
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);
        $dataHandler->expects(self::never())
            ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_postProcess('move', 'pages', 123, 1, $this->getCoreDataHandler([123 => 456]));
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPostProcessDeletesNothingWhenTableNotPages(): void
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);
        $dataHandler->expects(self::never())
            ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_postProcess('copy', 'tt_content', 123, 1, $this->getCoreDataHandler([123 => 456]));
    }

    private function getCoreDataHandler(array $pageMapping): \TYPO3\CMS\Core\DataHandling\DataHandler
    {
        $dataHandler = self::createStub(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        $dataHandler->copyMappingArray = ['pages' => $pageMapping];

        return $dataHandler;
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdNotWhenCommandDeleteButTableNotPages(): void
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::never())
            ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('delete', 'pages_not', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdNotWhenCommandNotDeleteButTablePages(): void
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::never())
        ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdNotWhenCommandNotDeleteAndTableNotPages(): void
    {
        $dataHandler = $this->getMock(DataHandler::class, ['deleteLogEntriesByPageId']);

        $dataHandler->expects(self::never())
        ->method('deleteLogEntriesByPageId');

        $dataHandler->processCmdmap_preProcess('not_delete', 'pages_not', 123, [], null);
    }

    /**
     * @group unit
     */
    public function testProcessCmdmapPreProcessCallsdeleteLogEntriesByPageIdWhenCommandDeleteAndTablePages(): void
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
    public function testDeleteLogEntriesByPageId(): void
    {
        $repository = $this->getMock(DevlogEntryRepository::class, ['deletyByPid']);
        $repository->expects(self::once())
            ->method('deletyByPid')
            ->with(123);
        GeneralUtility::addInstance(DevlogEntryRepository::class, $repository);

        $this->callInaccessibleMethod(Factory::makeInstance(DataHandler::class), 'deleteLogEntriesByPageId', 123);
    }
}
