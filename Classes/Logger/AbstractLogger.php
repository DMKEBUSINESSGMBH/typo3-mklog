<?php

/*
 * Copyright notice
 *
 * (c) 2011-2023 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mklog\Utility\Typo3Utility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Devlog logger.
 *
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractLogger implements \TYPO3\CMS\Core\Log\Writer\WriterInterface, \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Stores a devlog entry.
     *
     * @param string $message
     * @param string $extension
     * @param int    $severity
     * @param mixed  $extraData
     *
     * @return \DMK\Mklog\Domain\Model\DevlogEntry
     */
    protected function createDevlogEntry($message, $extension, $severity, $extraData)
    {
        $config = \DMK\Mklog\Factory::getConfigUtility();

        $repo = $this->getDevlogEntryRepository();

        /* @var $entry \DMK\Mklog\Domain\Model\DevlogEntry */
        $entry = $repo->createNewModel();
        $entry->setCrdate(time());
        $entry->setRunId($config->getCurrentRunId());
        $entry->setHost($entry->getHost());
        $entry->setMessage((string) $message);
        $entry->setExtKey((string) $extension);
        $entry->setSeverity((int) $severity);
        $entry->setPid(0);

        if (null !== Typo3Utility::getTsFe()) {
            $entry->setPid((int) Typo3Utility::getTsFe()->id);
        }

        $entry->setCruserId(Typo3Utility::getBeUserId());

        $entry->setExtraData($this->progressExtraData($extraData));

        return $entry;
    }

    /**
     * Progresses the extra data and adds some aditional informations.
     *
     * @param mixed $extraData
     *
     * @return array
     */
    protected function progressExtraData($extraData)
    {
        // force extra_data to be an array!
        if (!is_array($extraData)) {
            $extraData = ['extra' => $extraData];
        }
        // add userdata
        $extraData['__feuser'] = Typo3Utility::getFeUserId();
        $extraData['__beuser'] = Typo3Utility::getBeUserId();
        // add current uri
        $extraData['__requesturl'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        // add trace to extradata
        $extraData['__trace'] = $this->getBacktrace();

        return $extraData;
    }

    /**
     * Returns the Backtrase excluding the log calls.
     *
     * @return array
     */
    private function getBacktrace()
    {
        $trace = array_reverse(
            explode(' // ', DebugUtility::debugTrail())
        );

        $lastIgnoreKey = 0;
        $ignoreClasses = [
            // ignore internal loger calls
            'DMK\\Mklog\\Logger\\',
            // ignore core devlog and logerr calls
            'Psr\\Log\\AbstractLogger',
            'TYPO3\\CMS\\Core\\Log\\',
            'TYPO3\\CMS\\Core\\Utility\\GeneralUtility::devLog',
            // ignore rnbase loggers
            'Tx_Rnbase_Utility_Logger',
            'tx_rnbase_util_Logger',
        ];

        foreach ($trace as $key => $path) {
            $ignore = false;
            foreach ($ignoreClasses as $ignoreClass) {
                $ignore = (0 === strpos($path, $ignoreClass, 0));
                if ($ignore) {
                    break;
                }
            }
            // break if ther is no more ignore
            if ($ignore) {
                $lastIgnoreKey = $key;
            }
        }

        return array_splice($trace, $lastIgnoreKey + 1);
    }

    /**
     * Send an exeption mail for all exceptions during the store log process.
     *
     * @TODO: add recursive call check for exceptions (
     *     throw exception, only block at secnd exception.
     *     so the gelf logger can log the exception
     *     and only a recursion of logging will prevented.
     * )
     */
    protected function handleExceptionDuringLogging(
        \Exception $exception
    ) {
        if (ExtensionManagementUtility::isLoaded('rn_base')) {
            // try to send mail
            $address = \Sys25\RnBase\Configuration\Processor::getExtensionCfgValue(
                'rn_base',
                'sendEmailOnException'
            );
            if ($address) {
                \Sys25\RnBase\Utility\Misc::sendErrorMail(
                    $address,
                    'Mklog\DevlogLogger',
                    $exception
                );
            }
        }
    }

    /**
     * Returns the devlog entry repository.
     *
     * @return \DMK\Mklog\Domain\Repository\DevlogEntryRepository
     */
    protected function getDevlogEntryRepository()
    {
        return \DMK\Mklog\Factory::getDevlogEntryRepository();
    }
}
