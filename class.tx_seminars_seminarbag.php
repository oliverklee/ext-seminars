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
 * Class 'tx_seminars_seminarbag' for the 'seminars' extension.
 *
 * This aggregate class holds a bunch of seminar objects and allows
 * to iterate over them.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_bag.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');

class tx_seminars_seminarbag extends tx_seminars_bag {
	/** Same as class name */
	var $prefixId = 'tx_seminars_seminar_seminarbag';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_seminarbag.php';

	/**
	 * The constructor. Creates a seminar bag that contains seminar
	 * records and allows to iterate over them.
	 *
	 * @param	string		string that will be prepended to the WHERE clause
	 * 						using AND, e.g. 'pid=42' (the AND and the enclosing
	 * 						spaces are not necessary for this parameter)
	 * @param	string		comma-separated names of additional DB tables used
	 * 						for JOINs, may be empty
	 * @param	string		GROUP BY clause (may be empty), must already by
	 * 						safeguarded against SQL injection
	 * @param	string		ORDER BY clause (may be empty), must already by
	 * 						safeguarded against SQL injection
	 * @param	string		LIMIT clause (may be empty), must already by
	 * 						safeguarded against SQL injection
	 * @param	integer		If $showHiddenRecords is set (0/1), any hidden-
	 * 						fields in records are ignored.
	 * @param	boolean		If $ignoreTimingOfRecords is true the timing of
	 * 						records is ignored.
	 *
	 * @access	public
	 */
	function tx_seminars_seminarbag(
		$queryParameters = '1=1',
		$additionalTableNames = '',
		$groupBy = '',
		$orderBy = '',
		$limit = '',
		$showHiddenRecords = -1,
		$ignoreTimingOfRecords = false
	) {
		// Although the parent class also calls init(), we need to call it
		// here already so that $this->tableSeminars is provided.
		$this->init();
		parent::tx_seminars_bag(
			$this->tableSeminars,
			$queryParameters,
			$additionalTableNames,
			$groupBy,
			$orderBy,
			$limit,
			$showHiddenRecords,
			$ignoreTimingOfRecords
		);

		return;
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult
	 * as a source. If the current item cannot be created, $this->currentItem
	 * will be nulled out.
	 *
	 * $this->dbResult must be ensured to be non-null when this function is called.
	 *
	 * @access	protected
	 */
	function createItemFromDbResult() {
		$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
		$this->currentItem =& new $seminarClassname(0, $this->dbResult);
		$this->checkCurrentItem();

		return;
	}

	/**
	 * Removes the dummy option from the submitted form data.
	 *
	 * @param	array		the POST data submitted from the form, may be empty
	 *
	 * @return	array		the POST data without the dummy option
	 *
	 * @access	public
	 */
	function removeDummyOptionFromFormData($formData) {
		$cleanedFormData = array();

		foreach ($formData as $value) {
			if (($value != 'none') || ($value === 0)) {
				$cleanedFormData[] = $value;
			}
		}

		return $cleanedFormData;
	}

	/**
	 * Returns the additional query parameters for the country selection from
	 * the selector widget. If the user has not selected any country, an empty
	 * string will be returned.
	 *
	 * If the user has selected at least one country, the POST data is an array
	 * in any case (i.e. it doesn't matter whether the user selected exactly one
	 * language or five of them).
	 *
	 * @param	array		array of ISO codes for the countries, from POST data
	 *
	 * @return	string		the additional query parameter starting with ' AND',
	 * 						can be appended to existing query string, may be empty
	 *
	 * @access	private
	 */
	function getAdditionalQueryForCountry($countries) {
		$result = '';

		// Removes the dummy option from the form data if the user selected it.
		$countries = $this->removeDummyOptionFromFormData($countries);

		if (!empty($countries)) {
			// Implodes the array to a comma-separated list and adds quotes
			// around each entry to make it work with the database. This is needed
			// because the values are of type string.
			$countryIsoCodes = implode(
				',',
				$GLOBALS['TYPO3_DB']->fullQuoteArray(
					$countries,
					$this->tableSites
				)
			);

			// Checks whether there are places that have one of the selected
			// countries set and if there are events that have those places
			// set (looked up in the m:n table).
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$this->tableSeminars.'.uid',
				$this->tableSeminars
					.' LEFT JOIN '.$this->tableSitesMM.' ON '
					.$this->tableSeminars.'.uid='.$this->tableSitesMM.'.uid_local'
					.' LEFT JOIN '.$this->tableSites.' ON '
					.$this->tableSitesMM.'.uid_foreign='.$this->tableSites.'.uid',
				$this->tableSites.'.country IN('.$countryIsoCodes.')'
			);

			// Adds the additional part of the query only if there was at least
			// one matching entry found in the m:n table.
			if ($dbResult) {
				$seminarUids = array();
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$seminarUids[] = $row['uid'];
				}
				if (!empty($seminarUids)) {
					$seminarUidsWithThisCountry = implode(',', $seminarUids);
					$result = ' AND '.$this->tableSeminars
						.'.uid IN('.$seminarUidsWithThisCountry.')';
				}
			}
		}

