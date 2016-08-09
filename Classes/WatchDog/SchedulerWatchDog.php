<?php
namespace DMK\Mklog\WatchDog;

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

\tx_rnbase::load('Tx_Rnbase_Scheduler_Task');
\tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');

/**
 * MK Log watchdog
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SchedulerWatchDog
	extends \Tx_Rnbase_Scheduler_Task
{
	/**
	 * Internal options storage
	 *
	 * @var Tx_Rnbase_Domain_Model_Data
	 */
	private $options = null;

	/**
	 * Returns a storage
	 *
	 * @return Tx_Rnbase_Domain_Model_Data
	 */
	public function getOptions()
	{
		if ($this->options === null) {
			$this->options = \tx_rnbase::makeInstance(
				'Tx_Rnbase_Domain_Model_Data'
			);
		}

		return $this->options;
	}

	/**
	 * Do the magic and publish all new messages thu the transport.
	 *
	 * @return bool Returns TRUE on successful execution, FALSE on error
	 */
	public function execute()
	{
		// @TODO: make configurable by the scheduler!
		$this->getOptions()->setMailTo('mwagner@localhost.net');

		$transport = $this->getTransport();

		// initialize the transport
		$transport->initialize($this->getOptions());

		$repo = \DMK\Mklog\Factory::getDevlogEntryRepository();
		// @TODO: find only new entrys, not all!
		foreach ($repo->findAll() as $message) {
			$transport->publish($message);
		}

		// shutdown the transport
		$transport->shutdown();

		// @TODO: mark logs as deleted!

		return true;
	}

	/**
	 * Creates the Transport
	 *
	 * @return \DMK\Mklog\WatchDog\Transport\InterfaceTransport
	 */
	protected function getTransport()
	{
		$class = $this->getOptions()->getTransport();
		$instance = \tx_rnbase::makeInstance($class);

		if (!$instance instanceof \DMK\Mklog\WatchDog\Transport\InterfaceTransport) {
			throw new \Exception(
				'The Transport "' . get_class($instance) . '" ' .
				'has to implement the "\DMK\Mklog\WatchDog\Transport\InterfaceTransport"'
			);
		}

		return $instance;
	}
}
