<?php
namespace DMK\Mklog\Backend\Lister;

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

use \DMK\Mklog\Backend\Decorator\DevlogEntryDecorator;

\tx_rnbase::load('Tx_Rnbase_Backend_Lister_AbstractLister');

/**
 * Devlog Entry lister
 *
 * @package TYPO3
 * @subpackage DMK\Mklog
 * @author Michael Wagner
 */
class DevlogEntryLister
	extends \Tx_Rnbase_Backend_Lister_AbstractLister
{
	/**
	 * The devlog entry repository
	 *
	 * @return Tx_Rnbase_Domain_Repository_InterfaceSearch
	 */
	protected function getRepository()
	{
		return \DMK\Mklog\Factory::getDevlogEntryRepository();
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
	 * Liefert die Spalten, in denen gesucht werden soll
	 *
	 * @return array
	 */
	protected function getSearchColumns()
	{
		return array(
			'DEVLOGENTRY.uid',
			'DEVLOGENTRY.ext_key',
			'DEVLOGENTRY.message',
		);
	}

	/**
	 * Initializes the filter array
	 *
	 * @return Tx_Rnbase_Backend_Lister_AbstractLister
	 */
	public function initFilter()
	{
		parent::initFilter();

		$filter = $this->getFilter();

		$filter->setRunid(
			$this->getModuleValue($this->getListerId() . 'Runid')
		);

		$filter->setSeverity(
			$this->getModuleValue($this->getListerId() . 'Severity')
		);
		// remove filter value, if empty
		if ($filter->getSeverity() === '') {
			$filter->unsSeverity();
		}

		$filter->setExtKey(
			$this->getModuleValue($this->getListerId() . 'ExtKey')
		);

		return $this;
	}

	/**
	 * Returns the complete search form
	 *
	 * @return 	string
	 */
	public function getSearchFormData()
	{
		$data = parent::getSearchFormData();

		$button = $data['updatebutton'];
		unset($data['updatebutton']);

		$filter = $this->getFilter();

		$data['runid'] = array(
			'field' => \Tx_Rnbase_Backend_Utility::getFuncMenu(
				$this->getOptions()->getPid(),
				'SET[' . $this->getListerId() . 'Runid]',
				$filter->getRunid(),
				$this->getLatestRuns()
			),
			'label' => '###LABEL_FILTER_RUNID###',
		);

		$data['severity'] = array(
			'field' => \Tx_Rnbase_Backend_Utility::getFuncMenu(
				$this->getOptions()->getPid(),
				'SET[' . $this->getListerId() . 'Severity]',
				$filter->getSeverity(),
				$this->getSeverityLevels()
			),
			'label' => '###LABEL_FILTER_SEVERITY###',
		);

		$data['extkeys'] = array(
			'field' => \Tx_Rnbase_Backend_Utility::getFuncMenu(
				$this->getOptions()->getPid(),
				'SET[' . $this->getListerId() . 'ExtKey]',
				$filter->getExtKey(),
				$this->getLoggedExtensions()
			),
			'label' => '###LABEL_FILTER_EXTKEYS###',
		);

		$data['updatebutton'] = $button;

		return $data;
	}

	/**
	 * Returns the latest log runs
	 *
	 * @return array
	 */
	public function getLatestRuns()
	{
		$repo = \DMK\Mklog\Factory::getDevlogEntryRepository();
		$latestRuns = $repo->getLatestRunIds();

		$items = array('' => '');

		foreach ($latestRuns as $id) {
			$items[$id] = strftime('%d.%m.%y %H:%M:%S', substr($id, 0, 10));
		}

		return $items;
	}

	/**
	 * Returns the severity levels
	 *
	 * @return array
	 */
	public function getSeverityLevels()
	{

		$items = array();
		$items[''] = '';
		foreach (\DMK\Mklog\Utility\SeverityUtility::getItems() as $id => $name) {
			$items[$id] = $id . ' - ' . ucfirst(strtolower($name));
		}

		return $items;
	}

	/**
	 * Returns all extension keys who has logged into devlog
	 *
	 * @return array
	 */
	public function getLoggedExtensions()
	{
		$repo = \DMK\Mklog\Factory::getDevlogEntryRepository();
		$extKeys = $repo->getLoggedExtensions();

		$items = array('' => '');

		/* @var $item \DMK\Mklog\Domain\Model\DevlogEntryModel */
		foreach ($extKeys as $extKey) {
			$items[$extKey] = $extKey;
		}

		return $items;
	}

	/**
	 * Initializes the fields and options for the repository search
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

		if ($filter->getSeverity() !== null) {
			$fields['DEVLOGENTRY.severity'][OP_LTEQ_INT] = $filter->getSeverity();
		}

		if ($filter->getExtKey()) {
			$fields['DEVLOGENTRY.ext_key'][OP_EQ] = $filter->getExtKey();
		}
	}

	/**
	 * The decorator to render the rows
	 *
	 * @return string
	 */
	protected function getDecoratorClass()
	{
		return 'DMK\\Mklog\\Backend\\Decorator\\DevlogEntryDecorator';
	}

	/**
	 * Liefert die Spalten für den Decorator.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	protected function addDecoratorColumns(
		array &$columns
	) {

		$columns['crdate'] = array(
			'title' => 'label_tableheader_crdate',
			'decorator' => $this->getDecorator(),
		);
		$columns['severity'] = array(
			'title' => 'label_tableheader_severity',
			'decorator' => $this->getDecorator(),
		);
		$columns['ext_key'] = array(
			'title' => 'label_tableheader_ext_key',
			'decorator' => $this->getDecorator(),
		);
		$columns['message'] = array(
			'title' => 'label_tableheader_message',
			'decorator' => $this->getDecorator(),
		);
		$columns['extra_data'] = array(
			'title' => 'label_tableheader_extra_data',
			'decorator' => $this->getDecorator(),
		);

		return $columns;
	}
}
