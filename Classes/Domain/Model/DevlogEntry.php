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

use DMK\Mklog\WatchDog\Message\InterfaceMessage;
use Tx_Rnbase_Domain_Model_RecordInterface as LegacyRecordInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Devlog entry Model.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntry implements InterfaceMessage, LegacyRecordInterface
{
    public const TABLENAME = 'tx_mklog_devlog_entry';

    /**
     * @var int The uid of the record. The uid is only unique in the context of the database table.
     */
    protected $uid;

    /**
     * @var int the id of the page the record is "stored"
     */
    protected $pid;

    /**
     * @var int
     */
    protected $crdate;

    /**
     * @var string As varchar(50)
     */
    protected $runId;
    /**
     * @var int
     */
    protected $severity;
    /**
     * @var string As varchar(255)
     */
    protected $extKey;
    /**
     * @var string As varchar(255)
     */
    protected $host;
    /**
     * @var string As text
     */
    protected $message;
    /**
     * @var string As mediumblob
     */
    protected $extraData;
    /**
     * @var int
     */
    protected $cruserId;
    /**
     * @var string As varchar(60)
     */
    protected $transportIds;

    public function __construct(array $record = null)
    {
        //@TODO: legacy rnbase make instance model support, remove if rn_base isn't used anymore
        if (null !== $record && is_array($record)) {
            foreach ($record as $columnName => $value) {
                $propertyName = GeneralUtility::underscoredToLowerCamelCase($columnName);
                if (property_exists($this, $propertyName)) {
                    $this->{$propertyName} = $value;
                }
            }
        }
    }

    /**
     * @return array
     *
     * @TODO: legacy rnbase model support, remove if rn_base isn't used anymore
     */
    public function getRecord()
    {
        $values = [];

        foreach (get_object_vars($this) as $propertyName => $value) {
            if (null === $value || '_' === $propertyName[0]) {
                continue;
            }
            $column = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
            $values[$column] = $value;
        }

        return $values;
    }

    /**
     * @return array
     *
     * @TODO: legacy rnbase model support, remove if rn_base isn't used anymore
     */
    public function getProperty($property)
    {
        $properties = $this->getRecord();

        if (isset($properties[$property])) {
            return $properties[$property];
        }

        return null;
    }

    /**
     * Liefert den aktuellen Tabellenname.
     *
     * @return string
     */
    public function getTableName()
    {
        return self::TABLENAME;
    }

    /**
     * Getter for uid.
     *
     * @return int the uid or NULL if none set yet
     */
    public function getUid()
    {
        if (null !== $this->uid) {
            return (int) $this->uid;
        }

        return null;
    }

    /**
     * Setter for the uid.
     *
     * @param int $uid
     */
    public function setUid($uid): void
    {
        $this->uid = $uid;
    }

    /**
     * Getter for the pid.
     *
     * @return int|null the pid or NULL if none set yet
     */
    public function getPid()
    {
        if (null === $this->pid) {
            return null;
        }

        return (int) $this->pid;
    }

    /**
     * Setter for the pid.
     *
     * @param int $pid
     */
    public function setPid($pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return int
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * @param int $crdate
     *
     * @return self
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getRunId()
    {
        return $this->runId;
    }

    /**
     * @param string $runId
     *
     * @return self
     */
    public function setRunId($runId)
    {
        $this->runId = $runId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param int $severity
     *
     * @return self
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtKey()
    {
        return $this->extKey;
    }

    /**
     * @param string $extKey
     *
     * @return self
     */
    public function setExtKey($extKey)
    {
        $this->extKey = $extKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return int
     */
    public function getCruserId()
    {
        return $this->cruserId;
    }

    /**
     * @param int $cruserId
     *
     * @return self
     */
    public function setCruserId($cruserId)
    {
        $this->cruserId = $cruserId;

        return $this;
    }

    /**
     * A list of scheduler task uids which has already transferred this message.
     *
     * @return array
     */
    public function getTransportIds()
    {
        if (empty($this->transportIds)) {
            return [];
        }

        return explode(',', $this->transportIds);
    }

    /**
     * Adds a scheduler to the transport id list.
     *
     * @param string $transportId
     *
     * @return self
     */
    public function addTransportId(
        $transportId
    ) {
        $ids = $this->getTransportIds();
        $ids[] = $transportId;

        $this->transportIds = implode(',', array_unique($ids));

        return $this;
    }

    /**
     * The raw extra data.
     *
     * @return string
     */
    public function getExtraDataRaw()
    {
        return $this->extraData;
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
     * @return DevlogEntry
     */
    public function setExtraData(
        array $data
    ) {
        $this->extraData = \DMK\Mklog\Factory::getDataConverterUtility()->encode($data);

        return $this;
    }

    /**
     * Setter for extra data.
     *
     * @return DevlogEntry
     */
    public function setExtraDataEncoded(
        string $data
    ) {
        $this->extraData = $data;

        return $this;
    }

    /**
     * Returns the public values of extra data.
     *
     * @return mixed
     */
    public function getExternalExtraData()
    {
        $data = [];

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
        $data = [];

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
     * Returns the full text of the message for the WatchDog-Transport.
     *
     * The Message is shortened if bigger as configured max_extra_data_size.
     *
     * @return string
     */
    public function getFullMessage()
    {
        return \DMK\Mklog\Factory::getDataConverterUtility()->encode(
            \DMK\Mklog\Factory::getEntryDataParserUtility($this)->getShortenedExternalExtraData()
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
        $host = $this->host;

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
     * Set the host.
     *
     * @param $host
     *
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Returns the value of the additional field of the message for the WatchDog-Transport.
     *
     * The Message is shortened if bigger as configured max_extra_data_size.
     *
     * @return array
     */
    public function getAdditionalData()
    {
        return \DMK\Mklog\Factory::getEntryDataParserUtility($this)->getShortenedInternalExtraData();
    }
}
