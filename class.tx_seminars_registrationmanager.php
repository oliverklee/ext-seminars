<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');

class tx_seminars_registrationmanager extends tx_seminars_dbplugin {
	/** Same as class name */
	var $prefixId = 'tx_seminars_registrationmanager';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_registrationmanager.php';

	/** the data of the current registration (tx_seminars_registration) */
	var $registration = null;

	/**
	 * The constructor.
	 *
	 * @access	public
	 */
	function tx_seminars_registrationmanager() {
		$this->init();
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

		if ($this->isLoggedIn() && !$this->couldThisUserRegister($seminar)) {
			// The current user can not register for this event (no multiple
			// registrations are possible and the user is already registered).
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

		if ($this->isLoggedIn() && $this->isUserBlocked($seminar)) {
			// The current user is already blocked for this event.
			$result = $this->pi_getLL('message_userIsBlocked');
		} elseif ($this->isLoggedIn() && !$this->couldThisUserRegister($seminar)) {
			// The current user can not register for this event (no multiple
			// registrations are possible and the user is already registered).
			$result = $this->pi_getLL('message_alreadyRegistered');
		} elseif (!$seminar->canSomebodyRegister()) {
			// it is not possible to register for this seminar at all (it is
			// canceled, full, etc.)
			$result = $seminar->canSomebodyRegisterMessage();
		}

		return $result;
	}

	/**
	 * Checks whether the current FE user (if any is logged in) could register
	 * for the current event, not checking the event's vacancies yet.
	 * So this function only checks whether the user is logged in and isn't
	 * blocked for the event's duration yet.
	 *
	 * @param	object		a seminar for which we'll check if it is possible to register (must not be null)
	 *
	 * @return	boolean		true if the user could register for the given event, false otherwise
	 *
	 * @access	protected
	 */
	function couldThisUserRegister(&$seminar) {
		// A user can register either if the event allows multiple registrations
		// or the user isn't registered yet and isn't blocked either.
		return $seminar->allowsMultipleRegistrations()
			|| (
				!$this->isUserRegistered($seminar)
				&& !$this->isUserBlocked($seminar)
			);
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
			// provide the login link
			$result = $plugin->getLoginLink(
				$plugin->pi_getLL('label_onlineRegistration'),
				$plugin->getConfValueInteger('registerPID'),
				$seminar->getUid()
			);
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
		return tx_seminars_objectfromdb::recordExists($seminarUid, $this->tableSeminars);
	}

	/**
	 * Checks whether a seminar UID is valid,
	 * ie. a non-deleted and non-hidden seminar with the given number exists.
	 *
	 * This function can be called even if no seminar object exists.
	 *
	 * @param	string		a given seminar UID (may not neccessarily be an integer)
	 *
	 * @return	string		empty string if the UID is valid, else a localized error message
	 *
	 * @access	public
	 */
	function existsSeminarMessage($seminarUid) {
		/** This is empty as long as no error has occured. */
		$message = '';

		if (!tx_seminars_objectfromdb::recordExists($seminarUid, $this->tableSeminars)) {
			$message = $this->pi_getLL('message_wrongSeminarNumber');
			header('Status: 404 Not Found');
		}

		return $message;
	}

	/**
	 * Checks whether a front-end user is already registered for this seminar.
	 *
	 * This method must not be called when no front-end user is logged in!
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
	 * This method must not be called when no front-end user is logged in!
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
	 * Checks whether a front-end user is already blocked during the time for
	 * a given event by other booked events.
	 *
	 * For this, only events that forbid multiple registrations are checked.
	 *
	 * @param	object		a seminar for which we'll check whether the user already is blocked by an other seminars
	 *
	 * @return	boolean		true if user is blocked by another registration, false otherwise
	 *
	 * @access	protected
	 */
	function isUserBlocked(&$seminar) {
		return $seminar->isUserBlocked($this->getFeUserUid());
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
	 * The additional notifications will only be sent if this is enabled in the TypoScript setup (which is the default).
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	array		associative array with the registration data the user has just entered
	 * @param	object		live plugin object (must not be null)
	 *
	 * @access	public
	 */
	function createRegistration(&$seminar, $registrationData, &$plugin) {
		// Add the total price to the array that contains all neccessary
		// informations before creating the registration object.
		if (isset($registrationData['seats']) && ($registrationData['seats'] > 0)) {
			$seats = $registrationData['seats'];
		} else {
			$seats = 1;
		}

		$registrationClassname = t3lib_div::makeInstanceClassName('tx_seminars_registration');
		$this->registration =& new $registrationClassname($plugin->cObj);
		$this->registration->setRegistrationData($seminar, $this->getFeUserUid(), $registrationData);
		$this->registration->commitToDb();

		$seminar->calculateStatistics();

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
