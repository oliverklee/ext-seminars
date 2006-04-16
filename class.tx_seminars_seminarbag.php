<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2006 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_seminarbag' for the 'seminars' extension.
 *
 * This aggregate class holds a bunch of seminar objects and allows
 * to iterate over them.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_bag.php');

class tx_seminars_seminarbag extends tx_seminars_bag {
	/** Same as class name */
	var $prefixId = 'tx_seminars_seminar_seminarbag';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_seminarbag.php';

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;

	/**
	 * The constructor. Creates a seminar bag that (currently) contains all
	 * non-deleted and visible seminar records.
	 *
	 * @param	object		an instance of a registrationManager (must not be null)
	 * @param	string		string that will be prepended to the WHERE clause
	 *						using AND, e.g. 'pid=42' (the AND and the enclosing
	 *						spaces are not necessary for this parameter)
	 * @param	string		comma-separated names of additional DB tables used for JOINs, may be empty
	 * @param	string		GROUP BY clause (may be empty), must already by safeguarded against SQL injection
	 * @param	string		ORDER BY clause (may be empty), must already by safeguarded against SQL injection
	 * @param	string		LIMIT clause (may be empty), must already by safeguarded against SQL injection
	 *
	 * @access	public
	 */
	function tx_seminars_seminarbag(&$registrationManager, $queryParameters = '1', $additionalTableNames = '', $groupBy = '', $orderBy = '', $limit = '') {
		$this->registrationManager =& $registrationManager;

		// Although the parent class also calls init(), we need to call it
		// here already so that $this->tableSeminars is provided.
		$this->init();
		parent::tx_seminars_bag($this->tableSeminars, $queryParameters, $additionalTableNames, $groupBy, $orderBy, $limit);

		return;
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult as a source.
	 * If the current item cannot be created, $this->currentItem will be nulled out.
	 *
	 * $this->dbResult is ensured to be non-null when this function is called.
	 *
	 * @access	protected
	 */
	function createItemFromDbResult() {
		$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
		$this->currentItem =& new $seminarClassname($this->registrationManager, 0, $this->dbResult);

		// Null out the seminar object if has not been initialized properly,
		// e.g. when there was no more data from the DB.
		if (!$this->currentItem->isOk()) {
			$this->currentItem = null;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']);
}

?>
