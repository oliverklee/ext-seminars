<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Email\EmailBuilder;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Service for sending emails to the attendees of an event from the seminars back-end module.
 *
 * @internal
 */
class EmailService implements SingletonInterface
{
    private EventRepository $eventRepository;

    private RegistrationRepository $registrationRepository;

    public function __construct(EventRepository $eventRepository, RegistrationRepository $registrationRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->registrationRepository = $registrationRepository;
    }

    /**
     * Sends an email to the regular attendees of the event with the given UID using the provided email subject
     * and message body.
     *
     * @param positive-int $eventUid
     *
     * @throws NotFoundException if event could not be instantiated
     */
    public function sendPlainTextEmailToRegularAttendees(int $eventUid, string $subject, string $rawBody): void
    {
        $event = $this->eventRepository->findByUid($eventUid);
        if (!$event instanceof EventDateInterface) {
            throw new NotFoundException('There is no event with this UID.', 1333292164);
        }

        $organizer = $event->getFirstOrganizer();
        $sender = $this->determineEmailSenderForEvent($event);

        $registrations = $this->registrationRepository->findRegularRegistrationsByEvent($eventUid);

        if ($registrations !== []) {
            foreach ($registrations as $registration) {
                $user = $registration->getUser();
                if (!($user instanceof FrontendUser) || $user->getEmail() === '') {
                    continue;
                }
                $email = GeneralUtility::makeInstance(EmailBuilder::class)->from($sender)
                    ->replyTo($organizer)
                    ->subject($subject)
                    ->to($user)
                    ->text($this->appendEmailFooterIfProvided($rawBody, $organizer))
                    ->build();

                $email->send();
            }

            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate('message_emailToAttendeesSent', 'seminars'),
                '',
                FlashMessage::OK,
                true
            );
            $this->addFlashMessage($message);
        }
    }

    /**
     * Returns a `MailRole` with the default email data from the TYPO3 configuration if possible.
     *
     * Otherwise, returns the first organizer of the given event.
     */
    private function determineEmailSenderForEvent(EventDateInterface $event): MailRole
    {
        $systemEmailFromBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);
        if ($systemEmailFromBuilder->canBuild()) {
            $sender = $systemEmailFromBuilder->build();
        } else {
            $sender = $event->getFirstOrganizer();
        }

        return $sender;
    }

    private function addFlashMessage(FlashMessage $flashMessage): void
    {
        $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier('extbase.flashmessages.tx_seminars_web_seminarsevents');
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    private function appendEmailFooterIfProvided(string $rawBody, Organizer $sender): string
    {
        $messageFooter = $sender->hasEmailFooter() ? "\n-- \n" . $sender->getEmailFooter() : '';

        return $rawBody . $messageFooter;
    }
}
