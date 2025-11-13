<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the front-end registration list.
 *
 * @internal
 */
class RegistrationListConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCommonFrontEndSettings();

        $this->checkShowFeUserFieldsInRegistrationsList();
        $this->checkListPid();
    }

    private function checkShowFeUserFieldsInRegistrationsList(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'showFeUserFieldsInRegistrationsList',
            'These values specify the FE user fields to show in the list of  registrations for an event.
            A mistyped field name will cause the contents of the field to not get displayed.',
            'fe_users',
        );
    }
}
