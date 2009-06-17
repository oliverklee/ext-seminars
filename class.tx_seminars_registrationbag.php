<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(PATH_tslib . 'class.tslib_content.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'tx_seminars_registrationbag' for the 'seminars' extension.
 *
 * This aggregate class holds a bunch of registration objects and allows
 * to iterate over them.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_registrationbag extends tx_seminars_bag {
	/**
	 * The constructor. Creates a registration bag that contains registration
	 * records and allows to iterate over them.
	 *
	 * @param string string that will be prepended to the WHERE
	 *               clause using AND, e.g. 'pid=42' (the AND and the
	 *               enclosing spaces are not necessary for this
	 *               parameter)
	 * @param string comma-separated names of additional DB tables used
	 *               for JOINs, may be empty
	 * @param string GROUP BY clause (may be empty), must already be
	 *               safeguarded against SQL injection
	 * @param string ORDER BY clause (may be empty), must already be
	 *               safeguarded against SQL injection
	 * @param string LIMIT clause (may be empty), must already be
	 *               safeguarded against SQL injection
	 */
	public function __construct(
		$queryParameters = '1=1', $additionalTableNames = '', $groupBy = '',
		$orderBy = 'uid', $limit = '', $showHiddenRecords = -1
	) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		parent::__construct(
			SEMINARS_TABLE_ATTENDANCES,
			$queryParameters,
			$additionalTableNames,
			$groupBy,
			$orderBy,
			$limit,
			$showHiddenRecords
		);
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult
	 * as a source. If the current item cannot be created, $this->currentItem
	 * will be nulled out.
	 *
	 * $this->dbResult is ensured to be not false when this function is called.
	 */
	protected function createItemFromDbResult() {
		$this->currentItem = tx_oelib_ObjectFactory::make(
			'tx_seminars_registration', $this->cObj, $this->dbResult
		);
		$this->valid();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationbag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationbag.php']);
}
?>