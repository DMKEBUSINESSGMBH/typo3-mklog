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

namespace DMK\Mklog\Utility;

use DMK\Mklog\Domain\Model\GenericArrayObject as ConfigObject;
use DMK\Mklog\Factory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * MK Log Factory.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ConfigUtility implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Internal config storage.
     *
     * @var ConfigObject
     */
    private $storage;

    /**
     * Returns a storage.
     *
     * @return ConfigObject
     */
    protected function getStorage()
    {
        if (null === $this->storage) {
            $this->storage = ConfigObject::getInstance();
        }

        return $this->storage;
    }

    /**
     * The extension configuration!
     *
     * @param string $key
     *
     * @return int|string|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getExtConf($key, $default = null)
    {
        $storage = $this->getStorage();
        if (!$storage->hasExtConf()) {
            $storage->setExtConf([]);

            $storage->setExtConf(
                Factory::makeInstance(ExtensionConfiguration::class)->get(
                    'mklog',
                    ''
                )
            );
        }

        $extConf = $storage->getExtConf();

        if (empty($extConf[$key])) {
            return $default;
        }

        return $extConf[$key];
    }

    /**
     * The current run id.
     *
     * @return int
     */
    public function getCurrentRunId()
    {
        $storage = $this->getStorage();
        if (!$storage->hasDevLogCurrentRunId()) {
            [$sec, $usec] = explode('.', (string) microtime(true));
            // miliseconds has to be exactly 6 sings long. otherwise the resulting number is too small.
            $usec .= str_repeat('0', 6 - strlen($usec));
            $storage->setDevLogCurrentRunId($sec.$usec);
        }

        return $storage->getDevLogCurrentRunId();
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getSiteName(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] ?? '';
    }

    /**
     * Is the devlog enabled?
     */
    public function isEnableDevLog(): bool
    {
        return (bool) $this->getExtConf('enable_devlog');
    }

    /**
     * Is the configured host?
     *
     * @return string
     */
    public function getHost()
    {
        return $this->getExtConf('host', '');
    }

    /**
     * Minimum log level to log.
     *
     * @return int
     */
    public function getMinLogLevel()
    {
        return (int) $this->getExtConf('min_log_level');
    }

    /**
     * Max rows to keep after cleanup.
     *
     * @return int
     */
    public function getMaxLogs()
    {
        return (int) $this->getExtConf('max_logs');
    }

    /**
     * Max size of extra_data in byte.
     *
     * @return int
     */
    public function getMaxTransportExtraDataSize()
    {
        $maxSize = (int) $this->getExtConf('max_transport_extra_data_size');
        $maxSize = $maxSize ?: \DMK\Mklog\Utility\EntryDataParserUtility::SIZE_8MB;

        return $maxSize;
    }

    /**
     * Th extension keys to exclude from logging.
     *
     * @return array
     */
    public function getExcludeExtKeys()
    {
        $extKeys = $this->getExtConf('exclude_ext_keys', []);

        if (!is_array($extKeys)) {
            $extKeys = GeneralUtility::trimExplode(',', $extKeys);
        }

        return $extKeys;
    }

    /**
     * Is the gelf logging enabled?
     */
    public function isGelfEnable(): bool
    {
        return (bool) $this->getExtConf('gelf_enable');
    }

    /**
     * Minimum log level for gelf logger.
     */
    public function getGelfMinLogLevel(): int
    {
        return (int) $this->getExtConf('gelf_min_log_level');
    }

    /**
     * Transport for gelf loging.
     */
    public function getGelfTransport(): string
    {
        return $this->getExtConf('gelf_transport') ?: \DMK\Mklog\WatchDog\Transport\Gelf\UdpGelf::class;
    }

    /**
     * Credentials for gelf loging.
     */
    public function getGelfCredentials(): string
    {
        return $this->getExtConf('gelf_credentials', '');
    }

    /**
     * The global from mail address.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getGlobalMailFrom(): string
    {
        $mail = $this->getExtConf('from_mail', $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? '');

        return $mail;
    }
}
