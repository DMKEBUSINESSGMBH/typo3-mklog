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
class GelfLogger
	extends AbstractLogger
{
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
		$config = \DMK\Mklog\Factory::getConfigUtility();

		// check min log level
		if ((
			!$config->getGelfEnable() ||
			!$config->getGelfCredentials() ||
			$record->getLevel() > $config->getGelfMinLogLevel()
		)) {
			return $this;
		}

		$options = \tx_rnbase::makeInstance(
			'Tx_Rnbase_Domain_Model_Data',
			array(
				'credentials' => $config->getGelfCredentials(),
			)
		);

		$transport = $this->getTransport();

		$transport->initialize($options);

		$message = $this->createDevlogEntry(
			$record->getMessage(),
			$record->getComponent(),
			$record->getLevel(),
			$record->getData()
		);

		try {
			$transport->publish($message);
		} catch (\Exception $e) {
			// what todo on transport exception?
			// usualy we have a emergency and a other logger (file or mail) shold take over
			return $this;
		}

		$transport->shutdown();

		return $this;
	}

	/**
	 * Creates the transport
	 *
	 * @return \DMK\Mklog\WatchDog\Transport\InterfaceTransport
	 */
	protected function getTransport()
	{
		return \tx_rnbase::makeInstance(
			'DMK\Mklog\WatchDog\Transport\Gelf\UdpGelf'
		);
	}
}
