<?php

namespace DMK\Mklog\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2020 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * Basic data model with geter's and seter's.
 *
 * @author Michael Wagner
 */
class DevlogEntryDemand
{
    /**
     * @var string
     */
    private $orderByField = '';

    /**
     * @var string
     */
    private $orderByDirection = 'ASC';

    /**
     * @var string
     */
    private $transportId;

    /**
     * @var int
     */
    private $severity;

    /**
     * @var array
     */
    private $extensionWhitelist;
    /**
     * @var array
     */
    private $extensionBlacklist;

    /**
     * @var int
     */
    private $maxResults;

    /**
     * @return bool
     */
    public function hasOrderBy(): bool
    {
        return !empty($this->orderByField);
    }

    /**
     * @return string
     */
    public function getOrderByField(): string
    {
        return $this->orderByField;
    }

    /**
     * @param string $orderByField
     */
    public function setOrderBy(string $orderByField, string $orderByDirection = 'ASC'): void
    {
        $this->orderByField = $orderByField;
        $this->orderByDirection = $orderByDirection;
    }

    /**
     * @return string
     */
    public function getOrderByDirection(): string
    {
        return $this->orderByDirection;
    }

    /**
     * @return bool
     */
    public function hasTransportId(): bool
    {
        return !empty($this->transportId);
    }

    /**
     * @return string
     */
    public function getTransportId(): string
    {
        return $this->transportId;
    }

    /**
     * @param string $transportId
     */
    public function setTransportId(string $transportId): void
    {
        $this->transportId = $transportId;
    }

    /**
     * @return bool
     */
    public function hasSeverity(): bool
    {
        return null !== $this->severity;
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        return $this->severity;
    }

    /**
     * @param int $severity
     */
    public function setSeverity(int $severity): void
    {
        $this->severity = $severity;
    }

    /**
     * @return bool
     */
    public function hasExtensionWhitelist(): bool
    {
        return !empty($this->extensionWhitelist);
    }

    /**
     * @return array
     */
    public function getExtensionWhitelist(): array
    {
        return $this->extensionWhitelist;
    }

    /**
     * @param array $extensionWhitelist
     */
    public function setExtensionWhitelist(array $extensionWhitelist): void
    {
        $this->extensionWhitelist = $extensionWhitelist;
    }

    /**
     * @return bool
     */
    public function hasExtensionBlacklist(): bool
    {
        return !empty($this->extensionBlacklist);
    }

    /**
     * @return array
     */
    public function getExtensionBlacklist(): array
    {
        return $this->extensionBlacklist;
    }

    /**
     * @param array $extensionBlacklist
     */
    public function setExtensionBlacklist(array $extensionBlacklist): void
    {
        $this->extensionBlacklist = $extensionBlacklist;
    }

    /**
     * @return bool
     */
    public function hasMaxResults(): bool
    {
        return null !== $this->maxResults;
    }

    /**
     * @return int
     */
    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    /**
     * @param int $maxResults
     */
    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }
}
