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

\tx_rnbase::load('Tx_Rnbase_Scheduler_FieldProvider');

/**
 * MK Log watchdog scheduler fields
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SchedulerFieldProviderWatchDog
	extends \Tx_Rnbase_Scheduler_FieldProvider
{
	/**
	 * This method is used to define new fields for adding or editing a task
	 * In this case, it adds an email field
	 *
	 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
	 * @param object $task When editing, reference to the current task object. Null when adding.
	 * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
	 *
	 * @return array Array Containg all the information pertaining to the additional fields
	 *               The array is multidimensional, keyed to the task class name and each field's id
	 *               For each field it provides an associative sub-array with the following:
	 *                   ['code']     => The HTML code for the field
	 *                   ['label']    => The label of the field (possibly localized)
	 *                   ['cshKey']   => The CSH key for the field
	 *                   ['cshLabel'] => The code of the CSH label
	 */
	// @codingStandardsIgnoreStart (interface/abstract mistake)
	protected function _getAdditionalFields(
		array &$taskInfo,
		$task,
		$parentObject
	) {
		// @codingStandardsIgnoreEnd

		// Initialize extra field value
		if ((
			!array_key_exists('mklog_watchdog_transport', $taskInfo) ||
			empty($taskInfo['mklog_watchdog_transport'])
		)) {
			if ($parentObject->CMD == 'add') {
				// New task
				$taskInfo['mklog_watchdog_transport'] = '';

			} elseif ($parentObject->CMD == 'edit') {
				// Editing a task, set to internal value if data was not submitted already
				$taskInfo['mklog_watchdog_transport'] = $task->getOptions()->getTransport();
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['mklog_watchdog_transport'] = '';
			}
		}

		// Write the code for the field
		$additionalFields = array();

		$additionalFields['field_mklog_watchdog_transport'] = $this->getTransportField($taskInfo);

		return $additionalFields;
	}

	/**
	 * Creates the Transport drop down
	 *
	 * @param array $taskInfo
	 *
	 * @return array
	 */
	protected function getTransportField(
		array &$taskInfo
	) {
		// Transport
		$fieldCode = '<select name="tx_scheduler[mklog_watchdog_transport]" id="field_mklog_watchdog_transport">';

		foreach (array(
			'\DMK\Mklog\WatchDog\Transport\MailTransport' => 'Mail',

		) as $key => $label) {
			$fieldCode .= sprintf(
				'<option value="%1$s" %3$s />%2$s</option>',
				$key,
				$label,
				$taskInfo['mklog_watchdog_transport'] == $key ? 'selected="selected"' : ''
			);
		}
		$fieldCode .= '</select>';

		return array(
			'code' => $fieldCode,
			'label'  => 'Transport',
		);
	}

	/**
	 * This method checks any additional data that is relevant to the specific task
	 * If the task class is not relevant, the method is expected to return true
	 *
	 * @param array $submittedData Reference to the array containing the data submitted by the user
	 * @param tx_scheduler_Module $scheduler Module Reference to the calling object
	 *
	 * @return bool True if validation was ok (or selected class is not relevant), false otherwise
	 */
	// @codingStandardsIgnoreStart (interface/abstract mistake)
	protected function _validateAdditionalFields(
		array &$submittedData,
		$schedulerModule
	) {
		// @codingStandardsIgnoreEnd
		return true;
	}

	/**
	 * This method is used to save any additional input into the current task object
	 * if the task class matches
	 *
	 * @param array $submittedData Array containing the data submitted by the user
	 * @param Tx_Rnbase_Scheduler_Task $task Reference to the current task object
	 *
	 * @return void
	 */
	// @codingStandardsIgnoreStart (interface/abstract mistake)
	protected function _saveAdditionalFields(
		array $submittedData,
		\Tx_Rnbase_Scheduler_Task $task
	) {
		// @codingStandardsIgnoreEnd
		$task->getOptions()->setTransport($submittedData['mklog_watchdog_transport']);
	}
}
