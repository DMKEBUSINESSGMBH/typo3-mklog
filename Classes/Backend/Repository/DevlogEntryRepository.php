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

namespace DMK\Mklog\Backend\Repository;

use DMK\Mklog\Domain\Model\DevlogEntry;

/**
 * Devlog Entry Repository (legacy rn_base based repo for backend module).
 *
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryRepository extends \Sys25\RnBase\Domain\Repository\PersistenceRepository
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return DevlogEntry::TABLENAME;
    }

    /**
     * Liefert den Namen der Suchklasse.
     *
     * @return string
     */
    protected function getSearchClass()
    {
        return 'tx_rnbase_util_SearchGeneric';
    }

    /**
     * Liefert die Model Klasse.
     *
     * @return string
     */
    protected function getWrapperClass()
    {
        return 'DMK\\Mklog\\Backend\\Model\\DevlogEntry';
    }

    /**
     * Returns the latest log runs.
     *
     * @param int $limit
     *
     * @return array
     */
    public function getLatestRunIds(
        $limit = 50
    ) {
        $fields = $options = [];

        $options['what'] = 'DEVLOGENTRY.run_id';
        $options['groupby'] = 'DEVLOGENTRY.run_id';
        $options['orderby']['DEVLOGENTRY.run_id'] = 'DESC';
        $options['limit'] = (int) $limit;
        $options['collection'] = false;

        $items = $this->search($fields, $options);

        return $this->convertSingleSelectToFlatArray($items, 'run_id');
    }

    /**
     * Returns all extension keys who has logged into devlog.
     *
     * @return array
     */
    public function getLoggedExtensions()
    {
        $fields = $options = [];

        $options['what'] = 'DEVLOGENTRY.ext_key';
        $options['groupby'] = 'DEVLOGENTRY.ext_key';
        $options['orderby']['DEVLOGENTRY.ext_key'] = 'DESC';
        $options['collection'] = false;

        $items = $this->search($fields, $options);

        return $this->convertSingleSelectToFlatArray($items, 'ext_key');
    }

    /**
     * Flattens an single select array.
     *
     * @param string $field
     *
     * @return array
     */
    private function convertSingleSelectToFlatArray(
        array $items,
        $field
    ) {
        if (empty($items)) {
            return [];
        }

        $items = call_user_func_array('array_merge_recursive', $items);

        if (empty($items)) {
            return [];
        }

        if (!is_array($items[$field])) {
            $items[$field] = [$items[$field]];
        }

        return $items[$field];
    }

    /**
     * On default, return hidden and deleted fields in backend.
     */
    protected function prepareFieldsAndOptions(
        array &$fields,
        array &$options
    ) {
        // there is no tca for the table!
        $options['enablefieldsoff'] = true;
        parent::prepareFieldsAndOptions($fields, $options);
        $this->prepareGenericSearcher($options);
    }

    /**
     * Prepares the simple generic searcher.
     */
    protected function prepareGenericSearcher(
        array &$options
    ) {
        if (empty($options['searchdef']) || !is_array($options['searchdef'])) {
            $options['searchdef'] = [];
        }

        $model = $this->getEmptyModel();
        $options['searchdef'] = \Sys25\RnBase\Utility\Arrays::mergeRecursiveWithOverrule(
            // default searcher config
            [
                'usealias' => 1,
                'basetable' => $model->getTableName(),
                'basetablealias' => 'DEVLOGENTRY',
                'wrapperclass' => get_class($model),
                'alias' => [
                    'DEVLOGENTRY' => [
                        'table' => $model->getTableName(),
                    ],
                ],
            ],
            // searcher config overrides
            $options['searchdef']
        );
    }
}
