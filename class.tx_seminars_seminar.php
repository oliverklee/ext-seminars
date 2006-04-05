<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationmanager.php');

class tx_seminars_seminar extends tx_seminars_objectfromdb {
	/** Same as class name */
	var $prefixId = 'tx_seminars_seminar';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_seminar.php';

	/** Organizers data as an array of arrays with their UID as key. Lazily initialized. */
	var $organizersCache = array();

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager = null;

	/** The number of paid attendances.
	 *  This variable is only available directly after updateStatistics() has been called.
	 *  It will go completely away once we have a configuration about whether to count
	 *  only the paid or all attendances. */
	var $numberOfAttendancesPaid = 0;

	/**
	 * The constructor. Creates a seminar instance from a DB record.
	 *
	 * @param	object		An instance of a registrationManager.
	 * @param	integer		The UID of the seminar to retrieve from the DB.
	 * 						This parameter will be ignored if $dbResult is provided.
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 						If this parameter is provided, $uid will be ignored.
	 *
	 * @access	public
	 */
	function tx_seminars_seminar(&$registrationManager, $seminarUid, $dbResult = null) {
		$this->init();
		$this->tableName = $this->tableSeminars;
		$this->registrationManager =& $registrationManager;

		if (!$dbResult) {
			$dbResult = $this->retrieveSeminar($seminarUid);
		}

	 	if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->recordData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$this->isInDb = true;
	 	}

