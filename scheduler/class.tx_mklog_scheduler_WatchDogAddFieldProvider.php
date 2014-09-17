<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 René Nitzsche <dev@dmk-ebusiness.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php';
if (!interface_exists('tx_scheduler_AdditionalFieldProvider')) {
	require_once t3lib_extMgm::extPath(
		'scheduler', '/interfaces/interface.tx_scheduler_additionalfieldprovider.php'
	);
}
tx_rnbase::load('tx_rnbase_util_Misc');

define('MKLOG_FIELD_EMAIL', 'mklog_email');
define('MKLOG_FIELD_SEVERITY', 'mklog_severity');
define('MKLOG_FIELD_FORCE', 'mklog_force');
define('MKLOG_FIELD_DATAVAR', 'mklog_datavar');

/**
 *
 */
class tx_mklog_scheduler_WatchDogAddFieldProvider implements tx_scheduler_AdditionalFieldProvider {

	/**
	 * This method is used to define new fields for adding or editing a task
	 * In this case, it adds an email field
	 *
	 * @param	array					$taskInfo: reference to the array containing the info used in the add/edit form
	 * @param	object					$task: when editing, reference to the current task object. Null when adding.
	 * @param	tx_scheduler_Module		$parentObject: reference to the calling object (Scheduler's BE module)
	 * @return	array					Array containg all the information pertaining to the additional fields
	 *									The array is multidimensional, keyed to the task class name and each field's id
	 *									For each field it provides an associative sub-array with the following:
	 *										['code']		=> The HTML code for the field
	 *										['label']		=> The label of the field (possibly localized)
	 *										['cshKey']		=> The CSH key for the field
	 *										['cshLabel']	=> The code of the CSH label
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {


		// Initialize extra field value
		if (!array_key_exists(MKLOG_FIELD_EMAIL, $taskInfo) || empty($taskInfo[MKLOG_FIELD_EMAIL])) {
			if ($parentObject->CMD == 'add') {
				// New task
				$taskInfo[MKLOG_FIELD_EMAIL] = '';

			} elseif ($parentObject->CMD == 'edit') {
				// Editing a task, set to internal value if data was not submitted already
				$taskInfo[MKLOG_FIELD_EMAIL] = $task->getEmailReceiver();
				$taskInfo[MKLOG_FIELD_FORCE] = $task->getForceSummaryMail();
				$taskInfo[MKLOG_FIELD_SEVERITY] = $task->getMinimalSeverity();
				$taskInfo[MKLOG_FIELD_DATAVAR] = $task->getIncludeDataVar();
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo[MKLOG_FIELD_EMAIL] = '';
			}
		}

		// Write the code for the field
		$additionalFields = array();

		// Email
		$fieldID = 'field_'.MKLOG_FIELD_EMAIL;
		// Note: Name qualifier MUST be "tx_scheduler" as the tx_scheduler's BE module is used!
		$fieldCode = '<input type="text" name="tx_scheduler['.MKLOG_FIELD_EMAIL.']" id="' . $fieldID .
						'" value="' . $taskInfo[MKLOG_FIELD_EMAIL] . '" size="50" />';
		$additionalFields[$fieldID] = array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:mklog/locallang_db.xml:scheduler_watchdog_field_'.MKLOG_FIELD_EMAIL,
			'cshKey'   => '_MOD_tools_txschedulerM1',
//			'cshLabel' => $fieldID
		);

		// Minimum severity
		$fieldID = 'field_'.MKLOG_FIELD_SEVERITY;
		$fieldCode = '<select name="tx_scheduler['.MKLOG_FIELD_SEVERITY.']" id="' . $fieldID . '">';
		$srv = tx_rnbase_util_Misc::getService('mklog', 'WatchDog');
		$severities = $srv->getSeverities();

		foreach($severities As $key => $label) {
			$fieldCode .= '<option value="'.$key.'" ' . ($taskInfo[MKLOG_FIELD_SEVERITY] == $key ? 'selected="selected"' : '') . ' />' . $label . "</option>\n";
		}
		$fieldCode .= '</select>';
		$additionalFields[$fieldID] = array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:mklog/locallang_db.xml:scheduler_watchdog_field_'.MKLOG_FIELD_SEVERITY,
			'cshKey'   => '_MOD_tools_txschedulerM1',
		);

		// Force summary
		$fieldID = 'field_'.MKLOG_FIELD_FORCE;
		$fieldCode = '<input type="radio" name="tx_scheduler['.MKLOG_FIELD_FORCE.']" id="' . $fieldID .
						'" value="1" ' . ($taskInfo[MKLOG_FIELD_FORCE] ? 'checked="checked"' : '') . ' /> Yes';
		$fieldCode .= '<input type="radio" name="tx_scheduler['.MKLOG_FIELD_FORCE.']" id="' . $fieldID .
						'" value="0" ' . ($taskInfo[MKLOG_FIELD_FORCE] ? '':'checked="checked"') . ' /> No';
		$additionalFields[$fieldID] = array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:mklog/locallang_db.xml:scheduler_watchdog_field_'.MKLOG_FIELD_FORCE,
			'cshKey'   => '_MOD_tools_txschedulerM1',
		);

		// data_var
		$fieldID = 'field_'.MKLOG_FIELD_DATAVAR;
		$fieldCode = '<input type="radio" name="tx_scheduler['.MKLOG_FIELD_DATAVAR.']" id="' . $fieldID .
						'" value="1" ' . ($taskInfo[MKLOG_FIELD_DATAVAR] ? 'checked="checked"' : '') . ' /> Yes';
		$fieldCode .= '<input type="radio" name="tx_scheduler['.MKLOG_FIELD_DATAVAR.']" id="' . $fieldID .
						'" value="0" ' . ($taskInfo[MKLOG_FIELD_DATAVAR] ? '':'checked="checked"') . ' /> No';
		$additionalFields[$fieldID] = array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:mklog/locallang_db.xml:scheduler_watchdog_field_'.MKLOG_FIELD_DATAVAR,
			'cshKey'   => '_MOD_tools_txschedulerM1',
		);

		return $additionalFields;
	}

	/**
	 * This method checks any additional data that is relevant to the specific task
	 * If the task class is not relevant, the method is expected to return true
	 *
	 * @param	array					$submittedData: reference to the array containing the data submitted by the user
	 * @param	tx_scheduler_Module		$parentObject: reference to the calling object (Scheduler's BE module)
	 * @return	boolean					True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$submittedData[MKLOG_FIELD_EMAIL] = trim($submittedData[MKLOG_FIELD_EMAIL]);
		if (empty($submittedData[MKLOG_FIELD_EMAIL])) {
			$parentObject->addMessage($GLOBALS['LANG']->sL('LLL:EXT:scheduler/mod1/locallang.xml:msg.noEmail'), t3lib_FlashMessage::ERROR);
			return false;
		}

		return true;
	}

	/**
	 * This method is used to save any additional input into the current task object
	 * if the task class matches
	 *
	 * @param	array				$submittedData: array containing the data submitted by the user
	 * @param	tx_mklog_scheduler_WatchDog	$task: reference to the current task object
	 * @return	void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->setEmailReceiver($submittedData[MKLOG_FIELD_EMAIL]);
		$task->setForceSummaryMail($submittedData[MKLOG_FIELD_FORCE]);
		$task->setMinimalSeverity($submittedData[MKLOG_FIELD_SEVERITY]);
		$task->setIncludeDataVar($submittedData[MKLOG_FIELD_DATAVAR]);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklog/scheduler/class.tx_mklog_scheduler_WatchDogAddFieldProvider.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklog/scheduler/class.tx_mklog_scheduler_WatchDogAddFieldProvider.php']);
}