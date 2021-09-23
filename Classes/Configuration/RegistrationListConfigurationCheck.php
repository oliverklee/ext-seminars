<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the front-end registration list.
 */
class RegistrationListConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCommonFrontEndSettings();

        $this->checkShowFeUserFieldsInRegistrationsList();
        $this->checkShowRegistrationFieldsInRegistrationsList();
        $this->checkListPid();
    }

    private function checkShowFeUserFieldsInRegistrationsList(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'showFeUserFieldsInRegistrationsList',
            'These values specify the FE user fields to show in the list of  registrations for an event.
            A mistyped field name will cause the contents of the field to not get displayed.',
            'fe_users'
        );
    }

    private function checkShowRegistrationFieldsInRegistrationsList(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'showRegistrationFieldsInRegistrationList',
            'These values specify the registration fields to show in the list of registrations for an event.
            A mistyped field name will cause the contents of the field to not get displayed.',
            'tx_seminars_attendances'
        );
    }
}
