<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_configgetter.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registration.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_registrationbag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');

class tx_seminars_pi2 extends tx_seminars_templatehelper {
	/** same as class name */
	var $prefixId = 'tx_seminars_pi2';
	/** path to this script relative to the extension dir */
	var $scriptRelPath = 'pi2/class.tx_seminars_pi2.php';

	/** the seminar which we want to export */
	var $seminar;

	/** This object provides access to config values in plugin.tx_seminars. */
	var $configGetter;

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

		$this->configGetter =& t3lib_div::makeInstance('tx_seminars_configgetter');
		$this->configGetter->init();

		$result = '';

		switch ($this->piVars['table']) {
			case 'registrations':
				$result = $this->createListOfRegistrations();
				break;
			default:
				header('Status: 404 Not Found');
				$result = $this->pi_getLL('message_404');
				break;
		}

		return $result;
	}

	/**
	 * Creates a CSV list of registrations for the event given in
	 * $this->piVars['seminar'].
	 *
	 * If the seminar does not exist, an empty string is returned, and an error
	 * 404 is set.
	 *
	 * @return	string		CSV list of registrations for the given seminar or an empty string in case of an error
	 */
	function createListOfRegistrations() {
		$result = '';

		$eventUid = intval($this->piVars['seminar']);

		if (tx_seminars_objectfromdb::recordExists($eventUid, $this->tableSeminars)) {
			if ($this->canAccessListOfRegistrations()) {
				$this->setContentTypeForRegistrationLists();

				// Create the heading first.
				$result .= '"'.str_replace(
					',',
					'","',
					$this->configGetter->getConfValueString('fieldsFromFeUserForCsv')
						.','
						.$this->configGetter->getConfValueString('fieldsFromAttendanceForCsv')
						.'"'.chr(13).chr(10)
					);

				// Now let's have a registration bag to iterate over all
				// registrations of this event.
				$registrationBagClassname = t3lib_div::makeInstanceClassName('tx_seminars_registrationbag');
				$registrationBag =& new $registrationBagClassname('seminar='.$eventUid);

				while ($currentRegistration =& $registrationBag->getCurrent()) {
					$userData = $this->retrieveData(
						$currentRegistration,
						'getUserData',
						$this->configGetter->getConfValueString('fieldsFromFeUserForCsv')
					);
					$registrationData = $this->retrieveData(
						$currentRegistration,
						'getRegistrationData',
						$this->configGetter->getConfValueString('fieldsFromAttendanceForCsv')
					);
					// Combine the arrays with the user and registration data
					// and create a list of comma-separated values from them.
					$result .= implode(
						',',
						array_merge($userData, $registrationData)
					).chr(13).chr(10);

					$registrationBag->getNext();
				}
			} else {
				// Access is denied.
				header('Status: 403 Forbidden');
				$result = $this->pi_getLL('message_403');
			}
		} else {
			// Wrong or missing UID.
			header('Status: 404 Not Found');
			$result = $this->pi_getLL('message_404');
		}

		return $result;
	}

	/**
	 * Retrieves data from an object and returns that data as an array of
	 * values. The individual values are already wrapped in double quotes, with
	 * the contents having all quotes escaped.
	 *
	 * @param	object		object that will deliver the data
	 * @param	string		name of a function of the given object that expects a key as a parameter and returns the value for that key as a string
	 * @param	string		comma-separated list of keys to retrieve
	 *
	 * @return	array		the data for the keys provided in $keys (may be empty)
	 */
	function retrieveData(&$dataSupplier, $supplierFunction, $keys) {
		$result = array();

		if (!empty($keys) && method_exists($dataSupplier, $supplierFunction)) {
			$allKeys = explode(',', $keys);
			foreach ($allKeys as $currentKey) {
				$rawData = $dataSupplier->$supplierFunction($currentKey);
				// Escape double quotes and wrap the whole string in double quotes.
				$result[] = '"'.str_replace('"', '""', $rawData).'"';
			}
		}

		return $result;
	}

	/**
	 * Checks whether the list of registrations is accessible, ie.
	 * CSV access is allowed for testing purposes.
	 *
	 * TODO: When additional ways to access the CSV data are added (e.g. FE
	 * links or in the BE in mod2), the corresponding access checks need to be
	 * added to this function.
	 *
	 * @return	boolean		true if the list of registrations may be exported as CSV
	 *
	 * @access	protected
	 */
	function canAccessListOfRegistrations() {
		$result = $this->configGetter->getConfValueBoolean('allowAccessToCsv');

		return $result;
	}

	/**
	 * Sets the HTTP header: the content type and filename (content disposition).
	 *
	 * @access	protected
	 */
	function setContentTypeForRegistrationLists() {
		// In addition to the CSV content type and the charset, announce that
		// we provide a CSV header line.
		header('Content-type: text/csv; header=present; charset='
			.$this->configGetter->getConfValueString('charsetForCsv'), true);
		header('Content-disposition: attachment; filename='
			.$this->configGetter->getConfValueString('filenameForRegistrationsCsv'),
			true
		);

		return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']);
}

?>
