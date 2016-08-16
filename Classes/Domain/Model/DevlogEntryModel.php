<?php
namespace DMK\Mklog\Domain\Model;

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

\tx_rnbase::load('Tx_Rnbase_Domain_Model_Base');

/**
 * Devlog entry Model
 *
 * @method int getPid()
 * @method DevlogEntryModel setPid() setPid(int $pid)
 * @method bool hasPid()
 *
 * @method int getRunId()
 * @method DevlogEntryModel setRunId() setRunId(int $runId)
 * @method bool hasRunId()
 *
 * @method string getExtKey()
 * @method DevlogEntryModel setExtKey() setExtKey(string $extKey)
 * @method bool hasExtKey()
 *
 * @method string getMessage()
 * @method DevlogEntryModel setMessage() setMessage(string $message)
 * @method bool hasMessage()
 *
 * @method string getSeverity()
 * @method DevlogEntryModel setSeverity() setSeverity(string $severity)
 * @method bool hasSeverity()
 *
 * @method int getCruserId()
 * @method DevlogEntryModel setCruserId() setCruserId(int $cruserId)
 * @method bool hasCruserId()
 *
 * @method string getExtraData()
 * @method DevlogEntryModel setExtraData() setExtraData(string $extraData)
 * @method bool hasExtraData()
 *
 * @method int getCruserId()
 * @method DevlogEntryModel setCruserId() setCruserId(int $cruserId)
 * @method bool hasCruserId()
 *
 * @method int getCrdate()
 * @method DevlogEntryModel setCrdate() setCrdate(int $crdate)
 * @method bool hasCrdate()
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryModel
	extends \Tx_Rnbase_Domain_Model_Base implements \DMK\Mklog\WatchDog\Message\InterfaceMessage
{
	/**
	 * Liefert den aktuellen Tabellenname
	 *
	 * @return Tabellenname als String
	 */
	public function getTableName()
	{
		return 'tx_mklog_devlog_entry';
	}

	/* *** ******************************************** *** *
	 * *** \DMK\Mklog\WatchDog\Message\InterfaceMessage *** *
	 * *** ******************************************** *** */

	/**
	 * Returns the short text of the message
	 *
	 * @return string
	 */
	public function getShortMessage()
	{
		return $this->getMessage();
	}

	/**
	 * Returns the full text of the message
	 *
	 * @return string
	 */
	public function getFullMessage()
	{
		return $this->getExtraData();
	}

	/**
	 * Returns the timestamp of the message
	 *
	 * @return float
	 */
	public function getTimestamp()
	{
		return $this->getCrdate();
	}

	/**
	 * Returns the log level of the message as a Psr\Log\Level-constant
	 *
	 * @return string
	 */
	public function getLevel()
	{
		return \DMK\Mklog\Utility\SeverityUtility::getPsrLevelConstant(
			$this->getSeverity()
		);
	}

	/**
	 * Returns the facility of the message
	 *
	 * @return string
	 */
	public function getFacility()
	{
		return $this->getExtKey();
	}

	/**
	 * Returns the host of the message
	 *
	 * @return string
	 */
	public function getHost()
	{
		$utility = \tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();

		return $utility::getIndpEnv('TYPO3_HOST_ONLY');
	}

	/**
	 * Returns the value of the additional field of the message
	 *
	 * @return mixed
	 */
	public function getAdditionalData()
	{
		return '';
	}
}
