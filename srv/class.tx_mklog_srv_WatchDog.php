<?php
/**
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 */

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
require_once(PATH_t3lib.'class.t3lib_svbase.php');
tx_rnbase::load('tx_rnbase_util_Logger');

/**
 * Service für WatchDog
 *
 * @author René Nitzsche
 */
class tx_mklog_srv_WatchDog extends t3lib_svbase {

	/**
	 * Versand von Infomails
	 * @param 	string 	$emails
	 * @param 	date 	$lastRun
	 * @param 	array 	$filters
	 * @param 	array 	$options
	 */
	public function triggerMails($emails, $lastRun, $filters=array(), $options=array()) {
		$infos = $this->lookupMsgs($lastRun, $filters, $options);
		// muss eine Mail verschickt werden?
		if(intval($options['forceSummery']) > 0 || $infos['datafound'])
			$this->sendMail($emails, $infos, $lastRun, $options);
	}
	
	protected function lookupMsgs($lastRun, $filters=array(), $options=array()) {
		$infos = array();
		$infos['summery'] = $this->getSummary($lastRun);

		$minLevel = $options['minlevel'] ? $options['minlevel'] : tx_rnbase_util_Logger::LOGLEVEL_WARN;

		$hasData = false;
		for($i = $minLevel; $i < 4; $i++) {
			$entries = $this->getLatestEntries($lastRun, $i, $options);
			$infos['latest'][$i] = $entries;
			if(count($entries))
				$hasData=true;
		}

		$infos['datafound'] = $hasData;
		return $infos;
	}
	
	protected function getLatestEntries(DateTime $lastRun, $severity, array $options) {
		$what = '*';
		$from = 'tx_devlog';
		$options['enablefieldsoff'] = '1';
		$options['where'] = 'crdate>='. $lastRun->format('U') . ' AND severity>='. intval($severity);
		// notbremse, es können ziemlich viele logs vorhanden sein.
		if(!isset($options['limit'])) $options['limit'] = 100;
		$options['orderby'] = 'crdate desc';
		$result = tx_rnbase_util_DB::doSelect($what, $from, $options);
		return $result;
	}
	
	/**
	 * Anzahl aller Meldungen für alle Log-Level laden
	 * @param DateTime $lastRun
	 */
	protected function getSummary(DateTime $lastRun) {
		$what = 'severity, count(uid) As cnt';
		$from = 'tx_devlog';
		$options = array();
		$options['groupby'] = 'severity';
		$options['enablefieldsoff'] = '1';
		$options['where'] = 'crdate>='. $lastRun->format('U');
		$result = tx_rnbase_util_DB::doSelect($what, $from, $options);
		return $result;
	}
	protected function sendMail($emails, $infos, DateTime $lastRun, $options=array()) {
		$contentArr = $this->buildMailContents($infos, $lastRun, $options);

		$mail = t3lib_div::makeInstance('t3lib_htmlmail');
		$mail->start();
		$mail->subject         = 'WatchDog for logger on site '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		$mail->from_email      = tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'fromEmail');
		$mail->from_name       = '';
		$mail->organisation    = '';
		$mail->priority        = 1;
		$mail->addPlain($contentArr['text']);
		$mail->setHTML($contentArr['html']);
		return $mail->send($emails);
	}
	public function getSeverities() {
		return array(
			tx_rnbase_util_Logger::LOGLEVEL_DEBUG => 'DEBUG',
			tx_rnbase_util_Logger::LOGLEVEL_INFO => 'INFO',
			tx_rnbase_util_Logger::LOGLEVEL_NOTICE => 'NOTICE',
			tx_rnbase_util_Logger::LOGLEVEL_WARN => 'WARN',
			tx_rnbase_util_Logger::LOGLEVEL_FATAL => 'FATAL',
		);
	}
	protected function buildMailContents($infos, DateTime $lastRun, $options=array()) {
		$levels = $this->getSeverities();
		$textPart = 'This is an automatic email from TYPO3. Don\'t answer!'."\n\n";
		$htmlPart = '<strong>This is an automatic email from TYPO3. Don\'t answer!</strong>';
		$textPart .= '== Developer Log summery since '. $lastRun->format('Y-m-d H:i:s') ."==\n\n";
		$htmlPart .= '<h2>Developer Log summery since '. $lastRun->format('Y-m-d H:i:s').'</h2>';
		$htmlPart .= "\n<ul>\n";
		foreach ($infos['summery'] As $data) {
			$textPart .= sprintf('Level %s (%d): %d items found', $levels[$data['severity']], $data['severity'], $data['cnt']);
			$textPart .= "\n";
			$htmlPart .= sprintf('<li>Level %s (%d): %d items found</li>', $levels[$data['severity']], $data['severity'], $data['cnt']);
		}
		$htmlPart .= "\n</ul>\n";
		if($infos['datafound']) {
			$textPart .= "\n== Latest entries by log level ==\n";
			$htmlPart .= '<h2>Latest entries by log level</h2>'."\n";
			foreach ($infos['latest'] As $level=>$records) {
				if(!count($records)) continue;
				$textPart .= sprintf("\nLevel %s (%d):\n", $levels[$level], $data['severity']);
				$htmlPart .= sprintf("<h3>Level %s (%d)</h3>\n", $levels[$level], $data['severity']);
				foreach($records As $record) {
					$datavar = 'DataVar: '.($options['dataVar'] && $record['data_var'] ? print_r(unserialize($record['data_var']), true) : '');
					$textPart .= sprintf("Time: %s Extension: %s\nMessage: %s\n%s", date('Y-m-d H:i:s',$record['crdate']), $record['extkey'], $record['msg'], $datavar);
					$htmlPart .= sprintf("<p>Time: %s<br />Extension: %s<br />Message: %s</p>\n<pre>%s</pre>", date('Y-m-d H:i:s',$record['crdate']), $record['extkey'], $record['msg'], $datavar);
				}
			}
		}
		return array('text'=>$textPart, 'html'=>$htmlPart);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklog/srv/class.tx_mklog_srv_WatchDog.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklog/srv/class.tx_mklog_srv_WatchDog.php']);
}