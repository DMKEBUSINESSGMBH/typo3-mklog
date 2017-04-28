<?php
namespace DMK\Mklog\Logger;

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

/**
 * Devlog logger
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractLogger implements \TYPO3\CMS\Core\Log\Writer\WriterInterface, \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Constructs this log writer
     *
     * @param array $options Configuration options - depends on the actual log writer
     *
     * @return void
     */
    public function __construct(
        array $options = array()
    ) {
        // $this->options = \Tx_Rnbase_Domain_Model_Data::getInstance($options);
    }

    /**
     * Stores a devlog entry
     *
     * @param string $message
     * @param string $extension
     * @param int $severity
     * @param mixed $extraData
     *
     * @return \DMK\Mklog\Domain\Model\DevlogEntryModel
     */
    protected function createDevlogEntry($message, $extension, $severity, $extraData)
    {
        $config = \DMK\Mklog\Factory::getConfigUtility();

        $repo = $this->getDevlogEntryRepository();

        /* @var $entry \DMK\Mklog\Domain\Model\DevlogEntryModel */
        $entry = $repo->createNewModel();
        $entry->setCrdate(time());
        $entry->setRunId($config->getCurrentRunId());
        $entry->setHost($entry->getHost());
        $entry->setMessage((string) $message);
        $entry->setExtKey((string) $extension);
        $entry->setSeverity((int) $severity);
        $entry->setPid(0);

        if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE'])) {
            $entry->setPid((int) $GLOBALS['TSFE']->id);
        }

        $entry->setCruserId(0);
        if (!empty($GLOBALS['BE_USER']->user['uid'])) {
            $entry->setCruserId((int) $GLOBALS['BE_USER']->user['uid']);
        }

        $entry->setExtraData($this->progressExtraData($extraData));

        return $entry;
    }

    /**
     * Progresses the extra data and adds some aditional informations
     *
     * @param mixed $extraData
     *
     * @return array
     */
    protected function progressExtraData($extraData)
    {
        // force extra_data to be an array!
        if (!is_array($extraData)) {
            $extraData = array('extra' => $extraData);
        }
        // add userdata
        \tx_rnbase::load('tx_rnbase_util_TYPO3');
        $extraData['__feuser'] = \tx_rnbase_util_TYPO3::getFEUserUID();
        $extraData['__beuser'] = \tx_rnbase_util_TYPO3::getBEUserUID();
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
        \tx_rnbase::load('tx_rnbase_util_Debug');
        $trace = array_reverse(
            \tx_rnbase_util_Debug::getTracePaths()
        );

        $lastIgnoreKey = 0;
        $ignoreClasses = array(
            // ignore internal loger calls
            'DMK\\Mklog\\Logger\\',
            // ignore core devlog and logerr calls
            'TYPO3\\CMS\\Core\\Log\\',
            'TYPO3\\CMS\\Core\\Utility\\GeneralUtility::devLog',
            // ignore rnbase loggers
            'Tx_Rnbase_Utility_Logger',
            'tx_rnbase_util_Logger',
        );

        foreach ($trace as $key => $path) {
            $ignore = false;
            foreach ($ignoreClasses as $ignoreClass) {
                $ignore = \Tx_Rnbase_Utility_Strings::isFirstPartOfStr($path, $ignoreClass);
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
     * Returns the devlog entry repository
     *
     * @return \DMK\Mklog\Domain\Repository\DevlogEntryRepository
     */
    protected function getDevlogEntryRepository()
    {
        return \DMK\Mklog\Factory::getDevlogEntryRepository();
    }
}
