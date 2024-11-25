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

use DMK\Mklog\Domain\Model\GenericArrayObject;
use DMK\Mklog\Utility\SeverityUtility;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * Devlog logger.
 *
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class GelfLogger extends AbstractLogger
{
    /**
     * Writes the log record.
     *
     * @param \TYPO3\CMS\Core\Log\LogRecord $record Log record
     *
     * @return \TYPO3\CMS\Core\Log\Writer\WriterInterface $this
     */
    public function writeLog(
        \TYPO3\CMS\Core\Log\LogRecord $record,
    ) {
        try {
            $this->storeLog(
                $record->getMessage(),
                $record->getComponent(),
                $record->getLevel(),
                $record->getData()
            );
        } catch (\Exception $e) {
            $this->handleExceptionDuringLogging($e);
        }

        return $this;
    }

    /**
     * Old devlog Hook from the old TYPO3 API.
     */
    public function sysLogHook(array $params)
    {
        // do nothing on syslog init
        if (isset($params['initLog']) && $params['initLog']) {
            return;
        }

        /*
         * \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_*
         * SYSLOG_SEVERITY_INFO = 0;
         * SYSLOG_SEVERITY_NOTICE = 1;
         * SYSLOG_SEVERITY_WARNING = 2;
         * SYSLOG_SEVERITY_ERROR = 3;
         * SYSLOG_SEVERITY_FATAL = 4;
         */
        // map the old log levels to the new one
        switch ((int) ($params['severity'] ?? 0)) {
            case 4:
                $params['severity'] = SeverityUtility::ALERT;
                break;
            case 3:
                $params['severity'] = SeverityUtility::CRITICAL;
                break;
            case 2:
                $params['severity'] = SeverityUtility::WARNING;
                break;
            case 1:
                $params['severity'] = SeverityUtility::NOTICE;
                break;
            case 0:
            default:
                $params['severity'] = SeverityUtility::INFO;
                break;
        }

        try {
            $this->storeLog(
                $params['msg'],
                $params['extKey'],
                $params['severity'],
                ['__trace' => $params['backTrace']]
            );
        } catch (\Exception $e) {
            $this->handleExceptionDuringLogging($e);
        }
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
        $config = \DMK\Mklog\Factory::getConfigUtility();

        $severity = LogLevel::normalizeLevel($severity);

        // check min log level
        if (
            !$config->isGelfEnable()
            || !$config->getGelfCredentials()
            || $severity > $config->getGelfMinLogLevel()
        ) {
            return;
        }

        $options = GenericArrayObject::getInstance([
            'credentials' => $config->getGelfCredentials(),
        ]);

        $transport = $this->getTransport($config);

        $transport->initialize($options);

        $gelfMsg = $this->createDevlogEntry(
            $message,
            $extension,
            $severity,
            $extraData
        );

        try {
            $transport->publish($gelfMsg);
        } catch (\Exception $e) {
            // what todo on transport exception?
            // usualy we have a emergency and a other logger (file or mail) shold take over
            return $this;
        }

        $transport->shutdown();
    }

    /**
     * Creates the transport.
     *
     * @return \DMK\Mklog\WatchDog\Transport\InterfaceTransport
     */
    protected function getTransport(
        \DMK\Mklog\Utility\ConfigUtility $config,
    ) {
        return \DMK\Mklog\Factory::getTransport(
            $config->getGelfTransport()
        );
    }
}
