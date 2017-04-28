<?php
/**
 *  Copyright notice
 *
 *  (c) 2011 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_util_Logger');
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('Tx_Rnbase_Service_Base');
tx_rnbase::load('Tx_Mklog_Utility_Devlog');

/**
 * Service für WatchDog
 *
 * @author René Nitzsche
 */
class tx_mklog_srv_WatchDog extends Tx_Rnbase_Service_Base
{

    /**
     * Versand von Infomails
     * @param   string  $emailReceiver
     * @param   date    $lastRun
     * @param   array   $filters
     * @param   array   $options
     */
    public function triggerMails(
        $emailReceiver,
        $lastRun,
        $filters = array(),
        $options = array()
    ) {
        $infos = $this->lookupMsgs($lastRun, $filters, $options);
        // muss eine Mail verschickt werden?
        if (intval($options['forceSummaryMail']) > 0 || $infos['datafound']) {
            $this->sendMail($emailReceiver, $infos, $lastRun, $options);
        }
    }

    /**
     * @param DateTime $lastRun
     * @param array $filters
     * @param array $options
     * @return array
     */
    protected function lookupMsgs(
        DateTime $lastRun,
        array $filters = array(),
        array $options = array()
    ) {
        $infos = array();
        $infos['summary'] = $this->getSummary($lastRun);
        $minimalSeverity = isset($options['minimalSeverity']) ?
            $options['minimalSeverity'] : tx_rnbase_util_Logger::LOGLEVEL_WARN;

        $hasData = false;
        for ($severity = $minimalSeverity; $severity < 4; $severity++) {
            $entries = $this->getLatestEntries($lastRun, $severity, $options);
            $infos['latest'][$severity] = $entries;
            if (count($entries)) {
                $hasData = true;
            }
        }

        $infos['datafound'] = $hasData;

        return $infos;
    }

    /**
     * @param DateTime $lastRun
     * @param int $severity
     * @param array $options
     * @return array
     */
    protected function getLatestEntries(DateTime $lastRun, $severity, array $options)
    {
        $what = '*';
        $from = Tx_Mklog_Utility_Devlog::getTableName();
        $options['enablefieldsoff'] = '1';
        $options['where'] = 'crdate>='. $lastRun->format('U') .
                            ' AND severity='. intval($severity);
        // notbremse, es können ziemlich viele logs vorhanden sein.
        if (!isset($options['limit'])) {
            $options['limit'] = 30;
        }
        $options['orderby'] = 'crdate desc';

        if ($options['count']) {
            $what .= ', COUNT(*) as msgCount';
        }

        $result = tx_rnbase_util_DB::doSelect($what, $from, $options);

        return $result;
    }

    /**
     * Anzahl aller Meldungen für alle Log-Level laden
     * @param DateTime $lastRun
     */
    protected function getSummary(DateTime $lastRun)
    {
        $what = 'severity, count(uid) As cnt';
        $from = Tx_Mklog_Utility_Devlog::getTableName();
        $options = array();
        $options['groupby'] = 'severity';
        $options['enablefieldsoff'] = '1';
        $options['where'] = 'crdate>='. $lastRun->format('U');
        $result = tx_rnbase_util_DB::doSelect($what, $from, $options);

        return $result;
    }

    /**
     *
     * @param string $emailReceiver
     * @param array $infos
     * @param DateTime $lastRun
     * @param array $options
     *
     * @return bool
     */
    protected function sendMail($emailReceiver, array $infos, DateTime $lastRun, array $options = array())
    {
        $contentArr = $this->buildMailContents($infos, $lastRun, $options);

        /* @var $mail tx_rnbase_util_Mail */
        $mail = tx_rnbase::makeInstance('tx_rnbase_util_Mail');
        $mail->setSubject(
            'WatchDog for logger on site ' .
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']
        );
        $mail->setFrom(
            tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'fromEmail')
        );
        $mail->setTo($emailReceiver);
        $mail->setTextPart($contentArr['text']);
        $mail->setHtmlPart($contentArr['html']);

