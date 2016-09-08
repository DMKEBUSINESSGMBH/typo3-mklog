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
		if (empty($taskInfo['mklog_watchdog_transport'])) {
			if ($parentObject->CMD == 'edit') {
				// Editing a task, set to internal value if data was not submitted already
				$taskInfo['mklog_watchdog_transport'] = $task->getOptions()->getTransport();
				$taskInfo['mklog_watchdog_credentials'] = $task->getOptions()->getCredentials();
				$taskInfo['mklog_watchdog_severity'] = $task->getOptions()->getSeverity();
				$taskInfo['mklog_watchdog_messagelimit'] = $task->getOptions()->getMessageLimit();
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['mklog_watchdog_transport'] = '';
				$taskInfo['mklog_watchdog_credentials'] = '';
				$taskInfo['mklog_watchdog_severity'] = \DMK\Mklog\Utility\SeverityUtility::DEBUG;
				$taskInfo['mklog_watchdog_messagelimit'] = '100';
			}
		}

		// Write the code for the field
		$additionalFields = array();

		$additionalFields['field_mklog_watchdog_transport'] = $this->getTransportField($taskInfo);
		$additionalFields['field_mklog_watchdog_credentials'] = $this->getCredentialsField($taskInfo);
		$additionalFields['field_mklog_watchdog_severity'] = $this->getSeverityField($taskInfo);
		$additionalFields['field_mklog_watchdog_messagelimit'] = $this->getMessageLimitField($taskInfo);

		return $additionalFields;
	}

	/**
	 * Creates the transport drop down
	 *
	 * @param array $taskInfo
	 *
	 * @return array
	 */
	protected function getTransportField(
		array &$taskInfo
	) {
		$fieldCode = '<select ' .
			'name="tx_scheduler[mklog_watchdog_transport]" ' .
			'id="field_mklog_watchdog_transport" ' .
		'>';

		foreach (array(
			'Mail' => array(
				'\DMK\Mklog\WatchDog\Transport\MailTransport' => 'Mail Message',
			),
			'Gelf (GrayLog)' => array(
				'\DMK\Mklog\WatchDog\Transport\Gelf\HttpGelf' => 'Gelf HTTP',
				'\DMK\Mklog\WatchDog\Transport\Gelf\UdpGelf' => 'Gelf UDP',
			),
		) as $group => $subs) {
			$fieldCode .= '<optgroup label="' . $group . '">';
			foreach ($subs as $key => $label) {
				$fieldCode .= sprintf(
					'<option value="%1$s" %3$s />%2$s</option>',
					$key,
					$label,
					$taskInfo['mklog_watchdog_transport'] == $key ? 'selected="selected"' : ''
				);
			}
			$fieldCode .= '</optgroup>';
		}
		$fieldCode .= '</select>';

		return array(
			'code' => $fieldCode,
			'label'  => 'Transport',
		);
	}

	/**
	 * Creates the credentials input field
	 *
	 * @param array $taskInfo
	 *
	 * @return array
	 */
	protected function getCredentialsField(
		array &$taskInfo
	) {
		$fieldCode = '<input ' .
			'type="text" ' .
			'name="tx_scheduler[mklog_watchdog_credentials]" ' .
			'id="field_mklog_watchdog_credentials" ' .
			'value="' . $taskInfo['mklog_watchdog_credentials'] . '" ' .
			'size="50" />';

		return array(
			'code' => $fieldCode,
			'label' => 'Credentials',
		);
	}

	/**
	 * Creates the severity drop down
	 *
	 * @param array $taskInfo
	 *
	 * @return array
	 */
	protected function getSeverityField(
		array &$taskInfo
	) {
		// Transport
		$fieldCode = '<select ' .
			'name="tx_scheduler[mklog_watchdog_severity]" ' .
			'id="field_mklog_watchdog_severity" ' .
		'>';

		$levels = \DMK\Mklog\Utility\SeverityUtility::getItems();

		foreach ($levels as $key => $label) {
			$fieldCode .= sprintf(
				'<option value="%1$s" %3$s />%2$s</option>',
				$key,
				$label,
				$taskInfo['mklog_watchdog_severity'] == $key ? 'selected="selected"' : ''
			);
		}
		$fieldCode .= '</select>';

		return array(
			'code' => $fieldCode,
			'label'  => 'Min Severity',
		);
	}

	/**
	 * Creates the transport drop down
	 *
	 * @param array $taskInfo
	 *
	 * @return array
	 */
	protected function getMessageLimitField(
		array &$taskInfo
	) {
		$fieldCode = '<input ' .
			'type="text" ' .
			'name="tx_scheduler[mklog_watchdog_messagelimit]" ' .
			'id="field_mklog_watchdog_messagelimit" ' .
			'value="' . $taskInfo['mklog_watchdog_messagelimit'] . '" ' .
			'size="50" />';

		return array(
			'code' => $fieldCode,
			'label' => 'Message limit per run',
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

		$credentials = &$submittedData['mklog_watchdog_credentials'];
		$credentials = trim($credentials);
		if (empty($credentials)) {
			$flashMessageClass = \tx_rnbase_util_Typo3Classes::getFlashMessageClass();
			$schedulerModule->addMessage(
				'The credentials for the transport are required!',
				$flashMessageClass::ERROR
			);

			return false;
		}

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
		($task->getOptions()
			->setTransport($submittedData['mklog_watchdog_transport'])
			->setCredentials($submittedData['mklog_watchdog_credentials'])
			->setSeverity((int) $submittedData['mklog_watchdog_severity'])
			->setMessageLimit((int) $submittedData['mklog_watchdog_messagelimit'])
		);
	}
}
