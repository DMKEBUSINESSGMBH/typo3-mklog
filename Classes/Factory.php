<?php

namespace DMK\Mklog;

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

use DMK\Mklog\Domain\Model\GenericArrayObject as StorageObject;
use DMK\Mklog\Domain\Repository\DevlogEntryRepository;
use DMK\Mklog\Utility\ConfigUtility;
use DMK\Mklog\Utility\DataConverterUtility;
use DMK\Mklog\Utility\EntryDataParserUtility;
use DMK\Mklog\Utility\VersionUtility;
use DMK\Mklog\WatchDog\Transport\InterfaceTransport;
use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
     *
     * @return StorageObject
     */
    public static function getStorage()
    {
        static $storage = null;

        if (null === $storage) {
            $storage = StorageObject::getInstance();
        }

        return $storage;
    }

    /**
     * @param string $className
     * @param array<int, mixed> $constructorArguments
     *
     * @return object the created instance
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function makeInstance($className, ...$constructorArguments)
    {
        return call_user_func_array(
            [GeneralUtility::class, 'makeInstance'],
            func_get_args()
        );
    }

    /**
     * @return ObjectManager
     */
    public static function getObjectManager()
    {
        // On some early state the object manager wasn't build with di/serviceprovider yet.
        // Then the constructor arguments has to be passed to create the object manager.
        // Or we call the service provider directly
        // @TODO: old di is deprecated, refactor if typo3 9 support will be dropped.
        if (VersionUtility::isTypo3Version10OrHigher()) {
            return \TYPO3\CMS\Extbase\ServiceProvider::getObjectManager(GeneralUtility::getContainer());
        }

        return self::makeInstance(ObjectManager::class);
    }

    /**
     * Returns the config.
     *
     * @return \DMK\Mklog\Utility\ConfigUtility
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
     * @return \DMK\Mklog\Utility\DataConverterUtility
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
     * @param Domain\Model\DevlogEntry $devlogEntry
     *
     * @return \DMK\Mklog\Utility\EntryDataParserUtility
     */
    public static function getEntryDataParserUtility(Domain\Model\DevlogEntry $devlogEntry)
    {
        return self::makeInstance(EntryDataParserUtility::class, $devlogEntry);
    }

    /**
     * Returns the devlog entry repository.
     *
     * @return \DMK\Mklog\Domain\Repository\DevlogEntryRepository
     */
    public static function getDevlogEntryRepository()
    {
        // @TODO: old di is deprecated, refactor if typo3 9 support will be dropped.
        return self::getObjectManager()->get(DevlogEntryRepository::class);
    }

    /**
     * Creates a transport based on the classname.
     *
     * @param string $class
     *
     * @return \DMK\Mklog\WatchDog\Transport\InterfaceTransport
     */
    public static function getTransport($class)
    {
        $transport = self::makeInstance($class);

        if (!$transport instanceof InterfaceTransport) {
            throw new Exception(sprintf('The Transport "%1$s" '.'has to implement the "%2$s"', get_class($transport), InterfaceTransport::class));
        }

        return $transport;
    }
}
