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
 * Class 'tx_seminars_seminar' for the 'seminars' extension.
 *
 * This class represents a seminar (or similar event).
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationmanager.php');

class tx_seminars_seminar extends tx_seminars_dbplugin {
	/** Same as class name */
	var $prefixId = 'tx_seminars_seminar';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_seminar.php';

	/** the seminar data as an array, initialized on construction */
	var $seminarData = null;

	/** Organizers data as an array of arrays with their UID as key. Lazily initialized. */
	var $organizersCache = array();

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager = null;

	/**
	 * The constructor. Creates a seminar instance from a DB record.
	 *
	 * @param	object		An instance of a registrationManager.
	 * @param	integer		The UID of the seminar to retrieve from the DB.
	 * 						This parameter will be ignored if $dbResult is provided.
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 						If this parameter is provided, $uid will be ignored.
	 *
	 * @return	boolean		true if the seminar has been properly initialized, false otherwise
	 *
	 * @access public
	 */
	function tx_seminars_seminar(&$registrationManager, $seminarUid, $dbResult = null) {
		$result = false;
		$this->init();
		$this->registrationManager =& $registrationManager;

		if (!$dbResult) {
			$dbResult = $this->retrieveSeminar($seminarUid);
		}

	 	if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
	 		$this->seminarData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
	 		$result = true;
	 	}

