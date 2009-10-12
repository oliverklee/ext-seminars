<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

require_once(t3lib_extMgm::extPath('lang') . 'lang.php');

/**
 * Class 'tx_seminars_flexForms' for the 'seminars' extension.
 *
 * This class is needed to dynamically create the list of selectable database
 * columns for the pi1 flex forms.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_flexForms {
	/**
	 * @var language the back-end language object
	 */
	private $language = null;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->language = t3lib_div::makeInstance('language');
		$this->language->init($GLOBALS['BE_USER']->uc['lang']);
		$this->language->includeLLFile('EXT:seminars/pi1/locallang.xml');
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->language);
	}

	/**
	 * Returns the configuration for the flex forms field
	 * "showFeUserFieldsInRegistrationsList" with the selectable database
	 * columns.
	 *
	 * @param array the flex forms configuration
	 *
	 * @return array the modified flex forms configuration including the
	 *               selectable database columns
	 */
	public function getShowFeUserFieldsInRegistrationsList(array $configuration) {
		foreach ($this->getColumnsOfTable('fe_users') as $column) {
			$label = $this->language->getLL('label_' . $column);

			if ($label == '') {
				$label = $column;
			}

			$configuration['items'][] = array(0 => $label, 1 => $column);
		}

		return $configuration;
	}

	/**
	 * Returns the configuration for the flex forms field
	 * "showRegistrationFieldsInRegistrationList" with the selectable database
	 * columns.
	 *
	 * @param array the flex forms configuration
	 *
	 * @return array the modified flex forms configuration including the
	 *               selectable database columns
	 */
	public function getShowRegistrationFieldsInRegistrationList(array $configuration) {
		foreach ($this->getColumnsOfTable(SEMINARS_TABLE_ATTENDANCES) as $column) {
			$label = $this->language->getLL(
				'label_' . ($column == 'uid' ? 'registration_' : '') .$column
			);

			if ($label == '') {
				$label = $column;
			}

			$configuration['items'][] = array(0 => $label, 1 => $column);
		}

		return $configuration;
	}

	/**
	 * Returns the column names of the table given in the first parameter
	 * $tableName.
	 *
	 * @param string the table name to get the columns for, must not be empty
	 *
	 * @return array the column names of the given table name, may not be empty
	 */
	private function getColumnsOfTable($tableName) {
		if ($tableName == '') {
			throw new Exception(
				'The first parameter $tableName must not be empty.'
			);
		}

		$columns = $GLOBALS['TYPO3_DB']->admin_get_fields($tableName);

		return array_keys($columns);
	}

	/**
	 * Returns the items of the given table for the flexforms.
	 *
	 * The table to retrieve the items for must have a title column.
	 *
	 * The given configuration array must apply to the following convention:
	 * - the sub-arrays "config", "row" and "items" must exist
	 * - "config" must have an element "itemTable" with a valid table name of a
	 *   table which has a title column
	 * - "row" must have an item "pid" with the current page ID
	 *
	 * @param array $configuration the flexforms configuration
	 *
	 * @return array the modified flexforms configuration including the items
	 *               available for selection
	 */
	public function getEntriesFromGeneralStoragePage(array $configuration) {
		$storagePid = 0;
		$whereClause = '1 = 1';
		$table = $configuration['config']['itemTable'];

		if (tx_oelib_configurationProxy::getInstance('seminars')
			->getConfigurationValueBoolean('useStoragePid')
		) {
			$rootlinePages = t3lib_befunc::BEgetRootLine(
				$configuration['row']['pid']
			);

			foreach ($rootlinePages as $page) {
				$storagePid = intval($page['storage_pid']);
				if ($storagePid > 0) {
					$whereClause = '(' . $table . '.pid = ' . $storagePid . ')';
					break;
				}
			}
		}

		$items = tx_oelib_db::selectMultiple(
			'uid,title',
			$table,
			$whereClause . tx_oelib_db::enableFields($table),
			'',
			'title ASC'
		);

		$configuration['items'] = array();
		foreach ($items as $item) {
			$configuration['items'][] = array(
				0 => $item['title'],
				1 => $item['uid'],
				2 => $GLOBALS['TCA'][$table]['ctrl']['iconfile']
			);
		}

		return $configuration;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_flexForms.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_flexForms.php']);
}
?>