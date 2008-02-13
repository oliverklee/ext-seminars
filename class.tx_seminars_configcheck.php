<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
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
		$this->checkShowTimeOfRegistrationDeadline();
		$this->checkShowTimeOfEarlyBirdDeadline();
		$this->checkShowVacanciesThreshold();
		$this->checkDecimalDigits();
		$this->checkDecimalSplitChar();
		$this->checkShowToBeAnnouncedForEmptyPrice();
		$this->checkEventType();
		$this->checkUnregistrationDeadlineDaysBeforeBeginDate();
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

		$this->checkRegistrationFlag();

		$this->checkThankYouMail();
		$this->checkGeneralPriceInMail();
		$this->checkNotificationMail();
		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkAttendancesPid();
		}
	}

	/**
	 * Checks the configuration for: tx_seminars_seminarbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_seminarbag() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_registrationbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_registrationbag() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_speakerbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_speakerbag() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_speaker/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_speaker() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_organizerbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_organizerbag() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_organizer/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_organizer() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_placebag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_placebag() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_place/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_place() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_timeslot/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_timeslot() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_test/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_test() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_testbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_testbag() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_category/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_category() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_categorybag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_categorybag() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/seminar_registration.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_seminar_registration() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();
		if (!$this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$message = 'You are using the registration page although online '
				.'registration is disabled. This will break the registration '
				.'page and the automatic configuration check. '
				.'Please either enable online registration by setting the TS '
				.'setup variable <strong>'.$this->getTSSetupPath()
				.'enableRegistration</strong> to <strong>1</strong> or remove '
				.'the registration page.';
			$this->setErrorMessage($message);
		}

		$this->checkBaseUrl();
		$this->checkRegistrationEditorTemplateFile();

		$this->checkNumberOfClicksForRegistration();
		$this->checkNumberOfFirstRegistrationPage();
		$this->checkNumberOfLastRegistrationPage();
		$this->checkGeneralPriceInSingle();
		$this->checkEventFieldsOnRegistrationPage();
		$this->checkShowRegistrationFields();
		$this->checkThankYouAfterRegistrationPID();
		$this->checkSendParametersToThankYouAfterRegistrationPageUrl();
		$this->checkPageToShowAfterUnregistrationPID();
		$this->checkSendParametersToPageToShowAfterUnregistrationUrl();
		$this->checkListPid();
		$this->checkLoginPid();
		$this->checkBankTransferUid();
		$this->checkLogOutOneTimeAccountsAfterRegistration();
		$this->checkMyEventsPid();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/single_view.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_single_view() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();

		$this->checkHideFields();
		$this->checkGeneralPriceInSingle();
		$this->checkShowSpeakerDetails();
		$this->checkShowSiteDetails();
		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkRegisterPid();
			$this->checkLoginPid();
		}
		$this->checkRegistrationsListPidOptional();
		$this->checkRegistrationsVipListPidOptional();
		$this->checkDetailPid();
		$this->checkDefaultEventVipsFeGroupID();
		$this->checkExternalLinkTarget();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/seminar_list.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_seminar_list() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();

		$this->checkPages();
		$this->checkRecursive();
		$this->checkListView(array_keys($this->objectToCheck->orderByList));

		$this->checkHideColumns();
		$this->checkTimeframeInList();
		$this->checkHideSelectorWidget();
		$this->checkShowEmptyEntryInOptionLists();
		$this->checkHideSearchForm();
		$this->checkHidePageBrowser();
		$this->checkHideCanceledEvents();
		$this->checkGeneralPriceInList();
		$this->checkOmitDateIfSameAsPrevious();
		$this->checkListPid();
		$this->checkDetailPid();
		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkRegisterPid();
		}
		$this->checkRegistrationsListPidOptional();
		$this->checkRegistrationsVipListPidOptional();
		$this->checkDefaultEventVipsFeGroupID();
	}

 	/**
	 * Checks the configuration for: tx_seminars_pi1/countdown.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_countdown() {
		$this->checkCommonFrontEndSettings();
		$this->checkPages();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/my_vip_events.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_my_vip_events() {
		$this->check_tx_seminars_pi1_seminar_list();
		$this->checkRegistrationsVipListPid();
		$this->checkDefaultEventVipsFeGroupID();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/topic_list.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_topic_list() {
		$this->check_tx_seminars_pi1_seminar_list();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/my_events.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_my_events() {
		$this->check_tx_seminars_pi1_seminar_list();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/list_registrations.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_list_registrations() {
		$this->checkCommonFrontEndSettings();

		$this->checkShowFeUserFieldsInRegistrationsList();
		$this->checkListPid();
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/list_vip_registrations.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_list_vip_registrations() {
		$this->check_tx_seminars_pi1_list_registrations();
	}

	/**
	 * Checks the configuration for: check_tx_seminars_pi1/edit_event.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_edit_event() {
		$this->checkCommonFrontEndSettings();

		$this->checkEventEditorFeGroupID();
		$this->checkCreateEventsPID();
		$this->checkEventSuccessfullySavedPID();
	}

	/**
	 * Checks the configuration for: check_tx_seminars_pi1/my_entered_events.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_my_entered_events() {
		$this->check_tx_seminars_pi1_seminar_list();
		$this->checkEventEditorFeGroupID();
		$this->checkEventEditorPID();
	}

	/**
	 * Checks the configuration related to thank-you e-mails.
	 *
	 * @access	private
	 */
	function checkThankYouMail() {
		$this->checkCharsetForEMails();
		$this->checkHideFieldsInThankYouMail();
		$this->checkSendConfirmation();
		$this->checkSendConfirmationOnQueueUpdate();
		$this->checkSendConfirmationOnRegistrationForQueue();
		$this->checkSendConfirmationOnUnregistration();
	}

	/**
	 * Checks the configuration related to notification e-mails.
	 *
	 * @access	private
	 */
	function checkNotificationMail() {
		$this->checkCharsetForEMails();
		$this->checkHideFieldsInNotificationMail();
		$this->checkShowSeminarFieldsInNotificationMail();
		$this->checkShowFeUserFieldsInNotificationMail();
		$this->checkShowAttendanceFieldsInNotificationMail();
		$this->checkSendAdditionalNotificationEmails();
		$this->checkSendNotification();
		$this->checkSendNotificationOnQueueUpdate();
		$this->checkSendNotificationOnRegistrationForQueue();
		$this->checkSendNotificationOnUnregistration();
	}

	/**
	 * Checks the settings for time and date format.
	 *
	 * @access	private
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

		$this->checkAbbreviateDateRanges();
	}

	/**
	 * Checks the setting of the configuration value baseUrl.
	 *
	 * @see		http://www.ietf.org/rfc/rfc2396.txt
	 *
	 * @access	private
	 */
	function checkBaseUrl() {
		// The regular expression used for the host name mostly conforms with
		// http://www.ietf.org/rfc/rfc2396.txt.
		$this->checkRegExpNotEmpty(
			'baseURL',
			true,
			's_template_special',
			'This value specifies the base URL that will be used to create '
				.'links in e-mails. The base URL must include the protocol '
				.'(http:// or https://) and the trailing slash. '
				.'If this value is incorrect, invalid URLs will be created '
				.'in e-mails to the participants.',
			// the protocol
			'/^http(s?):\/\/'
				.'('
					// either a domain name ...
					.'(([a-z\d]|[a-z\d][a-z\d\-]*[a-z\d])\.)*'
						// ... with a top level domain (or a host at the local
						// network) at the end
						.'([a-z][a-z\d\-]*[a-z\d])'
					// or an IPv4 address
					.'|\d+\.\d+\.\d+\.\d+'
				.')'
				// a port (optional)
				.'(:\d+)?'
				.'\/'
				// any number of path segments (including none)
				.'([a-zA-Z\d_\-\.]+\/)'
				.'*$/'
		);
	}

	/**
	 * Checks the setting of the configuration value baseUrl.
	 *
	 * @access	private
	 */
	function checkRegistrationFlag() {
		$this->checkIfBoolean(
			'enableRegistration',
			false,
			'',
			'This value specifies whether the extension will provide online '
				.'registration. If this value is incorrect, the online '
				.'registration will not be enabled or disabled correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value decimalDigits.
	 *
	 * @access	private
	 */
	function checkDecimalDigits() {
		$explanation = 'This value specifies the amount of digits displayed '
			.'behind the decimal point in prices. If this value is incorrect, '
			.'prices may have an unexpected look.';
		$this->checkForNonEmptyString(
			'decimalDigits',
			false,
			'',
			$explanation
		);
		$this->checkIfInteger(
			'decimalDigits',
			false,
			'',
			$explanation
		);
	}

	/**
	 * Checks the setting of the configuration value decimalSplitChar.
	 *
	 * @access	private
	 */
	function checkDecimalSplitChar() {
		$this->checkForNonEmptyString(
			'decimalSplitChar',
			false,
			'',
			'This value specifies the char that is used to split the price. '
			.'If this value is empty all prices will be shown wrong (missing '
			.'decimal point).'
		);
	}

	/**
	 * Checks the setting of the configuration value what_to_display.
	 *
	 * @access	private
	 */
	function checkWhatToDisplay() {
		$this->checkIfSingleInSetNotEmpty(
			'what_to_display',
			true,
			'sDEF',
			'This value specifies the type of seminar manager plug-in to '
				.'display. If this value is not set correctly, the wrong '
				.'type of plug-in will be displayed.',
			array(
				'seminar_list',
				'topic_list',
				'my_events',
				'my_vip_events',
				'seminar_registration',
				'list_registrations',
				'list_vip_registrations',
				'edit_event',
				'my_entered_events',
				'countdown'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showTimeOfRegistrationDeadline.
	 *
	 * @access	private
	 */
	function checkShowTimeOfRegistrationDeadline() {
		$this->checkIfBoolean(
			'showTimeOfRegistrationDeadline',
			false,
			'',
			'This value specifies whether to also show the time of '
				.'registration deadlines. If this value is incorrect, the '
				.'time might get shown although this is not intended '
				.'(or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showTimeOfEarlyBirdDeadline.
	 *
	 * @access	private
	 */
	function checkShowTimeOfEarlyBirdDeadline() {
		$this->checkIfBoolean(
			'showTimeOfEarlyBirdDeadline',
			false,
			'',
			'This value specifies whether to also show the time of '
				.'early bird deadlines. If this value is incorrect, the '
				.'time might get shown although this is not intended '
				.'(or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showVacanciesThreshold.
	 *
	 * @access	private
	 */
	function checkShowVacanciesThreshold() {
		$this->checkIfInteger(
			'showVacanciesThreshold',
			false,
			'',
			'This value specifies down from which threshold the exact number '
				.'of vancancies will be displayed. If this value is incorrect, '
				.'the number might get shown although this is not intended '
				.'(or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value generalPriceInMail.
	 *
	 * @access	private
	 */
	function checkGeneralPriceInMail() {
		$this->checkIfBoolean(
			'generalPriceInMail',
			false,
			'',
			'This value specifies which wording to use for the standard price '
				.'in e-mails. If this value is incorrect, the wrong wording '
				.'might get used.'
		);
	}

	/**
	 * Checks the setting of the configuration value attendancesPID.
	 *
	 * @access	private
	 */
	function checkAttendancesPid() {
		$this->checkIfSingleSysFolderNotEmpty(
			'attendancesPID',
			false,
			'',
			'This value specifies the page on which registrations will be '
				.'stored. If this value is not set correctly, registration '
				.'records will be dumped in the TYPO3 root page. If you '
				.'explicitely do not wish to use the online registration '
				.'feature, you can disable these checks by setting '
				.'<strong>plugin.tx_seminars.enableRegistration</strong> and '
				.'<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
				.'to 0.'
		);
	}

	/**
	 * Checks the setting of the configuration value hideFields.
	 *
	 * @access	private
	 */
	function checkHideFields() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFields',
			true,
			's_template_special',
			'This value specifies which section to remove from the details view. '
				.'Incorrect values will cause the sections to still be displayed.',
			array(
				'title',
				'subtitle',
				'description',
				'accreditation_number',
				'credit_points',
				'category',
				'date',
				'timeslots',
				'uid',
				'time',
				'place',
				'room',
				'additional_times_places',
				'speakers',
				'language',
				'partners',
				'tutors',
				'leaders',
				'price_regular',
				// We use "price_board_regular" instead of "price_regular_board"
				// to keep the subpart names prefix-free.
				'price_board_regular',
				'price_special',
				// Ditto for "price_board_special".
				'price_board_special',
				'paymentmethods',
				'additional_information',
				'target_groups',
				'organizers',
				'vacancies',
				'deadline_registration',
				'otherdates',
				'eventsnextday',
				'registration',
				'back'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value hideColumns.
	 *
	 * @access	private
	 */
	function checkHideColumns() {
		$this->checkIfMultiInSetOrEmpty(
			'hideColumns',
			true,
			's_template_special',
			'This value specifies which columns to remove from the list view. '
				.'Incorrect values will cause the colums to still be displayed.',
			array(
				'category',
				'title',
				'subtitle',
				'uid',
				'event_type',
				'accreditation_number',
				'credit_points',
				'teaser',
				'speakers',
				'language',
				'date',
				'time',
				'place',
				'city',
				'seats',
				'country',
				'price_regular',
				'price_special',
				'total_price',
				'organizers',
				'target_groups',
				'vacancies',
				'status_registration',
				'registration',
				'list_registrations',
				'edit'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value timeframeInList.
	 *
	 * @access	private
	 */
	function checkTimeframeInList() {
		$this->checkIfSingleInSetNotEmpty(
			'timeframeInList',
			true,
			's_template_special',
			'This value specifies the time-frame from which events should be '
				.'displayed in the list view. An incorrect value will events '
				.'from a different time-frame cause to be displayed and other '
				.'events to not get displayed.',
			array(
				'all',
				'past',
				'pastAndCurrent',
				'current',
				'currentAndUpcoming',
				'upcoming',
				'deadlineNotOver'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value hideSelectorWidget.
	 *
	 * @access	private
	 */
	function checkHideSelectorWidget() {
		$this->checkIfBoolean(
			'hideSelectorWidget',
			true,
			's_template_special',
			'This value specifies whether the selector widget in the list view '
				.'will be displayed. If this value is incorrect, the selector '
				.'widget might get displayed when this is not intended (or '
				.'vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showEmptyEntryInOptionLists.
	 *
	 * @access	private
	 */
	function checkShowEmptyEntryInOptionLists() {
		$this->checkIfBoolean(
			'showEmptyEntryInOptionLists',
			true,
			's_template_special',
			'This value specifies whether the option boxes in the selector widget '
				.'will contain a dummy entry called "not selected". This is only '
				.'needed if you changed the HTML template to show the selectors '
				.'as dropdown menues. If this value is incorrect, the dummy entry '
				.'might get displayed when this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value hideSearchForm.
	 *
	 * @access	private
	 */
	function checkHideSearchForm() {
		$this->checkIfBoolean(
			'hideSearchForm',
			true,
			's_template_special',
			'This value specifies whether the search form in the list view '
				.'will be displayed. If this value is incorrect, the search '
				.'form might get displayed when this is not intended (or '
				.'vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value hidePageBrowser.
	 *
	 * @access	private
	 */
	function checkHidePageBrowser() {
		$this->checkIfBoolean(
			'hidePageBrowser',
			true,
			's_template_special',
			'This value specifies whether the page browser in the list view '
				.'will be displayed. If this value is incorrect, the page '
				.'browser might get displayed when this is not intended (or '
				.'vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value hideCanceledEvents.
	 *
	 * @access	private
	 */
	function checkHideCanceledEvents() {
		$this->checkIfBoolean(
			'hideCanceledEvents',
			true,
			's_template_special',
			'This value specifies whether canceled events will be removed '
				.'from the list view. If this value is incorrect, canceled '
				.'events might get displayed when this is not intended (or '
				.'vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value hideFieldsInThankYouMail.
	 *
	 * @access	private
	 */
	function checkHideFieldsInThankYouMail() {
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
				'uid',
				'ticket_id',
				'price',
				'seats',
				'total_price',
				'attendees_names',
				'lodgings',
				'foods',
				'checkboxes',
				'kids',
				'accreditation_number',
				'credit_points',
				'date',
				'time',
				'place',
				'room',
				'additional_times_places',
				'paymentmethod',
				'billing_address',
				'url',
				'footer'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value hideFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkHideFieldsInNotificationMail() {
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
	}

	/**
	 * Checks the setting of the configuration value showSeminarFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkShowSeminarFieldsInNotificationMail() {
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
				'accreditation_number',
				'credit_points',
				'room',
				'place',
				'speakers',
				'price_regular',
				'price_regular_early',
				'price_special',
				'price_special_early',
				'allows_multiple_registrations',
				'attendees',
				'attendees_min',
				'attendees_max',
				'vacancies',
				'enough_attendees',
				'is_full',
				'notes'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showFeUserFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkShowFeUserFieldsInNotificationMail() {
		$this->checkIfMultiInTableOrEmpty(
			'showFeUserFieldsInNotificationMail',
			false,
			'',
			'These values specify the FE user fields to show in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'not get included.',
			'fe_users'
		);
	}

	/**
	 * Checks the setting of the configuration value showAttendanceFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkShowAttendanceFieldsInNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'showAttendanceFieldsInNotificationMail',
			false,
			'',
			'These values specify the registration fields to show in e-mails '
				.'to organizers. A mistyped field name will cause the field '
				.'to not get included.',
			array(
				'uid',
				'interests',
				'expectations',
				'background_knowledge',
				'lodgings',
				'accommodation',
				'foods',
				'food',
				'known_from',
				'notes',
				'checkboxes',
				'price',
				'seats',
				'total_price',
				'attendees_names',
				'kids',
				'method_of_payment',
				'gender',
				'name',
				'address',
				'zip',
				'city',
				'country',
				'telephone',
				'email'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value sendAdditionalNotificationEmails.
	 *
	 * @access	private
	 */
	function checkSendAdditionalNotificationEmails() {
		$this->checkIfBoolean(
			'sendAdditionalNotificationEmails',
			false,
			'',
			'This value specifies whether organizers receive additional '
				.'notification e-mails. If this value is incorrect, e-mails '
				.'might get sent when this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value abbreviateDateRanges.
	 *
	 * @access	private
	 */
	function checkAbbreviateDateRanges() {
		$this->checkIfBoolean(
			'abbreviateDateRanges',
			false,
			'',
			'This value specifies whether date ranges will be abbreviated. '
				.'If this value is incorrect, the values might be abbreviated '
				.'although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value generalPriceInList.
	 *
	 * @access	private
	 */
	function checkGeneralPriceInList() {
		$this->checkIfBoolean(
			'generalPriceInList',
			true,
			's_template_special',
			'This value specifies whether the column header for the standard '
				.'price in the list view will be just <em>Price</em> instead '
				.'of <em>Standard price</em>. '
				.'If this value is incorrect, the wrong label might be used.'
		);
	}

	/**
	 * Checks the setting of the configuration value generalPriceInSingle.
	 *
	 * @access	private
	 */
	function checkGeneralPriceInSingle() {
		$this->checkIfBoolean(
			'generalPriceInSingle',
			true,
			's_template_special',
			'This value specifies whether the heading for the standard price '
				.'in the detailed view and on the registration page will be '
				.'just <em>Price</em> instead of <em>Standard price</em>. '
				.'If this value is incorrect, the wrong label might be used.'
		);
	}

	/**
	 * Checks the setting of the configuration value omitDateIfSameAsPrevious.
	 *
	 * @access	private
	 */
	function checkOmitDateIfSameAsPrevious() {
		$this->checkIfBoolean(
			'omitDateIfSameAsPrevious',
			true,
			's_template_special',
			'This value specifies whether to omit the date in the '
				.'list view if it is the same as the previous item\'s. '
				.'If this value is incorrect, the date might be omited '
				.'although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value eventFieldsOnRegistrationPage.
	 *
	 * @access	private
	 */
	function checkEventFieldsOnRegistrationPage() {
		$this->checkIfMultiInSetNotEmpty(
			'eventFieldsOnRegistrationPage',
			true,
			's_template_special',
			'This value specifies which data fields of the selected event '
				.'will be displayed on the registration page. '
				.'Incorrect values will cause those fields to not get displayed.',
			array(
				'uid',
				'title',
				'accreditation_number',
				'price_regular',
				'price_special',
				'vacancies'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showRegistrationFields.
	 *
	 * @access	private
	 */
	function checkShowRegistrationFields() {
		$this->checkIfMultiInSetNotEmpty(
			'showRegistrationFields',
			true,
			's_template_special',
			'This value specifies which registration fields '
				.'will be displayed on the registration page. '
				.'Incorrect values will cause those fields to not get displayed.',
			array(
				'step_counter',
				'price',
				'method_of_payment',
				'account_number',
				'bank_code',
				'bank_name',
				'account_owner',
				'billing_address',
				'gender',
				'name',
				'address',
				'zip',
				'city',
				'country',
				'telephone',
				'email',
				'interests',
				'expectations',
				'background_knowledge',
				'accommodation',
				'food',
				'known_from',
				'seats',
				'attendees_names',
				'kids',
				'lodgings',
				'foods',
				'checkboxes',
				'notes',
				'feuser_data',
				'billing_address',
				'registration_data',
				'terms',
				'terms_2'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showSpeakerDetails.
	 *
	 * @access	private
	 */
	function checkShowSpeakerDetails() {
		$this->checkIfBoolean(
			'showSpeakerDetails',
			true,
			's_template_special',
			'This value specifies whether to show detailed information of '
				.'the speakers in the single view. '
				.'If this value is incorrect, the detailed information might '
				.'be shown although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showSiteDetails.
	 *
	 * @access	private
	 */
	function checkShowSiteDetails() {
		$this->checkIfBoolean(
			'showSiteDetails',
			true,
			's_template_special',
			'This value specifies whether to show detailed information of '
				.'the locations in the single view. '
				.'If this value is incorrect, the detailed information might '
				.'be shown although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showFeUserFieldsInRegistrationsList.
	 *
	 * @access	private
	 */
	function checkShowFeUserFieldsInRegistrationsList() {
		$this->checkIfMultiInTableOrEmpty(
			'showFeUserFieldsInRegistrationsList',
			true,
			's_template_special',
			'These values specify the FE user fields to show in the list of '
				.'registrations for an event. A mistyped field name will '
				.'cause the contents of the field to not get displayed.',
			'fe_users'
		);
	}

	/**
	 * Checks the setting of the configuration value listPID.
	 *
	 * @access	private
	 */
	function checkListPid() {
		$this->checkIfSingleFePageNotEmpty(
			'listPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of events. '
				.'If this value is not set correctly, the links in the list '
				.'view and the back link on the list of registrations will '
				.'not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value detailPID.
	 *
	 * @access	private
	 */
	function checkDetailPid() {
		$this->checkIfSingleFePageNotEmpty(
			'detailPID',
			true,
			'sDEF',
			'This value specifies the page that contains the detailed view. '
				.'If this value is not set correctly, the links to single '
				.'events will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value myEventsPID.
	 *
	 * @access	private
	 */
	function checkMyEventsPid() {
		$this->checkIfSingleFePageNotEmpty(
			'myEventsPID',
			true,
			'sDEF',
			'This value specifies the page that contains the <em>my events</em> '
				.'list. If this value is not set correctly, the redirection to '
				.'the my events list after canceling the unregistration process '
				.'will not work correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value registerPID.
	 *
	 * @access	private
	 */
	function checkRegisterPid() {
		$this->checkIfSingleFePageNotEmpty(
			'registerPID',
			true,
			'sDEF',
			'This value specifies the page that contains the registration '
				.'form. If this value is not set correctly, the link to the '
				.'registration page will not work. If you explicitely do not '
				.'wish to use the online registration feature, you can '
				.'disable these checks by setting '
				.'<strong>plugin.tx_seminars.enableRegistration</strong> and '
				.'<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
				.'to 0.'
		);
	}

	/**
	 * Checks the setting of the configuration value loginPID.
	 *
	 * @access	private
	 */
	function checkLoginPid() {
		$this->checkIfSingleFePageNotEmpty(
			'loginPID',
			true,
			'sDEF',
			'This value specifies the page that contains the login form. '
				.'If this value is not set correctly, the link to the '
				.'login page will not work. If you explicitely do not '
				.'wish to use the online registration feature, you can '
				.'disable these checks by setting '
				.'<strong>plugin.tx_seminars.enableRegistration</strong> and '
				.'<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
				.'to 0.'
		);
	}

	/**
	 * Checks the setting of the configuration value registrationsListPID.
	 *
	 * @access	private
	 */
	function checkRegistrationsListPidOptional() {
		$this->checkIfSingleFePageOrEmpty(
			'registrationsListPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value registrationsVipListPID.
	 *
	 * @access	private
	 */
	function checkRegistrationsVipListPid() {
		$this->checkIfSingleFePageNotEmpty(
			'registrationsVipListPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value registrationsVipListPID,
	 * but also allows empty values.
	 *
	 * @access	private
	 */
	function checkRegistrationsVipListPidOptional() {
		$this->checkIfSingleFePageOrEmpty(
			'registrationsVipListPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value pages.
	 *
	 * @access	private
	 */
	function checkPages() {
		$this->checkIfSysFoldersNotEmpty(
			'pages',
			true,
			'sDEF',
			'This value specifies the system folders that contain the '
			.'event records for the list view. If this value is not set '
			.'correctly, some events might not get displayed in the list '
			.'view.'
		);
	}

	/**
	 * Checks the setting of the configuration value recursive,
	 * but also allows empty values.
	 *
	 * @access	private
	 */
	function checkRecursive() {
		$this->checkIfInteger(
			'recursive',
			true,
			'sDEF',
			'This value specifies the how deep the recursion will be for '
				.'selecting the pages that contain the event records for the '
				.'list view. If this value is not set correctly, some events '
				.'might not get displayed in the list view.'
		);
	}

	/**
	 * Checks the settings that are common to all FE plug-in variations of this
	 * extension: CSS styled content, static TypoScript template included,
	 * template file, css file, salutation mode, CSS class names, and what to
	 * display.
	 *
	 * @access	protected
	 */
	function checkCommonFrontEndSettings() {
		$this->checkCssStyledContent();
		$this->checkStaticIncluded();
		$this->checkTemplateFile(true);
		$this->checkCssFileFromConstants();
		$this->checkSalutationMode(true);
		$this->checkCssClassNames();
		$this->checkWhatToDisplay();
	}

	/**
	 * Checks the setting of the configuration value eventEditorFeGroupID.
	 *
	 * @access	private
	 */
	function checkEventEditorFeGroupID() {
		$this->checkIfPositiveInteger(
			'eventEditorFeGroupID',
			true,
			's_fe_editing',
			'This value specifies the front-end user group that is allowed to '
				.'enter and edit event records in the front end. If this value '
				.'is not set correctly, FE editing for events will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value defaultEventVipsFeGroupID.
	 *
	 * @access	private
	 */
	function checkDefaultEventVipsFeGroupID() {
		$this->checkIfPositiveIntegerOrEmpty(
			'defaultEventVipsFeGroupID',
			true,
			'',
			'This value specifies the front-end user group that is allowed to '
				.'see the registrations for all events and get all events listed '
				.'on their "my VIP events" page. If this value is not set '
				.'correctly, the users of this group will not be treated as '
				.'VIPs for all events.'
		);
	}

	/**
	 * Checks the setting of the configuration value createEventsPID.
	 *
	 * @access	private
	 */
	function checkCreateEventsPID() {
		$this->checkIfSingleSysFolderNotEmpty(
			'createEventsPID',
			true,
			's_fe_editing',
			'This value specifies the page on which FE-entered events will be '
				.'stored. If this value is not set correctly, those event '
				.'records will be dumped in the TYPO3 root page.'
		);
	}

	/**
	 * Checks the setting of the configuration value eventSuccessfullySavedPID.
	 *
	 * @access	private
	 */
	function checkEventSuccessfullySavedPID() {
		$this->checkIfSingleFePageNotEmpty(
			'eventSuccessfullySavedPID',
			true,
			's_fe_editing',
			'This value specifies the page to which the user will be '
				.'redirected after saving an event record in the front end. If '
				.'this value is not set correctly, the redirect will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value eventType.
	 *
	 * @access	private
	 */
	function checkEventType() {
		$this->checkForNonEmptyString(
			'eventType',
			false,
			'',
			'This value specifies the default event type. In the default TS '
				.'setup that comes with the extension, it is "Workshop". '
				.'If this value is not set correctly, the page might look '
				.'ugly.'
		);
	}

	/**
	 * Checks the setting of the configuration value eventEditorPID.
	 *
	 * @access	private
	 */
	function checkEventEditorPID() {
		$this->checkIfSingleFePageNotEmpty(
			'eventEditorPID',
			true,
			's_fe_editing',
			'This value specifies the page that contains the plug-in for '
				.'editing event records in the front end. If this value is not '
				.'set correctly, the <em>edit</em> link in the <em>events '
				.'which I have entered</em> list will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value thankYouAfterRegistrationPID.
	 *
	 * @access	private
	 */
	function checkThankYouAfterRegistrationPID() {
		$this->checkIfSingleFePageNotEmpty(
			'thankYouAfterRegistrationPID',
			true,
			's_registration',
			'This value specifies the page that will be displayed after a user '
				.'signed up for an event. If this value is not set correctly, '
				.'the user will see the list of events instead.'
		);
	}

	/**
	 * Checks the setting of the configuration value pageToShowAfterUnregistrationPID.
	 *
	 * @access	private
	 */
	function checkPageToShowAfterUnregistrationPID() {
		$this->checkIfSingleFePageNotEmpty(
			'pageToShowAfterUnregistrationPID',
			true,
			's_registration',
			'This value specifies the page that will be displayed after a user '
				.'has unregistered from an event. If this value is not set correctly, '
				.'the user will see the list of events instead.'
		);
	}

	/**
	 * Checks the setting of the configuration value bankTransferUID.
	 *
	 * @access	private
	 */
	function checkBankTransferUid() {
		$this->checkIfPositiveIntegerOrEmpty(
			'bankTransferUID',
			false,
			'',
			'This value specifies the payment method that corresponds to '
				.'a bank transfer. If this value is not set correctly, '
				.'validation of the bank data in the event registration '
				.'form will not work correctly.'
		);
	}

	/**
	 * Checks the CSV-related settings.
	 *
	 * @access	private
	 */
	function check_tx_seminars_seminarbag_csv() {
		$this->checkAllowAccessToCsv();
		$this->checkCharsetForCsv();
		$this->checkFilenameForEventsCsv();
		$this->checkFilenameForRegistrationsCsv();
		$this->checkFieldsFromEventsForCsv();
		$this->checkFieldsFromFeUserForCsv();
		$this->checkFieldsFromAttendanceForCsv();
	}

	/**
	 * Checks the setting of the configuration value allowAccessToCsv.
	 *
	 * @access	private
	 */
	function checkAllowAccessToCsv() {
		$this->checkIfBoolean(
			'allowAccessToCsv',
			false,
			'',
			'This value specifies whether the access check for the CSV export '
				.'will be overridden. '
				.'If this value is not set correctly, anyone could use the CSV '
				.'export, gaining access to sensitive data.'
		);
	}

	/**
	 * Checks the setting of the configuration value charsetForCsv.
	 *
	 * @access	private
	 */
	function checkCharsetForCsv() {
		$this->checkForNonEmptyString(
			'charsetForCsv',
			false,
			'',
			'This value specifies the charset to use for the CSV export. '
				.'If this value is not set, no charset information will be '
				.'provided for CSV downloads.'
		);
	}

	/**
	 * Checks the setting of the configuration value filenameForEventsCsv.
	 *
	 * @access	private
	 */
	function checkFilenameForEventsCsv() {
		$this->checkForNonEmptyString(
			'filenameForEventsCsv',
			false,
			'',
			'This value specifies the file name to suggest for the CSV export '
				.'of event records. '
				.'If this value is not set, an empty filename will be used for '
				.'saving the CSV file which will cause problems.'
		);
	}

	/**
	 * Checks the setting of the configuration value filenameForRegistrationsCsv.
	 *
	 * @access	private
	 */
	function checkFilenameForRegistrationsCsv() {
		$this->checkForNonEmptyString(
			'filenameForRegistrationsCsv',
			false,
			'',
			'This value specifies the file name to suggest for the CSV export '
				.'of registration records. '
				.'If this value is not set, an empty filename will be used for '
				.'saving the CSV file which will cause problems.'
		);
	}

	/**
	 * Checks the setting of the configuration value fieldsFromEventsForCsv.
	 *
	 * @access	private
	 */
	function checkFieldsFromEventsForCsv() {
		$this->checkIfMultiInSetNotEmpty(
			'fieldsFromEventsForCsv',
			false,
			'',
			'These values specify the event fields to export via CSV. '
				.'A mistyped field name will cause the field to not get '
				.'included.',
			array(
				'uid',
				'tstamp',
				'crdate',
				'title',
				'subtitle',
				'teaser',
				'description',
				'event_type',
				'accreditation_number',
				'credit_points',
				'date',
				'time',
				'deadline_registration',
				'deadline_early_bird',
				'deadline_unregistration',
				'place',
				'room',
				'lodgings',
				'foods',
				'additional_times_places',
				'speakers',
				'partners',
				'tutors',
				'leaders',
				'price_regular',
				'price_regular_early',
				'price_regular_board',
				'price_special',
				'price_special_early',
				'price_special_board',
				'additional_information',
				'payment_methods',
				'organizers',
				'attendees_min',
				'attendees_max',
				'attendees',
				'vacancies',
				'enough_attendees',
				'is_full',
				'cancelled'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value fieldsFromFeUserForCsv.
	 *
	 * @access	private
	 */
	function checkFieldsFromFeUserForCsv() {
		$this->checkIfMultiInTableOrEmpty(
			'fieldsFromFeUserForCsv',
			false,
			'',
			'These values specify the FE user fields to export via CSV. '
				.'A mistyped field name will cause the field to not get '
				.'included.',
			'fe_users'
		);
	}

	/**
	 * Checks the setting of the configuration value fieldsFromAttendanceForCsv.
	 *
	 * @access	private
	 */
	function checkFieldsFromAttendanceForCsv() {
		$this->checkIfMultiInTableOrEmpty(
			'fieldsFromAttendanceForCsv',
			false,
			'',
			'These values specify the registration fields to export via CSV. '
				.'A mistyped field name will cause the field to not get '
				.'included.',
			SEMINARS_TABLE_ATTENDANCES
		);
	}

	/**
	 * Checks the setting of the configuration value showToBeAnnouncedForEmptyPrice.
	 *
	 * @access	private
	 */
	function checkShowToBeAnnouncedForEmptyPrice() {
		$this->checkIfBoolean(
			'showToBeAnnouncedForEmptyPrice',
			false,
			'',
			'This value specifies whether &quot;to be announced&quot; should be '
				.'displayed instead of &quot;free&quot; if an event has no '
				.'regular price set yet.'
				.'If this value is not set correctly, the wrong wording '
				.'might get displayed.'
		);
	}

	/**
	 * Checks whether the HTML template for the registration form is provided
	 * and the file exists.
	 *
	 * @access	protected
	 */
	function checkRegistrationEditorTemplateFile() {
		$this->checkForNonEmptyString(
			'registrationEditorTemplateFile',
			true,
			's_registration',
			'This specifies the HTML template for the registration form.'
		);

		if ($this->objectToCheck->hasConfValueString(
			'registrationEditorTemplateFile', 's_registration'
		)) {
			$rawFileName = $this->objectToCheck->getConfValueString(
				'registrationEditorTemplateFile',
				's_template_special'
			);
			if (!is_file($GLOBALS['TSFE']->tmpl->getFileName($rawFileName))) {
				$message = 'The specified HTML template file <strong>'
					.htmlspecialchars($rawFileName)
					.'</strong> cannot be read. '
					.'This specifies the HTML template for the registration form. '
					.'Please either create the file <strong>'.$rawFileName
					.'</strong> or select an existing file using the TS setup '
					.'variable <strong>'.$this->getTSSetupPath()
					.'templateFile</strong> or via FlexForms.';
				$this->setErrorMessage($message);
			}
		}
	}

	/**
	 * Checks the setting of the configuration value
	 * logOutOneTimeAccountsAfterRegistration.
	 *
	 * @access	private
	 */
	function checkLogOutOneTimeAccountsAfterRegistration() {
		$this->checkIfBoolean(
			'logOutOneTimeAccountsAfterRegistration',
			false,
			'',
			'This value specifies whether one-time FE user accounts will '
				.'automatically be logged out after registering for an event. '
				.'If this value is incorrect, the automatic logout will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * charsetForEMails.
	 *
	 * @access	private
	 */
	function checkCharsetForEMails() {
		$this->checkForNonEmptyString(
			'charsetForEMails',
			false,
			'',
			'This value specifies the charset that will be used in e-mails to '
				.'the organizers and the attendees. '
				.'If this value is empty, special characters in these e-mails '
				.'will appear garbled.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * numberOfFirstRegistrationPage.
	 *
	 * @access	private
	 */
	function checkNumberOfFirstRegistrationPage() {
		$this->checkIfPositiveInteger(
			'numberOfFirstRegistrationPage',
			false,
			'',
			'This value specifies the number of the first registration page '
				.'(for the <em>Step x of y</em> heading). '
				.'If this value is not set correctly, the number of the current '
				.'page will not be displayed correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * numberOfLastRegistrationPage.
	 *
	 * @access	private
	 */
	function checkNumberOfLastRegistrationPage() {
		$this->checkIfPositiveInteger(
			'numberOfLastRegistrationPage',
			false,
			'',
			'This value specifies the number of the last registration page '
				.'(for the <em>Step x of y</em> heading). '
				.'If this value is not set correctly, the number of the last '
				.'page will not be displayed correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * numberOfClicksForRegistration.
	 *
	 * @access	private
	 */
	function checkNumberOfClicksForRegistration() {
		$this->checkIfInteger(
			'numberOfClicksForRegistration',
			true,
			's_registration',
			'This specifies the number of clicks for registration'
		);

		$this->checkIfIntegerInRange(
			'numberOfClicksForRegistration',
			2,
			3,
			true,
			's_registration',
			'This specifies the number of clicks for registration.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendParametersToThankYouAfterRegistrationPageUrl.
	 *
	 * @access	private
	 */
	function checkSendParametersToThankYouAfterRegistrationPageUrl() {
		$this->checkIfBoolean(
			'sendParametersToThankYouAfterRegistrationPageUrl',
			true,
			's_registration',
			'This value specifies whether the sending of parameters to the '
				.'thank you page after a registration should be enabled or not. '
				.'If this value is incorrect the sending of parameters will '
				.'not be enabled or disabled correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendParametersToPageToShowAfterUnregistrationUrl.
	 *
	 * @access	private
	 */
	function checkSendParametersToPageToShowAfterUnregistrationUrl() {
		$this->checkIfBoolean(
			'sendParametersToPageToShowAfterUnregistrationUrl',
			true,
			's_registration',
			'This value specifies wether the sending of parameters to the page '
				.'which is shown after an unregistration should be enabled or '
				.'not. If this value is incorrect the sending of parameters '
				.'will not be enabled or disabled correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * unregistrationDeadlineDaysBeforeBeginDate.
	 *
	 * @access	private
	 */
	function checkUnregistrationDeadlineDaysBeforeBeginDate() {
		$this->checkIfPositiveIntegerOrEmpty(
			'unregistrationDeadlineDaysBeforeBeginDate',
			false,
			'',
			'This value specifies the number of days before the start of an '
				.'event until unregistration is possible. (If you want to '
				.'disable this feature, just leave this value empty.) If this '
				.'value is incorrect, unregistration will fail to work or the '
				.'unregistration period will be a different number of days than '
				.'desired.'
		);
	}

	/**
	 * Checks the setting of the configuration value sendNotification.
	 *
	 * @access	private
	 */
	function checkSendNotification() {
		$this->checkIfBoolean(
			'sendNotification',
			false,
			'',
			'This value specifies wether a notification e-mail should be send '
				.'to the organizer after a user has registered. If this value '
				.'is not set correctly, the sending of notifications probably '
				.'will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendNotificationOnUnregistration.
	 *
	 * @access	private
	 */
	function checkSendNotificationOnUnregistration() {
		$this->checkIfBoolean(
			'sendNotificationOnUnregistration',
			false,
			'',
			'This value specifies wether a notification e-mail should be send '
				.'to the organizer after a user has unregistered. If this value '
				.'is not set correctly, the sending of notifications probably '
				.'will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendNotificationOnRegistrationForQueue.
	 *
	 * @access	private
	 */
	function checkSendNotificationOnRegistrationForQueue() {
		$this->checkIfBoolean(
			'sendNotificationOnRegistrationForQueue',
			false,
			'',
			'This value specifies wether a notification e-mail should be send '
				.'to the organizer after someone registered for the queue. If '
				.'this value is not set correctly, the sending of notifications '
				.'probably will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendNotificationOnQueueUpdate.
	 *
	 * @access	private
	 */
	function checkSendNotificationOnQueueUpdate() {
		$this->checkIfBoolean(
			'sendNotificationOnQueueUpdate',
			false,
			'',
			'This value specifies wether a notification e-mail should be send '
				.'to the organizer after the queue has been updated. If '
				.'this value is not set correctly, the sending of notifications '
				.'probably will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value sendConfirmation.
	 *
	 * @access	private
	 */
	function checkSendConfirmation() {
		$this->checkIfBoolean(
			'sendConfirmation',
			false,
			'',
			'This value specifies wether a confirmation e-mail should be send '
				.'to the user after the user has registered. If this value is '
				.'not set correctly, the sending of notifications probably will '
				.'not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendConfirmationOnUnregistration.
	 *
	 * @access	private
	 */
	function checkSendConfirmationOnUnregistration() {
		$this->checkIfBoolean(
			'sendConfirmationOnUnregistration',
			false,
			'',
			'This value specifies wether a confirmation e-mail should be send '
				.'to the user after the user has unregistered. If this value is '
				.'not set correctly, the sending of notifications probably will '
				.'not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendConfirmationOnRegistrationForQueue.
	 *
	 * @access	private
	 */
	function checkSendConfirmationOnRegistrationForQueue() {
		$this->checkIfBoolean(
			'sendConfirmationOnRegistrationForQueue',
			false,
			'',
			'This value specifies wether a confirmation e-mail should be send '
				.'to the user after the user has registered for the queue. If '
				.'this value is not set correctly, the sending of notifications '
				.'probably will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendConfirmationOnQueueUpdate.
	 *
	 * @access	private
	 */
	function checkSendConfirmationOnQueueUpdate() {
		$this->checkIfBoolean(
			'sendConfirmationOnQueueUpdate',
			false,
			'',
			'This value specifies wether a confirmation e-mail should be send '
				.'to the user after the queue has been updated. If this value is '
				.'not set correctly, the sending of notifications probably will '
				.'not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value externalLinkTarget.
	 * But currently does nothing as we don't think there's something to check
	 * for.
	 *
	 * @access	private
	 */
	function checkExternalLinkTarget() {
		// Does nothing.
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']);
}

?>