		return $result;
	}

	/**
	 * Checks whether a non-deleted and non-hidden seminar with a given UID exists in the DB.
	 *
	 * This method may be called statically.
	 *
	 * @param	String		String with a UID (need not necessarily be escaped, will be intval'ed)
	 *
	 * @return	boolean		true if a visible seminar with that UID exists; false otherwise.
	 *
	 * @access public
	 */
	function existsSeminar($seminarUid) {
		$result = is_numeric($seminarUid) && ($seminarUid);

		if ($result) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*) AS num',
				$this->tableSeminars,
				'uid='.intval($seminarUid)
					.t3lib_pageSelect::enableFields($this->tableSeminars),
				'',
				'',
				'');

			if ($dbResult) {
				$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$result = ($dbResultAssoc['num'] == 1);
			} else {
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Retrieves a seminar from the database.
	 *
	 * @param	integer		The UID of the seminar to retrieve from the DB.
	 *
	 * @return	pointer		MySQL result pointer (of SELECT query)/DBAL object, null if the UID is invalid
	 *
	 * @access private
	 */
	 function retrieveSeminar($seminarUid) {
	 	if ($this->existsSeminar($seminarUid)) {
		 	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$this->tableSeminars,
				'uid='.intval($seminarUid)
					.t3lib_pageSelect::enableFields($this->tableSeminars),
				'',
				'',
				'1');
	 	} else {
	 		$result = null;
	 	}

		return $result;
	 }

	/**
	 * Gets our UID.
	 *
	 * @return	integer		our UID (or 0 if there is an error)
	 *
	 * @access public
	 */
	function getUid() {
		return $this->getSeminarsPropertyInteger('uid');
	}

	/**
	 * Gets the event type (seminar, workshop, lecture ...).
	 *
	 * @return	String	the seminar type (may be empty)
	 *
	 * @access public
	 */
	function getType() {
		return $this->conf['eventType'];
	}

	/**
	 * Checks whether the seminar has an event type set
	 *
	 * @return	boolean		true if we have a type, false otherwise.
	 *
	 * @access public
	 */
	function hasType() {
		return ($this->getType() !== '');
	}

	/**
	 * Gets our title.
	 *
	 * @return	String	our seminar title (or '' if there is an error)
	 *
	 * @access public
	 */
	function getTitle() {
		return $this->getSeminarsPropertyString('title');
	}

	/**
	 * Creates a hyperlink link to this seminar details page.
	 *
	 * If $this->conf['listPID'] (and the corresponding flexforms value) is not set or 0,
	 * the link will use the current page's PID.
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page) which we can call pi_list_linkSingle() on (must not be null)
	 *
	 * @return	string	HTML code for the link to the seminar details page
	 *
	 * @access public
	 */
	function getLinkedTitle(&$plugin) {
		return $plugin->pi_list_linkSingle(
			$this->getTitle(),
			$this->getUid(),
			0,
			array(),
			false,
			$plugin->getConfValue('listPID')
		);
	}

	/**
	 * Gets our subtitle.
	 *
	 * @return	String		our seminar subtitle (or '' if there is an error)
	 *
	 * @access public
	 */
	function getSubtitle() {
		return $this->getSeminarsPropertyString('subtitle');
	}

	/**
	 * Checks whether we have a subtitle.
	 *
	 * @return	boolean		true if we have a non-empty subtitle, false otherwise.
	 *
	 * @access public
	 */
	function hasSubtitle() {
		return ($this->getSubtitle() !== '');
	}

	/**
	 * Gets our description, complete as RTE'ed HTML.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	String		our seminar description (or '' if there is an error)
	 *
	 * @access public
	 */
	function getDescription(&$plugin) {
		return $plugin->pi_RTEcssText($this->getSeminarsPropertyString('description'));
	}

	/**
	 * Checks whether we have a description.
	 *
	 * @return	boolean		true if we have a non-empty description, false otherwise.
	 *
	 * @access public
	 */
	function hasDescription() {
		return ($this->getSeminarsPropertyString('description') !== '');
	}

	/**
	 * Gets the unique seminar title, constiting of the seminar title and the date
	 * (comma-separated).
	 *
	 * If the seminar has no date, just the title is returned.
	 *
	 * @param	String		the character or HTML entity used to separate start date and end date
	 *
	 * @return	String		the unique seminar title (or '' if there is an error)
	 *
	 * @access public
	 */
	function getTitleAndDate($dash = '-') {
		$date = $this->hasDate() ? ', '.$this->getDate($dash) : '';

		return $this->getTitle().$date;
	}

	/**
	 * Gets the seminar date.
	 * Returns a localized string "will be announced" if the seminar has no date set.
	 *
	 * Returns just one day if the seminar takes place on only one day.
	 * Returns a date range if the seminar takes several days.
	 *
	 * @param	String		the character or HTML entity used to separate start date and end date
	 *
	 * @return	String		the seminar date
	 *
	 * @access public
	 */
	function getDate($dash = '-') {
		if (!$this->hasDate()) {
			$result = $this->pi_getLL('message_willBeAnnounced');
		} else {
			$beginDate = $this->getSeminarsPropertyInteger('begin_date');
			$endDate = $this->getSeminarsPropertyInteger('end_date');

			$beginDateDay = strftime($this->conf['dateFormatYMD'], $beginDate);
			$endDateDay = strftime($this->conf['dateFormatYMD'], $endDate);

			// Does the workshop span several days?
			if ($beginDateDay == $endDateDay) {
				$result = $beginDateDay;
			} else {
				if (!$this->conf['abbreviateDateRanges']) {
					$result = $beginDateDay;
				} else {
					// Are the years different? Then include the complete begin date.
					if (strftime($this->conf['dateFormatY'], $beginDate) !== strftime($this->conf['dateFormatY'], $endDate)) {
						$result = $beginDateDay;
					} else {
						// Are the months different? Then include day and month.
						if (strftime($this->conf['dateFormatM'], $beginDate) !== strftime($this->conf['dateFormatM'], $endDate)) {
							$result = strftime($this->conf['dateFormatMD'], $beginDate);
						} else {
							$result = strftime($this->conf['dateFormatD'], $beginDate);
						}
					}
				}
				$result .= $dash.$endDateDay;
			}
		}

		return $result;
	}

	/**
	 * Checks whether the seminar has a date set (a begin date and an end date)
	 *
	 * @return	boolean		true if we have a date, false otherwise.
	 *
	 * @access public
	 */
	function hasDate() {
		return ($this->getSeminarsPropertyInteger('begin_date') && $this->getSeminarsPropertyInteger('end_date'));
	}

	/**
	 * Gets the seminar time.
	 * Returns a localized string "will be announced" if the seminar has no time set
	 * (i.e. both begin time and end time are 00:00).
	 * Returns only the begin time if begin time and end time are the same.
	 *
	 * @param	String		the character or HTML entity used to separate begin time and end time
	 *
	 * @return	String		the seminar time
	 *
	 * @access public
	 */
	function getTime($dash = '-') {
		if (!$this->hasTime()) {
			$result = $this->pi_getLL('message_willBeAnnounced');
		} else {
			$beginDate = $this->getSeminarsPropertyInteger('begin_date');
			$endDate = $this->getSeminarsPropertyInteger('end_date');

			$beginTime = strftime($this->conf['timeFormat'], $beginDate);
			$endTime = strftime($this->conf['timeFormat'], $endDate);

			$result = $beginTime;

			if ($beginTime !== $endTime) {
				$result .= $dash.$endTime;
			}
		}

		return $result;
	}

	/**
	 * Checks whether the seminar has a time set (begin time or end time != 00:00)
	 *
	 * @return	boolean		true if we have a time, false otherwise.
	 *
	 * @access public
	 */
	function hasTime() {
		$beginTime = strftime('%H:%M', $this->getSeminarsPropertyInteger('begin_date'));
		$endTime = strftime('%H:%M', $this->getSeminarsPropertyInteger('end_date'));

		return ($beginTime !== '00:00' || $endTime !== '00:00');
	}

	/**
	 * Gets our place (or places), complete as RTE'ed HTML with address and links.
	 * Returns a localized string "will be announced" if the seminar has no places set.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	String		our places description (or '' if there is an error)
	 *
	 * @access public
	 */
	function getPlace(&$plugin) {
		$result = '';

		if ($this->hasPlace()) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title, address, homepage, directions',
				$this->tableSites.', '.$this->tableSitesMM,
				'uid_local='.$this->getUid().' AND uid=uid_foreign'
					.t3lib_pageSelect::enableFields($this->tableSites),
				'',
				'',
				''
			);

			if ($dbResult) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					if (!empty($result)) {
						$result .= chr(10);
					}

					$name = $row['title'];
					if (!empty($row['homepage'])) {
						$name = $plugin->cObj->getTypoLink($name, $row['homepage']);
					}
					$result .= $name;
					if (!empty($row['address'])) {
						$result .= '<br />'.$row['address'];
					}
					if (!empty($row['directions'])) {
						$result .= '<br />'.$row['directions'];
					}
				}
			}
		} else {
			$result = $this->pi_getLL('message_willBeAnnounced');
		}

		return $plugin->pi_RTEcssText($result);
	}

	/**
	 * Gets our place (or places) as a plain test list (just the place names).
	 * Returns a localized string "will be announced" if the seminar has no places set.
	 *
	 * @return	String		our places list (or '' if there is an error)
	 *
	 * @access public
	 */
	function getPlaceShort() {
		$result = '';

		if ($this->hasPlace()) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				$this->tableSites.', '.$this->tableSitesMM,
				'uid_local='.$this->getUid().' AND uid=uid_foreign'
					.t3lib_pageSelect::enableFields($this->tableSites),
				'',
				'',
				''
			);

			if ($dbResult) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					if (!empty($result)) {
						$result .= ', ';
					}

					$result .= $row['title'];
				}
			}
		} else {
			$result = $this->pi_getLL('message_willBeAnnounced');
		}

		return $result;
	}

	/**
	 * Checks whether we have a place (or places) set.
	 *
	 * @return	boolean		true if we have a non-empty places list, false otherwise.
	 *
	 * @access public
	 */
	function hasPlace() {
		return (boolean) $this->getSeminarsPropertyInteger('place');
	}

	/**
	 * Gets the seminar room (not the site).
	 *
	 * @return	String		the seminar room (may be empty)
	 *
	 * @access public
	 */
	function getRoom() {
		return $this->getSeminarsPropertyString('room');
	}

	/**
	 * Checks whether we have a room set.
	 *
	 * @return	boolean		true if we have a non-empty room, false otherwise.
	 *
	 * @access public
	 */
	function hasRoom() {
		return ($this->getRoom() !== '');
	}

	/**
	 * Gets our speaker (or speakers), complete as RTE'ed HTML with details and links.
	 * Returns an empty paragraph if this seminar doesn't have any speakers.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	String		our speakers (or '' if there is an error)
	 *
	 * @access public
	 */
	function getSpeakers(&$plugin) {
		$result = '';

		if ($this->hasSpeakers()) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title, organization, homepage, description',
				$this->tableSpeakers.', '.$this->tableSpeakersMM,
				'uid_local='.$this->getUid().' AND uid=uid_foreign'
					.t3lib_pageSelect::enableFields($this->tableSpeakers),
				'',
				'',
				''
			);

			if ($dbResult) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					if (!empty($result)) {
						$result .= chr(10);
					}

					$name = $row['title'];
					if (!empty($row['organization'])) {
						$name .= ', '.$row['organization'];
					}
					if (!empty($row['homepage'])) {
						$result .= $plugin->cObj->getTypoLink($name, $row['homepage']);
					} else {
						$result .= $name;
					}
					if (!empty($row['description'])) {
						$result .= chr(10).$row['description'];
					}
				}
			}
		}

		return $plugin->pi_RTEcssText($result);
	}

	/**
	 * Gets our speaker (or speakers) as a plain test list (just their names).
	 * Returns an empty string if this seminar doesn't have any speakers.
	 *
	 * @return	String		our speakers list (or '' if there is an error)
	 *
	 * @access public
	 */
	function getSpeakersShort() {
		$result = '';

		if ($this->hasSpeakers()) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				$this->tableSpeakers.', '.$this->tableSpeakersMM,
				'uid_local='.$this->getUid().' AND uid=uid_foreign'
					.t3lib_pageSelect::enableFields($this->tableSpeakers),
				'',
				'',
				''
			);

			if ($dbResult) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					if (!empty($result)) {
						$result .= ', ';
					}

					$result .= $row['title'];
				}
			}
		}

		return $result;
	}

	/**
	 * Checks whether we have any speakers set, but does not check the validity of that entry.
	 *
	 * @return	boolean		true if we have any speakers asssigned to this seminar, false otherwise.
	 *
	 * @access public
	 */
	function hasSpeakers() {
		return (boolean) $this->getSeminarsPropertyInteger('speakers');
	}

	/**
	 * Gets our regular price as a string containing amount and currency.
	 *
	 * @return	String		the seminar price
	 *
	 * @access public
	 */
	function getPrice() {
		return $this->getSeminarsPropertyInteger('price_regular').'&nbsp;EUR';
	}

	/**
	 * Checks whether this seminar has a non-zero regular price set.
	 *
	 * @return	boolean		true if the seminar has a non-zero regular price, false if it is free.
	 *
	 * @access public
	 */
	function hasPrice() {
		return ($this->getSeminarsPropertyInteger('price_regular') !== 0);
	}

	/**
	 * Gets our allowed payment methods, complete as RTE'ed HTML LI list (with enclosing UL).
	 * Returns an empty paragraph if this seminar doesn't have any payment methods.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	String		our payment methods as HTML (or '' if there is an error)
	 *
	 * @access public
	 */
	function getPaymentMethods(&$plugin) {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'payment_methods',
			$this->tableSeminars,
			'uid='.$this->getUid()
				.t3lib_pageSelect::enableFields($this->tableSeminars),
			'',
			'',
			''
		);

		if ($dbResult) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$paymentMethodsUids = explode(',', $row['payment_methods']);
			foreach ($paymentMethodsUids as $currentPaymentMethod) {
				$dbResultPaymentMethod = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'title',
					$this->tablePaymentMethods,
					'uid='.intval($currentPaymentMethod)
						.t3lib_pageSelect::enableFields($this->tablePaymentMethods),
					'',
					'',
					''
				);

				// we expect just one result
				if ($dbResultPaymentMethod && $GLOBALS['TYPO3_DB']->sql_num_rows ($dbResultPaymentMethod)) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultPaymentMethod);
					$result .= '  <li>'.$row['title'].'</li>'.chr(10);
				}
			}

			$result = '<ul>'.chr(10).$result.'</ul>'.chr(10);
		} else {
			$result = '';
		}

		return $plugin->pi_RTEcssText($result);
	}

	/**
	 * Checks whether this seminar has any paxment methods set.
	 *
	 * @return	boolean		true if the seminar has any payment methods, false if it is free.
	 *
	 * @access public
	 */
	function hasPaymentMethods() {
		return ($this->getSeminarsPropertyString('payment_methods') !== '');
	}

	/**
	 * Gets the number of vacancies for this seminar
	 *
	 * @return	integer		the number of vacancies (will be 0 if the seminar is overbooked)
	 *
	 * @access public
	 */
	function getVacancies() {
		return max(0, $this->getSeminarsPropertyInteger('attendees_max') - $this->getSeminarsPropertyInteger('attendees'));
	}

	/**
	 * Checks whether this seminar still has vacancies (is not full yet).
	 *
	 * @return	boolean		true if the seminar has vacancies, false if it is full.
	 *
	 * @access public
	 */
	function hasVacancies() {
		return !$this->getSeminarsPropertyInteger('is_full');
	}

	/**
	 * Gets our organizers (as HTML code with hyperlinks to their homepage, if they have any).
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page, must not be null)
	 *
	 * @return	string		the hyperlinked names and descriptions or our organizers
	 *
	 * @access public
	 */
	function getOrganizers(&$plugin) {
		$result = '';

		if ($this->hasOrganizers()) {
			$organizersNumbers = explode(',', $this->getSeminarsPropertyString('organizers'));
			foreach($organizersNumbers as $currentOrganizerNumber) {
				$currentOrganizerData =& $this->retrieveOrganizer($currentOrganizerNumber);

				if ($currentOrganizerData) {
					if (!empty($result)) {
						$result .= ', ';
					}
					$result .= $plugin->cObj->getTypoLink($currentOrganizerData['title'], $currentOrganizerData['homepage']);
				}
			}
		}

		return $result;
	}

	/**
	 * Retrieves an organizer from the DB and caches it in this->organizersCache.
	 * If that organizer already is in the cache, it is taken from there instead.
	 *
	 * In case of error, $this->organizersCache will stay untouched.
	 *
	 * @param	integer		UID of the organizer to retrieve
	 *
	 * @return	array		the organizer data (will be null if an error has occured)
	 *
	 * @access private
	 */
	 function &retrieveOrganizer($organizerUid) {
	 	$result = false;

	 	if (isset($this->organizersCache[$organizerUid])) {
	 		$result = $this->organizersCache[$organizerUid];
	 	} else {
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$this->tableOrganizers,
				'uid='.intval($organizerUid)
					.t3lib_pageSelect::enableFields($this->tableOrganizers),
				'',
				'',
				''
			);

			if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
				$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$this->organizersCache[$organizerUid] =& $result;
			}
		}

		return $result;
	}

	/**
	 * Checks whether we have any organizers set, but does not check the validity of that entry.
	 *
	 * @return	boolean		true if we have any organizers asssigned to this seminar, false otherwise.
	 *
	 * @access public
	 */
	function hasOrganizers() {
		return ($this->getSeminarsPropertyString('organizers') !== '');
	}

	/**
	 * Creates a HTML link to the registration page (not wrapped in a paragraph, though)
	 * or a localized error message if registration is not possible.
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page) which we can call pi_linkTP() on (must not be null)
	 *
	 * @return	String		HTML link or error message
	 *
	 * @access public
	 */
	function getRegistrationLink(&$plugin) {
		if (!$this->registrationManager->canGenerallyRegister($this->getUid())) {
			$result = $this->registrationManager->canGenerallyRegisterMessage($this->getUid());
		} elseif (!$this->registrationManager->canUserRegisterForSeminar($this)) {
			$result = $this->registrationManager->canUserRegisterForSeminarMessage($this);
		} else {
			$result = $plugin->cObj->getTypoLink(
				$plugin->pi_getLL('label_onlineRegistration'),
				$plugin->getConfValue('registerPID'),
				array('tx_seminars_pi1[seminar]' => $this->getUid())
			);
		}

		return $result;
	}

	/**
	 * Gets a trimmed string element of the seminars array.
	 * If the array has not been intialized properly, an empty string is returned instead.
	 *
	 * @param	String		key of the element to return
	 *
	 * @return	String		the corresponding element from the seminars array.
	 *
	 * @access private
	 */
	function getSeminarsPropertyString($key) {
		$result = ($this->seminarData && isset($this->seminarData[$key]))
			? trim($this->seminarData[$key]) : '';

		return $result;
	}

	/**
	 * Gets an (intval'ed) integer element of the seminars array.
	 * If the array has not been intialized properly, 0 is returned instead.
	 *
	 * @param	String		key of the element to return
	 *
	 * @return	integer		the corresponding element from the seminars array.
	 *
	 * @access private
	 */
	function getSeminarsPropertyInteger($key) {
		$result = ($this->seminarData && isset($this->seminarData[$key]))
			? intval($this->seminarData[$key]) : 0;

		return $result;
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * @param	integer		UID of the user to check
	 *
	 * @return	boolean		true if the user already is registered, false otherwise.
	 *
	 * @access public
	 */
	function isUserRegistered($feuserUid) {
		$result = false;

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS num',
			$this->tableAttendances,
			'seminar='.$this->getUid().' AND user='.$feuserUid
				.t3lib_pageSelect::enableFields($this->tableAttendances),
			'',
			'',
			'');
		if ($dbResult) {
			$numberOfRegistrations = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$result = ($numberOfRegistrations['num'] > 0);
		}

		return $result;
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * @param	integer		UID of the user to check
	 *
	 * @return	String		empty string if everything is OK, else a localized error message.
	 *
	 * @access public
	 */
	function isUserRegisteredMessage($feuserUid) {
		return ($this->isUserRegistered($feuserUid)) ? $this->pi_getLL('message_alreadyRegistered') : '';
	}

	/**
	 * Checkes whether it is possible at all to register for this seminar,
	 * ie. it needs registration at all,
	 *     has not been cancelled,
	 *     has not begun yet
	 *     and there are still vacancies.
	 *
	 * @return	boolean		true if registration is possible, false otherwise.
	 *
	 * @access public
	 */
	function canSomebodyRegister() {
		return $this->needsRegistration() &&
			!$this->seminarData['cancelled'] &&
			($GLOBALS['SIM_EXEC_TIME'] < $this->seminarData['begin_date']) &&
			$this->hasVacancies();
	}

	/**
	 * Checkes whether it is possible at all to register for this seminar,
	 * ie. it needs registration at all,
	 *     has not been cancelled,
	 *     has not begun yet
	 *     and there are still vacancies,
	 * and returns a localized error message if registration is not possible.
	 *
	 * @return	String		empty string if everything is OK, else a localized error message.
	 *
	 * @access public
	 */
	function canSomebodyRegisterMessage() {
		$message = '';

		if (!$this->needsRegistration()) {
			$message = $this->pi_getLL('message_noRegistrationNecessary');
		} elseif ($this->seminarData['cancelled']) {
			$message = $this->pi_getLL('message_seminarCancelled');
		} elseif ($GLOBALS['SIM_EXEC_TIME'] > $this->seminarData['end_date']) {
			$message = $this->pi_getLL('message_seminarOver');
		} elseif ($GLOBALS['SIM_EXEC_TIME'] >= $this->seminarData['begin_date']) {
			$message = $this->pi_getLL('message_seminarStarted');
		} elseif (!$this->hasVacancies()) {
			$message = $this->pi_getLL('message_noVacancies');
		}

		return $message;
	}

	/**
	 * Checks whether for this event, registration is necessary at all.
	 *
	 * @return	boolean		true if registration is necessary, false otherwise
	 *
	 * @access public
	 */
	function needsRegistration() {
		return (boolean) $this->seminarData['needs_registration'];
	}

	/**
	 * Recalculates the statistics for this seminar:
	 *   the number of participants,
	 *   whether there are enough registrations for this seminar to take place,
	 *   and whether this seminar even is full.
	 *
	 * @return	boolean		true if everything went ok, false otherwise
	 *
	 * @access public
	 */
	function updateStatistics() {
		$result = false;

		$dbResultAttendees = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS num',
			$this->tableAttendances,
			'seminar='.$this->getUid()
				.t3lib_pageSelect::enableFields($this->tableAttendances),
			'',
			'',
			''
		);
		$dbResultAttendeesPaid = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS num',
			$this->tableAttendances,
			'seminar='.$this->getUid().' AND paid=1'
				.t3lib_pageSelect::enableFields($this->tableAttendances),
			'',
			'',
			''
		);

		if ($dbResultAttendees && $dbResultAttendeesPaid) {
			$numberOfAttendees = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultAttendees);
			$numberOfAttendeesPaid = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultAttendeesPaid);

			// We count paid and unpaid registrations.
			// This behaviour will be configurable in a later version.
			$numberOfSeenAttendees = $numberOfAttendees['num'];

			// We use 1 and 0 instead of boolean values as we need to write a number into the DB
			$hasEnoughAttendees = ($numberOfSeenAttendees >= $this->seminarData['attendees_min']) ? 1 : 0;
			// We use 1 and 0 instead of boolean values as we need to write a number into the DB
			$isFull = ($numberOfSeenAttendees >= $this->seminarData['attendees_max']) ? 1 : 0;

			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$this->tableSeminars,
				'uid='.$this->getUid(),
				array(
					'attendees' => $numberOfSeenAttendees,
					'enough_attendees' => $hasEnoughAttendees,
					'is_full' => $isFull
				)
			);
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']);
}
