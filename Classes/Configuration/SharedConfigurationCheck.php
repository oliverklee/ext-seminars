<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration check for all settings in `plugin.tx_seminars`.
 *
 * @internal
 */
class SharedConfigurationCheck extends AbstractConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCurrency();
        $this->checkGeneralPriceInMail();
        $this->checkRegistrationFlag();
        $this->checkSalutationMode();
        $this->checkStaticIncluded();

        if ($this->configuration->getAsBoolean('enableRegistration')) {
            $this->checkAllowRegistrationForEventsWithoutDate();
            $this->checkAllowRegistrationForStartedEvents();
            $this->checkAttendancesPid();
            $this->checkNotificationMail();
            $this->checkShowVacanciesThreshold();
            $this->checkThankYouMail();
            $this->checkUnregistrationDeadlineDaysBeforeBeginDate();
        }
    }

    /**
     * Checks whether `plugin.tx_seminars.currency` is not empty and a valid ISO 4217 alpha 3.
     */
    private function checkCurrency(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('static_currencies');
        $result = $connection->select(['cu_iso_3'], 'static_currencies')->fetchAllAssociative();
        $allowedValues = \array_column($result, 'cu_iso_3');

        $this->checkIfSingleInSetNotEmpty(
            'currency',
            'The specified currency setting is either empty or not a valid ISO 4217 alpha 3 code.',
            $allowedValues
        );
    }

    private function checkGeneralPriceInMail(): void
    {
        $this->checkIfBoolean(
            'generalPriceInMail',
            'This value specifies which wording to use for the standard price in emails.
            If this value is incorrect, the wrong wording might get used.'
        );
    }

    private function checkRegistrationFlag(): void
    {
        $this->checkIfBoolean(
            'enableRegistration',
            'This value specifies whether the extension will provide online registration.
            If this value is incorrect, the online  registration will not be enabled or disabled correctly.'
        );
    }

    private function checkAllowRegistrationForEventsWithoutDate(): void
    {
        $this->checkIfBoolean(
            'allowRegistrationForEventsWithoutDate',
            'This value specifies whether registration is possible for events without a fixed date.
            If this value is incorrect, registration might be possible even when this is not desired (or vice versa).'
        );
    }

    private function checkAllowRegistrationForStartedEvents(): void
    {
        $this->checkIfBoolean(
            'allowRegistrationForStartedEvents',
            'This value specifies whether registration is possible even when an event already has started.
            If this value is incorrect, registration might be possible even when this is not desired (or vice versa).'
        );
    }

    private function checkAttendancesPid(): void
    {
        $this->checkIfPositiveInteger(
            'attendancesPID',
            'This value specifies the page on which registrations will be stored.
            If this value is not set correctly, registration records will be dumped in the TYPO3 root page.
            <br/>
            If you explicitly do not wish to use the online registration feature, you can disable these checks
            by setting <strong>plugin.tx_seminars.enableRegistration</strong> and
            <strong>plugin.tx_seminars_pi1.enableRegistration</strong> to 0.'
        );
    }

    /**
     * Checks the configuration related to notification emails.
     */
    private function checkNotificationMail(): void
    {
        $this->checkHideFieldsInNotificationMail();
        $this->checkShowSeminarFieldsInNotificationMail();
        $this->checkShowFeUserFieldsInNotificationMail();
        $this->checkShowAttendanceFieldsInNotificationMail();
        $this->checkSendAdditionalNotificationEmails();
        $this->checkSendNotification();
        $this->checkSendNotificationOnQueueUpdate();
        $this->checkSendNotificationOnRegistrationForQueue();
        $this->checkSendNotificationOnUnregistration();
    }

    private function checkHideFieldsInNotificationMail(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'hideFieldsInNotificationMail',
            'These values specify the sections to hide in emails to organizers.
            A mistyped field name will cause the field to be included nonetheless.',
            ['summary', 'seminardata', 'feuserdata', 'attendancedata']
        );
    }

    private function checkShowSeminarFieldsInNotificationMail(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'showSeminarFieldsInNotificationMail',
            'These values specify the event fields to show in emails to  organizers.
            A mistyped field name will cause the field to not get included.',
            [
                'uid',
                'event_type',
                'title',
                'subtitle',
                'titleanddate',
                'date',
                'time',
                'accreditation_number',
                'credit_points',
                'room',
                'place',
                'speakers',
                'price_regular',
                'price_regular_early',
                'price_special',
                'price_special_early',
                'allows_multiple_registrations',
                'attendees',
                'attendees_min',
                'attendees_max',
                'vacancies',
                'enough_attendees',
                'is_full',
                'notes',
            ]
        );
    }

    private function checkShowFeUserFieldsInNotificationMail(): void
    {
        $this->checkIfMultiInTableColumnsOrEmpty(
            'showFeUserFieldsInNotificationMail',
            'These values specify the FE user fields to show in emails to organizers.
            A mistyped field name will cause the field to not get included.',
            'fe_users'
        );
    }

    private function checkShowAttendanceFieldsInNotificationMail(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'showAttendanceFieldsInNotificationMail',
            'These values specify the registration fields to show in emails to organizers.
            A mistyped field name will cause the field to not get included.',
            [
                'uid',
                'interests',
                'expectations',
                'background_knowledge',
                'lodgings',
                'accommodation',
                'foods',
                'food',
                'known_from',
                'notes',
                'checkboxes',
                'price',
                'attendance_mode',
                'seats',
                'total_price',
                'attendees_names',
                'kids',
                'method_of_payment',
                'company',
                'gender',
                'name',
                'address',
                'zip',
                'city',
                'country',
                'telephone',
                'email',
            ]
        );
    }

    private function checkSendAdditionalNotificationEmails(): void
    {
        $this->checkIfBoolean(
            'sendAdditionalNotificationEmails',
            'This value specifies whether organizers receive additional notification emails.
            If this value is incorrect, emails might get sent when this is not intended (or vice versa).'
        );
    }

    private function checkSendNotification(): void
    {
        $this->checkIfBoolean(
            'sendNotification',
            'This value specifies whether a notification email should be sent to the organizer
            after a user has registered.
            If this value is not set correctly, the sending of notifications probably will not work as expected.'
        );
    }

    private function checkSendNotificationOnQueueUpdate(): void
    {
        $this->checkIfBoolean(
            'sendNotificationOnQueueUpdate',
            'This value specifies whether a notification email should be sent to the organizer
            after the queue has been updated.
            If this value is not set correctly, the sending of notifications probably will not work as expected.'
        );
    }

    private function checkSendNotificationOnRegistrationForQueue(): void
    {
        $this->checkIfBoolean(
            'sendNotificationOnRegistrationForQueue',
            'This value specifies whether a notification email should be sent to the organizer
            after someone registered for the queue.
            If this value is not set correctly, the sending of notifications probably will not work as expected.'
        );
    }

    private function checkSendNotificationOnUnregistration(): void
    {
        $this->checkIfBoolean(
            'sendNotificationOnUnregistration',
            'This value specifies whether a notification email should be sent to the organizer
            after a user has unregistered.
            If this value is not set correctly, the sending of notifications probably will not work as expected.'
        );
    }

    private function checkShowVacanciesThreshold(): void
    {
        $this->checkIfNonNegativeIntegerOrEmpty(
            'showVacanciesThreshold',
            'This value specifies down from which threshold the exact number of vancancies will be displayed.
            If this value is incorrect, the number might get shown although this is not intended (or vice versa).'
        );
    }

    /**
     * Checks the configuration related to thank-you emails.
     */
    private function checkThankYouMail(): void
    {
        $this->checkHideFieldsInThankYouMail();
        $this->checkSendConfirmation();
        $this->checkSendConfirmationOnQueueUpdate();
        $this->checkSendConfirmationOnRegistrationForQueue();
        $this->checkSendConfirmationOnUnregistration();
    }

    private function checkHideFieldsInThankYouMail(): void
    {
        $this->checkIfMultiInSetOrEmpty(
            'hideFieldsInThankYouMail',
            'These values specify the sections to hide in emails to  participants.
            A mistyped field name will cause the field to be included nonetheless.',
            [
                'hello',
                'title',
                'uid',
                'ticket_id',
                'price',
                'attendance_mode',
                'seats',
                'total_price',
                'attendees_names',
                'lodgings',
                'accommodation',
                'foods',
                'food',
                'checkboxes',
                'kids',
                'accreditation_number',
                'credit_points',
                'date',
                'time',
                'place',
                'room',
                'paymentmethod',
                'billing_address',
                'interests',
                'planned_disclaimer',
                'footer',
                'first_name',
                'last_name',
                'unregistration_notice',
            ]
        );
    }

    private function checkSendConfirmation(): void
    {
        $this->checkIfBoolean(
            'sendConfirmation',
            'This value specifies whether a confirmation email should be sent to the user they have registered.
            If this value is not set correctly, the sending of notifications probably will not work as expected.'
        );
    }

    private function checkSendConfirmationOnQueueUpdate(): void
    {
        $this->checkIfBoolean(
            'sendConfirmationOnQueueUpdate',
            'This value specifies whether a confirmation email should be sent to the user
            after the queue has been updated.
            If this value is not set correctly, the sending of notifications probably will not work as expected.'
        );
    }

    private function checkSendConfirmationOnRegistrationForQueue(): void
    {
        $this->checkIfBoolean(
            'sendConfirmationOnRegistrationForQueue',
            'This value specifies whether a confirmation email should be sent
            to the user after the user has registered for the queue. If
            this value is not set correctly, the sending of notifications
            probably will not work as expected.'
        );
    }

    private function checkSendConfirmationOnUnregistration(): void
    {
        $this->checkIfBoolean(
            'sendConfirmationOnUnregistration',
            'This value specifies whether a confirmation email should be sent to the user after they have unregistered.
            If this value is not set correctly, the sending of notifications probably will not work as expected.'
        );
    }

    private function checkUnregistrationDeadlineDaysBeforeBeginDate(): void
    {
        $this->checkIfPositiveIntegerOrEmpty(
            'unregistrationDeadlineDaysBeforeBeginDate',
            'This value specifies the number of days before the start of an event until unregistration is possible.
            (If you want to disable this feature, just leave this value empty.)
            If this value is incorrect, unregistration will fail to work
            or the unregistration period will be a different number of days than desired.'
        );
    }
}
