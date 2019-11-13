<?php

namespace DMK\Mklog\Domain\Repository;

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

\tx_rnbase::load('Tx_Rnbase_Domain_Repository_PersistenceRepository');

/**
 * Devlog Entry Repository.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryRepository extends \Tx_Rnbase_Domain_Repository_PersistenceRepository
{
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
        return 'DMK\\Mklog\\Domain\\Model\\DevlogEntryModel';
    }

    /**
     * Exists the table at the db?
     *
     * @return bool
     */
    public function isTableAvailable()
    {
        $db = $this->getConnection()->getDatabaseConnection();

        if ($db instanceof \tx_rnbase_util_db_IDatabase) {
            // @FIXME: $db is'nt TYPO3_DB with admin_get_fields. since new rn_base version it is tx_rnbase_util_db_IDatabase!
            // find a way to check if the table exists!
            return true;
        }

        $tableFields = $db->admin_get_fields(
            $this->getEmptyModel()->getTableName()
        );

        return !empty($tableFields);
    }

    /**
     * Exists the table at the db?
     */
    public function optimize()
    {
        static $optimized = false;

        // Only one optimize run per request
        if ($optimized) {
            return;
        }
        $optimized = true;

        $maxRows = \DMK\Mklog\Factory::getConfigUtility()->getMaxLogs();

        // no cleanup
        if (empty($maxRows)) {
            return;
        }

        // fetch current rows
        $numRows = $this->search(array(), array('count' => true));

        // there are log entries to delete
        if ($numRows > $maxRows) {
            // fetch the execution date from the latest log entry
            $collection = $this->search(
                array(),
                array(
                    'what' => 'run_id',
                    'offset' => $maxRows,
                    'limit' => 1,
                    'orderby' => array('DEVLOGENTRY.run_id' => 'DESC'),
                )
            );

            if ($collection->isEmpty()) {
                return;
            }
            $lastExec = reset($collection->first());
            // nothing found to delete!?
            if (empty($lastExec)) {
                return;
            }

            // delete all entries, older than the last exeution date!
            $this->getConnection()->doDelete(
                $this->getEmptyModel()->getTableName(),
                'run_id < '.$lastExec
            );
        }
    }

    /**
     * Persists an model.
     *
     * @param array|\Tx_Rnbase_Domain_Model_Data $options
     */
    public function persist(
        \Tx_Rnbase_Domain_Model_DomainInterface $model,
        $options = null
    ) {
        \tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');
        $options = \Tx_Rnbase_Domain_Model_Data::getInstance($options);

        // there is no tca, so skip this check!
        $options->setSkipTcaColumnElimination(true);

        parent::persist($model, $options);
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
        $fields = $options = array();

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
        $fields = $options = array();

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
            return array();
        }

        $items = call_user_func_array('array_merge_recursive', $items);

        if (empty($items)) {
            return array();
        }

        if (!is_array($items[$field])) {
            $items[$field] = array($items[$field]);
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
            $options['searchdef'] = array();
        }

        $model = $this->getEmptyModel();
        \tx_rnbase::load('tx_rnbase_util_Arrays');
        $options['searchdef'] = \tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
            // default searcher config
            array(
                'usealias' => 1,
                'basetable' => $model->getTableName(),
                'basetablealias' => 'DEVLOGENTRY',
                'wrapperclass' => get_class($model),
                'alias' => array(
                    'DEVLOGENTRY' => array(
                        'table' => $model->getTableName(),
                    ),
                ),
            ),
            // searcher config overrides
            $options['searchdef']
        );
    }
}
