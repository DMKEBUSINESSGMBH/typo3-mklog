<?php
namespace DMK\Mklog\WatchDog\Transport;

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

\tx_rnbase::load('DMK\\Mklog\\WatchDog\\Transport\\AbstractTransport');
\tx_rnbase::load('Tx_Rnbase_Interface_Singleton');

/**
 * MK Log watchdog
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MailTransport
	extends AbstractTransport implements \Tx_Rnbase_Interface_Singleton
{
	/**
	 * Messages to send
	 *
	 * @var array
	 */
	private $messages = array();

	/**
	 * Unique message counts
	 *
	 * @var array
	 */
	private $uniqs = array();

	/**
	 * An unique identifier for the transport
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return 'mklogmail';
	}

	/**
	 * Adds a Message to send
	 *
	 * @param \DMK\Mklog\WatchDog\Message\InterfaceMessage $message
	 *
	 * @return void
	 */
	protected function addMessage(
		\DMK\Mklog\WatchDog\Message\InterfaceMessage $message
	) {
		$level = $message->getLevel();

		if (!isset($this->messages[$level])) {
			$this->messages[$level] = array();
		}
		if (!isset($this->uniqs[$level])) {
			$this->uniqs[$level] = array();
		}

		// build message unique key
		$key = md5($message->getShortMessage() . $message->getFullMessage());

		// set the summary count
		if (!isset($this->uniqs[$level]['summary'])) {
			$this->uniqs[$level]['summary'] = 0;
		}
		$this->uniqs[$level]['summary']++;
		// set the unique count
		if (!isset($this->uniqs[$level][$key])) {
			$this->uniqs[$level][$key] = 0;
		}
		$this->uniqs[$level][$key]++;

		// store the unique message
		$this->messages[$level][$key] = $message;
	}

	/**
	 * Publishes a message by the provider
	 *
	 * @param \DMK\Mklog\WatchDog\Message\InterfaceMessage $message
	 *
	 * @return void
	 */
	public function publish(
		\DMK\Mklog\WatchDog\Message\InterfaceMessage $message
	) {
		$this->addMessage($message);
	}

	/**
	 * Deinitializes the Transport.
	 * For this transport we send the Mail
	 *
	 * @return void
	 */
	public function shutdown()
	{
		// no messages? nothing todo!
		if (empty($this->uniqs)) {
			return;
		}

		$content = '';
		$content .= 'This is an automatic email from TYPO3. Don\'t answer!' . LF . LF;
		$content .= '== Developer Log summary' . LF . LF;

		// create summary
		foreach ($this->uniqs as $level => $messages) {
			$content .= sprintf(
				'Level %s : %d items found',
				$level,
				$messages['summary']
			);
			$content .= LF;
		}

		$content .= LF . LF . '== Latest entries by log level' . LF;

		foreach ($this->messages as $level => $messages) {
			$content .= sprintf(
				LF . '=== Level %s (%d):',
				$level,
				$this->uniqs[$level]['summary']
			);
			$content .= LF . LF;

			/* @var $message \DMK\Mklog\WatchDog\Message\InterfaceMessage */
			foreach ($messages as $key => $message) {
				$content .= sprintf(
					'Time: %2$s %1$sExtension: %3$s %1$sMessage: %4$s %1$sCount: %5$s',
					LF,
					strftime('%d.%m.%y %H:%M:%S', $message->getTimestamp()),
					$message->getShortMessage(),
					$message->getFullMessage(),
					$this->uniqs[$level][$key]
				);
				$content .= LF . LF;
			}
		}

		// now send the mail!
		$this->sendMail($content);
	}

	/**
	 * Sends the devlog content per mail
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	protected function sendMail(
		$content
	) {
		/* @var $mail \tx_rnbase_util_Mail */
		$mail = \tx_rnbase::makeInstance('tx_rnbase_util_Mail');
		$mail->setSubject(
			'DevLog WatchDog on site ' .
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']
		);
		$mail->setFrom(\DMK\Mklog\Factory::getConfigUtility()->getGlobalMailFrom());
		$mail->setTo($this->getOptions()->getMailTo());
		$mail->setTextPart($content);

		$mail->send();
	}
}
