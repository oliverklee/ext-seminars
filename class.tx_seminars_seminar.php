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

	/** The seminar's speakers data as an array of arrays with their UID as key. Lazily initialized. */
	var $speakers = null;
	/** The seminar's sites data as an array of arrays with their UID as key. Lazily initialized. */
	var $sites = null;
	/** The seminar's organizers data as an array of arrays with their UID as key. Lazily initialized. */
	var $organizers = null;

	/** an instance of registration manager which we want to have around only once (for performance reasons) */
	var $registrationManager;

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
	 * Retrieves the current seminar's speakers from the database to $this->speakers.
	 * If this already has been done, we will overwrite existing entries
	 * in the array.
	 * 
	 * XXX Currently, this method is not used. Check whether we'll need it or else remove it
	 *  
	 * @return	boolean		true if everything went OK, false otherwise.
	 * 
	 * @access private
	 */
	 function retrieveSpeakers() {
	 	$result = false;
	 	
	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$this->tableSpeakers.', '.$this->tableSpeakersMM,
			'uid_local='.$this->getUid().' AND uid=uid_foreign'
				.t3lib_pageSelect::enableFields($this->tableSpeakers),
			'',
			'',
			'' );

		if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$result = true;

			if (!$this->speakers) {
				$this->speakers = array();
			}	
	
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$this->speakers[$row['uid']] = $row; 
			}
		}

		return $result;
	 }
	 
	/**
	 * Retrieves the current seminar's organizers from the database to $this->organizers.
	 * If this already has been done, we will overwrite existing entries
	 * in the array.
	 * 
	 * XXX Currently, this method is not used. Check whether we'll need it or else remove it
	 *  
	 * @return	boolean		true if everything went OK, false otherwise.
	 * 
	 * @access private
	 */
	 function retrieveOrganizers() {
	 	$result = false;
	 	
		$organizers = explode(',', $this->seminarData['organizers']);
		foreach ($organizers as $currentOrganizer) {
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$this->tableOrganizers,
				'uid='.intval($currentOrganizer)
					.t3lib_pageSelect::enableFields($this->tableOrganizers),
				'',
				'',
				''
			);

			if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
				$result = true;

				if (!$this->organizers) {
					$this->organizers = array();
				}	
		
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$this->organizers[$row['uid']] = $row; 
			}
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
	 * Gets our subtitle.
	 * 
	 * @return	String		our seminar title (or '' if there is an error)
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
	 * Gets a string element of the seminars array.
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
		return $this->seminarData['needs_registration'] &&
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
		
		if (!$this->seminarData['needs_registration']) {
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
