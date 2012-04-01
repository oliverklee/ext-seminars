<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_configcheck extends tx_oelib_configcheck {
	/**
	 * Checks the configuration for: tx_seminars_registrationmanager/.
	 */
	protected function check_tx_seminars_registrationmanager() {
		// The registration manager needs to be able to create registration
		// objects. So we check whether the prerequisites for registrations
		// are fullfilled as well.
		$this->check_tx_seminars_registration();
	}

	/**
	 * Checks the configuration for: tx_seminars_seminar/.
	 */
	protected function check_tx_seminars_seminar() {
		$this->checkStaticIncluded();
		$this->checkSalutationMode();
		$this->checkTimeAndDate();
		$this->checkCurrency();
		$this->checkShowToBeAnnouncedForEmptyPrice();

		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkShowTimeOfRegistrationDeadline();
			$this->checkShowTimeOfEarlyBirdDeadline();
			$this->checkShowVacanciesThreshold();
			$this->checkAllowRegistrationForStartedEvents();
			$this->checkAllowRegistrationForEventsWithoutDate();
			$this->checkSkipRegistrationCollisionCheck();
		}
	}

	/**
	 * Checks the configuration for: tx_seminars_registration/.
	 */
	protected function check_tx_seminars_registration() {
		$this->checkStaticIncluded();
		$this->checkTemplateFile();
		$this->checkSalutationMode();

		$this->checkRegistrationFlag();

		$this->checkThankYouMail();
		$this->checkGeneralPriceInMail();
		$this->checkNotificationMail();

		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkAttendancesPid();
			$this->checkUnregistrationDeadlineDaysBeforeBeginDate();
			$this->checkAllowUnregistrationWithEmptyWaitingList();
		}
	}

	/**
	 * Checks the configuration for: tx_seminars_speaker/.
	 */
	protected function check_tx_seminars_speaker() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_OldModel_Organizer/.
	 */
	protected function check_tx_seminars_OldModel_Organizer() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_timeslot/.
	 */
	protected function check_tx_seminars_timeslot() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_test/.
	 */
	protected function check_tx_seminars_test() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_OldModel_Category/.
	 */
	protected function check_tx_seminars_OldModel_Category() {
		$this->checkStaticIncluded();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_RegistrationsList/.
	 */
	protected function check_tx_seminars_FrontEnd_RegistrationsList() {
		$this->checkCommonFrontEndSettings();

		$this->checkShowFeUserFieldsInRegistrationsList();
		$this->checkShowRegistrationFieldsInRegistrationsList();
		$this->checkListPid();
	}

	/**
	 * Does nothing.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController() {
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_DefaultController/seminar_registration.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_seminar_registration() {
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

		$this->checkRegistrationEditorTemplateFile();

		$this->checkNumberOfClicksForRegistration();
		$this->checkNumberOfFirstRegistrationPage();
		$this->checkNumberOfLastRegistrationPage();
		$this->checkRegistrationPageNumbers();
		$this->checkGeneralPriceInSingle();
		$this->checkEventFieldsOnRegistrationPage();
		$this->checkShowRegistrationFields();
		$this->checkThankYouAfterRegistrationPID();
		$this->checkSendParametersToThankYouAfterRegistrationPageUrl();
		$this->checkPageToShowAfterUnregistrationPID();
		$this->checkSendParametersToPageToShowAfterUnregistrationUrl();

		$this->checkCreateAdditionalAttendeesAsFrontEndUsers();
		if ($this->objectToCheck->getConfValueBoolean(
			'createAdditionalAttendeesAsFrontEndUsers', 's_registration'
		)) {
			$this->checkSysFolderForAdditionalAttendeeUsersPID();
			$this->checkUserGroupUidsForAdditionalAttendeesFrontEndUsers();
		}

		$this->checkListPid();
		$this->checkLoginPid();
		$this->checkBankTransferUid();
		$this->checkLogOutOneTimeAccountsAfterRegistration();
		$this->checkMyEventsPid();
		$this->checkDetailPid();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_DefaultController/single_view.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_single_view() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();

		$this->checkShowSingleEvent();
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
		$this->checkSingleViewImageSizes();
		$this->checkShowOwnerDataInSingleView();
		if ($this->objectToCheck->getConfValueBoolean(
			'showOwnerDataInSingleView', 's_singleView'
		)) {
			$this->checkOwnerPictureMaxWidth();
		}
		$this->checkLimitFileDownloadToAttendees();
		$this->checkShowOnlyEventsWithVacancies();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_DefaultController/seminar_list.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_seminar_list() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();

		$this->checkPages();
		$this->checkRecursive();
		$this->checkListView(array_keys($this->objectToCheck->orderByList));

		// This is checked for the list view as well because an invalid value
		// might cause the list view to be displayed instead of the single view.
		$this->checkShowSingleEvent();
		$this->checkHideColumns();
		$this->checkTimeframeInList();
		$this->checkShowEmptyEntryInOptionLists();
		$this->checkHidePageBrowser();
		$this->checkHideCanceledEvents();
		$this->checkSortListViewByCategory();
		$this->checkGeneralPriceInList();
		$this->checkOmitDateIfSameAsPrevious();
		$this->checkListPid();
		$this->checkDetailPid();
		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkRegisterPid();
			$this->checkLoginPid();
		}
		$this->checkAccessToFrontEndRegistrationLists();
		$this->checkRegistrationsListPidOptional();
		$this->checkRegistrationsVipListPidOptional();
		$this->checkDefaultEventVipsFeGroupID();
		$this->checkLimitListViewToEventTypes();
		$this->checkLimitListViewToCategories();
		$this->checkLimitListViewToPlaces();
		$this->checkLimitListViewToOrganizers();
		$this->checkCategoryIconDisplay();
		$this->checkSeminarImageSizes();
		$this->checkDisplaySearchFormFields();
		$this->checkNumberOfYearsInDateFilter();
		$this->checkLimitFileDownloadToAttendees();
		$this->checkShowOnlyEventsWithVacancies();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_Countdown.
	 */
	protected function check_tx_seminars_FrontEnd_Countdown() {
		$this->checkCommonFrontEndSettings();
		$this->checkPages();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_DefaultController/my_vip_events.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_my_vip_events() {
		$this->check_tx_seminars_FrontEnd_DefaultController_seminar_list();
		$this->checkRegistrationsVipListPid();
		$this->checkDefaultEventVipsFeGroupID();
		$this->checkMayManagersEditTheirEvents();
		$this->checkAllowCsvExportOfRegistrationsInMyVipEventsView();

		if ($this->objectToCheck->getConfValueBoolean(
			'mayManagersEditTheirEvents', 's_listView'
		)) {
			$this->checkEventEditorPID();
		}
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_DefaultController/topic_list.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_topic_list() {
		$this->check_tx_seminars_FrontEnd_DefaultController_seminar_list();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_DefaultController/my_events.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_my_events() {
		$this->check_tx_seminars_FrontEnd_DefaultController_seminar_list();
	}

	/**
	 * Checks the configuration for: check_tx_seminars_FrontEnd_DefaultController/edit_event.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_edit_event() {
		$this->checkCommonFrontEndSettings();

		$this->checkEventEditorTemplateFile();
		$this->checkEventEditorFeGroupID();
		$this->checkCreateEventsPID();
		$this->checkEventSuccessfullySavedPID();
		$this->checkAllowedExtensionsForUpload();
		$this->checkDisplayFrontEndEditorFields();
		$this->checkRequiredFrontEndEditorFields();
		$this->checkRequiredFrontEndEditorPlaceFields();

		$this->checkAllowFrontEndEditingOfCheckboxes();
		$this->checkAllowFrontEndEditingOfPlaces();
		$this->checkAllowFrontEndEditingOfSpeakers();
		$this->checkAllowFrontEndEditingOfTargetGroups();
	}

	/**
	 * Checks the configuration for: check_tx_seminars_FrontEnd_DefaultController/my_entered_events.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_my_entered_events() {
		$this->check_tx_seminars_FrontEnd_DefaultController_seminar_list();
		$this->checkEventEditorFeGroupID();
		$this->checkEventEditorPID();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_CategoryList.
	 */
	protected function check_tx_seminars_FrontEnd_CategoryList() {
		$this->checkCommonFrontEndSettings();

		$this->checkPagesForCategoryList();
		$this->checkRecursive();
		$this->checkTimeframeInList();

		$this->checkListPid();
	}

	/**
	 * Checks the configuration for: tx_seminars_FrontEnd_DefaultController/favorites_list
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_favorites_list() {
		$this->check_tx_seminars_FrontEnd_DefaultController_seminar_list();
	}

	/**
	 * This check isn't actually used. It is merely needed for the unit tests.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_events_next_day() {}

	/**
	 * Checks if the common frontend settings are set.
	 */
	protected function check_tx_seminars_FrontEnd_EventHeadline() {
		$this->checkCommonFrontEndSettings();
		$this->checkTimeAndDate();
	}

	/**
	 * This check isn't actually used. It is merely needed for the unit tests.
	 */
	protected function check_tx_seminars_FrontEnd_DefaultController_event_headline() {
	}

	/**
	 * Checks the configuration related to thank-you e-mails.
	 */
	private function checkThankYouMail() {
		$this->checkHideFieldsInThankYouMail();
		$this->checkSendConfirmation();
		$this->checkSendConfirmationOnQueueUpdate();
		$this->checkSendConfirmationOnRegistrationForQueue();
		$this->checkSendConfirmationOnUnregistration();
	}

	/**
	 * Checks the configuration related to notification e-mails.
	 */
	private function checkNotificationMail() {
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
	 */
	private function checkTimeAndDate() {
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
				FALSE,
				'',
				$explanation
			);
		}

		$this->checkAbbreviateDateRanges();
	}

	/**
	 * Checks the setting of the configuration value enableRegistration.
	 */
	private function checkRegistrationFlag() {
		$this->checkIfBoolean(
			'enableRegistration',
			FALSE,
			'',
			'This value specifies whether the extension will provide online '
				.'registration. If this value is incorrect, the online '
				.'registration will not be enabled or disabled correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value what_to_display.
	 */
	private function checkWhatToDisplay() {
		$this->checkIfSingleInSetNotEmpty(
			'what_to_display',
			TRUE,
			'sDEF',
			'This value specifies the type of seminar manager plug-in to '
				.'display. If this value is not set correctly, the wrong '
				.'type of plug-in will be displayed.',
			array(
				'seminar_list',
				'single_view',
				'topic_list',
				'my_events',
				'my_vip_events',
				'seminar_registration',
				'list_registrations',
				'list_vip_registrations',
				'edit_event',
				'my_entered_events',
				'countdown',
				'category_list',
				'event_headline',
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showTimeOfRegistrationDeadline.
	 */
	private function checkShowTimeOfRegistrationDeadline() {
		$this->checkIfBoolean(
			'showTimeOfRegistrationDeadline',
			FALSE,
			'',
			'This value specifies whether to also show the time of '
				.'registration deadlines. If this value is incorrect, the '
				.'time might get shown although this is not intended '
				.'(or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showTimeOfEarlyBirdDeadline.
	 */
	private function checkShowTimeOfEarlyBirdDeadline() {
		$this->checkIfBoolean(
			'showTimeOfEarlyBirdDeadline',
			FALSE,
			'',
			'This value specifies whether to also show the time of '
				.'early bird deadlines. If this value is incorrect, the '
				.'time might get shown although this is not intended '
				.'(or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showVacanciesThreshold.
	 */
	private function checkShowVacanciesThreshold() {
		$this->checkIfInteger(
			'showVacanciesThreshold',
			FALSE,
			'',
			'This value specifies down from which threshold the exact number '
				.'of vancancies will be displayed. If this value is incorrect, '
				.'the number might get shown although this is not intended '
				.'(or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value generalPriceInMail.
	 */
	private function checkGeneralPriceInMail() {
		$this->checkIfBoolean(
			'generalPriceInMail',
			FALSE,
			'',
			'This value specifies which wording to use for the standard price '
				.'in e-mails. If this value is incorrect, the wrong wording '
				.'might get used.'
		);
	}

	/**
	 * Checks the setting of the configuration value attendancesPID.
	 */
	private function checkAttendancesPid() {
		$this->checkIfSingleSysFolderNotEmpty(
			'attendancesPID',
			FALSE,
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
	 */
	private function checkHideFields() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFields',
			TRUE,
			's_template_special',
			'This value specifies which section to remove from the details view. '
				.'Incorrect values will cause the sections to still be displayed.',
			array(
				'image',
				'event_type',
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
				'expiry',
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
				'attached_files',
				'organizers',
				'vacancies',
				'deadline_registration',
				'otherdates',
				'eventsnextday',
				'registration',
				'back',
				'requirements',
				'dependencies',
			)
		);
	}

	/**
	 * Checks the setting of the configuration value hideColumns.
	 */
	private function checkHideColumns() {
		$this->checkIfMultiInSetOrEmpty(
			'hideColumns',
			TRUE,
			's_template_special',
			'This value specifies which columns to remove from the list view. '
				.'Incorrect values will cause the colums to still be displayed.',
			array(
				'image',
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
				'expiry',
				'place',
				'city',
				'seats',
				'country',
				'price_regular',
				'price_special',
				'total_price',
				'organizers',
				'target_groups',
				'attached_files',
				'vacancies',
				'status_registration',
				'registration',
				'list_registrations',
				'status',
				'edit',
			)
		);
	}

	/**
	 * Checks the setting of the configuration value timeframeInList.
	 */
	private function checkTimeframeInList() {
		$this->checkIfSingleInSetNotEmpty(
			'timeframeInList',
			TRUE,
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
				'deadlineNotOver',
				'today',
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showEmptyEntryInOptionLists.
	 */
	private function checkShowEmptyEntryInOptionLists() {
		$this->checkIfBoolean(
			'showEmptyEntryInOptionLists',
			TRUE,
			's_template_special',
			'This value specifies whether the option boxes in the selector widget '
				.'will contain a dummy entry called "not selected". This is only '
				.'needed if you changed the HTML template to show the selectors '
				.'as dropdown menues. If this value is incorrect, the dummy entry '
				.'might get displayed when this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value hidePageBrowser.
	 */
	private function checkHidePageBrowser() {
		$this->checkIfBoolean(
			'hidePageBrowser',
			TRUE,
			's_template_special',
			'This value specifies whether the page browser in the list view '
				.'will be displayed. If this value is incorrect, the page '
				.'browser might get displayed when this is not intended (or '
				.'vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value hideCanceledEvents.
	 */
	private function checkHideCanceledEvents() {
		$this->checkIfBoolean(
			'hideCanceledEvents',
			TRUE,
			's_template_special',
			'This value specifies whether canceled events will be removed '
				.'from the list view. If this value is incorrect, canceled '
				.'events might get displayed when this is not intended (or '
				.'vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value sortListViewByCategory.
	 */
	private function checkSortListViewByCategory() {
		$this->checkIfBoolean(
			'sortListViewByCategory',
			TRUE,
			's_template_special',
			'This value specifies whether the list view should be sorted by '
				.'category before applying the normal sorting. If this value '
				.'is incorrect, the list view might get sorted by category '
				.'when this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value hideFieldsInThankYouMail.
	 */
	private function checkHideFieldsInThankYouMail() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFieldsInThankYouMail',
			FALSE,
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
				'paymentmethod',
				'billing_address',
				'url',
				'planned_disclaimer',
				'footer',
			)
		);
	}

	/**
	 * Checks the setting of the configuration value hideFieldsInNotificationMail.
	 */
	protected function checkHideFieldsInNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFieldsInNotificationMail',
			FALSE,
			'',
			'These values specify the sections to hide in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'be included nonetheless.',
			array(
				'summary',
				'seminardata',
				'feuserdata',
				'attendancedata'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showSeminarFieldsInNotificationMail.
	 */
	private function checkShowSeminarFieldsInNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'showSeminarFieldsInNotificationMail',
			FALSE,
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
	 */
	private function checkShowFeUserFieldsInNotificationMail() {
		$this->checkIfMultiInTableOrEmpty(
			'showFeUserFieldsInNotificationMail',
			FALSE,
			'',
			'These values specify the FE user fields to show in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'not get included.',
			'fe_users'
		);
	}

	/**
	 * Checks the setting of the configuration value showAttendanceFieldsInNotificationMail.
	 */
	private function checkShowAttendanceFieldsInNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'showAttendanceFieldsInNotificationMail',
			FALSE,
			'',
			'These values specify the registration fields to show in e-mails ' .
				'to organizers. A mistyped field name will cause the field ' .
				'to not get included.',
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
				'company',
				'gender',
				'name',
				'address',
				'zip',
				'city',
				'country',
				'telephone',
				'email',
			)
		);
	}

	/**
	 * Checks the setting of the configuration value sendAdditionalNotificationEmails.
	 */
	private function checkSendAdditionalNotificationEmails() {
		$this->checkIfBoolean(
			'sendAdditionalNotificationEmails',
			FALSE,
			'',
			'This value specifies whether organizers receive additional '
				.'notification e-mails. If this value is incorrect, e-mails '
				.'might get sent when this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value abbreviateDateRanges.
	 */
	private function checkAbbreviateDateRanges() {
		$this->checkIfBoolean(
			'abbreviateDateRanges',
			FALSE,
			'',
			'This value specifies whether date ranges will be abbreviated. '
				.'If this value is incorrect, the values might be abbreviated '
				.'although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value generalPriceInList.
	 */
	private function checkGeneralPriceInList() {
		$this->checkIfBoolean(
			'generalPriceInList',
			TRUE,
			's_template_special',
			'This value specifies whether the column header for the standard '
				.'price in the list view will be just <em>Price</em> instead '
				.'of <em>Standard price</em>. '
				.'If this value is incorrect, the wrong label might be used.'
		);
	}

	/**
	 * Checks the setting of the configuration value generalPriceInSingle.
	 */
	private function checkGeneralPriceInSingle() {
		$this->checkIfBoolean(
			'generalPriceInSingle',
			TRUE,
			's_template_special',
			'This value specifies whether the heading for the standard price '
				.'in the detailed view and on the registration page will be '
				.'just <em>Price</em> instead of <em>Standard price</em>. '
				.'If this value is incorrect, the wrong label might be used.'
		);
	}

	/**
	 * Checks the setting of the configuration value omitDateIfSameAsPrevious.
	 */
	private function checkOmitDateIfSameAsPrevious() {
		$this->checkIfBoolean(
			'omitDateIfSameAsPrevious',
			TRUE,
			's_template_special',
			'This value specifies whether to omit the date in the '
				.'list view if it is the same as the previous item\'s. '
				.'If this value is incorrect, the date might be omited '
				.'although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * accessToFrontEndRegistrationLists.
	 */
	private function checkAccessToFrontEndRegistrationLists() {
		$this->checkIfSingleInSetNotEmpty(
			'accessToFrontEndRegistrationLists',
			FALSE,
			'',
			'This value specifies who is able to see the registered persons  ' .
				'an event in the front end. ' .
				'If this value is incorrect, persons may access the ' .
				'registration lists although they should not be allowed to ' .
				'(or vice versa).',
			array('attendees_and_managers', 'login', 'world')
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * allowCsvExportOfRegistrationsInMyVipEventsView.
	 */
	private function checkAllowCsvExportOfRegistrationsInMyVipEventsView() {
		$this->checkIfBoolean(
			'allowCsvExportOfRegistrationsInMyVipEventsView',
			FALSE,
			'',
			'This value specifies whether managers are allowed to access the ' .
				'CSV export of registrations from the "my VIP events" view. ' .
				'If this value is incorrect, managers may be allowed to access ' .
				'the CSV export of registrations from the "my VIP events ' .
				'view" although they should not be allowed to (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value mayManagersEditTheirEvents.
	 */
	private function checkMayManagersEditTheirEvents() {
		$this->checkIfBoolean(
			'mayManagersEditTheirEvents',
			TRUE,
			's_listView',
			'This value specifies whether VIPs may edit their events. If this ' .
				'value is incorrect, VIPs may be allowed to edit their events ' .
				' although they should not be allowed to (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value eventFieldsOnRegistrationPage.
	 */
	private function checkEventFieldsOnRegistrationPage() {
		$this->checkIfMultiInSetNotEmpty(
			'eventFieldsOnRegistrationPage',
			TRUE,
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
	 */
	private function checkShowRegistrationFields() {
		$this->checkIfMultiInSetNotEmpty(
			'showRegistrationFields',
			TRUE,
			's_template_special',
			'This value specifies which registration fields ' .
				'will be displayed on the registration page. ' .
				'Incorrect values will cause those fields to not get displayed.',
			array(
				'step_counter',
				'price',
				'method_of_payment',
				'account_number',
				'bank_code',
				'bank_name',
				'account_owner',
				'billing_address',
				'company',
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
				'registered_themselves',
				'attendees_names',
				'kids',
				'lodgings',
				'foods',
				'checkboxes',
				'notes',
				'total_price',
				'feuser_data',
				'registration_data',
				'terms',
				'terms_2'
			)
		);
	}

	/**
	 * Checks the setting of the configuration value showSpeakerDetails.
	 */
	private function checkShowSpeakerDetails() {
		$this->checkIfBoolean(
			'showSpeakerDetails',
			TRUE,
			's_template_special',
			'This value specifies whether to show detailed information of '
				.'the speakers in the single view. '
				.'If this value is incorrect, the detailed information might '
				.'be shown although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showSiteDetails.
	 */
	private function checkShowSiteDetails() {
		$this->checkIfBoolean(
			'showSiteDetails',
			TRUE,
			's_template_special',
			'This value specifies whether to show detailed information of '
				.'the locations in the single view. '
				.'If this value is incorrect, the detailed information might '
				.'be shown although this is not intended (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value limitFileDownloadToAttendees.
	 */
	private function checkLimitFileDownloadToAttendees() {
		$this->checkIfBoolean(
			'limitFileDownloadToAttendees',
			TRUE,
			's_singleView',
			'This value specifies whether the list of attached files is only ' .
				'shown to logged in and registered attendees. If this value is ' .
				'incorrect, the attached files may be shown to the public ' .
				'although they should be visible only to the attendees ' .
				'(or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value showFeUserFieldsInRegistrationsList.
	 */
	private function checkShowFeUserFieldsInRegistrationsList() {
		$this->checkIfMultiInTableOrEmpty(
			'showFeUserFieldsInRegistrationsList',
			TRUE,
			's_template_special',
			'These values specify the FE user fields to show in the list of '
				.'registrations for an event. A mistyped field name will '
				.'cause the contents of the field to not get displayed.',
			'fe_users'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * showRegistrationFieldsInRegistrationList.
	 */
	private function checkShowRegistrationFieldsInRegistrationsList() {
		$this->checkIfMultiInTableOrEmpty(
			'showRegistrationFieldsInRegistrationList',
			TRUE,
			's_template_special',
			'These values specify the registration fields to show in the list ' .
				'of registrations for an event. A mistyped field name will ' .
				'cause the contents of the field to not get displayed.',
			'tx_seminars_attendances'
		);
	}

	/**
	 * Checks the setting of the configuration value listPID.
	 */
	private function checkListPid() {
		$this->checkIfSingleFePageNotEmpty(
			'listPID',
			TRUE,
			'sDEF',
			'This value specifies the page that contains the list of events. '
				.'If this value is not set correctly, the links in the list '
				.'view and the back link on the list of registrations will '
				.'not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value detailPID.
	 */
	private function checkDetailPid() {
		$this->checkIfSingleFePageNotEmpty(
			'detailPID',
			TRUE,
			'sDEF',
			'This value specifies the page that contains the detailed view. '
				.'If this value is not set correctly, the links to single '
				.'events will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value myEventsPID.
	 */
	private function checkMyEventsPid() {
		$this->checkIfSingleFePageNotEmpty(
			'myEventsPID',
			TRUE,
			'sDEF',
			'This value specifies the page that contains the <em>my events</em> '
				.'list. If this value is not set correctly, the redirection to '
				.'the my events list after canceling the unregistration process '
				.'will not work correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value registerPID.
	 */
	private function checkRegisterPid() {
		$this->checkIfSingleFePageNotEmpty(
			'registerPID',
			TRUE,
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
	 */
	private function checkLoginPid() {
		$this->checkIfSingleFePageNotEmpty(
			'loginPID',
			TRUE,
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
	 */
	private function checkRegistrationsListPidOptional() {
		$this->checkIfSingleFePageOrEmpty(
			'registrationsListPID',
			TRUE,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value registrationsVipListPID.
	 */
	private function checkRegistrationsVipListPid() {
		$this->checkIfSingleFePageNotEmpty(
			'registrationsVipListPID',
			TRUE,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value registrationsVipListPID,
	 * but also allows empty values.
	 */
	private function checkRegistrationsVipListPidOptional() {
		$this->checkIfSingleFePageOrEmpty(
			'registrationsVipListPID',
			TRUE,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value pages.
	 */
	private function checkPages() {
		$this->checkIfSysFoldersNotEmpty(
			'pages',
			TRUE,
			'sDEF',
			'This value specifies the system folders that contain the '
			.'event records for the list view. If this value is not set '
			.'correctly, some events might not get displayed in the list '
			.'view.'
		);
	}

	/**
	 * Checks the setting of the configuration value pages for the category
	 * list.
	 */
	private function checkPagesForCategoryList() {
		$this->checkIfSysFoldersOrEmpty(
			'pages',
			TRUE,
			'sDEF',
			'This value specifies the system folders that contain the '
			.'event records for which the categories should be listed. If this '
			.'value is not set correctly, the wrong (or no) categories could '
			.'get displayed.'
		);
	}

	/**
	 * Checks the setting of the configuration value recursive,
	 * but also allows empty values.
	 */
	private function checkRecursive() {
		$this->checkIfInteger(
			'recursive',
			TRUE,
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
	 */
	private function checkCommonFrontEndSettings() {
		$this->checkCssStyledContent();
		$this->checkStaticIncluded();
		$this->checkTemplateFile(TRUE);
		$this->checkCssFileFromConstants();
		$this->checkSalutationMode(TRUE);
		$this->checkCssClassNames();
		$this->checkWhatToDisplay();
	}

	/**
	 * Checks the setting of the configuration value eventEditorFeGroupID.
	 */
	private function checkEventEditorFeGroupID() {
		$this->checkIfPositiveInteger(
			'eventEditorFeGroupID',
			TRUE,
			's_fe_editing',
			'This value specifies the front-end user group that is allowed to '
				.'enter and edit event records in the front end. If this value '
				.'is not set correctly, FE editing for events will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value defaultEventVipsFeGroupID.
	 */
	private function checkDefaultEventVipsFeGroupID() {
		$this->checkIfPositiveIntegerOrEmpty(
			'defaultEventVipsFeGroupID',
			TRUE,
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
	 */
	private function checkCreateEventsPID() {
		$this->checkIfSingleSysFolderNotEmpty(
			'createEventsPID',
			TRUE,
			's_fe_editing',
			'This value specifies the page on which FE-entered events will be '
				.'stored. If this value is not set correctly, those event '
				.'records will be dumped in the TYPO3 root page.'
		);
	}

	/**
	 * Checks the setting of the configuration value eventSuccessfullySavedPID.
	 */
	private function checkEventSuccessfullySavedPID() {
		$this->checkIfSingleFePageNotEmpty(
			'eventSuccessfullySavedPID',
			TRUE,
			's_fe_editing',
			'This value specifies the page to which the user will be '
				.'redirected after saving an event record in the front end. If '
				.'this value is not set correctly, the redirect will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value allowedExtensionsForUpload.
	 */
	private function checkAllowedExtensionsForUpload() {
		$this->checkForNonEmptyString(
			'allowedExtensionsForUpload',
			TRUE,
			's_fe_editing',
			'This value specifies the list of allowed extensions of files to ' .
				'upload in the FE editor. If this value is empty, files ' .
				'cannot be uploaded.'
		);
	}

	/**
	 * Checks the setting of the configuration value eventEditorPID.
	 */
	private function checkEventEditorPID() {
		$this->checkIfSingleFePageNotEmpty(
			'eventEditorPID',
			TRUE,
			's_fe_editing',
			'This value specifies the page that contains the plug-in for '
				.'editing event records in the front end. If this value is not '
				.'set correctly, the <em>edit</em> link in the <em>events '
				.'which I have entered</em> list will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value thankYouAfterRegistrationPID.
	 */
	private function checkThankYouAfterRegistrationPID() {
		$this->checkIfSingleFePageNotEmpty(
			'thankYouAfterRegistrationPID',
			TRUE,
			's_registration',
			'This value specifies the page that will be displayed after a user '
				.'signed up for an event. If this value is not set correctly, '
				.'the user will see the list of events instead.'
		);
	}

	/**
	 * Checks the setting of the configuration value pageToShowAfterUnregistrationPID.
	 */
	private function checkPageToShowAfterUnregistrationPID() {
		$this->checkIfSingleFePageNotEmpty(
			'pageToShowAfterUnregistrationPID',
			TRUE,
			's_registration',
			'This value specifies the page that will be displayed after a user '
				.'has unregistered from an event. If this value is not set correctly, '
				.'the user will see the list of events instead.'
		);
	}

	/**
	 * Checks the setting of the configuration value bankTransferUID.
	 */
	private function checkBankTransferUid() {
		$this->checkIfPositiveIntegerOrEmpty(
			'bankTransferUID',
			FALSE,
			'',
			'This value specifies the payment method that corresponds to '
				.'a bank transfer. If this value is not set correctly, '
				.'validation of the bank data in the event registration '
				.'form will not work correctly.'
		);
	}

	/**
	 * Checks the CSV-related settings.
	 */
	protected function check_tx_seminars_Bag_Event_csv() {
		$this->checkAllowAccessToCsv();
		$this->checkCharsetForCsv();
		$this->checkFilenameForEventsCsv();
		$this->checkFilenameForRegistrationsCsv();
		$this->checkFieldsFromEventsForCsv();
		$this->checkFieldsFromFeUserForCsv();
		$this->checkFieldsFromAttendanceForCsv();
		$this->checkFieldsFromFeUserForEmailCsv();
		$this->checkFieldsFromAttendanceForEmailCsv();
		$this->checkShowAttendancesOnRegistrationQueueInEmailCsv();
	}

	/**
	 * Checks the setting of the configuration value allowAccessToCsv.
	 */
	private function checkAllowAccessToCsv() {
		$this->checkIfBoolean(
			'allowAccessToCsv',
			FALSE,
			'',
			'This value specifies whether the access check for the CSV export '
				.'will be overridden. '
				.'If this value is not set correctly, anyone could use the CSV '
				.'export, gaining access to sensitive data.'
		);
	}

	/**
	 * Checks the setting of the configuration value charsetForCsv.
	 */
	private function checkCharsetForCsv() {
		$this->checkForNonEmptyString(
			'charsetForCsv',
			FALSE,
			'',
			'This value specifies the charset to use for the CSV export. '
				.'If this value is not set, no charset information will be '
				.'provided for CSV downloads.'
		);
	}

	/**
	 * Checks the setting of the configuration value filenameForEventsCsv.
	 */
	private function checkFilenameForEventsCsv() {
		$this->checkForNonEmptyString(
			'filenameForEventsCsv',
			FALSE,
			'',
			'This value specifies the file name to suggest for the CSV export '
				.'of event records. '
				.'If this value is not set, an empty filename will be used for '
				.'saving the CSV file which will cause problems.'
		);
	}

	/**
	 * Checks the setting of the configuration value filenameForRegistrationsCsv.
	 */
	private function checkFilenameForRegistrationsCsv() {
		$this->checkForNonEmptyString(
			'filenameForRegistrationsCsv',
			FALSE,
			'',
			'This value specifies the file name to suggest for the CSV export '
				.'of registration records. '
				.'If this value is not set, an empty filename will be used for '
				.'saving the CSV file which will cause problems.'
		);
	}

	/**
	 * Checks the setting of the configuration value fieldsFromEventsForCsv.
	 */
	private function checkFieldsFromEventsForCsv() {
		$this->checkIfMultiInSetNotEmpty(
			'fieldsFromEventsForCsv',
			FALSE,
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
	 */
	private function checkFieldsFromFeUserForCsv() {
		$this->checkIfMultiInTableOrEmpty(
			'fieldsFromFeUserForCsv',
			FALSE,
			'',
			'These values specify the FE user fields to export via CSV in web ' .
				'mode. A mistyped field name will cause the field to not get ' .
				'included.',
			'fe_users'
		);
	}

	/**
	 * Checks the setting of the configuration value fieldsFromAttendanceForCsv.
	 */
	private function checkFieldsFromAttendanceForCsv() {
		$this->checkIfMultiInTableOrEmpty(
			'fieldsFromAttendanceForCsv',
			FALSE,
			'',
			'These values specify the registration fields to export via CSV in ' .
				'web mode. A mistyped field name will cause the field to not get ' .
				'included.',
			'tx_seminars_attendances'
		);
	}

	/**
	 * Checks the setting of the configuration value fieldsFromFeUserForEmailCsv.
	 */
	private function checkFieldsFromFeUserForEmailCsv() {
		$this->checkIfMultiInTableOrEmpty(
			'fieldsFromFeUserForCliCsv',
			FALSE,
			'',
			'These values specify the FE user fields to export via CSV in e-mail ' .
				'mode. A mistyped field name will cause the field to not get ' .'
				included.',
			'fe_users'
		);
	}

	/**
	 * Checks the setting of the configuration value fieldsFromAttendanceForEmailCsv.
	 */
	private function checkFieldsFromAttendanceForEmailCsv() {
		$this->checkIfMultiInTableOrEmpty(
			'fieldsFromAttendanceForEmailCsv',
			FALSE,
			'',
			'These values specify the registration fields to export via CSV in ' .
				'e-mail mode. A mistyped field name will cause the field to not ' .
				'get included.',
			'tx_seminars_attendances'
		);
	}

	/**
	 * Checks the setting of the configuration value showToBeAnnouncedForEmptyPrice.
	 */
	private function checkShowToBeAnnouncedForEmptyPrice() {
		$this->checkIfBoolean(
			'showToBeAnnouncedForEmptyPrice',
			FALSE,
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
	 */
	private function checkRegistrationEditorTemplateFile() {
		$errorMessage = 'This specifies the HTML template for the registration '.
			'form. If this file is not available, the registration form cannot '.
			'be used.';

		$this->checkForNonEmptyString(
			'registrationEditorTemplateFile',
			FALSE,
			'',
			$errorMessage
		);

		if ($this->objectToCheck->hasConfValueString(
			'registrationEditorTemplateFile', '', TRUE
		)) {
			$rawFileName = $this->objectToCheck->getConfValueString(
				'registrationEditorTemplateFile', '', TRUE, TRUE
			);
			if (!is_file($GLOBALS['TSFE']->tmpl->getFileName($rawFileName))) {
				$message = 'The specified HTML template file <strong>' .
					htmlspecialchars($rawFileName) .  '</strong> cannot be read. ' .
					$errorMessage . ' ' .
					'Please either create the file <strong>' . $rawFileName .
					'</strong> or select an existing file using the TS setup ' .
					'variable <strong>'.$this->getTSSetupPath() .
					'templateFile</strong> or via FlexForms.';
				$this->setErrorMessage($message);
			}
		}
	}

	/**
	 * Checks the setting of the configuration value
	 * logOutOneTimeAccountsAfterRegistration.
	 */
	private function checkLogOutOneTimeAccountsAfterRegistration() {
		$this->checkIfBoolean(
			'logOutOneTimeAccountsAfterRegistration',
			FALSE,
			'',
			'This value specifies whether one-time FE user accounts will '
				.'automatically be logged out after registering for an event. '
				.'If this value is incorrect, the automatic logout will not work.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * numberOfFirstRegistrationPage.
	 */
	private function checkNumberOfFirstRegistrationPage() {
		$this->checkIfPositiveInteger(
			'numberOfFirstRegistrationPage',
			FALSE,
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
	 */
	private function checkNumberOfLastRegistrationPage() {
		$this->checkIfPositiveInteger(
			'numberOfLastRegistrationPage',
			FALSE,
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
	 */
	private function checkNumberOfClicksForRegistration() {
		$this->checkIfInteger(
			'numberOfClicksForRegistration',
			TRUE,
			's_registration',
			'This specifies the number of clicks for registration'
		);

		$this->checkIfIntegerInRange(
			'numberOfClicksForRegistration',
			2,
			3,
			TRUE,
			's_registration',
			'This specifies the number of clicks for registration.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendParametersToThankYouAfterRegistrationPageUrl.
	 */
	private function checkSendParametersToThankYouAfterRegistrationPageUrl() {
		$this->checkIfBoolean(
			'sendParametersToThankYouAfterRegistrationPageUrl',
			TRUE,
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
	 */
	private function checkSendParametersToPageToShowAfterUnregistrationUrl() {
		$this->checkIfBoolean(
			'sendParametersToPageToShowAfterUnregistrationUrl',
			TRUE,
			's_registration',
			'This value specifies whether the sending of parameters to the page '
				.'which is shown after an unregistration should be enabled or '
				.'not. If this value is incorrect the sending of parameters '
				.'will not be enabled or disabled correctly.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * createAdditionalAttendeesAsFrontEndUsers.
	 */
	private function checkCreateAdditionalAttendeesAsFrontEndUsers() {
		$this->checkIfBoolean(
			'createAdditionalAttendeesAsFrontEndUsers',
			TRUE,
			's_registration',
			'This value specifies whether additional attendees will be ' .
				'stored as FE user record . If this value is incorrect, ' .
				'those records will no be created, and the registration ' .
				'form will look different than intended.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sysFolderForAdditionalAttendeeUsersPID.
	 */
	private function checkSysFolderForAdditionalAttendeeUsersPID() {
		$this->checkIfSingleSysFolderNotEmpty(
			'sysFolderForAdditionalAttendeeUsersPID',
			TRUE,
			's_registration',
			'This value specifies the system folder in which the FE user ' .
				'records for additional attendees will be stored. If this ' .
				'value is not set correctly, those records will be dumped ' .
				'in the TYPO3 root page.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * userGroupUidsForAdditionalAttendeesFrontEndUsers.
	 */
	private function checkUserGroupUidsForAdditionalAttendeesFrontEndUsers() {
		$this->checkIfPidListNotEmpty(
			'userGroupUidsForAdditionalAttendeesFrontEndUsers',
			TRUE,
			's_registration',
			'This value specifies the FE user groups for the FE users ' .
				'created for additional attendees. If this value is not set ' .
				'correctly, those FE users might not be able to log in.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * unregistrationDeadlineDaysBeforeBeginDate.
	 */
	private function checkUnregistrationDeadlineDaysBeforeBeginDate() {
		$this->checkIfPositiveIntegerOrEmpty(
			'unregistrationDeadlineDaysBeforeBeginDate',
			FALSE,
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
	 */
	private function checkSendNotification() {
		$this->checkIfBoolean(
			'sendNotification',
			FALSE,
			'',
			'This value specifies whether a notification e-mail should be sent '
				.'to the organizer after a user has registered. If this value '
				.'is not set correctly, the sending of notifications probably '
				.'will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendNotificationOnUnregistration.
	 */
	private function checkSendNotificationOnUnregistration() {
		$this->checkIfBoolean(
			'sendNotificationOnUnregistration',
			FALSE,
			'',
			'This value specifies whether a notification e-mail should be sent '
				.'to the organizer after a user has unregistered. If this value '
				.'is not set correctly, the sending of notifications probably '
				.'will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendNotificationOnRegistrationForQueue.
	 */
	private function checkSendNotificationOnRegistrationForQueue() {
		$this->checkIfBoolean(
			'sendNotificationOnRegistrationForQueue',
			FALSE,
			'',
			'This value specifies whether a notification e-mail should be sent '
				.'to the organizer after someone registered for the queue. If '
				.'this value is not set correctly, the sending of notifications '
				.'probably will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendNotificationOnQueueUpdate.
	 */
	private function checkSendNotificationOnQueueUpdate() {
		$this->checkIfBoolean(
			'sendNotificationOnQueueUpdate',
			FALSE,
			'',
			'This value specifies whether a notification e-mail should be sent '
				.'to the organizer after the queue has been updated. If '
				.'this value is not set correctly, the sending of notifications '
				.'probably will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value sendConfirmation.
	 */
	private function checkSendConfirmation() {
		$this->checkIfBoolean(
			'sendConfirmation',
			FALSE,
			'',
			'This value specifies whether a confirmation e-mail should be sent '
				.'to the user after the user has registered. If this value is '
				.'not set correctly, the sending of notifications probably will '
				.'not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendConfirmationOnUnregistration.
	 */
	private function checkSendConfirmationOnUnregistration() {
		$this->checkIfBoolean(
			'sendConfirmationOnUnregistration',
			FALSE,
			'',
			'This value specifies whether a confirmation e-mail should be sent '
				.'to the user after the user has unregistered. If this value is '
				.'not set correctly, the sending of notifications probably will '
				.'not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendConfirmationOnRegistrationForQueue.
	 */
	private function checkSendConfirmationOnRegistrationForQueue() {
		$this->checkIfBoolean(
			'sendConfirmationOnRegistrationForQueue',
			FALSE,
			'',
			'This value specifies whether a confirmation e-mail should be sent '
				.'to the user after the user has registered for the queue. If '
				.'this value is not set correctly, the sending of notifications '
				.'probably will not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * sendConfirmationOnQueueUpdate.
	 */
	private function checkSendConfirmationOnQueueUpdate() {
		$this->checkIfBoolean(
			'sendConfirmationOnQueueUpdate',
			FALSE,
			'',
			'This value specifies whether a confirmation e-mail should be sent '
				.'to the user after the queue has been updated. If this value is '
				.'not set correctly, the sending of notifications probably will '
				.'not work as expected.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * allowRegistrationForStartedEvents.
	 */
	private function checkAllowRegistrationForStartedEvents() {
		$this->checkIfBoolean(
			'allowRegistrationForStartedEvents',
			FALSE,
			'',
			'This value specifies whether registration is possible even when ' .
				'an event already has started. ' .
				'If this value is incorrect, registration might be possible ' .
				'even when this is not desired (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * allowRegistrationForEventsWithoutDate.
	 */
	private function checkAllowRegistrationForEventsWithoutDate() {
		$this->checkIfBoolean(
			'allowRegistrationForEventsWithoutDate',
			FALSE,
			'',
			'This value specifies whether registration is possible for ' .
				'events without a fixed date. ' .
				'If this value is incorrect, registration might be possible ' .
				'even when this is not desired (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * allowUnregistrationWithEmptyWaitingList.
	 */
	private function checkAllowUnregistrationWithEmptyWaitingList() {
		$this->checkIfBoolean(
			'allowUnregistrationWithEmptyWaitingList',
			FALSE,
			'',
			'This value specifies whether unregistration is possible even when '
				.'there are no registrations on the waiting list yet. '
				.'If this value is incorrect, unregistration might be possible '
				.'even when this is not desired (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value externalLinkTarget.
	 * But currently does nothing as we don't think there's something to check
	 * for.
	 */
	private function checkExternalLinkTarget() {
		// Does nothing.
	}

	/**
	 * Checks the setting of the configuration value
	 * showSingleEvent.
	 */
	private function checkShowSingleEvent() {
		$this->checkIfPositiveIntegerOrEmpty(
			'showSingleEvent',
			TRUE,
			's_template_special',
			'This value specifies which fixed single event should be shown. If '
				.'this value is not set correctly, an error message will be '
				.'shown instead.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * limitListViewToEventTypes.
	 */
	private function checkLimitListViewToEventTypes() {
		$this->checkIfPidListOrEmpty(
			'limitListViewToEventTypes',
			TRUE,
			's_listView',
			'This value specifies the event types by which the list view ' .
				'should be filtered. If this value is not set correctly, ' .
				'some events might unintentionally get hidden or shown.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * limitListViewToCategories.
	 */
	private function checkLimitListViewToCategories() {
		$this->checkIfPidListOrEmpty(
			'limitListViewToCategories',
			TRUE,
			's_listView',
			'This value specifies the categories by which the list view ' .
				'should be filtered. If this value is not set correctly, ' .
				'some events might unintentionally get hidden or shown.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * limitListViewToPlaces.
	 */
	private function checkLimitListViewToPlaces() {
		$this->checkIfPidListOrEmpty(
			'limitListViewToPlaces',
			TRUE,
			's_listView',
			'This value specifies the places for which the list view ' .
				'should be filtered. If this value is not set correctly, ' .
				'some events might unintentionally get hidden or shown.'
		);
	}

	/**
	 * Checks the setting of the configuration value
	 * limitListViewToOrganizers.
	 */
	private function checkLimitListViewToOrganizers() {
		$this->checkIfPidListOrEmpty(
			'limitListViewToOrganizers',
			TRUE,
			's_listView',
			'This value specifies the organizers for which the list view ' .
				'should be filtered. If this value is not set correctly, ' .
				'some events might unintentionally get hidden or shown.'
		);
	}

	/**
	 * Checks the setting of the configuration value skipRegistrationCollisionCheck.
	 */
	private function checkSkipRegistrationCollisionCheck() {
		$this->checkIfBoolean(
			'skipRegistrationCollisionCheck',
			FALSE,
			'',
			'This value specifies whether the registration collision check ' .
				'should be disabled for all events. If this value is incorrect, ' .
				'the registration collision check might be enabled although it ' .
				'should be disabled (or vice versa).'
		);
	}

	/**
	 * Checks the setting of the configuration value categoriesInListView.
	 */
	private function checkCategoryIconDisplay() {
		$this->checkIfSingleInSetNotEmpty(
			'categoriesInListView',
			TRUE,
			's_listView',
			'This setting determines whether the seminar category is shown, as ' .
				'icon and text, as text only or as icon only. If this value is ' .
				'not set correctly, the category will only be shown as text.',
			array(
				'both',
				'text',
				'icon',
			)
		);
	}

	/**
	 * Checks the settings for the image width, and height in the list view.
	 */
	private function checkSeminarImageSizes() {
		$this->checkListViewImageWidth();
		$this->checkListViewImageHeight();
	}

	/**
	 * Checks the settings for seminarImageListViewWidth.
	 */
	private function checkListViewImageWidth() {
		$this->checkIfPositiveInteger(
			'seminarImageListViewWidth',
			FALSE,
			'',
			'This value specifies the width of the image of a seminar. If this ' .
				'value is not set, the image will be shown in full size.'
		);

	}

	/**
	 * Checks the settings for seminarImageListViewHeight.
	 */
	private function checkListViewImageHeight() {
		$this->checkIfPositiveInteger(
			'seminarImageListViewHeight',
			FALSE,
			'',
			'This value specifies the height of the image of a seminar. If ' .
				'this value is not set, the image will be shown in full size.'
		);
	}

	/**
	 * Checks the settings for the image width, and height in the single view.
	 */
	private function checkSingleViewImageSizes() {
		$this->checkSingleViewImageWidth();
		$this->checkSingleViewImageHeight();
	}

	/**
	 * Checks the settings for seminarImageSingleViewWidth.
	 */
	private function checkSingleViewImageWidth() {
		$this->checkIfPositiveInteger(
			'seminarImageSingleViewWidth',
			FALSE,
			'',
			'This value specifies the width of the image of a seminar. If this ' .
				'value is not set, the image will be shown in full size.'
		);
	}

	/**
	 * Checks the settings for seminarImageSingleViewHeight.
	 */
	private function checkSingleViewImageHeight() {
		$this->checkIfPositiveInteger(
			'seminarImageSingleViewHeight',
			FALSE,
			'',
			'This value specifies the height of the image of a seminar. If ' .
				'this value is not set, the image will be shown in full size.'
		);

	}

	/**
	 * Checks the setting of the configuration value showOwnerDataInSingleView.
	 */
	private function checkShowOwnerDataInSingleView() {
		$this->checkIfBoolean(
			'showOwnerDataInSingleView',
			TRUE,
			's_singleView',
			'This value specifies whether the owner data will be displayed  ' .
				'on the single view page. If this value is incorrect, ' .
				'the the data might be displayed although it should not be ' .
				'(which is a privacy issue) or vice versa.'
		);
	}

	/**
	 * Checks the setting of the configuration value ownerPictureMaxWidth.
	 */
	private function checkOwnerPictureMaxWidth() {
		$this->checkIfPositiveInteger(
			'ownerPictureMaxWidth',
			FALSE,
			'',
			'This value specifies the maximum width for the owner picture ' .
				'on the single view page. If this value is not set ' .
				'correctly, the image might be too large or not get ' .
				'displayed at all.'
		);
	}

	/**
	 * Checks the setting for displaySearchFormFields.
	 */
	private function checkDisplaySearchFormFields() {
		$this->checkIfMultiInSetOrEmpty(
			'displaySearchFormFields',
			TRUE,
			's_listView',
			'This value specifies which search widget fields to display in the ' .
				'list view. The search widget will not display any fields at ' .
				'all if this value is empty or contains only invalid keys.',
			array(
				'event_type', 'language', 'country' , 'city' , 'place',
				'full_text_search', 'date', 'age', 'organizer', 'price'
			)
		);
	}

	/**
	 * Checks the settings for numberOfYearsInDateFilter.
	 */
	private function checkNumberOfYearsInDateFilter() {
		$this->checkIfPositiveInteger(
			'numberOfYearsInDateFilter',
			TRUE,
			's_listView',
			'This value specifies the number years of years the user can ' .
				'search for events in the event list. The date search will ' .
				'have an empty drop-down for the year if this variable is ' .
				'misconfigured.'
		);
	}

	/**
	 * Checks the setting of the configuration value displayFrontEndEditorFields.
	 */
	private function checkDisplayFrontEndEditorFields() {
		$this->checkIfMultiInSetOrEmpty(
			'displayFrontEndEditorFields',
			TRUE,
			's_fe_editing',
			'This value specifies which fields should be displayed in the ' .
				'fe-editor. Incorrect values will cause the fields not to be ' .
				'displayed.',
			array(
				'subtitle',
				'accreditation_number',
				'credit_points',
				'categories',
				'event_type',
				'cancelled',
				'teaser',
				'description',
				'additional_information',
				'begin_date',
				'end_date',
				'begin_date_registration',
				'deadline_early_bird',
				'deadline_registration',
				'needs_registration',
				'allows_multiple_registrations',
				'queue_size',
				'attendees_min',
				'attendees_max',
				'offline_attendees',
				'target_groups',
				'price_regular',
				'price_regular_early',
				'price_regular_board',
				'price_special',
				'price_special_early',
				'price_special_board',
				'payment_methods',
				'place',
				'room',
				'lodgings',
				'foods',
				'speakers',
				'leaders',
				'partners',
				'tutors',
				'checkboxes',
				'uses_terms_2',
				'attached_file_box',
				'notes',
			)
		);
	}

	/**
	 * Checks the setting for allowFrontEndEditingOfSpeakers.
	 */
	private function checkAllowFrontEndEditingOfSpeakers() {
		$this->checkIfBoolean(
			'allowFrontEndEditingOfSpeakers',
			TRUE,
			's_fe_editing',
			'This value specifies whether front-end editing of speakers is ' .
				'possible. If this value is incorrect, front-end editing of ' .
				'speakers might be possible even when this is not desired ' .
				'(or vice versa).'
		);
	}

	/**
	 * Checks the setting for allowFrontEndEditingOfPlaces.
	 */
	private function checkAllowFrontEndEditingOfPlaces() {
		$this->checkIfBoolean(
			'allowFrontEndEditingOfPlaces',
			TRUE,
			's_fe_editing',
			'This value specifies whether front-end editing of places is ' .
				'possible. If this value is incorrect, front-end editing of ' .
				'places might be possible even when this is not desired ' .
				'(or vice versa).'
		);
	}

	/**
	 * Checks the setting for allowFrontEndEditingOfCheckboxes.
	 */
	private function checkAllowFrontEndEditingOfCheckboxes() {
		$this->checkIfBoolean(
			'allowFrontEndEditingOfCheckboxes',
			TRUE,
			's_fe_editing',
			'This value specifies whether front-end editing of checkboxes is ' .
				'possible. If this value is incorrect, front-end editing of ' .
				'checkboxes might be possible even when this is not desired ' .
				'(or vice versa).'
		);
	}

	/**
	 * Checks the setting for allowFrontEndEditingOfTargetGroups.
	 */
	private function checkAllowFrontEndEditingOfTargetGroups() {
		$this->checkIfBoolean(
			'allowFrontEndEditingOfTargetGroups',
			TRUE,
			's_fe_editing',
			'This value specifies whether front-end editing of target groups ' .
				'is possible. If this value is incorrect, front-end editing of ' .
				'target groups might be possible even when this is not desired ' .
				'(or vice versa).'
		);
	}

	/**
	 * Checks the configuration for requiredFrontEndEditorFields.
	 */
	private function checkRequiredFrontEndEditorFields() {
		$this->checkIfMultiInSetOrEmpty(
			'requiredFrontEndEditorFields',
			TRUE,
			's_fe_editing',
			'This value specifies which fields are required to be filled when ' .
				'editing an event. Some fields will be not be required if ' .
				'this configuration is incorrect.',
			array(
				'subtitle',
				'accreditation_number',
				'credit_points',
				'categories',
				'event_type',
				'cancelled',
				'teaser',
				'description',
				'additional_information',
				'begin_date',
				'end_date',
				'begin_date_registration',
				'deadline_early_bird',
				'deadline_registration',
				'needs_registration',
				'allows_multiple_registrations',
				'queue_size',
				'attendees_min',
				'attendees_max',
				'offline_attendees',
				'target_groups',
				'price_regular',
				'price_regular_early',
				'price_regular_board',
				'price_special',
				'price_special_early',
				'price_special_board',
				'payment_methods',
				'place',
				'room',
				'lodgings',
				'foods',
				'speakers',
				'leaders',
				'partners',
				'tutors',
				'checkboxes',
				'uses_terms_2',
				'attached_file_box',
				'notes',
			)
		);

		// checks whether the required fields are visible
		$this->checkIfMultiInSetOrEmpty(
			'requiredFrontEndEditorFields',
			TRUE,
			's_fe_editing',
			'This value specifies which fields are required to be filled when ' .
				'editing an event. Some fields are set to required but are ' .
				'actually not configured to be visible in the form. The form ' .
				'cannot be submitted as long as this inconsistency remains.',
			t3lib_div::trimExplode(
				',',
				$this->objectToCheck->getConfValueString(
					'displayFrontEndEditorFields', 's_fe_editing'
				),
				TRUE
			)
		);
	}

	/**
	 * Checks the configuration for requiredFrontEndEditorPlaceFields.
	 */
	private function checkRequiredFrontEndEditorPlaceFields() {
		$this->checkIfMultiInSetOrEmpty(
			'requiredFrontEndEditorPlaceFields',
			FALSE,
			'',
			'This value specifies which fields are required to be filled when ' .
				'editing a pkace. Some fields will be not be required if ' .
				'this configuration is incorrect.',
			array(
				'address',
				'zip',
				'city',
				'country',
				'homepage',
				'directions',
			)
		);
	}

	/**
	 * Checks whether the HTML template for the event editor is provided and the
	 * file exists.
	 */
	private function checkEventEditorTemplateFile() {
		$errorMessage = 'This specifies the HTML template for the event editor. '.
			'If this file is not available, the event editor cannot be used.';

		$this->checkForNonEmptyString(
			'eventEditorTemplateFile',
			FALSE,
			'',
			$errorMessage
		);

		if ($this->objectToCheck->hasConfValueString(
			'eventEditorTemplateFile', '', TRUE
		)) {
			$rawFileName = $this->objectToCheck->getConfValueString(
				'eventEditorTemplateFile', '', TRUE, TRUE
			);
			if (!is_file($GLOBALS['TSFE']->tmpl->getFileName($rawFileName))) {
				$message = 'The specified HTML template file <strong>' .
					htmlspecialchars($rawFileName) .  '</strong> cannot be read. ' .
					$errorMessage . ' ' .
					'Please either create the file <strong>' . $rawFileName .
					'</strong> or select an existing file using the TS setup ' .
					'variable <strong>'.$this->getTSSetupPath() .
					'templateFile</strong> or via FlexForms.';
				$this->setErrorMessage($message);
			}
		}
	}

	/**
	 * Checks the setting of the configuration value showOnlyEventsWithVacancies.
	 */
	private function checkShowOnlyEventsWithVacancies() {
		$this->checkIfBoolean(
			'showOnlyEventsWithVacancies',
			TRUE,
			's_listView',
			'This value specifies whether only events with vacancies should be ' .
				'shown in the list view. If this value is not configured ' .
				'properly, events with no vacancies will be shown in the ' .
				'list view.'
		);
	}

	/**
	 * Checks the relation between last and first page and the number of clicks.
	 */
	private function checkRegistrationPageNumbers() {
		$clicks = $this->objectToCheck->getConfValueInteger(
			'numberOfClicksForRegistration', 's_registration'
		);
		$firstPage = $this->objectToCheck->getConfValueInteger(
			'numberOfFirstRegistrationPage'
		);
		$lastPage = $this->objectToCheck->getConfValueInteger(
			'numberOfLastRegistrationPage'
		);
		$calculatedSteps = $lastPage - $firstPage + 2;

		if ($calculatedSteps != $clicks) {
			$message = 'The specified number of clicks does not correspond ' .
				'to the number of the first and last registration page. ' .
				'Please correct the values of <strong>' .
				'numberOfClicksForRegistration</strong>, ' .
				'<strong>numberOfFirstRegistrationPage</strong> or ' .
				'<strong>numberOfLastRegistrationPage</strong>. A not ' .
				'properly configured setting will lead to a misleading ' .
				'number of steps, shown on the registration page.';
			$this->setErrorMessage($message);
		}
	}

	/**
	 * Checks whether plugin.tx_seminars.currency is not empty and a valid ISO
	 * 4217 alpha 3.
	 */
	public function checkCurrency() {
		$this->checkIfSingleInSetNotEmpty(
			'currency',
			FALSE,
			'',
			'The specified currency setting is either empty or not a valid ' .
				'ISO 4217 alpha 3 code. Please correct the value of <strong>' .
				$this->getTSSetupPath() . 'currency</strong>.',
			tx_oelib_db::selectColumnForMultiple('cu_iso_3', 'static_currencies')
		);
	}

	/**
	 * Checks the setting of showAttendancesOnRegistrationQueueInEmailCsv
	 */
	public function checkShowAttendancesOnRegistrationQueueInEmailCsv() {
		$this->checkIfBoolean(
			'showAttendancesOnRegistrationQueueInEmailCsv',
			FALSE,
			'',
			'This value specifies if attendances on the registration queue ' .
				'should also be exported in the CSV file in the e-mail mode.' .
				'If this is not set correctly, the attendances on the ' .
				'registration queue might not get exported.'
		);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']) {
	include_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']);
}
?>