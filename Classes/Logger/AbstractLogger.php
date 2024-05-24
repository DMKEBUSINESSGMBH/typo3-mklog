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

use DMK\Mklog\Utility\Typo3Utility;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\DebugUtility;
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
     *
     * @return \DMK\Mklog\Domain\Model\DevlogEntry
     */
    protected function createDevlogEntry($message, $extension, $severity, $extraData)
    {
        $config = \DMK\Mklog\Factory::getConfigUtility();

        $repo = $this->getDevlogEntryRepository();

        $extraData = $this->progressExtraData($extraData);

        /* @var $entry \DMK\Mklog\Domain\Model\DevlogEntry */
        $entry = $repo->createNewModel();
        $entry->setCrdate(time());
        $entry->setRunId($config->getCurrentRunId());
        $entry->setHost($entry->getHost());
        $entry->setMessage($this->replaceMessagePlaceholders((string) $message, $extraData));
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
     * @see TYPO3\CMS\Core\Log\Writer\AbstractWriter::interpolate()
     */
    protected function replaceMessagePlaceholders(string $message, array $extraData = []): string
    {
        // Build a replacement array with braces around the context keys.
        $replace = [];
        foreach ($extraData as $key => $value) {
            if (!is_array($value) && !is_null($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replace['{'.$key.'}'] = $value;
            }
        }

        // Interpolate replacement values into the message and return.
        return strtr($message, $replace);
    }

    /**
     * Progresses the extra data and adds some aditional informations.
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
            \Psr\Log\AbstractLogger::class,
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
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function handleExceptionDuringLogging(\Throwable $exception): void
    {
        $address = $GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr'] ?? '';
        if ($address && $this->canMailBeSend()) {
            $mailContent = 'This is an automatic email from TYPO3. Don\'t answer!'."\n\n";
            $mailContent .= 'URL: '.GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')."\n";
            $mailContent .= 'Message: '.$exception->getMessage()."\n\n";
            $mailContent .= "Stacktrace:\n".$this->getExceptionTraceWithoutArguments($exception)."\n";
            GeneralUtility::makeInstance(MailMessage::class)
                ->to(new Address($address))
                ->subject('Exception during logging on site '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'])
                ->text($mailContent)
                ->html(nl2br($mailContent))
                ->send();
        }
    }

    /**
     * Send mail only once every minute.
     */
    protected function canMailBeSend(): bool
    {
        $lastSendTime = 0;
        $mailCanBeSend = false;
        $lockDir = Environment::getVarPath().'/lock';
        $lockFile = $lockDir.'/mklog_exception_during_logging.lock';
        if (file_exists($lockFile)) {
            $lastSendTime = file_get_contents($lockFile);
        }

        $timeOneMinuteAgo = time() - 60;
        if ($lastSendTime < $timeOneMinuteAgo) {
            GeneralUtility::mkdir($lockDir);
            file_put_contents($lockFile, time());
            $mailCanBeSend = true;
        }

        return $mailCanBeSend;
    }

    protected function getExceptionTraceWithoutArguments(\Throwable $exception): string
    {
        $traceAsString = '';
        foreach ($exception->getTrace() as $key => $trace) {
            $traceAsString .= '#'.$key;
            $traceAsString .= ' '.$trace['file'];
            $traceAsString .= '('.$trace['line'].'): ';
            $traceAsString .= $trace['class'] ?? '';
            $traceAsString .= $trace['type'] ?? '';
            $traceAsString .= $trace['function'] ?? '';
            $traceAsString .= "\n";
        }

        return $traceAsString;
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
