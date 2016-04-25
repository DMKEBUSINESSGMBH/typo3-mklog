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
tx_rnbase::load('Tx_Rnbase_Scheduler_Task');
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_util_Logger');
tx_rnbase::load('Tx_Rnbase_Utility_Strings');
tx_rnbase::load('Tx_Mklog_Utility_Devlog');

/**
 * tx_mklog_scheduler_WatchDog
 *
 * @package 		TYPO3
 * @subpackage	 	mklog
 * @author 			René Nitzsche
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_mklog_scheduler_WatchDog extends Tx_Rnbase_Scheduler_Task {

	/**
	 * Email address(es) for notification mail
	 *
	 * @var	string
	 */
	 private $email;

	 /**
	  *
	  * @var int
	  */
	 private $severity;

	 /**
	  * @var boolean
	  */
	 private $force;

	 /**
	  * @var boolean
	  */
	 private $dataVar;

	 /**
	  * @var boolean
	  */
	 private $groupEntries;

	/**
	 * Function executed from the Scheduler.
	 * Sends an email
	 *
	 * @return	boolean
	 */
	public function execute() {
		$success = TRUE;
		$taskId = $this->taskUid;

		tx_rnbase::load('tx_rnbase_util_Misc');
		try {
			$lastRun = $this->getLastRunTime($taskId);
			/* @var $srv tx_mklog_srv_WatchDog */
			$srv = tx_rnbase_util_Misc::getService('mklog', 'WatchDog');
			$filters = array();
			$options = array();
			$options['minimalSeverity'] = $this->getMinimalSeverity();
			$options['forceSummaryMail'] = $this->getForceSummaryMail();
			$options['includeDataVar'] = $this->getIncludeDataVar();

			//damit jede Nachricht nur einmal kommt, auch wenn sie mehrmals vorhanden ist
			if ($this->getGroupEntries()) {
				$options['groupby'] = Tx_Mklog_Utility_Devlog::getMessageFieldName() . ',extkey';
				// wir wollen aber wissen wie oft jede Nachricht vorhanden ist
				$options['count'] = TRUE;
			}

			$srv->triggerMails($this->getEmailReceiver(), $lastRun, $filters, $options);
			$this->updateLastRunTime($taskId);

		} catch (Exception $e) {
			tx_rnbase_util_Logger::fatal(
				'WatchDog failed!', 'mklog', array('Exception' => $e->getMessage())
			);
			$success = FALSE;
		}
		return $success;
	}

	/**
	 * @param int $taskId
	 * @return DateTime
	 */
	protected function getLastRunTime($taskId) {
		$options = array();
		$options['enablefieldsoff'] = 1;
		$options['where'] = 'uid='.intval($taskId);
		$ret = tx_rnbase_util_DB::doSelect(
			'tx_mklog_lastrun', 'tx_scheduler_task', $options
		);
		$lastrun = new DateTime();
		if(count($ret)) {
			$lastrun = new DateTime($ret[0]['tx_mklog_lastrun']);
		}

		return $lastrun;
	}

	/**
	 * @param int $taskId
	 *
	 * @return int number of rows affected
	 */
	protected function updateLastRunTime($taskId) {
		$lastRun = new DateTime();
		return tx_rnbase_util_DB::doUpdate('tx_scheduler_task', 'uid='.intval($taskId),
			array('tx_mklog_lastrun' => $lastRun->format('Y-m-d H:i:s')
		));
	}

	/**
	 * @return string
	 */
	public function getEmailReceiver() {
		return $this->email;
	}

	/**
	 * @return int
	 */
	public function getMinimalSeverity() {
		return $this->severity;
	}

	/**
	 * @return boolean
	 */
	public function getForceSummaryMail() {
		return $this->force;
	}

	/**
	 * @return boolean
	 */
	public function getIncludeDataVar() {
		return $this->dataVar;
	}

	/**
	 * @return boolean
	 */
	public function getGroupEntries() {
		return isset($this->groupEntries) ? $this->groupEntries : TRUE;
	}

	/**
	 * @param string	$emails
	 * @return void
	 */
	public function setEmailReceiver($emails) {
		$emails = Tx_Rnbase_Utility_Strings::trimExplode(',', $emails);

		foreach($emails As $email) {
			if(!Tx_Rnbase_Utility_Strings::validEmail($email)) {
				throw new Exception(
					'tx_mklog_scheduler_WatchDog->setEmail(): Invalid email address given!'
				);
			}
		}

		$this->email = implode(',', $emails);
	}

	/**
	 * @param int	$minimalSeverity
	 * @return void
	 */
	public function setMinimalSeverity($minimalSeverity) {
		$this->severity = $minimalSeverity;
	}

	/**
	 * Set force summary
	 *
	 * @param int	$forceSummaryMail
	 * @return void
	 */
	public function setForceSummaryMail($forceSummaryMail) {
		$this->force = (boolean) $forceSummaryMail;
	}

	/**
	 * Set data var
	 *
	 * @param array	$includeDataVar
	 * @return void
	 */
	public function setIncludeDataVar($includeDataVar) {
		$this->dataVar = (boolean) $includeDataVar;
	}

	/**
	 * @param boolean	$groupEntries
	 * @return void
	 */
	public function setGroupEntries($groupEntries) {
		$this->groupEntries = (boolean) $groupEntries;
	}

	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		return sprintf(
			$GLOBALS['LANG']->sL('LLL:EXT:mklog/locallang_db.xml:scheduler_watchdog_taskinfo'),
			$this->getEmailReceiver()
		);
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklog/scheduler/class.tx_mklog_scheduler_WatchDog.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklog/scheduler/class.tx_mklog_scheduler_WatchDog.php']);
}
