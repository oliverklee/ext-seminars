<?php

declare(strict_types=1);

use OliverKlee\Oelib\Configuration\ConfigurationCheck;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class checks the Seminar Manager configuration for basic sanity.
 *
 * The correct functioning of this class does not rely on any HTML templates or
 * language files so it works even under the worst of circumstances.
 *
 * phpcs:disable PSR1.Methods.CamelCapsMethodName
 */
class Tx_Seminars_ConfigCheck extends ConfigurationCheck
{
    /**
     * Checks the configuration for: tx_seminars_test/.
     *
     * @return void
     */
    protected function check_tx_seminars_test()
    {
        $this->checkStaticIncluded();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_RegistrationsList/.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_RegistrationsList()
    {
    }

    /**
     * Does nothing.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/seminar_registration.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_seminar_registration()
    {
        $this->checkCommonFrontEndSettings();

        $this->checkRegistrationFlag();
        if (!$this->objectToCheck->getConfValueBoolean('enableRegistration')) {
            $message = 'You are using the registration page although online '
                . 'registration is disabled. This will break the registration '
                . 'page and the automatic configuration check. '
                . 'Please either enable online registration by setting the TS '
                . 'setup variable <strong>' . $this->getTSSetupPath()
                . 'enableRegistration</strong> to <strong>1</strong> or remove '
                . 'the registration page.';
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
        $this->checkShowFeUserFieldsInRegistrationForm();
        $this->checkShowFeUserFieldsInRegistrationFormWithLabel();
        $this->checkThankYouAfterRegistrationPID();
        $this->checkSendParametersToThankYouAfterRegistrationPageUrl();
        $this->checkPageToShowAfterUnregistrationPID();
        $this->checkSendParametersToPageToShowAfterUnregistrationUrl();

        $this->checkCreateAdditionalAttendeesAsFrontEndUsers();
        if ($this->objectToCheck->getConfValueBoolean('createAdditionalAttendeesAsFrontEndUsers', 's_registration')) {
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
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/single_view.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_single_view()
    {
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
        if ($this->objectToCheck->getConfValueBoolean('showOwnerDataInSingleView', 's_singleView')) {
            $this->checkOwnerPictureMaxWidth();
        }
        $this->checkLimitFileDownloadToAttendees();
        $this->checkShowOnlyEventsWithVacancies();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/seminar_list.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_seminar_list()
    {
        $this->checkCommonFrontEndSettings();

        $this->checkRegistrationFlag();

        $this->checkPages();
        $this->checkRecursive();
        /** @var \Tx_Seminars_FrontEnd_DefaultController $objectToCheck */
        $objectToCheck = $this->objectToCheck;
        $this->checkListView(array_keys($objectToCheck->orderByList));

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
        $this->checkEnableSortingLinksInListView();
        $this->checkLinkToSingleView();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_Countdown.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_Countdown()
    {
        $this->checkCommonFrontEndSettings();
        $this->checkPages();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/my_vip_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_vip_events()
    {
        $this->check_Tx_Seminars_FrontEnd_DefaultController_seminar_list();
        $this->checkRegistrationsVipListPid();
        $this->checkDefaultEventVipsFeGroupID();
        $this->checkMayManagersEditTheirEvents();
        $this->checkAllowCsvExportOfRegistrationsInMyVipEventsView();

        if ($this->objectToCheck->getConfValueBoolean('mayManagersEditTheirEvents', 's_listView')) {
            $this->checkEventEditorPID();
        }
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/topic_list.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_topic_list()
    {
        $this->check_Tx_Seminars_FrontEnd_DefaultController_seminar_list();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/my_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_events()
    {
        $this->check_Tx_Seminars_FrontEnd_DefaultController_seminar_list();
    }

    /**
     * Checks the configuration for: check_Tx_Seminars_FrontEnd_DefaultController/edit_event.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_edit_event()
    {
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
     * Checks the configuration for: check_Tx_Seminars_FrontEnd_DefaultController/my_entered_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_entered_events()
    {
        $this->check_Tx_Seminars_FrontEnd_DefaultController_seminar_list();
        $this->checkEventEditorFeGroupID();
        $this->checkEventEditorPID();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_CategoryList.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_CategoryList()
    {
        $this->checkCommonFrontEndSettings();

        $this->checkRecursive();
        $this->checkTimeframeInList();

        $this->checkListPid();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/favorites_list
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_favorites_list()
    {
        $this->check_Tx_Seminars_FrontEnd_DefaultController_seminar_list();
    }

    /**
     * This check isn't actually used. It is merely needed for the unit tests.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_events_next_day()
    {
    }

    /**
     * Checks if the common frontend settings are set.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_EventHeadline()
    {
        $this->checkCommonFrontEndSettings();
    }

    /**
     * This check isn't actually used. It is merely needed for the unit tests.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_event_headline()
    {
    }

    /**
     * Checks the setting of the configuration value enableRegistration.
     *
     * @return void
     */
    private function checkRegistrationFlag()
    {
        $this->checkIfBoolean(
            'enableRegistration',
            false,
            '',
            'This value specifies whether the extension will provide online '
            . 'registration. If this value is incorrect, the online '
            . 'registration will not be enabled or disabled correctly.'
        );
    }

    /**
     * Checks the setting of the configuration value what_to_display.
     *
     * @return void
     */
    private function checkWhatToDisplay()
    {
        $this->checkIfSingleInSetNotEmpty(
            'what_to_display',
            true,
            'sDEF',
            'This value specifies the type of seminar manager plug-in to '
            . 'display. If this value is not set correctly, the wrong '
            . 'type of plug-in will be displayed.',
            [
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
            ]
        );
    }

    /**
     * Checks the setting of the configuration value hideFields.
     *
     * @return void
     */
    private function checkHideFields()
    {
        $this->checkIfMultiInSetOrEmpty(
            'hideFields',
            true,
            's_template_special',
            'This value specifies which section to remove from the details view. '
            . 'Incorrect values will cause the sections to still be displayed.',
            [
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
            ]
        );
    }

    /**
     * Checks the setting of the configuration value hideColumns.
     *
     * @return void
     */
    private function checkHideColumns()
    {
        $this->checkIfMultiInSetOrEmpty(
            'hideColumns',
            true,
            's_template_special',
            'This value specifies which columns to remove from the list view. '
            . 'Incorrect values will cause the colums to still be displayed.',
            [
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
            ]
        );
    }

    /**
     * Checks the setting of the configuration value timeframeInList.
     *
     * @return void
     */
    private function checkTimeframeInList()
    {
        $this->checkIfSingleInSetNotEmpty(
            'timeframeInList',
            true,
            's_template_special',
            'This value specifies the time-frame from which events should be '
            . 'displayed in the list view. An incorrect value will events '
            . 'from a different time-frame cause to be displayed and other '
            . 'events to not get displayed.',
            [
                'all',
                'past',
                'pastAndCurrent',
                'current',
                'currentAndUpcoming',
                'upcoming',
                'deadlineNotOver',
                'today',
            ]
        );
    }

    /**
     * Checks the setting of the configuration value showEmptyEntryInOptionLists.
     *
     * @return void
     */
    private function checkShowEmptyEntryInOptionLists()
    {
        $this->checkIfBoolean(
            'showEmptyEntryInOptionLists',
            true,
            's_template_special',
            'This value specifies whether the option boxes in the selector widget '
            . 'will contain a dummy entry called "not selected". This is only '
            . 'needed if you changed the HTML template to show the selectors '
            . 'as dropdown menues. If this value is incorrect, the dummy entry '
            . 'might get displayed when this is not intended (or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value hidePageBrowser.
     *
     * @return void
     */
    private function checkHidePageBrowser()
    {
        $this->checkIfBoolean(
            'hidePageBrowser',
            true,
            's_template_special',
            'This value specifies whether the page browser in the list view '
            . 'will be displayed. If this value is incorrect, the page '
            . 'browser might get displayed when this is not intended (or '
            . 'vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value hideCanceledEvents.
     *
     * @return void
     */
    private function checkHideCanceledEvents()
    {
        $this->checkIfBoolean(
            'hideCanceledEvents',
            true,
            's_template_special',
            'This value specifies whether canceled events will be removed '
            . 'from the list view. If this value is incorrect, canceled '
            . 'events might get displayed when this is not intended (or '
            . 'vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value sortListViewByCategory.
     *
     * @return void
     */
    private function checkSortListViewByCategory()
    {
        $this->checkIfBoolean(
            'sortListViewByCategory',
            true,
            's_template_special',
            'This value specifies whether the list view should be sorted by '
            . 'category before applying the normal sorting. If this value '
            . 'is incorrect, the list view might get sorted by category '
            . 'when this is not intended (or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value generalPriceInList.
     *
     * @return void
     */
    private function checkGeneralPriceInList()
    {
        $this->checkIfBoolean(
            'generalPriceInList',
            true,
            's_template_special',
            'This value specifies whether the column header for the standard '
            . 'price in the list view will be just <em>Price</em> instead '
            . 'of <em>Standard price</em>. '
            . 'If this value is incorrect, the wrong label might be used.'
        );
    }

    /**
     * Checks the setting of the configuration value generalPriceInSingle.
     *
     * @return void
     */
    private function checkGeneralPriceInSingle()
    {
        $this->checkIfBoolean(
            'generalPriceInSingle',
            true,
            's_template_special',
            'This value specifies whether the heading for the standard price '
            . 'in the detailed view and on the registration page will be '
            . 'just <em>Price</em> instead of <em>Standard price</em>. '
            . 'If this value is incorrect, the wrong label might be used.'
        );
    }

    /**
     * Checks the setting of the configuration value omitDateIfSameAsPrevious.
     *
     * @return void
     */
    private function checkOmitDateIfSameAsPrevious()
    {
        $this->checkIfBoolean(
            'omitDateIfSameAsPrevious',
            true,
            's_template_special',
            'This value specifies whether to omit the date in the '
            . 'list view if it is the same as the previous item\'s. '
            . 'If this value is incorrect, the date might be omited '
            . 'although this is not intended (or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value
     * accessToFrontEndRegistrationLists.
     *
     * @return void
     */
    private function checkAccessToFrontEndRegistrationLists()
    {
        $this->checkIfSingleInSetNotEmpty(
            'accessToFrontEndRegistrationLists',
            false,
            '',
            'This value specifies who is able to see the registered persons  ' .
            'an event in the front end. ' .
            'If this value is incorrect, persons may access the ' .
            'registration lists although they should not be allowed to ' .
            '(or vice versa).',
            ['attendees_and_managers', 'login', 'world']
        );
    }

    /**
     * Checks the setting of the configuration value
     * allowCsvExportOfRegistrationsInMyVipEventsView.
     *
     * @return void
     */
    private function checkAllowCsvExportOfRegistrationsInMyVipEventsView()
    {
        $this->checkIfBoolean(
            'allowCsvExportOfRegistrationsInMyVipEventsView',
            false,
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
     *
     * @return void
     */
    private function checkMayManagersEditTheirEvents()
    {
        $this->checkIfBoolean(
            'mayManagersEditTheirEvents',
            true,
            's_listView',
            'This value specifies whether VIPs may edit their events. If this ' .
            'value is incorrect, VIPs may be allowed to edit their events ' .
            ' although they should not be allowed to (or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value eventFieldsOnRegistrationPage.
     *
     * @return void
     */
    private function checkEventFieldsOnRegistrationPage()
    {
        $this->checkIfMultiInSetNotEmpty(
            'eventFieldsOnRegistrationPage',
            true,
            's_template_special',
            'This value specifies which data fields of the selected event '
            . 'will be displayed on the registration page. '
            . 'Incorrect values will cause those fields to not get displayed.',
            ['uid', 'title', 'price_regular', 'price_special', 'vacancies']
        );
    }

    /**
     * Checks the setting of the configuration value showRegistrationFields.
     *
     * @return void
     */
    private function checkShowRegistrationFields()
    {
        $this->checkIfMultiInSetNotEmpty(
            'showRegistrationFields',
            true,
            's_template_special',
            'This value specifies which registration fields ' .
            'will be displayed on the registration page. ' .
            'Incorrect values will cause those fields to not get displayed.',
            [
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
                'first_name',
                'last_name',
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
                'terms_2',
            ]
        );
    }

    /**
     * Checks the setting of the configuration value showFeUserFieldsInRegistrationForm.
     *
     * @return void
     */
    private function checkShowFeUserFieldsInRegistrationForm()
    {
        $this->checkIfMultiInTableOrEmpty(
            'showFeUserFieldsInRegistrationFormMail',
            false,
            '',
            'These values specify the FE user fields to show in the registration form. ' .
            'A mistyped field name will cause the field to not get included.',
            'fe_users'
        );
    }

    /**
     * Checks the setting of the configuration value showFeUserFieldsInRegistrationFormWithLabel.
     *
     * @return void
     */
    private function checkShowFeUserFieldsInRegistrationFormWithLabel()
    {
        $this->checkIfMultiInTableOrEmpty(
            'showFeUserFieldsInRegistrationFormWithLabel',
            false,
            '',
            'These values specify the FE user labels to show in the registration form. ' .
            'A mistyped field name will cause the label to not get displayed.',
            'fe_users'
        );
    }

    /**
     * Checks the setting of the configuration value showSpeakerDetails.
     *
     * @return void
     */
    private function checkShowSpeakerDetails()
    {
        $this->checkIfBoolean(
            'showSpeakerDetails',
            true,
            's_template_special',
            'This value specifies whether to show detailed information of '
            . 'the speakers in the single view. '
            . 'If this value is incorrect, the detailed information might '
            . 'be shown although this is not intended (or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value showSiteDetails.
     *
     * @return void
     */
    private function checkShowSiteDetails()
    {
        $this->checkIfBoolean(
            'showSiteDetails',
            true,
            's_template_special',
            'This value specifies whether to show detailed information of '
            . 'the locations in the single view. '
            . 'If this value is incorrect, the detailed information might '
            . 'be shown although this is not intended (or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value limitFileDownloadToAttendees.
     *
     * @return void
     */
    private function checkLimitFileDownloadToAttendees()
    {
        $this->checkIfBoolean(
            'limitFileDownloadToAttendees',
            true,
            's_singleView',
            'This value specifies whether the list of attached files is only ' .
            'shown to logged in and registered attendees. If this value is ' .
            'incorrect, the attached files may be shown to the public ' .
            'although they should be visible only to the attendees ' .
            '(or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value listPID.
     *
     * @return void
     */
    private function checkListPid()
    {
        $this->checkIfSingleFePageNotEmpty(
            'listPID',
            true,
            'sDEF',
            'This value specifies the page that contains the list of events. '
            . 'If this value is not set correctly, the links in the list '
            . 'view and the back link on the list of registrations will '
            . 'not work.'
        );
    }

    /**
     * Checks the setting of the configuration value detailPID.
     *
     * @return void
     */
    private function checkDetailPid()
    {
        $this->checkIfSingleFePageNotEmpty(
            'detailPID',
            true,
            'sDEF',
            'This value specifies the page that contains the detailed view. '
            . 'If this value is not set correctly, the links to single '
            . 'events will not work as expected.'
        );
    }

    /**
     * Checks the setting of the configuration value myEventsPID.
     *
     * @return void
     */
    private function checkMyEventsPid()
    {
        $this->checkIfSingleFePageNotEmpty(
            'myEventsPID',
            true,
            'sDEF',
            'This value specifies the page that contains the <em>my events</em> '
            . 'list. If this value is not set correctly, the redirection to '
            . 'the my events list after canceling the unregistration process '
            . 'will not work correctly.'
        );
    }

    /**
     * Checks the setting of the configuration value registerPID.
     *
     * @return void
     */
    private function checkRegisterPid()
    {
        $this->checkIfSingleFePageNotEmpty(
            'registerPID',
            true,
            'sDEF',
            'This value specifies the page that contains the registration '
            . 'form. If this value is not set correctly, the link to the '
            . 'registration page will not work. If you explicitely do not '
            . 'wish to use the online registration feature, you can '
            . 'disable these checks by setting '
            . '<strong>plugin.tx_seminars.enableRegistration</strong> and '
            . '<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
            . 'to 0.'
        );
    }

    /**
     * Checks the setting of the configuration value loginPID.
     *
     * @return void
     */
    private function checkLoginPid()
    {
        $this->checkIfSingleFePageNotEmpty(
            'loginPID',
            true,
            'sDEF',
            'This value specifies the page that contains the login form. '
            . 'If this value is not set correctly, the link to the '
            . 'login page will not work. If you explicitely do not '
            . 'wish to use the online registration feature, you can '
            . 'disable these checks by setting '
            . '<strong>plugin.tx_seminars.enableRegistration</strong> and '
            . '<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
            . 'to 0.'
        );
    }

    /**
     * Checks the setting of the configuration value registrationsListPID.
     *
     * @return void
     */
    private function checkRegistrationsListPidOptional()
    {
        $this->checkIfSingleFePageOrEmpty(
            'registrationsListPID',
            true,
            'sDEF',
            'This value specifies the page that contains the list of '
            . 'registrations for an event. If this value is not set '
            . 'correctly, the link to that page will not work.'
        );
    }

    /**
     * Checks the setting of the configuration value registrationsVipListPID.
     *
     * @return void
     */
    private function checkRegistrationsVipListPid()
    {
        $this->checkIfSingleFePageNotEmpty(
            'registrationsVipListPID',
            true,
            'sDEF',
            'This value specifies the page that contains the list of '
            . 'registrations for an event. If this value is not set '
            . 'correctly, the link to that page will not work.'
        );
    }

    /**
     * Checks the setting of the configuration value registrationsVipListPID,
     * but also allows empty values.
     *
     * @return void
     */
    private function checkRegistrationsVipListPidOptional()
    {
        $this->checkIfSingleFePageOrEmpty(
            'registrationsVipListPID',
            true,
            'sDEF',
            'This value specifies the page that contains the list of '
            . 'registrations for an event. If this value is not set '
            . 'correctly, the link to that page will not work.'
        );
    }

    /**
     * Checks the setting of the configuration value pages.
     *
     * @return void
     */
    private function checkPages()
    {
        $this->checkIfSysFoldersNotEmpty(
            'pages',
            true,
            'sDEF',
            'This value specifies the system folders that contain the '
            . 'event records for the list view. If this value is not set '
            . 'correctly, some events might not get displayed in the list '
            . 'view.'
        );
    }

    /**
     * Checks the setting of the configuration value recursive,
     * but also allows empty values.
     *
     * @return void
     */
    private function checkRecursive()
    {
        $this->checkIfInteger(
            'recursive',
            true,
            'sDEF',
            'This value specifies the how deep the recursion will be for '
            . 'selecting the pages that contain the event records for the '
            . 'list view. If this value is not set correctly, some events '
            . 'might not get displayed in the list view.'
        );
    }

    /**
     * Checks the settings that are common to all FE plug-in variations of this
     * extension: CSS styled content, static TypoScript template included,
     * template file, css file, salutation mode, CSS class names, and what to
     * display.
     *
     * @return void
     */
    private function checkCommonFrontEndSettings()
    {
        $this->checkStaticIncluded();
        $this->checkTemplateFile(true);
        $this->checkCssFileFromConstants();
        $this->checkSalutationMode(true);
        $this->checkWhatToDisplay();
    }

    /**
     * Checks the setting of the configuration value eventEditorFeGroupID.
     *
     * @return void
     */
    private function checkEventEditorFeGroupID()
    {
        $this->checkIfPositiveInteger(
            'eventEditorFeGroupID',
            true,
            's_fe_editing',
            'This value specifies the front-end user group that is allowed to '
            . 'enter and edit event records in the front end. If this value '
            . 'is not set correctly, FE editing for events will not work.'
        );
    }

    /**
     * Checks the setting of the configuration value defaultEventVipsFeGroupID.
     *
     * @return void
     */
    private function checkDefaultEventVipsFeGroupID()
    {
        $this->checkIfPositiveIntegerOrEmpty(
            'defaultEventVipsFeGroupID',
            true,
            '',
            'This value specifies the front-end user group that is allowed to '
            . 'see the registrations for all events and get all events listed '
            . 'on their "my VIP events" page. If this value is not set '
            . 'correctly, the users of this group will not be treated as '
            . 'VIPs for all events.'
        );
    }

    /**
     * Checks the setting of the configuration value createEventsPID.
     *
     * @return void
     */
    private function checkCreateEventsPID()
    {
        $this->checkIfSingleSysFolderNotEmpty(
            'createEventsPID',
            true,
            's_fe_editing',
            'This value specifies the page on which FE-entered events will be '
            . 'stored. If this value is not set correctly, those event '
            . 'records will be dumped in the TYPO3 root page.'
        );
    }

    /**
     * Checks the setting of the configuration value eventSuccessfullySavedPID.
     *
     * @return void
     */
    private function checkEventSuccessfullySavedPID()
    {
        $this->checkIfSingleFePageNotEmpty(
            'eventSuccessfullySavedPID',
            true,
            's_fe_editing',
            'This value specifies the page to which the user will be '
            . 'redirected after saving an event record in the front end. If '
            . 'this value is not set correctly, the redirect will not work.'
        );
    }

    /**
     * Checks the setting of the configuration value allowedExtensionsForUpload.
     *
     * @return void
     */
    private function checkAllowedExtensionsForUpload()
    {
        $this->checkForNonEmptyString(
            'allowedExtensionsForUpload',
            true,
            's_fe_editing',
            'This value specifies the list of allowed extensions of files to ' .
            'upload in the FE editor. If this value is empty, files ' .
            'cannot be uploaded.'
        );
    }

    /**
     * Checks the setting of the configuration value eventEditorPID.
     *
     * @return void
     */
    private function checkEventEditorPID()
    {
        $this->checkIfSingleFePageNotEmpty(
            'eventEditorPID',
            true,
            's_fe_editing',
            'This value specifies the page that contains the plug-in for '
            . 'editing event records in the front end. If this value is not '
            . 'set correctly, the <em>edit</em> link in the <em>events '
            . 'which I have entered</em> list will not work.'
        );
    }

    /**
     * Checks the setting of the configuration value thankYouAfterRegistrationPID.
     *
     * @return void
     */
    private function checkThankYouAfterRegistrationPID()
    {
        $this->checkIfSingleFePageNotEmpty(
            'thankYouAfterRegistrationPID',
            true,
            's_registration',
            'This value specifies the page that will be displayed after a user '
            . 'signed up for an event. If this value is not set correctly, '
            . 'the user will see the list of events instead.'
        );
    }

    /**
     * Checks the setting of the configuration value pageToShowAfterUnregistrationPID.
     *
     * @return void
     */
    private function checkPageToShowAfterUnregistrationPID()
    {
        $this->checkIfSingleFePageNotEmpty(
            'pageToShowAfterUnregistrationPID',
            true,
            's_registration',
            'This value specifies the page that will be displayed after a user '
            . 'has unregistered from an event. If this value is not set correctly, '
            . 'the user will see the list of events instead.'
        );
    }

    /**
     * Checks the setting of the configuration value bankTransferUID.
     *
     * @return void
     */
    private function checkBankTransferUid()
    {
        $this->checkIfPositiveIntegerOrEmpty(
            'bankTransferUID',
            false,
            '',
            'This value specifies the payment method that corresponds to '
            . 'a bank transfer. If this value is not set correctly, '
            . 'validation of the bank data in the event registration '
            . 'form will not work correctly.'
        );
    }

    /**
     * Checks the CSV-related settings.
     *
     * @return void
     */
    protected function check_tx_seminars_Bag_Event_csv()
    {
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
     * Checks the setting of the configuration value charsetForCsv.
     *
     * @return void
     */
    private function checkCharsetForCsv()
    {
        $this->checkForNonEmptyString(
            'charsetForCsv',
            false,
            '',
            'This value specifies the charset to use for the CSV export. '
            . 'If this value is not set, no charset information will be '
            . 'provided for CSV downloads.'
        );
    }

    /**
     * Checks the setting of the configuration value filenameForEventsCsv.
     *
     * @return void
     */
    private function checkFilenameForEventsCsv()
    {
        $this->checkForNonEmptyString(
            'filenameForEventsCsv',
            false,
            '',
            'This value specifies the file name to suggest for the CSV export '
            . 'of event records. '
            . 'If this value is not set, an empty filename will be used for '
            . 'saving the CSV file which will cause problems.'
        );
    }

    /**
     * Checks the setting of the configuration value filenameForRegistrationsCsv.
     *
     * @return void
     */
    private function checkFilenameForRegistrationsCsv()
    {
        $this->checkForNonEmptyString(
            'filenameForRegistrationsCsv',
            false,
            '',
            'This value specifies the file name to suggest for the CSV export '
            . 'of registration records. '
            . 'If this value is not set, an empty filename will be used for '
            . 'saving the CSV file which will cause problems.'
        );
    }

    /**
     * Checks the setting of the configuration value fieldsFromEventsForCsv.
     *
     * @return void
     */
    private function checkFieldsFromEventsForCsv()
    {
        $this->checkIfMultiInSetNotEmpty(
            'fieldsFromEventsForCsv',
            false,
            '',
            'These values specify the event fields to export via CSV. '
            . 'A mistyped field name will cause the field to not get '
            . 'included.',
            [
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
                'cancelled',
            ]
        );
    }

    /**
     * Checks the setting of the configuration value fieldsFromFeUserForCsv.
     *
     * @return void
     */
    private function checkFieldsFromFeUserForCsv()
    {
        $this->checkIfMultiInTableOrEmpty(
            'fieldsFromFeUserForCsv',
            false,
            '',
            'These values specify the FE user fields to export via CSV in web ' .
            'mode. A mistyped field name will cause the field to not get ' .
            'included.',
            'fe_users'
        );
    }

    /**
     * Checks the setting of the configuration value fieldsFromAttendanceForCsv.
     *
     * @return void
     */
    private function checkFieldsFromAttendanceForCsv()
    {
        $this->checkIfMultiInTableOrEmpty(
            'fieldsFromAttendanceForCsv',
            false,
            '',
            'These values specify the registration fields to export via CSV in ' .
            'web mode. A mistyped field name will cause the field to not get ' .
            'included.',
            'tx_seminars_attendances'
        );
    }

    /**
     * Checks the setting of the configuration value fieldsFromFeUserForEmailCsv.
     *
     * @return void
     */
    private function checkFieldsFromFeUserForEmailCsv()
    {
        $this->checkIfMultiInTableOrEmpty(
            'fieldsFromFeUserForCliCsv',
            false,
            '',
            'These values specify the FE user fields to export via CSV in e-mail ' .
            'mode. A mistyped field name will cause the field to not get ' . '
                included.',
            'fe_users'
        );
    }

    /**
     * Checks the setting of the configuration value fieldsFromAttendanceForEmailCsv.
     *
     * @return void
     */
    private function checkFieldsFromAttendanceForEmailCsv()
    {
        $this->checkIfMultiInTableOrEmpty(
            'fieldsFromAttendanceForEmailCsv',
            false,
            '',
            'These values specify the registration fields to export via CSV in ' .
            'e-mail mode. A mistyped field name will cause the field to not ' .
            'get included.',
            'tx_seminars_attendances'
        );
    }

    /**
     * Checks whether the HTML template for the registration form is provided
     * and the file exists.
     *
     * @return void
     */
    private function checkRegistrationEditorTemplateFile()
    {
        $errorMessage = 'This specifies the HTML template for the registration ' .
            'form. If this file is not available, the registration form cannot ' .
            'be used.';

        $this->checkForNonEmptyString('registrationEditorTemplateFile', false, '', $errorMessage);

        if ($this->objectToCheck->hasConfValueString('registrationEditorTemplateFile', '', true)) {
            $rawFileName = $this->objectToCheck->getConfValueString('registrationEditorTemplateFile', '', true, true);

            $file = GeneralUtility::getFileAbsFileName($rawFileName);
            if ($file === '' || !\is_file($file)) {
                $message = 'The specified HTML template file <strong>' .
                    \htmlspecialchars($rawFileName, ENT_QUOTES | ENT_HTML5) . '</strong> cannot be read. ' .
                    $errorMessage . ' ' .
                    'Please either create the file <strong>' . $rawFileName .
                    '</strong> or select an existing file using the TS setup ' .
                    'variable <strong>' . $this->getTSSetupPath() .
                    'templateFile</strong> or via FlexForms.';
                $this->setErrorMessage($message);
            }
        }
    }

    /**
     * Checks the setting of the configuration value
     * logOutOneTimeAccountsAfterRegistration.
     *
     * @return void
     */
    private function checkLogOutOneTimeAccountsAfterRegistration()
    {
        $this->checkIfBoolean(
            'logOutOneTimeAccountsAfterRegistration',
            false,
            '',
            'This value specifies whether one-time FE user accounts will '
            . 'automatically be logged out after registering for an event. '
            . 'If this value is incorrect, the automatic logout will not work.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * numberOfFirstRegistrationPage.
     *
     * @return void
     */
    private function checkNumberOfFirstRegistrationPage()
    {
        $this->checkIfPositiveInteger(
            'numberOfFirstRegistrationPage',
            false,
            '',
            'This value specifies the number of the first registration page '
            . '(for the <em>Step x of y</em> heading). '
            . 'If this value is not set correctly, the number of the current '
            . 'page will not be displayed correctly.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * numberOfLastRegistrationPage.
     *
     * @return void
     */
    private function checkNumberOfLastRegistrationPage()
    {
        $this->checkIfPositiveInteger(
            'numberOfLastRegistrationPage',
            false,
            '',
            'This value specifies the number of the last registration page '
            . '(for the <em>Step x of y</em> heading). '
            . 'If this value is not set correctly, the number of the last '
            . 'page will not be displayed correctly.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * numberOfClicksForRegistration.
     *
     * @return void
     */
    private function checkNumberOfClicksForRegistration()
    {
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
     * @return void
     */
    private function checkSendParametersToThankYouAfterRegistrationPageUrl()
    {
        $this->checkIfBoolean(
            'sendParametersToThankYouAfterRegistrationPageUrl',
            true,
            's_registration',
            'This value specifies whether the sending of parameters to the '
            . 'thank you page after a registration should be enabled or not. '
            . 'If this value is incorrect the sending of parameters will '
            . 'not be enabled or disabled correctly.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * sendParametersToPageToShowAfterUnregistrationUrl.
     *
     * @return void
     */
    private function checkSendParametersToPageToShowAfterUnregistrationUrl()
    {
        $this->checkIfBoolean(
            'sendParametersToPageToShowAfterUnregistrationUrl',
            true,
            's_registration',
            'This value specifies whether the sending of parameters to the page '
            . 'which is shown after an unregistration should be enabled or '
            . 'not. If this value is incorrect the sending of parameters '
            . 'will not be enabled or disabled correctly.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * createAdditionalAttendeesAsFrontEndUsers.
     *
     * @return void
     */
    private function checkCreateAdditionalAttendeesAsFrontEndUsers()
    {
        $this->checkIfBoolean(
            'createAdditionalAttendeesAsFrontEndUsers',
            true,
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
     *
     * @return void
     */
    private function checkSysFolderForAdditionalAttendeeUsersPID()
    {
        $this->checkIfSingleSysFolderNotEmpty(
            'sysFolderForAdditionalAttendeeUsersPID',
            true,
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
     *
     * @return void
     */
    private function checkUserGroupUidsForAdditionalAttendeesFrontEndUsers()
    {
        $this->checkIfPidListNotEmpty(
            'userGroupUidsForAdditionalAttendeesFrontEndUsers',
            true,
            's_registration',
            'This value specifies the FE user groups for the FE users ' .
            'created for additional attendees. If this value is not set ' .
            'correctly, those FE users might not be able to log in.'
        );
    }

    /**
     * Checks the setting of the configuration value externalLinkTarget.
     * But currently does nothing as we don't think there's something to check for.
     *
     * @return void
     */
    private function checkExternalLinkTarget()
    {
        // Does nothing.
    }

    /**
     * Checks the setting of the configuration value
     * showSingleEvent.
     *
     * @return void
     */
    private function checkShowSingleEvent()
    {
        $this->checkIfPositiveIntegerOrEmpty(
            'showSingleEvent',
            true,
            's_template_special',
            'This value specifies which fixed single event should be shown. If '
            . 'this value is not set correctly, an error message will be '
            . 'shown instead.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * limitListViewToEventTypes.
     *
     * @return void
     */
    private function checkLimitListViewToEventTypes()
    {
        $this->checkIfPidListOrEmpty(
            'limitListViewToEventTypes',
            true,
            's_listView',
            'This value specifies the event types by which the list view ' .
            'should be filtered. If this value is not set correctly, ' .
            'some events might unintentionally get hidden or shown.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * limitListViewToCategories.
     *
     * @return void
     */
    private function checkLimitListViewToCategories()
    {
        $this->checkIfPidListOrEmpty(
            'limitListViewToCategories',
            true,
            's_listView',
            'This value specifies the categories by which the list view ' .
            'should be filtered. If this value is not set correctly, ' .
            'some events might unintentionally get hidden or shown.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * limitListViewToPlaces.
     *
     * @return void
     */
    private function checkLimitListViewToPlaces()
    {
        $this->checkIfPidListOrEmpty(
            'limitListViewToPlaces',
            true,
            's_listView',
            'This value specifies the places for which the list view ' .
            'should be filtered. If this value is not set correctly, ' .
            'some events might unintentionally get hidden or shown.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * limitListViewToOrganizers.
     *
     * @return void
     */
    private function checkLimitListViewToOrganizers()
    {
        $this->checkIfPidListOrEmpty(
            'limitListViewToOrganizers',
            true,
            's_listView',
            'This value specifies the organizers for which the list view ' .
            'should be filtered. If this value is not set correctly, ' .
            'some events might unintentionally get hidden or shown.'
        );
    }

    /**
     * Checks the setting of the configuration value categoriesInListView.
     *
     * @return void
     */
    private function checkCategoryIconDisplay()
    {
        $this->checkIfSingleInSetNotEmpty(
            'categoriesInListView',
            true,
            's_listView',
            'This setting determines whether the seminar category is shown, as ' .
            'icon and text, as text only or as icon only. If this value is ' .
            'not set correctly, the category will only be shown as text.',
            ['both', 'text', 'icon']
        );
    }

    /**
     * Checks the settings for the image width, and height in the list view.
     *
     * @return void
     */
    private function checkSeminarImageSizes()
    {
        $this->checkListViewImageWidth();
        $this->checkListViewImageHeight();
    }

    /**
     * Checks the settings for seminarImageListViewWidth.
     *
     * @return void
     */
    private function checkListViewImageWidth()
    {
        $this->checkIfPositiveInteger(
            'seminarImageListViewWidth',
            false,
            '',
            'This value specifies the width of the image of a seminar. If this ' .
            'value is not set, the image will be shown in full size.'
        );
    }

    /**
     * Checks the settings for seminarImageListViewHeight.
     *
     * @return void
     */
    private function checkListViewImageHeight()
    {
        $this->checkIfPositiveInteger(
            'seminarImageListViewHeight',
            false,
            '',
            'This value specifies the height of the image of a seminar. If ' .
            'this value is not set, the image will be shown in full size.'
        );
    }

    /**
     * Checks the settings for the image width, and height in the single view.
     *
     * @return void
     */
    private function checkSingleViewImageSizes()
    {
        $this->checkSingleViewImageWidth();
        $this->checkSingleViewImageHeight();
    }

    /**
     * Checks the settings for seminarImageSingleViewWidth.
     *
     * @return void
     */
    private function checkSingleViewImageWidth()
    {
        $this->checkIfPositiveInteger(
            'seminarImageSingleViewWidth',
            false,
            '',
            'This value specifies the width of the image of a seminar. If this ' .
            'value is not set, the image will be shown in full size.'
        );
    }

    /**
     * Checks the settings for seminarImageSingleViewHeight.
     *
     * @return void
     */
    private function checkSingleViewImageHeight()
    {
        $this->checkIfPositiveInteger(
            'seminarImageSingleViewHeight',
            false,
            '',
            'This value specifies the height of the image of a seminar. If ' .
            'this value is not set, the image will be shown in full size.'
        );
    }

    /**
     * Checks the setting of the configuration value showOwnerDataInSingleView.
     *
     * @return void
     */
    private function checkShowOwnerDataInSingleView()
    {
        $this->checkIfBoolean(
            'showOwnerDataInSingleView',
            true,
            's_singleView',
            'This value specifies whether the owner data will be displayed  ' .
            'on the single view page. If this value is incorrect, ' .
            'the the data might be displayed although it should not be ' .
            '(which is a privacy issue) or vice versa.'
        );
    }

    /**
     * Checks the setting of the configuration value ownerPictureMaxWidth.
     *
     * @return void
     */
    private function checkOwnerPictureMaxWidth()
    {
        $this->checkIfPositiveInteger(
            'ownerPictureMaxWidth',
            false,
            '',
            'This value specifies the maximum width for the owner picture ' .
            'on the single view page. If this value is not set ' .
            'correctly, the image might be too large or not get ' .
            'displayed at all.'
        );
    }

    /**
     * Checks the setting for displaySearchFormFields.
     *
     * @return void
     */
    private function checkDisplaySearchFormFields()
    {
        $this->checkIfMultiInSetOrEmpty(
            'displaySearchFormFields',
            true,
            's_listView',
            'This value specifies which search widget fields to display in the ' .
            'list view. The search widget will not display any fields at ' .
            'all if this value is empty or contains only invalid keys.',
            [
                'event_type',
                'language',
                'country',
                'city',
                'place',
                'full_text_search',
                'date',
                'age',
                'organizer',
                'categories',
                'price',
            ]
        );
    }

    /**
     * Checks the settings for numberOfYearsInDateFilter.
     *
     * @return void
     */
    private function checkNumberOfYearsInDateFilter()
    {
        $this->checkIfPositiveInteger(
            'numberOfYearsInDateFilter',
            true,
            's_listView',
            'This value specifies the number years of years the user can ' .
            'search for events in the event list. The date search will ' .
            'have an empty drop-down for the year if this variable is ' .
            'misconfigured.'
        );
    }

    /**
     * Checks the setting of the configuration value displayFrontEndEditorFields.
     *
     * @return void
     */
    private function checkDisplayFrontEndEditorFields()
    {
        $this->checkIfMultiInSetOrEmpty(
            'displayFrontEndEditorFields',
            true,
            's_fe_editing',
            'This value specifies which fields should be displayed in the ' .
            'fe-editor. Incorrect values will cause the fields not to be ' .
            'displayed.',
            [
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
            ]
        );
    }

    /**
     * Checks the setting for allowFrontEndEditingOfSpeakers.
     *
     * @return void
     */
    private function checkAllowFrontEndEditingOfSpeakers()
    {
        $this->checkIfBoolean(
            'allowFrontEndEditingOfSpeakers',
            true,
            's_fe_editing',
            'This value specifies whether front-end editing of speakers is ' .
            'possible. If this value is incorrect, front-end editing of ' .
            'speakers might be possible even when this is not desired ' .
            '(or vice versa).'
        );
    }

    /**
     * Checks the setting for allowFrontEndEditingOfPlaces.
     *
     * @return void
     */
    private function checkAllowFrontEndEditingOfPlaces()
    {
        $this->checkIfBoolean(
            'allowFrontEndEditingOfPlaces',
            true,
            's_fe_editing',
            'This value specifies whether front-end editing of places is ' .
            'possible. If this value is incorrect, front-end editing of ' .
            'places might be possible even when this is not desired ' .
            '(or vice versa).'
        );
    }

    /**
     * Checks the setting for allowFrontEndEditingOfCheckboxes.
     *
     * @return void
     */
    private function checkAllowFrontEndEditingOfCheckboxes()
    {
        $this->checkIfBoolean(
            'allowFrontEndEditingOfCheckboxes',
            true,
            's_fe_editing',
            'This value specifies whether front-end editing of checkboxes is ' .
            'possible. If this value is incorrect, front-end editing of ' .
            'checkboxes might be possible even when this is not desired ' .
            '(or vice versa).'
        );
    }

    /**
     * Checks the setting for allowFrontEndEditingOfTargetGroups.
     *
     * @return void
     */
    private function checkAllowFrontEndEditingOfTargetGroups()
    {
        $this->checkIfBoolean(
            'allowFrontEndEditingOfTargetGroups',
            true,
            's_fe_editing',
            'This value specifies whether front-end editing of target groups ' .
            'is possible. If this value is incorrect, front-end editing of ' .
            'target groups might be possible even when this is not desired ' .
            '(or vice versa).'
        );
    }

    /**
     * Checks the configuration for requiredFrontEndEditorFields.
     *
     * @return void
     */
    private function checkRequiredFrontEndEditorFields()
    {
        $this->checkIfMultiInSetOrEmpty(
            'requiredFrontEndEditorFields',
            true,
            's_fe_editing',
            'This value specifies which fields are required to be filled when ' .
            'editing an event. Some fields will be not be required if ' .
            'this configuration is incorrect.',
            [
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
            ]
        );

        // checks whether the required fields are visible
        $this->checkIfMultiInSetOrEmpty(
            'requiredFrontEndEditorFields',
            true,
            's_fe_editing',
            'This value specifies which fields are required to be filled when ' .
            'editing an event. Some fields are set to required but are ' .
            'actually not configured to be visible in the form. The form ' .
            'cannot be submitted as long as this inconsistency remains.',
            GeneralUtility::trimExplode(
                ',',
                $this->objectToCheck->getConfValueString(
                    'displayFrontEndEditorFields',
                    's_fe_editing'
                ),
                true
            )
        );
    }

    /**
     * Checks the configuration for requiredFrontEndEditorPlaceFields.
     *
     * @return void
     */
    private function checkRequiredFrontEndEditorPlaceFields()
    {
        $this->checkIfMultiInSetOrEmpty(
            'requiredFrontEndEditorPlaceFields',
            false,
            '',
            'This value specifies which fields are required to be filled when ' .
            'editing a pkace. Some fields will be not be required if ' .
            'this configuration is incorrect.',
            [
                'address',
                'zip',
                'city',
                'country',
                'homepage',
                'directions',
            ]
        );
    }

    /**
     * Checks whether the HTML template for the event editor is provided and the
     * file exists.
     *
     * @return void
     */
    private function checkEventEditorTemplateFile()
    {
        $errorMessage = 'This specifies the HTML template for the event editor. ' .
            'If this file is not available, the event editor cannot be used.';

        $this->checkForNonEmptyString('eventEditorTemplateFile', false, '', $errorMessage);

        if ($this->objectToCheck->hasConfValueString('eventEditorTemplateFile', '', true)) {
            $rawFileName = $this->objectToCheck->getConfValueString('eventEditorTemplateFile', '', true, true);
            $file = GeneralUtility::getFileAbsFileName($rawFileName);
            if ($file === '' || !\is_file($file)) {
                $message = 'The specified HTML template file <strong>' .
                    \htmlspecialchars($rawFileName, ENT_QUOTES | ENT_HTML5) . '</strong> cannot be read. ' .
                    $errorMessage . ' ' .
                    'Please either create the file <strong>' . $rawFileName .
                    '</strong> or select an existing file using the TS setup ' .
                    'variable <strong>' . $this->getTSSetupPath() .
                    'templateFile</strong> or via FlexForms.';
                $this->setErrorMessage($message);
            }
        }
    }

    /**
     * Checks the setting of the configuration value showOnlyEventsWithVacancies.
     *
     * @return void
     */
    private function checkShowOnlyEventsWithVacancies()
    {
        $this->checkIfBoolean(
            'showOnlyEventsWithVacancies',
            true,
            's_listView',
            'This value specifies whether only events with vacancies should be ' .
            'shown in the list view. If this value is not configured ' .
            'properly, events with no vacancies will be shown in the ' .
            'list view.'
        );
    }

    /**
     * Checks the relation between last and first page and the number of clicks.
     *
     * @return void
     */
    private function checkRegistrationPageNumbers()
    {
        $clicks = $this->objectToCheck->getConfValueInteger(
            'numberOfClicksForRegistration',
            's_registration'
        );
        $firstPage = $this->objectToCheck->getConfValueInteger('numberOfFirstRegistrationPage');
        $lastPage = $this->objectToCheck->getConfValueInteger('numberOfLastRegistrationPage');
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
     *
     * @return void
     */
    public function checkCurrency()
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $pool->getConnectionForTable('static_currencies');
        $result = $connection->select(['cu_iso_3'], 'static_currencies')->fetchAll();
        $allowedValues = array_column($result, 'cu_iso_3');

        $this->checkIfSingleInSetNotEmpty(
            'currency',
            false,
            '',
            'The specified currency setting is either empty or not a valid ' .
            'ISO 4217 alpha 3 code. Please correct the value of <strong>' .
            $this->getTSSetupPath() . 'currency</strong>.',
            $allowedValues
        );
    }

    /**
     * Checks the setting of showAttendancesOnRegistrationQueueInEmailCsv
     *
     * @return void
     */
    public function checkShowAttendancesOnRegistrationQueueInEmailCsv()
    {
        $this->checkIfBoolean(
            'showAttendancesOnRegistrationQueueInEmailCsv',
            false,
            '',
            'This value specifies if attendances on the registration queue ' .
            'should also be exported in the CSV file in the e-mail mode.' .
            'If this is not set correctly, the attendances on the ' .
            'registration queue might not get exported.'
        );
    }

    /**
     * Checks the setting of the configuration value
     * enableSortingLinksInListView.
     *
     * @return void
     */
    private function checkEnableSortingLinksInListView()
    {
        $this->checkIfBoolean(
            'enableSortingLinksInListView',
            false,
            '',
            'This value specifies whether the list view header should be sorting links. ' .
            'If this value is incorrect, the sorting might be enabled ' .
            'even when this is not desired (or vice versa).'
        );
    }

    /**
     * Checks the setting of the configuration value linkToSingleView.
     *
     * @return void
     */
    private function checkLinkToSingleView()
    {
        $this->checkIfSingleInSetNotEmpty(
            'linkToSingleView',
            true,
            's_listView',
            'This value specifies when the list view will link to the single view. '
            . 'If this value is not set correctly, the single view might not be linked although this is intended '
            . '(or vice versa).',
            [
                'always',
                'never',
                'onlyForNonEmptyDescription',
            ]
        );
    }
}
