<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the event editor.
 */
class EventEditorConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCommonFrontEndSettings();

        $this->checkEventEditorTemplateFile();
        $this->checkEventEditorFeGroupID();
        $this->checkCreateEventsPID();
        $this->checkEventSuccessfullySavedPID();
        $this->checkDisplayFrontEndEditorFields();
        $this->checkRequiredFrontEndEditorFields();
        $this->checkRequiredFrontEndEditorPlaceFields();
    }

    private function checkEventEditorTemplateFile(): void
    {
        $this->checkFileExists(
            'eventEditorTemplateFile',
            'This specifies the HTML template for the event editor.
            If this file is not available, the event editor cannot be used.'
        );
    }

    private function checkCreateEventsPID(): void
    {
        $this->checkIfPositiveInteger(
            'createEventsPID',
            'This value specifies the page on which FE-entered events will be stored.
            If this value is not set correctly, those event records will be dumped in the TYPO3 root page.'
        );
    }

    private function checkEventSuccessfullySavedPID(): void
    {
        $this->checkIfPositiveInteger(
            'eventSuccessfullySavedPID',
            'This value specifies the page to which the user will be redirected
            after saving an event record in the front end.
            If this value is not set correctly, the redirect will not work.'
        );
    }

    private function checkDisplayFrontEndEditorFields(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'displayFrontEndEditorFields',
            'This value specifies which fields should be displayed in the front-end editor.
            Incorrect values will cause the fields not to be displayed.',
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
                'price_special',
                'price_special_early',
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
                'notes',
            ]
        );
    }

    private function checkRequiredFrontEndEditorFields(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'requiredFrontEndEditorFields',
            'This value specifies which fields are required to be filled when editing an event.
            Some fields will be not be required if this configuration is incorrect.',
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
                'price_special',
                'price_special_early',
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
                'notes',
            ]
        );

        // checks whether the required fields are visible
        $this->checkIfMultiInSetOrEmpty(
            'requiredFrontEndEditorFields',
            'This value specifies which fields are required to be filled when  editing an event.
            Some fields are set to required but are actually not configured to be visible in the form.
            The form cannot be submitted as long as this inconsistency remains.',
            $this->configuration->getAsTrimmedArray('displayFrontEndEditorFields')
        );
    }

    private function checkRequiredFrontEndEditorPlaceFields(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'requiredFrontEndEditorPlaceFields',
            'This value specifies which fields are required to be filled when editing a place.
            Some fields will be not be required if this configuration is incorrect.',
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
}
