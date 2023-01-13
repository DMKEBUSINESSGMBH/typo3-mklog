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

namespace DMK\Mklog\Backend\Lister;

use DMK\Mklog\Backend\Decorator\DevlogEntryDecorator;
use DMK\Mklog\Backend\Repository\DevlogEntryRepository;
use DMK\Mklog\Factory;

/**
 * Devlog Entry lister.
 *
 * @author Michael Wagner
 */
class DevlogEntryLister extends \Sys25\RnBase\Backend\Lister\AbstractLister
{
    /**
     * The devlog entry repository.
     *
     * @return DevlogEntryRepository
     */
    protected function getRepository()
    {
        return new DevlogEntryRepository();
    }

    /**
     * The unique id for the lister.
     * It is recommended the childclass extends this method!
     *
     * @return string
     */
    protected function getListerId()
    {
        return 'mklogDevlogEntry';
    }

    /**
     * Liefert die Spalten, in denen gesucht werden soll.
     *
     * @return array
     */
    protected function getSearchColumns()
    {
        return [
            'DEVLOGENTRY.uid',
            'DEVLOGENTRY.ext_key',
            'DEVLOGENTRY.message',
        ];
    }

    /**
     * Initializes the filter array.
     *
     * @return self
     */
    public function initFilter()
    {
        parent::initFilter();

        $filter = $this->getFilter();

        $filter->setRunid(
            $this->getModuleValue($this->getListerId().'Runid')
        );

        $filter->setSeverity(
            $this->getModuleValue($this->getListerId().'Severity')
        );
        // remove filter value, if empty
        if ('' === $filter->getSeverity()) {
            $filter->unsSeverity();
        }

        $filter->setExtKey(
            $this->getModuleValue($this->getListerId().'ExtKey')
        );

        return $this;
    }

    /**
     * Returns the complete search form.
     *
     * @return string
     */
    public function getSearchFormData()
    {
        $data = parent::getSearchFormData();

        $button = $data['updatebutton'];
        unset($data['updatebutton']);

        $filter = $this->getFilter();

        $data['runid'] = [
            'field' => \Sys25\RnBase\Backend\Utility\BackendUtility::getFuncMenu(
                $this->getOptions()->getPid(),
                'SET['.$this->getListerId().'Runid]',
                $filter->getRunid(),
                $this->getLatestRuns()
            ),
            'label' => '###LABEL_FILTER_RUNID###',
        ];

        $data['severity'] = [
            'field' => \Sys25\RnBase\Backend\Utility\BackendUtility::getFuncMenu(
                $this->getOptions()->getPid(),
                'SET['.$this->getListerId().'Severity]',
                $filter->getSeverity(),
                $this->getSeverityLevels()
            ),
            'label' => '###LABEL_FILTER_SEVERITY###',
        ];

        $data['extkeys'] = [
            'field' => \Sys25\RnBase\Backend\Utility\BackendUtility::getFuncMenu(
                $this->getOptions()->getPid(),
                'SET['.$this->getListerId().'ExtKey]',
                $filter->getExtKey(),
                $this->getLoggedExtensions()
            ),
            'label' => '###LABEL_FILTER_EXTKEYS###',
        ];

        $data['updatebutton'] = $button;

        return $data;
    }

    /**
     * Returns the latest log runs.
     *
     * @return array
     */
    public function getLatestRuns()
    {
        $repo = new DevlogEntryRepository();
        $latestRuns = $repo->getLatestRunIds();

        $items = ['' => ''];

        foreach ($latestRuns as $id) {
            $items[$id] = strftime('%d.%m.%y %H:%M:%S', substr($id, 0, 10));
        }

        return $items;
    }

    /**
     * Returns the severity levels.
     *
     * @return array
     */
    public function getSeverityLevels()
    {
        $items = [];
        $items[''] = '';
        foreach (\DMK\Mklog\Utility\SeverityUtility::getItems() as $id => $name) {
            $items[$id] = $id.' - '.ucfirst(strtolower($name));
        }

        return $items;
    }

    /**
     * Returns all extension keys who has logged into devlog.
     *
     * @return array
     */
    public function getLoggedExtensions()
    {
        $repo = new DevlogEntryRepository();
        $extKeys = $repo->getLoggedExtensions();

        $items = ['' => ''];

        /* @var $item \DMK\Mklog\Domain\Model\DevlogEntry */
        foreach ($extKeys as $extKey) {
            $items[$extKey] = $extKey;
        }

        return $items;
    }

    /**
     * Initializes the fields and options for the repository search.
     */
    protected function prepareFieldsAndOptions(
        array &$fields,
        array &$options
    ) {
        parent::prepareFieldsAndOptions($fields, $options);

        $options['orderby']['DEVLOGENTRY.run_id'] = 'DESC';
        $options['orderby']['DEVLOGENTRY.crdate'] = 'DESC';
        $options['orderby']['DEVLOGENTRY.uid'] = 'DESC';

        if ($this->getOptions()->getPid() > 0) {
            $fields['DEVLOGENTRY.pid'][OP_EQ_INT] = $this->getOptions()->getPid();
        }

        $filter = $this->getFilter();

        if ($filter->getRunid() > 0) {
            $fields['DEVLOGENTRY.run_id'][OP_EQ] = $filter->getRunid();
        }

        if (null !== $filter->getSeverity()) {
            $fields['DEVLOGENTRY.severity'][OP_LTEQ_INT] = $filter->getSeverity();
        }

        if ($filter->getExtKey()) {
            $fields['DEVLOGENTRY.ext_key'][OP_EQ] = $filter->getExtKey();
        }
    }

    /**
     * The decorator instace.
     *
     * @return Tx_Rnbase_Backend_Decorator_InterfaceDecorator
     */
    protected function getDecorator()
    {
        if (!$this->getStorage()->hasDecorator()) {
            $decorator = Factory::makeInstance(
                DevlogEntryDecorator::class,
                $this->getModule(),
                $this->getOptions()
            );
            $this->getStorage()->setDecorator($decorator);
        }

        return $this->getStorage()->getDecorator();
    }

    /**
     * Liefert die Spalten für den Decorator.
     *
     * @return array
     */
    protected function addDecoratorColumns(
        array &$columns
    ) {
        $columns['crdate'] = [
            'title' => 'label_tableheader_crdate',
            'decorator' => $this->getDecorator(),
        ];
        $columns['severity'] = [
            'title' => 'label_tableheader_severity',
            'decorator' => $this->getDecorator(),
        ];
        $columns['ext_key'] = [
            'title' => 'label_tableheader_ext_key',
            'decorator' => $this->getDecorator(),
        ];
        $columns['message'] = [
            'title' => 'label_tableheader_message',
            'decorator' => $this->getDecorator(),
        ];
        $columns['extra_data'] = [
            'title' => 'label_tableheader_extra_data',
            'decorator' => $this->getDecorator(),
        ];

        return $columns;
    }
}
