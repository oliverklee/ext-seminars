<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_timespan' for the 'seminars' extension.
 *
 * This class offers timespan-related methods for the timeslot and seminar
 * classes.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_seminars_timespan extends tx_seminars_objectfromdb {
	/** @var string same as class name */
	public $prefixId = 'tx_seminars_timespan';
	/** @var string path to this script relative to the extension dir */
	public $scriptRelPath = 'class.tx_seminars_timespan.php';

	/**
	 * Gets the begin date.
	 *
	 * @return string the begin date (or the localized string "will be
	 *                announced" if no begin date is set)
	 */
	public function getBeginDate() {
		if (!$this->hasBeginDate()) {
			$result = $this->translate('message_willBeAnnounced');
		} else {
			$result = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$this->getBeginDateAsTimestamp()
			);
		}

		return $result;
	}

	/**
	 * Checks whether there's a begin date set.
	 *
	 * @return boolean true if we have a begin date, false otherwise
	 */
	public function hasBeginDate() {
		return ($this->getBeginDateAsTimestamp() > 0);
	}

	/**
	 * Gets the end date.
	 *
	 * @return string the end date (or the localized string "will be
	 *                announced" if no end date is set)
	 */
	public function getEndDate() {
		if (!$this->hasEndDate()) {
			$result = $this->translate('message_willBeAnnounced');
		} else {
			$result = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$this->getEndDateAsTimestamp()
			);
		}

		return $result;
	}

	/**
	 * Checks whether there's an end date set.
	 *
	 * @return boolean true if we have an end date, false otherwise
	 */
	public function hasEndDate() {
		return ($this->getEndDateAsTimestamp() > 0);
	}

	/**
	 * Gets the date.
	 * Returns a localized string "will be announced" if there's no date set.
	 *
	 * Returns just one day if the timespan takes place on only one day.
	 * Returns a date range if the timespan takes several days.
	 *
	 * @param string the character or HTML entity used to separate start
	 *               date and end date
	 *
	 * @return string the seminar date
	 */
	public function getDate($dash = '&#8211;') {
		if (!$this->hasDate()) {
			$result = $this->translate('message_willBeAnnounced');
		} else {
			$beginDate = $this->getBeginDateAsTimestamp();
			$endDate = $this->getEndDateAsTimestamp();

			$beginDateDay = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$beginDate
			);
			$endDateDay = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$endDate
			);

			// Does the workshop span only one day (or is open-ended)?
			if (($beginDateDay == $endDateDay) || !$this->hasEndDate()) {
				$result = $beginDateDay;
			} else {
				if (!$this->getConfValueBoolean('abbreviateDateRanges')) {
					$result = $beginDateDay;
				} else {
					// Are the years different? Then includes the complete begin
					// date.
					if (strftime(
							$this->getConfValueString('dateFormatY'),
							$beginDate
						) !== strftime(
							$this->getConfValueString('dateFormatY'),
							$endDate)
					) {
						$result = $beginDateDay;
					} else {
						// Are the months different? Then include day and month.
						if (strftime(
								$this->getConfValueString('dateFormatM'),
								$beginDate
							) !== strftime(
								$this->getConfValueString('dateFormatM'),
								$endDate)
						) {
							$result = strftime(
								$this->getConfValueString('dateFormatMD'),
								$beginDate
							);
						} else {
							$result = strftime(
								$this->getConfValueString('dateFormatD'),
								$beginDate
							);
						}
					}
				}
				$result .= $dash.$endDateDay;
			}
		}

		return $result;
	}

	/**
	 * Checks whether there's a (begin) date set.
	 * If there's an end date but no begin date,
	 * this function still will return false.
	 *
	 * @return boolean true if we have a begin date, false otherwise.
	 */
	public function hasDate() {
		return $this->hasRecordPropertyInteger('begin_date');
	}

	/**
	 * Gets the time.
	 * Returns a localized string "will be announced" if there's no time set
	 * (i.e. both begin time and end time are 00:00).
	 * Returns only the begin time if begin time and end time are the same.
	 *
	 * @param string the character or HTML entity used to separate begin
	 *               time and end time
	 *
	 * @return string the time
	 */
	public function getTime($dash = '&#8211;') {
		if (!$this->hasTime()) {
			$result = $this->translate('message_willBeAnnounced');
		} else {
			$beginTime = strftime(
				$this->getConfValueString('timeFormat'),
				$this->getBeginDateAsTimestamp()
			);
			$endTime = strftime(
				$this->getConfValueString('timeFormat'),
				$this->getEndDateAsTimestamp()
			);

			$result = $beginTime;

			// Only display the end time if the event has an end date/time set
			// and the end time is not the same as the begin time.
			if ($this->hasEndTime() && ($beginTime !== $endTime)) {
				$result .= $dash.$endTime;
			}
		}

		return $result;
	}

	/**
	 * Checks whether there's a time set (begin time != 00:00)
	 * If there's no date/time set, the result will be false.
	 *
	 * @return boolean true if we have a begin time, false otherwise
	 */
	public function hasTime() {
		$beginTime = strftime('%H:%M', $this->getBeginDateAsTimestamp());

		return ($this->hasDate() && ($beginTime !== '00:00'));
	}

	/**
	 * Checks whether there's an end time set (end time != 00:00)
	 * If there's no end date/time set, the result will be false.
	 *
	 * @return boolean true if we have an end time, false otherwise
	 */
	public function hasEndTime() {
		$endTime = strftime('%H:%M', $this->getEndDateAsTimestamp());

		return ($this->hasEndDate() && ($endTime !== '00:00'));
	}

	/**
	 * Returns our begin date and time as a UNIX timestamp.
	 *
	 * @return integer our begin date and time as a UNIX timestamp or 0 if
	 *                 we don't have a begin date
	 *
	 * @access protected
	 */
	function getBeginDateAsTimestamp() {
		return $this->getRecordPropertyInteger('begin_date');
	}

	/**
	 * Returns our end date and time as a UNIX timestamp.
	 *
	 * @return integer our end date and time as a UNIX timestamp or 0 if
	 *                 we don't have an end date
	 *
	 * @access protected
	 */
	function getEndDateAsTimestamp() {
		return $this->getRecordPropertyInteger('end_date');
	}

	/**
	 * Gets our end date and time as a UNIX timestamp. If this event is
	 * open-ended, midnight after the begin date and time is returned.
	 * If we don't even have a begin date, 0 is returned.
	 *
	 * @return integer our end date and time as a UNIX timestamp, 0 if
	 *                 we don't have a begin date
	 *
	 * @access protected
	 */
	function getEndDateAsTimestampEvenIfOpenEnded() {
		$result = 0;

		if ($this->hasBeginDate()) {
			if (!$this->isOpenEnded()) {
				$result = $this->getEndDateAsTimestamp();
			} else {
				$splitBeginDate = getdate($this->getBeginDateAsTimestamp());
				$result = mktime(
					0, 0, 0,
					$splitBeginDate['mon'],
					$splitBeginDate['mday'] + 1,
					$splitBeginDate['year']
				);
			}
		}

		return $result;
	}

	/**
	 * Gets the seminar room (not the site).
	 *
	 * @return string the seminar room (may be empty)
	 */
	public function getRoom() {
		return $this->getRecordPropertyString('room');
	}

	/**
	 * Checks whether we have a room set.
	 *
	 * @return boolean true if we have a non-empty room, false otherwise.
	 */
	public function hasRoom() {
		return $this->hasRecordPropertyString('room');
	}

	/**
	 * Checks whether this time span is open-ended.
	 *
	 * A time span is considered to be open-ended if it does not have an end
	 * date.
	 *
	 * @return boolean true if this time span is open-ended, false otherwise
	 */
	public function isOpenEnded() {
		return !$this->hasEndDate();
	}

	/**
	 * Checks whether we have a place (or places) set.
	 *
	 * @return boolean true if we have a non-empty places list, false otherwise
	 */
	public function hasPlace() {
		return $this->hasRecordPropertyInteger('place');
	}

	/**
	 * Gets the number of places associated with this record.
	 *
	 * @return integer the number of places associated with this record,
	 *                 will be >= 0
	 */
	public function getNumberOfPlaces() {
		return $this->getRecordPropertyInteger('place');
	}

	/**
	 * Gets our place(s) as plain text (just the places name).
	 * Returns a localized string "will be announced" if the time slot has no
	 * place set.
	 *
	 * @return string our places or an empty string if the timespan has
	 *                no places
	 */
	public abstract function getPlaceShort();
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timespan.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timespan.php']);
}
?>