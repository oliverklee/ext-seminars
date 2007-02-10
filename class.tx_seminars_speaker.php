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
 * Class 'tx_seminars_speaker' for the 'seminars' extension.
 *
 * This class represents a speaker.
 *
 * @author	Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');

class tx_seminars_speaker extends tx_seminars_objectfromdb {
	/** Same as class name */
	var $prefixId = 'tx_seminars_speaker';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_speaker.php';

	/**
	 * The constructor. Creates a speaker instance from a DB record.
	 *
	 * @param	integer		The UID of the speaker to retrieve from the DB.
	 * 						This parameter will be ignored if $dbResult is provided.
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 						If this parameter is provided, $uid will be ignored.
	 *
	 * @access	public
	 */
	function tx_seminars_speaker($speakerUid, $dbResult = null) {
		$this->init();
		$this->tableName = $this->tableSpeakers;

		if (!$dbResult) {
			$dbResult = $this->retrieveRecord($speakerUid);
		}

		if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->getDataFromDbResult($GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult));
		}

		return;
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
	 * Gets our organization.
	 *
	 * @return	string		our organization (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getOrganization() {
		return $this->getRecordPropertyString('organization');
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
	 * Gets our description.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	string		our description (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getDescription(&$plugin) {
		return $plugin->pi_RTEcssText($this->getRecordPropertyString('description'));
	}

	/**
	 * Gets our internal notes.
	 *
	 * @return	string		our internal notes (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getNotes() {
		return $this->getRecordPropertyString('notes');
	}

	/**
	 * Gets our address.
	 *
	 * @return	string		our address (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getAddress() {
		return $this->getRecordPropertyString('address');
	}

	/**
	 * Gets our work phone number.
	 *
	 * @return	string		our work phone number (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getPhoneWork() {
		return $this->getRecordPropertyString('phone_work');
	}

	/**
	 * Gets our home phone number.
	 *
	 * @return	string		our home phone number (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getPhoneHome() {
		return $this->getRecordPropertyString('phone_home');
	}

	/**
	 * Gets our mobile phone number.
	 *
	 * @return	string		our mobile phone number (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getPhoneMobile() {
		return $this->getRecordPropertyString('phone_mobile');
	}

	/**
	 * Gets our fax number.
	 *
	 * @return	string		our fax number (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getFax() {
		return $this->getRecordPropertyString('fax');
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
}
?>
