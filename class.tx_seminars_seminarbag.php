<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Class 'tx_seminars_seminarbag' for the 'seminars' extension.
 *
 * This aggregate class holds a bunch of seminar objects and allows
 * to iterate over them.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');

class tx_seminars_seminarbag extends tx_seminars_dbplugin {
	/** Same as class name */
	var $prefixId = 'tx_seminars_seminar_seminarbag';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_seminarbag.php';

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;
	/** an SQL query result (not yet converted to an associative array) */
	var $dbResultSeminars = null;
	/** the current seminar object (may be null) */
	var $currentSeminar = null;

	/**
	 * The constructor. Creates a seminar bag that (currently) contains all
	 * non-deleted and visible seminar records.
	 *
	 * @param	object		An instance of a registrationManager (may not be null)
	 *
	 * @return	boolean		true if the seminar bag has been properly initialized,
	 * 						false otherwise (eg. on DB problems)
	 *
	 * @access	public
	 */
	function tx_seminars_seminarbag(&$registrationManager) {
		$this->registrationManager =& $registrationManager;
		$this->init();

		return $this->resetToFirst();
	}

	/**
	 * Sets the iterator to the first seminar.
	 *
	 * @return	boolean		true if everything went okay, false otherwise
	 *
	 * @access	public
	 */
	function resetToFirst() {
		$result = false;

		// free old results if there are any
		if ($this->dbResultSeminars) {
			$GLOBALS['TYPO3_DB']->sql_free_result($this->dbResultSeminars);
			// we don't need to null out $this->dbResultSeminars as this will be
			// rewritten immediately
		}

		$this->dbResultSeminars =& $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$this->tableSeminars,
			'1'.t3lib_pageSelect::enableFields($this->tableSeminars),
			'',
			'',
			''
		);

		if ($this->dbResultSeminars) {
			$result = (boolean) $this->getNext();
		}

		return $result;
	}

	/**
	 * Advances to the next event record and returns a reference to that object.
	 *
	 * @return	object		the now current seminar object (may be null if there is no next seminar)
	 *
	 * @access	public
	 */
	function getNext() {
		if ($this->dbResultSeminars) {
			$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
			$this->currentSeminar =& new $seminarClassname($this->registrationManager, 0, $this->dbResultSeminars);

			// Null out the seminar object if has not been initialized properly,
			// e.g. when there was no more data from the DB.
			if (!$this->currentSeminar->isOk()) {
				$this->currentSeminar = null;
			}
		} else {
			$this->currentSeminar = null;
		}

		return $this->getCurrent();
	}

	/**
	 * Returns the current seminar object (which may be null).
	 *
	 * @return	object		the current seminar object (may be null if there is no current object)
	 *
	 * @access	public
	 */
	function getCurrent() {
		return $this->currentSeminar;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']);
}
