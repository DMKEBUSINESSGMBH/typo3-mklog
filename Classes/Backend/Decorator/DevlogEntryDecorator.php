<?php

/*
 * Copyright notice
 *
 * (c) 2011-2023 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\Backend\Decorator;

use DMK\Mklog\Domain\Model\DevlogEntry;
use DMK\Mklog\Domain\Model\GenericArrayObject;
use DMK\Mklog\Utility\SeverityUtility;

/**
 * Devlog Entry decorator.
 *
 * @author Michael Wagner
 */
class DevlogEntryDecorator
{
    /**
     * The module.
     *
     * @var \tx_rnbase_mod_BaseModule
     */
    private $mod;

    /**
     * The internal options object.
     *
     * @var GenericArrayObject
     */
    private $options;

    /**
     * Constructor.
     *
     * @param array|GenericArrayObject $options
     */
    public function __construct(
        \Sys25\RnBase\Backend\Module\BaseModule $mod,
        $options = []
    ) {
        $this->mod = $mod;

        $this->options = GenericArrayObject::getInstance($options);
    }

    /**
     * Formats a value.
     *
     * @param string $columnValue
     * @param string $columnName
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function format(
        $columnValue,
        $columnName,
        array $record,
        DevlogEntry $entry
    ) {
        $return = $columnValue;

        $method = \Sys25\RnBase\Utility\Strings::underscoredToLowerCamelCase($columnName);
        $method = 'format'.ucfirst($method).'Column';

        if (method_exists($this, $method)) {
            $return = $this->{$method}($entry);
        }

        return $this->wrapValue($return, $entry, $columnName);
    }

    /**
     * Wraps the Value.
     * A childclass can extend this and wrap each value in a spac.
     * For example a strikethrough for disabled entries.
     *
     * @param string $formatedValue
     * @param string $columnName
     *
     * @return string
     */
    protected function wrapValue(
        $formatedValue,
        DevlogEntry $entry,
        $columnName
    ) {
        return sprintf(
            '<span class="column-%3$s severity-%2$s">%1$s</span>',
            $formatedValue,
            strtolower(SeverityUtility::getName($entry->getSeverity())),
            $columnName
        );
    }

    /**
     * Renders the crdate column.
     *
     * @return string
     */
    protected function formatCrdateColumn(
        DevlogEntry $entry
    ) {
        return sprintf(
            '<button '.
                'type="submit" '.
                'class="button button-runid" '.
                'name="SET[mklogDevlogEntryRunid]" '.
                'value="%1$s" '.
                'title="Filter this run"'.
            '>%2$s</button>',
            $entry->getRunId(),
            strftime('%d.%m.%y %H:%M:%S', $entry->getProperty('crdate'))
        );
    }

    /**
     * Renders the severity column.
     *
     * @return string
     */
    protected function formatSeverityColumn(
        DevlogEntry $entry
    ) {
        $severityId = $entry->getSeverity();
        $severityName = SeverityUtility::getName($severityId);
        $icon = $this->getSeverityIconClass($severityId);

        if (!empty($icon)) {
            $icon = \Sys25\RnBase\Backend\Utility\Icons::getSpriteIcon($icon);
        }

        return sprintf(
            '<button '.
                'type="submit" '.
                'class="button button-severity severity severity-%2$s" '.
                'name="SET[mklogDevlogEntrySeverity]" '.
                'value="%1$s" '.
                'title="Filter %3$s (%1$s)"'.
            '>%4$s<span>%3$s</span></button>',
            $severityId,
            strtolower($severityName),
            ucfirst(strtolower($severityName)),
            $icon
        );
    }

    /**
     * @return string
     */
    private function getSeverityIconClass($severityId)
    {
        $icon = '';
        switch ($severityId) {
            case SeverityUtility::DEBUG:
                $icon = 'status-dialog-ok';
                break;
            case SeverityUtility::INFO:
                $icon = 'status-dialog-information';
                break;
            case SeverityUtility::NOTICE:
                $icon = 'status-dialog-notification';
                break;
            case SeverityUtility::WARNING:
                $icon = 'status-dialog-warning';
                break;
            case SeverityUtility::ERROR:
            case SeverityUtility::CRITICAL:
            case SeverityUtility::ALERT:
            case SeverityUtility::EMERGENCY:
                $icon = 'status-dialog-error';
                break;
        }

        return $icon;
    }

    /**
     * Renders the ext_key column.
     *
     * @return string
     */
    protected function formatExtKeyColumn(
        DevlogEntry $entry
    ) {
        return sprintf(
            '<button '.
                'type="submit" '.
                'class="button button-extkey severity-%1$s" '.
                'name="SET[mklogDevlogEntryExtKey]" '.
                'value="%1$s" '.
                'title="Filter %1$s"'.
            '>%1$s</button>',
            $entry->getExtKey()
        );
    }

    /**
     * Renders the message column.
     *
     * @return string
     */
    protected function formatMessageColumn(
        DevlogEntry $entry
    ) {
        $message = $entry->getMessage();

        return sprintf(
            '<span title="%1$s">%2$s%3$s</span>',
            htmlspecialchars($message),
            htmlspecialchars(substr($message, 0, 64)),
            strlen($message) > 64 ? ' ...' : ''
        );
    }

    /**
     * Renders the extra_data column.
     *
     * @return string
     */
    protected function formatExtraDataColumn(
        DevlogEntry $entry
    ) {
        $parser = \DMK\Mklog\Factory::getEntryDataParserUtility($entry);
        $extraData = $parser->getShortenedRaw($parser::SIZE_512KB);

        if (empty($extraData)) {
            return '';
        }

        return sprintf(
            '<a id="log-togggle-%1$s-link" '.
                'href="#log-togggle-%1$s-data" '.
                'onclick="DMK.DevLog.toggleData(%1$s);"'.
                '>+ show data</a>'.
                '<pre id="log-togggle-%1$s-data" style="display:none;">%2$s</pre>',
            $entry->getProperty('uid'),
            htmlspecialchars($extraData)
        );
    }
}
