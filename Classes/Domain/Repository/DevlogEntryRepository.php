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
 * Devlog Entry Repository
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryRepository
	extends \Tx_Rnbase_Domain_Repository_PersistenceRepository
{
	/**
	 * Liefert den Namen der Suchklasse
	 *
	 * @return 	string
	 */
	protected function getSearchClass()
	{
		return 'tx_rnbase_util_SearchGeneric';
	}

	/**
	 * Liefert die Model Klasse.
	 *
	 * @return 	string
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
		$tablename = $this->getEmptyModel()->getTableName();
		$db = \Tx_Rnbase_Database_Connection::getInstance()->getDatabaseConnection();
		$tableFields = $db->admin_get_fields($tablename);

		return !empty($tableFields);
	}

	/**
	 * Exists the table at the db?
	 *
	 * @return void
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
					'orderby' => array('DEVLOGENTRY.run_id' => 'ASC'),
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
				'run_id < ' . $lastExec
			);
		}
	}

	/**
	 * Persists an model
	 *
	 * @param \Tx_Rnbase_Domain_Model_DomainInterface $model
	 * @param array|\Tx_Rnbase_Domain_Model_Data $options
	 *
	 * @return void
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
	 * Returns the latest log runs
	 *
	 * @param int $limit
	 *
	 * @return Tx_Rnbase_Domain_Collection_Base
	 */
	public function getLatestRuns(
		$limit = 50
	) {
		$fields = $options = array();

		$options['groupby'] = 'DEVLOGENTRY.run_id';
		$options['orderby']['DEVLOGENTRY.run_id'] = 'DESC';
		$options['limit'] = (int) $limit;
		$options['forcewrapper'] = 1;

		return $this->search($fields, $options);
	}

	/**
	 * Returns all extension keys who has logged into devlog
	 *
	 * @return Tx_Rnbase_Domain_Collection_Base
	 */
	public function getLoggedExtensions()
	{
		$fields = $options = array();

		$options['groupby'] = 'DEVLOGENTRY.ext_key';
		$options['orderby']['DEVLOGENTRY.ext_key'] = 'DESC';
		$options['forcewrapper'] = 1;

		return $this->search($fields, $options);
	}

	/**
	 * On default, return hidden and deleted fields in backend
	 *
	 * @param array $fields
	 * @param array $options
	 *
	 * @return void
	 */
	protected function prepareFieldsAndOptions(
		array &$fields,
		array &$options
	) {
		parent::prepareFieldsAndOptions($fields, $options);
		$this->prepareGenericSearcher($options);
		// there is no tca for the table!
		$options['enablefieldsoff'] = true;
	}

	/**
	 * Prepares the simple generic searcher
	 *
	 * @param array $options
	 *
	 * @return void
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
						'table' => $model->getTableName()
					)
				)
			),
			// searcher config overrides
			$options['searchdef']
		);
	}
}
