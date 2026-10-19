<?php

declare(strict_types=1);

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

namespace DMK\Mklog\Hooks;

use DMK\Mklog\Domain\Model\DevlogEntry;

/**
 * Tx_Mklog_Hooks_DataHandler.
 *
 * @author          Hannes Bochmann
 * @author          Michael Wagner
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class DataHandler
{
    protected function getTableName(): string
    {
        return DevlogEntry::TABLENAME;
    }

    /**
     * Wenn eine Seite gelöscht werden soll und darauf devlog Einträge liegen, dann können das nicht Admins
     * nur wenn sie Schreibrechte auf die devlog Tabelle haben. Dadurch werden beim kopieren von Seiten mit devlog
     * Einträgen diese Einträge auch mitkopiert. Das führt dann zu unerwarteten WatchDog Meldungen, da diese neu
     * eingefügt werden aber eigentlich alt sind. Daher sollte kein Redakteur Zugriff auf die devlog Tabelle haben.
     * Stattdessen löschen wir die Einträge auf einer Seite einfach selbst, womit die fehlenden Rechte auf devlog
     * Tabellen nicht stört.
     *
     * Wir könnten theoretisch auch den processCmdmap_deleteAction Hook nutzen. Dort ist aber schon
     * einer der version Extension registriert, welche die Berechtigungen selbst prüft. Daher würde die Meldungen dass
     * die Rechte fehlen schon ausgegeben bevor wir löschen. Der devlog Eintrag ist dann zwar weg, die Seite
     * aber noch nicht weil eben festgestellt wurde dass devlog Einträge drauf liegen. Daher hooken wir uns einen
     * Schritt eher ein.
     *
     * @param string                                   $command
     * @param string                                   $table
     * @param array                                    $value
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processCmdmap_preProcess($command, $table, int $pageId, $value, $dataHandler): void
    {
        if ('pages' === $table && 'delete' === $command) {
            $this->deleteLogEntriesByPageId($pageId);
        }
    }

    /**
     * Löscht Logeinträge, die beim Kopieren einer Seite mitgenommen wurden.
     * In TYPO3 14 gibt es keine Möglichkeit mehr, Tabellen vor dem Kopieren aus der Kopierliste
     * zu entfernen (DataHandler::$copyWhichTables wurde entfernt). Daher werden die Einträge
     * auf der neu kopierten Seite im Post-Process-Hook wieder gelöscht.
     *
     * @param string                                   $command
     * @param string                                   $table
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processCmdmap_postProcess($command, $table, int $pageId, $value, $dataHandler): void
    {
        if ('pages' === $table && 'copy' === $command) {
            $newPageId = $dataHandler->copyMappingArray['pages'][$pageId] ?? null;
            if (null !== $newPageId) {
                $this->deleteLogEntriesByPageId((int) $newPageId);
            }
        }
    }

    protected function deleteLogEntriesByPageId(int $pageId): void
    {
        \DMK\Mklog\Factory::getDevlogEntryRepository()->deletyByPid($pageId);
    }
}