		return;
	}

	/**
	 * Checks whether a non-deleted and non-hidden seminar with a given UID exists in the DB.
	 *
	 * This method may be called statically.
	 *
	 * @param	string		string with a UID (need not necessarily be escaped, will be intval'ed)
	 *
	 * @return	boolean		true if a visible seminar with that UID exists; false otherwise.
	 *
	 * @access	public
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

		return (boolean) $result;
	}

	/**
	 * Retrieves a seminar from the database.
	 *
	 * @param	integer		The UID of the seminar to retrieve from the DB.
	 *
	 * @return	pointer		MySQL result pointer (of SELECT query)/DBAL object, null if the UID is invalid
	 *
	 * @access	private
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
	 * @access	public
	 */
	function getUid() {
		return $this->getRecordPropertyInteger('uid');
	}

	/**
	 * Gets the event type (seminar, workshop, lecture ...).
	 *
	 * @return	string	the seminar type (may be empty)
	 *
	 * @access	public
	 */
	function getType() {
		return $this->getConfValue('eventType');
	}

	/**
	 * Checks whether the seminar has an event type set
	 *
	 * @return	boolean		true if we have a type, false otherwise.
	 *
	 * @access	public
	 */
	function hasType() {
		return ($this->getType() !== '');
	}

	/**
	 * Gets our title.
	 *
	 * @return	string	our seminar title (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getTitle() {
		return $this->getRecordPropertyString('title');
	}

	/**
	 * Creates a hyperlink to this seminar details page.
	 *
	 * If $this->conf['listPID'] (and the corresponding flexforms value) is not set or 0,
	 * the link will use the current page's PID.
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page) which we can call pi_list_linkSingle() on (must not be null)
	 *
	 * @return	string		HTML code for the link to the seminar details page
	 *
	 * @access	public
	 */
	function getLinkedTitle(&$plugin) {
		return $plugin->cObj->getTypoLink(
			$this->getTitle(),
			$plugin->getConfValue('listPID'),
			array('tx_seminars_pi1[showUid]' => $this->getUid())
		);
	}

	/**
	 * Gets our subtitle.
	 *
	 * @return	string		our seminar subtitle (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getSubtitle() {
		return $this->getRecordPropertyString('subtitle');
	}

	/**
	 * Checks whether we have a subtitle.
	 *
	 * @return	boolean		true if we have a non-empty subtitle, false otherwise.
	 *
	 * @access	public
	 */
	function hasSubtitle() {
		return $this->hasRecordPropertyString('subtitle');
	}

	/**
	 * Gets our description, complete as RTE'ed HTML.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	string		our seminar description (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getDescription(&$plugin) {
		return $plugin->pi_RTEcssText($this->getRecordPropertyString('description'));
	}

	/**
	 * Checks whether we have a description.
	 *
	 * @return	boolean		true if we have a non-empty description, false otherwise.
	 *
	 * @access	public
	 */
	function hasDescription() {
		return $this->hasRecordPropertyString('description');
	}

	/**
	 * Gets the unique seminar title, constiting of the seminar title and the date
	 * (comma-separated).
	 *
	 * If the seminar has no date, just the title is returned.
	 *
	 * @param	string		the character or HTML entity used to separate start date and end date
	 *
	 * @return	string		the unique seminar title (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getTitleAndDate($dash = '&#8211;') {
		$date = $this->hasDate() ? ', '.$this->getDate($dash) : '';

		return $this->getTitle().$date;
	}

	/**
	 * Gets the accreditation number (which actually is a string, not an integer).
	 *
	 * @return	string		the accreditation number (may be empty)
	 *
	 * @access	public
	 */
	function getAccreditationNumber() {
		return $this->getRecordPropertyString('accreditation_number');
	}

	/**
	 * Checks whether we have an accreditation number set.
	 *
	 * @return	boolean		true if we have a non-empty accreditation number, false otherwise.
	 *
	 * @access	public
	 */
	function hasAccreditationNumber() {
		return $this->hasRecordPropertyString('accreditation_number');
	}

	/**
	 * Gets the number of credit points for this seminar
	 * (or an empty string if it is not set yet).
	 *
	 * @return	string		the number of credit points (or a an empty string if it is 0)
	 *
	 * @access	public
	 */
	function getCreditPoints() {
		return $this->hasCreditPoints() ? $this->getRecordPropertyInteger('credit_points') : '';
	}

	/**
	 * Checks whether this seminar has a non-zero number of credit points assigned.
	 *
	 * @return	boolean		true if the seminar has credit points assigned, false otherwise.
	 *
	 * @access	public
	 */
	function hasCreditPoints() {
		return $this->hasRecordPropertyInteger('credit_points');
	}

	/**
	 * Gets the seminar date.
	 * Returns a localized string "will be announced" if the seminar has no date set.
	 *
	 * Returns just one day if the seminar takes place on only one day.
	 * Returns a date range if the seminar takes several days.
	 *
	 * @param	string		the character or HTML entity used to separate start date and end date
	 *
	 * @return	string		the seminar date
	 *
	 * @access	public
	 */
	function getDate($dash = '&#8211;') {
		if (!$this->hasDate()) {
			$result = $this->pi_getLL('message_willBeAnnounced');
		} else {
			$beginDate = $this->getRecordPropertyInteger('begin_date');
			$endDate = $this->getRecordPropertyInteger('end_date');

			$beginDateDay = strftime($this->getConfValue('dateFormatYMD'), $beginDate);
			$endDateDay = strftime($this->getConfValue('dateFormatYMD'), $endDate);

			// Does the workshop span several days?
			if ($beginDateDay == $endDateDay) {
				$result = $beginDateDay;
			} else {
				if (!$this->getConfValue('abbreviateDateRanges')) {
					$result = $beginDateDay;
				} else {
					// Are the years different? Then include the complete begin date.
					if (strftime($this->getConfValue('dateFormatY'), $beginDate) !== strftime($this->getConfValue('dateFormatY'), $endDate)) {
						$result = $beginDateDay;
					} else {
						// Are the months different? Then include day and month.
						if (strftime($this->getConfValue('dateFormatM'), $beginDate) !== strftime($this->getConfValue('dateFormatM'), $endDate)) {
							$result = strftime($this->getConfValue('dateFormatMD'), $beginDate);
						} else {
							$result = strftime($this->getConfValue('dateFormatD'), $beginDate);
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
	 * @access	public
	 */
	function hasDate() {
		return ($this->hasRecordPropertyInteger('begin_date') && $this->hasRecordPropertyInteger('end_date'));
	}

	/**
	 * Gets the seminar time.
	 * Returns a localized string "will be announced" if the seminar has no time set
	 * (i.e. both begin time and end time are 00:00).
	 * Returns only the begin time if begin time and end time are the same.
	 *
	 * @param	string		the character or HTML entity used to separate begin time and end time
	 *
	 * @return	string		the seminar time
	 *
	 * @access	public
	 */
	function getTime($dash = '&#8211;') {
		if (!$this->hasTime()) {
			$result = $this->pi_getLL('message_willBeAnnounced');
		} else {
			$beginDate = $this->getRecordPropertyInteger('begin_date');
			$endDate = $this->getRecordPropertyInteger('end_date');

			$beginTime = strftime($this->getConfValue('timeFormat'), $beginDate);
			$endTime = strftime($this->getConfValue('timeFormat'), $endDate);

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
	 * @access	public
	 */
	function hasTime() {
		$beginTime = strftime('%H:%M', $this->getRecordPropertyInteger('begin_date'));
		$endTime = strftime('%H:%M', $this->getRecordPropertyInteger('end_date'));

		return ($beginTime !== '00:00' || $endTime !== '00:00');
	}

	/**
	 * Gets our place (or places), complete as RTE'ed HTML with address and links.
	 * Returns a localized string "will be announced" if the seminar has no places set.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	string		our places description (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getPlaceWithDetails(&$plugin) {
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
	 * @return	string		our places list (or '' if there is an error)
	 *
	 * @access	public
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
	 * @access	public
	 */
	function hasPlace() {
		return $this->hasRecordPropertyInteger('place');
	}

	/**
	 * Gets the seminar room (not the site).
	 *
	 * @return	string		the seminar room (may be empty)
	 *
	 * @access	public
	 */
	function getRoom() {
		return $this->getRecordPropertyString('room');
	}

	/**
	 * Checks whether we have a room set.
	 *
	 * @return	boolean		true if we have a non-empty room, false otherwise.
	 *
	 * @access	public
	 */
	function hasRoom() {
		return $this->hasRecordPropertyString('room');
	}

	/**
	 * Gets our speaker (or speakers), complete as RTE'ed HTML with details and links.
	 * Returns an empty paragraph if this seminar doesn't have any speakers.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	string		our speakers (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getSpeakersWithDescription(&$plugin) {
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
	 * Gets our speaker (or speakers) as a plain text list (just their names).
	 * Returns an empty string if this seminar doesn't have any speakers.
	 *
	 * @return	string		our speakers list (or '' if there is an error)
	 *
	 * @access	public
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
	 * @access	public
	 */
	function hasSpeakers() {
		return $this->hasRecordPropertyInteger('speakers');
	}

	/**
	 * Gets our regular price as a string containing amount and currency.
	 *
	 * @param	string		the character or HTML entity used to separate price and currency
	 *
	 * @return	string		the regular seminar price
	 *
	 * @access	public
	 */
	function getPriceRegular($space = '&nbsp;') {
		return $this->getRecordPropertyInteger('price_regular').$space.$this->getConfValue('currency');
	}

	/**
	 * Checks whether this seminar has a non-zero regular price set.
	 *
	 * @return	boolean		true if the seminar has a non-zero regular price, false if it is free.
	 *
	 * @access	public
	 */
	function hasPriceRegular() {
		return $this->hasRecordPropertyInteger('price_regular');
	}

	/**
	 * Gets our special price as a string containing amount and currency.
	 * Returns an empty string if there is no special price set.
	 *
	 * @param	string		the character or HTML entity used to separate price and currency
	 *
	 * @return	string		the special seminar price
	 *
	 * @access	public
	 */
	function getPriceSpecial($space = '&nbsp;') {
		return $this->hasPriceSpecial() ?
			($this->getRecordPropertyInteger('price_special').$space.$this->getConfValue('currency')) : '';
	}

	/**
	 * Checks whether this seminar has a non-zero special price set.
	 *
	 * @return	boolean		true if the seminar has a non-zero special price, false if it is free.
	 *
	 * @access	public
	 */
	function hasPriceSpecial() {
		return $this->hasRecordPropertyInteger('price_special');
	}

	/**
	 * Gets our allowed payment methods, complete as RTE'ed HTML LI list (with enclosing UL),
	 * but without the detailed description.
	 * Returns an empty paragraph if this seminar doesn't have any payment methods.
	 *
	 * @param	object		the live pibase object
	 *
	 * @return	string		our payment methods as HTML (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getPaymentMethods(&$plugin) {
		$result = '';

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
		}

		return $plugin->pi_RTEcssText($result);
	}

	/**
	 * Gets our allowed payment methods, just as plain text,
	 * including the detailed description.
	 * Returns an empty string if this seminar doesn't have any payment methods.
	 *
	 * @return	string		our payment methods as plain text (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getPaymentMethodsPlain() {
		$result = '';

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
					'title, description',
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
					$result .= '* '.$row['title'].': ';
					$result .= $row['description'].chr(10).chr(10);
				}
			}
		}

		return $result;
	}

	/**
	 * Checks whether this seminar has any paxment methods set.
	 *
	 * @return	boolean		true if the seminar has any payment methods, false if it is free.
	 *
	 * @access	public
	 */
	function hasPaymentMethods() {
		return $this->hasRecordPropertyString('payment_methods');
	}

	/**
	 * Gets the number of attendances for this seminar
	 * (currently the paid attendances as well as the unpaid ones)
	 *
	 * @return	integer		the number of attendances
	 *
	 * @access	public
	 */
	function getAttendances() {
		return $this->getRecordPropertyInteger('attendees');
	}

	/**
	 * Gets the number of paid attendances for this seminar.
	 * This function may only be called after updateStatistics() has been called.
	 *
	 * @return	integer		the number of paid attendances
	 *
	 * @access	public
	 */
	function getAttendancesPaid() {
		return $this->numberOfAttendancesPaid;
	}

	/**
	 * Gets the number of attendances that are not paid yet
	 *
	 * @return	integer		the number of attendances that are not paid yet
	 *
	 * @access	public
	 */
	function getAttendancesNotPaid() {
		return ($this->getAttendances() - $this->getAttendancesPaid());
	}

	/**
	 * Gets the number of vacancies for this seminar
	 *
	 * @return	integer		the number of vacancies (will be 0 if the seminar is overbooked)
	 *
	 * @access	public
	 */
	function getVacancies() {
		return max(0, $this->getRecordPropertyInteger('attendees_max') - $this->getAttendances());
	}

	/**
	 * Checks whether this seminar still has vacancies (is not full yet).
	 *
	 * @return	boolean		true if the seminar has vacancies, false if it is full.
	 *
	 * @access	public
	 */
	function hasVacancies() {
		return !($this->isFull());
	}

	/**
	 * Checks whether this seminar already is full .
	 *
	 * @return	boolean		true if the seminar is full, false if it still has vacancies.
	 *
	 * @access	public
	 */
	function isFull() {
		return $this->getRecordPropertyBoolean('is_full');
	}

	/**
	 * Checks whether this seminar has enough attendances to take place.
	 *
	 * @return	boolean		true if the seminar has enough attendances, false otherwise.
	 *
	 * @access	public
	 */
	function hasEnoughAttendances() {
		return $this->getRecordPropertyBoolean('enough_attendees');
	}

	/**
	 * Returns the latest date/time to register for a seminar.
	 * This is either the registration deadline (if set) or the begin date of an event.
	 *
	 * @return	integer		the latest possible moment to register for a seminar
	 *
	 * @access	public
	 */
	function getLatestPossibleRegistrationTime() {
		return (($this->hasRegistrationDeadline()) ?
			$this->getRecordPropertyInteger('deadline_registration') :
			$this->getRecordPropertyInteger('begin_date')
		);
	}

	/**
	 * Returns the seminar registration deadline
	 * The returned string is formatted using the format configured in dateFormatYMD and timeFormat
	 *
	 * @return	string		the date + time of the deadline
	 *
	 * @access	public
	 */
	function getRegistrationDeadline() {
		return strftime($this->getConfValue('dateFormatYMD').' '.$this->getConfValue('timeFormat'), $this->getRecordPropertyInteger('deadline_registration'));
	}

	/**
	 * Checks whether this seminar has a deadline for registration set.
	 *
	 * @return	boolean		true if the seminar has a datetime set.
	 *
	 * @access	public
	 */
	function hasRegistrationDeadline() {
		return $this->hasRecordPropertyInteger('deadline_registration');
	}

	/**
	 * Checks whether this seminar has a minimum of attendees set.
	 *
	 * @return	boolean		true if the seminar has a minimum of attendees set.
	 *
	 * @access	public
	 */
	function hasMinimumAttendees() {
		return $this->hasRecordPropertyInteger('attendees_min');
	}

	/**
	 * Returns the minimum amount of attendees required for this event to be held.
	 *
	 * @return	integer		the minimum amount of attendees
	 *
	 * @access	public
	 */
	function getMinimumAttendees() {
		return $this->getRecordPropertyInteger('attendees_min');
	}

	/**
	 * Gets our organizers (as HTML code with hyperlinks to their homepage, if they have any).
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page, must not be null)
	 *
	 * @return	string		the hyperlinked names and descriptions of our organizers
	 *
	 * @access	public
	 */
	function getOrganizers(&$plugin) {
		$result = '';

		if ($this->hasOrganizers()) {
			$organizerUids = explode(',', $this->getRecordPropertyString('organizers'));
			foreach ($organizerUids as $currentOrganizerUid) {
				$currentOrganizerData =& $this->retrieveOrganizer($currentOrganizerUid);

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
	 * Gets our organizers' names and e-mail addresses in the format
	 * '"John Doe" <john.doe@example.com>'.
	 *
	 * The name is not encoded yet.
	 *
	 * @return	array		the organizers' names and e-mail addresses
	 *
	 * @access	public
	 */
	function getOrganizersNameAndEmail() {
		$result = array();

		if ($this->hasOrganizers()) {
			$organizerUids = explode(',', $this->getRecordPropertyString('organizers'));
			foreach ($organizerUids as $currentOrganizerUid) {
				$currentOrganizerData =& $this->retrieveOrganizer($currentOrganizerUid);

				if ($currentOrganizerData) {
					$result[] = '"'.$currentOrganizerData['title'].'" <'.$currentOrganizerData['email'].'>';
				}
			}
		}

		return $result;
	}

	/**
	 * Gets our organizers' e-mail addresses in the format
	 * "john.doe@example.com".
	 *
	 * @return	array		the organizers' e-mail addresses
	 *
	 * @access	public
	 */
	function getOrganizersEmail() {
		$result = array();

		if ($this->hasOrganizers()) {
			$organizerUids = explode(',', $this->getRecordPropertyString('organizers'));
			foreach ($organizerUids as $currentOrganizerUid) {
				$currentOrganizerData =& $this->retrieveOrganizer($currentOrganizerUid);

				if ($currentOrganizerData) {
					$result[] = $currentOrganizerData['email'];
				}
			}
		}

		return $result;
	}

	/**
	 * Gets our organizers' e-mail footers.
	 *
	 * @return	array		the organizers' e-mail footers.
	 *
	 * @access	public
	 */
	function getOrganizersFooter() {
		$result = array();

		if ($this->hasOrganizers()) {
			$organizerUids = explode(',', $this->getRecordPropertyString('organizers'));
			foreach ($organizerUids as $currentOrganizerUid) {
				$currentOrganizerData =& $this->retrieveOrganizer($currentOrganizerUid);

				if ($currentOrganizerData) {
					$result[] = $currentOrganizerData['email_footer'];
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
	 * @return	array		a reference to the organizer data (will be null if an error has occured)
	 *
	 * @access	private
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
	 * @access	public
	 */
	function hasOrganizers() {
		return $this->hasRecordPropertyString('organizers');
	}

	/**
	 * Gets the URL to the detailed view of this seminar.
	 *
	 * If $this->conf['listPID'] (and the corresponding flexforms value) is not set or 0,
	 * the link will use the current page's PID.
	 *
	 * @param	object		a plugin object (for a live page, must not be null)
	 *
	 * @return	string		URL of the seminar details page
	 *
	 * @access	public
	 */
	function getDetailedViewUrl(&$plugin) {
		return $plugin->getConfValue('baseURL')
			.$plugin->cObj->getTypoLink_URL(
				$plugin->getConfValue('listPID'),
				array('tx_seminars_pi1[showUid]' => $this->getUid())
			);
	}

	/**
	 * Gets a plain text list of property values (if they exist),
	 * formatted as strings (and nicely lined up) in the following format:
	 *
	 * key1: value1
	 *
	 * @param	string		comma-separated list of key names
	 *
	 * @return	string		formatted output (may be empty)
	 *
	 * @access	public
	 */
	function dumpSeminarValues($keysList) {
		$keys = explode(',', $keysList);

		$maxLength = 0;
		foreach ($keys as $index => $currentKey) {
			$currentKeyTrimmed = strtolower(trim($currentKey));
			// write the trimmed key back so that we don't have to trim again
			$keys[$index] = $currentKeyTrimmed;
			$maxLength = max($maxLength, strlen($currentKeyTrimmed));
		}

		$result = '';
		foreach ($keys as $currentKey) {
			switch ($currentKey) {
				case 'date':
					$value = $this->getDate('-');
					break;
				case 'place':
					$value = $this->getPlaceShort();
					break;
				case 'price_regular':
					$value = $this->getPriceRegular(' ');
					break;
				case 'price_special':
					$value = $this->getPriceSpecial(' ');
					break;
				case 'speakers':
					$value = $this->getSpeakersShort();
					break;
				case 'time':
					$value = $this->getTime('-');
					break;
				case 'titleanddate':
					$value = $this->getTitleAndDate('-');
					break;
				case 'type':
					$value = $this->getType();
					break;
				case 'vacancies':
					$value = $this->getVacancies();
					break;
				default:
					$value = $this->getRecordPropertyString($currentKey);
					break;
			}
			$result .= str_pad($currentKey.': ', $maxLength + 2, ' ').$value.chr(10);
		}

		return $result;
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * @param	integer		UID of the user to check
	 *
	 * @return	boolean		true if the user already is registered, false otherwise.
	 *
	 * @access	public
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
	 * @return	string		empty string if everything is OK, else a localized error message.
	 *
	 * @access	public
	 */
	function isUserRegisteredMessage($feuserUid) {
		return ($this->isUserRegistered($feuserUid)) ? $this->pi_getLL('message_alreadyRegistered') : '';
	}

	/**
	 * Checkes whether it is possible at all to register for this seminar,
	 * ie. it needs registration at all,
	 *     has not been canceled,
	 *     has not begun yet,
	 *     registration deadline is not over yet,
	 *     and there are still vacancies.
	 *
	 * @return	boolean		true if registration is possible, false otherwise.
	 *
	 * @access	public
	 */
	function canSomebodyRegister() {
		return $this->needsRegistration() &&
			!$this->isCanceled() &&
			!$this->isRegistrationDeadlineOver() &&
			$this->hasVacancies();
	}

	/**
	 * Checkes whether it is possible at all to register for this seminar,
	 * ie. it needs registration at all,
	 *     has not been canceled,
	 *     has not begun yet,
	 *     the registration deadline is not over yet
	 *     and there are still vacancies,
	 * and returns a localized error message if registration is not possible.
	 *
	 * @return	string		empty string if everything is OK, else a localized error message.
	 *
	 * @access	public
	 */
	function canSomebodyRegisterMessage() {
		$message = '';

		if (!$this->needsRegistration()) {
			$message = $this->pi_getLL('message_noRegistrationNecessary');
		} elseif ($this->isCanceled()) {
			$message = $this->pi_getLL('message_seminarCancelled');
		} elseif ($this->isRegistrationDeadlineOver()) {
			$message = $this->pi_getLL('message_seminarRegistrationIsClosed');
		} elseif ($this->isFull()) {
			$message = $this->pi_getLL('message_noVacancies');
		}

		return $message;
	}

	/**
	 * Checks whether this event has been canceled.
	 *
	 * @return	boolean		true if the event has been canceled, false otherwise
	 *
	 * @access	public
	 */
	function isCanceled() {
		return $this->getRecordPropertyBoolean('cancelled');
	}

 	/**
	 * Checks whether the latest possibility to register for this event is over.
	 *
	 * The latest moment is either the time the event starts, or a set registration deadline.
	 *
	 * @return	boolean		true if the deadline has passed, false otherwise
	 *
	 * @access	public
	 */
	function isRegistrationDeadlineOver() {
		return ($GLOBALS['SIM_EXEC_TIME'] >= $this->getLatestPossibleRegistrationTime());
	}

	/**
	 * Checks whether for this event, registration is necessary at all.
	 *
	 * @return	boolean		true if registration is necessary, false otherwise
	 *
	 * @access	public
	 */
	function needsRegistration() {
		return $this->getRecordPropertyBoolean('needs_registration');
	}

	/**
	 * Recalculates the statistics for this seminar:
	 *   the number of participants,
	 *   whether there are enough registrations for this seminar to take place,
	 *   and whether this seminar even is full.
	 *
	 * @access	public
	 */
	function updateStatistics() {
		$numberOfAttendances = $this->countAttendances();
		$numberOfAttendancesPaid = $this->countAttendances('(paid=1 OR datepaid!=0)');

		// We count paid and unpaid registrations.
		// This behaviour will be configurable in a later version.
		$this->recordData['attendees'] = $numberOfAttendances;
		// Let's store the other result in case someone needs it.
		$this->numberOfAttendancesPaid = $numberOfAttendancesPaid;

		// We use 1 and 0 instead of boolean values as we need to write a number into the DB
		$this->recordData['enough_attendees'] = ($this->getAttendances() >= $this->getRecordPropertyInteger('attendees_min')) ? 1 : 0;
		// We use 1 and 0 instead of boolean values as we need to write a number into the DB
		$this->recordData['is_full'] = ($this->getAttendances() >= $this->getRecordPropertyInteger('attendees_max')) ? 1 : 0;

		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			$this->tableSeminars,
			'uid='.$this->getUid(),
			array(
				'attendees' => $this->getRecordPropertyInteger('attendees'),
				'enough_attendees' => $this->getRecordPropertyInteger('enough_attendees'),
				'is_full' => $this->getRecordPropertyInteger('is_full'),
				'tstamp' => time()
			)
		);

		return;
	}

	/**
	 * Queries the DB for the number of visible attendances for this event
	 * and returns the result of the DB query with the number stored in 'num'
	 * (the result will be null if the query fails).
	 *
	 * This function takes multi-seat registrations into account as well.
	 *
	 * An additional string can be added to the WHERE clause to look only for
	 * certain attendances, e.g. only the paid ones.
	 *
	 * Note that this does not write the values back to the seminar record yet.
	 * This needs to be done in an additional step after this.
	 *
	 * @param	string		string that will be prepended to the WHERE clause
	 *						using AND, e.g. 'pid=42' (the AND and the enclosing
	 *						spaces are not necessary for this parameter)
	 *
	 * @return	integer		the number of attendances
	 *
	 * @access	protected
	 */
	function countAttendances($queryParameters = '1') {
		$result = 0;

		$dbResultSingleSeats = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS number',
			$this->tableAttendances,
			$queryParameters
				.' AND seminar='.$this->getUid()
				.' AND seats=0'
				.t3lib_pageSelect::enableFields($this->tableAttendances),
			'',
			'',
			''
		);
		if ($dbResultSingleSeats) {
			$fieldsSingleSeats = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultSingleSeats);
			$result += $fieldsSingleSeats['number'];
		}

		$dbResultMultiSeats = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'SUM(seats) AS number',
			$this->tableAttendances,
			$queryParameters
				.' AND seminar='.$this->getUid()
				.' AND seats!=0'
				.t3lib_pageSelect::enableFields($this->tableAttendances),
			'',
			'',
			''
		);

		if ($dbResultMultiSeats) {
			$fieldsMultiSeats = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultMultiSeats);
			$result += $fieldsMultiSeats['number'];
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']);
}
