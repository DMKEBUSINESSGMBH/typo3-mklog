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

namespace DMK\Mklog\ViewHelper;

use DMK\Mklog\Domain\Model\DevlogEntry;
use DMK\Mklog\Utility\SeverityUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class SeverityIconViewHelper.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class SeverityIconViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'logEntry',
            DevlogEntry::class,
            'logEntry',
            true
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(): string
    {
        $severityId = $this->arguments['logEntry']->getSeverity();
        $severityName = SeverityUtility::getName($severityId);
        $icon = $this->getSeverityIconClass($severityId);
        if ('' !== $icon && '0' !== $icon) {
            $icon = GeneralUtility::makeInstance(IconFactory::class)->getIcon($icon, Icon::SIZE_SMALL);
        }

        return sprintf(
            '<span class="button button-severity severity severity-%1$s">%3$s<span>%2$s</span></span>',
            strtolower($severityName),
            ucfirst(strtolower($severityName)),
            $icon
        );
    }

    private function getSeverityIconClass(int $severityId): string
    {
        $icon = '';

        return match ($severityId) {
            SeverityUtility::DEBUG => 'status-dialog-ok',
            SeverityUtility::INFO => 'status-dialog-information',
            SeverityUtility::NOTICE => 'status-dialog-notification',
            SeverityUtility::WARNING => 'status-dialog-warning',
            SeverityUtility::ERROR, SeverityUtility::CRITICAL, SeverityUtility::ALERT, SeverityUtility::EMERGENCY => 'status-dialog-error',
            default => $icon,
        };
    }
}
