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
 * Class 'tx_seminars_registration' for the 'seminars' extension.
 *
 * This class represents a registration/attendance.
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');

class tx_seminars_registration extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_registration';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_registration.php';

	/** associative array with the values from/for the DB */
	var $recordData = array();

	/** our seminar (object) */
	var $seminar = null;

	/** whether this attendance already is stored in the DB */
	var $isInDb = false;

	/**
	 * The constructor.
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	integer		the UID of the feuser who wants to sign up
	 * @param	array		associative array with the registration data the user has just entered
	 *
	 * @access public
	 */
	function tx_seminars_registration(&$seminar, $userUid, $registrationData) {
		$this->init();

		$this->seminar = $seminar;

		$this->recordData['seminar'] = $seminar->getUid();
		$this->recordData['user'] = $userUid;

		$this->recordData['interests'] = $registrationData['interests'];
		$this->recordData['expectations'] = $registrationData['expectations'];
		$this->recordData['background_knowledge'] = $registrationData['background_knowledge'];
		$this->recordData['known_from'] = $registrationData['known_from'];
		$this->recordData['notes'] = $registrationData['notes'];

		$this->recordData['pid'] = $this->getConfValue('attendancesPID');

		$this->createTitle();

		return;
	}

	/**
	 * Gets our title, containing:
	 *  the attendee's full name,
	 *  the seminar title,
	 *  the seminar date
	 *
	 * @return	String		the attendance title
	 *
	 * @access public
	 */
	function getTitle() {
		return $this->$this->recordData['title'];
	}

	/**
	 * Creates our title and writes it to $this->title.
	 *
	 * The title is constructed like this:
	 *   Name of Attendee / Title of Seminar seminardate
	 *
	 * @access private
	 */
	function createTitle() {
		$this->recordData['title'] = $this->getUserName().' / '.$this->seminar->getTitle().' '.$this->seminar->getDate();

		return;
	}

	/**
	 * Gets the attendee's uid.
	 *
	 * @return	integer		the attendee's feuser uid
	 *
	 * @access public
	 */
	function getUser() {
		return intval($this->recordData['user']);
	}

	/**
	 * Gets the attendee's (real) name
	 *
	 * @return	String		the attendee's name
	 *
	 * @access private
	 */
	function getUserName() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'name',
				'fe_users',
				'uid='.$this->getUser(),
				'',
				'',
				'');
		if ($dbResult) {
			$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$result = $dbResultAssoc['name'];
		} else {
			$result = '';
		}

		return $result;
	}

	/**
	 * Gets the seminar's uid.
	 *
	 * @return	integer		the seminar's uid
	 *
	 * @access public
	 */
	function getSeminar() {
		return intval($this->recordData['seminar']);
	}

	/**
	 * Gets whether this attendance has already been paid for.
	 *
	 * @return	boolean		whether this attendance has already been paid for
	 *
	 * @access public
	 */
	function getIsPaid() {
		trigger_error('Member function tx_seminars_registration->getIsPaid not implemented yet.');
	}

	/**
	 * Gets the date at which the user has paid for this attendance.
	 *
	 * @return	integer		the date at which the user has paid for this attendance
	 *
	 * @access public
	 */
	function getDatePaid() {
		trigger_error('Member function tx_seminars_registration->getDatePaid not implemented yet.');
	}

	/**
	 * Gets the method of payment.
	 *
	 * @return	integer		the uid of the method of payment (may be 0 if none is given)
	 *
	 * @access public
	 */
	function getMethodOfPayment() {
		trigger_error('Member function tx_seminars_registration->getMethodOfPayment not implemented yet.');
	}

	/**
	 * Gets whether the attendee has been at the seminar.
	 *
	 * @return	boolean		whether the attendee has attended the seminar
	 *
	 * @access public
	 */
	function getHasBeenThere() {
		trigger_error('Member function tx_seminars_registration->getHasBeenThere not implemented yet.');
	}

	/**
	 * Gets the attendee's special interests in the subject.
	 *
	 * @return	String		a description of the attendee's special interests (may be empty)
	 *
	 * @access public
	 */
	function getInterests() {
		return $this->recordData['interests'];
	}

	/**
	 * Gets the attendee's expectations for the seminar.
	 *
	 * @return	String		a description of the attendee's expectations for the seminar (may be empty)
	 *
	 * @access public
	 */
	function getExpectations() {
		return $this->recordData['expectations'];
	}

	/**
	 * Gets the attendee's background knowledge on the subject.
	 *
	 * @return	String		a description of the attendee's background knowledge (may be empty)
	 *
	 * @access public
	 */
	function getKnowledge() {
		return $this->recordData['background_knowledge'];
	}

	/**
	 * Gets where the attendee has heard about this seminar.
	 *
	 * @return	String		a description of where the attendee has heard about this seminar (may be empty)
	 *
	 * @access public
	 */
	function getKnownFrom() {
		return $this->recordData['known_from'];
	}

	/**
	 * Gets text from the "additional notes" field the attendee could fill at online registration.
	 *
	 * @return	String		additional notes on registration (may be empty)
	 *
	 * @access public
	 */
	function getNotes() {
		return $this->recordData['notes'];
	}

	/**
	 * Writes this registration to the DB.
	 *
	 * Parent page is $this->conf['attendancesPID].
	 *
	 * @return	boolean		true if everything went OK, false otherwise
	 */
	function commitToDb() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tableAttendances, $this->recordData);
		if ($dbResult) {
			$this->isInDb = true;
		}

		return $dbResult;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']);
}
