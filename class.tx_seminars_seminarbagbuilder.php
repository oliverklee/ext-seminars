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

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_bagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');

class tx_seminars_seminarbagbuilder extends tx_seminars_bagbuilder {
	/** class name of the bag class that will be built */
	var $bagClassName = 'tx_seminars_seminarbag';

	/**
	 * Configures the seminar bag to work like a BE list: It will use the
	 * default sorting in the BE, and hidden records will be shown.
	 *
	 * @access	public
	 */
	function setBackEndMode() {
		$this->useBackEndSorting();
		parent::setBackEndMode();
	}

	/**
	 * Sets the sorting to be the same as in the BE.
	 *
	 * @access	private
	 */
	function useBackEndSorting() {
		// unserializes the configuration array
		$globalConfiguration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']
		);
		$this->orderBy = ($globalConfiguration['useManualSorting'])
			? 'sorting' : 'begin_date DESC';
	}

	/**
	 * Limits the bag to events from the category with the UID provided as the
	 * parameter $categoryUid.
	 *
	 * @param	integer		UID of the category which the bag should limited to,
	 * 						must be > 0
	 */
	function limitToCategory($categoryUid) {
		if ($categoryUid <= 0) {
			return;
		}

		$this->whereClauseParts['category'] = 'EXISTS (SELECT * FROM '
			.SEMINARS_TABLE_CATEGORIES_MM.' WHERE '
			.SEMINARS_TABLE_CATEGORIES_MM.'.uid_local='
			.SEMINARS_TABLE_SEMINARS.'.uid AND '
			.SEMINARS_TABLE_CATEGORIES_MM.'.uid_foreign='.$categoryUid
			.')';
	}

	/**
	 * Sets the bag to ignore canceled events.
	 *
	 * @access	public
	 */
	function ignoreCanceledEvents() {
		$this->whereClauseParts['hideCanceledEvents'] = 'cancelled=0';
	}

	/**
	 * Allows the bag to include canceled events again.
	 *
	 * @access	public
	 */
	function allowCanceledEvents() {
		unset($this->whereClauseParts['hideCanceledEvents']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']);
}

?>
