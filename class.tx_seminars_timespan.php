<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Niels Pardon (mail@niels-pardon.de)
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
 * This class offers timespan-related methods for the timeslot and seminar classes.
 *
 * @author	Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');

class tx_seminars_timespan extends tx_seminars_objectfromdb {
	/** same as class name */
	var $prefixId = 'tx_seminars_timespan';
	/**  path to this script relative to the extension dir */
	var $scriptRelPath = 'class.tx_seminars_timespan.php';

	/**
	 * Gets the begin date.
	 *
	 * @return	string		the begin date (or the localized string "will be 
	 * 						announced" if no begin date is set)
	 *
	 * @access	public
	 */
	function getBeginDate() {
		if (!$this->hasBeginDate()) {
			$result = $this->pi_getLL('message_willBeAnnounced');
		} else {
			$beginDate = $this->getRecordPropertyInteger('begin_date');
			$result = strftime($this->getConfValueString('dateFormatYMD'), $beginDate);
		}

		return $result;
	}

	/**
	 * Checks whether there's a begin date set.
	 *
	 * @return	boolean		true if we have a begin date, false otherwise
	 *
	 * @access	public
	 */
	function hasBeginDate() {
		return $this->hasRecordPropertyInteger('begin_date');
	}

	/**
	 * Gets the end date.
	 *
	 * @return	string		the end date (or the localized string "will be
	 * 						announced" if no end date is set)
	 *
	 * @access	public
	 */
	function getEndDate() {
		if (!$this->hasEndDate()) {
			$result = $this->pi_getLL('message_willBeAnnounced');
		} else {
			$endDate = $this->getRecordPropertyInteger('end_date');
			$result = strftime($this->getConfValueString('dateFormatYMD'), $endDate);
		}

		return $result;
	}

	/**
	 * Checks whether there's an end date set.
	 *
	 * @return	boolean		true if we have an end date, false otherwise.
	 *
	 * @access	public
	 */
	function hasEndDate() {
		return $this->hasRecordPropertyInteger('end_date');
	}

	/**
	 * Gets the date.
	 * Returns a localized string "will be announced" if there's no date set.
	 *
	 * Returns just one day if the timespan takes place on only one day.
	 * Returns a date range if the timespan takes several days.
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

			$beginDateDay = strftime($this->getConfValueString('dateFormatYMD'), $beginDate);
			$endDateDay = strftime($this->getConfValueString('dateFormatYMD'), $endDate);

			// Does the workshop span only one day (or is open-ended)?
			if (($beginDateDay == $endDateDay) || !$this->hasEndDate()) {
				$result = $beginDateDay;
			} else {
				if (!$this->getConfValueBoolean('abbreviateDateRanges')) {
					$result = $beginDateDay;
				} else {
					// Are the years different? Then include the complete begin date.
					if (strftime($this->getConfValueString('dateFormatY'), $beginDate) !== strftime($this->getConfValueString('dateFormatY'), $endDate)) {
						$result = $beginDateDay;
					} else {
						// Are the months different? Then include day and month.
						if (strftime($this->getConfValueString('dateFormatM'), $beginDate) !== strftime($this->getConfValueString('dateFormatM'), $endDate)) {
							$result = strftime($this->getConfValueString('dateFormatMD'), $beginDate);
						} else {
							$result = strftime($this->getConfValueString('dateFormatD'), $beginDate);
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
	 * @return	boolean		true if we have a begin date, false otherwise.
	 *
	 * @access	public
	 */
	function hasDate() {
		return $this->hasRecordPropertyInteger('begin_date');
	}

	/**
	 * Gets the time.
	 * Returns a localized string "will be announced" if there's no time set
	 * (i.e. both begin time and end time are 00:00).
	 * Returns only the begin time if begin time and end time are the same.
	 *
	 * @param	string		the character or HTML entity used to separate begin time and end time
	 *
	 * @return	string		the time
	 *
	 * @access	public
	 */
	function getTime($dash = '&#8211;') {
		if (!$this->hasTime()) {
			$result = $this->pi_getLL('message_willBeAnnounced');
		} else {
			$beginDate = $this->getRecordPropertyInteger('begin_date');
			$endDate = $this->getRecordPropertyInteger('end_date');

			$beginTime = strftime($this->getConfValueString('timeFormat'), $beginDate);
			$endTime = strftime($this->getConfValueString('timeFormat'), $endDate);

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
	 * @return	boolean		true if we have a begin time, false otherwise
	 *
	 * @access	public
	 */
	function hasTime() {
		$beginTime = strftime('%H:%M', $this->getRecordPropertyInteger('begin_date'));

		return ($this->hasDate() && ($beginTime !== '00:00'));
	}

	/**
	 * Checks whether there's an end time set (end time != 00:00)
	 * If there's no end date/time set, the result will be false.
	 *
	 * @return	boolean		true if we have an end time, false otherwise
	 *
	 * @access	public
	 */
	function hasEndTime() {
		$endTime = strftime('%H:%M', $this->getRecordPropertyInteger('end_date'));

		return ($this->hasEndDate() && ($endTime !== '00:00'));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timespan.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timespan.php']);
}

?>
