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
	 * @var \Tx_Rnbase_Domain_Model_Data
	 */
	private $options = null;

	/**
	 * Returns a storage
	 *
	 * @return \Tx_Rnbase_Domain_Model_Data
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
		$transport = $this->getTransport();

		// initialize the transport
		$transport->initialize($this->getOptions());

		// @TODO: find only new entrys, not all!
		foreach ($this->findMessages() as $message) {
			try {
				$transport->publish($message);
			} catch (\Exception $e) {
				\tx_rnbase::load('tx_rnbase_util_Logger');
				\tx_rnbase_util_Logger::fatal(
					'Message could not be send',
					'mklog',
					array(
						'transport' => get_class($transport),
						'exception' => array(
							'message' => $e->getMessage(),
							'trase' => $e->getTraceAsString(),
							'__string' => $e->__toString(),
						),
					)
				);
			}
		}

		// shutdown the transport
		$transport->shutdown();

		// @TODO: mark logs as deleted!

		return true;
	}

	/**
	 * Returns all untransportet messages
	 *
	 * @return \Tx_Rnbase_Domain_Collection_Base
	 */
	protected function findMessages()
	{
		$repo = \DMK\Mklog\Factory::getDevlogEntryRepository();

		$fields = $options = array();

		if ($this->getOptions()->getSeverity()) {
			$fields['DEVLOGENTRY.severity'][OP_LTEQ_INT] = $this->getOptions()->getSeverity();
		}

		return $repo->search($fields, $options);
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

	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation()
	{
		if ($this->getOptions()->isEmpty()) {
			return '';
		}

		\tx_rnbase::load('Tx_Rnbase_Utility_Strings');

		$options = array();

		foreach ($this->getOptions() as $key => $value) {
			$key = \Tx_Rnbase_Utility_Strings::underscoredToLowerCamelCase($key);
			$options[] = ucfirst($key) . ': ' . $value;
		}

		return 'Options: ' . implode('; ', $options);
	}
}
