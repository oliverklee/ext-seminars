<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_seminar.php');

/**
 * Class 'frontEndCountdown' for the 'seminars' extension.
 *
 * This class represents a countdown to the next upcoming event.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Mario Rimann <typo3-coding@rimann.org>
 */
class tx_seminars_frontEndCountdown extends tx_seminars_templatehelper {
	/**
	 * @var string same as plugin class name
	 */
	public $prefixId = 'tx_seminars_pi1';

	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi1/class.tx_seminars_frontEndCountdown.php';

	/**
	 * @var tx_seminars_seminar the seminar for which we want to show the
	 *                          countdown
	 */
	private $seminar = null;

	/**
	 * @var boolean true if the current object is in test mode, false otherwise
	 */
	private $testMode = false;

	/**
	 * The constructor. Initializes the TypoScript configuration, initializes
	 * the flex forms, gets the template HTML code, sets the localized labels
	 * and set the CSS classes from TypoScript.
	 *
	 * @param array TypoScript configuration for the plugin
	 * @param tslib_cObj the parent cObj, needed for the flexforms
	 */
	public function __construct($configuration, tslib_cObj $cObj) {
		$this->cObj = $cObj;
		$this->init($configuration);
		$this->pi_initPIflexForm();

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		if ($this->seminar) {
			$this->seminar->__destruct();
		}

		unset($this->seminar);
		parent::__destruct();

	}

	/**
	 * Creates a seminar in $this->seminar.
	 *
	 * @param integer an event UID, must be >= 0
	 */
	private function createSeminar($seminarUid) {
		$seminarClassName = t3lib_div::makeInstanceClassName(
			'tx_seminars_seminar'
		);
		$this->seminar = new $seminarClassName($seminarUid);
	}

	/**
	 * Creates a countdown to the next upcoming event.
	 *
	 * @return string HTML code of the countdown or a message if no upcoming
	 *                event found
	 */
	public function render() {
		$now = $GLOBALS['SIM_ACCESS_TIME'];

		$additionalWhere = 'tx_seminars_seminars.cancelled=0' .
			tx_oelib_db::enableFields(SEMINARS_TABLE_SEMINARS) .
			' AND ' . SEMINARS_TABLE_SEMINARS . '.object_type!=' .
			SEMINARS_RECORD_TYPE_TOPIC . ' AND ' . SEMINARS_TABLE_SEMINARS .
			'.begin_date>' . $now;

		if ($this->testMode) {
			$additionalWhere .= ' AND is_dummy_record=1';
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid', SEMINARS_TABLE_SEMINARS, $additionalWhere,
			'', 'begin_date ASC', '1'
		);
		if (!$dbResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}
		$dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);

		if ($dbResultRow) {
			$this->createSeminar($dbResultRow['uid']);

			// Lets warnings from the seminar bubble up to us.
			$this->setErrorMessage(
				$this->seminar->checkConfiguration(true)
			);

			// Calculates the time left until the event starts.
			$eventStartTime = $this->seminar->getBeginDateAsTimestamp();
			$timeLeft = $eventStartTime - $now;

			$message = $this->createCountdownMessage($timeLeft);
		} else {
			$message = $this->translate('message_countdown_noEventFound');
		}

		$this->setMarker('count_down_message', $message);
		$result = $this->getSubpart('COUNTDOWN');

		$this->checkConfiguration();
		$result .= $this->getWrappedConfigCheckMessage();

		return $result;
	}

	/**
	 * Returns a localized string representing an amount of seconds in words.
	 * For example:
	 * 150000 seconds -> "1 day"
	 * 200000 seconds -> "2 days"
	 * 50000 seconds -> "13 hours"
	 * The function uses localized strings and also looks for proper usage of
	 * singular/plural.
	 *
	 * @param integer the amount of seconds to rewrite into words
	 *
	 * @return string a localized string representing the time left until the
	 *                event starts
	 */
	private function createCountdownMessage($seconds) {
		if ($seconds > 82800) {
			// more than 23 hours left, show the time in days
			$countdownValue = round($seconds / ONE_DAY);
			if ($countdownValue > 1) {
				$countdownText = $this->translate('countdown_days_plural');
			} else {
				$countdownText = $this->translate('countdown_days_singular');
			}
		} elseif ($seconds > 3540) {
			// more than 59 minutes left, show the time in hours
			$countdownValue = round($seconds / 3600);
			if ($countdownValue > 1) {
				$countdownText = $this->translate('countdown_hours_plural');
			} else {
				$countdownText = $this->translate('countdown_hours_singular');
			}
		} elseif ($seconds > 59) {
			// more than 59 seconds left, show the time in minutes
			$countdownValue = round($seconds / 60);
			if ($countdownValue > 1) {
				$countdownText = $this->translate('countdown_minutes_plural');
			} else {
				$countdownText = $this->translate('countdown_minutes_singular');
			}
		} else {
			// less than 60 seconds left, show the time in seconds
			$countdownValue = $seconds;
			$countdownText = $this->translate('countdown_seconds_plural');
		}

		return sprintf(
			$this->translate('message_countdown'),
			$countdownValue,
			$countdownText
		);
	}

	/**
	 * Enables the test mode for the current object.
	 */
	public function setTestMode() {
		$this->testMode = true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_frontEndCountdown.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_frontEndCountdown.php']);
}
?>