<?php

namespace DMK\Mklog\Utility;

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

use DMK\Mklog\Domain\Model\GenericArrayObject as ConfigObject;
use DMK\Mklog\Factory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
    private $storage = null;

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

            if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mklog'])) {
                $storage->setExtConf(
                    unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mklog'])
                );
            }

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
            $usec = $usec.str_repeat('0', 6 - strlen($usec));
            $storage->setDevLogCurrentRunId($sec.$usec);
        }

        return $storage->getDevLogCurrentRunId();
    }

    /**
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getSiteName(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
    }

    /**
     * Is the devlog enabled?
     *
     * @return bool
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
     *
     * @return bool
     */
    public function isGelfEnable(): bool
    {
        return (bool) $this->getExtConf('gelf_enable');
    }

    /**
     * Minimum log level for gelf logger.
     *
     * @return int
     */
    public function getGelfMinLogLevel(): int
    {
        return (int) $this->getExtConf('gelf_min_log_level');
    }

    /**
     * Transport for gelf loging.
     *
     * @return string
     */
    public function getGelfTransport(): string
    {
        return $this->getExtConf('gelf_transport') ?: 'DMK\Mklog\WatchDog\Transport\Gelf\UdpGelf';
    }

    /**
     * Credentials for gelf loging.
     *
     * @return string
     */
    public function getGelfCredentials(): string
    {
        return $this->getExtConf('gelf_credentials', '');
    }

    /**
     * The global from mail address.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getGlobalMailFrom(): string
    {
        $mail = $this->getExtConf('from_mail', $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']);

        // fallback to old variant if installed
        if (empty($mail) && ExtensionManagementUtility::isLoaded('rn_base')) {
            $mail = \tx_rnbase_configurations::getExtensionCfgValue(
                'rn_base',
                'fromEmail'
            );
        }

        return $mail;
    }
}
