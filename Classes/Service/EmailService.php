<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Oelib\Email\Mail;
use OliverKlee\Oelib\Email\MailerFactory;
use OliverKlee\Seminar\Email\Salutation;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * This class takes care of sending emails.
 *
 * The following markers will get replaced in the e-mail body:
 *
 * %salutation
 * %userName
 * %eventTitle
 * %eventDate
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EmailService implements SingletonInterface
{
    /**
     * @var Salutation
     */
    protected $salutationBuilder = null;

    /**
     * @var \Tx_Seminars_ViewHelper_DateRange
     */
    protected $dateRangeViewHelper = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->salutationBuilder = GeneralUtility::makeInstance(Salutation::class);
        $this->dateRangeViewHelper = GeneralUtility::makeInstance(\Tx_Seminars_ViewHelper_DateRange::class);
    }

    /**
     * Sends an email to of registered users of the given event.
     *
     * @param \Tx_Seminars_Model_Event $event
     * @param string $subject
     * @param string $body can contain %salutation which will expand to a full salutation with the user's name
     *
     * @return void
     */
    public function sendEmailToAttendees(\Tx_Seminars_Model_Event $event, string $subject, string $body)
    {
        /** @var MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(MailerFactory::class);
        $mailer = $mailerFactory->getMailer();

        /** @var \Tx_Seminars_Model_Registration $registration */
        foreach ($event->getRegistrations() as $registration) {
            $user = $registration->getFrontEndUser();
            if ($user === null || !$user->hasEmailAddress()) {
                continue;
            }

            /** @var Mail $eMail */
            $eMail = GeneralUtility::makeInstance(Mail::class);
            $eMail->setSender($event->getEmailSender());
            $eMail->setReplyTo($event->getFirstOrganizer());
            $eMail->addRecipient($user);
            $eMail->setSubject($this->replaceMarkers($subject, $event, $user));

            $eMail->setMessage($this->buildMessageBody($body, $event, $user));

            $mailer->send($eMail);
        }
    }

    /**
     * Builds the message body (including the email footer).
     *
     * @param string $rawBody
     * @param \Tx_Seminars_Model_Event $event
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return string
     */
    protected function buildMessageBody(
        string $rawBody,
        \Tx_Seminars_Model_Event $event,
        \Tx_Seminars_Model_FrontEndUser $user
    ): string {
        $bodyWithFooter = $this->replaceMarkers($rawBody, $event, $user);
        $organizer = $event->getFirstOrganizer();
        if ($organizer->hasEMailFooter()) {
            $bodyWithFooter .= LF . '-- ' . LF . $organizer->getEMailFooter();
        }

        return $bodyWithFooter;
    }

    /**
     * Replaces markers in $textWithMarkers.
     *
     * The following markers will get replaced:
     *
     * %salutation
     * %userName
     * %eventTitle
     * %eventDate
     *
     * @param string $textWithMarkers
     * @param \Tx_Seminars_Model_Event $event
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return string
     */
    protected function replaceMarkers(
        string $textWithMarkers,
        \Tx_Seminars_Model_Event $event,
        \Tx_Seminars_Model_FrontEndUser $user
    ): string {
        $markers = [
            '%salutation' => $this->salutationBuilder->getSalutation($user),
            '%userName' => $user->getName(),
            '%eventTitle' => $event->getTitle(),
            '%eventDate' => $this->dateRangeViewHelper->render($event, '-'),
        ];

        return str_replace(array_keys($markers), $markers, $textWithMarkers);
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService|null
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
