<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'tx_seminars_timeslot' for the 'seminars' extension.
 *
 * This class represents a time slot.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_timeslot extends tx_seminars_timespan {
	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = 'tx_seminars_timeslots';

	/**
	 * Creates and returns a speakerbag object.
	 *
	 * @return tx_seminars_speakerbag a speakerbag object
	 */
	private function getSpeakerBag() {
		return tx_oelib_ObjectFactory::make(
			'tx_seminars_speakerbag',
			'tx_seminars_timeslots_speakers_mm.uid_local = ' . $this->getUid()
				.' AND uid = uid_foreign',
			'tx_seminars_timeslots_speakers_mm'
		);
	}

	/**
	 * Gets the speaker UIDs.
	 *
	 * @return array the speaker UIDs
	 */
	public function getSpeakersUids() {
		$result = array();

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid_foreign',
			'tx_seminars_timeslots_speakers_mm',
			'uid_local='.$this->getUid()
		);

		if ($dbResult) {
			while ($speaker = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result[] = $speaker['uid_foreign'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
		}

		return $result;
	}

	/**
	 * Gets the speakers of the time slot as a plain text comma-separated list.
	 *
	 * @return string the comma-separated plain text list of speakers (or ''
	 *                if there was an error)
	 */
	public function getSpeakersShortCommaSeparated() {
		$result = array();
		$speakerBag = $this->getSpeakerBag();

		foreach ($speakerBag as $speaker) {
			$result[] = $speaker->getTitle();
		}

		return implode(', ', $result);
	}

	/**
	 * Gets our place as plain text (just the name).
	 * Returns a localized string "will be announced" if the time slot has no
	 * place set.
	 *
	 * @return string our places or a localized string "will be announced"
	 *                if this timeslot has no place assigned
	 */
	public function getPlaceShort() {
		if (!$this->hasPlace()) {
			return $this->translate('message_willBeAnnounced');
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			'tx_seminars_sites',
			'uid=' . $this->getPlace() .
				tx_oelib_db::enableFields('tx_seminars_sites')
		);
		if (!$dbResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}

		$dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
		if (!$dbResultRow) {
			throw new Exception(
				'The related place with the UID ' . $this->getPlace() .
					' could not be found in the DB.'
			);
		}

		return $dbResultRow['title'];
	}

	/**
	 * Gets the place.
	 *
	 * @return integer the place UID
	 */
	public function getPlace() {
		return $this->getRecordPropertyInteger('place');
	}

	/**
	 * Gets the entry date and time as a formatted date. If the begin date of
	 * this timeslot is on the same day as the entry date, only the time will be
	 * returned.
	 *
	 * @return string the entry date and time (or the localized string "will be
	 *                announced" if no entry date is set)
	 */
	public function getEntryDate() {
		if (!$this->hasEntryDate()) {
			return $this->translate('message_willBeAnnounced');
		}

		$beginDate = $this->getBeginDateAsTimestamp();
		$entryDate = $this->getRecordPropertyInteger('entry_date');

		if (strftime('%d-%m-%Y', $entryDate) != strftime('%d-%m-%Y', $beginDate)
		) {
			$dateFormat = $this->getConfValueString('dateFormatYMD') . ' ';
		} else {
			$dateFormat = '';
		}
		$dateFormat .= $this->getConfValueString('timeFormat');

		return strftime($dateFormat, $entryDate);
	}

	/**
	 * Checks whether the timeslot has a entry date set.
	 *
	 * @return boolean TRUE if we have a entry date, FALSE otherwise
	 */
	public function hasEntryDate() {
		return $this->hasRecordPropertyInteger('entry_date');
	}

	/**
	 * Returns an associative array, containing fieldname/value pairs that need
	 * to be updated in the database. Update means "set the title" so far.
	 *
	 * @return array associative array containing data to update the
	 *               database entry of the timeslot, might be empty
	 */
	public function getUpdateArray() {
		$updateArray = array();

		$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] :
			'iso-8859-1';

		$updateArray['title'] = html_entity_decode(
			$this->getDate(),
			ENT_COMPAT,
			$charset
		);

		return $updateArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timeslot.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timeslot.php']);
}
?>