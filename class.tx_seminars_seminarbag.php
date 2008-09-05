<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_bag.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_seminar.php');

class tx_seminars_seminarbag extends tx_seminars_bag {
	/**
	 * The constructor. Creates a seminar bag that contains seminar
	 * records and allows to iterate over them.
	 *
	 * @param	string		string that will be prepended to the WHERE clause
	 * 						using AND, e.g. 'pid=42' (the AND and the enclosing
	 * 						spaces are not necessary for this parameter)
	 * @param	string		comma-separated names of additional DB tables used
	 * 						for JOINs, may be empty
	 * @param	string		GROUP BY clause (may be empty), must already be
	 * 						safeguarded against SQL injection
	 * @param	string		ORDER BY clause (may be empty), must already be
	 * 						safeguarded against SQL injection
	 * @param	string		LIMIT clause (may be empty), must already be
	 * 						safeguarded against SQL injection
	 * @param	integer		If $showHiddenRecords is set (0/1), any hidden-
	 * 						fields in records are ignored.
	 * @param	boolean		If $ignoreTimingOfRecords is true the timing of
	 * 						records is ignored.
	 */
	public function __construct(
		$queryParameters = '1=1', $additionalTableNames = '', $groupBy = '',
		$orderBy = 'uid', $limit = '', $showHiddenRecords = -1,
		$ignoreTimingOfRecords = false
	) {
		parent::__construct(
			SEMINARS_TABLE_SEMINARS,
			$queryParameters,
			$additionalTableNames,
			$groupBy,
			$orderBy,
			$limit,
			$showHiddenRecords,
			$ignoreTimingOfRecords
		);
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult
	 * as a source. If the current item cannot be created, $this->currentItem
	 * will be nulled out.
	 *
	 * $this->dbResult must be ensured to be non-null when this function is
	 * called.
	 */
	protected function createItemFromDbResult() {
		$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
		$this->currentItem = new $seminarClassname(0, $this->dbResult);
		$this->checkCurrentItem();
	}

	/**
	 * Removes the dummy option from the submitted form data.
	 *
	 * @param	array		the POST data submitted from the form, may be empty
	 *
	 * @return	array		the POST data without the dummy option
	 */
	public function removeDummyOptionFromFormData(array $formData) {
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
	function getAdditionalQueryForCountry(array $countries) {
		// Removes the dummy option from the form data if the user selected it.
		$countries = $this->removeDummyOptionFromFormData($countries);

		if (empty($countries)) {
			return '';
		}

		$result = '';

		// Implodes the array to a comma-separated list and adds quotes around
		// each entry to make it work with the database. This is needed because
		// the values are of type string.
		$countryIsoCodes = implode(
			',',
			$GLOBALS['TYPO3_DB']->fullQuoteArray(
				$countries,
				SEMINARS_TABLE_SITES
			)
		);

		// Checks whether there are places that have one of the selected
		// countries set and if there are events that have those places set
		// (looked up in the m:n table).
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			SEMINARS_TABLE_SEMINARS.'.uid',
			SEMINARS_TABLE_SEMINARS
				.' LEFT JOIN '.SEMINARS_TABLE_SITES_MM.' ON '
				.SEMINARS_TABLE_SEMINARS.'.uid='.SEMINARS_TABLE_SITES_MM.'.uid_local'
				.' LEFT JOIN '.SEMINARS_TABLE_SITES.' ON '
				.SEMINARS_TABLE_SITES_MM.'.uid_foreign='.SEMINARS_TABLE_SITES.'.uid',
			SEMINARS_TABLE_SITES.'.country IN('.$countryIsoCodes.')',
			'',
			SEMINARS_TABLE_SEMINARS.'.uid'
		);

		// Adds the additional part of the query only if there was at least one
		// matching entry found in the m:n table.
		if ($dbResult) {
			$seminarUids = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$seminarUids[] = $row['uid'];
			}
			if (!empty($seminarUids)) {
				$seminarUidsWithThisCountry = implode(',', $seminarUids);
				$result = ' AND '.SEMINARS_TABLE_SEMINARS
					.'.uid IN('.$seminarUidsWithThisCountry.')';
			}
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
	function getAdditionalQueryForLanguage(array $languages) {
		// Removes the dummy option from the form data if the user selected it.
		$realLanguages
			= $this->removeDummyOptionFromFormData($languages);

		if (empty($realLanguages)) {
			return '';
		}

		$result = '';

		// Implodes the array to a comma-separated list and adds quotes around
		// each entry to make it work with the database. This is needed
		// because the values are of type string.
		$languageUids = implode(
			',',
			$GLOBALS['TYPO3_DB']->fullQuoteArray(
				$realLanguages,
				SEMINARS_TABLE_SEMINARS
			)
		);
		$result = ' AND '.SEMINARS_TABLE_SEMINARS.'.language IN('
			.$languageUids.')';

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
	function getAdditionalQueryForPlace(array $placeUids) {
		$result = '';
		$sanitizedPlaceUids = array();

		// Removes the dummy option from the form data if the user selected it.
		$placeUids = $this->removeDummyOptionFromFormData($placeUids);

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
				SEMINARS_TABLE_SITES_MM,
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
					$result = ' AND '.SEMINARS_TABLE_SEMINARS
						.'.uid IN('.$seminarUidsWithThisPlace.')';
				}
			}
		}

		return $result;
	}

