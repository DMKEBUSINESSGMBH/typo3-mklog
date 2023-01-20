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

namespace DMK\Mklog\Domain\Model;

use DMK\Mklog\WatchDog\Message\InterfaceMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Devlog entry Model.
 *
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class DevlogEntry implements InterfaceMessage
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

    public function getRecord(): array
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
     * Liefert den aktuellen Tabellenname.
     */
    public function getTableName(): string
    {
        return self::TABLENAME;
    }

    /**
     * Getter for uid.
     *
     * @return int the uid or NULL if none set yet
     */
    public function getUid(): int
    {
        return (int) $this->uid;
    }

    /**
     * Setter for the uid.
     *
     * @param int $uid
     */
    public function setUid($uid): self
    {
        $this->uid = (int) $uid;

        return $this;
    }

    /**
     * Getter for the pid.
     *
     * @return int|null the pid or NULL if none set yet
     */
    public function getPid(): int
    {
        return (int) $this->pid;
    }

    /**
     * Setter for the pid.
     *
     * @param int $pid
     */
    public function setPid($pid): self
    {
        $this->pid = (int) $pid;

        return $this;
    }

    public function getCrdate(): int
    {
        return (int) $this->crdate;
    }

    /**
     * @param int $crdate
     *
     * @return self
     */
    public function setCrdate($crdate)
    {
        $this->crdate = (int) $crdate;

        return $this;
    }

    public function getRunId(): string
    {
        return $this->runId;
    }

    /**
     * @return self
     */
    public function setRunId(string $runId)
    {
        $this->runId = $runId;

        return $this;
    }

    public function getSeverity(): int
    {
        return (int) $this->severity;
    }

    /**
     * @param int $severity
     *
     * @return self
     */
    public function setSeverity($severity)
    {
        $this->severity = (int) $severity;

        return $this;
    }

    public function getExtKey(): string
    {
        return $this->extKey;
    }

    /**
     * @return self
     */
    public function setExtKey(string $extKey)
    {
        $this->extKey = $extKey;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return self
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    public function getCruserId(): int
    {
        return (int) $this->cruserId;
    }

    /**
     * @param int $cruserId
     *
     * @return self
     */
    public function setCruserId($cruserId)
    {
        $this->cruserId = (int) $cruserId;

        return $this;
    }

    /**
     * A list of scheduler task uids which has already transferred this message.
     */
    public function getTransportIds(): array
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
     */
    public function setTransportIdsRaw(
        string $transportIds
    ): self {
        $this->transportIds = $transportIds;

        return $this;
    }

    /**
     * Adds a scheduler to the transport id list.
     */
    public function addTransportId(
        string $transportId
    ): self {
        $ids = $this->getTransportIds();
        $ids[] = $transportId;

        $this->transportIds = implode(',', array_unique($ids));

        return $this;
    }

    /**
     * The raw extra data.
     */
    public function getExtraDataRaw(): string
    {
        return $this->extraData;
    }

    /**
     * Returns the extra data.
     */
    private function getExtraData(): array
    {
        return \DMK\Mklog\Factory::getDataConverterUtility()->decode(
            $this->getExtraDataRaw()
        );
    }

    /**
     * Setter for extra data.
     */
    public function setExtraData(
        array $data
    ): self {
        $this->extraData = \DMK\Mklog\Factory::getDataConverterUtility()->encode($data);

        return $this;
    }

    /**
     * Setter for extra data.
     */
    public function setExtraDataEncoded(
        string $data
    ): self {
        $this->extraData = $data;

        return $this;
    }

    /**
     * Returns the public values of extra data.
     */
    public function getExternalExtraData(): array
    {
        $data = [];

        foreach ($this->getExtraData() as $key => $value) {
            if ($this->isInternalProperty($key)) {
                continue;
            }
            $data[$key] = $value;
        }

        return $data;
    }

    protected function isInternalProperty($property): bool
    {
        return is_string($property) && '__' === substr($property, 0, 2);
    }

    /**
     * Returns the protected values of extra data.
     */
    public function getInternalExtraData(): array
    {
        $data = [];

        foreach ($this->getExtraData() as $key => $value) {
            if (!$this->isInternalProperty($key)) {
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
     */
    public function getShortMessage(): string
    {
        return $this->getMessage();
    }

    /**
     * Returns the full text of the message for the WatchDog-Transport.
     *
     * The Message is shortened if bigger as configured max_extra_data_size.
     */
    public function getFullMessage(): string
    {
        return \DMK\Mklog\Factory::getDataConverterUtility()->encode(
            \DMK\Mklog\Factory::getEntryDataParserUtility($this)->getShortenedExternalExtraData()
        );
    }

    /**
     * Returns the timestamp of the message.
     */
    public function getTimestamp(): \DateTime
    {
        $dateTime = \DateTime::createFromFormat('U.u', $this->getCrdate().'.0216');
        // createFromFormat bzw. UNIX Timestamps haben per default GMT als Zeitzone.
        // Daher müssen wir zusätzlich die aktuelle Zeitzone setzen.
        $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }

    /**
     * Returns the log level of the message as a Psr\Log\Level-constant.
     */
    public function getLevel(): string
    {
        return \DMK\Mklog\Utility\SeverityUtility::getPsrLevelConstant(
            $this->getSeverity()
        );
    }

    /**
     * Returns the facility of the message.
     */
    public function getFacility(): string
    {
        return $this->getExtKey();
    }

    /**
     * Returns the host of the message.
     */
    public function getHost(): string
    {
        $host = $this->host;

        // first check ext conf
        if (empty($host)) {
            $config = \DMK\Mklog\Factory::getConfigUtility();
            $host = $config->getHost();
        }

        // now check the domain
        if (empty($host)) {
            $host = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
        }

        // as fallback use the server hostname
        if (empty($host)) {
            $host = gethostname();
        }

        return $host;
    }

    /**
     * Set the host.
     */
    public function setHost($host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Returns the value of the additional field of the message for the WatchDog-Transport.
     *
     * The Message is shortened if bigger as configured max_extra_data_size.
     */
    public function getAdditionalData(): array
    {
        return \DMK\Mklog\Factory::getEntryDataParserUtility($this)->getShortenedInternalExtraData();
    }
}
