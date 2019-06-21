<?php

namespace DMK\Mklog\Domain\Model;

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

\tx_rnbase::load('Tx_Rnbase_Domain_Model_Base');

/**
 * Devlog entry Model.
 *
 * @method int              getPid()
 * @method DevlogEntryModel setPid() setPid(int $pid)
 * @method bool             hasPid()
 * @method int              getRunId()
 * @method DevlogEntryModel setRunId() setRunId(int $runId)
 * @method bool             hasRunId()
 * @method string           getExtKey()
 * @method DevlogEntryModel setExtKey() setExtKey(string $extKey)
 * @method bool             hasExtKey()
 * @method DevlogEntryModel setHost() setHost(string $host)
 * @method bool             hasHost()
 * @method string           getMessage()
 * @method DevlogEntryModel setMessage() setMessage(string $message)
 * @method bool             hasMessage()
 * @method int              getSeverity()
 * @method DevlogEntryModel setSeverity() setSeverity(int $severity)
 * @method bool             hasSeverity()
 * @method int              getCruserId()
 * @method DevlogEntryModel setCruserId() setCruserId(int $cruserId)
 * @method bool             hasCruserId()
 * @method int              getCrdate()
 * @method DevlogEntryModel setCrdate() setCrdate(int $crdate)
 * @method bool             hasCrdate()
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryModel extends \Tx_Rnbase_Domain_Model_Base implements \DMK\Mklog\WatchDog\Message\InterfaceMessage
{
    /**
     * Override reset and dont load record after creating entry.
     *
     * @return Tx_Rnbase_Domain_Model_Base
     */
    public function reset()
    {
        //$this->loadRecord();

        // set the modified state to clean
        $this->resetCleanState();

        return $this;
    }

    /**
     * Liefert den aktuellen Tabellenname.
     *
     * @return Tabellenname als String
     */
    public function getTableName()
    {
        return 'tx_mklog_devlog_entry';
    }

    /**
     * A list of scheduler task uids which has already transferred this message.
     *
     * @return array
     */
    public function getTransportIds()
    {
        if ($this->isPropertyEmpty('transport_ids')) {
            return array();
        }

        return explode(',', $this->getProperty('transport_ids'));
    }

    /**
     * Adds a scheduler to the transport id list.
     *
     * @param string $transportId
     *
     * @return array
     */
    public function addTransportId(
        $transportId
    ) {
        $ids = $this->getTransportIds();
        $ids[] = $transportId;

        return $this->setProperty(
            'transport_ids',
            implode(',', array_unique($ids))
        );
    }

    /**
     * The raw extra data.
     *
     * @return string
     */
    public function getExtraDataRaw()
    {
        return $this->getProperty('extra_data');
    }

    /**
     * Returns the extra data.
     *
     * @return array
     */
    private function getExtraData()
    {
        return \DMK\Mklog\Factory::getDataConverterUtility()->decode(
            $this->getExtraDataRaw()
        );
    }

    /**
     * Setter for extra data.
     *
     * @param array $data
     *
     * @return DevlogEntryModel
     */
    public function setExtraData(
        array $data
    ) {
        return $this->setProperty(
            'extra_data',
            \DMK\Mklog\Factory::getDataConverterUtility()->encode($data)
        );
    }

    /**
     * Returns the public values of extra data.
     *
     * @return mixed
     */
    public function getExternalExtraData()
    {
        $data = array();

        foreach ($this->getExtraData() as $key => $value) {
            if ('_' === $key[0] && '_' === $key[1]) {
                continue;
            }
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Returns the protected values of extra data.
     *
     * @return mixed
     */
    public function getInternalExtraData()
    {
        $data = array();

        foreach ($this->getExtraData() as $key => $value) {
            if (!('_' === $key[0] && '_' === $key[1])) {
                continue;
            }
            $data[substr($key, 2)] = $value;
        }

        return $data;
    }

    /* *** ******************************************** *** *
     * *** \DMK\Mklog\WatchDog\Message\InterfaceMessage *** *
     * *** ******************************************** *** */

    /**
     * Returns the short text of the message.
     *
     * @return string
     */
    public function getShortMessage()
    {
        return $this->getMessage();
    }

    /**
     * Returns the full text of the message.
     *
     * @return string
     */
    public function getFullMessage()
    {
        return \DMK\Mklog\Factory::getDataConverterUtility()->encode(
            $this->getExternalExtraData()
        );
    }

    /**
     * Returns the timestamp of the message.
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        $dateTime = \DateTime::createFromFormat('U.u', $this->getCrdate().'.0216');
        // createFromFormat bzw. UNIX Timestamps haben per default GMT als Zeitzone.
        // Daher müssen wir zusätzlich die aktuelle Zeitzone setzen.
        $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }

    /**
     * Returns the log level of the message as a Psr\Log\Level-constant.
     *
     * @return string
     */
    public function getLevel()
    {
        return \DMK\Mklog\Utility\SeverityUtility::getPsrLevelConstant(
            $this->getSeverity()
        );
    }

    /**
     * Returns the facility of the message.
     *
     * @return string
     */
    public function getFacility()
    {
        return $this->getExtKey();
    }

    /**
     * Returns the host of the message.
     *
     * @return string
     */
    public function getHost()
    {
        $host = $this->getProperty('host');

        // first check ext conf
        if (empty($host)) {
            $config = \DMK\Mklog\Factory::getConfigUtility();
            $host = $config->getHost();
        }

        // now check the domain
        if (empty($host)) {
            $utility = \tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

            $host = $utility::getIndpEnv('TYPO3_HOST_ONLY');
        }

        // as fallback use the server hostname
        if (empty($host)) {
            $host = gethostname();
        }

        return $host;
    }

    /**
     * Returns the value of the additional field of the message.
     *
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->getInternalExtraData();
    }
}
