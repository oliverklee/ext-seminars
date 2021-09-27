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
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/single_view.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_single_view()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/seminar_list.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_seminar_list()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_Countdown.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_Countdown()
    {
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
            'CSV export of registrations from the &quot;my VIP events&quot; view. ' .
            'If this value is incorrect, managers may be allowed to access ' .
            'the CSV export of registrations from the &quot;my VIP events ' .
            'view&quot; although they should not be allowed to (or vice versa).'
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
            . 'on their &quot;my VIP events&quot; page. If this value is not set '
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
}
