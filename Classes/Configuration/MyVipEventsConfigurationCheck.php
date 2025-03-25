<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the "my events" list.
 *
 * @internal
 */
class MyVipEventsConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkRegistrationsVipListPid();
    }

    private function checkRegistrationsVipListPid(): void
    {
        $this->checkIfPositiveInteger(
            'registrationsVipListPID',
            'This value specifies the page that contains the list of registrations for an event.
            If this value is not set correctly, the link to that page will not work.'
        );
    }
}
