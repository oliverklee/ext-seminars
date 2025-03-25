<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;

/**
 * Base class for configuration checks for front-end views.
 *
 * @internal
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

    protected function getMessageAboutRegistrationSwitch(): string
    {
        return '<br/>
            If you explicitly do not wish to use the online registration feature,
            you can disable these checks by setting
            <strong>plugin.tx_seminars.enableRegistration</strong> and
            <strong>plugin.tx_seminars_pi1.enableRegistration</strong> to 0.';
    }

    protected function isRegistrationEnabled(): bool
    {
        return $this->configuration->getAsBoolean('enableRegistration');
    }

    protected function checkRegistrationFlag(): void
    {
        $this->checkIfBoolean(
            'enableRegistration',
            'This value specifies whether the extension will provide online registration.
            If this value is incorrect, the online registration will not be enabled or disabled correctly.'
        );
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
                'list_registrations',
                'list_vip_registrations',
                'category_list',
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

    protected function checkGeneralPriceInSingle(): void
    {
        $this->checkIfBoolean(
            'generalPriceInSingle',
            'This value specifies whether the heading for the standard price in the detailed view
            and on the registration page will be just <em>Price</em> instead of <em>Standard price</em>.
            If this value is incorrect, the wrong label might be used.'
        );
    }

    protected function checkRegisterPid(): void
    {
        $this->checkIfPositiveInteger(
            'registerPID',
            'This value specifies the page that contains the registration form.
            If this value is not set correctly, the link to the registration page will not work.'
            . $this->getMessageAboutRegistrationSwitch()
        );
    }

    protected function checkLoginPid(): void
    {
        $this->checkIfPositiveInteger(
            'loginPID',
            'This value specifies the page that contains the login form.
            If this value is not set correctly, the link to the login page will not work.'
            . $this->getMessageAboutRegistrationSwitch()
        );
    }

    protected function checkDetailPid(): void
    {
        $this->checkIfPositiveInteger(
            'detailPID',
            'This value specifies the page that contains the detailed view.
            If this value is not set correctly, the links to single events will not work as expected.'
        );
    }

    protected function checkLimitFileDownloadToAttendees(): void
    {
        $this->checkIfBoolean(
            'limitFileDownloadToAttendees',
            'This value specifies whether the list of attached files is only shown to logged in
            and registered attendees.
            If this value is incorrect, the attached files may be shown to the public
            although they should be visible only to the attendees (or vice versa).'
        );
    }

    protected function checkShowOnlyEventsWithVacancies(): void
    {
        $this->checkIfBoolean(
            'showOnlyEventsWithVacancies',
            'This value specifies whether only events with vacancies should be shown in the list view.
            If this value is not configured properly, events with no vacancies will be shown in the list view.'
        );
    }

    protected function checkShowSingleEvent(): void
    {
        $this->checkIfPositiveIntegerOrEmpty(
            'showSingleEvent',
            'This value specifies which fixed single event should be shown.
            If this value is not set correctly, an error message will be shown instead.'
        );
    }

    protected function checkPages(): void
    {
        $this->checkIfIntegerListNotEmpty(
            'pages',
            'This value specifies the system folders that contain the event records for the list view.
            If this value is not set correctly, some events might not get displayed in the list view.'
        );
    }

    protected function checkRecursive(): void
    {
        $this->checkIfNonNegativeIntegerOrEmpty(
            'recursive',
            'This value specifies the how deep the recursion will be for selecting
            the pages that contain the event records for the list view.
            If this value is not set correctly, some events might not get displayed in the list view.'
        );
    }

    protected function checkTimeframeInList(): void
    {
        $this->checkIfSingleInSetNotEmpty(
            'timeframeInList',
            'This value specifies the time-frame from which events should be displayed in the list view.
            An incorrect value will events from a different time-frame cause to be displayed
            and other events to not get displayed.',
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
}
