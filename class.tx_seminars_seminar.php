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

	/** the seminar data as an array */
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
	 * @param	array		TypoScript configuration
	 * 						(usually the same as for the FE plugin/BE module that instantiates this class)
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
	function tx_seminars_seminar($conf, &$registrationManager, $uid, $dbResult = null) {
		$result = false;
		
		$this->init($conf);
		
		$this->registrationManager =& $registrationManager;
		
		if (!$dbResult) {
			$dbResult = $this->retrieveSeminar($uid);
		}

	 	if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
	 		$this->seminarData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult); 
	 		$result = true;
	 	}

		return $result;	 	
	}
	
	/**
	 * Retrieve a seminar from the database.
	 * 
	 * @param	integer		The UID of the seminar to retrieve from the DB.
	 * 
	 * @return	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 
	 * @access private
	 */
	 function retrieveSeminar($uid) {
	 	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$this->tableSeminars,
			'uid='.intval($uid)
				.t3lib_pageSelect::enableFields($this->tableSeminars),
			'',
			'',
			'1');

		return $result;
	 }

	/**
	 * Retrieve the current seminar's speakers from the database to $this->speakers.
	 * If this already has been done, we will overwrite existing entries
	 * in the array.
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
	 * Retrieve the current seminar's organizers from the database to $this->organizers.
	 * If this already has been done, we will overwrite existing entries
	 * in the array.
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
				'' );

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
	 * Get our UID.
	 * 
	 * @return	integer		our UID (or 0 if there is an error)
	 * 
	 * @access public
	 */
	function getUid() {
		return $this->getSeminarsPropertyInteger('uid');
	}
		 
	/**
	 * Get our title.
	 * 
	 * @return	string	our seminar title (or '' if there is an error)
	 * 
	 * @access public
	 */
	function getTitle() {
		return $this->getSeminarsPropertyString('title');
	}
	 
	/**
	 * Get our subtitle.
	 * 
	 * @return	string		our seminar title (or '' if there is an error)
	 * 
	 * @access public
	 */
	function getSubtitle() {
		return $this->getSeminarsPropertyString('subtitle');
	}
	 
	/**
	 * Get a string element of the seminars array.
	 * If the array has not been intialized properly, an empty string is returned instead.
	 * 
	 * @param	string		key of the element to return
	 * 
	 * @return	string		the corresponding element from the seminars array.
	 * 
	 * @access private
	 */
	function getSeminarsPropertyString($key) {
		$result = ($this->seminarData && isset($this->seminarData[$key])) ? $this->seminarData[$key] : '';
		
		return $result;
	}

	/**
	 * Get an (intval'ed) integer element of the seminars array.
	 * If the array has not been intialized properly, 0 is returned instead.
	 * 
	 * @param	string		key of the element to return
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
	function isUserRegistered($uid) {
	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS num',
			$this->tableAttendances,
			'seminar='.$this->getUid().' AND user='.$uid
				.t3lib_pageSelect::enableFields($this->tableAttendances),
			'',
			'',
			'');
		$numberOfRegistrations = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		
		return ($numberOfRegistrations['num'] > 0);
	}
	
	/**
	 * Checks whether a user is logged in and hasn't registered for this seminar yet.
	 * Returns an empty string if everything is OK and an error message otherwise.
	 * 
	 * Note: This method does not check if it is possible to register for a given seminar at all. 
	 *
	 * @return	string		empty string if everything is OK, else a localized error message.
	 * 
	 * @access	public
	 */	
	function userCanRegister() {
		/** This is empty as long as no error has occured. Used to circumvent deeply-nested ifs. */
		$error = '';
	
		if (!$GLOBALS['TSFE']->loginUser) {
			$error = $this->pi_getLL('please_log_in');
		}

		if (empty($error)) {
			$error = $this->readSeminarFromDB();
		}

		if (empty($error)) {
			if (!$this->seminar[needs_registration]) {
				$error = $this->pi_getLL('no_registration_necessary');
			}
		}

		if (empty($error)) {
			if ($this->seminar[cancelled]) {
				$error = $this->pi_getLL('seminar_cancelled');
			}
		}

		if (empty($error)) {
			if ($this->seminar[is_full]) {
				$error = $this->pi_getLL('seminar_full');
			}
		}

		return $error;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']);
}
