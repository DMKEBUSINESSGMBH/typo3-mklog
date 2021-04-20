<?php

namespace DMK\Mklog\WatchDog;

/***************************************************************
 * Copyright notice
 *
 * (c) 2020 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mklog\Factory;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * MK Log watchdog scheduler fields.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SchedulerFieldProviderWatchDog implements AdditionalFieldProviderInterface
{
    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds an email field.
     *
     * @param array               $taskInfo        Reference to the array containing the info used in the add/edit form
     * @param object              $task            When editing, reference to the current task object. Null when adding.
     * @param tx_scheduler_Module $schedulerModule Reference to the calling object (Scheduler's BE module)
     *
     * @return array Array Containg all the information pertaining to the additional fields
     *               The array is multidimensional, keyed to the task class name and each field's id
     *               For each field it provides an associative sub-array with the following:
     *               ['code']     => The HTML code for the field
     *               ['label']    => The label of the field (possibly localized)
     *               ['cshKey']   => The CSH key for the field
     *               ['cshLabel'] => The code of the CSH label
     */
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        // @codingStandardsIgnoreEnd

        // Initialize extra field value
        if (empty($taskInfo['mklog_watchdog_transport'])) {
            $action = $schedulerModule->getCurrentAction();

            // initialuze an empty value, as it will not be used anyway
            $taskInfo['mklog_watchdog_transport'] = '';
            $taskInfo['mklog_watchdog_credentials'] = '';
            $taskInfo['mklog_watchdog_severity'] = \DMK\Mklog\Utility\SeverityUtility::DEBUG;
            $taskInfo['mklog_watchdog_messagelimit'] = '100';
            $taskInfo['mklog_watchdog_extension_whitelist'] = '';
            $taskInfo['mklog_watchdog_extension_blacklist'] = '';
            $taskInfo['mklog_watchdog_mail_subject'] = '';

            if ('edit' == $action) {
                // Editing a task, set to internal value if data was not submitted already
                $taskInfo['mklog_watchdog_transport'] = $task->getOptions()->getTransport();
                $taskInfo['mklog_watchdog_credentials'] = $task->getOptions()->getCredentials();
                $taskInfo['mklog_watchdog_severity'] = $task->getOptions()->getSeverity();
                $taskInfo['mklog_watchdog_messagelimit'] = $task->getOptions()->getMessageLimit();
                $taskInfo['mklog_watchdog_extension_whitelist'] = $task->getOptions()->getExtensionWhitelist();
                $taskInfo['mklog_watchdog_extension_blacklist'] = $task->getOptions()->getExtensionBlacklist();
                $taskInfo['mklog_watchdog_mail_subject'] = $task->getOptions()->getMailSubject();
            }
        }

        // Write the code for the field
        $additionalFields = [];

        $additionalFields['field_mklog_watchdog_transport'] = $this->getTransportField($taskInfo);
        $additionalFields['field_mklog_watchdog_credentials'] = $this->getCredentialsField($taskInfo);
        $additionalFields['field_mklog_watchdog_severity'] = $this->getSeverityField($taskInfo);
        $additionalFields['field_mklog_watchdog_messagelimit'] = $this->getMessageLimitField($taskInfo);
        $additionalFields['field_mklog_watchdog_whitelist'] = $this->getExtensionWhitelistField($taskInfo);
        $additionalFields['field_mklog_watchdog_blacklist'] = $this->getExtensionBlacklistField($taskInfo);
        $additionalFields['field_mklog_watchdog_mail_subject'] = $this->getMailSubjectField($taskInfo);

        return $additionalFields;
    }

    /**
     * Creates the transport drop down.
     *
     * @return array
     */
    protected function getTransportField(
        array &$taskInfo
    ) {
        $fieldCode = '<select '.
            'name="tx_scheduler[mklog_watchdog_transport]" '.
            'id="field_mklog_watchdog_transport" '.
        '>';

        foreach ([
            'Mail' => [
                'DMK\Mklog\WatchDog\Transport\MailTransport' => 'Mail Message',
            ],
            'Gelf (GrayLog)' => [
                'DMK\Mklog\WatchDog\Transport\Gelf\HttpGelf' => 'Gelf HTTP',
                'DMK\Mklog\WatchDog\Transport\Gelf\UdpGelf' => 'Gelf UDP',
            ],
        ] as $group => $subs) {
            $fieldCode .= '<optgroup label="'.$group.'">';
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

        return [
            'code' => $fieldCode,
            'label' => 'Transport',
        ];
    }

    /**
     * Creates the credentials input field.
     *
     * @return array
     */
    protected function getCredentialsField(array &$taskInfo)
    {
        return $this->getInputField('credentials', 'Credentials', $taskInfo);
    }

    /**
     * @return array
     */
    protected function getInputField(string $fieldName, string $label, array &$taskInfo)
    {
        $fieldCode = '<input '.
            'type="text" '.
            'name="tx_scheduler[mklog_watchdog_'.$fieldName.']" '.
            'id="field_mklog_watchdog_'.$fieldName.'" '.
            'value="'.$taskInfo['mklog_watchdog_'.$fieldName].'" '.
            'size="50" />';

        return [
            'code' => $fieldCode,
            'label' => $label,
        ];
    }

    /**
     * Creates the severity drop down.
     *
     * @return array
     */
    protected function getSeverityField(
        array &$taskInfo
    ) {
        // Transport
        $fieldCode = '<select '.
            'name="tx_scheduler[mklog_watchdog_severity]" '.
            'id="field_mklog_watchdog_severity" '.
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

        return [
            'code' => $fieldCode,
            'label' => 'Min Severity',
        ];
    }

    /**
     * Creates the transport drop down.
     *
     * @return array
     */
    protected function getMessageLimitField(array &$taskInfo)
    {
        return $this->getInputField('messagelimit', 'Message limit per run', $taskInfo);
    }

    /**
     * @return array
     */
    protected function getExtensionWhitelistField(array &$taskInfo)
    {
        return $this->getInputField('extension_whitelist', 'Extension whitelist', $taskInfo);
    }

    /**
     * @return array
     */
    protected function getExtensionBlacklistField(array &$taskInfo)
    {
        return $this->getInputField('extension_blacklist', 'Extension blacklist', $taskInfo);
    }

    /**
     * @return array
     */
    protected function getMailSubjectField(array &$taskInfo)
    {
        return $this->getInputField('mail_subject', 'Custom mail subject', $taskInfo);
    }

    /**
     * This method checks any additional data that is relevant to the specific task
     * If the task class is not relevant, the method is expected to return true.
     *
     * @param array               $submittedData Reference to the array containing the data submitted by the user
     * @param tx_scheduler_Module $scheduler     Module Reference to the calling object
     *
     * @return bool True if validation was ok (or selected class is not relevant), false otherwise
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        // @codingStandardsIgnoreEnd
        $credentials = &$submittedData['mklog_watchdog_credentials'];
        $credentials = trim($credentials);
        if (empty($credentials)) {
            $flashMessage = Factory::makeInstance(
                \TYPO3\CMS\Core\Messaging\FlashMessage::class,
                'The credentials for the transport are required!',
                'MK LOGGER WatchDog',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
            $flashMessageService = Factory::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
            $flashMessageService->getMessageQueueByIdentifier()->enqueue($flashMessage);

            return false;
        }

        return true;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches.
     *
     * @param array $submittedData Array containing the data submitted by the user
     * @param AbstractTask $task Reference to the current task object
     */
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        // @codingStandardsIgnoreEnd
        ($task->getOptions()
            ->setTransport($submittedData['mklog_watchdog_transport'])
            ->setCredentials($submittedData['mklog_watchdog_credentials'])
            ->setSeverity((int) $submittedData['mklog_watchdog_severity'])
            ->setMessageLimit((int) $submittedData['mklog_watchdog_messagelimit'])
            ->setExtensionWhitelist($submittedData['mklog_watchdog_extension_whitelist'])
            ->setExtensionBlacklist($submittedData['mklog_watchdog_extension_blacklist'])
            ->setMailSubject($submittedData['mklog_watchdog_mail_subject'])
        );
    }
}
