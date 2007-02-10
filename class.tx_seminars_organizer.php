<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_organizer' for the 'seminars' extension.
 *
 * This class represents an organizer.
 *
 * @author	Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');

class tx_seminars_organizer extends tx_seminars_objectfromdb {
	/** Same as class name */
	var $prefixId = 'tx_seminars_organizer';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_organizer.php';

	/**
	 * The constructor. Creates an organizer instance from a DB record.
	 *
	 * @param	integer		The UID of the organizer to retrieve from the DB.
	 * 						This parameter will be ignored if $dbResult is provided.
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 						If this parameter is provided, $uid will be ignored.
	 *
	 * @access	public
	 */
	function tx_seminars_organizer($organizerUid, $dbResult = null) {
		$this->init();
		$this->tableName = $this->tableOrganizers;

		if (!$dbResult) {
			$dbResult = $this->retrieveOrganizer($organizerUid);
		}

		if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->getDataFromDbResult($GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult));
		}

		return;
	}

	/**
	 * Checks whether a non-deleted and non-hidden organizer with a given UID exists in the DB.
	 *
	 * This method may be called statically.
	 *
	 * @param	string		string with a UID (need not necessarily be escaped, will be intval'ed)
	 *
	 * @return	boolean		true if a visible organizer with that UID exists; false otherwise.
	 *
	 * @access	public
	 */
	function existsOrganizer($organizerUid) {
		$result = is_numeric($organizerUid) && ($organizerUid);

		if ($result) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*) AS num',
				$this->tableOrganizers,
				'uid='.intval($organizerUid)
					.t3lib_pageSelect::enableFields($this->tableOrganizers),
				'',
				'',
				'');

			if ($dbResult) {
				$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$result = ($dbResultAssoc['num'] == 1);
			} else {
				$result = false;
			}
		}

		return (boolean) $result;
	}

	/**
	 * Retrieves a organizer from the database.
	 *
	 * @param	integer		The UID of the organizer to retrieve from the DB.
	 *
	 * @return	pointer		MySQL result pointer (of SELECT query)/DBAL object, null if the UID is invalid
	 *
	 * @access	private
	 */
	 function retrieveOrganizer($organizerUid) {
	 	if ($this->existsOrganizer($organizerUid)) {
		 	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$this->tableOrganizers,
				'uid='.intval($organizerUid)
					.t3lib_pageSelect::enableFields($this->tableOrganizers),
				'',
				'',
				'1');
	 	} else {
	 		$result = null;
	 	}

		return $result;
	 }

	/**
	 * Gets our UID.
	 *
	 * @return	integer		our UID (or 0 if there is an error)
	 *
	 * @access	public
	 */
	function getUid() {
		return $this->getRecordPropertyInteger('uid');
	}

	/**
	 * Gets our title.
	 *
	 * @return	string		our title (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getTitle() {
		return $this->getRecordPropertyString('title');
	}

	/**
	 * Gets our homepage.
	 *
	 * @return	string		our homepage (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getHomepage() {
		return $this->getRecordPropertyString('homepage');
	}

	/**
	 * Gets our e-mail address.
	 *
	 * @return	string		our e-mail address (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getEmail() {
		return $this->getRecordPropertyString('email');
	}

	/**
	 * Gets our e-mail footer.
	 *
	 * @return	string		our e-mail footer (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getEmailFooter() {
		return $this->getRecordPropertyString('email_footer');
	}
}
?>
