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

use DMK\Mklog\Domain\Model\DevlogEntry;
use DMK\Mklog\Factory;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Devlog Entry Repository.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryRepository
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return DevlogEntry::TABLENAME;
    }

    /**
     * Creates an new model instance.
     *
     * @param array $record
     *
     * @return DevlogEntry
     */
    public function createNewModel()
    {
        return Factory::makeInstance(DevlogEntry::class);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    protected function getConnection()
    {
        return Factory::makeInstance(ConnectionPool::class)->getConnectionForTable($this->getTableName());
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @return int
     */
    public function countAll()
    {
        return $this->createQueryBuilder()
            ->count('uid')
            ->from($this->getTableName())
            ->execute()
            ->fetchOne();
    }

    /**
     * Exists the table at the db?
     */
    public function optimize()
    {
        static $optimized = false;

        // Only one optimize run per request
        if ($optimized) {
            return;
        }
        $optimized = true;

        $maxRows = \DMK\Mklog\Factory::getConfigUtility()->getMaxLogs();

        // no cleanup
        if (empty($maxRows)) {
            return;
        }

        // fetch current rows
        $numRows = $this->countAll();

        // there are log entries to delete
        if ($numRows > $maxRows) {
            // fetch the execution date from the latest log entry
            $qb = $this->createQueryBuilder();
            $query = $qb
                ->select('run_id')
                ->from($this->getTableName())
                ->orderBy('run_id', 'DESC')
                ->setFirstResult($maxRows)
                ->setMaxResults(1);

            $lastExec = $query->execute()->fetchOne();

            // nothing found to delete!?
            if (empty($lastExec)) {
                return;
            }

            // delete all entries, older than the last exeution date!
            $qb = $this->createQueryBuilder();
            $query = $qb->delete($this->getTableName())->where('run_id < '.$lastExec);
            $query->execute();
        }
    }

    /**
     * Persists an model.
     */
    public function persist(
        DevlogEntry $model
    ) {
        // reduce extra data to current maximum of the field in db (mediumblob: 16MB)
        $model->setExtraDataEncoded(
            \DMK\Mklog\Factory::getEntryDataParserUtility($model)->getShortenedRaw(
                \DMK\Mklog\Utility\EntryDataParserUtility::SIZE_8MB * 2
            )
        );

        $connection = $this->getConnection();
        $query = $connection->createQueryBuilder()->insert($this->getTableName())->values($model->getRecord());
        if ($query->execute()) {
            $model->setUid($connection->lastInsertId($this->getTableName()));
        }
    }
}
