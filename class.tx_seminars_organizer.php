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
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
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
			$dbResult = $this->retrieveRecord($organizerUid);
		}

		if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->getDataFromDbResult(
				$GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)
			);
		}

		return;
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_organizer.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_organizer.php']);
}

?>
