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

namespace DMK\Mklog;

use DMK\Mklog\Domain\Model\GenericArrayObject as StorageObject;
use DMK\Mklog\Domain\Repository\DevlogEntryRepository;
use DMK\Mklog\Utility\ConfigUtility;
use DMK\Mklog\Utility\DataConverterUtility;
use DMK\Mklog\Utility\EntryDataParserUtility;
use DMK\Mklog\WatchDog\Transport\InterfaceTransport;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * MK Log Factory.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
final class Factory
{
    /**
     * Returns a storage.
     */
    public static function getStorage(): StorageObject
    {
        static $storage = null;

        if (null === $storage) {
            $storage = StorageObject::getInstance();
        }

        return $storage;
    }

    /**
     * @param array<int, mixed> $constructorArguments
     *
     * @return object the created instance
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function makeInstance(string $className, ...$constructorArguments): mixed
    {
        return call_user_func_array(
            [GeneralUtility::class, 'makeInstance'],
            func_get_args()
        );
    }

    /**
     * Returns the config.
     *
     * @return ConfigUtility
     */
    public static function getConfigUtility()
    {
        $storage = self::getStorage();
        if (!$storage->hasConfigUtility()) {
            $storage->setConfigUtility(
                self::makeInstance(ConfigUtility::class)
            );
        }

        return $storage->getConfigUtility();
    }

    /**
     * Returns the data converter.
     *
     * @return DataConverterUtility
     */
    public static function getDataConverterUtility()
    {
        $storage = self::getStorage();
        if (!$storage->hasDataConverterUtility()) {
            $storage->setDataConverterUtility(
                self::makeInstance(DataConverterUtility::class)
            );
        }

        return $storage->getDataConverterUtility();
    }

    /**
     * Creates a devlog entry extra data parser instance.
     *
     * @return EntryDataParserUtility
     */
    public static function getEntryDataParserUtility(Domain\Model\DevlogEntry $devlogEntry): mixed
    {
        return self::makeInstance(EntryDataParserUtility::class, $devlogEntry);
    }

    /**
     * Returns the devlog entry repository.
     *
     * @return DevlogEntryRepository
     */
    public static function getDevlogEntryRepository(): mixed
    {
        return self::makeInstance(DevlogEntryRepository::class);
    }

    /**
     * Creates transport based on the classname.
     */
    public static function getTransport(string $class): InterfaceTransport
    {
        $transport = self::makeInstance($class);

        if (!$transport instanceof InterfaceTransport) {
            throw new \Exception(sprintf('The Transport "%1$s" has to implement the "%2$s"', $transport::class, InterfaceTransport::class));
        }

        return $transport;
    }
}
