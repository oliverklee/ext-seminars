<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Email\EmailBuilder;
use OliverKlee\Seminars\Email\Salutation;
use OliverKlee\Seminars\Hooks\Interfaces\BackEndModule;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Organizer;
use OliverKlee\Seminars\Model\Registration;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This is the base class for e-mail forms in the back end.
 */
abstract class AbstractEventMailForm
{
    /**
     * @var LegacyEvent the event which this e-mail form refers to
     */
    private $oldEvent;

    /**
     * @var Event the event which this e-mail form refers to
     */
    private $event;

    /**
     * @var array
     */
    private $postData = [];

    /**
     * hook objects for the list view
     *
     * @var list<BackEndModule>
     */
    private $hooks = [];

    /**
     * whether the hooks in $this->hooks have been retrieved
     *
     * @var bool
     */
    private $hooksHaveBeenRetrieved = false;

    /**
     * The constructor of this class. Instantiates an event object.
     *
     * @param positive-int $eventUid
     *
     * @throws \InvalidArgumentException
     * @throws NotFoundException if event could not be instantiated
     */
    public function __construct(int $eventUid)
    {
        $this->oldEvent = GeneralUtility::makeInstance(LegacyEvent::class, $eventUid);

        if (!$this->oldEvent->comesFromDatabase()) {
            throw new NotFoundException('There is no event with this UID.', 1333292164);
        }

        $mapper = MapperRegistry::get(EventMapper::class);
        $this->event = $mapper->find($eventUid);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns the logged-in back-end user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackEndUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns the event object.
     *
     * @return LegacyEvent the event object
     */
    protected function getOldEvent(): LegacyEvent
    {
        return $this->oldEvent;
    }

    /**
     * Returns the event this e-mail form refers to.
     *
     * @return Event the event
     */
    protected function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * Sets the POST data.
     *
     * @param array $postData associative array with the POST data, may be empty
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
    protected function getPostData(string $key): string
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
     * @return bool TRUE if the stored POST data contains an entry, FALSE otherwise
     *
     * @throws \InvalidArgumentException
     */
    protected function hasPostData(string $key): bool
    {
        return isset($this->postData[$key]);
    }

    /**
     * Sends an e-mail to the attendees to inform about the changed event status.
     */
    public function sendEmailToAttendees(): void
    {
        $event = $this->getEvent();
        $organizer = $event->getFirstOrganizer();
        $sender = $event->getEmailSender();

        $registrationBagBuilder = GeneralUtility::makeInstance(RegistrationBagBuilder::class);
        $registrationBagBuilder->limitToEvent($event->getUid());
        $registrations = $registrationBagBuilder->build();

        if (!$registrations->isEmpty()) {
            $registrationMapper = MapperRegistry::get(RegistrationMapper::class);
            /** @var LegacyRegistration $oldRegistration */
            foreach ($registrations as $oldRegistration) {
                $registrationUid = $oldRegistration->getUid();
                \assert($registrationUid > 0);
                $registration = $registrationMapper->find($registrationUid);
                $user = $registration->getFrontEndUser();
                if (($user === null) || !$user->hasEmailAddress()) {
                    continue;
                }
                $emailBuilder = GeneralUtility::makeInstance(EmailBuilder::class);
                $email = $emailBuilder->from($sender)
                    ->replyTo($organizer)
                    ->subject($this->getPostData('subject'))
                    ->to($user)
                    ->text($this->createMessageBody($user, $organizer))
                    ->build();

                $this->modifyEmailWithHook($registration, $email);
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
     * Adds a flash message to the queue.
     *
     * @param FlashMessage $flashMessage
     */
    protected function addFlashMessage(FlashMessage $flashMessage): void
    {
        $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Calls all registered hooks for modifying the e-mail.
     */
    protected function modifyEmailWithHook(Registration $registration, MailMessage $eMail): void
    {
    }

    /**
     * Creates the message body for the e-mail.
     *
     * @param FrontEndUser $user the recipient of the e-mail
     * @param Organizer $organizer the organizer which is selected as sender
     *
     * @return string the message with the salutation replaced by the user's
     *                name, will be empty if no message has been set in the POST
     *                data
     */
    private function createMessageBody(FrontEndUser $user, Organizer $organizer): string
    {
        $messageText = str_replace(
            '%salutation',
            GeneralUtility::makeInstance(Salutation::class)->getSalutation($user),
            $this->getPostData('messageBody')
        );
        $messageFooter = $organizer->hasEmailFooter()
            ? "\n-- \n" . $organizer->getEmailFooter() : '';

        return $messageText . $messageFooter;
    }

    /**
     * Gets the hooks.
     *
     * @return list<BackEndModule>
     *
     * @throws \UnexpectedValueException if any hook classes that do not implement the `BackEndModule` interface
     */
    protected function getHooks(): array
    {
        if (!$this->hooksHaveBeenRetrieved) {
            $hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'] ?? null;
            if (\is_array($hookClasses)) {
                foreach ($hookClasses as $hookClass) {
                    $hookInstance = GeneralUtility::makeInstance($hookClass);
                    if (!$hookInstance instanceof BackEndModule) {
                        throw new \UnexpectedValueException(
                            'The class ' . \get_class($hookInstance) . ' is used for the event list view hook, ' .
                            'but does not implement the BackEndModule interface.',
                            1301928334
                        );
                    }
                    $this->hooks[] = $hookInstance;
                }
            }

            $this->hooksHaveBeenRetrieved = true;
        }

        return $this->hooks;
    }

    protected function getUriBuilder(): UriBuilder
    {
        return GeneralUtility::makeInstance(UriBuilder::class);
    }
}
