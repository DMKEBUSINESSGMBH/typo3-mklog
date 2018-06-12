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
class SchedulerWatchDog extends \Tx_Rnbase_Scheduler_Task
{
    /**
     * Internal options storage
     *
     * @var \Tx_Rnbase_Domain_Model_Data
     */
    private $options = null;

    /**
     * The current configured transport
     *
     * @var \DMK\Mklog\WatchDog\Transport\InterfaceTransport
     */
    private $transport = null;

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
        $failures = $successes = array();

        $transport = $this->getTransport();

        // initialize the transport
        $transport->initialize($this->getOptions());

        /* @var $message \DMK\Mklog\Domain\Model\DevlogEntryModel */
        foreach ($this->findMessages() as $message) {
            try {
                $transport->publish($message);
                // mark entry as send for current transport
                $this->markAsTransported($message);
                $successes[$message->getUid()] = '';
            } catch (\Exception $e) {
                $failures[$message->getUid()] = $e->getMessage();
            }
        }

        // shutdown the transport
        $transport->shutdown();

        \tx_rnbase::load('tx_rnbase_util_Logger');
        $success = empty($failures);
        $msg = sprintf(
            'WatchDog %1$s has %2$d messages send and %3$d failures.',
            $this->getTransportId(),
            count($successes),
            count($failures)
        );
        \tx_rnbase_util_Logger::devLog(
            $msg,
            'mklog',
            $success ? \tx_rnbase_util_Logger::LOGLEVEL_DEBUG : \tx_rnbase_util_Logger::LOGLEVEL_WARN,
            array(
                'transport' => $this->getTransportId(),
                'successes' => $successes,
                'failures' => $failures,
            )
        );

        // create a flash message for the beuser
        \tx_rnbase::load('tx_rnbase_util_TYPO3');
        if (\tx_rnbase_util_TYPO3::getBEUserUID()) {
            \tx_rnbase::load('tx_rnbase_util_Misc');
            \tx_rnbase_util_Misc::addFlashMessage(
                $msg,
                'MK LOGGER WatchDog',
                $success ? 0 : 2,
                false
            );
        }

        return $success;
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

        $fields[SEARCH_FIELD_CUSTOM] = sprintf(
            'NOT FIND_IN_SET(\'%s\', `transport_ids`)',
            $this->getTransportId()
        );

        if ($this->getOptions()->getSeverity()) {
            $fields['DEVLOGENTRY.severity'][OP_LTEQ_INT] = $this->getOptions()->getSeverity();
        }

        $limit = $this->getOptions()->getMessageLimit();
        // fallback of 100, if no limit is configured
        if ($limit === null) {
            $limit = 100;
        }
        $limit = (int) $limit;
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        $options['orderby'] = ['DEVLOGENTRY.crdate' => 'ASC'];

        return $repo->search($fields, $options);
    }

    /**
     * Marks the message as transported
     *
     * @param \DMK\Mklog\Domain\Model\DevlogEntryModel $message
     *
     * @return void
     */
    protected function markAsTransported(
        \DMK\Mklog\Domain\Model\DevlogEntryModel $message
    ) {
        $repo = \DMK\Mklog\Factory::getDevlogEntryRepository();
        $repo->persist(
            $message->addTransportId(
                $this->getTransportId()
            )
        );
    }

    /**
     * Creates the transport
     *
     * @return \DMK\Mklog\WatchDog\Transport\InterfaceTransport
     */
    protected function getTransport()
    {
        if ($this->transport === null) {
            $this->transport = \DMK\Mklog\Factory::getTransport(
                $this->getOptions()->getTransport()
            );
        }

        return $this->transport;
    }

    /**
     * Creates the transport id
     *
     * @return \DMK\Mklog\WatchDog\Transport\InterfaceTransport
     */
    protected function getTransportId()
    {
        return $this->getTransport()->getIdentifier() . ':' . $this->getTaskUid();
    }

    /**
     * This method returns the destination mail address as additional information
     *
     * @return  string  Information to display
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
