<?php
namespace DMK\Mklog\Logger;

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

use \DMK\Mklog\Utility\SeverityUtility;

/**
 * Devlog logger
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogLogger
	implements \TYPO3\CMS\Core\Log\Writer\WriterInterface
{
	/**
	 * Constructs this log writer
	 *
	 * @param array $options Configuration options - depends on the actual log writer
	 *
	 * @return void
	 */
	public function __construct(
		array $options = array()
	) {
		// $this->options = \Tx_Rnbase_Domain_Model_Data::getInstance($options);
	}

	/**
	 * Writes the log record
	 *
	 * @param \TYPO3\CMS\Core\Log\LogRecord $record Log record
	 *
	 * @return WriterInterface $this
	 */
	public function writeLog(
		\TYPO3\CMS\Core\Log\LogRecord $record
	) {
		$this->storeLog(
			$record->getMessage(),
			$record->getComponent(),
			$record->getLevel(),
			$record->getData()
		);

		return $this;
	}

	/**
	 * Stores a devlog entry
	 *
	 * @param string $message
	 * @param string $extension
	 * @param int $severity
	 * @param mixed $extraData
	 *
	 * @return void
	 */
	protected function storeLog($message, $extension, $severity, $extraData)
	{
		if (!$this->isLoggingEnabled()) {
			return;
		}

		$config = \DMK\Mklog\Factory::getConfigUtility();

		// check min log level
		if ($severity > $config->getMinLogLevel()) {
			return;
		}

		// check exclude extension keys
		if (in_array($extension, $config->getExcludeExtKeys())) {
			return;
		}

		$repo = $this->getDevlogEntryRepository();

		// optimize the log table
		$repo->optimize();

		/* @var $entry \DMK\Mklog\Domain\Model\DevlogEntryModel */
		$entry = $repo->createNewModel();
		$entry->setCrdate(time());
		$entry->setRunId($config->getCurrentRunId());
		$entry->setMessage($message);
		$entry->setExtKey($extension);
		$entry->setSeverity($severity);
		$entry->setPid(0);

		if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE'])) {
			$entry->setPid($GLOBALS['TSFE']->id);
		}

		$entry->setCruserId(0);
		if (!empty($GLOBALS['BE_USER']->user['uid'])) {
			$entry->setCruserId($GLOBALS['BE_USER']->user['uid']);
		}

		if (!empty($extraData)) {
			// @TODO: use an converter!
			$extraData = json_encode($extraData, JSON_FORCE_OBJECT);
			$entry->setExtraData($extraData);
		}

		$repo->persist(
			$entry,
			array(
				'skip_tca_column_elimination' => true,
			)
		);
	}

	/**
	 * Old devlog Hook from the old TYPO3 API
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function devLogHook(array $params)
	{
		\tx_rnbase::load('tx_rnbase_util_Logger');
		// map the old log levels to the new one
		switch ((int) $params['severity']) {
			case \tx_rnbase_util_Logger::LOGLEVEL_DEBUG:
				$params['severity'] = SeverityUtility::DEBUG;
				break;
			case \tx_rnbase_util_Logger::LOGLEVEL_INFO:
				$params['severity'] = SeverityUtility::INFO;
				break;
			case \tx_rnbase_util_Logger::LOGLEVEL_NOTICE:
				$params['severity'] = SeverityUtility::NOTICE;
				break;
			case \tx_rnbase_util_Logger::LOGLEVEL_WARN:
				$params['severity'] = SeverityUtility::WARNING;
				break;
			case \tx_rnbase_util_Logger::LOGLEVEL_FATAL:
				$params['severity'] = SeverityUtility::CRITICAL;
				break;
		}

		$this->storeLog(
			$params['msg'],
			$params['extKey'],
			$params['severity'],
			$params['dataVar']
		);
	}

	/**
	 * Is logging enabled?
	 *
	 * @return bool
	 */
	protected function isLoggingEnabled()
	{
		// skip logging, if there is no db.
		if (empty($GLOBALS['TYPO3_DB']) || !is_object($GLOBALS['TYPO3_DB'])) {
			return false;
		}

		// skip if logging is disabled
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklog']['nolog']) {
			return false;
		}

		// now check some cachable options

		$storage = \DMK\Mklog\Factory::getStorage();

		if ($storage->hasLoggingActive()) {
			return $storage->getLoggingActive();
		}

		$repo = \DMK\Mklog\Factory::getDevlogEntryRepository();
		$config = \DMK\Mklog\Factory::getConfigUtility();

		$storage->setLoggingActive(true);

		if (!$config->getEnableDevLog()) {
			$storage->setLoggingActive(false);
		} elseif (!$repo->isTableAvailable()) {
			// check for exsisting db table
			$storage->setLoggingActive(false);
		}

		return $storage->getLoggingActive();
	}

	/**
	 * Returns the devlog entry repository
	 *
	 * @return \DMK\Mklog\Domain\Repository\DevlogEntryRepository
	 */
	protected function getDevlogEntryRepository()
	{
		return \DMK\Mklog\Factory::getDevlogEntryRepository();
	}
}
