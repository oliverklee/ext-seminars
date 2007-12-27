<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_seminarbagbuilder' for the 'seminars' extension.
 *
 * This builder class creates customized seminarbag objects.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');

class tx_seminars_seminarbagbuilder {
	/**
	 * associative array with the WHERE clause parts (they will be concatenated
	 * with " AND " later)
	 */
	var $whereClauseParts = array();

	/** the sorting field */
	var $orderBy = '';

	/** the field by which the DB query result should be grouped */
	var $groupBy = '';

	/** the number of records to retrieve; leave empty to set no limit */
	var $limit = '';

	/** comma-separated list of additional table names for the query */
	var $additionalTableNames = '';

	/** whether the timing of events should be ignored */
	var $ignoreTimingOfRecords = false;

	/** whether hidden records should be shown, too */
	var $showHiddenRecords = false;

	/**
	 * The constructor. Currently does nothing.
	 *
	 * @access	public
	 */
	function tx_seminars_seminarbagbuilder() {
	}

	/**
	 * Creates and returns the customized bag.
	 *
	 * @return	tx_seminars_seminarbag	customized, newly-created seminar bag
	 *
	 * @access	public
	 */
	function build() {
		if (!empty($this->whereClauseParts)) {
			$whereClause = implode(' AND ', $this->whereClauseParts);
		} else {
			$whereClause = '1=1';
		}

		$seminarBagClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_seminarbag'
		);
		return new $seminarBagClassname(
			$whereClause,
			$this->additionalTableNames,
			$this->groupBy,
			$this->orderBy,
			$this->limit,
			($this->showHiddenRecords ? 1 : -1),
			$this->ignoreTimingOfRecords
		);
	}

	/**
	 * Configures the seminar bag to work like a BE list: It will use the
	 * default sorting in the BE, and hidden records will be shown.
	 *
	 * @access	public
	 */
	function setBackEndMode() {
		$this->useBackEndSorting();

		$this->showHiddenRecords = true;
		$this->ignoreTimingOfRecords = true;
	}

	/**
	 * Sets the sorting to be the same as in the BE.
	 *
	 * @access	public
	 */
	function useBackEndSorting() {
		// unserializes the configuration array
		$globalConfiguration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']
		);
		$this->orderBy = ($globalConfiguration['useManualSorting'])
			? 'sorting' : 'begin_date';
	}

	/**
	 * Sets the PIDs of the system folders that contain the event records.
	 *
	 * @param	string		comma-separated list of PIDs of the system folders
	 * 						with the event records; must not be empty; need not
	 * 						be safeguarded against SQL injection
	 *
	 * @access	public
	 */
	function setSourcePages($sourcePagePids) {
		if (!preg_match('/^([\d+,] *)*\d+$/', $sourcePagePids)) {
			return;
		}

		$this->whereClauseParts['pages'] = 'pid IN('.$sourcePagePids.')';
	}

	/**
	 * Checks whether some source pages have already been set.
	 *
	 * @return	boolean		true if source pages have already been set, false
	 * 						otherwise
	 *
	 * @access	public
	 */
	public function hasSourcePages() {
		return isset($this->whereClauseParts['pages']);
	}

	/**
	 * Sets the created bag to only take records into account that have been
	 * created with the oelib testing framework.
	 *
	 * @access	public
	 */
	function setTestMode() {
		$this->whereClauseParts['tests'] = 'is_dummy_record = 1';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']);
}

?>
