<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Email\EmailBuilder;
use OliverKlee\Seminars\Email\Salutation;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Organizer;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This class represents an email form.
 *
 * @internal
 */
class GeneralEventMailForm
{
    private Event $event;

    /**
     * @var array<string, string|int>
     */
    private array $postData = [];

    /**
     * The constructor of this class. Instantiates an event object.
     *
     * @param positive-int $eventUid
     *
     * @throws NotFoundException if event could not be instantiated
     */
    public function __construct(int $eventUid)
    {
        $mapper = MapperRegistry::get(EventMapper::class);
        if (!$mapper->existsModel($eventUid)) {
            throw new NotFoundException('There is no event with this UID.', 1333292164);
        }

        $this->event = $mapper->find($eventUid);
    }

    private function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param array<string, string|int> $postData associative array with the POST data, may be empty
     */
    public function setPostData(array $postData): void
    {
        $this->postData = $postData;
    }

    /**
     * Returns an entry from the stored POST data or an empty string if that
     * key is not set.
     *
     * @param non-empty-string $key the key of the field to return
     *
     * @return string the value of the field, may be empty
     */
    private function getPostData(string $key): string
    {
        if (!$this->hasPostData($key)) {
            return '';
        }

        return (string)$this->postData[$key];
    }

    /**
     * Checks whether the stored POST data contains data for a certain field.
     *
     * @param non-empty-string $key the key of the field to check for
     *
     * @throws \InvalidArgumentException
     */
    private function hasPostData(string $key): bool
    {
        return isset($this->postData[$key]);
    }

    /**
     * Sends an email to the attendees to inform about the changed event status.
     */
    public function sendEmailToAttendees(): void
    {
        $event = $this->getEvent();
        $organizer = $event->getFirstOrganizer();
        $sender = $event->getEmailSender();

        $eventUid = $event->getUid();
        \assert($eventUid > 0);
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
                    ->subject($this->getPostData('subject'))
                    ->to($user)
                    ->text($this->createMessageBody($user, $organizer))
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

    /**
     * @return string the message with the salutation replaced by the user's name,
     *                will be empty if no message has been set in the POST data
     */
    private function createMessageBody(FrontEndUser $recipient, Organizer $sender): string
    {
        $messageText = str_replace(
            '%salutation',
            GeneralUtility::makeInstance(Salutation::class)->getSalutation($recipient),
            $this->getPostData('messageBody')
        );
        $messageFooter = $sender->hasEmailFooter()
            ? "\n-- \n" . $sender->getEmailFooter() : '';

        return $messageText . $messageFooter;
    }
}
