<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * This utility class checks and creates registrations for seminars.
 *
 * This file does not include the locallang file in the BE because objectfromdb already does that.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registrationmanager extends tx_oelib_templatehelper {
	/**
	 * @var string same as class name
	 */
	public $prefixId = 'tx_seminars_registrationmanager';
	/**
	 * @var string path to this script relative to the extension directory
	 */
	public $scriptRelPath = 'class.tx_seminars_registrationmanager.php';

	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * @var tx_seminars_registrationmanager the Singleton instance
	 */
	private static $instance = NULL;

	/**
	 * @var tx_seminars_registration the current registration
	 */
	private $registration = NULL;

	/**
	 * @var bool whether we have already initialized the templates
	 *              (which is done lazily)
	 */
	private $isTemplateInitialized = FALSE;

	/**
	 * hook objects for this class
	 *
	 * @var array
	 */
	private $hooks = array();

	/**
	 * whether the hooks in $this->hooks have been retrieved
	 *
	 * @var bool
	 */
	private $hooksHaveBeenRetrieved = FALSE;

	/**
	 * @var int use text format for e-mails to attendees
	 */
	const SEND_TEXT_MAIL = 0;

	/**
	 * @var int use HTML format for e-mails to attendees
	 */
	const SEND_HTML_MAIL = 1;

	/**
	 * @var int use user-specific format for e-mails to attendees
	 */
	const SEND_USER_MAIL = 2;

	/**
	 * a link builder instance
	 *
	 * @var tx_seminars_Service_SingleViewLinkBuilder
	 */
	private $linkBuilder = NULL;

	/**
	 * The constructor.
	 *
	 * It still is public due to the templatehelper base class. Nevertheless,
	 * getInstance should be used so the Singleton property is retained.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->registration, $this->linkBuilder);
		$this->hooks = array();

		parent::__destruct();
	}

	/**
	 * Returns the instance of this class.
	 *
	 * @return tx_seminars_registrationmanager the current Singleton instance
	 */
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = t3lib_div::makeInstance('tx_seminars_registrationmanager');
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new instance.
	 *
	 * @return void
	 */
	public static function purgeInstance() {
		self::$instance = NULL;
	}

	/**
	 * Checks whether is possible to register for a given event at all:
	 * if a possibly logged-in user has not registered yet for this event,
	 * if the event isn't canceled, full etc.
	 *
	 * If no user is logged in, it is just checked whether somebody could
	 * register for this event.
	 *
	 * This function works even if no user is logged in.
	 *
	 * @param tx_seminars_seminar $event
	 *        am event for which we'll check if it is possible to register
	 *
	 * @return bool TRUE if it is okay to register, FALSE otherwise
	 */
	public function canRegisterIfLoggedIn(tx_seminars_seminar $event) {
		if (!$event->canSomebodyRegister()) {
			return FALSE;
		}
		if (!tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()) {
			return TRUE;
		}

		$canRegister = $this->couldThisUserRegister($event);

		/** @var $user tx_seminars_Model_FrontEndUser */
		$user = tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');
		foreach ($this->getHooks() as $hook) {
			if (method_exists($hook, 'canRegisterForSeminar')) {
				$canRegister = $canRegister && $hook->canRegisterForSeminar($event, $user);
			}
		}

		return $canRegister;
	}

	/**
	 * Checks whether is possible to register for a given seminar at all:
	 * if a possibly logged-in user has not registered yet for this seminar, if the seminar isn't canceled, full etc.
	 *
	 * If no user is logged in, it is just checked whether somebody could register for this seminar.
	 *
	 * Returns a message if there is anything to complain about and an empty string otherwise.
	 *
	 * This function even works if no user is logged in.
	 *
	 * Note: This function does not check whether a logged-in front-end user fulfills all requirements for an event.
	 *
	 * @param tx_seminars_seminar $event a seminar for which we'll check if it is possible to register
	 *
	 * @return string error message or empty string
	 */
	public function canRegisterIfLoggedInMessage(tx_seminars_seminar $event) {
		$message = '';

		$isLoggedIn = tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn();

		if ($isLoggedIn	&& $this->isUserBlocked($event)) {
			$message = $this->translate('message_userIsBlocked');
		} elseif ($isLoggedIn && !$this->couldThisUserRegister($event)) {
			$message = $this->translate('message_alreadyRegistered');
		} elseif (!$event->canSomebodyRegister()) {
			$message = $event->canSomebodyRegisterMessage();
		}

		if ($isLoggedIn && ($message === '')) {
			/** @var $user tx_seminars_Model_FrontEndUser */
			$user = tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');
			foreach ($this->getHooks() as $hook) {
				if (method_exists($hook, 'canRegisterForSeminarMessage')) {
					$message = $hook->canRegisterForSeminarMessage($event, $user);
					if ($message !== '') {
						break;
					}
				}
			}
		}

		return $message;
	}

	/**
	 * Checks whether the current FE user (if any is logged in) could register
	 * for the current event, not checking the event's vacancies yet.
	 * So this function only checks whether the user is logged in and isn't blocked for the event's duration yet.
	 *
	 * Note: This function does not check whether a logged-in front-end user fulfills all requirements for an event.
	 *
	 * @param tx_seminars_seminar $seminar a seminar for which we'll check if it is possible to register
	 *
	 * @return bool TRUE if the user could register for the given event, FALSE otherwise
	 */
	private function couldThisUserRegister(tx_seminars_seminar $seminar) {
		// A user can register either if the event allows multiple registrations
		// or the user isn't registered yet and isn't blocked either.
		return $seminar->allowsMultipleRegistrations() || (!$this->isUserRegistered($seminar) && !$this->isUserBlocked($seminar));
	}

	/**
	 * Creates an HTML link to the registration or login page.
	 *
	 * @param tx_oelib_templatehelper $plugin the pi1 object with configuration data
	 * @param tx_seminars_seminar $event the seminar to create the registration link for
	 *
	 * @return string the HTML tag, will be empty if the event needs no registration, nobody can register to this event or the
	 *                currently logged in user is already registered to this event and the event does not allow multiple
	 *                registrations by one user
	 */
	public function getRegistrationLink(tx_oelib_templatehelper $plugin, tx_seminars_seminar $event) {
		if (!$event->needsRegistration() || !$this->canRegisterIfLoggedIn($event)) {
			return '';
		}

		return $this->getLinkToRegistrationOrLoginPage($plugin, $event);
	}

	/**
	 * Creates an HTML link to either the registration page (if a user is logged in) or the login page (if no user is logged in).
	 *
	 * Before you can call this function, you should make sure that the link makes sense (ie. the seminar still has vacancies, the
	 * user has not registered for this seminar etc.).
	 *
	 * @param tx_oelib_templatehelper $plugin an object for a live page
	 * @param tx_seminars_seminar $seminar a seminar for which we'll check if it is possible to register
	 *
	 * @return string HTML code with the link
	 */
	public function getLinkToRegistrationOrLoginPage(tx_oelib_templatehelper $plugin, tx_seminars_seminar $seminar) {
		return $this->getLinkToStandardRegistrationOrLoginPage($plugin, $seminar, $this->getRegistrationLabel($plugin, $seminar));
	}

	/**
	 * Creates the label for the registration link.
	 *
	 * @param tx_oelib_templatehelper $plugin an object for a live page
	 * @param tx_seminars_seminar $seminar a seminar to which the registration should relate
	 *
	 * @return string label for the registration link, will not be empty
	 */
	private function getRegistrationLabel(tx_oelib_templatehelper $plugin, tx_seminars_seminar $seminar) {
		if ($seminar->hasVacancies()) {
			if ($seminar->hasDate()) {
				$label = $plugin->translate('label_onlineRegistration');
			} else {
				$label = $plugin->translate('label_onlinePrebooking');
			}
		} else {
			if ($seminar->hasRegistrationQueue()) {
				$label = sprintf(
					$plugin->translate('label_onlineRegistrationOnQueue'), $seminar->getAttendancesOnRegistrationQueue()
				);
			} else {
				$label = $plugin->translate('label_onlineRegistration');
			}
		}

		return $label;
	}

	/**
	 * Creates an HTML link to either the registration page (if a user is logged in) or the login page (if no user is logged in).
	 *
	 * This function only creates the link to the standard registration or login
	 * page; it should not be used if the seminar has a separate details page.
	 *
	 * @param tx_oelib_templatehelper $plugin an object for a live page
	 * @param tx_seminars_seminar $seminar a seminar for which we'll check if it is possible to register
	 * @param string $label label for the link, will not be empty
	 *
	 * @return string HTML code with the link
	 */
	private function getLinkToStandardRegistrationOrLoginPage(
		tx_oelib_templatehelper $plugin, tx_seminars_seminar $seminar, $label
	) {
		if (tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()) {
			// provides the registration link
			$result = $plugin->cObj->getTypoLink(
				$label,
				$plugin->getConfValueInteger('registerPID'),
				array('tx_seminars_pi1[seminar]' => $seminar->getUid(), 'tx_seminars_pi1[action]' => 'register')
			);
		} else {
			// provides the login link
			$result = $plugin->getLoginLink($label, $plugin->getConfValueInteger('registerPID'), $seminar->getUid());
		}

		return $result;
	}

	/**
	 * Creates an HTML link to the unregistration page (if a user is logged in).
	 *
	 * @param tslib_pibase $plugin an object for a live page
	 * @param tx_seminars_registration $registration a registration from which we'll get the UID for our GET parameters
	 *
	 * @return string HTML code with the link
	 */
	public function getLinkToUnregistrationPage(tslib_pibase $plugin, tx_seminars_registration $registration) {
		return $plugin->cObj->getTypoLink(
			$plugin->translate('label_onlineUnregistration'),
			$plugin->getConfValueInteger('registerPID'),
			array('tx_seminars_pi1[registration]' => $registration->getUid(), 'tx_seminars_pi1[action]' => 'unregister')
		);
	}

	/**
	 * Checks whether a seminar UID is valid, ie., a non-deleted and non-hidden seminar with the given number exists.
	 *
	 * This function can be called even if no seminar object exists.
	 *
	 * @param string $seminarUid a given seminar UID (needs not necessarily be an integer)
	 *
	 * @return bool TRUE the UID is valid, FALSE otherwise
	 */
	public function existsSeminar($seminarUid) {
		return tx_seminars_OldModel_Abstract::recordExists($seminarUid, 'tx_seminars_seminars');
	}

	/**
	 * Checks whether a seminar UID is valid, ie., a non-deleted and non-hidden seminar with the given number exists.
	 *
	 * This function can be called even if no seminar object exists.
	 *
	 * @param string $seminarUid a given seminar UID (needs not necessarily be an integer)
	 *
	 * @return string empty string if the UID is valid, else a localized error message
	 */
	public function existsSeminarMessage($seminarUid) {
		/** This is empty as long as no error has occured. */
		$message = '';

		if (!tx_seminars_OldModel_Abstract::recordExists($seminarUid, 'tx_seminars_seminars')) {
			$message = $this->translate('message_wrongSeminarNumber');
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
		}

		return $message;
	}

	/**
	 * Checks whether a front-end user is already registered for this seminar.
	 *
	 * This method must not be called when no front-end user is logged in!
	 *
	 * @param tx_seminars_seminar $seminar a seminar for which we'll check if it is possible to register
	 *
	 * @return bool TRUE if user is already registered, FALSE otherwise.
	 */
	public function isUserRegistered(tx_seminars_seminar $seminar) {
		return $seminar->isUserRegistered($this->getFeUserUid());
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * This method must not be called when no front-end user is logged in!
	 *
	 * @param tx_seminars_seminar $seminar a seminar for which we'll check if it is possible to register
	 *
	 * @return string empty string if everything is OK, else a localized error message
	 */
	public function isUserRegisteredMessage(tx_seminars_seminar $seminar) {
		return $seminar->isUserRegisteredMessage($this->getFeUserUid());
	}

	/**
	 * Checks whether a front-end user is already blocked during the time for a given event by other booked events.
	 *
	 * For this, only events that forbid multiple registrations are checked.
	 *
	 * @param tx_seminars_seminar $seminar a seminar for which we'll check whether the user already is blocked by an other seminars
	 *
	 * @return bool TRUE if user is blocked by another registration, FALSE otherwise
	 */
	private function isUserBlocked(tx_seminars_seminar $seminar) {
		return $seminar->isUserBlocked($this->getFeUserUid());
	}

	/**
	 * Checks whether the data the user has just entered is okay for creating
	 * a registration, e.g. mandatory fields are filled, number fields only
	 * contain numbers, the number of seats to register is not too high etc.
	 *
	 * Please note that this function does not create a registration - it just checks.
	 *
	 * @param tx_seminars_seminar $seminar the seminar object (that's the seminar we would like to register for)
	 * @param array $registrationData associative array with the registration data the user has just entered
	 *
	 * @return bool TRUE if the data is okay, FALSE otherwise
	 */
	public function canCreateRegistration(tx_seminars_seminar $seminar, array $registrationData) {
		return $this->canRegisterSeats($seminar, $registrationData['seats']);
	}

	/**
	 * Checks whether a registration with a given number of seats could be
	 * created, ie. an actual number is given and there are at least that many vacancies.
	 *
	 * @param tx_seminars_seminar $seminar the seminar object (that's the seminar we would like to register for)
	 * @param int|string $numberOfSeats the number of seats to check (should be an integer, but we can't be sure of this)
	 *
	 * @return bool TRUE if there are at least that many vacancies, FALSE otherwise
	 */
	public function canRegisterSeats(tx_seminars_seminar $seminar, $numberOfSeats) {
		$numberOfSeats = trim($numberOfSeats);

		// If no number of seats is given, ie. the user has not entered anything
		// or the field is not shown at all, assume 1.
		if (($numberOfSeats == '') || ($numberOfSeats == '0')) {
			$numberOfSeats = '1';
		}

		$numberOfSeatsInt = (int)$numberOfSeats;

		// Check whether we have a valid number
		if ($numberOfSeats == strval($numberOfSeatsInt)) {
			if ($seminar->hasUnlimitedVacancies()) {
				$result = TRUE;
			} else {
				$result = ($seminar->hasRegistrationQueue() || ($seminar->getVacancies() >= $numberOfSeatsInt));
			}
		} else {
			$result = FALSE;
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
	 * @param tx_seminars_seminar $seminar the seminar we would like to register for
	 * @param array $formData the raw registration data from the registration form
	 * @param tslib_pibase $plugin live plugin object
	 *
	 * @return tx_seminars_Model_Registration the created, saved registration
	 */
	public function createRegistration(tx_seminars_seminar $seminar, array $formData, tslib_pibase $plugin) {
		$this->registration = t3lib_div::makeInstance('tx_seminars_registration', $plugin->cObj);
		$this->registration->setRegistrationData($seminar, $this->getFeUserUid(), $formData);
		$this->registration->commitToDb();
		$seminar->calculateStatistics();

		$user = tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');
		foreach ($this->getHooks() as $hook) {
			if (method_exists($hook, 'seminarRegistrationCreated')) {
				$hook->seminarRegistrationCreated($this->registration, $user);
			}
		}

		return tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')->find($this->registration->getUid());
	}

	/**
	 * Sends the e-mails for a new registration.
	 *
	 * @param tx_seminars_registration $registration
	 * @param tslib_pibase $plugin
	 *
	 * @return void
	 */
	public function sendEmailsForNewRegistration(tx_seminars_registration $registration, tslib_pibase $plugin) {
		if ($this->registration->isOnRegistrationQueue()) {
			$this->notifyAttendee($this->registration, $plugin, 'confirmationOnRegistrationForQueue');
			$this->notifyOrganizers($this->registration, 'notificationOnRegistrationForQueue');
		} else {
			$this->notifyAttendee($this->registration, $plugin, 'confirmation');
			$this->notifyOrganizers($this->registration, 'notification');
		}

		if ($this->getConfValueBoolean('sendAdditionalNotificationEmails')) {
			$this->sendAdditionalNotification($this->registration);
		}
	}

	/**
	 * Fills $registration with $formData (as submitted via the registration form).
	 *
	 * This function sets all necessary registration data except for three
	 * things:
	 * - event
	 * - user
	 * - whether the registration is on the queue
	 *
	 * Note: This functions does not check whether registration is possible at all.
	 *
	 * @param tx_seminars_Model_Registration $registration the registration to fill, must already have an event assigned
	 * @param array $formData the raw data submitted via the form, may be empty
	 *
	 * @return void
	 */
	protected function setRegistrationData(tx_seminars_Model_Registration $registration, array $formData) {
		$event = $registration->getEvent();

		$seats = isset($formData['seats']) ? (int)$formData['seats'] : 1;
		if ($seats < 1) {
			$seats = 1;
		}
		$registration->setSeats($seats);

		$registeredThemselves = isset($formData['registered_themselves'])
			? (bool)$formData['registered_themselves'] : FALSE;
		$registration->setRegisteredThemselves($registeredThemselves);

		$availablePrices = $event->getAvailablePrices();
		if (isset($formData['price']) && isset($availablePrices[$formData['price']])) {
			$priceCode = $formData['price'];
		} else {
			reset($availablePrices);
			$priceCode = key($availablePrices);
		}
		$registration->setPrice($priceCode);
		$totalPrice = $availablePrices[$priceCode] * $seats;
		$registration->setTotalPrice($totalPrice);

		$attendeesNames = isset($formData['attendees_names']) ? strip_tags($formData['attendees_names']) : '';
		$registration->setAttendeesNames($attendeesNames);

		$kids = isset($formData['kids']) ? max(0, (int)$formData['kids']) : 0;
		$registration->setKids($kids);

		$paymentMethod = NULL;
		if ($totalPrice > 0) {
			$availablePaymentMethods = $event->getPaymentMethods();
			if (!$availablePaymentMethods->isEmpty()) {
				if ($availablePaymentMethods->count() == 1) {
					$paymentMethod = $availablePaymentMethods->first();
				} else {
					$paymentMethodUid = isset($formData['method_of_payment'])
						? max(0, (int)$formData['method_of_payment']) : 0;
					if (($paymentMethodUid > 0) && $availablePaymentMethods->hasUid($paymentMethodUid)) {
						$paymentMethod = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_PaymentMethod')
							->find($paymentMethodUid);
					}
				}
			}
		}
		$registration->setPaymentMethod($paymentMethod);

		$accountNumber = isset($formData['account_number'])
			? strip_tags($this->unifyWhitespace($formData['account_number'])) : '';
		$registration->setAccountNumber($accountNumber);
		$bankCode = isset($formData['bank_code']) ? strip_tags($this->unifyWhitespace($formData['bank_code'])) : '';
		$registration->setBankCode($bankCode);
		$bankName = isset($formData['bank_name']) ? strip_tags($this->unifyWhitespace($formData['bank_name'])) : '';
		$registration->setBankName($bankName);
		$accountOwner = isset($formData['account_owner']) ? strip_tags($this->unifyWhitespace($formData['account_owner'])) : '';
		$registration->setAccountOwner($accountOwner);

		$company = isset($formData['company']) ? strip_tags($formData['company']) : '';
		$registration->setCompany($company);

		$validGenderMale = (string) tx_oelib_Model_FrontEndUser::GENDER_MALE;
		$validGenderFemale = (string) tx_oelib_Model_FrontEndUser::GENDER_FEMALE;
		if (isset($formData['gender'])
			&& (($formData['gender'] === $validGenderMale) || ($formData['gender'] === $validGenderFemale)
			)
		) {
			$gender = (int)$formData['gender'];
		} else {
			$gender = tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN;
		}
		$registration->setGender($gender);

		$name = isset($formData['name']) ? strip_tags($this->unifyWhitespace($formData['name'])) : '';
		$registration->setName($name);
		$address = isset($formData['address']) ? strip_tags($formData['address']) : '';
		$registration->setAddress($address);
		$zip = isset($formData['zip']) ? strip_tags($this->unifyWhitespace($formData['zip'])) : '';
		$registration->setZip($zip);
		$city = isset($formData['city']) ? strip_tags($this->unifyWhitespace($formData['city'])) : '';
		$registration->setCity($city);
		$country = isset($formData['country']) ? strip_tags($this->unifyWhitespace($formData['country'])) : '';
		$registration->setCountry($country);
	}

	/**
	 * Replaces all non-space whitespace in $rawString with single regular spaces.
	 *
	 * @param string $rawString the string to unify, may be empty
	 *
	 * @return string $rawString with all whitespace changed to regular spaces
	 */
	private function unifyWhitespace($rawString) {
		return preg_replace('/[\r\n\t ]+/', ' ', $rawString);
	}

	/**
	 * Removes the given registration (if it exists and if it belongs to the
	 * currently logged-in FE user).
	 *
	 * @param int $uid the UID of the registration that should be removed
	 * @param tslib_pibase $plugin a live plugin object
	 *
	 * @return void
	 */
	public function removeRegistration($uid, tslib_pibase $plugin) {
		if (!tx_seminars_OldModel_Abstract::recordExists($uid, 'tx_seminars_attendances')) {
			return;
		}
		$this->registration = t3lib_div::makeInstance(
			'tx_seminars_registration',
			$plugin->cObj,
			tx_oelib_db::select(
				'*', 'tx_seminars_attendances', 'uid = ' . $uid . tx_oelib_db::enableFields('tx_seminars_attendances')
			)
		);
		if ($this->registration->getUser() !== $this->getFeUserUid()) {
			return;
		}

		/** @var $user tx_seminars_Model_FrontEndUser */
		$user = tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');
		foreach ($this->getHooks() as $hook) {
			if (method_exists($hook, 'seminarRegistrationRemoved')) {
				$hook->seminarRegistrationRemoved($this->registration, $user);
			}
		}

		tx_oelib_db::update(
			'tx_seminars_attendances',
			'uid = ' . $uid,
			array('hidden' => 1, 'tstamp' => $GLOBALS['SIM_EXEC_TIME'])
		);

		$this->notifyAttendee($this->registration, $plugin, 'confirmationOnUnregistration');
		$this->notifyOrganizers($this->registration, 'notificationOnUnregistration');

		$this->fillVacancies($plugin);
	}

	/**
	 * Fills vacancies created through a unregistration with attendees from the registration queue.
	 *
	 * @param tslib_pibase $plugin live plugin object
	 *
	 * @return void
	 */
	private function fillVacancies(tslib_pibase $plugin) {
		$seminar = $this->registration->getSeminarObject();
		$seminar->calculateStatistics();
		if (!$seminar->hasVacancies()) {
			return;
		}

		$vacancies = $seminar->getVacancies();

		/** @var $registrationBagBuilder tx_seminars_BagBuilder_Registration */
		$registrationBagBuilder = t3lib_div::makeInstance('tx_seminars_BagBuilder_Registration');
		$registrationBagBuilder->limitToEvent($seminar->getUid());
		$registrationBagBuilder->limitToOnQueue();
		$registrationBagBuilder->limitToSeatsAtMost($vacancies);

		$bag = $registrationBagBuilder->build();
		foreach ($bag as $registration) {
			if ($vacancies <= 0) {
				break;
			}

			if ($registration->getSeats() <= $vacancies) {
				tx_oelib_db::update(
					'tx_seminars_attendances', 'uid = ' . $registration->getUid(), array('registration_queue' => 0)
				);
				$vacancies -= $registration->getSeats();

				$user = $registration->getFrontEndUser();
				foreach ($this->getHooks() as $hook) {
					if (method_exists($hook, 'seminarRegistrationMovedFromQueue')) {
						$hook->seminarRegistrationMovedFromQueue($registration, $user);
					}
				}

				$this->notifyAttendee($registration, $plugin, 'confirmationOnQueueUpdate');
				$this->notifyOrganizers($registration, 'notificationOnQueueUpdate');

				if ($this->getConfValueBoolean('sendAdditionalNotificationEmails')) {
					$this->sendAdditionalNotification($registration);
				}
			}
		}
	}

	/**
	 * Checks if the logged-in user fulfills all requirements for registration for the event $event.
	 *
	 * A front-end user needs to be logged in when this function is called.
	 *
	 * @param tx_seminars_seminar $event the event to check
	 *
	 * @return bool TRUE if the user fulfills all requirements, FALSE otherwise
	 */
	public function userFulfillsRequirements(tx_seminars_seminar $event) {
		if (!$event->hasRequirements()) {
			return TRUE;
		}
		$missingTopics = $this->getMissingRequiredTopics($event);
		$result = $missingTopics->isEmpty();

		return $result;
	}

	/**
	 * Returns the event topics the user still needs to register for in order to be able to register for $event.
	 *
	 * @param tx_seminars_seminar $event the event to check
	 *
	 * @return tx_seminars_Bag_Event the event topics which still need the user's registration, may be empty
	 */
	public function getMissingRequiredTopics(tx_seminars_seminar $event) {
		/** @var $builder tx_seminars_BagBuilder_Event */
		$builder = t3lib_div::makeInstance('tx_seminars_BagBuilder_Event');
		$builder->limitToRequiredEventTopics($event->getTopicUid());
		$builder->limitToTopicsWithoutRegistrationByUser($this->getFeUserUid());

		return $builder->build();
	}

	/**
	 * Sends an e-mail to the attendee with a message concerning his/her registration or unregistration.
	 *
	 * @param tx_seminars_registration $oldRegistration the registration for which the notification should be sent
	 * @param tslib_pibase $plugin a live plugin
	 * @param string $helloSubjectPrefix
	 *        prefix for the locallang key of the localized hello and subject
	 *        string; allowed values are:
	 *        - confirmation
	 *        - confirmationOnUnregistration
	 *        - confirmationOnRegistrationForQueue
	 *        - confirmationOnQueueUpdate
	 *        In the following the parameter is prefixed with "email_" and
	 *        postfixed with "Hello" or "Subject".
	 *
	 * @return void
	 */
	public function notifyAttendee(
		tx_seminars_registration $oldRegistration, tslib_pibase $plugin, $helloSubjectPrefix = 'confirmation'
	) {
		if (!$this->getConfValueBoolean('send' . ucfirst($helloSubjectPrefix))) {
			return;
		}

		/** @var $event tx_seminars_seminar */
		$event = $oldRegistration->getSeminarObject();
		if (!$event->hasOrganizers()) {
			return;
		}

		if (!$oldRegistration->hasExistingFrontEndUser()) {
			return;
		}

		if (!$oldRegistration->getFrontEndUser()->hasEMailAddress()) {
			return;
		}

		/** @var $eMailNotification tx_oelib_Mail */
		$eMailNotification = t3lib_div::makeInstance('tx_oelib_Mail');
		$eMailNotification->addRecipient($oldRegistration->getFrontEndUser());
		$eMailNotification->setSender($event->getFirstOrganizer());
		$eMailNotification->setSubject(
			$this->translate('email_' . $helloSubjectPrefix . 'Subject') . ': ' . $event->getTitleAndDate('-')
		);

		$this->initializeTemplate();

		$mailFormat = tx_oelib_configurationProxy::getInstance('seminars')->getAsInteger('eMailFormatForAttendees');
		if (($mailFormat == self::SEND_HTML_MAIL)
			|| (($mailFormat == self::SEND_USER_MAIL) && $oldRegistration->getFrontEndUser()->wantsHtmlEMail())
		) {
			$eMailNotification->setCssFile($this->getConfValueString('cssFileForAttendeeMail'));
			$eMailNotification->setHTMLMessage($this->buildEmailContent($oldRegistration, $plugin, $helloSubjectPrefix, TRUE));
		}

		$eMailNotification->setMessage($this->buildEmailContent($oldRegistration, $plugin, $helloSubjectPrefix));

		/** @var $registration tx_seminars_Model_Registration */
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')->find($oldRegistration->getUid());
		foreach ($this->getHooks() as $hook) {
			if (method_exists($hook, 'modifyThankYouEmail')) {
				$hook->modifyThankYouEmail($eMailNotification, $registration);
			}
		}

		/** @var Tx_Oelib_MailerFactory $mailerFactory */
		$mailerFactory = t3lib_div::makeInstance('Tx_Oelib_MailerFactory');
		$mailerFactory->getMailer()->send($eMailNotification);
	}

	/**
	 * Sends an e-mail to all organizers with a message about a registration or unregistration.
	 *
	 * @param tx_seminars_registration $registration
	 *        the registration for which the notification should be send
	 * @param string $helloSubjectPrefix
	 *        prefix for the locallang key of the localized hello and subject string, Allowed values are:
	 *        - notification
	 *        - notificationOnUnregistration
	 *        - notificationOnRegistrationForQueue
	 *        - notificationOnQueueUpdate
	 *        In the following, the parameter is prefixed with "email_" and postfixed with "Hello" or "Subject".
	 *
	 * @return void
	 */
	public function notifyOrganizers(tx_seminars_registration $registration, $helloSubjectPrefix = 'notification') {
		if (!$this->getConfValueBoolean('send' . ucfirst($helloSubjectPrefix))) {
			return;
		}
		if (!$registration->hasExistingFrontEndUser()) {
			return;
		}
		$event = $registration->getSeminarObject();
		if (!$event->hasOrganizers()) {
			return;
		}

		$organizers = $event->getOrganizerBag();
		/** @var $eMailNotification tx_oelib_Mail */
		$eMailNotification = t3lib_div::makeInstance('tx_oelib_Mail');
		$eMailNotification->setSender($event->getFirstOrganizer());

		/** @var $organizer tx_seminars_OldModel_Organizer */
		foreach ($organizers as $organizer) {
			$eMailNotification->addRecipient($organizer);
		}

		$eMailNotification->setSubject(
			$this->translate('email_' . $helloSubjectPrefix . 'Subject') . ': ' . $registration->getTitle()
		);

		$this->initializeTemplate();
		$this->hideSubparts($this->getConfValueString('hideFieldsInNotificationMail'), 'field_wrapper');

		$this->setMarker('hello', $this->translate('email_' . $helloSubjectPrefix . 'Hello'));
		$this->setMarker('summary', $registration->getTitle());

		if ($this->hasConfValueString('showSeminarFieldsInNotificationMail')) {
			$this->setMarker(
				'seminardata', $event->dumpSeminarValues($this->getConfValueString('showSeminarFieldsInNotificationMail'))
			);
		} else {
			$this->hideSubparts('seminardata', 'field_wrapper');
		}

		if ($this->hasConfValueString('showFeUserFieldsInNotificationMail')) {
			$this->setMarker(
				'feuserdata', $registration->dumpUserValues($this->getConfValueString('showFeUserFieldsInNotificationMail'))
			);
		} else {
			$this->hideSubparts('feuserdata', 'field_wrapper');
		}

		if ($this->hasConfValueString('showAttendanceFieldsInNotificationMail')) {
			$this->setMarker(
				'attendancedata',
				$registration->dumpAttendanceValues($this->getConfValueString('showAttendanceFieldsInNotificationMail'))
			);
		} else {
			$this->hideSubparts('attendancedata', 'field_wrapper');
		}

		$this->callModifyOrganizerNotificationEmailHooks($registration, $this->getTemplate());

		$eMailNotification->setMessage($this->getSubpart('MAIL_NOTIFICATION'));
		$this->modifyNotificationEmail($eMailNotification, $registration);

		/** @var Tx_Oelib_MailerFactory $mailerFactory */
		$mailerFactory = t3lib_div::makeInstance('Tx_Oelib_MailerFactory');
		$mailerFactory->getMailer()->send($eMailNotification);
	}

	/**
	 * Calls the modifyOrganizerNotificationEmail hooks.
	 *
	 * @param tx_seminars_registration $registration
	 * @param Tx_Oelib_Template $emailTemplate
	 *
	 * @return void
	 */
	protected function callModifyOrganizerNotificationEmailHooks(
		tx_seminars_registration $registration, Tx_Oelib_Template $emailTemplate
	) {
		foreach ($this->getHooks() as $hook) {
			if ($hook instanceof tx_seminars_Interface_Hook_Registration) {
				/** @var $hook tx_seminars_Interface_Hook_Registration */
				$hook->modifyOrganizerNotificationEmail($registration, $emailTemplate);
			}
		}
	}

	/**
	 * Modifies the notification e-mail.
	 *
	 * This method is intended to be overridden in XClasses if needed.
	 *
	 * @param tx_oelib_Mail $emailNotification
	 * @param tx_seminars_registration $registration
	 *
	 * @return void
	 */
	protected function modifyNotificationEmail(tx_oelib_Mail $emailNotification, tx_seminars_registration $registration) {
	}

	/**
	 * Calls the modifyAttendeeEmailText hooks.
	 *
	 * @param tx_seminars_registration $registration
	 * @param Tx_Oelib_Template $emailTemplate
	 *
	 * @return void
	 */
	protected function callModifyAttendeeEmailTextHooks(tx_seminars_registration $registration, Tx_Oelib_Template $emailTemplate) {
		foreach ($this->getHooks() as $hook) {
			if ($hook instanceof tx_seminars_Interface_Hook_Registration) {
				/** @var $hook tx_seminars_Interface_Hook_Registration */
				$hook->modifyAttendeeEmailText($registration, $emailTemplate);
			}
		}
	}

	/**
	 * Checks if additional notifications to the organizers are necessary.
	 * In that case, the notification e-mails will be sent to all organizers.
	 *
	 * Additional notifications mails will be sent out upon the following events:
	 * - an event now has enough registrations
	 * - an event is fully booked
	 * If both things happen at the same time (minimum and maximum count of
	 * attendees are the same), only the "event is full" message will be sent.
	 *
	 * @param tx_seminars_registration $registration the registration for which the notification should be send
	 *
	 * @return void
	 */
	public function sendAdditionalNotification(
		tx_seminars_registration $registration
	) {
		if ($registration->isOnRegistrationQueue()) {
			return;
		}

		$emailReason = $this->getReasonForNotification($registration);
		if ($emailReason == '') {
			return;
		}

		$event = $registration->getSeminarObject();
		/** @var $eMail tx_oelib_Mail */
		$eMail = t3lib_div::makeInstance('tx_oelib_Mail');

		$eMail->setSender($event->getFirstOrganizer());
		$eMail->setMessage($this->getMessageForNotification($registration, $emailReason));
		$eMail->setSubject(sprintf(
			$this->translate('email_additionalNotification' . $emailReason . 'Subject'),
			$event->getUid(),
			$event->getTitleAndDate('-')
		));

		foreach ($event->getOrganizerBag() as $organizer) {
			$eMail->addRecipient($organizer);
		}

		/** @var Tx_Oelib_MailerFactory $mailerFactory */
		$mailerFactory = t3lib_div::makeInstance('Tx_Oelib_MailerFactory');
		$mailerFactory->getMailer()->send($eMail);
	}

	/**
	 * Returns the topic for the additional notification e-mail.
	 *
	 * @param tx_seminars_registration $registration the registration for which the notification should be send
	 *
	 * @return string "EnoughRegistrations" if the event has enough attendances,
	 *                "IsFull" if the event is fully booked, otherwise an empty string
	 */
	private function getReasonForNotification(tx_seminars_registration $registration) {
		$result = '';

		$event = $registration->getSeminarObject();
		if ($event->isFull()) {
			$result = 'IsFull';
		// Using "==" instead of ">=" ensures that only one set of e-mails is
		// sent to the organizers.
		// This also ensures that no e-mail is send when minAttendances is 0
		// since this function is only called when at least one registration
		// is present.
		} elseif ($event->getAttendances() == $event->getAttendancesMin()) {
			$result = 'EnoughRegistrations';
		}

		return $result;
	}

	/**
	 * Returns the message for an e-mail according to the reason
	 * $reasonForNotification provided.
	 *
	 * @param tx_seminars_registration $registration
	 *        the registration for which the notification should be send
	 * @param string $reasonForNotification
	 *        reason for the notification, must be either "IsFull" or "EnoughRegistrations", must not be empty
	 *
	 * @return string the message, will not be empty
	 */
	private function getMessageForNotification(tx_seminars_registration $registration, $reasonForNotification) {
		$localLanguageKey = 'email_additionalNotification' . $reasonForNotification;
		$this->initializeTemplate();

		$this->setMarker('message', $this->translate($localLanguageKey));
		$showSeminarFields = $this->getConfValueString('showSeminarFieldsInNotificationMail');
		if ($showSeminarFields != '') {
			$this->setMarker('seminardata', $registration->getSeminarObject()->dumpSeminarValues($showSeminarFields));
		} else {
			$this->hideSubparts('seminardata', 'field_wrapper');
		}

		return $this->getSubpart('MAIL_ADDITIONALNOTIFICATION');
	}

	/**
	 * Reads and initializes the templates.
	 * If this has already been called for this instance, this function does nothing.
	 *
	 * This function will read the template file as it is set in the TypoScript setup. If there is a template file set in the
	 * flexform of pi1, this will be ignored!
	 *
	 * @return void
	 */
	private function initializeTemplate() {
		if (!$this->isTemplateInitialized) {
			$this->getTemplateCode(TRUE);
			$this->setLabels();

			$this->isTemplateInitialized = TRUE;
		}
	}

	/**
	 * Builds the e-mail body for an e-mail to the attendee.
	 *
	 * @param tx_seminars_registration $registration
	 *        the registration for which the notification should be send
	 * @param tslib_pibase $plugin a live plugin
	 * @param string $helloSubjectPrefix
	 *        prefix for the locallang key of the localized hello and subject
	 *        string; allowed values are:
	 *        - confirmation
	 *        - confirmationOnUnregistration
	 *        - confirmationOnRegistrationForQueue
	 *        - confirmationOnQueueUpdate
	 *        In the following, the parameter is prefixed with "email_" and postfixed with "Hello" or "Subject".
	 * @param bool $useHtml whether to create HTML instead of plain text
	 *
	 * @return string the e-mail body for the attendee e-mail, will not be empty
	 */
	private function buildEmailContent(
		tx_seminars_registration $registration, tslib_pibase $plugin, $helloSubjectPrefix , $useHtml = FALSE
	) {
		if ($this->linkBuilder === NULL) {
			/** @var $linkBuilder tx_seminars_Service_SingleViewLinkBuilder */
			$linkBuilder = t3lib_div::makeInstance('tx_seminars_Service_SingleViewLinkBuilder');
			$this->injectLinkBuilder($linkBuilder);
		}
		$this->linkBuilder->setPlugin($plugin);

		$wrapperPrefix = ($useHtml ? 'html_' : '') . 'field_wrapper';

		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
			$charset = 'utf-8';
		} else {
			$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']
				? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'utf-8';
		}

		$this->setMarker('html_mail_charset', $charset);
		$this->hideSubparts($this->getConfValueString('hideFieldsInThankYouMail'), $wrapperPrefix);

		$this->setEMailIntroduction($helloSubjectPrefix, $registration);
		$event = $registration->getSeminarObject();
		$this->fillOrHideUnregistrationNotice($helloSubjectPrefix, $registration, $useHtml);

		$this->setMarker('uid', $event->getUid());

		$this->setMarker('registration_uid', $registration->getUid());

		if ($registration->hasSeats()) {
			$this->setMarker('seats', $registration->getSeats());
		} else {
			$this->hideSubparts('seats', $wrapperPrefix);
		}

		$this->fillOrHideAttendeeMarker($registration, $useHtml);

		if ($registration->hasLodgings()) {
			$this->setMarker('lodgings', $registration->getLodgings());
		} else {
			$this->hideSubparts('lodgings', $wrapperPrefix);
		}

		if ($registration->hasAccommodation()) {
			$this->setMarker('accommodation', $registration->getAccommodation());
		} else {
			$this->hideSubparts('accommodation', $wrapperPrefix);
		}

		if ($registration->hasFoods()) {
			$this->setMarker('foods', $registration->getFoods());
		} else {
			$this->hideSubparts('foods', $wrapperPrefix);
		}

		if ($registration->hasFood()) {
			$this->setMarker('food', $registration->getFood());
		} else {
			$this->hideSubparts('food', $wrapperPrefix);
		}

		if ($registration->hasCheckboxes()) {
			$this->setMarker('checkboxes', $registration->getCheckboxes());
		} else {
			$this->hideSubparts('checkboxes', $wrapperPrefix);
		}

		if ($registration->hasKids()) {
			$this->setMarker('kids', $registration->getNumberOfKids());
		} else {
			$this->hideSubparts('kids', $wrapperPrefix);
		}

		if ($event->hasAccreditationNumber()) {
			$this->setMarker('accreditation_number', $event->getAccreditationNumber());
		} else {
			$this->hideSubparts('accreditation_number', $wrapperPrefix);
		}

		if ($event->hasCreditPoints()) {
			$this->setMarker('credit_points', $event->getCreditPoints());
		} else {
			$this->hideSubparts('credit_points', $wrapperPrefix);
		}

		$this->setMarker('date', $event->getDate(($useHtml ? '&#8212;' : '-')));
		$this->setMarker('time', $event->getTime(($useHtml ? '&#8212;' : '-')));

		$this->fillPlacesMarker($event, $useHtml);

		if ($event->hasRoom()) {
			$this->setMarker('room', $event->getRoom());
		} else {
			$this->hideSubparts('room', $wrapperPrefix);
		}

		if ($registration->hasPrice()) {
			$this->setMarker('price', $registration->getPrice());
		} else {
			$this->hideSubparts('price', $wrapperPrefix);
		}

		if ($registration->hasTotalPrice()) {
			$this->setMarker('total_price', $registration->getTotalPrice());
		} else {
			$this->hideSubparts('total_price', $wrapperPrefix);
		}

		// We don't need to check $this->seminar->hasPaymentMethods() here as
		// method_of_payment can only be set (using the registration form) if
		// the event has at least one payment method.
		if ($registration->hasMethodOfPayment()) {
			$this->setMarker('paymentmethod', $event->getSinglePaymentMethodPlain($registration->getMethodOfPaymentUid()));
		} else {
			$this->hideSubparts('paymentmethod', $wrapperPrefix);
		}

		$this->setMarker('billing_address', $registration->getBillingAddress());

		if ($registration->hasInterests()) {
			$this->setMarker('interests', $registration->getInterests());
		} else {
			$this->hideSubparts('interests', $wrapperPrefix);
		}


		/** @var $newEvent tx_seminars_Model_Event */
		$newEvent = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->find($event->getUid());
		$singleViewUrl = $this->linkBuilder->createAbsoluteUrlForEvent($newEvent);
		$this->setMarker('url', ($useHtml ? htmlspecialchars($singleViewUrl) : $singleViewUrl));

		if ($event->isPlanned()) {
			$this->unhideSubparts('planned_disclaimer', $wrapperPrefix);
		} else {
			$this->hideSubparts('planned_disclaimer', $wrapperPrefix);
		}

		$footers = $event->getOrganizersFooter();
		$this->setMarker('footer', !empty($footers) ? LF . '-- ' . LF . $footers[0] : '');

		$this->callModifyAttendeeEmailTextHooks($registration, $this->getTemplate());

		return $this->getSubpart($useHtml ? 'MAIL_THANKYOU_HTML' : 'MAIL_THANKYOU');
	}

	/**
	 * Checks whether the given event allows registration, as far as its date is concerned.
	 *
	 * @param tx_seminars_seminar $event the event to check the registration for
	 *
	 * @return bool TRUE if the event allows registration by date, FALSE otherwise
	 */
	public function allowsRegistrationByDate(tx_seminars_seminar $event) {
		if ($event->hasDate()) {
			$result = !$event->isRegistrationDeadlineOver();
		} else {
			$result = $event->getConfValueBoolean('allowRegistrationForEventsWithoutDate');
		}

		return $result && $this->registrationHasStarted($event);
	}

	/**
	 * Checks whether the given event allows registration as far as the number of vacancies are concerned.
	 *
	 * @param tx_seminars_seminar $event the event to check the registration for
	 *
	 * @return bool TRUE if the event has enough seats for registration, FALSE otherwise
	 */
	public function allowsRegistrationBySeats(tx_seminars_seminar $event) {
		return $event->hasRegistrationQueue() || $event->hasUnlimitedVacancies() || $event->hasVacancies();
	}

	/**
	 * Checks whether the registration for this event has started.
	 *
	 * @param tx_seminars_seminar $event the event to check the registration for
	 *
	 * @return bool TRUE if registration for this event already has started, FALSE otherwise
	 */
	public function registrationHasStarted(tx_seminars_seminar $event) {
		if (!$event->hasRegistrationBegin()) {
			return TRUE;
		}

		return ($GLOBALS['SIM_EXEC_TIME'] >= $event->getRegistrationBeginAsUnixTimestamp());
	}

	/**
	 * Fills the attendees_names marker or hides it if necessary.
	 *
	 * @param tx_seminars_registration $registration the current registration
	 * @param bool $useHtml whether to create HTML instead of plain text
	 *
	 * @return void
	 */
	private function fillOrHideAttendeeMarker(tx_seminars_registration $registration, $useHtml) {
		if (!$registration->hasAttendeesNames()) {
			$this->hideSubparts('attendees_names', ($useHtml ? 'html_' : '') . 'field_wrapper');
			return;
		}

		$this->setMarker('attendees_names', $registration->getEnumeratedAttendeeNames($useHtml));
	}

	/**
	 * Sets the places marker for the attendee notification.
	 *
	 * @param tx_seminars_seminar $event event of this registration
	 * @param bool $useHtml whether to create HTML instead of plain text
	 *
	 * @return void
	 */
	private function fillPlacesMarker(tx_seminars_seminar $event, $useHtml) {
		if (!$event->hasPlace()) {
			$this->setMarker('place', $this->translate('message_willBeAnnounced'));
			return;
		}

		$newline = ($useHtml) ? '<br />' : LF;

		$formattedPlaces = array();
		foreach ($event->getPlaces() as $place) {
			$formattedPlaces[] = $this->formatPlace($place, $newline);
		}

		$this->setMarker('place', implode($newline . $newline, $formattedPlaces));
	}

	/**
	 * Formats a place for the thank-you e-mail.
	 *
	 * @param tx_seminars_Model_Place $place the place to format
	 * @param string $newline the newline to use in formatting, must be either LF or '<br />'
	 *
	 * @return string the formatted place, will not be empty
	 */
	private function formatPlace(tx_seminars_Model_Place $place, $newline) {
		$address = preg_replace('/[\n|\r]+/', ' ', str_replace('<br />', ' ', strip_tags($place->getAddress())));

		$countryName = ($place->hasCountry()) ? ', ' . $place->getCountry()->getLocalShortName() : '';
		$zipAndCity = trim($place->getZip() . ' ' . $place->getCity());

		return $place->getTitle() . $newline . $address . $newline . $zipAndCity . $countryName;
	}

	/**
	 * Sets the introductory part of the e-mail to the attendees.
	 *
	 * @param string $helloSubjectPrefix
	 *        prefix for the locallang key of the localized hello and subject
	 *        string, allowed values are:
	 *          - confirmation
	 *          - confirmationOnUnregistration
	 *          - confirmationOnRegistrationForQueue
	 *          - confirmationOnQueueUpdate
	 *          In the following the parameter is prefixed with
	 *          "email_" and postfixed with "Hello".
	 * @param tx_seminars_registration $registration the registration the introduction should be created for
	 *
	 * @return void
	 */
	private function setEMailIntroduction($helloSubjectPrefix, tx_seminars_registration $registration) {
		/** @var $salutation tx_seminars_EmailSalutation */
		$salutation = t3lib_div::makeInstance('tx_seminars_EmailSalutation');
		$this->setMarker('salutation', $salutation->getSalutation($registration->getFrontEndUser()));

		$event = $registration->getSeminarObject();
		$introduction = $salutation->createIntroduction($this->translate('email_' . $helloSubjectPrefix . 'Hello'), $event);

		if ($registration->hasTotalPrice()) {
			$introduction .= ' ' . sprintf($this->translate('email_price'), $registration->getTotalPrice());
		}

		$this->setMarker('introduction', $introduction . '.');
	}

	/**
	 * Fills or hides the unregistration notice depending on the notification
	 * e-mail type.
	 *
	 * @param string $helloSubjectPrefix
	 *        prefix for the locallang key of the localized hello and subject
	 *        string, allowed values are:
	 *          - confirmation
	 *          - confirmationOnUnregistration
	 *          - confirmationOnRegistrationForQueue
	 *          - confirmationOnQueueUpdate
	 * @param tx_seminars_registration $registration the registration the introduction should be created for
	 * @param bool $useHtml whether to send HTML instead of plain text e-mail
	 *
	 * @return void
	 */
	private function fillOrHideUnregistrationNotice($helloSubjectPrefix, tx_seminars_registration $registration, $useHtml) {
		$event = $registration->getSeminarObject();
		if (($helloSubjectPrefix === 'confirmationOnUnregistration') || !$event->isUnregistrationPossible()) {
			$this->hideSubparts('unregistration_notice', ($useHtml ? 'html_' : '') . 'field_wrapper');
			return;
		}

		$this->setMarker('unregistration_notice', $this->getUnregistrationNotice($event));
	}

	/**
	 * Returns the unregistration notice for the notification mails.
	 *
	 * @param tx_seminars_seminar $event the event to get the unregistration deadline from
	 *
	 * @return string the unregistration notice with the event's unregistration deadline, will not be empty
	 */
	protected function getUnregistrationNotice(tx_seminars_seminar $event) {
		$unregistrationDeadline = $event->getUnregistrationDeadlineFromModelAndConfiguration();

		return sprintf(
			$this->translate('email_unregistrationNotice'),
			strftime($this->getConfValueString('dateFormatYMD'), $unregistrationDeadline)
		);
	}

	/**
	 * Returns the (old) registration created via createRegistration.
	 *
	 * @return tx_seminars_registration the created registration, will be NULL if no registration has been created
	 */
	public function getRegistration() {
		return $this->registration;
	}

	/**
	 * Gets all hooks for this class.
	 *
	 * @return array the hook objects, will be empty if no hooks have been set
	 */
	private function getHooks() {
		if (!$this->hooksHaveBeenRetrieved) {
			$hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'];
			if (is_array($hookClasses)) {
				foreach ($hookClasses as $hookClass) {
					$this->hooks[] = t3lib_div::getUserObj($hookClass);
				}
			}

			$this->hooksHaveBeenRetrieved = TRUE;
		}

		return $this->hooks;
	}

	/**
	 * Injects a link builder.
	 *
	 * @param tx_seminars_Service_SingleViewLinkBuilder $linkBuilder the link builder instance to use
	 *
	 * @return void
	 */
	public function injectLinkBuilder(tx_seminars_Service_SingleViewLinkBuilder $linkBuilder) {
		$this->linkBuilder = $linkBuilder;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationmanager.php']);
}