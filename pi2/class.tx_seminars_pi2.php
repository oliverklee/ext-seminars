<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Plugin 'CSV export' for the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(PATH_t3lib.'class.t3lib_befunc.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_headerProxyFactory.php');

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_configgetter.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbagbuilder.php');

class tx_seminars_pi2 extends tx_seminars_templatehelper {
	/** same as class name */
	var $prefixId = 'tx_seminars_pi2';
	/** path to this script relative to the extension dir */
	var $scriptRelPath = 'pi2/class.tx_seminars_pi2.php';

	/** This object provides access to config values in plugin.tx_seminars. */
	var $configGetter = null;

	/**
	 * Creates a CSV export.
	 *
	 * @param	string		(unused)
	 * @param	array		TypoScript configuration for the plugin, may be
	 * 						empty
	 *
	 * @return	string		HTML for the plugin, might be empty
	 */
	public function main($unused, array $configuration) {
		$this->init($configuration);

		switch ($this->piVars['table']) {
			case SEMINARS_TABLE_SEMINARS:
				$result = $this->createAndOutputListOfEvents();
				break;
			case SEMINARS_TABLE_ATTENDANCES:
				$result = $this->createAndOutputListOfRegistrations();
				break;
			default:
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
					'Status: 404 Not Found'
				);
				$result = $this->translate('message_404');
				break;
		}

