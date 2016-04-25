<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Tx_Mklog_Utility_Devlog
 *
 * @package 		TYPO3
 * @subpackage	 	mklog
 * @author 			Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklog_Utility_Devlog {

	/**
	 * @return string
	 */
	static public function getTableName() {
		return self::getValueByDevlogExtensionVersion('tx_devlog', 'tx_devlog_domain_model_entry');
	}

	/**
	 * @return string
	 */
	static public function getMessageFieldName() {
		return self::getValueByDevlogExtensionVersion('msg', 'message');
	}

	/**
	 * @return string
	 */
	static public function getExtraDataFieldName() {
		return self::getValueByDevlogExtensionVersion('data_var', 'extra_data');
	}

	/**
	 * @param string $extraData
	 * @return array
	 */
	static function getExtraDataAsArray($extraData) {
		if (tx_rnbase_util_TYPO3::isExtMinVersion('devlog', '3000000')) {
			$extraData = unserialize(gzuncompress($extraData));
		} else {
			$extraData = unserialize($extraData);
		}

		return $extraData;
	}

	/**
	 * @param unknown $valueBeforeVersion3
	 * @param unknown $valueSinceVersion3
	 *
	 * @return string
	 */
	static protected function getValueByDevlogExtensionVersion($valueBeforeVersion3, $valueSinceVersion3) {
		if (tx_rnbase_util_TYPO3::isExtMinVersion('devlog', '3000000')) {
			$value = $valueSinceVersion3;
		} else {
			$value = $valueBeforeVersion3;
		}

		return $value;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Logger.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Logger.php']);
}