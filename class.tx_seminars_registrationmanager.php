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
 * Class 'tx_seminars_registrationmanager' for the 'seminars' extension.
 * 
 * This utility class checks and creates registrations for seminars.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
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
	 * @access public
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
	 * @access private
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
	 * Checks whether it is generally possible to register
	 * (without looking at a certain seminar):
	 * If a user is logged in and a non-deleted and non-hidden seminar with the given number exists.
	 * 
	 * This function can be called even if no seminar object exists.
	 * 
	 * Note: This function does not check whether the logged-in user could register for a certain seminar.
	 * 
	 * @param	String		a given seminar UID (may not neccessarily be an integer)
	 * 
	 * @return	boolean		true if it is possible to register, false otherwise
	 * 
	 * @access public
	 */
	function canGenerallyRegister($seminarUid) {
		// We can't use t3lib_div::makeInstanceClassName in this case as we
		// cannot use a class function when using a variable as class name. 
		return ($this->isLoggedIn()) && (tx_seminars_seminar::existsSeminar($seminarUid));
	}

	/**
	 * Checks whether it is generally possible to register
	 * (without looking at a certain seminar):
	 * If a user is logged in and a non-deleted and non-hidden seminar with the given number exists.
	 * 
	 * This function can be called even if no seminar object exists.
	 * 
	 * Note: This function does not check whether the logged-in user could register for a certain seminar.
	 * 
	 * @param	String		a given seminar UID (may not neccessarily be an integer)
	 * 
	 * @return	string		empty string if everything is OK, else a localized error message.
	 * 
	 * @access public
	 */
	function canGenerallyRegisterMessage($seminarUid) {
		/** This is empty as long as no error has occured. */
		$message = '';
	
		if (!$this->isLoggedIn()) {
			$message = $this->pi_getLL('message_notLoggedIn');
		} elseif (!tx_seminars_seminar::existsSeminar($seminarUid)) {
			$message = $this->pi_getLL('message_wrongSeminarNumber');
		}
		
		return $message;
	}

	/**
	 * Checks whether it the logged in user can register for a given seminar.
	 * 
	 * This method may only be called when a seminar object (with a valid UID) exists.
	 * 
	 * XXX Currently, this method is not used. Check whether we'll need it or else remove it
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
	 * Checks whether it the logged in user can register for a given seminar.
	 * 
	 * This method may only be called when a seminar object (with a valid UID) exists.
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
	 * Checks whether a user is logged in and hasn't registered for this seminar yet.
	 * Returns an empty string if everything is OK and an error message otherwise.
	 * Note: This method does not check if it is possible to register for a given seminar at all.
	 * 
	 * XXX Currently, this method is not used. Check whether we'll need it or else remove it
	 *  
	 * @param	object		a seminar for which we'll check if it is possible to register
	 *
	 * @return	string		empty string if everything is OK, else a localized error message.
	 * 
	 * @access	public
	 */	
	function canRegisterMessage(&$seminar) {
		/** This is empty as long as no error has occured. */
		$message = '';
	
		if (!$this->isLoggedIn()) {
			$message = $this->pi_getLL('message_notLoggedIn');
		} else {
			// The user is logged in. Let's see if he/she already has registered for this seminar.
			if ($this->isUserRegistered($seminar)) {
				$message = $this->pi_getLL('message_alreadyRegistered');
			}
		}

		return $message;
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
		return $seminar->isUserRegistered($this->feuser['uid']);
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 * 
	 * @param	object		a seminar for which we'll check if it is possible to register
	 *
	 * @return	string		empty string if everything is OK, else a localized error message.
	 * 
	 * @access public
	 */
	function isUserRegisteredMessage(&$seminar) {
		return $seminar->isUserRegisteredMessage($this->feuser['uid']);
	}
	
	/**
	 * Creates a registration to $this->registration, writes it to DB,
	 * and notifies the organizer and the user (both via email).
	 * 
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	array		associative array with the registration data the user has just entered
	 * 
	 * @access public
	 */
	function createRegistration(&$seminar, $registrationData) {
			$registrationClassname = t3lib_div::makeInstanceClassName('tx_seminars_registration');
			$this->registration =& new $registrationClassname($seminar, $this->feuser['uid'], $registrationData);

			$this->registration->commitToDb();
			$seminar->updateStatistics();

			return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']);
}
