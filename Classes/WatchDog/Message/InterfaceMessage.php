<?php
namespace DMK\Mklog\WatchDog\Message;

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
 * MK Log watchdog message iterface
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *		  GNU Lesser General Public License, version 3 or later
 */
interface InterfaceMessage
{
	/**
	 * Returns the short text of the message
	 *
	 * @return string
	 */
	public function getShortMessage();

	/**
	 * Returns the full text of the message
	 *
	 * @return string
	 */
	public function getFullMessage();

	/**
	 * Returns the timestamp of the message as a datetime object
	 *
	 * @return \DateTime
	 */
	public function getTimestamp();

	/**
	 * Returns the log level of the message as a Psr\Log\Level-constant
	 *
	 * @return string
	 */
	public function getLevel();

	/**
	 * Returns the facility of the message
	 *
	 * @return string
	 */
	public function getFacility();

	/**
	 * Returns the facility of the message
	 *
	 * @return string
	 */
	public function getHost();

	/**
	 * Returns the value of the additional field of the message
	 *
	 * @return mixed
	 */
	public function getAdditionalData();
}
