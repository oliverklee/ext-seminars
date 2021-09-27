<?php

declare(strict_types=1);

use OliverKlee\Oelib\Configuration\ConfigurationCheck;

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
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/topic_list.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_topic_list()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/my_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_events()
    {
    }

    /**
     * Checks the configuration for: check_Tx_Seminars_FrontEnd_DefaultController/edit_event.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_edit_event()
    {
    }

    /**
     * Checks the configuration for: check_Tx_Seminars_FrontEnd_DefaultController/my_entered_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_entered_events()
    {
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
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/favorites_list
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_favorites_list()
    {
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
     * Checks the setting of showAttendancesOnRegistrationQueueInEmailCsv
     *
     * @return void
     */
    private function checkShowAttendancesOnRegistrationQueueInEmailCsv()
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
