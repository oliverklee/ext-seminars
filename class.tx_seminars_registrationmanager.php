<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_objectfromdb.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_registration.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_registrationbag.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_headerProxyFactory.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_db.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_templatehelper.php');

// This file doesn't include the locallang file in the BE because objectfromdb
// already does that.

/**
 * Class 'tx_seminars_registrationmanager' for the 'seminars' extension.
 *
 * This utility class checks and creates registrations for seminars.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registrationmanager extends tx_oelib_templatehelper {
	/** same as class name */
	public $prefixId = 'tx_seminars_registrationmanager';
	/**  path to this script relative to the extension dir */
	public $scriptRelPath = 'class.tx_seminars_registrationmanager.php';

	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/** the data of the current registration (tx_seminars_registration) */
	private $registration = null;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->registration) {
			$this->registration->__destruct();
			unset($this->registration);
		}

		parent::__destruct();
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
	 * @param object a seminar for which we'll check if it is possible to
	 *               register
	 *
	 * @return boolean true if everything is okay for the link, false otherwise
	 *
	 * @access public
	 */
	function canRegisterIfLoggedIn(tx_seminars_seminar $seminar) {
		$result = true;

		if ($this->isLoggedIn() && !$this->couldThisUserRegister($seminar)) {
			// The current user can not register for this event (no multiple
			// registrations are possible and the user is already registered).
			$result = false;
		} else {
			// it is not possible to register for this seminar at all
			// (it is canceled, full, etc.)
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
	 * @param object a seminar for which we'll check if it is possible to
	 *               register
	 *
	 * @return string error message or empty string
	 *
	 * @access public
	 */
	function canRegisterIfLoggedInMessage(tx_seminars_seminar $seminar) {
		$result = '';

		if ($this->isLoggedIn() && $this->isUserBlocked($seminar)) {
			// The current user is already blocked for this event.
			$result = $this->translate('message_userIsBlocked');
		} elseif ($this->isLoggedIn() && !$this->couldThisUserRegister($seminar)) {
			// The current user can not register for this event (no multiple
			// registrations are possible and the user is already registered).
			$result = $this->translate('message_alreadyRegistered');
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
	 * @param object a seminar for which we'll check if it is possible to
	 *               register
	 *
	 * @return boolean true if the user could register for the given event,
	 *                 false otherwise
	 *
	 * @access protected
	 */
	function couldThisUserRegister(tx_seminars_seminar $seminar) {
		// A user can register either if the event allows multiple registrations
		// or the user isn't registered yet and isn't blocked either.
		return $seminar->allowsMultipleRegistrations()
			|| (
				!$this->isUserRegistered($seminar)
				&& !$this->isUserBlocked($seminar)
			);
	}

	/**
	 * Creates an HTML link to either the registration page (if a user is
	 * logged in) or the login page (if no user is logged in).
	 *
	 * If $seminar has a separate details page, the link to that details page
	 * will be returned instead.
	 *
	 * Before you can call this function, you should make sure that the link
	 * makes sense (ie. the seminar still has vacancies, the user hasn't
	 * registered for this seminar etc.).
	 *
	 * @param tx_oelib_templatehelper an object for a live page
	 * @param tx_seminars_seminar a seminar for which we'll check if it is
	 *                            possible to register
	 *
	 * @return string HTML code with the link
	 */
	public function getLinkToRegistrationOrLoginPage(
		tx_oelib_templatehelper $plugin, tx_seminars_seminar $seminar
	) {
		$label = $this->getRegistrationLabel($plugin, $seminar);

		if ($seminar->hasSeparateDetailsPage()) {
			$result = $plugin->cObj->typolink(
				$label,
			 	$seminar->getDetailedViewLinkConfiguration($plugin)
			);
		} else {
			$result = $this->getLinkToStandardRegistrationOrLoginPage(
				$plugin, $seminar, $label
			);
		}

		return $result;
	}

	/**
	 * Creates the label for the registration link.
	 *
	 * @param tx_oelib_templatehelper an object for a live page
	 * @param tx_seminars_seminar a seminar to which the registration
	 *                            should relate
	 *
	 * @return string label for the registration link, will not be empty
	 */
	private function getRegistrationLabel(
		tx_oelib_templatehelper $plugin, tx_seminars_seminar $seminar
	) {
		if (!$seminar->hasVacancies()
			&& $seminar->hasVacanciesOnRegistrationQueue()
		) {
			$label = sprintf(
				$plugin->translate('label_onlineRegistrationOnQueue'),
				$seminar->getAttendancesOnRegistrationQueue()
			);
		} else {
			$label = $plugin->translate('label_onlineRegistration');
		}

		return $label;
	}

	/**
	 * Creates an HTML link to either the registration page (if a user is
	 * logged in) or the login page (if no user is logged in).
	 *
	 * This function only creates the link to the standard registration or login
	 * page; it should not be used if the seminar has a separate details page.
	 *
	 * @param tx_oelib_templatehelper an object for a live page
	 * @param tx_seminars_seminar a seminar for which we'll check if it is
	 *                            possible to register
	 * @param string label for the link, will not be empty
	 *
	 * @return string HTML code with the link
	 */
	private function getLinkToStandardRegistrationOrLoginPage(
		tx_oelib_templatehelper $plugin, tx_seminars_seminar $seminar,
		$label
	) {
		if ($this->isLoggedIn()) {
			// provides the registration link
			$result = $plugin->cObj->getTypoLink(
				$label,
				$plugin->getConfValueInteger('registerPID'),
				array(
					'tx_seminars_pi1[seminar]' => $seminar->getUid(),
					'tx_seminars_pi1[action]' => 'register'
				)
			);
		} else {
			// provides the login link
			$result = $plugin->getLoginLink(
				$label,
				$plugin->getConfValueInteger('registerPID'),
				$seminar->getUid()
			);
		}

		return $result;
	}

	/**
	 * Creates an HTML link to the unregistration page (if a user is logged in).
	 *
	 * @param object a tslib_pibase object for a live page
	 * @param object a registration from which we'll get the UID for our
	 *               GET parameters
	 *
	 * @return string HTML code with the link
	 *
	 * @access public
	 */
	function getLinkToUnregistrationPage(
		tslib_pibase $plugin,
		tx_seminars_registration $registration
	) {
		return $plugin->cObj->getTypoLink(
			$plugin->translate('label_onlineUnregistration'),
			$plugin->getConfValueInteger('registerPID'),
			array(
				'tx_seminars_pi1[registration]' => $registration->getUid(),
				'tx_seminars_pi1[action]' => 'unregister'
			)
		);
	}

	/**
	 * Checks whether a seminar UID is valid,
	 * ie. a non-deleted and non-hidden seminar with the given number exists.
	 *
	 * This function can be called even if no seminar object exists.
	 *
	 * @param string a given seminar UID (may not neccessarily be an integer)
	 *
	 * @return boolean true the UID is valid, false otherwise
	 *
	 * @access public
	 */
	function existsSeminar($seminarUid) {
		// We can't use t3lib_div::makeInstanceClassName in this case as we
		// cannot use a class function when using a variable as class name.
		return tx_seminars_objectfromdb::recordExists(
			$seminarUid,
			SEMINARS_TABLE_SEMINARS
		);
	}

	/**
	 * Checks whether a seminar UID is valid,
	 * ie. a non-deleted and non-hidden seminar with the given number exists.
	 *
	 * This function can be called even if no seminar object exists.
	 *
	 * @param string a given seminar UID (may not neccessarily be an integer)
	 *
	 * @return string empty string if the UID is valid, else a localized error
	 *                message
	 *
	 * @access public
	 */
	function existsSeminarMessage($seminarUid) {
		/** This is empty as long as no error has occured. */
		$message = '';

		if (!tx_seminars_objectfromdb::recordExists(
				$seminarUid,
				SEMINARS_TABLE_SEMINARS
			)
		) {
			$message = $this->translate('message_wrongSeminarNumber');
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
				'Status: 404 Not Found'
			);
		}

		return $message;
	}

	/**
	 * Checks whether a front-end user is already registered for this seminar.
	 *
	 * This method must not be called when no front-end user is logged in!
	 *
	 * @param object a seminar for which we'll check if it is possible to
	 *               register
	 *
	 * @return boolean true if user is already registered, false otherwise.
	 *
	 * @access public
	 */
	function isUserRegistered(tx_seminars_seminar $seminar) {
		return $seminar->isUserRegistered($this->getFeUserUid());
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * This method must not be called when no front-end user is logged in!
	 *
	 * @param object a seminar for which we'll check if it is possible to
	 *               register
	 *
	 * @return string empty string if everything is OK, else a localized error
	 *                message
	 *
	 * @access public
	 */
	function isUserRegisteredMessage(tx_seminars_seminar $seminar) {
		return $seminar->isUserRegisteredMessage($this->getFeUserUid());
	}

	/**
	 * Checks whether a front-end user is already blocked during the time for
	 * a given event by other booked events.
	 *
	 * For this, only events that forbid multiple registrations are checked.
	 *
	 * @param object a seminar for which we'll check whether the user already is
	 *               blocked by an other seminars
	 *
	 * @return boolean true if user is blocked by another registration, false
	 *                 otherwise
	 *
	 * @access protected
	 */
	function isUserBlocked(tx_seminars_seminar $seminar) {
		return $seminar->isUserBlocked($this->getFeUserUid());
	}

	/**
	 * Checks whether the data the user has just entered is okay for creating
	 * a registration, e.g. mandatory fields are filled, number fields only
	 * contain numbers, the number of seats to register is not too high etc.
	 *
	 * Please note that this function doesn't create a registration - it just
	 * checks.
	 *
	 * @param object the seminar object (that's the seminar we would like to
	 *               register for), must not be null
	 * @param array associative array with the registration data the user has
	 *              just entered
	 *
	 * @return boolean true if the data is okay, false otherwise
	 *
	 * @access public
	 */
	function canCreateRegistration(
		tx_seminars_seminar $seminar, array $registrationData
	) {
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
	 * Please note that this function doesn't create a registration - it just
	 * checks.
	 *
	 * @param object the seminar object (that's the seminar we would like to
	 *               register for), must not be null
	 * @param array associative array with the registration data the user has
	 *              just entered
	 *
	 * @return string an empty string if everything is okay, otherwise a
	 *                localized error message
	 *
	 * @access public
	 */
	function canCreateRegistrationMessage(
		tx_seminars_seminar $seminar, array $registrationData
	) {
		return ($this->canRegisterSeats($seminar, $registrationData['seats'])) ?
			'' :
			sprintf($this->translate('message_invalidNumberOfSeats'),
				$seminar->getVacancies());
	}

	/**
	 * Checks whether a registration with a given number of seats could be
	 * created, ie. an actual number is given and there are at least that many
	 * vacancies.
	 *
	 * @param object the seminar object (that's the seminar we would like to
	 *               register for)
	 * @param string the number of seats to check (should be an integer, but we
	 *               can't be sure of this)
	 *
	 * @return boolean true if there are at least that many vacancies, false
	 *                 otherwise
	 *
	 * @access private
	 */
	function canRegisterSeats(tx_seminars_seminar $seminar, $numberOfSeats) {
		$numberOfSeats = trim($numberOfSeats);

		// If no number of seats is given, ie. the user has not entered anything
		// or the field is not shown at all, assume 1.
		if (($numberOfSeats == '') || ($numberOfSeats == '0')) {
			$numberOfSeats = '1';
		}

		$numberOfSeatsInt = intval($numberOfSeats);

		// Check whether we have a valid number
		if ($numberOfSeats == strval($numberOfSeatsInt)) {
			$result = ($seminar->getVacanciesOnRegistrationQueue() >= $numberOfSeatsInt);
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Creates a registration to $this->registration, writes it to DB,
	 * and notifies the organizer and the user (both via e-mail).
	 *
	 * The additional notifications will only be sent if this is enabled in the
	 * TypoScript setup (which is the default).
	 *
	 * @param object the seminar object (that's the seminar we would like to
	 *               register for)
	 * @param array associative array with the registration data the user has
	 *              just entered
	 * @param object live plugin object
	 *
	 * @access public
	 */
	function createRegistration(
		tx_seminars_seminar $seminar, array $registrationData,
		tslib_pibase $plugin
	) {
		// Add the total price to the array that contains all neccessary
		// informations before creating the registration object.
		if (isset($registrationData['seats']) && ($registrationData['seats'] > 0)) {
			$seats = $registrationData['seats'];
		} else {
			$seats = 1;
		}

		$registrationClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_registration'
		);
		$this->registration = new $registrationClassname($plugin->cObj);
		$this->registration->setRegistrationData(
			$seminar,
			$this->getFeUserUid(),
			$registrationData
		);
		$this->registration->commitToDb();

		$seminar->calculateStatistics();

		if ($this->registration->isOnRegistrationQueue()) {
			$this->registration->notifyAttendee(
				$plugin,
				'confirmationOnRegistrationForQueue'
			);
			$this->registration->notifyOrganizers(
				'notificationOnRegistrationForQueue'
			);
		} else {
			$this->registration->notifyAttendee($plugin, 'confirmation');
			$this->registration->notifyOrganizers('notification');
		}

		if ($this->getConfValueBoolean('sendAdditionalNotificationEmails')) {
			$this->registration->sendAdditionalNotification();
		}
	}

	/**
	 * Removes the given registration (if it exists and if it belongs to the
	 * currently logged in FE user).
	 *
	 * @param integer the UID of the registration that should be removed
	 * @param object live plugin object
	 *
	 * @access public
	 */
	function removeRegistration($registrationUid, tslib_pibase $plugin) {
		if (tx_seminars_objectfromdb::recordExists(
				$registrationUid,
				SEMINARS_TABLE_ATTENDANCES
		)){
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				SEMINARS_TABLE_ATTENDANCES,
				SEMINARS_TABLE_ATTENDANCES . '.uid=' . $registrationUid .
					tx_oelib_db::enableFields(SEMINARS_TABLE_ATTENDANCES)
			);

			if ($dbResult) {
				$registrationClassname = t3lib_div::makeInstanceClassname(
					'tx_seminars_registration'
				);
				$this->registration = new $registrationClassname(
					$plugin->cObj,
					$dbResult
				);

				if ($this->registration->getUser() == $this->getFeUserUid()) {
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						SEMINARS_TABLE_ATTENDANCES,
						SEMINARS_TABLE_ATTENDANCES.'.uid='.$registrationUid,
						array(
							'hidden' => 1,
							'tstamp' => time()
						)
					);

					$this->registration->notifyAttendee(
						$plugin,
						'confirmationOnUnregistration'
					);
					$this->registration->notifyOrganizers(
						'notificationOnUnregistration'
					);

					$this->fillVacancies($plugin);
				}
			}
		}
	}

	/**
	 * Fills vacancies created through a unregistration with attendees from the
	 * registration queue.
	 *
	 * @param tslib_pibase live plugin object
	 */
	public function fillVacancies(tslib_pibase $plugin) {
		$seminar = $this->registration->getSeminarObject();
		$seminar->calculateStatistics();

		if ($seminar->hasVacancies()) {
			$vacancies = $seminar->getVacancies();

			$registrationBagBuilder = t3lib_div::makeInstance(
				'tx_seminars_registrationBagBuilder'
			);
			$registrationBagBuilder->limitToEvent($seminar->getUid());
			$registrationBagBuilder->limitToOnQueue();
			$registrationBagBuilder->limitToSeatsEqualOrLessThanVacancies(
				$seminar->getVacancies()
			);

			foreach ($registrationBagBuilder->build() as $registration) {
				if ($vacancies <= 0) {
					break;
				}

				if ($registration->getSeats() <= $vacancies) {
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						SEMINARS_TABLE_ATTENDANCES,
						'uid='.$registration->getUid(),
						array(
							'registration_queue' => 0
						)
					);
					$vacancies -= $registration->getSeats();

					$registration->notifyAttendee(
						$plugin,
						'confirmationOnQueueUpdate'
					);
					$registration->notifyOrganizers('notificationOnQueueUpdate');

					if (
						$this->getConfValueBoolean(
							'sendAdditionalNotificationEmails'
						)
					) {
						$registration->sendAdditionalNotification();
					}
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']);
}
?>