<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the registration emails.
 *
 * It supersedes the deprecated `RegistrationEmailHookInterface` interface.
 *
 * Customize emails before they are sent.
 *
 * @author Pascal Rinker <projects@jweiland.net>
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface RegistrationEmail extends Hook
{
    /**
     * Modifies the attendee "Thank you" email just before it is sent.
     *
     * You may modify receiver or sender as well as subject and body of the email.
     *
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_Model_Registration $registration
     * @param string $emailReason Possible values:
     *          - confirmation
     *          - confirmationOnUnregistration
     *          - confirmationOnRegistrationForQueue
     *          - confirmationOnQueueUpdate
     *
     * @return void
     */
    public function modifyAttendeeEmail(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_Model_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the attendee "Thank you" email body just before the subpart is rendered to PlainText.
     *
     * This method is called for every confirmation mail, even if HTML emails are configured.
     * The body of a HTML mail alwyas contains a PlainText version, too.
     *
     * You may modify or set marker values in the template.
     *
     * @param \Tx_Oelib_Template $emailTemplate
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason Possible values:
     *          - confirmation
     *          - confirmationOnUnregistration
     *          - confirmationOnRegistrationForQueue
     *          - confirmationOnQueueUpdate
     *
     * @return void
     */
    public function modifyAttendeeEmailBodyPlainText(
        \Tx_Oelib_Template $emailTemplate,
        \Tx_Seminars_OldModel_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the attendee "Thank you" email body just before the subpart is rendered to HTML.
     *
     * This method is called only, if HTML emails are configured for confirmation emails.
     *
     * You may modify or set marker values in the template.
     *
     * @param \Tx_Oelib_Template $emailTemplate
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason Possible values:
     *          - confirmation
     *          - confirmationOnUnregistration
     *          - confirmationOnRegistrationForQueue
     *          - confirmationOnQueueUpdate
     *
     * @return void
     */
    public function modifyAttendeeEmailBodyHtml(
        \Tx_Oelib_Template $emailTemplate,
        \Tx_Seminars_OldModel_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the organizer notification email just before it is sent.
     *
     * You may modify receiver or sender as well as subject and body of the email.
     *
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason Possible values:
     *        - notification
     *        - notificationOnUnregistration
     *        - notificationOnRegistrationForQueue
     *        - notificationOnQueueUpdate
     *
     * @return void
     */
    public function modifyOrganizerEmail(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_OldModel_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the organizer additional notification email just before it is sent.
     *
     * You may modify receiver or sender as well as subject and body of the email.
     *
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason Possible values:
     *          - 'EnoughRegistrations' if the event has enough attendances
     *          - 'IsFull' if the event is fully booked
     *          see Tx_Seminars_Service_RegistrationManager::getReasonForNotification()
     *
     * @return void
     */
    public function modifyAdditionalEmail(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_OldModel_Registration $registration,
        string $emailReason
    );
}
