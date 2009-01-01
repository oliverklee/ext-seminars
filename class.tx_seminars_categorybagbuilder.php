<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

/**
 * Class 'tx_seminars_categorybagbuilder' for the 'seminars' extension.
 *
 * This builder class creates customized categorybag objects.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_categorybagbuilder extends tx_seminars_bagbuilder {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_categorybag';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = SEMINARS_TABLE_CATEGORIES;

	/**
	 * @var string the sorting field
	 */
	protected $orderBy = 'title';

	/**
	 * @var string the UIDs of the current events as commma-separated list,
	 *             will be set by limitToEvents
	 */
	protected $eventUids = '';

	/**
	 * Limits the bag to the categories of the events provided by the parameter
	 * $eventUids.
	 *
	 * Example: The events with the provided UIDs reference categories 9 and 12.
	 * So the bag will be limited to categories 9 and 12 (plus any additional
	 * limits).
	 *
	 * @param string comma-separated list of UID of the events to which
	 *               the category selection should be limited, may be
	 *               empty, all UIDs must be > 0
	 */
	public function limitToEvents($eventUids) {
		if ($eventUids == '') {
			return;
		}

		if (!preg_match('/^(\d+,)*\d+$/', $eventUids)
			|| preg_match('/(^|,)0+(,|$)/', $eventUids)
		) {
			throw new Exception(
				'$eventUids must be a comma-separated list of positive integers.'
			);
		}

		$this->whereClauseParts['event'] = 'EXISTS (' .
			'SELECT * FROM ' . SEMINARS_TABLE_SEMINARS_CATEGORIES_MM .
			' WHERE ' . SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.uid_local IN(' .
			$eventUids . ') AND ' . SEMINARS_TABLE_SEMINARS_CATEGORIES_MM .
			'.uid_foreign=' . SEMINARS_TABLE_CATEGORIES . '.uid' . ')';

		$this->eventUids = $eventUids;
	}

	/**
	 * Sets the values of additionalTables, whereClauseParts and orderBy for the
	 * category bag.
	 * These changes are made so that the categories are sorted by the relation
	 * sorting set in the back end.
	 *
	 * Before this function can be called, limitToEvents has to be called.
	 */
	public function sortByRelationOrder() {
		if ($this->eventUids == '') {
			throw new Exception(
				'The event UIDs were empty. This means limitToEvents has not ' .
				'been called. LimitToEvents has to be called before calling ' .
				'this function.'
			);
		}

		$this->addAdditionalTableName(SEMINARS_TABLE_SEMINARS_CATEGORIES_MM);
		$this->whereClauseParts['category'] = SEMINARS_TABLE_CATEGORIES . '.uid=' .
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.uid_foreign AND ' .
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.uid_local IN (' .
			$this->eventUids . ')';
		$this->orderBy = SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.sorting ASC';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_categorybagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_categorybagbuilder.php']);
}
?>