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

namespace DMK\Mklog\Domain\Model;

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

    public function hasOrderBy(): bool
    {
        return !empty($this->orderByField);
    }

    public function getOrderByField(): string
    {
        return $this->orderByField;
    }

    public function setOrderBy(string $orderByField, string $orderByDirection = 'ASC'): void
    {
        $this->orderByField = $orderByField;
        $this->orderByDirection = $orderByDirection;
    }

    public function getOrderByDirection(): string
    {
        return $this->orderByDirection;
    }

    public function hasTransportId(): bool
    {
        return !empty($this->transportId);
    }

    public function getTransportId(): string
    {
        return $this->transportId;
    }

    public function setTransportId(string $transportId): void
    {
        $this->transportId = $transportId;
    }

    public function hasSeverity(): bool
    {
        return null !== $this->severity;
    }

    public function getSeverity(): int
    {
        return $this->severity;
    }

    public function setSeverity(int $severity): void
    {
        $this->severity = $severity;
    }

    public function hasExtensionWhitelist(): bool
    {
        return !empty($this->extensionWhitelist);
    }

    public function getExtensionWhitelist(): array
    {
        return $this->extensionWhitelist;
    }

    public function setExtensionWhitelist(array $extensionWhitelist): void
    {
        $this->extensionWhitelist = $extensionWhitelist;
    }

    public function hasExtensionBlacklist(): bool
    {
        return !empty($this->extensionBlacklist);
    }

    public function getExtensionBlacklist(): array
    {
        return $this->extensionBlacklist;
    }

    public function setExtensionBlacklist(array $extensionBlacklist): void
    {
        $this->extensionBlacklist = $extensionBlacklist;
    }

    public function hasMaxResults(): bool
    {
        return null !== $this->maxResults;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }
}
