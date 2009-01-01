<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'pi2/class.tx_seminars_pi2.php');

/**
 * Plugin 'Seminar Manager' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi1 extends tx_oelib_templatehelper {
	/**
	 * @var string same as class name
	 */
	public $prefixId = 'tx_seminars_pi1';
	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi1/class.tx_seminars_pi1.php';
	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * @var tx_seminars_configgetter a config getter that gets us the
	 *                               configuration in plugin.tx_seminars
	 */
	private $configGetter = null;

	/**
	 * @var tx_seminars_seminar the seminar which we want to list/show or
	 *                          for which the user wants to register
	 */
	private $seminar = null;

	/**
	 * @var tx_seminars_registration the registration which we want to
	 *                               list/show in the "my events" view
	 */
	private $registration = null;

	/** @var string the previous event's category (used for the list view) */
	private $previousCategory = '';

	/** @var string the previous event's date (used for the list view) */
	private $previousDate = '';

	/**
	 * @var tx_seminars_registrationmanager an instance of registration manager
	 *                                      which we want to have around only
	 *                                      once (for performance reasons)
	 */
	private $registrationManager = null;

	/**
	 * @var tx_seminars_pi1_frontEndCategoryList
	 */
	private $categoryList = null;

	/**
	 * @var array List of field names (as keys) by which we can sort plus
	 *            the corresponding SQL sort criteria (as value).
	 *
	 * We cannot use the database table name constants here because default
	 * values for member variable don't allow for compound expression.
	 */
	public $orderByList = array(
		// The MIN gives us the first category if there are more than one.
		// The clause before the OR gets the events made up of topics (type=1)
		// and concrete dates (type=2).
		// After the OR we get the straight events.
		'category' => '(SELECT MIN(tx_seminars_categories.title)
			FROM tx_seminars_seminars_categories_mm, tx_seminars_categories,
					tx_seminars_seminars s1, tx_seminars_seminars s2
			WHERE ( ( s1.uid=s2.topic
						AND s1.object_type!=2
						AND s2.object_type=2
						AND s2.uid=tx_seminars_seminars.uid
				) OR ( s1.uid=s2.uid
						AND s2.object_type=0
						AND s1.uid=tx_seminars_seminars.uid
						)
				)
				AND tx_seminars_seminars_categories_mm.uid_foreign=tx_seminars_categories.uid
				AND tx_seminars_seminars_categories_mm.uid_local=s1.uid)',
		// Sort by title.
		// Complete event records get the title directly.
		// Date records get it from their topic record.
		'title' => '(SELECT s1.title
			FROM tx_seminars_seminars s1, tx_seminars_seminars s2
			WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type!=2))
				AND s2.uid=tx_seminars_seminars.uid)',
		'subtitle' => '(SELECT s1.subtitle
			FROM tx_seminars_seminars s1, tx_seminars_seminars s2
			WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type!=2))
				AND s2.uid=tx_seminars_seminars.uid)',
		'uid' => 'tx_seminars_seminars.uid',
		'event_type' => '(SELECT s1.event_type
			FROM tx_seminars_seminars s1, tx_seminars_seminars s2
			WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type!=2))
				AND s2.uid=tx_seminars_seminars.uid)',
		'accreditation_number' => 'tx_seminars_seminars.accreditation_number',
		'credit_points' => '(SELECT s1.credit_points
			FROM tx_seminars_seminars s1, tx_seminars_seminars s2
			WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type!=2))
				AND s2.uid=tx_seminars_seminars.uid)',
		// This will sort by the speaker names or the alphabetically lowest
		// speaker name (if there is more than one speaker).
		'speakers' => '(SELECT MIN(tx_seminars_speakers.title)
			FROM tx_seminars_seminars_speakers_mm, tx_seminars_speakers
			WHERE tx_seminars_seminars_speakers_mm.uid_local=tx_seminars_seminars.uid
				AND tx_seminars_seminars_speakers_mm.uid_foreign=tx_seminars_speakers.uid)',
		'date' => 'tx_seminars_seminars.begin_date',
		// 86400 seconds are one day, so this calculates us just the time of day.
		'time' => 'tx_seminars_seminars.begin_date % 86400',
		// This will sort by the place names or the alphabetically lowest
		// place name (if there is more than one place).
		'place' => '(SELECT MIN(tx_seminars_sites.title)
			FROM tx_seminars_seminars_place_mm, tx_seminars_sites
			WHERE tx_seminars_seminars_place_mm.uid_local=tx_seminars_seminars.uid
				AND tx_seminars_seminars_place_mm.uid_foreign=tx_seminars_sites.uid)',
		'price_regular' => '(SELECT s1.price_regular
			FROM tx_seminars_seminars s1, tx_seminars_seminars s2
			WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type!=2))
				AND s2.uid=tx_seminars_seminars.uid)',
		'price_special' => '(SELECT s1.price_special
			FROM tx_seminars_seminars s1, tx_seminars_seminars s2
			WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type!=2))
				AND s2.uid=tx_seminars_seminars.uid)',
		'organizers' => 'tx_seminars_seminars.organizers',
		'vacancies' => 'tx_seminars_seminars.attendees_max
				-(
					(SELECT COUNT(*)
					FROM tx_seminars_attendances
					WHERE tx_seminars_attendances.seminar=tx_seminars_seminars.uid
						AND tx_seminars_attendances.seats=0
						AND tx_seminars_attendances.deleted=0)
					+(SELECT SUM(tx_seminars_attendances.seats)
					FROM tx_seminars_attendances
					WHERE tx_seminars_attendances.seminar=tx_seminars_seminars.uid
						AND  tx_seminars_attendances.seats!=0
						AND tx_seminars_attendances.deleted=0)
				)',
		// This will sort by the target groups titles or the alphabetically lowest
		// target group title (if there is more than one speaker).
		'target_groups' => '(SELECT MIN(tx_seminars_target_groups.title)
			FROM tx_seminars_seminars_target_groups_mm, tx_seminars_target_groups
			WHERE tx_seminars_seminars_target_groups_mm.uid_local=tx_seminars_seminars.uid
				AND tx_seminars_seminars_target_groups_mm.uid_foreign=tx_seminars_target_groups.uid)',
		'status_registration' => 'tx_seminars_attendances.registration_queue'
	);

	/**
	 * @var array hook objects for this class
	 */
	private $hookObjects = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->configGetter) {
			$this->configGetter->__destruct();
		}
		if ($this->seminar) {
			$this->seminar->__destruct();
		}
		if ($this->registration) {
			$this->registration->__destruct();
		}
		if ($this->registrationManager) {
			$this->registrationManager->__destruct();
		}

		unset(
			$this->configGetter, $this->seminar, $this->registration,
			$this->registrationManager, $this->hookObjects, $this->feuser
		);
		parent::__destruct();
	}

	/**
	 * Displays the seminar manager HTML.
	 *
	 * @param string (unused)
	 * @param array TypoScript configuration for the plugin
	 *
	 * @return string HTML for the plugin
	 */
	public function main($unused, array $conf) {
		$this->init($conf);
		$this->pi_initPIflexForm();

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();
		$this->getHookObjects();
		$this->createHelperObjects();

		// Lets warnings from the registration manager bubble up to us.
		$this->setErrorMessage(
			$this->registrationManager->checkConfiguration(true)
		);

		$result = '';

		// Sets the UID of a single event that is requested (either by the
		// configuration in the flexform or by a parameter in the URL).
		if ($this->hasConfValueInteger('showSingleEvent', 's_template_special')) {
			$this->showUid = $this->getConfValueInteger(
				'showSingleEvent',
				's_template_special'
			);
		} else {
			$this->showUid = $this->piVars['showUid'];
		}

		$this->whatToDisplay = $this->getConfValueString('what_to_display');

		if (!in_array(
				$this->whatToDisplay,
				array(
					'list_registrations', 'list_vip_registrations',
					'countdown', 'category_list',
				)
		)) {
			$this->setFlavor($this->whatToDisplay);
		}

		switch ($this->whatToDisplay) {
			case 'edit_event':
				$result = $this->createEventEditor();
				break;
			case 'seminar_registration':
				$result = $this->createRegistrationPage();
				break;
			case 'list_vip_registrations':
				// The fallthrough is intended
				// because createRegistrationsListPage() will differentiate later.
			case 'list_registrations':
				$registrationsListClassName = t3lib_div::makeInstanceClassName(
					'tx_seminars_pi1_frontEndRegistrationsList'
				);
				$registrationsList = new $registrationsListClassName(
					$this->conf,
					$this->whatToDisplay,
					intval($this->piVars['seminar']),
					$this->cObj
				);
				$result = $registrationsList->render();
				$registrationsList->__destruct();
				unset($registrationsList);
				break;
			case 'countdown':
				$countdownClassName = t3lib_div::makeInstanceClassName(
					'tx_seminars_pi1_frontEndCountdown'
				);
				$countdown = new $countdownClassName($this->conf, $this->cObj);
				$result = $countdown->render();
				$countdown->__destruct();
				unset($countdown);
				break;
			case 'category_list':
				$categoryListClassName = t3lib_div::makeInstanceClassName(
					'tx_seminars_pi1_frontEndCategoryList'
				);
				$categoryList = new $categoryListClassName(
					$this->conf, $this->cObj
				);
				$result = $categoryList->render();
				$categoryList->__destruct();
				unset($categoryList);
				break;
			case 'csv_export_registrations':
				$result = $this->createCsvExportOfRegistrations();
				break;
			case 'event_headline':
				$eventHeadlineClassName = t3lib_div::makeInstanceClassName(
					'tx_seminars_pi1_frontEndEventHeadline'
				);
				$eventHeadline = new $eventHeadlineClassName(
					$this->conf, $this->cObj
				);
				$result = $eventHeadline->render();
				$this->setErrorMessage(
					$eventHeadline->checkConfiguration(true)
				);
				$eventHeadline->__destruct();
				unset($eventHeadline);
				break;
			case 'topic_list':
				// The fallthrough is intended
				// because createListView() will differentiate later.
			case 'my_events':
				// The fallthrough is intended
				// because createListView() will differentiate later.
			case 'my_vip_events':
				// The fallthrough is intended
				// because createListView() will differentiate later.
			case 'my_entered_events':
				// The fallthrough is intended
				// because createListView() will differentiate later.
			case 'seminar_list':
				// The fallthrough is intended
				// because createListView() will differentiate later.
			case 'favorites_list':
				// The fallthrough is intended
				// because createListView() will differentiate later.
			default:
				// Show the single view if a 'showUid' variable is set.
				if ($this->showUid) {
					// Intentionally overwrite the previously set flavor.
					$this->setFlavor('single_view');
					$this->whatToDisplay = 'seminar_list';
					$result = $this->createSingleView();
				} else {
					$result = $this->createListView($this->whatToDisplay);
				}
				break;
		}

		// Let's check the configuration and display any errors.
		// Here, we don't use the direct return value from
		// $this->checkConfiguration as this would ignore any previous error
		// messages.
		$this->checkConfiguration();
		$result .= $this->getWrappedConfigCheckMessage();

		return $this->pi_wrapInBaseClass($result);
	}


	///////////////////////
	// General functions.
	///////////////////////

	/**
	 * Checks that we are properly initialized and that we have a config getter
	 * and a registration manager.
	 *
	 * @return boolean true if we are properly initialized, false otherwise
	 */
	public function isInitialized() {
		return ($this->isInitialized
			&& is_object($this->configGetter)
			&& is_object($this->registrationManager));
	}

	/**
	 * Gets all hook objects for this class.
	 */
	private function getHookObjects() {
		$extensionConfiguration =& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$hooks =& $extensionConfiguration['seminars/pi1/class.tx_seminars_pi1.php']['hooks'];
		if (is_array($hooks)) {
			foreach ($hooks as $classReference) {
				$this->hookObjects[] = t3lib_div::getUserObj($classReference);
			}
		}
	}

	/**
	 * Creates a seminar in $this->seminar.
	 * If the seminar cannot be created, $this->seminar will be null, and
	 * this function will return false.
	 *
	 * $this->registrationManager must have been initialized before this
	 * method may be called.
	 *
	 * @param integer an event UID
	 *
	 * @return boolean true if the seminar UID is valid and the object has been
	 *                 created, false otherwise
	 */
	public function createSeminar($seminarUid) {
		$result = false;

		if ($this->seminar) {
			$this->seminar->__destruct();
			unset($this->seminar);
		}

		if (tx_seminars_objectfromdb::recordExists(
			$seminarUid,
			SEMINARS_TABLE_SEMINARS)
		) {
			/** Name of the seminar class in case someone subclasses it. */
			$seminarClassname = t3lib_div::makeInstanceClassName(
				'tx_seminars_seminar'
			);
			$this->seminar = new $seminarClassname($seminarUid);
			$result = true;
		} else {
			$this->seminar = null;
		}

		return $result;
	}

	/**
	 * Creates a registration in $this->registration from the database record
	 * with the UID specified in the parameter $registrationUid.
	 * If the registration cannot be created, $this->registration will be null,
	 * and this function will return false.
	 *
	 * $this->registrationManager must have been initialized before this
	 * method may be called.
	 *
	 * @param integer a registration UID
	 *
	 * @return boolean true if the registration UID is valid and the object
	 *                 has been created, false otherwise
	 */
	public function createRegistration($registrationUid) {
		$result = false;

		if ($this->registration) {
			$this->registration->__destruct();
			unset($this->registration);
		}

		if (tx_seminars_objectfromdb::recordExists(
			$registrationUid, SEMINARS_TABLE_ATTENDANCES)
		) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				SEMINARS_TABLE_ATTENDANCES,
				SEMINARS_TABLE_ATTENDANCES.' . uid=' . $registrationUid .
					tx_oelib_db::enableFields(SEMINARS_TABLE_ATTENDANCES)
			);
			/** Name of the registration class in case someone subclasses it. */
			$registrationClassname = t3lib_div::makeInstanceClassName('tx_seminars_registration');
			$this->registration = new $registrationClassname(
				$this->cObj, $dbResult
			);
			if ($dbResult) {
				$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			}
			$result = $this->registration->isOk();
			if (!$result) {
				$this->registration = null;
			}
		} else {
			$this->registration = null;
		}

		return $result;
	}

	/**
	 * Creates the config getter and the registration manager.
	 */
	public function createHelperObjects() {
		/** Name of the configGetter class in case someone subclasses it. */
		$configGetterClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_configgetter'
		);
		$this->configGetter = new $configGetterClassname();

		/** Name of the registrationManager class in case someone subclasses it. */
		$registrationManagerClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_registrationmanager'
		);
		$this->registrationManager = new $registrationManagerClassname();
	}

	/**
	 * Gets our seminar object.
	 *
	 * @return tx_seminars_seminar our seminar object
	 */
	public function getSeminar() {
		return $this->seminar;
	}

	/**
	 * Returns the current registration.
	 *
	 * @return tx_seminars_registration the current registration
	 */
	public function getRegistration() {
		return $this->registration;
	}

	/**
	 * Returns the shared registration manager.
	 *
	 * @return tx_seminars_registrationmanager the shared registration manager
	 */
	public function getRegistrationManager() {
		return $this->registrationManager;
	}

	/**
	 * Returns our config getter (which might be null if we aren't initialized
	 * properly yet).
	 *
	 * This function is intended for testing purposes only.
	 *
	 * @return object our config getter, might be null
	 */
	public function getConfigGetter() {
		return $this->configGetter;
	}

	/**
	 * Creates the link to the list of registrations for the current seminar.
	 * Returns an empty string if this link is not allowed.
	 * For standard lists, a link is created if either the user is a VIP or is
	 * registered for that seminar (with the link to the VIP list taking
	 * precedence).
	 *
	 * @return string HTML for the link (may be an empty string)
	 */
	protected function getRegistrationsListLink() {
		$result = '';
		$targetPageId = 0;

		if ($this->seminar->canViewRegistrationsList(
				$this->whatToDisplay,
				0,
				$this->getConfValueInteger('registrationsVipListPID'),
				$this->getConfValueInteger(
					'defaultEventVipsFeGroupID',
					's_template_special')
				)
			) {
			// So a link to the VIP list is possible.
			$targetPageId = $this->getConfValueInteger('registrationsVipListPID');
		// No link to the VIP list ... so maybe to the list for the participants.
		} elseif ($this->seminar->canViewRegistrationsList($this->whatToDisplay,
			$this->getConfValueInteger('registrationsListPID'))) {
			$targetPageId = $this->getConfValueInteger('registrationsListPID');
		}

		if ($targetPageId) {
			$result = $this->cObj->getTypoLink(
				$this->translate('label_listRegistrationsLink'),
				$targetPageId,
				array('tx_seminars_pi1[seminar]' => $this->seminar->getUid())
			);
		}

		return $result;
	}

	/**
	 * Returns a label wrapped in <a> tags. The link points to the login page
	 * and contains a redirect parameter that points back to a certain page
	 * (must be provided as a parameter to this function). The user will be
	 * redirected to this page after a successful login.
	 *
	 * If an event uid is provided, the return parameter will contain a showUid
	 * parameter with this UID.
	 *
	 * @param string the label to wrap into a link
	 * @param integer the PID of the page to redirect to after login (must not
	 *                be empty)
	 * @param integer the UID of the event (may be empty)
	 *
	 * @return string the wrapped label
	 */
	public function getLoginLink($label, $pageId, $eventId = 0) {
		$linkConfiguration = array('parameter' => $pageId);

		if ($eventId) {
			$linkConfiguration['additionalParams']
				= t3lib_div::implodeArrayForUrl(
					'tx_seminars_pi1',
					array(
						'seminar' => $eventId,
						'action' => 'register',
					),
					'',
					false,
					true
				);
		}

		$redirectUrl = t3lib_div::locationHeaderUrl(
			$this->cObj->typoLink_URL($linkConfiguration)
		);

		// XXX We need to do this workaround of manually encoding brackets in
		// the URL due to a bug in the TYPO3 core:
		// http://bugs.typo3.org/view.php?id=3808
		$redirectUrl = preg_replace(
			array('/\[/', '/\]/'),
			array('%5B', '%5D'),
			$redirectUrl
		);

		return $this->cObj->typoLink(
			$label,
			array(
				'parameter' => $this->getConfValueInteger('loginPID'),
				'additionalParams' => t3lib_div::implodeArrayForUrl(
					'',
					array(
						rawurlencode('tx_seminars_pi1[uid]') => $eventId,
						'redirect_url' => $redirectUrl,
					)
				)
			)
		);
	}


	///////////////////////////
	// Single view functions.
	///////////////////////////

	/**
	 * Displays detailed data for a seminar.
	 * Fields listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return string HTML for the plugin
	 */
	private function createSingleView() {
		$this->internal['currentTable'] = SEMINARS_TABLE_SEMINARS;
		$this->internal['currentRow'] = $this->pi_getRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->showUid
		);

		$this->hideSubparts(
			$this->getConfValueString('hideFields', 's_template_special'),
			'FIELD_WRAPPER'
		);

		if ($this->createSeminar($this->internal['currentRow']['uid'])) {
			// Lets warnings from the seminar bubble up to us.
			$this->setErrorMessage($this->seminar->checkConfiguration(true));

			// This sets the title of the page for use in indexed search results:
			$GLOBALS['TSFE']->indexedDocTitle = $this->seminar->getTitle();

			$this->setEventTypeMarker();

			$this->setMarker(
				'STYLE_SINGLEVIEWTITLE',
				$this->seminar->createImageForSingleView(
					$this->getConfValueInteger('seminarImageSingleViewWidth'),
					$this->getConfValueInteger('seminarImageSingleViewHeight')
				)
			);

			$this->setMarker('title', $this->seminar->getTitle());
			$this->setMarker('uid', $this->seminar->getUid());

			$this->setSubtitleMarker();
			$this->setDescriptionMarker();

			$this->setAccreditationNumberMarker();
			$this->setCreditPointsMarker();

			$this->setCategoriesMarker();

			$this->setMarker('date', $this->seminar->getDate());
			$this->setMarker('time', $this->seminar->getTime());

			$this->setPlaceMarker();
			$this->setRoomMarker();
			$this->setAdditionalTimesAndPlacesMarker();;

			$this->setTimeSlotsMarkers();

			$this->setGenderSpecificHeading('speakers');
			$this->setSpeakersMarker();
			$this->setGenderSpecificHeading('partners');
			$this->setPartnersMarker();
			$this->setGenderSpecificHeading('tutors');
			$this->setTutorsMarker();
			$this->setGenderSpecificHeading('leaders');
			$this->setLeadersMarker();

			$this->setLanguageMarker();

			$this->setPriceMarkers();
			$this->setPaymentMethodsMarker();

			$this->setAdditionalInformationMarker();

			$this->setTargetGroupsMarkers();

			$this->setRequirementsMarker();
			$this->setDependenciesMarker();

			$this->setMarker('organizers', $this->seminar->getOrganizers($this));
			$this->setOrganizingPartnersMarker();

			$this->setOwnerDataMarker();

			$this->setAttachedFilesMarkers();

			$this->setVacanciesMarker();

			$this->setRegistrationDeadlineMarker();
			$this->setRegistrationMarker();
			$this->setListOfRegistrationMarker();

			$this->hideUnneededSubpartsForTopicRecords();

			// Modifies the single view hook.
			foreach ($this->hookObjects as $hookObject) {
				if (method_exists($hookObject, 'modifySingleView')) {
					$hookObject->modifySingleView($this);
				}
			}

			$result = $this->getSubpart('SINGLE_VIEW');

			// Caches $this->seminar because the list view will overwrite
			// $this->seminar.
			// TODO: This needs to be removed as soon as the list view is moved
			// to it's own class.
			// @see https://bugs.oliverklee.com/show_bug.cgi?id=290
			$seminar = clone $this->seminar;
			if ($this->seminar->hasEndDate()) {
				$result .= $this->createEventsOnNextDayList();
			}
			$this->seminar->__destruct();
			unset($this->seminar);
			$this->seminar = $seminar;
			unset($seminar);
			if ($this->seminar->isEventTopic() || $this->seminar->isEventDate()) {
				$result .= $this->createOtherDatesList();
			}
		} else {
			$this->setMarker(
				'error_text',
				$this->translate('message_wrongSeminarNumber')
			);
			$result = $this->getSubpart('ERROR_VIEW');
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
				'Status: 404 Not Found'
			);
		}

		$this->setMarker(
			'backlink',
			$this->pi_linkTP(
				$this->translate('label_back', 'Back'),
				array(),
				true,
				$this->getConfValueInteger('listPID')
			)
		);
		$result .= $this->getSubpart('BACK_VIEW');

		return $result;
	}

	/**
	 * Fills in the matching marker for the event type or hides the subpart
	 * if there is no event type.
	 */
	private function setEventTypeMarker() {
		if (!$this->seminar->hasEventType()) {
			$this->hideSubparts('event_type', 'field_wrapper');
			return;
		}

		$this->setMarker('event_type', $this->seminar->getEventType());
	}

	/**
	 * Fills in the matching marker for the subtitle or hides the subpart
	 * if there is no subtitle.
	 */
	private function setSubtitleMarker() {
		if (!$this->seminar->hasSubtitle()) {
			$this->hideSubparts('subtitle', 'field_wrapper');
			return;
		}

		$this->setMarker('subtitle', $this->seminar->getSubtitle());
	}

	/**
	 * Fills in the matching marker for the desription or hides the subpart
	 * if there is no description.
	 */
	private function setDescriptionMarker() {
		if (!$this->seminar->hasDescription()) {
			$this->hideSubparts('description', 'field_wrapper');
			return;
		}

		$this->setMarker('description', $this->seminar->getDescription($this));
	}

	/**
	 * Fills in the matching marker for the accreditation number or hides the
	 * subpart if there is no accreditation number.
	 */
	private function setAccreditationNumberMarker() {
		if (!$this->seminar->hasAccreditationNumber()) {
			$this->hideSubparts('accreditation_number', 'field_wrapper');
			return;
		}

		$this->setMarker(
			'accreditation_number', $this->seminar->getAccreditationNumber()
		);
	}

	/**
	 * Fills in the matching marker for the credit points or hides the subpart
	 * if there are no credit points.
	 */
	private function setCreditPointsMarker() {
		if (!$this->seminar->hasCreditPoints()) {
			$this->hideSubparts('credit_points', 'field_wrapper');
			return;
		}

		$this->setMarker('credit_points', $this->seminar->getCreditPoints());
	}

	/**
	 * Fills in the matching marker for the categories or hides the subpart
	 * if there are no categories.
	 */
	private function setCategoriesMarker() {
		if (!$this->seminar->hasCategories()) {
			$this->hideSubparts('category', 'field_wrapper');
			return;
		}

		$categoryMarker = '';
		$allCategories = $this->seminar->getCategories();

		foreach ($allCategories as $category) {
			$this->setMarker('category_title', $category['title']);
			$this->setMarker(
				'category_icon', $this->createCategoryIcon($category)
			);
			$categoryMarker .= $this->getSubpart('SINGLE_CATEGORY');
		}
		$this->setSubpart('SINGLE_CATEGORY', $categoryMarker);
	}

	/**
	 * Fills in the matching marker for the place.
	 */
	private function setPlaceMarker() {
		$this->setMarker(
			'place',
			$this->getConfValueBoolean('showSiteDetails', 's_template_special')
				? $this->seminar->getPlaceWithDetails($this)
				: $this->seminar->getPlaceShort()
		);
	}

	/**
	 * Fills in the matching marker for the room or hides the subpart if there
	 * is no room.
	 */
	private function setRoomMarker() {
		if (!$this->seminar->hasRoom()) {
			$this->hideSubparts('room', 'field_wrapper');
			return;
		}

		$this->setMarker('room', $this->seminar->getRoom());
	}

	/**
	 * Fills in the matching marker for the additional times and places or hides
	 * the subpart if there are no additional times and places.
	 */
	private function setAdditionalTimesAndPlacesMarker() {
		if (!$this->seminar->hasAdditionalTimesAndPlaces()) {
			$this->hideSubparts('additional_times_places', 'field_wrapper');
			return;
		}

		$this->setMarker(
			'additional_times_places',
			$this->seminar->getAdditionalTimesAndPlaces()
		);
	}

	/**
	 * Fills in the matching markers for the time slots or hides the subpart
	 * if there are no time slots.
	 */
	private function setTimeSlotsMarkers() {
		if (!$this->seminar->hasTimeslots()) {
			$this->hideSubparts('timeslots', 'field_wrapper');
			return;
		}

		$this->hideSubparts('date,time', 'field_wrapper');
		$timeSlotsOutput = '';

		$timeSlots = $this->seminar->getTimeSlotsAsArrayWithMarkers();
		foreach ($timeSlots as $timeSlot) {
			foreach ($timeSlot as $key => $value) {
				$this->setMarker($key, $value, 'timeslot');
			}
			$timeSlotsOutput .= $this->getSubpart('SINGLE_TIMESLOT');
		}

		$this->setSubpart('SINGLE_TIMESLOT', $timeSlotsOutput);
	}

	/**
	 * Fills in the matching markers for the speakers or hides the subpart if
	 * there are no speakers.
	 */
	private function setSpeakersMarker() {
		if (!$this->seminar->hasSpeakers()) {
			$this->hideSubparts('speakers', 'field_wrapper');
			return;
		}

		$this->setSpeakersMarkerWithoutCheck('speakers');
	}

	/**
	 * Fills in the matching markers for the partners or hides the subpart if
	 * there are no partners.
	 */
	private function setPartnersMarker() {
		if (!$this->seminar->hasPartners()) {
			$this->hideSubparts('partners', 'field_wrapper');
			return;
		}

		$this->setSpeakersMarkerWithoutCheck('partners');
	}

	/**
	 * Fills in the matching markers for the tutors or hides the subpart if
	 * there are no tutors.
	 */
	private function setTutorsMarker() {
		if (!$this->seminar->hasTutors()) {
			$this->hideSubparts('tutors', 'field_wrapper');
			return;
		}

		$this->setSpeakersMarkerWithoutCheck('tutors');
	}

	/**
	 * Fills in the matching markers for the leaders or hides the subpart if
	 * there are no leaders.
	 */
	private function setLeadersMarker() {
		if (!$this->seminar->hasLeaders()) {
			$this->hideSubparts('leaders', 'field_wrapper');
			return;
		}

		$this->setSpeakersMarkerWithoutCheck('leaders');
	}

	/**
	 * Sets the speaker markers for the type given in $speakerType without
	 * checking whether the current event has any speakers of the given type.
	 *
	 * @throws Exception if the given speaker type is not allowed
	 *
	 * @param string the speaker type to set the markers for, must not be
	 *               empty, must be one of the following: "speakers",
	 *               "partners", "tutors" or "leaders"
	 */
	private function setSpeakersMarkerWithoutCheck($speakerType) {
		if (!in_array(
				$speakerType,
				array('speakers', 'partners', 'tutors', 'leaders')
		)) {
			throw new Exception(
				'The speaker type given in the parameter $speakerType is not ' .
					'an allowed type.'
			);
		}

		$this->setMarker(
			$speakerType,
			$this->getConfValueBoolean('showSpeakerDetails', 's_template_special')
				? $this->seminar->getSpeakersWithDescription($this, $speakerType)
				: $this->seminar->getSpeakersShort($this, $speakerType)
		);
	}

	/**
	 * Fills in the matching marker for the language or hides the unused
	 * subpart.
	 */
	private function setLanguageMarker() {
		if (!$this->seminar->hasLanguage()) {
			$this->hideSubparts('language', 'field_wrapper');
			return;
		}

		$this->setMarker('language', $this->seminar->getLanguageName());
	}

	/**
	 * Fills in the matching markers for the prices or hides the unused
	 * subparts.
	 */
	private function setPriceMarkers() {
		// set the regular price (with or without early bird rebate)
		if ($this->seminar->hasEarlyBirdPrice()
			&& !$this->seminar->isEarlyBirdDeadlineOver()
		) {
			$this->setMarker(
				'price_earlybird_regular',
				$this->seminar->getEarlyBirdPriceRegular()
			);
			$this->setMarker(
				'message_earlybird_price_regular',
				sprintf(
					$this->translate('message_earlybird_price'),
					$this->seminar->getEarlyBirdDeadline()
				)
			);
			$this->setMarker(
				'price_regular',
				$this->seminar->getPriceRegular()
			);
		} else {
			$this->setMarker(
				'price_regular',
				$this->seminar->getPriceRegular()
			);
			if ($this->getConfValueBoolean(
				'generalPriceInSingle',
				's_template_special')
			) {
				$this->setMarker(
					'label_price_regular',
					$this->translate('label_price_general')
				);
			}
			$this->hideSubparts('price_earlybird_regular', 'field_wrapper');
		}

		// set the special price (with or without early bird rebate)
		if ($this->seminar->hasPriceSpecial()) {
			if ($this->seminar->hasEarlyBirdPrice()
				&& !$this->seminar->isEarlyBirdDeadlineOver()
			) {
				$this->setMarker(
					'price_earlybird_special',
					$this->seminar->getEarlyBirdPriceSpecial()
				);
				$this->setMarker(
					'message_earlybird_price_special',
					sprintf(
						$this->translate('message_earlybird_price'),
						$this->seminar->getEarlyBirdDeadline()
					)
				);
				$this->setMarker(
					'price_special',
					$this->seminar->getPriceSpecial()
				);
			} else {
				$this->setMarker(
					'price_special',
					$this->seminar->getPriceSpecial()
				);
				$this->hideSubparts(
					'price_earlybird_special',
					'field_wrapper'
				);
			}
		} else {
			$this->hideSubparts('price_special', 'field_wrapper');
			$this->hideSubparts('price_earlybird_special', 'field_wrapper');
		}

		// set the regular price (including full board)
		if ($this->seminar->hasPriceRegularBoard()) {
			$this->setMarker(
				'price_board_regular',
				$this->seminar->getPriceRegularBoard()
			);
		} else {
			$this->hideSubparts('price_board_regular', 'field_wrapper');
		}

		// set the special price (including full board)
		if ($this->seminar->hasPriceSpecialBoard()) {
			$this->setMarker(
				'price_board_special',
				$this->seminar->getPriceSpecialBoard()
			);
		} else {
			$this->hideSubparts('price_board_special', 'field_wrapper');
		}
	}

	/**
	 * Fills in the matching marker for the payment methods or hides the subpart
	 * if there are no payment methods.
	 */
	private function setPaymentMethodsMarker() {
		if (!$this->seminar->hasPaymentMethods()) {
			$this->hideSubparts('paymentmethods', 'field_wrapper');
			return;
		}

		$this->setMarker(
			'paymentmethods', $this->seminar->getPaymentMethods($this)
		);
	}

	/**
	 * Fills in the matching marker for the additional information or hides the
	 * subpart if there is no additional information.
	 */
	private function setAdditionalInformationMarker() {
		if (!$this->seminar->hasAdditionalInformation()) {
			$this->hideSubparts('additional_information', 'field_wrapper');
			return;
		}

		$this->setMarker(
			'additional_information', $this->seminar->getAdditionalInformation($this)
		);
	}

	/**
	 * Fills in the matching markers for the attached files or hides the subpart
	 * if there are no attached files.
	 */
	private function setAttachedFilesMarkers() {
		$mayDisplayAttachedFiles = true;

		if ($this->getConfValueBoolean(
				'limitFileDownloadToAttendees', 's_singleView'
		)) {
			$mayDisplayAttachedFiles =
				$this->isLoggedIn() &&
				$this->seminar->isUserRegistered($this->getFeUserUid());
		}

		if (!$this->seminar->hasAttachedFiles() || !$mayDisplayAttachedFiles) {
			$this->hideSubparts('attached_files', 'field_wrapper');
			return;
		}

		$attachedFilesOutput = '';

		foreach ($this->seminar->getAttachedFiles($this) as $attachedFile) {
			$this->setMarker('attached_file_name', $attachedFile['name']);
			$this->setMarker('attached_file_size', $attachedFile['size']);
			$this->setMarker('attached_file_type', $attachedFile['type']);

			$attachedFilesOutput .= $this->getSubpart(
				'ATTACHED_FILES_LIST_ITEM'
			);
		}

		$this->setSubpart(
			'ATTACHED_FILES_LIST_ITEM', $attachedFilesOutput
		);
	}

	/**
	 * Fills in the matching marker for the target groups or hides the subpart
	 * if there are no target groups.
	 */
	private function setTargetGroupsMarkers() {
		if (!$this->seminar->hasTargetGroups()) {
			$this->hideSubparts('target_groups', 'field_wrapper');
			return;
		}

		$targetGroupsOutput = '';

		$targetGroups = $this->seminar->getTargetGroupsAsArray();
		foreach ($targetGroups as $targetGroup) {
			$this->setMarker('target_group', $targetGroup);
			$targetGroupsOutput .= $this->getSubpart('SINGLE_TARGET_GROUP');
		}

		$this->setSubpart('SINGLE_TARGET_GROUP', $targetGroupsOutput);
	}

	/**
	 * Fills the matching marker for the requirements or hides the subpart
	 * if there are no requirements for the current event.
	 */
	private function setRequirementsMarker() {
		if (!$this->seminar->hasRequirements()) {
			$this->hideSubparts('requirements', 'field_wrapper');
			return;
		}

		$requirementsLists = $this->createRequirementsList();
		$requirementsLists->setEvent($this->seminar);

		$this->setSubpart(
			'FIELD_WRAPPER_REQUIREMENTS',
			$requirementsLists->render()
		);

		$requirementsLists->__destruct();
	}

	/**
	 * Fills the matching marker for the dependencies or hides the subpart
	 * if there are no dependencies for the current event.
	 */
	private function setDependenciesMarker() {
		if (!$this->seminar->hasDependencies()) {
			$this->hideSubparts('dependencies', 'field_wrapper');
			return;
		}

		$output = '';
		foreach ($this->seminar->getDependencies() as $dependency) {
			$this->setMarker(
				'dependency_title',
				$dependency->getLinkedFieldValue($this, 'title')
			);
			$output .= $this->getSubpart('SINGLE_DEPENDENCY');
		}

		$this->setSubpart('SINGLE_DEPENDENCY', $output);
	}

	/**
	 * Fills in the matching marker for the organizing partners or hides the
	 * subpart if there are no organizing partners.
	 */
	private function setOrganizingPartnersMarker() {
		if (!$this->seminar->hasOrganizingPartners()) {
			$this->hideSubparts('organizing_partners', 'field_wrapper');
			return;
		}

		$this->setMarker(
			'organizing_partners', $this->seminar->getOrganizingPartners($this)
		);
	}

	/**
	 * Fills in the matching marker for the owner data or hides the subpart if
	 * the event has no owner or the owner data should not be displayed.
	 */
	private function setOwnerDataMarker() {
		if (
			!$this->getConfValueBoolean(
				'showOwnerDataInSingleView', 's_singleView'
			) || !$this->seminar->hasOwner()
		) {
			$this->hideSubparts('owner_data', 'field_wrapper');
			return;
		}

		$owner = $this->seminar->getOwner();
		$ownerData = array();
		// getName always returns a non-empty string for valid records.
		$ownerData[] = htmlspecialchars($owner->getName());
		if ($owner->hasPhoneNumber()) {
			$ownerData[] = htmlspecialchars($owner->getPhoneNumber());
		}
		if ($owner->hasEMailAddress()) {
			$ownerData[] = htmlspecialchars($owner->getEMailAddress());
		}
		$this->setSubpart(
			'OWNER_DATA',
			implode($this->getSubpart('OWNER_DATA_SEPARATOR'), $ownerData)
		);

		if ($owner->hasImage()) {
			$imageTag = $this->createRestrictedImage(
				'uploads/tx_srfeuserregister/' . $owner->getImage(), '',
				$this->getConfValueInteger('ownerPictureMaxWidth'), 0, 0, '',
				$this->prefixId . '_owner_image'
			);
		} else {
			$imageTag = '';
		}
		$this->setMarker(
			'owner_image', $imageTag
		);
	}

	/**
	 * Fills in the matching marker for the vacancies or hides the subpart if
	 * the seminar does not need a registration or was canceled.
	 */
	private function setVacanciesMarker() {
		if (!$this->seminar->needsRegistration() || $this->seminar->isCanceled()) {
			$this->hideSubparts('vacancies', 'field_wrapper');
			return;
		}

		$this->setMarker('vacancies', $this->seminar->getVacanciesString());
	}

	/**
	 * Fills in the matching marker for the registration deadline or hides the
	 * subpart if there is no registration deadline.
	 */
	private function setRegistrationDeadlineMarker() {
		if (!$this->seminar->hasRegistrationDeadline()) {
			$this->hideSubparts('deadline_registration', 'field_wrapper');
			return;
		}

		$this->setMarker(
			'deadline_registration', $this->seminar->getRegistrationDeadline()
		);
	}

	/**
	 * Fills in the matching marker for the link to the registration form or
	 * hides the subpart if the registration is disabled.
	 */
	private function setRegistrationMarker() {
		if (!$this->getConfValueBoolean('enableRegistration')) {
			$this->hideSubparts('registration', 'field_wrapper');
			return;
		}

		$this->setMarker(
			'registration',
			$this->registrationManager->canRegisterIfLoggedIn($this->seminar)
			? $this->registrationManager->getLinkToRegistrationOrLoginPage(
				$this, $this->seminar)
			: $this->registrationManager->canRegisterIfLoggedInMessage(
				$this->seminar)
		);
	}

	/**
	 * Fills in the matching marker for the link to the list of registrations
	 * or hides the subpart if the currently logged in FE user is not allowed
	 * to view the list of registrations.
	 */
	private function setListOfRegistrationMarker() {
		$canViewListOfRegistrations = $this->seminar->canViewRegistrationsList(
			$this->whatToDisplay,
			$this->getConfValueInteger('registrationsListPID'),
			$this->getConfValueInteger('registrationsVipListPID')
		);

		if (!$canViewListOfRegistrations) {
			$this->hideSubparts('list_registrations', 'field_wrapper');
			return;
		}

		$this->setMarker('list_registrations', $this->getRegistrationsListLink());
	}

	/**
	 * Hides unneeded subparts for topic records.
	 */
	private function hideUnneededSubpartsForTopicRecords() {
		if ($this->seminar->getRecordType() != SEMINARS_RECORD_TYPE_TOPIC) {
			return;
		}

		$this->hideSubparts(
			'accreditation_number,date,time,place,room,speakers,organizers,' .
				'vacancies,deadline_registration,registration,' .
				'list_registrations,eventsnextday',
			'field_wrapper'
		);
	}

 	/**
	 * Creates the list of events that start the next day (after the current
	 * event has ended). Practically, this is just a special kind of list view.
	 * In case the current record is a topic record, this function will return
	 * an empty string.
	 *
	 * Note: This function relies on $this->seminar, but also overwrites
	 * $this->seminar.
	 *
	 * @return string HTML for the events list (may be an empty string)
	 */
	private function createEventsOnNextDayList() {
		$result = '';

		$seminarBag = $this->initListView('events_next_day');

		if ($this->internal['res_count']) {
			$tableEventsNextDay = $this->createListTable(
				$seminarBag, 'events_next_day'
			);

			$this->setMarker('table_eventsnextday', $tableEventsNextDay);

			$result = $this->getSubpart('EVENTSNEXTDAY_VIEW');
		}

		// Lets warnings from the seminar and the seminar bag bubble up to us.
		$this->setErrorMessage($seminarBag->checkConfiguration(true));
		$seminarBag->__destruct();
		unset($seminarBag);

		// Let's also check the list view configuration..
		$this->checkConfiguration(true, 'seminar_list');

		return $result;
	}

 	/**
	 * Creates the list of (other) dates for this topic. Practically, this is
	 * just a special kind of list view. In case this topic has no other dates,
	 * this function will return an empty string.
	 *
	 * Note: This function relies on $this->seminar, but also overwrites
	 * $this->seminar.
	 *
	 * @return string HTML for the events list (may be an empty string)
	 */
	private function createOtherDatesList() {
		$result = '';

		$seminarBag = $this->initListView('other_dates');

		if ($this->internal['res_count']) {
			// If we are on a topic record, overwrite the label with an
			// alternative text.
			if (($this->seminar->getRecordType() == SEMINARS_RECORD_TYPE_COMPLETE)
				|| ($this->seminar->getRecordType() == SEMINARS_RECORD_TYPE_TOPIC)
			) {
				$this->setMarker(
					'label_list_otherdates',
					$this->translate('label_list_dates')
				);
			}

			// Hides unneeded columns from the list.
			$temporaryHiddenColumns = array('title', 'list_registrations');
			$this->hideColumns($temporaryHiddenColumns);

			$tableOtherDates = $this->createListTable($seminarBag, 'other_dates');

			$this->setMarker('table_otherdates', $tableOtherDates);

			$result = $this->getSubpart('OTHERDATES_VIEW');

			// Un-hides the previously hidden columns.
			$this->unhideColumns($temporaryHiddenColumns);
		}

		// Lets warnings from the seminar and the seminar bag bubble up to us.
		$this->setErrorMessage($seminarBag->checkConfiguration(true));

		$seminarBag->__destruct();
		unset($seminarBag);

		// Let's also check the list view configuration..
		$this->checkConfiguration(true, 'seminar_list');

		return $result;
	}


	/////////////////////////
	// List view functions.
	/////////////////////////

	/**
	 * Creates the HTML for the event list view.
	 * This function is used for the normal event list as well as the
	 * "my events" and the "my VIP events" list.
	 *
	 * @param string a string selecting the flavor of list view: either
	 *               an empty string (for the default list view), the
	 *               value from "what_to_display" or "other_dates"
	 *
	 * @return string HTML code with the event list
	 */
	protected function createListView($whatToDisplay) {
		$result = '';
		$isOkay = true;

		switch ($whatToDisplay) {
			case 'my_events':
				if ($this->isLoggedIn()) {
					$result .= $this->getSubpart('MESSAGE_MY_EVENTS');
				} else {
					$this->setMarker(
						'error_text',
						$this->translate('message_notLoggedIn')
					);
					$result .= $this->getSubpart('ERROR_VIEW');
					$result .= $this->getLoginLink(
						$this->translate('message_pleaseLogIn'),
						$GLOBALS['TSFE']->id
					);
					$isOkay = false;
				}
				break;
			case 'my_vip_events':
				if ($this->isLoggedIn()) {
					$result .= $this->getSubpart(
						'MESSAGE_MY_VIP_EVENTS'
					);
				} else {
					$this->setMarker(
						'error_text',
						$this->translate('message_notLoggedIn')
					);
					$result .= $this->getSubpart('ERROR_VIEW');
					$result .= $this->getLoginLink(
						$this->translate('message_pleaseLogIn'),
						$GLOBALS['TSFE']->id
					);
					$isOkay = false;
				}
				break;
			case 'my_entered_events':
				$result .= $this->createEventEditor(true);
				if (empty($result)) {
					$result .= $this->getSubpart(
						'MESSAGE_MY_ENTERED_EVENTS'
					);
				} else {
					$isOkay = false;
				}
				break;
			case 'favorites_list':
				$result = 'Hello World. When I grow up I will be the list of ' .
							'favorites';
				break;
			case 'seminar_list':
				$this->createCategoryList();
				break;
			default:
				break;
		}

		if ($isOkay) {
			$result .= $this->getSelectorWidgetIfNecessary($whatToDisplay);

			// Creates the seminar or registration bag for the list view (with
			// all the filtering applied).
			$seminarOrRegistrationBag = $this->initListView($whatToDisplay);

			if ($this->internal['res_count']) {
				$result .= $this->createListTable($seminarOrRegistrationBag, $whatToDisplay);
			} else {
				$this->setMarker(
					'error_text',
					$this->translate('message_noResults')
				);
				$result .= $this->getSubpart('ERROR_VIEW');
			}

			// Shows the page browser (if not deactivated in the configuration),
			// disabling htmlspecialchars (the last parameter).
			if (!$this->getConfValueBoolean('hidePageBrowser', 's_template_special')) {
				$result .= $this->pi_list_browseresults();
			}

			// Lets warnings from the seminar and the seminar bag bubble up to us.
			$this->setErrorMessage(
				$seminarOrRegistrationBag->checkConfiguration(true)
			);

			$seminarOrRegistrationBag->__destruct();
			unset($seminarOrRegistrationBag);
		}

		return $result;
	}

	/**
	 * Initializes the list view (normal list, my events or my VIP events) and
	 * creates a seminar bag or a registration bag (for the "my events" view),
	 * but does not create any actual HTML output.
	 *
	 * @param string a string selecting the flavor of list view: either
	 *               an empty string (for the default list view), the
	 *               value from "what_to_display" or "other_dates"
	 *
	 * @return object a seminar bag or a registration bag containing the
	 *                seminars or registrations for the list view
	 */
	protected function initListView($whatToDisplay = '') {
		if (strstr($this->cObj->currentRecord, 'tt_content')) {
			$this->conf['pidList'] = $this->getConfValueString('pages');
			$this->conf['recursive'] = $this->getConfValueInteger('recursive');
		}

		$this->hideColumnsForAllViewsFromTypoScriptSetup();
		$this->hideRegisterColumnIfNecessary();
		$this->hideColumnsForAllViewsExceptMyEvents($whatToDisplay);
		$this->hideCsvExportOfRegistrationsColumnIfNecessary($whatToDisplay);
		$this->hideListRegistrationsColumnIfNecessary($whatToDisplay);
		$this->hideEditColumnIfNecessary($whatToDisplay);

		if (!isset($this->piVars['pointer'])) {
			$this->piVars['pointer'] = 0;
		}

		$this->internal['descFlag'] = $this->getListViewConfValueBoolean('descFlag');
		$this->internal['orderBy'] = $this->getListViewConfValueString('orderBy');

		// Number of results to show in a listing.
		$this->internal['results_at_a_time'] = t3lib_div::intInRange(
			$this->getListViewConfValueInteger('results_at_a_time'),
			0,
			1000,
			20
		);
		// The maximum number of 'pages' in the browse-box: 'Page 1', 'Page 2', etc.
		$this->internal['maxPages'] = t3lib_div::intInRange(
			$this->getListViewConfValueInteger('maxPages'),
			0,
			1000,
			2
		);

		if ($whatToDisplay == 'my_events') {
			$builder = $this->createRegistrationBagBuilder();
		} else {
			$builder = $this->createSeminarBagBuilder();
		}

		// Time-frames and hiding canceled events doesn't make sense for the
		// topic list.
		if (($whatToDisplay != 'topic_list') && ($whatToDisplay != 'my_events')) {
			$this->limitForAdditionalParameters($builder);
		}

		switch ($whatToDisplay) {
			case 'topic_list':
				$builder->limitToTopicRecords();
				$this->hideColumnsForTheTopicListView();
				break;
			case 'my_events':
				$builder->limitToAttendee($this->getFeUserUid());
				break;
			case 'my_vip_events':
				$isDefaultVip = isset($GLOBALS['TSFE']->fe_user->groupData['uid'][
						$this->getConfValueInteger(
							'defaultEventVipsFeGroupID',
							's_template_special'
						)
					]
				);
				if (!$isDefaultVip) {
					// The current user is not listed as a default VIP for all
					// events. Change the query to show only events where the
					// current user is manually added as a VIP.
					$builder->limitToEventManager($this->getFeUserUid());
				}
				break;
			case 'my_entered_events':
				$builder->limitToOwner($this->getFeUserUid());
				break;
			case 'events_next_day':
				$builder->limitToEventsNextDay($this->seminar);
				break;
			case 'other_dates':
				$builder->limitToOtherDatesForTopic($this->seminar);
				break;
			default:
				break;
		}

		$pointer = intval($this->piVars['pointer']);
		$resultsAtATime = t3lib_div::intInRange(
			$this->internal['results_at_a_time'], 1, 1000
		);

		$builder->setLimit(($pointer * $resultsAtATime) . ',' . $resultsAtATime);

		$seminarOrRegistrationBag = $builder->build();

		$this->internal['res_count'] = $seminarOrRegistrationBag->countWithoutLimit();

		$this->previousDate = '';
		$this->previousCategory = '';

		return $seminarOrRegistrationBag;
	}

	/**
	 * Creates just the table for the list view (without any result browser or
	 * search form).
	 * This function should only be called when there are actually any list
	 * items.
	 *
	 * @param object initialized seminar or registration bag
	 * @param string a string selecting the flavor of list view: either
	 *               an empty string (for the default list view), the
	 *               value from "what_to_display" or "other_dates"
	 *
	 * @return string HTML for the table (will not be empty)
	 */
	protected function createListTable(
		tx_seminars_bag $seminarOrRegistrationBag, $whatToDisplay
	) {
		$result = $this->createListHeader();
		$rowCounter = 0;

		foreach ($seminarOrRegistrationBag as $currentItem) {
			if ($whatToDisplay == 'my_events') {
				$this->registration = $currentItem;
				$this->seminar = $this->registration->getSeminarObject();
			} else {
				$this->seminar = $currentItem;
			}

			$result .= $this->createListRow($rowCounter, $whatToDisplay);
			$rowCounter++;
		}

		$result .= $this->createListFooter();

		return $result;
	}

	/**
	 * Returns the list view header: Start of table, header row, start of table
	 * body.
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return string HTML output, the table header
	 */
	protected function createListHeader() {
		$availableColumns = array(
			'image',
			'category',
			'title',
			'subtitle',
			'uid',
			'event_type',
			'accreditation_number',
			'credit_points',
			'speakers',
			'language',
			'date',
			'time',
			'place',
			'country',
			'city',
			'seats',
			'price_regular',
			'price_special',
			'total_price',
			'organizers',
			'target_groups',
			'vacancies',
			'status_registration',
			'registration',
			'list_registrations',
			'edit',
			'registrations',
		);

		foreach ($availableColumns as $column) {
			$this->setMarker('header_' . $column, $this->getFieldHeader($column));
		}

		return $this->getSubpart('LIST_HEADER');
	}

	/**
	 * Returns the list view footer: end of table body, end of table.
	 *
	 * @return string HTML output, the table footer
	 */
	protected function createListFooter() {
		return $this->getSubpart('LIST_FOOTER');
	}

	/**
	 * Returns a list row as a TR. Gets data from $this->seminar.
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 * If $this->seminar is invalid, an empty string is returned.
	 *
	 * @param integer Row counter. Starts at 0 (zero). Used for alternating
	 *                class values in the output rows.
	 * @param string a string selecting the flavor of list view: either
	 *               an empty string (for the default list view), the
	 *               value from "what_to_display" or "other_dates"
	 *
	 * @return string HTML output, a table row with a class attribute set
	 *                (alternative based on odd/even rows)
	 */
	protected function createListRow($rowCounter = 0, $whatToDisplay) {
		$result = '';

		if ($this->seminar->isOk()) {
			$cssClasses = array();

			if ($rowCounter % 2) {
				$cssClasses[] = 'listrow-odd';
			}
			if ($this->seminar->isCanceled()) {
				$cssClasses[] = $this->pi_getClassName('canceled');
			}
			if ($this->seminar->isOwnerFeUser()) {
				$cssClasses[] = $this->pi_getClassName('owner');
			}
			// Only use the class construct if we actually have a class.
			$completeClass = (count($cssClasses)) ?
				' class="'.implode(' ', $cssClasses).'"' :
				'';

			$this->setMarker('class_itemrow', $completeClass);

			// Retrieves the data for the columns "number of seats", "total
			// price" and "status", but only if we are on the "my_events" list.
			if ($whatToDisplay == 'my_events') {
				$attendanceData = array(
					'seats' => $this->registration->getSeats(),
					'total_price' => $this->registration->getTotalPrice()
				);
				$this->setMarker(
					'status_registration',
					$this->registration->getStatus()
				);
			} else {
				$attendanceData = array(
					'seats' => '',
					'total_price' => ''
				);
			}

			if ($this->seminar->hasImage()) {
				$image = $this->createRestrictedImage(
					SEMINARS_UPLOAD_PATH . $this->seminar->getImage(),
					$this->seminar->getTitle(),
					$this->getConfValueInteger('seminarImageListViewWidth'),
					$this->getConfValueInteger('seminarImageListViewHeight'),
					0,
					$this->seminar->getTitle()
				);
			} else {
				$image = '';
			}
			$this->setMarker('image', $image);

			$allCategories = $this->seminar->getCategories();
			if ($whatToDisplay == 'seminar_list') {
				$listOfCategories = $this->categoryList->createCategoryList(
					$allCategories
				);
			} else {
				$listOfCategories = implode(', ', $allCategories);
			}
			if (($listOfCategories === $this->previousCategory)
				&& $this->getConfValueBoolean(
					'sortListViewByCategory',
					's_template_special')
			) {
				$listOfCategories = '';
			} else {
				$this->previousCategory = $listOfCategories;
			}
			$this->setMarker(
				'category',
				$listOfCategories
			);

			$this->setMarker(
				'title_link',
				$this->seminar->getLinkedFieldValue($this, 'title')
			);
			$this->setMarker('subtitle', $this->seminar->getSubtitle());
			$this->setMarker('uid', $this->seminar->getUid($this));
			$this->setMarker('event_type', $this->seminar->getEventType());
			$this->setMarker(
				'accreditation_number',
				$this->seminar->getAccreditationNumber()
			);
			$this->setMarker(
				'credit_points',
				$this->seminar->getCreditPoints()
			);
			$this->setMarker(
				'teaser', $this->seminar->getLinkedFieldValue($this, 'teaser')
			);
			$this->setMarker(
				'speakers', $this->seminar->getSpeakersShort($this)
			);
			$this->setMarker('language', $this->seminar->getLanguageName());

			$currentDate = $this->seminar->getDate();
			if (($currentDate === $this->previousDate)
				&& $this->getConfValueBoolean(
					'omitDateIfSameAsPrevious',
					's_template_special')
			) {
				$dateToShow = '';
			} else {
				if ($whatToDisplay == 'other_dates') {
					$dateToShow = $this->seminar->getLinkedFieldValue(
						$this, 'date'
					);
				} else {
					$dateToShow = $currentDate;
				}
				$this->previousDate = $currentDate;
			}
			$this->setMarker('date', $dateToShow);

			$this->setMarker('time', $this->seminar->getTime());
			$this->setMarker('place', $this->seminar->getPlaceShort());
			$this->setMarker(
				'country',
				$this->seminar->getCountry()
			);
			$this->setMarker(
				'city',
				$this->seminar->getCities()
			);
			$this->setMarker('seats', $attendanceData['seats']);
			$this->setMarker(
				'price_regular',
				$this->seminar->getCurrentPriceRegular()
			);
			$this->setMarker(
				'price_special',
				$this->seminar->getCurrentPriceSpecial()
			);
			$this->setMarker('total_price', $attendanceData['total_price']);
			$this->setMarker(
				'organizers',
				$this->seminar->getOrganizers($this)
			);
			$this->setMarker(
				'target_groups',
				$this->seminar->getTargetGroupNames()
			);
			$this->setMarker(
				'vacancies',
				$this->seminar->getVacanciesString()
			);
			$this->setMarker(
				'class_listvacancies',
				$this->getVacanciesClasses($this->seminar)
			);

			$registrationLink = '';
			if ($whatToDisplay != 'my_events') {
				$registrationLink
					= $this->registrationManager->getRegistrationLink(
						$this, $this->seminar
					);
			} elseif ($whatToDisplay == 'my_events'
				&& $this->seminar->isUnregistrationPossible()
			) {
				$registrationLink = $this->registrationManager->getLinkToUnregistrationPage(
					$this,
					$this->registration
				);
			}
			$this->setMarker('registration', $registrationLink);
			$this->setMarker(
				'list_registrations',
				$this->getRegistrationsListLink()
			);
			$this->setMarker('edit', $this->getEditLink());

			$this->setMarker('registrations', $this->getCsvExportLink());

			$result = $this->getSubpart('LIST_ITEM');
		}

		return $result;
	}

	/**
	 * Returns a seminarBagBuilder object with the source pages set for the list
	 * view.
	 *
	 * @return tx_seminars_seminarbagbuilder the seminarBagBuilder object for
	 *                                       the list view
	 */
	private function createSeminarBagBuilder() {
		$seminarBagBuilder = t3lib_div::makeInstance(
			'tx_seminars_seminarbagbuilder'
		);

		$seminarBagBuilder->setSourcePages(
			$this->getConfValueString('pidList'),
			$this->getConfValueInteger('recursive')
		);
		$seminarBagBuilder->setOrderBy($this->getOrderByForListView());

		return $seminarBagBuilder;
	}

	/**
	 * Returns a registrationBagBuilder object limited for registrations of the
	 * currently logged in front-end user as attendee for the "my events" list
	 * view.
	 *
	 * @return tx_seminars_registrationBagBuilder the registrationBagBuilder
	 *                                            object for the "my events"
	 *                                            list view
	 */
	private function createRegistrationBagBuilder() {
		$registrationBagBuilder = t3lib_div::makeInstance(
			'tx_seminars_registrationBagBuilder'
		);

		$registrationBagBuilder->limitToAttendee($this->getFeUserUid());
		$registrationBagBuilder->setOrderByEventColumn(
			$this->getOrderByForListView()
		);

		return $registrationBagBuilder;
	}

	/**
	 * Returns a pi1_frontEndRequirementsList object.
	 *
	 * @return tx_seminars_pi1_frontEndRequirementsList the object to build the
	 *                                                  requirements list with
	 */
	private function createRequirementsList() {
		$requirementsListClass = t3lib_div::makeInstanceClassName(
			'tx_seminars_pi1_frontEndRequirementsList'
		);

		$requirementsList = new $requirementsListClass(
			$this->conf,
			$GLOBALS['TSFE']->cObj
		);

		return $requirementsList;
	}

	/**
	 * Returns the ORDER BY statement for the list view.
	 *
	 * @return string the ORDER BY statement for the list view, may be empty
	 */
	private function getOrderByForListView() {
		$orderBy = '';

		if ($this->getConfValueBoolean(
			'sortListViewByCategory', 's_template_special'
		)) {
			$orderBy = $this->orderByList['category'] . ', ';
		}

		// Overwrites the default sort order with values given by the browser.
		// This happens if the user changes the sort order manually.
		if (!empty($this->piVars['sort'])) {
			list($this->internal['orderBy'], $this->internal['descFlag']) =
				explode(':', $this->piVars['sort']);
		}

		if (isset($this->internal['orderBy'])
			&& isset($this->orderByList[$this->internal['orderBy']])
		) {
			$orderBy .= $this->orderByList[$this->internal['orderBy']] .
				($this->internal['descFlag'] ? ' DESC' : '');
		}

		return $orderBy;
	}

	/**
	 * Gets the heading for a field type, automatically wrapped in a hyperlink
	 * that sorts by that column if sorting by that column is available.
	 *
	 * @param string key of the field type for which the heading should
	 *               be retrieved, must not be empty
	 *
	 * @return string the heading label, may be completely wrapped in a
	 *                hyperlink for sorting
	 */
	public function getFieldHeader($fieldName) {
		$label = $this->translate('label_' . $fieldName);
		if (($fieldName == 'price_regular')
			&& $this->getConfValueBoolean(
				'generalPriceInList',
				's_template_special')
		) {
			$label = $this->translate('label_price_general');
		}

		// Can we sort by that field?
		if (isset($this->orderByList[$fieldName])) {
			$result = $this->pi_linkTP_keepPIvars(
				$label,
				array(
					'sort' => $fieldName . ':' .
						($this->internal['descFlag'] ? 0 : 1)
				)
			);
		} else {
			$result = $label;
		}

		return $result;
	}

	/**
	 * Returns the selector widget for the "seminars_list" view.
	 *
	 * @param string a string selecting the flavor of list view: either an empty
	 *               string (for the default list view), the value from
	 *               "what_to_display" or "other_dates"
	 *
	 * @return string the HTML code of the selector widget, may be empty
	 */
	private function getSelectorWidgetIfNecessary($whatToDisplay) {
		if ($whatToDisplay != 'seminar_list') {
			return '';
		}

		$selectorWidgetClassName = t3lib_div::makeInstanceClassName(
			'tx_seminars_pi1_frontEndSelectorWidget'
		);
		$selectorWidget = new $selectorWidgetClassName($this->conf, $this->cObj);

		$result = $selectorWidget->render();

		$selectorWidget->__destruct();
		unset($selectorWidget);

		return $result;
	}

	/**
	 * Limits the given seminarbagbuilder for additional parameters needed to
	 * build the list view.
	 *
	 * @param tx_seminars_seminarbagbuilder the seminarbagbuilder to limit for
	 *                                      additional parameters
	 */
	protected function limitForAdditionalParameters(tx_seminars_seminarbagbuilder $builder) {
		$builder->limitToDateAndSingleRecords();

		// Adds the query parameter that result from the user selection in the
		// selector widget (including the search form).
		if (is_array($this->piVars['language'])) {
			$builder->limitToLanguages(
				tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
					$this->piVars['language']
				)
			);
		}

		// TODO: This needs to be changed when bug 2304 gets fixed.
		// @see https://bugs.oliverklee.com/show_bug.cgi?id=2304
		if (is_array($this->piVars['place'])) {
			$builder->limitToPlaces(
				tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
					$this->piVars['place']
				)
			);
		} else {
			// TODO: This needs to be changed as soon as we are using the new
			// TypoScript configuration class from tx_oelib which offers a
			// getAsIntegerArray() method.
			$builder->limitToPlaces(
				t3lib_div::trimExplode(
					',',
					$this->getConfValueString(
						'limitListViewToPlaces', 's_listView'
					),
					true
				)
			);
		}

		if (is_array($this->piVars['city'])) {
			$builder->limitToCities(
				tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
					$this->piVars['city']
				)
			);
		}
		if (is_array($this->piVars['country'])) {
			$builder->limitToCountries(
				tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
					$this->piVars['country']
				)
			);
		}
		if (isset($this->piVars['sword'])
			&& !empty($this->piVars['sword'])
		) {
			$builder->limitToFullTextSearch($this->piVars['sword']);
		}

		try {
			$builder->setTimeFrame(
				$this->getConfValueString(
					'timeframeInList', 's_template_special'
				)
			);
		} catch (Exception $exception) {
			// Ignores the exception because the user will be warned of the
			// problem by the configuration check.
		}

		if (
			$this->getConfValueBoolean('hideCanceledEvents', 's_template_special')
		) {
			$builder->ignoreCanceledEvents();
		}

		if (isset($this->piVars['event_type'])
			&& (is_array($this->piVars['event_type']))
		) {
			$builder->limitToEventTypes(
				tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
					$this->piVars['event_type']
				)
			);
		} else {
			// TODO: This needs to be changed as soon as we are using the new
			// TypoScript configuration class from tx_oelib which offers a
			// getAsIntegerArray() method.
			$builder->limitToEventTypes(
				t3lib_div::trimExplode(
					',',
					$this->getConfValueString(
						'limitListViewToEventTypes', 's_listView'
					),
					true
				)
			);
		}

		if (isset($this->piVars['category'])
			&& (intval($this->piVars['category']) > 0)
		) {
			$builder->limitToCategories(intval($this->piVars['category']));
		} else {
			$builder->limitToCategories(
				$this->getConfValueString(
					'limitListViewToCategories', 's_listView'
				)
			);
		}
	}

	/**
	 * Gets the CSS classes (space-separated) for the Vacancies TD.
	 *
	 * @param object the current seminar object
	 *
	 * @return string class attribute filled with a list a space-separated
	 *                CSS classes, plus a leading space
	 */
	protected function getVacanciesClasses(tx_seminars_seminar $seminar) {
		$result = $this->pi_getClassName('vacancies');

		if ($seminar->needsRegistration()) {
			if ($seminar->hasVacancies()) {
				$result .= ' '.$this->pi_getClassName('vacancies-available').' '
					.$this->pi_getClassName('vacancies-'.$seminar->getVacancies());
			} else {
				$result .= ' '.$this->pi_getClassName('vacancies-0');
			}
			// We add this class in addition to the number of vacancies so that
			// user stylesheets still can use the number of vacancies even for
			// events for which the registration deadline is over.
			if ($seminar->isRegistrationDeadlineOver()) {
				$result .= ' '.$this->pi_getClassName('registration-deadline-over');
			}
		}

		return ' class="'.$result.'"';
	}

	/**
	 * Creates the link to the event editor for the current event.
	 * Returns an empty string if editing this event is not allowed.
	 *
	 * A link is created if the logged-in FE user is the owner of the event.
	 *
	 * @return string HTML for the link (may be an empty string)
	 */
	protected function getEditLink() {
		$result = '';

		$mayManagersEditTheirEvents = $this->getConfValueBoolean(
			'mayManagersEditTheirEvents', 's_listView'
		);

		$isUserManager = $this->seminar->isUserVip(
			$this->getFeUserUid(),
			$this->getConfValueInteger('defaultEventVipsFeGroupID')
		);

		if ($this->seminar->isOwnerFeUser()
			|| ($mayManagersEditTheirEvents && $isUserManager)
		) {
			$result = $this->cObj->getTypoLink(
				$this->translate('label_edit'),
				$this->getConfValueInteger('eventEditorPID', 's_fe_editing'),
				array(
					'tx_seminars_pi1[seminar]' => $this->seminar->getUid(),
					'tx_seminars_pi1[action]' => 'EDIT',
				)
			);
		}

		return $result;
	}

	/**
	 * Hides the columns specified in the first parameter $columnsToHide.
	 *
	 * @param array the columns to hide, may be empty
	 */
	private function hideColumns(array $columnsToHide) {
		$this->hideSubpartsArray($columnsToHide, 'LISTHEADER_WRAPPER');
		$this->hideSubpartsArray($columnsToHide, 'LISTITEM_WRAPPER');
	}

	/**
	 * Un-hides the columns specified in the first parameter $columnsToHide.
	 *
	 * @param array the columns to un-hide, may be empty
	 */
	private function unhideColumns(array $columnsToUnhide) {
		$permanentlyHiddenColumns = t3lib_div::trimExplode(
			',',
			$this->getConfValueString('hideColumns', 's_template_special'),
			true
		);

		$this->unhideSubpartsArray(
			$columnsToUnhide, $permanentlyHiddenColumns, 'LISTHEADER_WRAPPER'
		);
		$this->unhideSubpartsArray(
			$columnsToUnhide, $permanentlyHiddenColumns, 'LISTITEM_WRAPPER'
		);
	}

	/**
	 * Hides the edit column if necessary.
	 *
	 * It is necessary if the list to display is not the "events which I have
	 * entered" list and is not the "my vip events" list and VIPs are not
	 * allowed to edit their events.
	 *
	 * @param string a string selecting the flavor of list view: either
	 *               an empty string (for the default list view), the
	 *               value from "what_to_display" or "other_dates"
	 */
	private function hideEditColumnIfNecessary($whatToDisplay) {
		$mayManagersEditTheirEvents = $this->getConfValueBoolean(
			'mayManagersEditTheirEvents', 's_listView'
		);

		if ($whatToDisplay != 'my_entered_events'
			&& !($whatToDisplay == 'my_vip_events' && $mayManagersEditTheirEvents)
		) {
			$this->hideColumns(array('edit'));
		}
	}

	/**
	 * Hides the column with the link to the list of registrations if online
	 * registration is disabled, no user is logged in or there is no page
	 * specified to link to.
	 * Also hides it for the "other_dates" and "events_next_day" lists.
	 *
	 * @param string a string selecting the flavor of list view: either an empty
	 *               string (for the default list view), the value from
	 *               "what_to_display" or "other_dates"
	 */
	private function hideListRegistrationsColumnIfNecessary($whatToDisplay) {
		if (!$this->getConfValueBoolean('enableRegistration')
			|| !$this->isLoggedIn()
			|| (($whatToDisplay == 'seminar_list')
				&& !$this->hasConfValueInteger('registrationsListPID')
				&& !$this->hasConfValueInteger('registrationsVipListPID'))
			|| ($whatToDisplay == 'other_dates')
			|| ($whatToDisplay == 'events_next_day')
			|| (($whatToDisplay == 'my_events')
				&& !$this->hasConfValueInteger('registrationsListPID'))
			|| (($whatToDisplay == 'my_vip_events')
				&& !$this->hasConfValueInteger('registrationsVipListPID'))
		) {
			$this->hideColumns(array('list_registrations'));
		}
	}

	/**
	 * Hides the registration column if online registration is disabled.
	 */
	private function hideRegisterColumnIfNecessary() {
		if (!$this->getConfValueBoolean('enableRegistration')) {
			$this->hideColumns(array('registration'));
		}
	}

	/**
	 * Hides the registrations column if we are not on the "my_vip_events" view
	 * or the CSV export of registrations is not allowed on the "my_vip_events"
	 * view.
	 *
	 * @param string a string selecting the flavor of list view: either an empty
	 *               string (for the default list view), the value from
	 *               "what_to_display" or "other_dates"
	 */
	private function hideCsvExportOfRegistrationsColumnIfNecessary($whatToDisplay) {
		$isCsvExportOfRegistrationsInMyVipEventsViewAllowed
			= $this->getConfValueBoolean(
				'allowCsvExportOfRegistrationsInMyVipEventsView'
			);

		if (($whatToDisplay != 'my_vip_events')
			|| !$isCsvExportOfRegistrationsInMyVipEventsViewAllowed
		) {
			$this->hideColumns(array('registrations'));
		}
	}

	/**
	 * Hides columns which are not needed for the "topic_list" view.
	 */
	private function hideColumnsForTheTopicListView() {
		$this->hideColumns(
			array(
				'uid',
				'accreditation_number',
				'speakers',
				'date',
				'time',
				'place',
				'organizers',
				'vacancies',
				'registration',
			)
		);
	}

	/**
	 * Hides the number of seats, the total price and the registration status
	 * columns when we're not on the "my_events" list view.
	 *
	 * @param string a string selecting the flavor of list view: either an empty
	 *               string (for the default list view), the value from
	 *               "what_to_display" or "other_dates"
	 */
	private function hideColumnsForAllViewsExceptMyEvents($whatToDisplay) {
		if ($whatToDisplay != 'my_events') {
			$this->hideColumns(
				array('total_price', 'seats', 'status_registration')
			);
		}
	}

	/**
	 * Hides the columns which are listed in the TypoScript setup variable
	 * "hideColumns".
	 */
	private function hideColumnsForAllViewsFromTypoScriptSetup() {
		$this->hideColumns(
			t3lib_div::trimExplode(
				',',
				$this->getConfValueString('hideColumns', 's_template_special'),
				true
			)
		);
	}

	/**
	 * Gets the link to the CSV export.
	 *
	 * @return string the link to the CSV export
	 */
	private function getCsvExportLink() {
		return $this->cObj->typoLink(
			$this->translate('label_registrationsAsCsv'),
			array(
				'parameter' => $GLOBALS['TSFE']->id,
				'additionalParams' => t3lib_div::implodeArrayForUrl(
					'',
					array(
						'type' => tx_seminars_pi2::getTypeNum(),
						'tx_seminars_pi2' => array(
							'table' => SEMINARS_TABLE_ATTENDANCES,
							'seminar' => $this->seminar->getUid(),
						),
					)
				),
			)
		);
	}

	/**
	 * Creates a tx_seminars_pi1_frontEndCategoryList object in $this->categoryList.
	 */
	private function createCategoryList() {
		$categoryListClassName = t3lib_div::makeInstanceClassName(
			'tx_seminars_pi1_frontEndCategoryList'
		);
		$this->categoryList = new $categoryListClassName(
			$this->conf, $this->cObj
		);
	}


	/////////////////////////////////
	// Registration view functions.
	/////////////////////////////////

	/**
	 * Creates the HTML for the registration page.
	 *
	 * @return string HTML code for the registration page
	 */
	protected function createRegistrationPage() {
		$this->feuser = $GLOBALS['TSFE']->fe_user;

		$errorMessage = '';
		$registrationForm = '';
		$isOkay = false;

		$this->toggleEventFieldsOnRegistrationPage();

		if ($this->createSeminar($this->piVars['seminar'])) {
			// Lets warnings from the seminar bubble up to us.
			$this->setErrorMessage($this->seminar->checkConfiguration(true));

			if (!$this->registrationManager->canRegisterIfLoggedIn($this->seminar)) {
				$errorMessage
					= $this->registrationManager->canRegisterIfLoggedInMessage(
						$this->seminar
					);
			} else {
				if ($this->isLoggedIn()) {
					$isOkay = true;
				} else {
					$errorMessage = $this->getLoginLink(
						$this->translate('message_notLoggedIn'),
						$GLOBALS['TSFE']->id,
						$this->seminar->getUid()
					);
				}
			}
		} elseif ($this->createRegistration(
			intval($this->piVars['registration'])
		)) {
			if ($this->createSeminar($this->registration->getSeminar())) {
				if ($this->seminar->isUnregistrationPossible()) {
					$isOkay = true;
				} else {
					$errorMessage = $this->translate(
						'message_unregistrationNotPossible'
					);
				}
			}
		} else {
			switch ($this->piVars['action']) {
				case 'unregister':
					$errorMessage = $this->translate(
						'message_notRegisteredForThisEvent'
					);
					break;
				case 'register':
					// The fall-through is intended.
				default:
					$errorMessage = $this->registrationManager->existsSeminarMessage(
						$this->piVars['seminar']
					);
					break;
			}
		}

		if ($isOkay) {
			switch ($this->piVars['action']) {
				case 'unregister':
					$registrationForm = $this->createUnregistrationForm();
					break;
				case 'register':
					// The fall-through is intended.
				default:
					if ($this->registrationManager
						->userFulfillsRequirements($this->seminar)
					) {
						$registrationForm = $this->createRegistrationForm();
					} else {
						$errorMessage = $this->translate(
							'message_requirementsNotFulfilled'
						);
						$requirementsList = $this->createRequirementsList();
						$requirementsList->setEvent($this->seminar);
						$requirementsList->limitToMissingRegistrations();
						$registrationForm = $requirementsList->render();
						$requirementsList->__destruct();
					}
					break;
			}
		}

		$result = $this->createRegistrationHeading($errorMessage);
		$result .= $registrationForm;

		return $result;
	}

	/**
	 * Creates the registration page title and (if applicable) any error
	 * messages. Data from the event will only be displayed if $this->seminar
	 * is non-null.
	 *
	 * @param string error message to be displayed (may be empty if there is no
	 *               error)
	 *
	 * @return string HTML code including the title and error message
	 */
	protected function createRegistrationHeading($errorMessage) {
		$this->setMarker(
			'registration',
			$this->translate('label_registration')
		);
		$this->setMarker(
			'title',
			($this->seminar) ? $this->seminar->getTitleAndDate() : ''
		);
		$this->setMarker(
			'uid',
			($this->seminar) ? $this->seminar->getUid() : ''
		);

		if ($this->seminar && $this->seminar->hasAccreditationNumber()) {
			$this->setMarker(
				'accreditation_number',
				($this->seminar) ? $this->seminar->getAccreditationNumber() : ''
			);
		} else {
			$this->hideSubparts(
				'accreditation_number',
				'registration_wrapper'
			);
		}

		if (empty($errorMessage)) {
			$this->hideSubparts('error', 'wrapper');
		} else {
			$this->setMarker('error_text', $errorMessage);
		}

		return $this->getSubpart('REGISTRATION_HEAD');
	}

	/**
	 * Creates the registration form.
	 *
	 * @return string HTML code for the form
	 */
	protected function createRegistrationForm() {
		$registrationEditorClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_pi1_registrationEditor'
		);
		$registrationEditor = new $registrationEditorClassname($this);

		$output = $registrationEditor->_render();
		$output .= $this->getSubpart('REGISTRATION_BOTTOM');

		$registrationEditor->__destruct();
		unset($registrationEditor);

		return $output;
	}

	/**
	 * Enables/disables the display of data from event records on the
	 * registration page depending on the config variable
	 * "eventFieldsOnRegistrationPage".
	 */
	protected function toggleEventFieldsOnRegistrationPage() {
		$fieldsToShow = array();
		if ($this->hasConfValueString(
				'eventFieldsOnRegistrationPage',
				's_template_special'
			)
		) {
			$fieldsToShow = explode(
				',',
				$this->getConfValueString(
					'eventFieldsOnRegistrationPage',
					's_template_special'
				)
			);
		}

		// First, we have a list of all fields that are removal candidates.
		$fieldsToRemove = array(
			'uid',
			'title',
			'accreditation_number',
			'price_regular',
			'price_special',
			'vacancies',
			'message'
		);

		// Now iterate over the fields to show and delete them from the list
		// of items to remove.
		foreach ($fieldsToShow as $currentField) {
			$key = array_search(trim($currentField), $fieldsToRemove);
			// $key will be false if the item has not been found.
			// Zero, on the other hand, is a valid key.
			if ($key !== false) {
				unset($fieldsToRemove[$key]);
			}
		}

		if (!empty($fieldsToRemove)) {
			$this->hideSubparts(
				implode(',', $fieldsToRemove),
				'registration_wrapper'
			);
		}
	}


	///////////////////////////////////
	// Unregistration view functions.
	///////////////////////////////////

	/**
	 * Creates the unregistration form.
	 * $this->registration has to be created before this method is called.
	 *
	 * @return string HTML code for the form
	 */
	protected function createUnregistrationForm() {
		$registrationEditorClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_pi1_registrationEditor'
		);
		$registrationEditor = new $registrationEditorClassname($this);

		$result = $registrationEditor->_render();
		$result .= $this->getSubpart('REGISTRATION_BOTTOM');

		$registrationEditor->__destruct();
		unset($registrationEditor);

		return $result;
	}


	/////////////////////////////////
	// Event editor view functions.
	/////////////////////////////////

	/**
	 * Checks whether logged-in FE user has access to the event editor and then
	 * either creates the event editor HTML (or an empty string if
	 * $accessTestOnly is true) or a localized error message.
	 *
	 * @param boolean whether only the access to the event editor should be
	 *                checked
	 *
	 * @return string HTML code for the event editor (or an error message if the
	 *                FE user doesn't have access to the editor)
	 */
	protected function createEventEditor($accessTestOnly = false) {
		$result = '';

		$eventEditorClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_pi1_eventEditor'
		);
		$eventEditor = new $eventEditorClassname($this);

		if ($eventEditor->hasAccess()) {
			if (!$accessTestOnly) {
				$result = $eventEditor->_render();
			}
		} else {
			$result = $eventEditor->hasAccessMessage();
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
				'Status: 403 Forbidden'
			);
		}

		$eventEditor->__destruct();
		unset($eventEditor);

		return $result;
	}

	/**
	 * Creates the category icon IMG tag with the icon title as title attribute.
	 *
	 * @param array the filename and title of the icon in an associative array
	 *              with "icon" as key for the filename and "title" as key for
	 *              the icon title, the values for "title" and "icon" may be
	 *              empty
	 *
	 * @return string the icon IMG tag with the given icon, will be empty if the
	 *                category has no icon
	 */
	private function createCategoryIcon(array $iconData) {
		if ($iconData['icon'] == '') {
			return '';
		}

		return $this->cObj->IMAGE(
			array(
				'file' => SEMINARS_UPLOAD_PATH . $iconData['icon'],
				'titleText' => $iconData['title'],
			)
		);
	}

	/**
	 * Sets a gender specific heading for speakers, tutors, leaders or partners,
	 * depending on the speakers, tutors, leaders or partners belonging to the
	 * current seminar.
	 *
	 * @param string type of gender specific heading, must be 'speaker', 'tutors',
	 *               'leaders' or 'partners'
	 */
	private function setGenderSpecificHeading($speakerType) {
		if (!in_array(
				$speakerType,
				array('speakers', 'partners', 'tutors', 'leaders')
		)) {
			throw new Exception(
				'The given speaker type "' .  $speakerType . '" is not ' .
					'an allowed type. Allowed types are "speakers", ' .
					'"partners", "tutors" or "leaders"'
			);
		}

		$this->setMarker(
			'label_' . $speakerType,
			$this->translate(
				'label_' . $this->seminar->getLanguageKeySuffixForType($speakerType)
			)
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']);
}
?>