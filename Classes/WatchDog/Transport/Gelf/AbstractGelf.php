<?php

/*
 * Copyright notice
 *
 * (c) 2011-2024 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\WatchDog\Transport\Gelf;

use DMK\Mklog\Domain\Model\GenericArrayObject;
use DMK\Mklog\Factory;
use DMK\Mklog\Utility\ComposerUtility;
use DMK\Mklog\WatchDog\Message\InterfaceMessage;
use DMK\Mklog\WatchDog\Transport\AbstractTransport;
use Gelf\Message;
use Gelf\Publisher;
use Gelf\PublisherInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * MK Log watchdog gelf transporter.
 *
 * All chunks MUST arrive within 5 seconds
 * or the server will discard all already arrived and still arriving chunks.
 * A message MUST NOT consist of more than 128 chunks.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractGelf extends AbstractTransport implements SingletonInterface
{
    /**
     * The gelf publisher.
     *
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * An unique identifier for the transport.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'mkLogGelf';
    }

    /**
     * Creates the Transport.
     *
     * @return \Gelf\Transport\AbstractTransport
     */
    abstract protected function getTransport();

    /**
     * Initializes the Transport.
     */
    public function initialize(
        GenericArrayObject $options
    ) {
        parent::initialize($options);

        ComposerUtility::autoload();
    }

    /**
     * Publishes a message by the provider.
     */
    public function publish(
        InterfaceMessage $message
    ) {
        $gelfMsg = new Message();
        $gelfMsg
            ->setVersion('1.1')
            ->setHost($message->getHost())
            ->setShortMessage($message->getShortMessage())
            ->setFullMessage($message->getFullMessage())
            ->setTimestamp($message->getTimestamp())
            ->setLevel($message->getLevel())
            ->setFacility($message->getFacility());

        $additionalData = $message->getAdditionalData();
        if (!is_array($additionalData)) {
            $additionalData = [];
        }

        $converter = Factory::getDataConverterUtility();
        foreach ($additionalData as $key => $value) {
            // the value shoult be an string, so we convert objects and arrays!
            if (!\is_scalar($value)) {
                $value = $converter->encode($value);
            }
            $gelfMsg->setAdditional(
                $key,
                $value
            );
        }

        $this->getPublisher()->publish($gelfMsg);
    }

    /**
     * Creates the Publisher.
     *
     * @return PublisherInterface
     */
    protected function getPublisher()
    {
        if (null === $this->publisher) {
            $this->publisher = new Publisher(
                $this->getTransport()
            );
        }

        return $this->publisher;
    }
}
