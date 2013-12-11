<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * This builder class creates customized category bag objects.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BagBuilder_Category extends tx_seminars_BagBuilder_Abstract {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_Bag_Category';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = 'tx_seminars_categories';

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
	 * @param string $eventUids
	 *        comma-separated list of UID of the events to which the category selection should be limited, may be empty,
	 *        all UIDs must be > 0
	 *
	 * @return void
	 */
	public function limitToEvents($eventUids) {
		if ($eventUids == '') {
			return;
		}

		if (!preg_match('/^(\d+,)*\d+$/', $eventUids)
			|| preg_match('/(^|,)0+(,|$)/', $eventUids)
		) {
			throw new InvalidArgumentException('$eventUids must be a comma-separated list of positive integers.', 1333292640);
		}

		$this->whereClauseParts['event'] = 'EXISTS (' .
			'SELECT * FROM tx_seminars_seminars_categories_mm' .
			' WHERE tx_seminars_seminars_categories_mm.uid_local IN(' .
			$eventUids . ') AND tx_seminars_seminars_categories_mm' .
			'.uid_foreign = tx_seminars_categories.uid)';

		$this->eventUids = $eventUids;
	}

	/**
	 * Sets the values of additionalTables, whereClauseParts and orderBy for the
	 * category bag.
	 * These changes are made so that the categories are sorted by the relation
	 * sorting set in the back end.
	 *
	 * Before this function can be called, limitToEvents has to be called.
	 *
	 * @return void
	 */
	public function sortByRelationOrder() {
		if ($this->eventUids == '') {
			throw new BadMethodCallException(
				'The event UIDs were empty. This means limitToEvents has not been called. LimitToEvents has to be called ' .
					'before calling this function.',
				1333292662
			);
		}

		$this->addAdditionalTableName('tx_seminars_seminars_categories_mm');
		$this->whereClauseParts['category'] = 'tx_seminars_categories.uid = ' .
			'tx_seminars_seminars_categories_mm.uid_foreign AND ' .
			'tx_seminars_seminars_categories_mm.uid_local IN (' .
			$this->eventUids . ')';
		$this->orderBy = 'tx_seminars_seminars_categories_mm.sorting ASC';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Category.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Category.php']);
}