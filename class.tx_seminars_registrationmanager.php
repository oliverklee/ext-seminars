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
 * Class 'tx_seminars_registrationmanager' for the 'seminars' extension.
 *
 * This utility class checks and creates registrations for seminars.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');

class tx_seminars_registrationmanager extends tx_seminars_dbplugin {
	/** Same as class name */
	var $prefixId = 'tx_seminars_registrationmanager';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_registrationmanager.php';

	/** The frontend user who currently is logged in. */
	var $feuser;

	/** the data of the current registration (tx_seminars_registration) */
	var $registration = null;

	/**
	 * The constructor.
	 *
	 * @access	public
	 */
	function tx_seminars_registrationmanager() {
		$this->init();
		$this->retrieveFEUser();
	}

	/**
	 * Retrieves the currently logged-in FE user (if at all) and store it in
	 * $this->feuser.
	 *
	 * Note that this will not be null if no user is logged in.
	 *
	 * @access	private
	 */
	function retrieveFEUser() {
		$this->feuser = $GLOBALS['TSFE']->fe_user->user;
	}

	/**
	 * Checks whether a front end user is logged in.
	 *
	 * @return	boolean		true if a user is logged in, false otherwise
	 *
	 * @access	public
	 */
	function isLoggedIn() {
		return $GLOBALS['TSFE']->loginUser;
	}

	/**
	 * Checks whether is possible to register for a given seminar at all:
	 * if a possibly logged-in user hasn't registered yet for this seminar,
	 * if the seminar isn't canceled, full etc.
	 *
	 * If no user is logged in, it is just checked whether somebody could register
	 * for this seminar.
	 *
	 * Returns true if everything is okay, false otherwise.
	 *
	 * This function even works if no user is logged in.
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register (must not be null)
	 *
	 * @return	boolean		true if everything is okay for the link, false otherwise
	 *
	 * @access	public
	 */
	function canRegisterIfLoggedIn(&$seminar) {
		$result = true;

		if ($this->isLoggedIn() && $this->isUserRegistered($seminar)) {
			// a user is logged in and is already registered for that seminar
			$result = false;
		} else {
			// it is not possible to register for this seminar at all (it is canceled, full, etc.)
			$result = $seminar->canSomebodyRegister();
		}

		return $result;
	}

	/**
	 * Checks whether is possible to register for a given seminar at all:
	 * if a possibly logged-in user hasn't registered yet for this seminar,
	 * if the seminar isn't canceled, full etc.
	 *
	 * If no user is logged in, it is just checked whether somebody could register
	 * for this seminar.
	 *
	 * Returns a message if there is anything to complain about
	 * and an empty string otherwise.
	 *
	 * This function even works if no user is logged in.
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register (must not be null)
	 *
	 * @return	string		error message or empty string
	 *
	 * @access	public
	 */
	function canRegisterIfLoggedInMessage(&$seminar) {
		$result = '';

		if ($this->isLoggedIn() && $this->isUserRegistered($seminar)) {
			// a user is logged in and is already registered for that seminar
			$result = $this->pi_getLL('message_alreadyRegistered');
		} elseif (!$seminar->canSomebodyRegister()) {
			// it is not possible to register for this seminar at all (it is canceled, full, etc.)
			$result = $seminar->canSomebodyRegisterMessage();
		}

		return $result;
	}

	/**
	 * Creates an HTML link to either the registration page (if a user is logged in)
	 * or the login page (if no user is logged in).
	 *
	 * Before you can call this function, you should make sure that the link makes sense
	 * (ie. the seminar still has vacancies, the user hasn't registered for this seminar etc.).
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page) which we can call pi_linkTP() on (must not be null)
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register (may be null), if this is null, the seminar UID parameter will be disregarded
	 *
	 * @return	string		HTML code with the link
	 *
	 * @access	public
	 */
	function getLinkToRegistrationOrLoginPage(&$plugin, &$seminar) {
		if ($this->isLoggedIn()) {
			// provide the registration link
			$result = $plugin->cObj->getTypoLink(
				$plugin->pi_getLL('label_onlineRegistration'),
				$plugin->getConfValueInteger('registerPID'),
				array('tx_seminars_pi1[seminar]' => $seminar->getUid())
			);
		} else {
			// provide a link to the login form
			$result = $plugin->cObj->getTypoLink($this->pi_getLL('message_notLoggedIn'),
				$plugin->getConfValueInteger('loginPID'));
		}

		return $result;
	}

	/**
	 * Checks whether a seminar UID is valid,
	 * ie. a non-deleted and non-hidden seminar with the given number exists.
	 *
	 * This function can be called even if no seminar object exists.
	 *
	 * @param	string		a given seminar UID (may not neccessarily be an integer)
	 *
	 * @return	boolean		true the UID is valid, false otherwise
	 *
	 * @access	public
	 */
	function existsSeminar($seminarUid) {
		// We can't use t3lib_div::makeInstanceClassName in this case as we
		// cannot use a class function when using a variable as class name.
		return tx_seminars_seminar::existsSeminar($seminarUid);
	}

