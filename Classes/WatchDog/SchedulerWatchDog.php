<?php

declare(strict_types=1);

/*
 * Copyright notice
 *
 * (c) 2011-2026 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklog" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mklog\WatchDog;

use DMK\Mklog\Domain\Model\DevlogEntryDemand;
use DMK\Mklog\Domain\Model\GenericArrayObject;
use DMK\Mklog\Factory;
use DMK\Mklog\Utility\SeverityUtility;
use DMK\Mklog\Utility\Typo3Utility;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * MK Log watchdog.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SchedulerWatchDog extends AbstractTask
{
    /**
     * Prefix of the native task fields in tx_scheduler_task.
     *
     * @see Configuration/TCA/Overrides/scheduler_mklog_watchdog.php
     */
    public const FIELD_PREFIX = 'tx_mklog_watchdog_';

    /**
     * The option keys, which are stored in native task fields.
     */
    public const OPTION_KEYS = [
        'transport',
        'credentials',
        'severity',
        'message_limit',
        'extension_whitelist',
        'extension_blacklist',
        'mail_subject',
    ];

    /**
     * Internal options storage.
     *
     * @var GenericArrayObject
     */
    protected $schedulerOptions;

    /**
     * The current configured transport.
     *
     * @var Transport\InterfaceTransport
     */
    protected $messageTransport;

    public function __unserialize(array $data): void
    {
        foreach ($data as $property => $value) {
            // legacy serialized tasks contain mangled names for non-public properties ("\0*\0name")
            $property = substr((string) $property, (int) strrpos("\0".$property, "\0"));
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    protected function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {
        return Factory::makeInstance(LogManager::class)->getLogger('DMK.Mklog.WatchDog.SchedulerWatchDog');
    }

    /**
     * Returns a storage.
     *
     * @return GenericArrayObject
     */
    public function getOptions()
    {
        // backwards compatiblity for schedulers that were created when we still used rn_base.
        if (null !== $this->schedulerOptions && $this->schedulerOptions::class === 'Sys25\RnBase\Domain\Model\DataModel') {
            $this->schedulerOptions = GenericArrayObject::getInstance($this->schedulerOptions->getProperties());
        }

        if (null === $this->schedulerOptions) {
            $this->schedulerOptions = GenericArrayObject::getInstance();
        }

        return $this->schedulerOptions;
    }

    /**
     * Returns the options as values for the native task fields.
     */
    public function getTaskParameters(): array
    {
        $options = $this->getOptions();
        $severity = $options->getSeverity();
        $limit = $options->getMessageLimit();

        return [
            self::FIELD_PREFIX.'transport' => (string) $options->getTransport(),
            self::FIELD_PREFIX.'credentials' => (string) $options->getCredentials(),
            self::FIELD_PREFIX.'severity' => null === $severity ? SeverityUtility::DEBUG : (int) $severity,
            self::FIELD_PREFIX.'message_limit' => null === $limit ? 100 : (int) $limit,
            self::FIELD_PREFIX.'extension_whitelist' => (string) $options->getExtensionWhitelist(),
            self::FIELD_PREFIX.'extension_blacklist' => (string) $options->getExtensionBlacklist(),
            self::FIELD_PREFIX.'mail_subject' => (string) $options->getMailSubject(),
        ];
    }

    /**
     * Fills the options from the native task fields.
     *
     * The legacy "schedulerOptions" parameter is taken into account
     * for tasks, which are not migrated to the native fields yet.
     */
    public function setTaskParameters(array $parameters): void
    {
        $options = $this->getOptions();

        $legacyOptions = $parameters['schedulerOptions'] ?? null;
        if ($legacyOptions instanceof GenericArrayObject) {
            $legacyOptions = $legacyOptions->toArray();
        }

        if (is_array($legacyOptions)) {
            foreach ($legacyOptions as $key => $value) {
                $options->setProperty($key, $value);
            }
        }

        foreach (self::OPTION_KEYS as $key) {
            if (!array_key_exists(self::FIELD_PREFIX.$key, $parameters)) {
                continue;
            }

            $value = $parameters[self::FIELD_PREFIX.$key];
            $options->setProperty(
                $key,
                in_array($key, ['severity', 'message_limit'], true) ? (int) $value : (string) $value
            );
        }

        // the transport depends on the options, so it has to be recreated
        $this->messageTransport = null;
    }

    /**
     * Validates the submitted credentials against the selected transport.
     *
     * Required fields are already validated by FormEngine.
     */
    public function validateTaskParameters(array $parameters): bool
    {
        $options = $this->getOptions();
        $transport = (string) ($parameters[self::FIELD_PREFIX.'transport'] ?? $options->getTransport());
        $credentials = trim((string) ($parameters[self::FIELD_PREFIX.'credentials'] ?? $options->getCredentials()));

        if ('' === $credentials) {
            return $this->addValidationError('scheduler_watchdog.error.credentials_required');
        }

        $isValid = match ($transport) {
            Transport\MailTransport::class => GeneralUtility::validEmail(
                // allow the "Name <mail@example.org>" notation
                1 === preg_match('/<([^>]+)>/', $credentials, $matches) ? $matches[1] : $credentials
            ),
            Transport\Gelf\UdpGelf::class => 1 === preg_match('/^[^:\s]+:\d+$/', $credentials),
            Transport\Gelf\HttpGelf::class => false !== filter_var($credentials, FILTER_VALIDATE_URL),
            default => true,
        };

        return $isValid || $this->addValidationError('scheduler_watchdog.error.credentials_invalid');
    }

    private function addValidationError(string $labelKey): bool
    {
        $message = $this->getLanguageService()?->sL(
            'LLL:EXT:mklog/Resources/Private/Language/locallang_db.xlf:'.$labelKey
        ) ?: $labelKey;
        Typo3Utility::addFlashMessage($message, 'MK Log WatchDog', ContextualFeedbackSeverity::ERROR);

        return false;
    }

    /**
     * Do the magic and publish all new messages thu the transport.
     *
     * @return bool Returns TRUE on successful execution, FALSE on error
     */
    public function execute()
    {
        $failures = [];
        $successes = [];
        $transport = $this->getTransport();

        // initialize the transport
        $transport->initialize($this->getOptions());

        /* @var $message \DMK\Mklog\Domain\Model\DevlogEntry */
        foreach ($this->findMessages() as $message) {
            try {
                $transport->publish($message);
                // mark entry as send for current transport
                $this->markAsTransported($message);
                $successes[$message->getUid()] = '';
            } catch (\Exception $exception) {
                $failures[$message->getUid()] = $exception->getMessage();
            }
        }

        // shutdown the transport
        $transport->shutdown();

        $success = [] === $failures;
        $msg = sprintf(
            'WatchDog %1$s has %2$d messages send and %3$d failures.',
            $this->getTransportId(),
            count($successes),
            count($failures)
        );

        $this->getLogger()->log(
            $success ? 'debug' : 'warning',
            $msg,
            [
                'transport' => $this->getTransportId(),
                'successes' => $successes,
                'failures' => $failures,
            ]
        );

        // create a flash message for the beuser
        if (0 !== Typo3Utility::getBeUserId()) {
            Typo3Utility::addFlashMessage(
                $msg,
                'MK Log WatchDog',
                $success ? ContextualFeedbackSeverity::OK : ContextualFeedbackSeverity::ERROR
            );
        }

        return $success;
    }

    /**
     * Returns all untransportet messages.
     */
    protected function findMessages(): array
    {
        return Factory::getDevlogEntryRepository()->findByDemand($this->getDevlogEntryDemand());
    }

    /**
     * @return DevlogEntryDemand
     */
    protected function getDevlogEntryDemand()
    {
        $options = $this->getOptions();
        $demand = Factory::makeInstance(DevlogEntryDemand::class);

        $demand->setTransportId($this->getTransportId());
        if ($options->getSeverity()) {
            $demand->setSeverity($options->getSeverity());
        }

        if ($options->getExtensionWhitelist()) {
            $demand->setExtensionWhitelist(
                GeneralUtility::trimExplode(',', $options->getExtensionWhitelist(), true)
            );
        }

        if ($options->getExtensionBlacklist()) {
            $demand->setExtensionBlacklist(
                GeneralUtility::trimExplode(',', $options->getExtensionBlacklist(), true)
            );
        }

        $limit = $options->getMessageLimit();
        // fallback of 100, if no limit is configured
        $limit ??= 100;

        $limit = (int) $limit;
        if ($limit > 0) {
            $demand->setMaxResults($limit);
        }

        $demand->setOrderBy('crdate', 'ASC');

        return $demand;
    }

    /**
     * Marks the message as transported.
     */
    protected function markAsTransported(
        \DMK\Mklog\Domain\Model\DevlogEntry $message,
    ) {
        $repo = Factory::getDevlogEntryRepository();
        $repo->persist(
            $message->addTransportId(
                $this->getTransportId()
            )
        );
    }

    /**
     * Creates the transport.
     *
     * @return Transport\InterfaceTransport
     */
    protected function getTransport()
    {
        if (null === $this->messageTransport) {
            $this->messageTransport = Factory::getTransport(
                $this->getOptions()->getTransport()
            );
        }

        return $this->messageTransport;
    }

    /**
     * Creates the transport id.
     */
    protected function getTransportId(): string
    {
        return $this->getTransport()->getIdentifier().':'.$this->getTaskUid();
    }

    /**
     * This method returns the destination mail address as additional information.
     *
     * @return string Information to display
     */
    #[\Override]
    public function getAdditionalInformation(): string
    {
        if ($this->getOptions()->isEmpty()) {
            return '';
        }

        $options = [];

        foreach ($this->getOptions()->toArray() as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $key = GeneralUtility::underscoredToLowerCamelCase($key);
            $options[] = '  '.ucfirst($key).': '.$value;
        }

        return 'Options: '.PHP_EOL.implode('; '.PHP_EOL, $options);
    }
}