		return $result;
	}

	/**
	 * Returns the additional query parameters for the event type selection from
	 * the selector widget. If the user has not selected an event type, an empty
	 * string will be returned.
	 *
	 * If the user has selected at least one event type, the POST data is an array
	 * in any case (i.e. it doesn't matter whether the user selected exactly one
	 * event type or five of them).
	 *
	 * @param	array		array of UIDs for the event type, from POST data,
	 * 						may be empty
	 *
	 * @return	string		the additional query parameter starting with ' AND',
	 * 						can be appended to existing query string, may be empty
	 *
	 * @access	private
	 */
	function getAdditionalQueryForEventType($eventTypeUids) {
		$result = '';
		$sanitizedEventTypeUids = array();

		// Removes the dummy option from the form data if the user selected it.
		$eventTypeUids = $this->removeDummyOptionFromFormData(
			$eventTypeUids
		);

		foreach ($eventTypeUids as $currentEventTypeUid) {
			if (intval($currentEventTypeUid) >= 0) {
				$sanitizedEventTypeUids[] = intval($currentEventTypeUid);
			}
		}

		if (!empty($sanitizedEventTypeUids)) {
			$result = ' AND '.$this->tableSeminars.'.event_type IN('
				. implode(',', $sanitizedEventTypeUids).')';
		}

		return $result;
	}

	/**
	 * Returns the additional query parameters for the language selection from
	 * the selector widget. If the user has not selected any language, an empty
	 * string will be returned.
	 *
	 * If the user has selected at least one language, the POST data is an array
	 * in any case (i.e. it doesn't matter whether the user selected exactly one
	 * language or five of them).
	 *
	 * @param	array		array of ISO codes for the language, from POST data,
	 * 						may be empty
	 *
	 * @return	string		the additional query parameter starting with ' AND',
	 * 						can be appended to existing query string, may be empty
	 *
	 * @access	private
	 */
	function getAdditionalQueryForLanguage($languages) {
		$result = '';

		// Removes the dummy option from the form data if the user selected it.
		$languages = $this->removeDummyOptionFromFormData(
			$languages
		);

		if (!empty($languages)) {
			// Implodes the array to a comma-separated list and adds quotes
			// around each entry to make it work with the database. This is needed
			// because the values are of type string.
			$languageUids = implode(
				',',
				$GLOBALS['TYPO3_DB']->fullQuoteArray(
					$languages,
					$this->tableSeminars
				)
			);
			$result = ' AND '.$this->tableSeminars.'.language IN('
				.$languageUids.')';
		}

		return $result;
	}

	/**
	 * Returns the additional query parameters for the place selection from
	 * the selector widget. If the user has not selected any place, an empty
	 * string will be returned.
	 *
	 * If the user has selected at least one place, the POST data is an array
	 * in any case (i.e. it doesn't matter whether the user selected exactly one
	 * language or five of them).
	 *
	 * @param	array		array of UIDs from the POST data, may be empty
	 *
	 * @return	string		the additional query parameter starting with ' AND',
	 * 						can be appended to existing query string, may be empty
	 *
	 * @access	private
	 */
	function getAdditionalQueryForPlace($placeUids) {
		$result = '';
		$sanitizedPlaceUids = array();

		// Removes the dummy option from the form data if the user selected it.
		$placeUids = $this->removeDummyOptionFromFormData(
			$placeUids
		);

		foreach ($placeUids as $currentPlaceUid) {
			if (intval($currentPlaceUid) > 0) {
				$sanitizedPlaceUids[] = intval($currentPlaceUid);
			}
		}

		if (!empty($sanitizedPlaceUids)) {
			// Checks whether there are places with the given UIDs that are
			// selected in any event record (looked up in the m:n table).
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid_local',
				$this->tableSitesMM,
				'uid_foreign IN('.implode(',', $sanitizedPlaceUids).')'
			);

			// Adds the additional part of the query only if there was at least
			// one matching entry found in the m:n table.
			if ($dbResult) {
				$seminarUids = array();
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$seminarUids[] = $row['uid_local'];
				}
				if (!empty($seminarUids)) {
					$seminarUidsWithThisPlace = implode(',', $seminarUids);
					$result = ' AND '.$this->tableSeminars
						.'.uid IN('.$seminarUidsWithThisPlace.')';	
				}
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']);
}

?>
