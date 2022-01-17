<?php

/*
 * Copyright notice
 *
 * (c) 2011-2022 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\WatchDog\Transport;

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

use DMK\Mklog\Domain\Model\GenericArrayObject;
use DMK\Mklog\Factory;
use DMK\Mklog\Tests\BaseTestCase;
use DMK\Mklog\Utility\VersionUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SchedulerWatchDogTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MailTransportTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = 'test site';
    }

    /**
     * @group unit
     * @test
     */
    public function testSendMailWithDefaultSubject()
    {
        $subjectMethod = VersionUtility::isTypo3Version10OrHigher() ? 'subject' : 'setSubject';
        $mailUtility = $this->getMock(MailMessage::class, [$subjectMethod, 'send']);
        $mailUtility->expects(self::once())
            ->method($subjectMethod)
            ->with('DevLog WatchDog on site test site')
            ->willReturnSelf();
        GeneralUtility::addInstance(MailMessage::class, $mailUtility);

        $mailTransport = Factory::makeInstance(MailTransport::class);
        $mailTransport->initialize(
            Factory::makeInstance(
                GenericArrayObject::class,
                ['credentials' => Factory::getConfigUtility()->getGlobalMailFrom()]
            )
        );

        $this->callInaccessibleMethod($mailTransport, 'sendMail', 'mail content');
    }

    /**
     * @group unit
     * @test
     */
    public function testSendMailWithSubjectFromOptions()
    {
        $subjectMethod = VersionUtility::isTypo3Version10OrHigher() ? 'subject' : 'setSubject';
        $mailUtility = $this->getMock(MailMessage::class, [$subjectMethod, 'send']);
        $mailUtility->expects(self::once())
            ->method($subjectMethod)
            ->with('test subject on test site')
            ->willReturnSelf();
        GeneralUtility::addInstance(MailMessage::class, $mailUtility);

        $mailTransport = GeneralUtility::makeInstance(MailTransport::class);
        $mailTransport->initialize(
            Factory::makeInstance(
                GenericArrayObject::class,
                [
                    'credentials' => 'John Dohe<john@dohe.org>',
                    'mail_subject' => 'test subject on %s',
                    ]
                )
        );

        $this->callInaccessibleMethod($mailTransport, 'sendMail', 'mail content');
    }
}
