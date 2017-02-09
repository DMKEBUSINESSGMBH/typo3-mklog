<?php
namespace DMK\Mklog\Utility;

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

\tx_rnbase::load('Tx_Rnbase_Interface_Singleton');

/**
 * MK Log Factory
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ConfigUtility
	implements \Tx_Rnbase_Interface_Singleton
{
	/**
	 * Internal config storage
	 *
	 * @var Tx_Rnbase_Domain_Model_Data
	 */
	private $storage = null;

	/**
	 * Returns a storage
	 *
	 * @return Tx_Rnbase_Domain_Model_Data
	 */
	private function getStorage()
	{
		if ($this->storage === null) {
			$this->storage = \tx_rnbase::makeInstance(
				'Tx_Rnbase_Domain_Model_Data'
			);
		}

		return $this->storage;
	}

	/**
	 * Is the devlog enabled?
	 *
	 * @return \Tx_Rnbase_Domain_Model_Data
	 */
	public function getExtConf()
	{
		if (!$this->getStorage()->hasExtConf()) {
			$this->getStorage()->setExtConf(
				\Tx_Rnbase_Domain_Model_Data::getInstance(
					unserialize(
						$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mklog']
					)
				)
			);
		}

		return $this->getStorage()->getExtConf();
	}

	/**
	 * The current run id
	 *
	 * @return int
	 */
	public function getCurrentRunId()
	{
		if (!$this->getStorage()->hasDevLogCurrentRunId()) {
			list($sec, $usec) = explode('.', (string) microtime(true));
			// miliseconds has to be exactly 6 sings long. otherwise the resulting number is too small.
			$usec = $usec . str_repeat('0', 6 - strlen($usec));
			$this->getStorage()->setDevLogCurrentRunId($sec . $usec);
		}

		return $this->getStorage()->getDevLogCurrentRunId();
	}

	/**
	 * Is the devlog enabled?
	 *
	 * @return bool
	 */
	public function getEnableDevLog()
	{
		return (bool) $this->getExtConf()->getEnableDevlog();
	}

	/**
	 * Minimum log level to log
	 *
	 * @return int
	 */
	public function getMinLogLevel()
	{
		return (int) $this->getExtConf()->getMinLogLevel();
	}

	/**
	 * Max rows to keep after cleanup
	 *
	 * @return int
	 */
	public function getMaxLogs()
	{
		return (int) $this->getExtConf()->getMaxLogs();
	}

	/**
	 * Th extension keys to exclude from logging
	 *
	 * @return array
	 */
	public function getExcludeExtKeys()
	{
		$extKeys = $this->getExtConf()->getExcludeExtKeys();
		if (!is_array($extKeys)) {
			\tx_rnbase::load('Tx_Rnbase_Utility_Strings');
			$extKeys = \Tx_Rnbase_Utility_Strings::trimExplode(',', $extKeys);
			$this->getExtConf()->setExcludeExtKeys($extKeys);
		}

		return $this->getExtConf()->getExcludeExtKeys();
	}

	/**
	 * Is the gelf logging enabled?
	 *
	 * @return bool
	 */
	public function getGelfEnable()
	{
		return (bool) $this->getExtConf()->getGelfEnable();
	}

	/**
	 * Minimum log level for gelf logger
	 *
	 * @return int
	 */
	public function getGelfMinLogLevel()
	{
		return (int) $this->getExtConf()->getGelfMinLogLevel();
	}

	/**
	 * Credentials for gelf loging
	 *
	 * @return int
	 */
	public function getGelfCredentials()
	{
		return $this->getExtConf()->getGelfCredentials();
	}

	/**
	 * The global from mail address
	 *
	 * @return array
	 */
	public function getGlobalMailFrom()
	{
		return \tx_rnbase_configurations::getExtensionCfgValue(
			'rn_base',
			'fromEmail'
		);
	}
}
