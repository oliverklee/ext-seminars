<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;

/**
 * Configuration check for the CSV export.
 */
class CsvExportConfigurationCheck extends AbstractConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkFilenameForEventsCsv();
        $this->checkFilenameForRegistrationsCsv();
        $this->checkFieldsFromEventsForCsv();
        $this->checkFieldsFromFeUserForCsv();
        $this->checkFieldsFromAttendanceForCsv();
        $this->checkFieldsFromFeUserForEmailCsv();
        $this->checkFieldsFromAttendanceForEmailCsv();
        $this->checkShowAttendancesOnRegistrationQueueInEmailCsv();
    }

    private function checkFilenameForEventsCsv(): void
    {
        $this->checkForNonEmptyString(
            'filenameForEventsCsv',
            'This value specifies the file name to suggest for the CSV export of event records.
            If this value is not set, an empty filename will be used for saving the CSV file which will cause problems.'
        );
    }

    private function checkFilenameForRegistrationsCsv(): void
    {
        $this->checkForNonEmptyString(
            'filenameForRegistrationsCsv',
            'This value specifies the file name to suggest for the CSV export of registration records.
            If this value is not set, an empty filename will be used for saving the CSV file which will cause problems.'
        );
    }

    private function checkFieldsFromEventsForCsv(): void
    {
        $this->checkIfMultiInSetNotEmpty(
            'fieldsFromEventsForCsv',
            'These values specify the event fields to export via CSV.
            A mistyped field name will cause the field to not get included.',
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
                // @deprecated #1773 will be removed in seminars 5.0
                'price_regular_board',
                'price_special',
                'price_special_early',
                // @deprecated #1773 will be removed in seminars 5.0
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

    private function checkFieldsFromFeUserForCsv(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'fieldsFromFeUserForCsv',
            'These values specify the FE user fields to export via CSV in web mode.
            A mistyped field name will cause the field to not get included.',
            'fe_users'
        );
    }

    private function checkFieldsFromAttendanceForCsv(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'fieldsFromAttendanceForCsv',
            'These values specify the registration fields to export via CSV in web mode.
            A mistyped field name will cause the field to not get
            included.',
            'tx_seminars_attendances'
        );
    }

    private function checkFieldsFromFeUserForEmailCsv(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'fieldsFromFeUserForCliCsv',
            'These values specify the FE user fields to export via CSV in e-mail mode.
            A mistyped field name will cause the field to not get included.',
            'fe_users'
        );
    }

    private function checkFieldsFromAttendanceForEmailCsv(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'fieldsFromAttendanceForEmailCsv',
            'These values specify the registration fields to export via CSV in e-mail mode.
            A mistyped field name will cause the field to not get included.',
            'tx_seminars_attendances'
        );
    }

    private function checkShowAttendancesOnRegistrationQueueInEmailCsv(): void
    {
        $this->checkIfBoolean(
            'showAttendancesOnRegistrationQueueInEmailCsv',
            'This value specifies if attendances on the registration queue should also be exported in the CSV file
            in the e-mail mode.
            If this is not set correctly, the attendances on the registration queue might not get exported.'
        );
    }
}
