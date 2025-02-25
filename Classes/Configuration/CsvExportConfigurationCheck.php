<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;

/**
 * Configuration check for the CSV export.
 *
 * @internal
 */
class CsvExportConfigurationCheck extends AbstractConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkFieldsFromFeUserForCsv();
        $this->checkFieldsFromAttendanceForCsv();
        $this->checkFieldsFromFeUserForEmailCsv();
        $this->checkFieldsFromAttendanceForEmailCsv();
        $this->checkShowAttendancesOnRegistrationQueueInEmailCsv();
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
            'These values specify the FE user fields to export via CSV in email mode.
            A mistyped field name will cause the field to not get included.',
            'fe_users'
        );
    }

    private function checkFieldsFromAttendanceForEmailCsv(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'fieldsFromAttendanceForEmailCsv',
            'These values specify the registration fields to export via CSV in email mode.
            A mistyped field name will cause the field to not get included.',
            'tx_seminars_attendances'
        );
    }

    private function checkShowAttendancesOnRegistrationQueueInEmailCsv(): void
    {
        $this->checkIfBoolean(
            'showAttendancesOnRegistrationQueueInEmailCsv',
            'This value specifies if attendances on the registration queue should also be exported in the CSV file
            in the email mode.
            If this is not set correctly, the attendances on the registration queue might not get exported.'
        );
    }
}
