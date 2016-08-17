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

/**
 * Devlog logger
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractLogger
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
	 * Stores a devlog entry
	 *
	 * @param string $message
	 * @param string $extension
	 * @param int $severity
	 * @param mixed $extraData
	 *
	 * @return \DMK\Mklog\Domain\Model\DevlogEntryModel
	 */
	protected function createDevlogEntry($message, $extension, $severity, $extraData)
	{
		$config = \DMK\Mklog\Factory::getConfigUtility();

		$repo = $this->getDevlogEntryRepository();

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

		return $entry;
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
