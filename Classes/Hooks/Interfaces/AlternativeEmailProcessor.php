<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks that implement an alternative email process.
 *
 * Might be useful if you want to use a different notification system than email.
 *
 * @author Oliver Heins <o.heins@bitmotion.de>
 * @author Andreas Engel <a.engel@bitmotion.de>
 */
interface AlternativeEmailProcessor extends Hook
{
    /**
     * Is called to send the attendee "Thank you" through a different system than seminars built-in mailer.
     *
     * @param \Tx_Oelib_Mail                  $email
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return mixed
     */
    public function processAttendeeEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Registration $registration
    );

    /**
     * Is called to send the organizer notification through a different system than seminars built-in mailer.
     *
     * @param \Tx_Oelib_Mail                  $email
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return mixed
     */
    public function processOrganizerEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Registration $registration
    );

    /**
     * Is called to send the organizer additional notification through a different system than seminars built-in mailer.
     *
     * @param \Tx_Oelib_Mail                  $email
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return mixed
     */
    public function processAdditionalEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Registration $registration
    );

    /**
     * Is called to send the organizer reminder notification through a different system than seminars built-in mailer.
     *
     * @param \Tx_Oelib_Mail              $email
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return mixed
     */
    public function processReminderEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Event $event
    );

    /**
     * Is called to send the reviewer notification through a different system than seminars built-in mailer.
     *
     * @param \Tx_Oelib_Mail              $email
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return mixed
     */
    public function processReviewerEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Event $event
    );

    /**
     * Is called to send the reviewer notification through a different system than seminars built-in mailer.
     *
     * @param \Tx_Oelib_Mail              $email
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return mixed
     */
    public function processAdditionalReviewerEmail(
        \Tx_Oelib_Mail $email
    );
}
