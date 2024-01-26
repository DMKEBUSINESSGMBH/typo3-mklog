<?php

/*
 * Copyright notice
 *
 * (c) 2011-2024 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mklog\Factory;
use DMK\Mklog\Utility\SeverityUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * Devlog logger.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogLogger extends AbstractLogger
{
    /**
     * We are during the log write process? prevent nesting loop.
     *
     * @var bool
     */
    protected $whileWriting = false;

    /**
     * Writes the log record.
     *
     * @param \TYPO3\CMS\Core\Log\LogRecord $record Log record
     *
     * @return \TYPO3\CMS\Core\Log\Writer\WriterInterface $this
     */
    public function writeLog(
        \TYPO3\CMS\Core\Log\LogRecord $record
    ) {
        try {
            //  prevent nesting write loops
            if ($this->whileWriting) {
                throw new \Exception('Nesting log writer calls prevented', 1513856342);
            }
            $this->whileWriting = true;

            $this->storeLog(
                $record->getMessage(),
                $record->getComponent(),
                $record->getLevel(),
                $record->getData()
            );
        } catch (\Exception $e) {
            $this->handleExceptionDuringLogging($e);
        }

        $this->whileWriting = false;

        return $this;
    }

    /**
     * Stores a devlog entry.
     *
     * @param string $message
     * @param string $extension
     * @param int    $severity
     */
    protected function storeLog($message, $extension, $severity, $extraData)
    {
        if (!$this->isLoggingEnabled()) {
            return;
        }

        $config = Factory::getConfigUtility();

        $severity = LogLevel::normalizeLevel($severity);

        // check min log level
        if ($severity > $config->getMinLogLevel()) {
            return;
        }

        // check exclude extension keys
        if (in_array($extension, $config->getExcludeExtKeys())) {
            return;
        }

        $repo = $this->getDevlogEntryRepository();

        $entry = $this->createDevlogEntry(
            $message,
            $extension,
            $severity,
            $extraData
        );

        $repo->persist($entry);
    }

    /**
     * Old devlog Hook from the old TYPO3 API.
     */
    public function devLogHook(array $params)
    {
        // map the old log levels to the new one
        switch ((int) $params['severity']) {
            case -1:
                $params['severity'] = SeverityUtility::DEBUG;
                break;
            case 0:
                $params['severity'] = SeverityUtility::INFO;
                break;
            case 1:
                $params['severity'] = SeverityUtility::NOTICE;
                break;
            case 2:
                $params['severity'] = SeverityUtility::WARNING;
                break;
            case 3:
                $params['severity'] = SeverityUtility::ERROR;
                break;
        }

        try {
            $this->storeLog(
                $params['msg'],
                $params['extKey'],
                $params['severity'],
                $params['dataVar']
            );
        } catch (\Exception $e) {
            $this->handleExceptionDuringLogging($e);
        }
    }

    /**
     * Is logging enabled?
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function isLoggingEnabled()
    {
        // skip logging, if there is no db.
        if (!$this->isDatabaseConnected()) {
            return false;
        }

        // skip if logging is disabled
        if (
            !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklog']['nolog'])
            || !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['devlog']['nolog'])
        ) {
            return false;
        }

        // now check some cachable options
        $storage = Factory::getStorage();

        if ($storage->hasLoggingActive()) {
            return $storage->getLoggingActive();
        }

        $config = Factory::getConfigUtility();

        $storage->setLoggingActive(true);

        if (!$config->isEnableDevLog()) {
            $storage->setLoggingActive(false);
        }

        return $storage->getLoggingActive();
    }

    /**
     * @return bool
     */
    protected function isDatabaseConnected()
    {
        $connectionManager = Factory::makeInstance(ConnectionPool::class);
        $connection = $connectionManager->getConnectionByName($connectionManager::DEFAULT_CONNECTION_NAME);

        return $connection->isConnected();
    }
}
