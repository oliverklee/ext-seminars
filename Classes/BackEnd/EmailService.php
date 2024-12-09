<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Email\EmailBuilder;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Organizer;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Service for sending emails to the attendees of an event from the seminars back-end module.
 *
 * @internal
 */
class EmailService
{
    private EventMapper $eventMapper;

    public function __construct()
    {
        $this->eventMapper = MapperRegistry::get(EventMapper::class);
    }

    /**
     * Sends an email to the regular attendees of the event with the given UID using the provided email subject
     * and message body.
     *
     * @param positive-int $eventUid
     *
     * @throws NotFoundException if event could not be instantiated
     */
    public function sendEmailToRegularAttendees(int $eventUid, string $subject, string $rawBody): void
    {
        if (!$this->eventMapper->existsModel($eventUid)) {
            throw new NotFoundException('There is no event with this UID.', 1333292164);
        }

        $event = $this->eventMapper->find($eventUid);
        $organizer = $event->getFirstOrganizer();
        $sender = $event->getEmailSender();

        $registrationBagBuilder = GeneralUtility::makeInstance(RegistrationBagBuilder::class);
        $registrationBagBuilder->limitToEvent($eventUid);
        $registrations = $registrationBagBuilder->build();

        if (!$registrations->isEmpty()) {
            /** @var LegacyRegistration $oldRegistration */
            foreach ($registrations as $oldRegistration) {
                $user = $oldRegistration->getFrontEndUser();
                if (($user === null) || !$user->hasEmailAddress()) {
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
