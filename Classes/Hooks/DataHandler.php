<?php
/**
 *  Copyright notice.
 *
 *  (c) 2016 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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

/**
 * Tx_Mklog_Hooks_DataHandler.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklog_Hooks_DataHandler
{
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
     * @param string $command
     * @param string $table
     * @param int    $id
     * @param array  $value
     * @param TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function processCmdmap_preProcess($command, $table, $id, $value, $dataHandler)
    {
        if ('pages' == $table) {
            switch ($command) {
                case 'delete':
                    $this->deleteDevlogEntriesByPageId($id);
                    $this->deleteMklogEntriesByPageId($id);
                    break;
                case 'copy':
                    $this->removeLogTablesFromTablesThatCanBeCopied($dataHandler);
                    break;
            }
        }
    }

    /**
     * @param int $pageId
     */
    protected function deleteDevlogEntriesByPageId($pageId)
    {
        if (tx_rnbase_util_Extensions::isLoaded('devlog')) {
            $this->getDatabaseConnection()->doDelete($this->getDevlogTableName(), 'pid = '.intval($pageId));
        }
    }

    /**
     * @param int $pageId
     */
    protected function deleteMklogEntriesByPageId($pageId)
    {
        $this->getDatabaseConnection()->doDelete($this->getMklogTableName(), 'pid = '.intval($pageId));
    }

    /**
     * @return Tx_Rnbase_Database_Connection
     */
    protected function getDatabaseConnection()
    {
        tx_rnbase::load('Tx_Rnbase_Database_Connection');

        return Tx_Rnbase_Database_Connection::getInstance();
    }

    /**
     * @return string
     */
    protected function getDevlogTableName()
    {
        tx_rnbase::load('Tx_Mklog_Utility_Devlog');

        return Tx_Mklog_Utility_Devlog::getTableName();
    }

    /**
     * @return string
     */
    protected function getMklogTableName()
    {
        return \DMK\Mklog\Factory::getDevlogEntryRepository()->getEmptyModel()->getTableName();
    }

    /**
     * Es ist nie gewünscht dass die devlog und tx_mklog_devlog_entry Einträge beim kopieren einer Seite mitkopiert werden,
     * auch nicht für Admins.
     *
     * @param TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected function removeLogTablesFromTablesThatCanBeCopied($dataHandler)
    {
        $tablesThatCanBeCopied = array_flip($dataHandler->compileAdminTables());

        $tablesThatShouldNotBeCopied = array($this->getDevlogTableName(), $this->getMklogTableName());
        foreach ($tablesThatShouldNotBeCopied as $tableThatShouldNotBeCopied) {
            if (isset($tablesThatCanBeCopied[$tableThatShouldNotBeCopied])) {
                unset($tablesThatCanBeCopied[$tableThatShouldNotBeCopied]);
            }
        }

        $dataHandler->copyWhichTables = implode(',', array_flip($tablesThatCanBeCopied));
    }
}
