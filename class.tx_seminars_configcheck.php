<?php
/***************************************************************
* Copyright notice
*
* (c) 2006 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_confcheck' for the 'seminars' extension.
 *
 * This class checks the Seminar Manager configuration for basic sanity.
 *
 * The correct functioning of this class does not rely on any HTML templates or
 * language files so it works even under the worst of circumstances.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_oe_configcheck.php');

class tx_seminars_configcheck extends tx_seminars_oe_configcheck {
	/**
	 * Checks the configuration for: tx_seminars_registrationmanager/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_registrationmanager() {
		// The registration manager needs to be able to create registration
		// objects. So we check whether the prerequisites for registrations
		// are fullfilled as well.
		$this->check_tx_seminars_registration();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_seminar/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_seminar() {
		$this->checkStaticIncluded();
		$this->checkSalutationMode();
		$this->checkTimeAndDate();
		$this->checkIfBoolean(
			'showTimeOfRegistrationDeadline',
			false,
			'',
			'This value specifies whether to also show the time of '
				.'registration deadlines. If this value is incorrect, the '
				.'might get shown although this is not intended '
				.'(or vice versa).'
		);
		$this->checkIfInteger(
			'showVacanciesThreshold',
			false,
			'',
			'This value specifies down from which threshold the exact number '
				.'of vancancies will be displayed. If this value is incorrect, '
				.'the number might get shown although this is not intended '
				.'(or vice versa).'
		);

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_registration/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_registration() {
		$this->checkStaticIncluded();
		$this->checkTemplateFile();
		$this->checkSalutationMode();
		$this->checkThankYouMail();
		$this->checkIfBoolean(
			'generalPriceInMail',
			false,
			'',
			'This value specifies which wording to use for the standard price '
				.'in e-mails. If this value is incorrect, the wrong wording '
				.'might get used.'
		);
		$this->checkNotificationMail();
		if ($this->objectToCheck->getConfValueInteger('enableRegistration')) { 
			$this->checkIfPositiveInteger(
				'attendancesPID',
				false,
				'',
				'This value specifies the page on which registrations will be '
					.'stored. If this value is not set correctly, registration '
					.'records will be dumped in the TYPO3 root page. If you '
					.'ecplicitely do not wish to use the online registration '
					.'feature, you can disable these checks by setting '
					.'<strong>plugin.tx_seminars.enableRegistration</strong> and '
					.'<strong>plugin.tx_seminars.enableRegistration_pi1</strong> '
					.'to 0.'
			);
		}

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_seminarbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_seminarbag() {
		$this->checkStaticIncluded();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_registrationbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_registrationbag() {
		$this->checkStaticIncluded();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/seminar_registration.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_seminar_registration() {
		$this->checkStaticIncluded();
		$this->checkTemplateFile(true);
		$this->checkSalutationMode(true);

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/single_view.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_single_view() {
		$this->checkStaticIncluded();
		$this->checkTemplateFile(true);
		$this->checkSalutationMode(true);

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/seminar_list.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_seminar_list() {
		$this->checkStaticIncluded();
		$this->checkTemplateFile(true);
		$this->checkSalutationMode(true);

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/my_vip_events.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_my_vip_events() {
		$this->check_tx_seminars_pi1_seminar_list();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/my_events.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_my_events() {
		$this->check_tx_seminars_pi1_seminar_list();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/list_registrations.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_list_registrations() {
		$this->checkStaticIncluded();
		$this->checkTemplateFile(true);
		$this->checkSalutationMode(true);

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/list_vip_registrations.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_list_vip_registrations() {
		$this->check_tx_seminars_pi1_list_registrations();

		return;
	}

	/**
	 * Checks the configuration related to thank-you e-mails.
	 *
	 * @access	private
	 */
	function checkThankYouMail() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFieldsInThankYouMail',
			false,
			'',
			'These values specify the sections to hide in e-mails to '
				.'participants. A mistyped field name will cause the field to '
				.'be included nonetheless.',
			array(
				'hello',
				'title',
				'accreditation_number',
				'credit_points',
				'date',
				'time',
				'place',
				'room',
				'price_regular',
				'price_special',
				'paymentmethods',
				'url',
				'footer'
			)
		);

		return;
	}

	/**
	 * Checks the configuration related to notification e-mails.
	 *
	 * @access	private
	 */
	function checkNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFieldsInNotificationMail',
			false,
			'',
			'These values specify the sections to hide in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'be included nonetheless.',
			array(
				'hello',
				'summary',
				'seminardata',
				'feuserdata',
				'attendancedata'
			)
		);
		$this->checkIfMultiInSetOrEmpty(
			'showSeminarFieldsInNotificationMail',
			false,
			'',
			'These values specify the event fields to show in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'not get included.',
			array(
				'uid',
				'event_type',
				'title',
				'subtitle',
				'titleanddate',
				'date',
				'time',
				'room',
				'place',
				'speakers',
				'price_regular',
				'price_special',
				'attendees',
				'vacancies',
				'enough_attendees',
				'is_full'
			)
		);
		$this->checkIfMultiInTableOrEmpty(
			'showFeUserFieldsInNotificationMail',
			false,
			'',
			'These values specify the FE user fields to show in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'not get included.',
			'fe_users'
		);
		$this->checkIfMultiInSetOrEmpty(
			'showAttendanceFieldsInNotificationMail',
			false,
			'',
			'These values specify the registration fields to show in e-mails '
				.'to organizers. A mistyped field name will cause the field '
				.'to not get included.',
			array(
				'interests',
				'expectations',
				'background_knowledge',
				'accommodation',
				'food',
				'known_from',
				'notes',
				'seats'
			)
		);
		$this->checkIfBoolean(
			'sendAdditionalNotificationEmails',
			false,
			'',
			'This value specifies whether organizers receive additional '
				.'notification e-mails. If this value is incorrect, e-mails '
				.'might get sent when this is not intended (or vice versa).'
		);

		return;
	}

	/**
	 * Checks the settings for time and date format.
	 */
	function checkTimeAndDate() {
		$explanation = 'This determines the way dates and times are '
			.'displayed. If this is not set correctly, dates and times might '
			.'be mangled or not get displayed at all.';
		$configVariables = array(
			'timeFormat',
			'dateFormatY',
			'dateFormatM',
			'dateFormatD',
			'dateFormatYMD',
			'dateFormatMD'
		);
		foreach ($configVariables as $configVariableToCheck) {
			$this->checkForNonEmptyString(
				$configVariableToCheck,
				false,
				'',
				$explanation
			);
		}

		$this->checkIfBoolean(
			'abbreviateDateRanges',
			false,
			'',
			'This value specifies whether date ranges will be abbreviated. '
				.'If this value is incorrect, the values might be abbreviated '
				.'although this is not intended (or vice versa).'
		);

		return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']);
}

?>