		return $result;
	}

	/**
	 * Initializes this object and its configuration getter.
	 *
	 * @param	array		TypoScript configuration for the plugin, may be
	 * 						empty
	 */
	public function init(array $configuration = array()) {
		parent::init($configuration);

		$this->configGetter = t3lib_div::makeInstance('tx_seminars_configgetter');
		$this->configGetter->init();
	}

	/**
	 * Creates a CSV list of registrations for the event given in
	 * $this->piVars['seminar'], including a heading line.
	 *
	 * If the seminar does not exist, an error message is returned, and an error
	 * 404 is set.
	 *
	 * If access is denied, an error message is returned, and an error 403 is
	 * set.
	 *
	 * @return	string		CSV list of registrations for the given seminar or
	 * 						an error message in case of an error
	 */
	public function createAndOutputListOfRegistrations() {
		$eventUid = intval($this->piVars['seminar']);

		if (tx_seminars_objectfromdb::recordExists(
			$eventUid,
			SEMINARS_TABLE_SEMINARS)
		) {
			if ($this->canAccessListOfRegistrations()) {
				$this->setContentTypeForRegistrationLists();
				$result = $this->createListOfRegistrations($eventUid);
			} else {
				// Access is denied.
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
					'Status: 403 Forbidden'
				);
				$result = $this->translate('message_403');
			}
		} else {
			// Wrong or missing UID.
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
				'Status: 404 Not Found'
			);
			$result = $this->translate('message_404_registrations');
		}

		return $result;
	}

	/**
	 * Creates a CSV list of registrations for the event with the UID given in
	 * $eventUid, including a heading line.
	 *
	 * This function does not do any access checks.
	 *
	 * @param	integer		UID of the event for which the registration list
	 * 						should be created, must be > 0
	 *
	 * @return	string		CSV list of registrations for the given seminar or
	 * 						an empty string if there is not event with the
	 * 						provided UID
	 */
	public function createListOfRegistrations($eventUid) {
		if (!tx_seminars_objectfromdb::recordExists(
			$eventUid,
			SEMINARS_TABLE_SEMINARS)
		) {
			return '';
		}

		$result = $this->createRegistrationsHeading();

		$additionalWhere = '';
		if (!$this->configGetter->getConfValueBoolean(
				'showAttendancesOnRegistrationQueueInCSV'
		)) {
			$additionalWhere = ' AND registration_queue=0';
		}

		$registrationBagClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_registrationbag'
		);
		$registrationBag =& new $registrationBagClassname(
			'seminar='.$eventUid.$additionalWhere
		);

		while ($currentRegistration =& $registrationBag->getCurrent()) {
			$userData = $this->retrieveData(
				$currentRegistration,
				'getUserData',
				$this->configGetter->getConfValueString(
					'fieldsFromFeUserForCsv'
				)
			);
			$registrationData = $this->retrieveData(
				$currentRegistration,
				'getRegistrationData',
				$this->configGetter->getConfValueString(
					'fieldsFromAttendanceForCsv'
				)
			);
			// Combines the arrays with the user and registration data
			// and creates a list of comma-separated values from them.
			$result .= implode(
				',',
				array_merge($userData, $registrationData)
			).CRLF;

			$registrationBag->getNext();
		}

		return $result;
	}

	/**
	 * Creates the heading line for the list of registrations (including a CRLF
	 * at the end).
	 *
	 * @return	string		the heading line for the list of registrations, will
	 * 						not be empty
	 */
	protected function createRegistrationsHeading() {
		$headerLineWithoutWrapping
			= $this->configGetter->getConfValueString('fieldsFromFeUserForCsv')
				.','
				.$this->configGetter->getConfValueString(
					'fieldsFromAttendanceForCsv'
				);
		return '"'
			.str_replace(',', '","', $headerLineWithoutWrapping)
			.'"'.CRLF;
	}

	/**
	 * Creates a CSV list of events for the page given in
	 * $this->piVars['pid'].
	 *
	 * If the page does not exist, an error message is returned, and an error
	 * 404 is set.
	 *
	 * If access is denied, an error message is returned, and an error 403 is
	 * set.
	 *
	 * @return	string		CSV list of events for the given page or an error
	 *						message in case of an error
	 */
	public function createAndOutputListOfEvents() {
		$pid = intval($this->piVars['pid']);

		if ($pid > 0) {
			if ($this->canAccessListOfEvents()) {
				$this->setContentTypeForEventLists();
				$result = $this->createListOfEvents($pid);
			} else {
				// Access is denied.
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
					'Status: 403 Forbidden'
				);
				$result = $this->translate('message_403');
			}
		} else {
			// Missing PID.
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
				'Status: 404 Not Found'
			);
			$result = $this->translate('message_404');
		}

		return $result;
	}

	/**
	 * Retrieves a list of events as CSV, including the header line.
	 *
	 * This function does not do any access checks.
	 *
	 * @param	integer		PID of the system folder from which the event
	 * 						records	should be exported, must be > 0
	 *
	 * @return	string		CSV export of the event records on that page
	 */
	public function createListOfEvents($pid) {
		if ($pid <= 0) {
			return '';
		}

		$result = $this->createEventsHeading();

		$builder = t3lib_div::makeInstance('tx_seminars_seminarbagbuilder');
		$builder->setBackEndMode();
		$builder->setSourcePages($pid);
		$seminarBag = $builder->build();

		while ($currentSeminar =& $seminarBag->getCurrent()) {
			$seminarData = $this->retrieveData(
				$currentSeminar,
				'getEventData',
				$this->configGetter->getConfValueString(
					'fieldsFromEventsForCsv'
				)
			);
			// Creates a list of comma-separated values of the event data.
			$result .= implode(
				',',
				$seminarData
			).CRLF;

			$seminarBag->getNext();
		}

		return $result;
	}

	/**
	 * Creates the heading line for a CSV event list.
	 *
	 * @return	string		header list, will not be empty if the CSV
	 * 						export has been configured correctly
	 */
	private function createEventsHeading() {
		return '"'.str_replace(
			',',
			'","',
			$this->configGetter->getConfValueString(
				'fieldsFromEventsForCsv'
			).'"'.CRLF
		);
	}

	/**
	 * Retrieves data from an object and returns that data as an array of
	 * values. The individual values are already wrapped in double quotes, with
	 * the contents having all quotes escaped.
	 *
	 * @param	object		object that will deliver the data
	 * @param	string		name of a function of the given object that expects
	 *						a key as a parameter and returns the value for that
	 *						key as a string
	 * @param	string		comma-separated list of keys to retrieve
	 *
	 * @return	array		the data for the keys provided in $keys
	 *						(may be empty)
	 */
	protected function retrieveData(&$dataSupplier, $supplierFunction, $keys) {
		$result = array();

		if (!empty($keys) && method_exists($dataSupplier, $supplierFunction)) {
			$allKeys = explode(',', $keys);
			foreach ($allKeys as $currentKey) {
				$rawData = $dataSupplier->$supplierFunction($currentKey);
				// Escapes double quotes and wraps the whole string in double
				// quotes.
				$result[] = '"'.str_replace('"', '""', $rawData).'"';
			}
		}

		return $result;
	}

	/**
	 * Checks whether the list of registrations is accessible, ie.
	 * 1. CSV access is allowed for testing purposes, or
	 * 2. the logged-in BE user has read access to the registrations table and
	 *    read access to *all* pages where the registration records of the
	 *    selected event are stored.
	 *
	 * TODO: When additional ways to access the CSV data are added (e.g. FE
	 * links), the corresponding access checks need to be added to this
	 * function.
	 *
	 * @param	integer		UID of the event record for which access should be checked; leave empty to use the event set via piVars
	 *
	 * @return	boolean		true if the list of registrations may be exported as CSV
	 */
	public function canAccessListOfRegistrations($eventUid = 0) {
		global $BE_USER;

		$result = $this->configGetter->getConfValueBoolean('allowAccessToCsv');

		// Only bother to check other permissions if we don't already have
		// global access.
		if (!$result) {
			if (TYPO3_MODE == 'BE') {
				// Check read access to the registrations table.
				$result = $BE_USER->check(
					'tables_select',
					SEMINARS_TABLE_ATTENDANCES
				);
				// Check read access to all pages with registrations from the
				// selected event.
				if (!$eventUid) {
					$eventUid = intval($this->piVars['seminar']);
				}
				$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'DISTINCT pid',
					SEMINARS_TABLE_ATTENDANCES,
					'seminar='.$eventUid
						.$this->enableFields(SEMINARS_TABLE_ATTENDANCES)
				);
				if ($dbResult) {
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
						// Check read access for the current page.
						$result &= $BE_USER->doesUserHaveAccess(
							t3lib_BEfunc::getRecord('pages', $row['pid']),
							1
						);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Checks whether the list of registrations is accessible, ie.
	 * 1. CSV access is allowed for testing purposes, or
	 * 2. the logged-in BE user has read access to the registrations table and
	 *    read access to *all* pages where the registration records of the
	 *    selected event are stored.
	 *
	 * TODO: When additional ways to access the CSV data are added (e.g. FE
	 * links), the corresponding access checks need to be added to this
	 * function.
	 *
	 * @return	boolean		true if the list of registrations may be exported as
	 * 						CSV
	 */
	public function canAccessListOfEvents() {
		global $BE_USER;

		$result = $this->configGetter->getConfValueBoolean('allowAccessToCsv');

		// Only bother to check other permissions if we don't already have
		// global access.
		if (!$result) {
			if (TYPO3_MODE == 'BE') {
				// Check read access to the events table.
				$result = $BE_USER->check(
					'tables_select',
					SEMINARS_TABLE_SEMINARS
				);
				// Check read access to the given page.
				$pid = intval($this->piVars['pid']);
				$result &= $BE_USER->doesUserHaveAccess(
					t3lib_BEfunc::getRecord('pages', $pid),
					1
				);
			}
		}

		return $result;
	}

	/**
	 * Sets the HTTP header: the content type and filename (content disposition)
	 * for registration lists.
	 */
	private function setContentTypeForRegistrationLists() {
		$this->setCsvContentType();
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Content-disposition: attachment; filename=' .
				$this->configGetter->getConfValueString(
					'filenameForRegistrationsCsv'
				),
			true
		);
	}

	/**
	 * Sets the HTTP header: the content type and filename (content disposition)
	 * for event lists.
	 */
	private function setContentTypeForEventLists() {
		$this->setCsvContentType();
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Content-disposition: attachment; filename=' .
				$this->configGetter->getConfValueString('filenameForEventsCsv'),
			true
		);
	}

	/**
	 * Sets the HTTP header: the content type for CSV.
	 */
	private function setCsvContentType() {
		// In addition to the CSV content type and the charset, announces that
		// we provide a CSV header line.
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Content-type: text/csv; header=present; charset=' .
				$this->configGetter->getConfValueString('charsetForCsv'),
			true
		);
	}

	/**
	 * Returns our config getter (which might be null if we aren't initialized
	 * properly yet).
	 *
	 * This function is intended for testing purposes only.
	 *
	 * @return	object		our config getter, might be null
	 */
	public function getConfigGetter() {
		return $this->configGetter;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']);
}
?>