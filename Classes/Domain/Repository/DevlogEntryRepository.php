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

namespace DMK\Mklog\Domain\Repository;

use DMK\Mklog\Domain\Mapper\DevlogEntryMapper;
use DMK\Mklog\Domain\Model\DevlogEntry;
use DMK\Mklog\Domain\Model\DevlogEntryDemand;
use DMK\Mklog\Factory;
use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Devlog Entry Repository.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryRepository
{
    public function getTableName(): string
    {
        return DevlogEntry::TABLENAME;
    }

    /**
     * Creates an new model instance.
     */
    public function createNewModel(): DevlogEntry
    {
        return Factory::makeInstance(DevlogEntry::class);
    }

    protected function getConnection(): Connection
    {
        return Factory::makeInstance(ConnectionPool::class)->getConnectionForTable($this->getTableName());
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->getConnection()->createQueryBuilder();
    }

    public function createSearchQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder()->select('*')->from($this->getTableName());
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder()
            ->count('uid')
            ->from($this->getTableName())
            ->execute()
            ->fetchColumn();
    }

    /**
     * Exists the table at the db?
     */
    public function optimize(): void
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
            $queryBuilder = $this->createQueryBuilder();
            $query = $queryBuilder
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
            $queryBuilder = $this->createQueryBuilder();
            $query = $queryBuilder->delete($this->getTableName())->where('run_id < '.$lastExec);
            $query->execute();
        }
    }

    /**
     * @return array<DevlogEntry>
     */
    public function findByDemand(DevlogEntryDemand $demand): array
    {
        $queryBuilder = $this->createSearchQueryBuilder();

        if ($demand->hasTransportId()) {
            $queryBuilder->where(
                sprintf(
                    'NOT FIND_IN_SET(\'%s\', `transport_ids`)',
                    $demand->getTransportId()
                )
            );
        }

        if ($demand->hasSeverity()) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->lte('severity', $demand->getSeverity())
            );
        }

        if ($demand->hasExtensionWhitelist()) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    'ext_key',
                    $this->quoteInArray(
                        $demand->getExtensionWhitelist()
                    )
                )
            );
        }

        if ($demand->hasExtensionBlacklist()) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn(
                    'ext_key',
                    $this->quoteInArray(
                        $demand->getExtensionBlacklist()
                    )
                )
            );
        }

        if ($demand->hasMaxResults()) {
            $queryBuilder->setMaxResults($demand->getMaxResults());
        }

        if ($demand->hasOrderBy()) {
            $queryBuilder->orderBy($demand->getOrderByField(), $demand->getOrderByDirection());
        }

        return DevlogEntryMapper::fromResults(
            $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE)
        );
    }

    public function deletyByPid(int $pid): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $query = $queryBuilder->delete($this->getTableName())->where('pid = '.$pid);
        $query->execute();
    }

    /**
     * Persists an model.
     */
    public function persist(
        DevlogEntry $model
    ): void {
        // reduce extra data to current maximum of the field in db (mediumblob: 16MB)
        $model->setExtraDataEncoded(
            \DMK\Mklog\Factory::getEntryDataParserUtility($model)->getShortenedRaw(
                \DMK\Mklog\Utility\EntryDataParserUtility::SIZE_8MB * 2
            )
        );

        if (0 === $model->getUid()) {
            $this->persistNew($model);

            return;
        }

        $this->persistUpdate($model);
    }

    private function persistUpdate(DevlogEntry $model): void
    {
        $connection = $this->getConnection();
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder = $queryBuilder
            ->update($this->getTableName())
            ->where($queryBuilder->expr()->eq('uid', $model->getUid()))
        ;
        foreach ($model->getRecord() as $property => $value) {
            $queryBuilder->set($property, $value);
        }
        $queryBuilder->execute();
    }

    private function persistNew(DevlogEntry $model): void
    {
        $connection = $this->getConnection();
        $query = $connection->createQueryBuilder()->insert($this->getTableName())->values($model->getRecord());
        if ($query->execute()) {
            $model->setUid($connection->lastInsertId($this->getTableName()));
        }
    }

    /**
     * @param string $list
     *
     * @return string[]
     */
    private function quoteInArray(array $list): array
    {
        return array_map(
            function ($entry) {
                return '\''.(string) $entry.'\'';
            },
            $list
        );
    }
}
