<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;

/**
 * Base class for configuration checks for front-end views.
 */
abstract class AbstractFrontEndConfigurationCheck extends AbstractConfigurationCheck
{
    /**
     * Checks the settings that are common to all FE plug-in variations of this extension:
     * CSS styled content, static TypoScript template included, template file, salutation mode,
     * CSS class names, and what to display.
     */
    protected function checkCommonFrontEndSettings(): void
    {
        $this->checkStaticIncluded();
        $this->checkTemplateFile();
        $this->checkSalutationMode();
        $this->checkWhatToDisplay();
    }

    private function checkWhatToDisplay(): void
    {
        $this->checkIfSingleInSetNotEmpty(
            'what_to_display',
            'This value specifies the type of seminar manager plug-in to display.
            If this value is not set correctly, the wrong type of plug-in will be displayed.',
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

    protected function checkListPid(): void
    {
        $this->checkIfPositiveInteger(
            'listPID',
            'This value specifies the page that contains the list of events.
            If this value is not set correctly, the links in the list view
            and the back link on the list of registrations will not work.'
        );
    }
}
