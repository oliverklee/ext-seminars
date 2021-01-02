<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

use OliverKlee\Oelib\Templating\Template;

/**
 * Use this interface for hooks concerning the registration emails.
 *
 * It supersedes the deprecated `RegistrationEmailHookInterface` interface.
 *
 * You can use it to customize emails before they are sent.
 *
 * @author Pascal Rinker <projects@jweiland.net>
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface RegistrationEmail extends Hook
{
    /**
     * Modifies the attendee "Thank you" email just before it is sent.
     *
     * You may modify the recipient or the sender as well as the subject and the body of the email.
     *
     * @param \Tx_Oelib_Mail $email
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
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the attendee "Thank you" email body just before the subpart is rendered to plain text.
     *
     * This method is called for every confirmation email, even if HTML emails are configured.
     * The body of a HTML email always contains a plain text version, too.
     *
     * You may modify or set marker values in the template.
     *
     * @param Template $emailTemplate
     * @param \Tx_Seminars_Model_Registration $registration
     * @param string $emailReason Possible values:
     *          - confirmation
     *          - confirmationOnUnregistration
     *          - confirmationOnRegistrationForQueue
     *          - confirmationOnQueueUpdate
     *
     * @return void
     */
    public function modifyAttendeeEmailBodyPlainText(
        Template $emailTemplate,
        \Tx_Seminars_Model_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the attendee "Thank you" email body just before the subpart is rendered to HTML.
     *
     * This method is called only, if HTML emails are configured for confirmation emails.
     *
     * You may modify or set marker values in the template.
     *
     * @param Template $emailTemplate
     * @param \Tx_Seminars_Model_Registration $registration
     * @param string $emailReason Possible values:
     *          - confirmation
     *          - confirmationOnUnregistration
     *          - confirmationOnRegistrationForQueue
     *          - confirmationOnQueueUpdate
     *
     * @return void
     */
    public function modifyAttendeeEmailBodyHtml(
        Template $emailTemplate,
        \Tx_Seminars_Model_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the organizer notification email just before it is sent.
     *
     * You may modify the recipient or the sender as well as the subject and the body of the email.
     *
     * @param \Tx_Oelib_Mail $email
     * @param \Tx_Seminars_Model_Registration $registration
     * @param string $emailReason Possible values:
     *        - notification
     *        - notificationOnUnregistration
     *        - notificationOnRegistrationForQueue
     *        - notificationOnQueueUpdate
     *
     * @return void
     */
    public function modifyOrganizerEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Registration $registration,
        string $emailReason
    );

    /**
     * Modifies the organizer additional notification email just before it is sent.
     *
     * You may modify the recipient or the sender as well as the subject and the body of the email.
     *
     * @param \Tx_Oelib_Mail $email
     * @param \Tx_Seminars_Model_Registration $registration
     * @param string $emailReason Possible values:
     *          - 'EnoughRegistrations' if the event has enough attendances
     *          - 'IsFull' if the event is fully booked
     *          see Tx_Seminars_Service_RegistrationManager::getReasonForNotification()
     *
     * @return void
     */
    public function modifyAdditionalEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Registration $registration,
        string $emailReason
    );
}
