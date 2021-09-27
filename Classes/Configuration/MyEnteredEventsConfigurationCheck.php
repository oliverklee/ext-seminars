<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the "my evented events" list.
 */
class MyEnteredEventsConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkEventEditorFeGroupID();
        $this->checkEventEditorPID();
    }
}
