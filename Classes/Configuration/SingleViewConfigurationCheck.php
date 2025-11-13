<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the single view.
 *
 * @internal
 */
class SingleViewConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCommonFrontEndSettings();

        $this->checkRegistrationFlag();

        $this->checkShowSingleEvent();
        $this->checkHideFields();
        $this->checkGeneralPriceInSingle();
        $this->checkShowSpeakerDetails();
        $this->checkShowSiteDetails();
        if ($this->isRegistrationEnabled()) {
            $this->checkRegisterPid();
            $this->checkLoginPid();
        }
        $this->checkRegistrationsListPidOptional();
        $this->checkRegistrationsVipListPidOptional();
        $this->checkDetailPid();
        $this->checkSingleViewImageSizes();
        $this->checkLimitFileDownloadToAttendees();
        $this->checkShowOnlyEventsWithVacancies();
    }

    private function checkHideFields(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'hideFields',
            'This value specifies which section to remove from the details view.
            Incorrect values will cause the sections to still be displayed.',
            [
                'image',
                'event_type',
                'title',
                'subtitle',
                'description',
                'accreditation_number',
                'credit_points',
                'category',
                'date',
                'timeslots',
                'uid',
                'time',
                'place',
                'room',
                'expiry',
                'speakers',
                'partners',
                'tutors',
                'leaders',
                'price_regular',
                'price_special',
                'paymentmethods',
                'additional_information',
                'target_groups',
                'attached_files',
                'organizers',
                'vacancies',
                'deadline_registration',
                'otherdates',
                'eventsnextday',
                'registration',
                'back',
                'requirements',
                'dependencies',
            ],
        );
    }

    private function checkShowSpeakerDetails(): void
    {
        $this->checkIfBoolean(
            'showSpeakerDetails',
            'This value specifies whether to show detailed information of the speakers in the single view.
            If this value is incorrect, the detailed information might be shown although this is not intended
            (or vice versa).',
        );
    }

    private function checkShowSiteDetails(): void
    {
        $this->checkIfBoolean(
            'showSiteDetails',
            'This value specifies whether to show detailed information of the locations in the single view.
            If this value is incorrect, the detailed information might  be shown although this is not intended
            (or vice versa).',
        );
    }

    private function checkRegistrationsListPidOptional(): void
    {
        $this->checkIfNonNegativeIntegerOrEmpty(
            'registrationsListPID',
            'This value specifies the page that contains the list of registrations for an event.
            If this value is not set correctly, the link to that page will not work.',
        );
    }

    private function checkRegistrationsVipListPidOptional(): void
    {
        $this->checkIfNonNegativeIntegerOrEmpty(
            'registrationsVipListPID',
            'This value specifies the page that contains the list of registrations for an event.
            If this value is not set correctly, the link to that page will not work.',
        );
    }

    private function checkSingleViewImageSizes(): void
    {
        $this->checkSingleViewImageWidth();
        $this->checkSingleViewImageHeight();
    }

    private function checkSingleViewImageWidth(): void
    {
        $this->checkIfPositiveInteger(
            'seminarImageSingleViewWidth',
            'This value specifies the width of the image of a seminar.
            If this value is not set, the image will be shown in full size.',
        );
    }

    private function checkSingleViewImageHeight(): void
    {
        $this->checkIfPositiveInteger(
            'seminarImageSingleViewHeight',
            'This value specifies the height of the image of a seminar.
            If  this value is not set, the image will be shown in full size.',
        );
    }
}
