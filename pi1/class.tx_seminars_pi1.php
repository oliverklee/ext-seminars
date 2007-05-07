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
 * Plugin 'Seminar Manager' for the 'seminars' extension.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationmanager.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'pi1/class.tx_seminars_event_editor.php');
require_once(t3lib_extMgm::extPath('seminars').'pi1/class.tx_seminars_registration_editor.php');

class tx_seminars_pi1 extends tx_seminars_templatehelper {
	/** same as class name */
	var $prefixId = 'tx_seminars_pi1';
	/** path to this script relative to the extension dir */
	var $scriptRelPath = 'pi1/class.tx_seminars_pi1.php';

	/** the seminar which we want to list/show or for which the user wants to register */
	var $seminar;

	/** the previous event's date (used for the list view) */
	var $previousDate;

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;

	/**
	 * list of field names (as keys) by which we can sort plus the
	 * corresponding SQL sort criteria (as value)
	 */
	var $orderByList = array(
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
		'vacancies' => 'tx_seminars_seminars.attendees_max-tx_seminars_seminars.attendees'
	);

	/**
	 * This is a list of field names in which we can search, grouped by record type.
	 * 'seminars' is the list of fields that are always stored in the seminar record.
	 * 'seminars_topic' is the list of fields that might be stored in the topic
	 *  record in if we are a date record (that refers to a topic record).
	 */
	var $searchFieldList = array(
		'seminars' => array(
			'accreditation_number'
		),
		'seminars_topic' => array(
			'title',
			'subtitle',
			'description'
		),
		'speakers' => array(
			'title',
			'organization',
			'description'
		),
		'partners' => array(
			'title',
			'organization',
			'description'
		),
		'tutors' => array(
			'title',
			'organization',
			'description'
		),
		'leaders' => array(
			'title',
			'organization',
			'description'
		),
		'places' => array(
			'title',
			'address'
		),
		'event_types' => array(
			'title'
		),
		'organizers' => array(
			'title'
		)
	);