        return $mail->send();
    }

    /**
     * @return array
     */
    public function getSeverities()
    {
        return array(
            tx_rnbase_util_Logger::LOGLEVEL_DEBUG => 'DEBUG',
            tx_rnbase_util_Logger::LOGLEVEL_INFO => 'INFO',
            tx_rnbase_util_Logger::LOGLEVEL_NOTICE => 'NOTICE',
            tx_rnbase_util_Logger::LOGLEVEL_WARN => 'WARN',
            tx_rnbase_util_Logger::LOGLEVEL_FATAL => 'FATAL',
        );
    }

    /**
     *
     * @param array $infos
     * @param DateTime $lastRun
     * @param array $options
     * @return array
     */
    protected function buildMailContents(
        array $infos,
        DateTime $lastRun,
        array $options = array()
    ) {
        $messageFieldName = Tx_Mklog_Utility_Devlog::getMessageFieldName();
        $extraDataFieldName = Tx_Mklog_Utility_Devlog::getExtraDataFieldName();
        $levels = $this->getSeverities();
        $textPart =    'This is an automatic email from TYPO3. Don\'t answer!'."\n\n";
        $htmlPart =    '<strong>This is an automatic email from TYPO3. Don\'t answer!</strong>';
        $textPart .=    '== Developer Log summary since '.
                        $lastRun->format('Y-m-d H:i:s') ."==\n\n";
        $htmlPart .=    '<h2>Developer Log summary since '.
                        $lastRun->format('Y-m-d H:i:s').'</h2>';
        $htmlPart .= "\n<ul>\n";
        foreach ($infos['summary'] as $data) {
            $textPart .= sprintf(
                'Level %s (%d): %d items found',
                $levels[$data['severity']],
                $data['severity'],
                $data['cnt']
            );
            $textPart .= "\n";
            $htmlPart .= sprintf(
                '<li><a href="#%s">Level %s (Severity Number: %d)</a>: %d items found</li>',
                strtolower($levels[$data['severity']]),
                $levels[$data['severity']],
                $data['severity'],
                $data['cnt']
            );
        }
        $htmlPart .= "\n</ul>\n";
        if ($infos['datafound']) {
            $textPart .= "\n== Latest entries by log level ==\n";
            $htmlPart .= '<h2>Latest entries by log level</h2>'."\n";
            foreach ($infos['latest'] as $level => $records) {
                if (!count($records)) {
                    continue;
                }
                $textPart .= sprintf(
                    "\nLevel %s (%d):\n",
                    $levels[$level],
                    $data['severity']
                );
                $htmlPart .= sprintf(
                    '<h3><a name="%s">Level %s (Severity Number: %d)</a></h3>',
                    strtolower($levels[$level]),
                    $levels[$level],
                    $data['severity']
                );
                foreach ($records as $record) {
                    $datavar = $options['includeDataVar'] ? (
                                    'Extra Data: '.(
                                        $record[$extraDataFieldName] ?
                                        print_r(Tx_Mklog_Utility_Devlog::getExtraDataAsArray(
                                            $record[$extraDataFieldName]
                                        ), true) : '')
                                    ) : '';
                    $textPart .= sprintf(
                        "Time: %s Extension: %s\nMessage: %s\nCount: %s\n%s",
                        date('Y-m-d H:i:s', $record['crdate']),
                        $record['extkey'],
                        $record[$messageFieldName],
                        $record['msgCount'],
                        $datavar
                    );
                    $htmlPart .= sprintf(
                        "<p>Time: %s<br />Extension: %s<br />Message: %s</p><br />Count: %s\n<pre>%s</pre>",
                        date('Y-m-d H:i:s', $record['crdate']),
                        $record['extkey'],
                        $record[$messageFieldName],
                        $record['msgCount'],
                        $datavar
                    );
                }
            }
        }

        return array('text' => $textPart, 'html' => $htmlPart);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklog/srv/class.tx_mklog_srv_WatchDog.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklog/srv/class.tx_mklog_srv_WatchDog.php']);
}
