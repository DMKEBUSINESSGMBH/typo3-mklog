<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 René Nitzsche <nitzsche@das-medienkombinat.de>
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
require_once t3lib_extMgm::extPath('scheduler', 'class.tx_scheduler_task.php');

tx_rnbase::load('tx_rnbase_util_Logger');

/**
 * 
 */
class tx_mklog_scheduler_WatchDog extends tx_scheduler_Task {

	/**
	 * Email address(es) for notification mail
	 *
	 * @var	string
	 */
	 private $email;
	 private $severity;
	 private $force;
	 private $dataVar;
	 
	/**
	 * Function executed from the Scheduler.
	 * Sends an email
	 *
	 * @return	void
	 */
	public function execute() {
		$success = true;
		$taskId = $this->taskUid;

		tx_rnbase::load('tx_rnbase_util_Misc');
		try {
			$lastRun = $this->getLastRunTime($taskId);
			/*@var $srv tx_mklog_srv_WatchDog */
			$srv = tx_rnbase_util_Misc::getService('mklog', 'WatchDog');
			$filters = array();
			$options = array();
			$options['minlevel'] = $this->severity;
			$options['forceSummery'] = $this->force;
			$options['dataVar'] = $this->dataVar;
			
			$srv->triggerMails($this->getEmail(), $lastRun, $filters, $options);
			$this->updateLastRunTime($taskId);

		} catch (Exception $e) {
			tx_rnbase_util_Logger::fatal('WatchDog failed!', 'mklog', array('Exception' => $e->getMessage()));
			$success = false;
		}
		return $success;
	}
	protected function getLastRunTime($taskId) {
		$options = array();
		$options['enablefieldsoff'] = 1;
		$options['where'] = 'uid='.intval($taskId);
		$ret = tx_rnbase_util_DB::doSelect('tx_mklog_lastrun', 'tx_scheduler_task', $options);
		$lastrun = new DateTime();
		if(count($ret))
			$lastrun = new DateTime($ret[0]['tx_mklog_lastrun']);
		return $lastrun;
	}
	protected function updateLastRunTime($taskId) {
		$lastRun = new DateTime();
		return tx_rnbase_util_DB::doUpdate('tx_scheduler_task', 'uid='.intval($taskId),
			array('tx_mklog_lastrun' => $lastRun->format('Y-m-d H:i:s')
		));
	}
	
	/**
	 * Return email address
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}
	public function getSeverity() {
		return $this->severity;
	}
	public function getForce() {
		return $this->force;
	}
	public function getDataVar() {
		return $this->dataVar;
	}

	/**
	 * Set mail address
	 *
	 * @param string	$val
	 * @return void
	 */
	public function setEmail($val) {
		$emails = t3lib_div::trimExplode(',',$val);

		foreach($emails As $email) {
			if(!t3lib_div::validEmail($email)) {
				throw new Exception('tx_mklog_scheduler_WatchDog->setEmail(): Invalid email address given!');
			}
		}
		
		$this->email = implode(',', $emails);
	}
	/**
	 * Set minimum severity
	 *
	 * @param int	$val
	 * @return void
	 */
	public function setSeverity($val) {
		$this->severity = $val;
	}
	/**
	 * Set force summery
	 *
	 * @param int	$val
	 * @return void
	 */
	public function setForce($val) {
		$this->force = $val;
	}

	/**
	 * Set data var
	 *
	 * @param int	$val
	 * @return void
	 */
	public function setDataVar($val) {
		$this->dataVar = $val;
	}

	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		return sprintf(	$GLOBALS['LANG']->sL('LLL:EXT:mklog/locallang_db.xml:scheduler_watchdog_taskinfo'),
			$this->getEmail());
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklog/scheduler/class.tx_mklog_scheduler_WatchDog.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklog/scheduler/class.tx_mklog_scheduler_WatchDog.php']);
}