	/**
	 * Displays the seminar manager HTML.
	 *
	 * @param	string		default content string, ignore
	 * @param	array		TypoScript configuration for the plugin
	 *
	 * @return	string		HTML for the plugin
	 *
	 * @access	public
	 */
	function main($content, $conf) {
		$this->init($conf);
		$this->pi_initPIflexForm();

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();

		// include CSS in header of page
		if ($this->hasConfValueString('cssFile', 's_template_special')) {
			$GLOBALS['TSFE']->additionalHeaderData[] = '<style type="text/css">@import "'.$this->getConfValueString('cssFile', 's_template_special', true).'";</style>';
		}

		/** Name of the registrationManager class in case someone subclasses it. */
		$registrationManagerClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationmanager');
		$this->registrationManager =& new $registrationManagerClassname();

		// Let warnings from the registration manager bubble up to us.
		$this->setErrorMessage($this->registrationManager->checkConfiguration(true));

		$result = '';

		// Set the uid of a single event that is requestet (either by the configuration in the
		// flexform or by a parameter in the URL).
		if ($this->hasConfValueInteger('showSingleEvent', 's_template_special')) {
			$this->showUid = $this->getConfValueInteger('showSingleEvent', 's_template_special');
		} else {
			$this->showUid = $this->piVars['showUid'];
		}

		$this->whatToDisplay = $this->getConfValueString('what_to_display');
		$this->setFlavor($this->whatToDisplay);

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
				$result = $this->createRegistrationsListPage();
				break;
			case 'countdown':
				$result = $this->createCountdown();
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
				// The fallthrough is intended.
			default:
				// Show the single view if a 'showUid' variable is set.
				if ($this->showUid) {
					// Intentionally overwrite the previously set flavor.
					$this->setFlavor('single_view');
					$result = $this->createSingleView();
				} else {
					$result = $this->createListView();
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

	/**
	 * Returns the additional query parameters needed to build the list view. This function checks
	 * - the time-frame to display
	 * - whether to show canceled events
	 * The result always starts with " AND" so that it can be directly appended
	 * to a WHERE clause.
	 *
	 * @return	string		the additional query parameters
	 *
	 * @access	protected
	 */
	function getAdditionalQueryParameters() {
		$result = '';
		$now = $GLOBALS['SIM_EXEC_TIME'];
		/** Prefix the column name with the table name so that the query also works with multiple tables. */
		$tablePrefix = $this->tableSeminars.'.';

		// Only show full event records(0) and event dates(2), but no event topics(1).
		$result .= ' AND '.$tablePrefix.'object_type!=1';

		// Work out from which time-frame we'll display the event list.
		// We also need to deal with the case that an event has no end date set
		// (ie. it is open-ended).
		switch ($this->getConfValueString('timeframeInList', 's_template_special')) {
			case 'past':
				// As past events, show the following:
				// 1. Generally, only events that have a begin date set, AND:
				// 2. If the event has an end date, does it lie in the past?, OR
				// 2. If the event has *no* end date, does the *begin* date lie in the past?
				$result .= ' AND '.$tablePrefix.'begin_date!=0 AND (('.$tablePrefix.'end_date!=0 AND '.$tablePrefix.'end_date<='.$now.') OR ('.$tablePrefix.'end_date=0 AND '.$tablePrefix.'begin_date<='.$now.'))';
				break;
			case 'pastAndCurrent':
				// As past and current events, show the following:
				// 1. Generally, only events that have a begin date set, AND
				// 2. the begin date lies in the past.
				// (So events without a begin date won't be listed here.)
				$result .= ' AND '.$tablePrefix.'begin_date!=0 AND '.$tablePrefix.'begin_date<='.$now;
				break;
			case 'current':
				// As current events, show the following:
				// 1. Events that have both a begin and end date, AND
				// 2. The begin date lies in the past, AND
				// 3. The end date lies in the future.
				$result .= ' AND '.$tablePrefix.'begin_date!=0 AND '.$tablePrefix.'begin_date<='.$now.' AND '.$tablePrefix.'end_date!=0 AND '.$tablePrefix.'end_date>'.$now;
				break;
			case 'currentAndUpcoming':
				// As current and upcoming events, show the following:
				// 1. Events with an existing end date in the future, OR
				// 2. Events without an end date, but with an existing begin date in the future
				//    (open-ended events that have not started yet), OR
				// 3. Events that have no (begin) date set yet.
				$result .= ' AND (('.$tablePrefix.'end_date!=0 AND '.$tablePrefix.'end_date>'.$now.') OR ('.$tablePrefix.'end_date=0 AND '.$tablePrefix.'begin_date>'.$now.') OR (begin_date=0))';
				break;
			case 'upcoming':
				// As upcoming events, show the following:
				// 1. Events with an existing begin date in the future
				//    (events that have not started yet), OR
				// 3. Events that have no (begin) date set yet.
				$result .= ' AND ('.$tablePrefix.'begin_date>'.$now.' OR '.$tablePrefix.'begin_date=0)';
				break;
			case 'deadlineNotOver':
				// As events for which the registration deadline is not over yet,
				// show the following:
				// 1. Events that have a deadline set that lies in the future, OR
				// 2. Events that have *no* deadline set, but
				//    with an existing begin date in the future
				//    (events that have not started yet), OR
				// 3. Events that have no (begin) date set yet.
				$result .= ' AND (('.$tablePrefix.'deadline_registration!=0 AND '.$tablePrefix.'deadline_registration>'.$now.') OR ('.$tablePrefix.'deadline_registration=0 AND ('.$tablePrefix.'begin_date>'.$now.' OR '.$tablePrefix.'begin_date=0)))';
				break;
			case 'all':
			default:
				// To show all events, we don't need any additional parameters.
				break;
		}

		// Check if canceled events should be hidden.
		if ($this->getConfValueBoolean('hideCanceledEvents', 's_template_special')) {
			$result .= ' AND '.$tablePrefix.'cancelled=0';
		}

		return $result;
	}

	/**
	 * Returns a label wrapped in <a> tags. The link points to the login page and
	 * contains a redirect parameter that points back to a certain page (must be
	 * provided as a parameter to this function). The user will be redirected to
	 * this page after a successful login.
	 *
	 * If an event uid is provided, the return parameter will contain a showUid
	 * parameter with this UID.
	 *
	 * @param	string		the label to wrap into a link
	 * @param	integer		the PID of the page to redirect to after login (may not be empty)
	 * @param	integer		the UID of the event (may be empty)
	 *
	 * @return	string		the wrapped label
	 */
	function getLoginLink($label, $pageId, $eventId = 0) {
		$redirectUrlParams = array();
		if ($eventId) {
			$redirectUrlParams = array(
				'tx_seminars_pi1[seminar]' => $eventId
			);
		}
		$redirectUrl = $this->cObj->getTypoLink_URL(
			$pageId,
			$redirectUrlParams
		);

		return $this->cObj->getTypoLink(
			$label,
			$this->getConfValueInteger('loginPID'),
			array('redirect_url' => $redirectUrl)
		);
	}

	/**
	 * Creates the HTML for the event list view.
	 * This function is used for the normal event list as well as the
	 * "my events" and the "my VIP events" list.
	 *
	 * @return	string		HTML code with the event list
	 *
	 * @access	protected
	 */
	function createListView() {
		$result = '';
		$isOkay = true;

		switch ($this->whatToDisplay) {
			case 'my_events':
				if ($this->isLoggedIn()) {
					$result .= $this->substituteMarkerArrayCached('MESSAGE_MY_EVENTS');
				} else {
					$this->setMarkerContent('error_text', $this->pi_getLL('message_notLoggedIn'));
					$result .= $this->substituteMarkerArrayCached('ERROR_VIEW');
					$result .= $this->getLoginLink(
						$this->pi_getLL('message_pleaseLogIn'),
						$GLOBALS['TSFE']->id
					);
					$isOkay = false;
				}
				break;
			case 'my_vip_events':
				$result .= $this->substituteMarkerArrayCached('MESSAGE_MY_VIP_EVENTS');
				break;
			case 'my_entered_events':
				$result .= $this->createEventEditor(true);
				if (empty($result)) {
					$result .= $this->substituteMarkerArrayCached('MESSAGE_MY_ENTERED_EVENTS');
				} else {
					$isOkay = false;
				}
				break;
			default:
				break;
		}

		if ($isOkay) {
			$seminarBag =& $this->initListView($this->whatToDisplay);

			if ($this->internal['res_count']) {
				$result = $this->createListTable($seminarBag);
			} else {
				$this->setMarkerContent('error_text', $this->pi_getLL('message_noResults'));
				$result .= $this->substituteMarkerArrayCached('ERROR_VIEW');
			}

			// Show the page browser (if not deactivated in the configuration).
			if (!$this->getConfValueBoolean('hidePageBrowser', 's_template_special')) {
				$result .= $this->pi_list_browseresults();
			}

			// Show the search box (if not deactivated in the configuration).
			if (!$this->getConfValueBoolean('hideSearchForm', 's_template_special')) {
				// The search box is shown even if the list is empty.
				$result .= $this->pi_list_searchBox();
			}

			// Let warnings from the seminar and the seminar bag bubble up to us.
			$this->setErrorMessage($seminarBag->checkConfiguration(true));
		}

		return $result;
	}

	/**
	 * Creates just the table for the list view (without any result browser or
	 * search form).
	 * This function should only be called when there are actually any list
	 * items.
	 *
	 * @param	object		initialized seminar bag (must not be null)
	 *
	 * @return	string		HTML for the table (will not be empty)
	 *
	 * @access	protected
	 */
	function createListTable(&$seminarBag) {
		$result = $this->createListHeader();
		$rowCounter = 0;

		while ($this->seminar =& $seminarBag->getCurrent()) {
			$result .= $this->createListRow($rowCounter);
			$rowCounter++;
			$seminarBag->getNext();
		}

		$result .= $this->createListFooter();

		return $result;
	}

	/**
	 * Initializes the list view (normal list, my events or my VIP events) and
	 * creates a seminar bag, but does not create any actual HTML output.
	 *
	 * @param	string		a string selecting the flavor of list view: either an empty string (for the default list view), the value from "what_to_display" or "other_dates"
	 * @param	string		additional query parameters that will be appended to the WHERE clause
	 *
	 * @return	object		a seminar bag containing the seminars for the list view
	 *
	 * @access	protected
	 */
	function &initListView($whatToDisplay = '', $additionalQueryParameters = '') {
		if (strstr($this->cObj->currentRecord, 'tt_content')) {
			$this->conf['pidList'] = $this->getConfValueString('pages');
			$this->conf['recursive'] = $this->getConfValueInteger('recursive');
		}

		$this->readSubpartsToHide($this->getConfValueString('hideColumns', 's_template_special'), 'LISTHEADER_WRAPPER');
		$this->readSubpartsToHide($this->getConfValueString('hideColumns', 's_template_special'), 'LISTITEM_WRAPPER');

		// Hide the registration column if online registration is disabled,
		// or the "my events" list should be displayed.
		if (!$this->getConfValueBoolean('enableRegistration')
			|| ($whatToDisplay == 'my_events')) {
			$this->readSubpartsToHide('registration', 'LISTHEADER_WRAPPER');
			$this->readSubpartsToHide('registration', 'LISTITEM_WRAPPER');
		}

		// Hide the number of seats and the total price column when we're not
		// on the "my events" list.
		if ($whatToDisplay != 'my_events') {
			$this->readSubpartsToHide('total_price,seats', 'LISTHEADER_WRAPPER');
			$this->readSubpartsToHide('total_price,seats', 'LISTITEM_WRAPPER');
		}

		// Hide the column with the link to the list of registrations if
		// online registration is disabled, no user is logged in or there is
		// no page specified to link to.
		if (!$this->getConfValueBoolean('enableRegistration')
			|| !$this->isLoggedIn()
			|| (($whatToDisplay == 'seminar_list')
				&& !$this->hasConfValueInteger('registrationsListPID')
				&& !$this->hasConfValueInteger('registrationsVipListPID'))
			|| (($whatToDisplay == 'my_events')
				&& !$this->hasConfValueInteger('registrationsListPID'))
			|| (($whatToDisplay == 'my_vip_events')
				&& !$this->hasConfValueInteger('registrationsVipListPID'))) {
			$this->readSubpartsToHide('list_registrations', 'LISTHEADER_WRAPPER');
			$this->readSubpartsToHide('list_registrations', 'LISTITEM_WRAPPER');
		}

		// Hide the edit column if the list to display is not the
		// "events which I have entered" list.
		if ($whatToDisplay != 'my_entered_events') {
			$this->readSubpartsToHide('edit', 'LISTHEADER_WRAPPER');
			$this->readSubpartsToHide('edit', 'LISTITEM_WRAPPER');
		}

		if (!isset($this->piVars['pointer'])) {
			$this->piVars['pointer'] = 0;
		}

		// Read the list view settings from the TS setup and write them to the list view configuration.
		$lConf = (isset($this->conf['listView.'])) ? $this->conf['listView.'] : array();
		if (!empty($lConf)) {
			foreach($lConf as $key => $value) {
				$this->internal[$key] = $value;
			}
		}

		// Overwrite the default sort order with values given by the browser.
		// This happens if the user changes the sort order manually.
		if (!empty($this->piVars['sort'])) {
			list($this->internal['orderBy'], $this->internal['descFlag']) = explode(':', $this->piVars['sort']);
		}

		// Number of results to show in a listing.
		$this->internal['results_at_a_time'] = t3lib_div::intInRange($lConf['results_at_a_time'], 0, 1000, 20);
		// The maximum number of 'pages' in the browse-box: 'Page 1', 'Page 2', etc.
		$this->internal['maxPages'] = t3lib_div::intInRange($lConf['maxPages'], 0, 1000, 2);

		$this->internal['orderByList'] = 'title,uid,event_type,accreditation_number,credit_points,begin_date,price_regular,price_special,organizers';

		$pidList = $this->pi_getPidList($this->getConfValueString('pidList'), $this->getConfValueInteger('recursive'));
		$queryWhere = $this->tableSeminars.'.pid IN ('.$pidList.')';

		// Time-frames and hiding canceled events doesn't make sense for the
		// topic list.
		if ($whatToDisplay != 'topic_list') {
			$queryWhere .= $this->getAdditionalQueryParameters();
		}

		$additionalTables = '';

		switch ($whatToDisplay) {
			case 'topic_list':
				$queryWhere .= ' AND '.$this->tableSeminars.'.object_type='
					.$this->recordTypeTopic;
				$this->readSubpartsToHide(
					'uid,accreditation_number,speakers,date,time,place,'
						.'organizers,vacancies,registration',
					'LISTHEADER_WRAPPER'
				);
				$this->readSubpartsToHide(
					'uid,accreditation_number,speakers,date,time,place,'
						.'organizers,vacancies,registration',
					'LISTITEM_WRAPPER'
				);
				break;
			case 'my_events':
				$additionalTables = $this->tableAttendances;
				$queryWhere .= ' AND '.$this->tableSeminars.'.uid='.$this->tableAttendances.'.seminar'
					.' AND '.$this->tableAttendances.'.user='.$this->registrationManager->getFeUserUid();
				break;
			case 'my_vip_events':
				$isDefaultVip = isset($GLOBALS['TSFE']->fe_user->groupData['uid'][
						$this->getConfValueInteger('defaultEventVipsFeGroupID', 's_template_special')
					]
				);
				if (!$isDefaultVip) {
					// The current user is not listed as a default VIP for all events.
					// Change the query to show only events where the current user is manually
					// added as a VIP.
					$additionalTables = $this->tableVipsMM;
					$queryWhere .= ' AND '.$this->tableSeminars.'.uid='.$this->tableVipsMM.'.uid_local'
						.' AND '.$this->tableVipsMM.'.uid_foreign='.$this->registrationManager->getFeUserUid();
				}
				break;
			case 'my_entered_events':
				$queryWhere .= ' AND '.$this->tableSeminars.'.owner_feuser='.$this->getFeUserUid();
				break;
			case 'events_next_day':
				// Here, we rely on the $additonalQueryParameters parameter
				// because $this->seminar is gone already.
				break;
			case 'other_dates':
				$queryWhere .= $this->seminar->getAdditionalQueryForOtherDates();
				break;
			default:
				break;
		}

		$queryWhere .= $additionalQueryParameters;

		$orderBy = '';
		if (isset($this->internal['orderBy'])
			&& isset($this->orderByList[$this->internal['orderBy']])) {
			$orderBy = $this->orderByList[$this->internal['orderBy']]
				.($this->internal['descFlag'] ? ' DESC' : '');
		}

		$limit = '';
		$pointer = intval($this->piVars['pointer']);
		$resultsAtATime = t3lib_div::intInRange($this->internal['results_at_a_time'], 1, 1000);
		$limit = ($pointer * $resultsAtATime).','.$resultsAtATime;

		if (isset($this->piVars['sword'])
			&& !empty($this->piVars['sword'])) {
			$queryWhere .= $this->searchWhere($this->piVars['sword']);
		}

		$seminarBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminarbag');
		$seminarBag =& new $seminarBagClassname(
			$queryWhere,
			$additionalTables,
			'',
			$orderBy,
			$limit
		);

		$this->internal['res_count'] = $seminarBag->getObjectCountWithoutLimit();

		$this->previousDate = '';

		return $seminarBag;
	}

	/**
	 * Displays detailed data for a seminar.
	 * Fields listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return	string		HTML for the plugin
	 *
	 * @access	protected
	 */
	function createSingleView() {
		$this->internal['currentTable'] = $this->tableSeminars;
		$this->internal['currentRow'] = $this->pi_getRecord($this->tableSeminars, $this->showUid);

		$this->readSubpartsToHide($this->getConfValueString('hideFields', 's_template_special'), 'FIELD_WRAPPER');

		if ($this->createSeminar($this->internal['currentRow']['uid'])) {
			// Let warnings from the seminar bubble up to us.
			$this->setErrorMessage($this->seminar->checkConfiguration(true));

			// This sets the title of the page for use in indexed search results:
			$GLOBALS['TSFE']->indexedDocTitle = $this->seminar->getTitle();

			$this->setMarkerContent('event_type', $this->seminar->getEventType());
			$this->setMarkerContent('title', $this->seminar->getTitle());
			$this->setMarkerContent('uid', $this->seminar->getUid());

			if ($this->seminar->hasSubtitle()) {
				$this->setMarkerContent('subtitle', $this->seminar->getSubtitle());
			} else {
				$this->readSubpartsToHide('subtitle', 'field_wrapper');
			}

			if ($this->seminar->hasDescription()) {
				$this->setMarkerContent('description', $this->seminar->getDescription($this));
			} else {
				$this->readSubpartsToHide('description', 'field_wrapper');
			}

			if ($this->seminar->hasAccreditationNumber()) {
				$this->setMarkerContent('accreditation_number', $this->seminar->getAccreditationNumber());
			} else {
				$this->readSubpartsToHide('accreditation_number', 'field_wrapper');
			}

			if ($this->seminar->hasCreditPoints()) {
				$this->setMarkerContent('credit_points', $this->seminar->getCreditPoints());
			} else {
				$this->readSubpartsToHide('credit_points', 'field_wrapper');
			}

			$this->setMarkerContent('date', $this->seminar->getDate());
			$this->setMarkerContent('time', $this->seminar->getTime());

			if ($this->getConfValueBoolean('showSiteDetails', 's_template_special')) {
				$this->setMarkerContent('place', $this->seminar->getPlaceWithDetails($this));
			} else {
				$this->setMarkerContent('place', $this->seminar->getPlaceShort());
			}

			if ($this->seminar->hasRoom()) {
				$this->setMarkerContent('room', $this->seminar->getRoom());
			} else {
				$this->readSubpartsToHide('room', 'field_wrapper');
			}

			if ($this->seminar->hasAdditionalTimesAndPlaces()) {
				$this->setMarkerContent(
					'additional_times_places',
					$this->seminar->getAdditionalTimesAndPlaces()
				);
			} else {
				$this->readSubpartsToHide('additional_times_places', 'field_wrapper');
			}

			if ($this->seminar->hasSpeakers()) {
				if ($this->getConfValueBoolean('showSpeakerDetails', 's_template_special')) {
					$this->setMarkerContent(
						'speakers',
						$this->seminar->getSpeakersWithDescription($this)
					);
				} else {
					$this->setMarkerContent(
						'speakers',
						$this->seminar->getSpeakersShort()
					);
				}
			} else {
				$this->readSubpartsToHide('speakers', 'field_wrapper');
			}
			if ($this->seminar->hasPartners()) {
				if ($this->getConfValueBoolean('showSpeakerDetails', 's_template_special')) {
					$this->setMarkerContent(
						'partners',
						$this->seminar->getSpeakersWithDescription(
							$this,
							'partners'
						)
					);
				} else {
					$this->setMarkerContent(
						'partners',
						$this->seminar->getSpeakersShort('partners')
					);
				}
			} else {
				$this->readSubpartsToHide('partners', 'field_wrapper');
			}
			if ($this->seminar->hasTutors()) {
				if ($this->getConfValueBoolean('showSpeakerDetails', 's_template_special')) {
					$this->setMarkerContent(
						'tutors',
						$this->seminar->getSpeakersWithDescription(
							$this,
							'tutors'
						)
					);
				} else {
					$this->setMarkerContent(
						'tutors',
						$this->seminar->getSpeakersShort('tutors')
					);
				}
			} else {
				$this->readSubpartsToHide('tutors', 'field_wrapper');
			}
			if ($this->seminar->hasLeaders()) {
				if ($this->getConfValueBoolean('showSpeakerDetails', 's_template_special')) {
					$this->setMarkerContent(
						'leaders',
						$this->seminar->getSpeakersWithDescription(
							$this,
							'leaders'
						)
					);
				} else {
					$this->setMarkerContent(
						'leaders',
						$this->seminar->getSpeakersShort('leaders')
					);
				}
			} else {
				$this->readSubpartsToHide('leaders', 'field_wrapper');
			}

			// set markers for prices
			$this->setPriceMarkers('field_wrapper');

			if ($this->seminar->hasPaymentMethods()) {
				$this->setMarkerContent('paymentmethods', $this->seminar->getPaymentMethods($this));
			} else {
				$this->readSubpartsToHide('paymentmethods', 'field_wrapper');
			}

			if ($this->seminar->hasAdditionalInformation()) {
				$this->setMarkerContent('additional_information', $this->seminar->getAdditionalInformation($this));
			} else {
				$this->readSubpartsToHide('additional_information', 'field_wrapper');
			}

			$this->setMarkerContent('organizers', $this->seminar->getOrganizers($this));

			if ($this->seminar->needsRegistration() && !$this->seminar->isCanceled()) {
				$this->setMarkerContent('vacancies', $this->seminar->getVacanciesString());
			} else {
				$this->readSubpartsToHide('vacancies', 'field_wrapper');
			}

			if ($this->seminar->hasRegistrationDeadline()) {
				$this->setMarkerContent('deadline_registration', $this->seminar->getRegistrationDeadline());
			} else {
				$this->readSubpartsToHide('deadline_registration', 'field_wrapper');
			}

			if ($this->getConfValueBoolean('enableRegistration')) {
				$this->setMarkerContent('registration',
					$this->registrationManager->canRegisterIfLoggedIn($this->seminar) ?
						$this->registrationManager->getLinkToRegistrationOrLoginPage($this, $this->seminar) :
						$this->registrationManager->canRegisterIfLoggedInMessage($this->seminar)
				);
			} else {
				$this->readSubpartsToHide('registration', 'field_wrapper');
			}

			if ($this->seminar->canViewRegistrationsList($this->whatToDisplay,
				$this->getConfValueInteger('registrationsListPID'),
				$this->getConfValueInteger('registrationsVipListPID'))) {
				$this->setMarkerContent('list_registrations', $this->getRegistrationsListLink());
			} else {
				$this->readSubpartsToHide('list_registrations', 'field_wrapper');
			}

			// Hide unneeded sections for topic records.
			if ($this->seminar->getRecordPropertyInteger('object_type') ==
				$this->recordTypeTopic) {
				$this->readSubpartsToHide(
					'accreditation_number,date,time,place,room,speakers,'
						.'organizers,vacancies,deadline_registration,'
						.'registration,list_registrations,eventsnextday',
					'field_wrapper'
				);
			}

			$result = $this->substituteMarkerArrayCached('SINGLE_VIEW');
			// We cache the additional query parameters and the other dates list
			// because the list view will overwrite $this->seminar.
			$nextDayQueryParameters = $this->seminar->getAdditionalQueryForNextDay();
			$otherDatesPart = $this->createOtherDatesList();
			if (!empty($nextDayQueryParameters)) {
				$result .= $this->createEventsOnNextDayList($nextDayQueryParameters);
			}
			$result .= $otherDatesPart;
		} else {
			$this->setMarkerContent('error_text', $this->pi_getLL('message_wrongSeminarNumber'));
			$result = $this->substituteMarkerArrayCached('ERROR_VIEW');
			header('Status: 404 Not Found');
		}

		$this->setMarkerContent(
			'backlink',
			$this->pi_linkTP(
				$this->pi_getLL('label_back', 'Back'),
				array(),
				true,
				$this->getConfValueInteger('listPID')
			)
		);
		$result .= $this->substituteMarkerArrayCached('BACK_VIEW');

		return $result;
	}

	/**
	 * Fills in the matching markers for the prices and hides the unused subparts.
	 *
	 * @param	string		the subpart wrapper prefix
	 *
	 * @access	protected
	 */
	function setPriceMarkers($wrapper) {
		// set the regular price (with or without early bird rebate)
		if ($this->seminar->hasEarlyBirdPrice() && !$this->seminar->isEarlyBirdDeadlineOver()) {
			$this->setMarkerContent('price_earlybird_regular', $this->seminar->getEarlyBirdPriceRegular());
			$this->setMarkerContent('message_earlybird_price_regular', sprintf($this->pi_getLL('message_earlybird_price'),
								$this->seminar->getEarlyBirdDeadline()));
			$this->setMarkerContent('price_regular', $this->seminar->getPriceRegular());
		} else {
			$this->setMarkerContent('price_regular', $this->seminar->getPriceRegular());
			if ($this->getConfValueBoolean('generalPriceInSingle', 's_template_special')) {
				$this->setMarkerContent('label_price_regular', $this->pi_getLL('label_price_general'));
			}
			$this->readSubpartsToHide('price_earlybird_regular', $wrapper);
		}

		// set the special price (with or without early bird rebate)
		if ($this->seminar->hasPriceSpecial()) {
			if ($this->seminar->hasEarlyBirdPrice() && !$this->seminar->isEarlyBirdDeadlineOver()) {
				$this->setMarkerContent('price_earlybird_special', $this->seminar->getEarlyBirdPriceSpecial());
				$this->setMarkerContent('message_earlybird_price_special', sprintf($this->pi_getLL('message_earlybird_price'),
								$this->seminar->getEarlyBirdDeadline()));
				$this->setMarkerContent('price_special', $this->seminar->getPriceSpecial());
			} else {
				$this->setMarkerContent('price_special', $this->seminar->getPriceSpecial());
				$this->readSubpartsToHide('price_earlybird_special', $wrapper);
			}
		} else {
			$this->readSubpartsToHide('price_special', $wrapper);
			$this->readSubpartsToHide('price_earlybird_special', $wrapper);
		}

		// set the regular price (including full board)
		if ($this->seminar->hasPriceRegularBoard()) {
			$this->setMarkerContent(
				'price_board_regular',
				$this->seminar->getPriceRegularBoard()
			);
		} else {
			$this->readSubpartsToHide('price_board_regular', $wrapper);
		}

		// set the special price (including full board)
		if ($this->seminar->hasPriceSpecialBoard()) {
			$this->setMarkerContent(
				'price_board_special',
				$this->seminar->getPriceSpecialBoard()
			);
		} else {
			$this->readSubpartsToHide('price_board_special', $wrapper);
		}

		return;
	}

 	/**
	 * Creates the list of events that start the next day (after the current
	 * event has ended). Practically, this is just a special kind of list view.
	 * In case the current record is a topic record, this function will return
	 * an empty string.
	 *
	 * Note: This function does *not* rely on $this->seminar, but overwrites
	 * $this->seminar.
	 *
	 * @param	string		query parameters that will be appended to the WHERE clause, selecting the correct records
	 *
	 * @return	string		HTML for the events list (may be an empty string)
	 *
	 * @access	protected
	 */
	function createEventsOnNextDayList($additionalQueryParameters) {
		$result = '';

		$seminarBag =& $this->initListView('events_next_day', $additionalQueryParameters);

		if ($this->internal['res_count']) {
			$tableEventsNextDay = $this->createListTable($seminarBag);

			$this->setMarkerContent('table_eventsnextday', $tableEventsNextDay);

			$result = $this->substituteMarkerArrayCached('EVENTSNEXTDAY_VIEW');
		}

		// Let warnings from the seminar and the seminar bag bubble up to us.
		$this->setErrorMessage($seminarBag->checkConfiguration(true));

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
	 * @return	string		HTML for the events list (may be an empty string)
	 *
	 * @access	protected
	 */
	function createOtherDatesList() {
		$result = '';

		$seminarBag =& $this->initListView('other_dates');

		if ($this->internal['res_count']) {
			// If we are on a topic record, overwrite the label with an alternative text.
			if (($this->seminar->getRecordType() == $this->recordTypeComplete)
				|| ($this->seminar->getRecordType() == $this->recordTypeTopic)) {
				$this->setMarkerContent('label_list_otherdates', $this->pi_getLL('label_list_dates'));
			}

			// Hide unneeded columns from the list.
			$temporaryHiddenColumns = 'listheader_wrapper_title,listitem_wrapper_title,'
				.'listheader_wrapper_list_registrations,listitem_wrapper_list_registrations';
			$this->readSubPartsToHide($temporaryHiddenColumns);

			$tableOtherDates = $this->createListTable($seminarBag);

			$this->setMarkerContent('table_otherdates', $tableOtherDates);

			$result = $this->substituteMarkerArrayCached('OTHERDATES_VIEW');

			// Un-hide the previously hidden columns.
			$hiddenColumns = $this->getConfValueString('hideColumns', 's_template_special');
			$this->readSubpartsToUnhide($temporaryHiddenColumns, $hiddenColumns);
		}

		// Let warnings from the seminar and the seminar bag bubble up to us.
		$this->setErrorMessage($seminarBag->checkConfiguration(true));

		// Let's also check the list view configuration..
		$this->checkConfiguration(true, 'seminar_list');

		return $result;
	}

	/**
	 * Creates the link to the list of registrations for the current seminar.
	 * Returns an empty string if this link is not allowed.
	 * For standard lists, a link is created if either the user is a VIP
	 * or is registered for that seminar (with the link to the VIP list taking precedence).
	 *
	 * @return	string		HTML for the link (may be an empty string)
	 *
	 * @access	protected
	 */
	function getRegistrationsListLink() {
		$result = '';
		$targetPageId = 0;

		if ($this->seminar->canViewRegistrationsList(
				$this->whatToDisplay,
				0,
				$this->getConfValueInteger('registrationsVipListPID'),
				$this->getConfValueInteger('defaultEventVipsFeGroupID', 's_template_special'))
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
				$this->pi_getLL('label_listRegistrationsLink'),
				$targetPageId,
				array('tx_seminars_pi1[seminar]' => $this->seminar->getUid())
			);
		}

		return $result;
	}

	/**
	 * Creates a seminar in $this->seminar.
	 * If the seminar cannot be created, $this->seminar will be null, and
	 * this function will return false.
	 *
	 * $this->registrationManager must have been initialized before this
	 * method may be called.
	 *
	 * @param	int			a seminar UID
	 *
	 * @return	boolean		true if the seminar UID is valid and the object has been created, false otherwise
	 *
	 * @access	protected
	 */
	function createSeminar($seminarUid) {
		$result = false;

		if (tx_seminars_objectfromdb::recordExists($seminarUid, $this->tableSeminars)) {
			/** Name of the seminar class in case someone subclasses it. */
			$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
			$this->seminar =& new $seminarClassname($seminarUid);
			$result = true;
		} else {
			$this->seminar = null;
		}

		return $result;
	}

	/**
	 * Returns the list view header: Start of table, header row, start of table body.
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 *
	 * @return	string		HTML output, the table header
	 *
	 * @access	protected
	 */
	function createListHeader() {
		$this->setMarkerContent('header_title', $this->getFieldHeader('title'));
		$this->setMarkerContent('header_subtitle', $this->getFieldHeader('subtitle'));
		$this->setMarkerContent('header_uid', $this->getFieldHeader('uid'));
		$this->setMarkerContent('header_event_type', $this->getFieldHeader('event_type'));
		$this->setMarkerContent('header_accreditation_number', $this->getFieldHeader('accreditation_number'));
		$this->setMarkerContent('header_credit_points', $this->getFieldHeader('credit_points'));
		$this->setMarkerContent('header_speakers', $this->getFieldHeader('speakers'));
		$this->setMarkerContent('header_date', $this->getFieldHeader('date'));
		$this->setMarkerContent('header_time', $this->getFieldHeader('time'));
		$this->setMarkerContent('header_place', $this->getFieldHeader('place'));
		$this->setMarkerContent('header_seats', $this->getFieldHeader('seats'));
		$this->setMarkerContent('header_price_regular', $this->getFieldHeader('price_regular'));
		$this->setMarkerContent('header_price_special', $this->getFieldHeader('price_special'));
		$this->setMarkerContent('header_total_price', $this->getFieldHeader('total_price'));
		$this->setMarkerContent('header_organizers', $this->getFieldHeader('organizers'));
		$this->setMarkerContent('header_vacancies', $this->getFieldHeader('vacancies'));
		$this->setMarkerContent('header_registration', $this->getFieldHeader('registration'));
		$this->setMarkerContent('header_list_registrations', $this->getFieldHeader('list_registrations'));
		$this->setMarkerContent('header_edit', $this->getFieldHeader('edit'));

		return $this->substituteMarkerArrayCached('LIST_HEADER');
	}

	/**
	 * Returns the list view footer: end of table body, end of table.
	 *
	 * @return	string		HTML output, the table footer
	 *
	 * @access	protected
	 */
	function createListFooter() {
		return $this->substituteMarkerArrayCached('LIST_FOOTER');
	}

	/**
	 * Get's data from one or more attendance records that belong to the current event
	 * AND the currently logged in user.
	 * Attention: The fields are totalized! (e.g. all seats from the different attendance
	 * records will be counted and returned as one value)
	 *
	 * @return	array		associative array containing the needed values
	 *
	 * @access	protected
	 */
	function getAttendanceData() {
		$result = array(
			'seats' => '',
			'total_price' => ''
		);

		// Create a registrationbag that contains all registrations for the
		// currently logged in user and the current event.
		$registrationBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationbag');
		$queryWhere = $this->tableAttendances.'.seminar='.$this->seminar->getUid()
					.' AND '.$this->tableAttendances.'.user='.$this->getFeUserUid();
		$registrationBag =& new $registrationBagClassname($queryWhere, '', '', 'crdate');

		if ($registrationBag->getObjectCountWithoutLimit()) {
			$numberOfSeats = 0;
			$totalPrice = 0;
			while ($currentRegistration =& $registrationBag->getCurrent()) {
				$numberOfSeats = $numberOfSeats + $currentRegistration->getSeats();
				$currentTotalPrice = $currentRegistration->getRecordPropertyDecimal('total_price');
				$totalPrice += $currentTotalPrice;
				$registrationBag->getNext();
			}

			// Add the total number of seats and the total price to the result array.
			// If the total price is not zero, format the string as needed and add the currency.
			// But if the total price is zero, set it to an empty string to avoid showing
			// "0" as total price.
			$result['seats'] = $numberOfSeats;
			if ($totalPrice) {
				$currency = $this->registrationManager->getConfValueString('currency');
				$result['total_price'] = $this->seminar->formatPrice($totalPrice).'&nbsp;'.$currency;
			} else {
				$result['total_price'] = '';
			}
		}

		return $result;
	}

	/**
	 * Returns a list row as a TR. Gets data from $this->seminar.
	 * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
	 * If $this->seminar is invalid, an empty string is returned.
	 *
	 * @param	integer		Row counter. Starts at 0 (zero). Used for alternating class values in the output rows.
	 *
	 * @return	string		HTML output, a table row with a class attribute set (alternative based on odd/even rows)
	 *
	 * @access	protected
	 */
	function createListRow($rowCounter = 0) {
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

			$this->setMarkerContent('class_itemrow', $completeClass);

			// Retrieve the data for the columns "Number of seats" and
			// "Total price", but only if we are on the "my_events" list.
			if ($this->whatToDisplay == 'my_events') {
				$attendanceData = $this->getAttendanceData();
			} else {
				$attendanceData = array(
					'seats' => '',
					'total_price' => ''
				);
			}

			$this->setMarkerContent('title_link', $this->seminar->getLinkedFieldValue($this, 'title'));
			$this->setMarkerContent('subtitle', $this->seminar->getSubtitle());
			$this->setMarkerContent('uid', $this->seminar->getUid($this));
			$this->setMarkerContent('event_type', $this->seminar->getEventType());
			$this->setMarkerContent('accreditation_number', $this->seminar->getAccreditationNumber());
			$this->setMarkerContent('credit_points', $this->seminar->getCreditPoints());
			$this->setMarkerContent('teaser', $this->seminar->getTeaser());
			$this->setMarkerContent('speakers', $this->seminar->getSpeakersShort());

			$currentDate = $this->seminar->getLinkedFieldValue($this, 'date');
			if (($currentDate === $this->previousDate)
				&& $this->getConfValueBoolean('omitDateIfSameAsPrevious', 's_template_special')) {
				$currentDate = '';
			} else {
				$this->previousDate = $currentDate;
			}
			$this->setMarkerContent('date', $currentDate);

			$this->setMarkerContent('time', $this->seminar->getTime());
			$this->setMarkerContent('place', $this->seminar->getPlaceShort());
			$this->setMarkerContent('seats', $attendanceData['seats']);
			$this->setMarkerContent('price_regular', $this->seminar->getCurrentPriceRegular());
			$this->setMarkerContent('price_special', $this->seminar->getCurrentPriceSpecial());
			$this->setMarkerContent('total_price', $attendanceData['total_price']);
			$this->setMarkerContent('organizers', $this->seminar->getOrganizers($this));
			$this->setMarkerContent('vacancies', $this->seminar->getVacanciesString());
			$this->setMarkerContent('class_listvacancies', $this->getVacanciesClasses($this->seminar));
			$this->setMarkerContent('registration', $this->registrationManager->canRegisterIfLoggedIn($this->seminar) ?
				$this->registrationManager->getLinkToRegistrationOrLoginPage($this, $this->seminar) : ''
			);
			$this->setMarkerContent('list_registrations', $this->getRegistrationsListLink());
			$this->setMarkerContent('edit', $this->getEditLink());

			$result = $this->substituteMarkerArrayCached('LIST_ITEM');
		}

		return $result;
	}

	/**
	 * Gets the heading for a field type, automatically wrapped in a hyperlink
	 * that sorts by that column if sorting by that column is available.
	 *
	 * @param	string		key of the field type for which the heading should be retrieved.
	 *
	 * @return	string		the heading label (may be completely wrapped in a hyperlink for sorting)
	 *
	 * @access	protected
	 */
	function getFieldHeader($fieldName) {
		$result = '';

		$label = $result = $this->pi_getLL('label_'.$fieldName, '['.$fieldName.']');
		if (($fieldName == 'price_regular')
			&& $this->getConfValueBoolean('generalPriceInList', 's_template_special')) {
			$label = $result = $this->pi_getLL('label_price_general');
		}

		// Can we sort by that field?
		if (isset($this->orderByList[$fieldName])) {
			$result = $this->pi_linkTP_keepPIvars(
				$label,
				array('sort' => $fieldName.':'.($this->internal['descFlag'] ? 0 : 1))
			);
		} else {
			$result = $label;
		}

		return $result;
	}

	/**
	 * Gets the CSS classes (space-separated) for the Vacancies TD.
	 *
	 * @param	object		the current Seminar object
	 *
	 * @return	string		class attribute filled with a list a space-separated CSS classes, plus a leading space
	 *
	 * @access	protected
	 */
	function getVacanciesClasses(&$seminar) {
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
	 * Creates the HTML for the registration page.
	 *
	 * @return	string		HTML code for the registration page
	 *
	 * @acces	protected
	 */
	function createRegistrationPage() {
		$this->feuser = $GLOBALS['TSFE']->fe_user;

		$errorMessage = '';
		$registrationForm = '';
		$registationThankyou = '';
		$isOkay = false;

		$this->toggleEventFieldsOnRegistrationPage();

		if ($this->createSeminar($this->piVars['seminar'])) {
			// Let warnings from the seminar bubble up to us.
			$this->setErrorMessage($this->seminar->checkConfiguration(true));

			if (!$this->registrationManager->canRegisterIfLoggedIn($this->seminar)) {
				$errorMessage = $this->registrationManager->canRegisterIfLoggedInMessage($this->seminar);
			} else {
				if ($this->isLoggedIn()) {
					$isOkay = true;
				} else {
					$errorMessage = $this->getLoginLink(
						$this->pi_getLL('message_notLoggedIn'),
						$GLOBALS['TSFE']->id,
						$this->seminar->getUid()
					);
				}
			}
		} else {
			$errorMessage = $this->registrationManager->existsSeminarMessage($this->piVars['seminar']);
		}

		if ($isOkay) {
			$registrationForm = $this->createRegistrationForm();
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
	 * @param	string	error message to be displayed (may be empty if there is no error)
	 *
	 * @return	string	HTML code including the title and error message
	 *
	 * @access	protected
	 */
	function createRegistrationHeading($errorMessage) {
		$this->setMarkerContent('registration', $this->pi_getLL('label_registration'));
		$this->setMarkerContent('event_type',	($this->seminar) ? $this->seminar->getEventType() : '');
		$this->setMarkerContent('title',        ($this->seminar) ? $this->seminar->getTitleAndDate() : '');
		$this->setMarkerContent('uid',          ($this->seminar) ? $this->seminar->getUid() : '');

		if ($this->seminar && $this->seminar->hasAccreditationNumber()) {
			$this->setMarkerContent('accreditation_number', ($this->seminar) ? $this->seminar->getAccreditationNumber() : '');
		} else {
			$this->readSubpartsToHide('accreditation_number', 'registration_wrapper');
		}

		if (empty($errorMessage)) {
			$this->readSubpartsToHide('error', 'wrapper');
		} else {
			$this->setMarkerContent('error_text', $errorMessage);
		}

		return $this->substituteMarkerArrayCached('REGISTRATION_HEAD');
	}

	/**
	 * Creates the registration form.
	 *
	 * @return	string		HTML code for the form
	 *
	 * @access	protected
	 */
	function createRegistrationForm() {
		// set the markers for the prices
		$this->setPriceMarkers('registration_wrapper');

		$this->setMarkerContent('vacancies', $this->seminar->getVacancies());
		$output = $this->substituteMarkerArrayCached('REGISTRATION_DETAILS');

		$registrationEditorClassname = t3lib_div::makeInstanceClassName('tx_seminars_registration_editor');
		$registrationEditor =& new $registrationEditorClassname($this);

		$output .= $registrationEditor->_render();
		$output .= $this->substituteMarkerArrayCached('REGISTRATION_BOTTOM');

		return $output;
	}

	/**
	 * Creates a list of registered participants for an event.
	 * If there are no registrations yet, a localized message is displayed instead.
	 *
	 * @return	string		HTML code for the list
	 *
	 * @access	protected
	 */
	function createRegistrationsListPage() {
		$errorMessage = '';
		$isOkay = false;

		if ($this->createSeminar($this->piVars['seminar'])) {
			// Okay, at least the seminar UID is valid so we can show the seminar title and date.
			$this->setMarkerContent('title', $this->seminar->getTitleAndDate());

			// Let warnings from the seminar bubble up to us.
			$this->setErrorMessage($this->seminar->checkConfiguration(true));

			if ($this->seminar->canViewRegistrationsList(
					$this->whatToDisplay,
					0,
					0,
					$this->getConfValueInteger('defaultEventVipsFeGroupID', 's_template_special'))
				) {
				$isOkay = true;
			} else {
				$errorMessage = $this->seminar->canViewRegistrationsListMessage($this->whatToDisplay);
			}
		} else {
			$errorMessage = $this->registrationManager->existsSeminarMessage($this->piVars['seminar']);
			$this->setMarkerContent('title', '');
			header('Status: 404 Not Found');
		}

		if ($isOkay) {
			$this->readSubpartsToHide('error', 'wrapper');
			$this->createRegistrationsList();
		} else {
			$this->setMarkerContent('error_text', $errorMessage);
			$this->readSubpartsToHide('registrations_list_message', 'wrapper');
			$this->readSubpartsToHide('registrations_list_body', 'wrapper');
		}

		$this->setMarkerContent('backlink',
			$this->cObj->getTypoLink(
				$this->pi_getLL('label_back'),
				$this->getConfValueInteger('listPID')
			)
		);

		$result = $this->substituteMarkerArrayCached('REGISTRATIONS_LIST_VIEW');

		return $result;
	}

	/**
	 * Creates the registration list (sorted by creation date) and fills in the
	 * corresponding subparts.
	 * If there are no registrations, a localized message is filled in instead.
	 *
	 * Before this function can be called, it must be ensured that $this->seminar
	 * is a valid seminar object.
	 *
	 * @access	protected
	 */
	function createRegistrationsList() {
		$registrationBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationbag');
		$registrationBag =& new $registrationBagClassname($this->tableAttendances.'.seminar='.$this->seminar->getUid(), '', '', 'crdate');

		if ($registrationBag->getCurrent()) {
			$result = '';
			while ($currentRegistration =& $registrationBag->getCurrent()) {
				$this->setMarkerContent('registrations_list_inneritem',
					$currentRegistration->getUserDataAsHtml(
						$this->getConfValueString('showFeUserFieldsInRegistrationsList', 's_template_special'),
						$this
					)
				);
				$result .= $this->substituteMarkerArrayCached('REGISTRATIONS_LIST_ITEM');
				$registrationBag->getNext();
			}
			$this->readSubpartsToHide('registrations_list_message', 'wrapper');
			$this->setMarkerContent('registrations_list_body', $result);
		} else {
			$this->readSubpartsToHide('registrations_list_body', 'wrapper');
			$this->setMarkerContent('message_no_registrations', $this->pi_getLL('message_noRegistrations'));
		}

		// Let warnings from the registration bag bubble up to us.
		$this->setErrorMessage($registrationBag->checkConfiguration(true));

		return;
	}

	/**
	 * Enables/disables the display of data from event records on the
	 * registration page depending on the config variable
	 * "eventFieldsOnRegistrationPage".
	 *
	 * @access	protected
	 */
	function toggleEventFieldsOnRegistrationPage() {
		$fieldsToShow = array();
		if ($this->hasConfValueString('eventFieldsOnRegistrationPage', 's_template_special')) {
			$fieldsToShow = explode(',', $this->getConfValueString('eventFieldsOnRegistrationPage', 's_template_special'));
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
			$this->readSubpartsToHide(implode(',', $fieldsToRemove), 'registration_wrapper');
		}

		return;
	}

	/**
	 * Generates a search WHERE clause based on the input search words
	 * (AND operation - all search words must be found in record.)
	 * The result will be in conjunctive normal form.
	 *
	 * Example: The $searchWords is "content management, system" (from an input
	 * form) and the search field list is "bodytext,header" then the output
	 * will be ' AND (bodytext LIKE "%content%" OR header LIKE "%content%")
	 * AND (bodytext LIKE "%management%" OR header LIKE "%management%")
	 * AND (bodytext LIKE "%system%" OR header LIKE "%system%")'
	 *
	 * @param	string		the search words, separated by spaces or commas
	 *
	 * @return	string		the WHERE clause (including the AND at the beginning)
	 *
	 * @access	protected
	 */
	function searchWhere($searchWords)	{
		$result = '';

		$mmTables = array(
			'speakers' => $this->tableSpeakersMM,
			'partners' => $this->tablePartnersMM,
			'tutors' => $this->tableTutorsMM,
			'leaders' => $this->tableLeadersMM
		);

		if (!empty($searchWords)) {
			$keywords = split('[ ,]', $searchWords);

			foreach ($keywords as $currentKeyword) {
				$currentPreparedKeyword = $this->escapeAndTrimSearchWord(
					$currentKeyword,
					$this->tableSeminars
				);

				// Only search for words with a certain length.
				if (strlen($currentPreparedKeyword) >= 2) {
					$whereParts = array();

					// Look up the field in the seminar record.
					foreach ($this->searchFieldList['seminars'] as $field) {
						$whereParts[] = $this->tableSeminars.'.'.$field
							.' LIKE \'%'.$currentPreparedKeyword.'%\'';
					}

					// When this is a date record,
					// look up the field in the corresponding topic record,
					// otherwise get it directly.
					foreach ($this->searchFieldList['seminars_topic'] as $field) {
						$whereParts[] = 'EXISTS ('
							.'SELECT * FROM '.$this->tableSeminars.' s1,'
								.$this->tableSeminars.' s2'
								.' WHERE (s1.'.$field.' LIKE \'%'.$currentPreparedKeyword.'%\''
								.' AND ((s1.uid=s2.topic AND s2.object_type=2) '
								.' OR (s1.uid=s2.uid AND s1.object_type!=2)))'
								.' AND s2.uid='.$this->tableSeminars.'.uid'
						.')';
					}

					// For speakers (and their variants partners, tutors and
					// leaders), we have real m:n relations.
					foreach ($mmTables as $key => $currentMmTable) {
						foreach ($this->searchFieldList[$key] as $field) {
							$whereParts[] = 'EXISTS ('
								.'SELECT * FROM '.$this->tableSpeakers.', '
										.$currentMmTable
									.' WHERE '.$this->tableSpeakers.'.'.$field
										.' LIKE \'%'.$currentPreparedKeyword.'%\''
									.' AND '.$currentMmTable.'.uid_local='
										.$this->tableSeminars.'.uid '
									.'AND '.$currentMmTable.'.uid_foreign='
										.$this->tableSpeakers.'.uid'
							.')';
						}
					}

					// For sites, we have real m:n relations, too.
					foreach ($this->searchFieldList['places'] as $field) {
						$whereParts[] = 'EXISTS ('
							.'SELECT * FROM '.$this->tableSites.', '.$this->tableSitesMM
								.' WHERE '.$this->tableSites.'.'.$field
									.' LIKE \'%'.$currentPreparedKeyword.'%\''
								.' AND '.$this->tableSitesMM.'.uid_local='
									.$this->tableSeminars.'.uid '
								.'AND '.$this->tableSitesMM.'.uid_foreign='
									.$this->tableSites.'.uid'
						.')';
					}

					// Will the default event type match a search?
					// NB: We ask the registration manager for its configuration
					// as we need the configuration from plugin.tx_seminars
					// (which the registration manager uses),
					// not from plugin.tx_seminars_pi1 (which we use).
					$eventTypeMatcher = '';
					if (stristr(
						$this->registrationManager->getConfValueString('eventType'),
						$currentPreparedKeyword
					) !== false) {
						$eventTypeMatcher = ' OR ('.$this->tableSeminars.'.event_type=0 '
											.' AND '.$this->tableSeminars.'.object_type!=2)'
											.' OR (s1.event_type=0 AND s1.uid=s2.topic '
											.' AND s2.object_type=2 AND s2.uid='.$this->tableSeminars.'.uid)';
					}

					// For event types, we have a single foreign key.
					foreach ($this->searchFieldList['event_types'] as $field) {
						$whereParts[] = 'EXISTS ('
							.'SELECT * FROM '.$this->tableEventTypes.', '.$this->tableSeminars.' s1, '.$this->tableSeminars.' s2'
								.' WHERE ('.$this->tableEventTypes.'.'.$field.' LIKE \'%'.$currentPreparedKeyword.'%\''
								.' AND '.$this->tableEventTypes.'.uid=s1.event_type'
								.' AND ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type!=2))'
								.' AND s2.uid='.$this->tableSeminars.'.uid)'
								.$eventTypeMatcher
						.')';
					}

					// For organizers, we have a comma-separated list of UIDs.
					foreach ($this->searchFieldList['organizers'] as $field) {
						$whereParts[] = 'EXISTS ('
							.'SELECT * FROM '.$this->tableOrganizers
								.' WHERE '.$this->tableOrganizers.'.'.$field
									.' LIKE \'%'.$currentPreparedKeyword.'%\''
								.' AND FIND_IN_SET('.$this->tableOrganizers.'.uid,'
									.$this->tableSeminars.'.organizers)'
						.')';
					}

					if (count($whereParts) > 0)	{
						$result .= ' AND ('.implode(' OR ', $whereParts).')';
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Convenience function. SQL-escaped and trims a potential search word.
	 *
	 * @param	string		single search word (may be prefixed or postfixed with spaces)
	 * @param	string		name of the SQL table in which the search word will be used
	 *
	 * @return	string		the trimmed and SQL-escaped $searchword
	 *
	 * @access	public
	 */
	function escapeAndTrimSearchWord($searchword, $tableName) {
		$result = '';

		if (method_exists($GLOBALS['TYPO3_DB'], 'escapeStrForLike')) {
			$result = $GLOBALS['TYPO3_DB']->escapeStrForLike(
				$GLOBALS['TYPO3_DB']->quoteStr(
					trim($searchword),
					$tableName
				),
				$tableName
			);
		} else {
			// xxx: This workaround can be removed once we require TYPO3 >= 4.0,
			// dropping support for TYPO3 3.8.x.
			$result = preg_replace('/[_%]/', '\\\$0',
				$GLOBALS['TYPO3_DB']->quoteStr(
					trim($searchword),
					$tableName
				)
			);
		}

		return $result;
	}

	/**
	 * Checks whether logged-in FE user has access to the event editor and then
	 * either creates the event editor HTML (or an empty string if
	 * $accessTestOnly is true) or a localized error message.
	 *
	 * @param	boolean		whether only the access to the event editor should be checked
	 *
	 * @return	string		HTML code for the event editor (or an error message if the FE user doesn't have access to the editor)
	 *
	 * @access	protected
	 */
	function createEventEditor($accessTestOnly = false) {
		$result = '';

		$eventEditorClassname = t3lib_div::makeInstanceClassName('tx_seminars_event_editor');
		$eventEditor =& new $eventEditorClassname($this);

		if ($eventEditor->hasAccess()) {
			if (!$accessTestOnly) {
				$result = $eventEditor->_render();
			}
		} else {
			$result = $eventEditor->hasAccessMessage();
		}

		return $result;
	}

	/**
	 * Creates the link to the event editor for the current event.
	 * Returns an empty string if editing this event is not allowed.
	 *
	 * A link is created if the logged-in FE user is the owner of the event.
	 *
	 * @return	string		HTML for the link (may be an empty string)
	 *
	 * @access	protected
	 */
	function getEditLink() {
		$result = '';

		if ($this->seminar->isOwnerFeUser()) {
			$result = $this->cObj->getTypoLink(
				$this->pi_getLL('label_edit'),
				$this->getConfValueInteger('eventEditorPID', 's_fe_editing'),
				array(
					'tx_seminars_pi1[seminar]' => $this->seminar->getUid(),
					'tx_seminars_pi1[action]' => 'EDIT'
				)
			);
		}

		return $result;
	}

	/**
	 * Creates a countdown to the next upcoming event.
	 *
	 * @return	string		HTML code of the countdown or a message if no upcoming event found
	 *
	 * @access	protected
	 */
	function createCountdown() {
		$message = '';
		$now = time();

		// define the additional where clause for the database query
		$additionalWhere = 'tx_seminars_seminars.cancelled=0'
			.$this->enableFields($this->tableSeminars)
			.' AND tx_seminars_seminars.object_type!='.$this->recordTypeTopic
			.' AND tx_seminars_seminars.begin_date>'.$now;

		// query the database
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid',
			$this->tableSeminars,
			$additionalWhere,
			'',
			'begin_date ASC',
			'1'
		);

		if ($dbResult) {
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				if ($this->createSeminar($row['uid'])) {
					// Let warnings from the seminar bubble up to us.
					$this->setErrorMessage($this->seminar->checkConfiguration(true));

					// calculate the time left until the event starts
					$eventStartTime = $this->seminar->getRecordPropertyInteger('begin_date');
					$timeLeft = $eventStartTime - $now;

					$message = $this->createCountdownMessage($timeLeft);
				}
			} else {
				// no event found - show a message
				$message = $this->pi_getLL('message_countdown_noEventFound');
			}
		}

		$this->setMarkerContent('count_down_message', $message);
		$result = $this->substituteMarkerArrayCached('COUNTDOWN');

		return $result;
	}

	/**
	 * Returns a localized string representing an amount of seconds in words.
	 * For example:
	 * 150000 seconds -> "1 day"
	 * 200000 seconds -> "2 days"
	 * 50000 seconds -> "13 hours"
	 * The function uses localized strings and also looks for proper usage of
	 * singular/plural.
	 *
	 * @param	integer		the amount of seconds to rewrite into words
	 *
	 * @return	string		a localized string representing the time left until the event starts
	 *
	 * @access	protected
	 */
	function createCountdownMessage($seconds) {
		if ($seconds > 82800) {
			// more than 23 hours left, show the time in days
			$countdownValue = round($seconds / ONE_DAY);
			if ($countdownValue > 1) {
				$countdownText = $this->pi_getLL('countdown_days_plural');
			} else {
				$countdownText = $this->pi_getLL('countdown_days_singular');
			}
		} elseif ($seconds > 3540) {
			// more than 59 minutes left, show the time in hours
			$countdownValue = round($seconds / 3600);
			if ($countdownValue > 1) {
				$countdownText = $this->pi_getLL('countdown_hours_plural');
			} else {
				$countdownText = $this->pi_getLL('countdown_hours_singular');				}
		} elseif ($seconds > 59) {
			// more than 59 seconds left, show the time in minutes
			$countdownValue = round($seconds / 60);
			if ($countdownValue > 1) {
				$countdownText = $this->pi_getLL('countdown_minutes_plural');
			} else {
				$countdownText = $this->pi_getLL('countdown_minutes_singular');
			}
		} else {
			// less than 60 seconds left, show the time in seconds
			$countdownValue = $seconds;
			$countdownText = $this->pi_getLL('countdown_seconds_plural');
		}

		return sprintf(
			$this->pi_getLL('message_countdown'),
			$countdownValue,
			$countdownText
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1.php']);
}

?>