	/**
	 * Returns the additional query parameters for the city selection from the
	 * selector widget. If the user has not selected any city, an empty string
	 * will be returned.
	 *
	 * If the user has selected at least one city, the POST data is an array in
	 * any case (i.e. it doesn't matter whether the user selected exactly one
	 * city or five of them).
	 *
	 * @param	array		city names, from POST data
	 *
	 * @return	string		the additional query parameter starting with ' AND',
	 * 						can be appended to existing query string, may be empty
	 *
	 * @access	private
	 */
	function getAdditionalQueryForCity(array $cities) {
		// Removes the dummy option from the form data if the user selected it.
		$cities = $this->removeDummyOptionFromFormData($cities);

		// Exits if the provided array of POST data is empty.
		if (empty($cities)) {
			return '';
		}

		$result = '';

		// Implodes the array to a comma-separated list and adds quotes
		// around each entry to make it work with the database. This is needed
		// because the values are of type string.
		$citiesSanitized = implode(
			',',
			$GLOBALS['TYPO3_DB']->fullQuoteArray(
				$cities,
				SEMINARS_TABLE_SITES
			)
		);

		// Checks whether there are places that have one of the selected
		// cities set and if there are events that have those places
		// set (looked up in the m:n table).
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			SEMINARS_TABLE_SEMINARS.'.uid',
			SEMINARS_TABLE_SEMINARS
				.' LEFT JOIN '.SEMINARS_TABLE_SITES_MM.' ON '
				.SEMINARS_TABLE_SEMINARS.'.uid='.SEMINARS_TABLE_SITES_MM.'.uid_local'
				.' LEFT JOIN '.SEMINARS_TABLE_SITES.' ON '
				.SEMINARS_TABLE_SITES_MM.'.uid_foreign='.SEMINARS_TABLE_SITES.'.uid',
			SEMINARS_TABLE_SITES.'.city IN('.$citiesSanitized.')',
			'',
			SEMINARS_TABLE_SEMINARS.'.uid'
		);

		// Adds the additional part of the query only if there was at least
		// one matching entry found in the m:n table.
		if ($dbResult) {
			$seminarUids = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$seminarUids[] = $row['uid'];
			}
			if (!empty($seminarUids)) {
				$seminarUidsWithThisCity = implode(',', $seminarUids);
				$result = ' AND '.SEMINARS_TABLE_SEMINARS
					.'.uid IN('.$seminarUidsWithThisCity.')';
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbag.php']);
}
?>