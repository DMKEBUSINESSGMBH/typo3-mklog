<?php
namespace DMK\Mklog;

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
 * MK Log Factory
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
final class Factory
{
	/**
	 * Returns a storage
	 *
	 * @return Tx_Rnbase_Domain_Model_Data
	 */
	public static function getStorage()
	{
		static $storage = null;

		if ($storage === null) {
			$storage = \tx_rnbase::makeInstance(
				'Tx_Rnbase_Domain_Model_Data'
			);
		}

		return $storage;
	}

	/**
	 * Returns a cache
	 *
	 * @return tx_rnbase_cache_ICache
	 */
	public static function getCache()
	{
		\tx_rnbase::load('tx_rnbase_cache_Manager');

		return \tx_rnbase_cache_Manager::getCache('mklog');
	}

	/**
	 * Returns the config
	 *
	 * @return \DMK\Mklog\Utility\ConfigUtility
	 */
	public static function getConfigUtility()
	{
		$storage = self::getStorage();
		if (!$storage->hasConfigUtility()) {
			$storage->setConfigUtility(
				\tx_rnbase::makeInstance(
					'DMK\\Mklog\\Utility\\ConfigUtility'
				)
			);
		}

		return $storage->getConfigUtility();
	}

	/**
	 * Returns the data converter
	 *
	 * @return \DMK\Mklog\Utility\DataConverterUtility
	 */
	public static function getDataConverterUtility()
	{
		$storage = self::getStorage();
		if (!$storage->hasDataConverterUtility()) {
			$storage->setDataConverterUtility(
				\tx_rnbase::makeInstance(
					'DMK\\Mklog\\Utility\\DataConverterUtility'
				)
			);
		}

		return $storage->getDataConverterUtility();
	}

	/**
	 * Returns the devlog entry repository
	 *
	 * @return \DMK\Mklog\Domain\Repository\DevlogEntryRepository
	 */
	public static function getDevlogEntryRepository()
	{
		return \tx_rnbase::makeInstance(
			'DMK\\Mklog\\Domain\\Repository\\DevlogEntryRepository'
		);
	}
}