	/**
	 * Checks whether a seminar UID is valid,
	 * ie. a non-deleted and non-hidden seminar with the given number exists.
	 *
	 * This function can be called even if no seminar object exists.
	 *
	 * @param	string		a given seminar UID (may not neccessarily be an integer)
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page) which we can call pi_list_linkSingle() on (must not be null)
	 *
	 * @return	string		empty string if the UID is valid, else a localized error message
	 *
	 * @access	public
	 */
	function existsSeminarMessage($seminarUid, &$plugin) {
		/** This is empty as long as no error has occured. */
		$message = '';

		if (!tx_seminars_seminar::existsSeminar($seminarUid)) {
			$message = $this->pi_getLL('message_wrongSeminarNumber');
			header('Status: 404 Not Found');
		}

		return $message;
	}
	/**
	 * Checks whether it is possible to register for a given seminar at all
	 * and the logged in user can register for it.
	 *
	 * Before calling this method, make sure that a user is logged in.
	 *
	 * This method may only be called when a seminar object (with a valid UID) exists.
	 *
	 * XXX This function may be needed for the drop-down-list on the registration page
	 * and else should go away.
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register (may not be null)
	 *
	 * @return	boolean		true if it is possible to register, false otherwise
	 *
	 * @access	public
	 */
	function canUserRegisterForSeminar(&$seminar) {
		return (!$this->isUserRegistered($seminar) && $seminar->canSomebodyRegister());
	}

	/**
	 * Checks whether it is possible to register for a given seminar at all
	 * and the logged in user can register for it.
	 *
	 * Before calling this method, make sure that a user is logged in.
	 *
	 * This method may only be called when a seminar object (with a valid UID) exists.
	 *
	 * XXX This function may be needed for the drop-down-list on the registration page
	 * and else should go away.
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register (may not be null)
	 *
	 * @return	string		empty string if everything is OK, else a localized error message.
	 *
	 * @access	public
	 */
	function canUserRegisterForSeminarMessage(&$seminar) {
		if ($this->isUserRegistered($seminar)) {
			$message = $this->isUserRegisteredMessage($seminar);
		} else {
			$message = $seminar->canSomebodyRegisterMessage();
		}
		return $message;
	}

	/**
	 * Returns the UID of the currently logged-in FE user
	 * or 0 if no FE user is logged in.
	 *
	 * @return	integer		the UID of the logged-in FE user or 0 if no FE user is logged in
	 *
	 * @access	public
	 */
	function getFeUserUid() {
		return ($this->isLoggedIn() ? intval($this->feuser['uid']) : 0);
	}

	/**
	 * Checks whether a front end user is already registered for this seminar.
	 *
	 * This method must not be called when no front end user is logged in!
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register
	 *
	 * @return	boolean		true if user is already registered, false otherwise.
	 *
	 * @access	public
	 */
	function isUserRegistered(&$seminar) {
		return $seminar->isUserRegistered($this->getFeUserUid());
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register
	 *
	 * @return	string		empty string if everything is OK, else a localized error message.
	 *
	 * @access	public
	 */
	function isUserRegisteredMessage(&$seminar) {
		return $seminar->isUserRegisteredMessage($this->getFeUserUid());
	}

	/**
	 * Checks whether the data the user has just entered is okay for creating
	 * a registration, e.g. mandatory fields are filled, number fields only
	 * contain numbers, the number of seats to register is not too high etc.
	 *
	 * Please note that this function doesn't create a registration - it just checks.
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	array		associative array with the registration data the user has just entered
	 *
	 * @return	boolean		true if the data is okay, false otherwise
	 *
	 * @access	public
	 */
	function canCreateRegistration(&$seminar, $registrationData) {
		return $this->canRegisterSeats($seminar, $registrationData['seats']);
	}

	/**
	 * Checks whether the data the user has just entered is okay for creating
	 * a registration, e.g. mandatory fields are filled, number fields only
	 * contain numbers, the number of seats to register is not too high etc.
	 *
	 * This function returns an empty string if everything is okay and a
	 * localized error message otherwise.
	 *
	 * Please note that this function doesn't create a registration - it just checks.
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	array		associative array with the registration data the user has just entered
	 *
	 * @return	string		an empty string if everything is okay, otherwise a localized error message
	 *
	 * @access	public
	 */
	function canCreateRegistrationMessage(&$seminar, $registrationData) {
		return ($this->canRegisterSeats($seminar, $registrationData['seats'])) ?
			'' :
			sprintf($this->pi_getLL('message_invalidNumberOfSeats'),
				$seminar->getVacancies());
	}

	/**
	 * Checks whether a registration with a given number of seats could be created,
	 * ie. an actual number is given and there are at least that many vacancies.
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	string		the number of seats to check (should be an integer, but we can't be sure of this)
	 *
	 * @return	boolean		true if there are at least that many vacancies, false otherwise.
	 *
	 * @access	private
	 */
	function canRegisterSeats(&$seminar, $numberOfSeats) {
		$numberOfSeats = trim($numberOfSeats);

		// If no number of seats is given, ie. the user has not entered anything
		// or the field is not shown at all, assume 1.
		if (($numberOfSeats == '') || ($numberOfSeats == '0')) {
			$numberOfSeats = '1';
		}

		$numberOfSeatsInt = intval($numberOfSeats);

		// Check whether we have a valid number
		if ($numberOfSeats == strval($numberOfSeatsInt)) {
			$result = ($seminar->getVacancies() >= $numberOfSeatsInt);
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Creates a registration to $this->registration, writes it to DB,
	 * and notifies the organizer and the user (both via e-mail).
	 *
	 * The additinal notifications will only be sent if this is enabled in the TypoScript setup (which is the default).
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	array		associative array with the registration data the user has just entered
	 * @param	object		live plugin object (must not be null)
	 *
	 * @access	public
	 */
	function createRegistration(&$seminar, $registrationData, &$plugin) {
			$registrationClassname = t3lib_div::makeInstanceClassName('tx_seminars_registration');
			$this->registration =& new $registrationClassname($seminar, $this->getFeUserUid(), $registrationData, $plugin->cObj);

			$this->registration->commitToDb();
			$seminar->updateStatistics();
			$this->registration->notifyAttendee($plugin);
			$this->registration->notifyOrganizers($plugin);
			if ($this->getConfValueBoolean('sendAdditionalNotificationEmails')) {
				$this->registration->sendAdditionalNotification($plugin);
			}

			return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']);
}

?>
