<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
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
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
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
     * @var string
     */
    private const MODULE_NAME = 'web_seminars';

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
     * @var string the action of this form
     */
    protected $action = '';

    /**
     * hook objects for the list view
     *
     * @var array<int, BackEndModule>
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
     * Returns the HTML needed to show the form. If the current user has not
     * the necessary permissions, an empty string is returned.
     *
     * @return string HTML for the whole form, will be empty if the user has
     *                insufficient permissions
     */
    public function render(): string
    {
        if (!$this->checkAccess()) {
            return '';
        }

        if ($this->isSubmitted()) {
            $this->setEventStatus();
            $this->sendEmailToAttendees();
            $this->redirectToListView();
        }

        $urlParameters = ['id' => PageFinder::getInstance()->getPageUid()];
        $formAction = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);

        return '<form action="' . \htmlspecialchars($formAction, ENT_QUOTES | ENT_HTML5) . '" method="post">' .
            $this->createSubjectFormElement() .
            $this->createMessageBodyFormElement() .
            $this->createBackButton() .
            $this->createSubmitButton() .
            '<input type="hidden" name="action" value="' . $this->action . '" />' .
            '<input type="hidden" name="eventUid" value="' . $this->getEvent()->getUid() . '" />' .
            '<input type="hidden" name="isSubmitted" value="1" />' .
            '</form>';
    }

    /**
     * Checks whether the form was already submitted by the user.
     *
     * @return bool TRUE if the form was submitted by the user, FALSE otherwise
     */
    protected function isSubmitted(): bool
    {
        return $this->getPostData('isSubmitted') === '1';
    }

    /**
     * Checks whether the current back-end user has the needed permissions to
     * access this form.
     *
     * @return bool whether the user is allowed to see/use the form
     */
    public function checkAccess(): bool
    {
        return $this->getBackEndUser()->check('tables_select', 'tx_seminars_seminars');
    }

    /**
     * Returns the HTML for the subject field of the form. It gets pre-filled
     * depending on the implementation of this abstract class. Shows an error
     * message next to the field if required after validation of this field.
     *
     * @return string HTML for the subject field, optionally with an error
     *                message, will not be empty
     */
    protected function createSubjectFormElement(): string
    {
        return '<div class="mb-3">' .
            '<label for="subject" class="form-label">' . $this->getLanguageService()->getLL('eventMailForm_subject') .
            '</label>' .
            '<input type="text" class="form-control"  id="subject" name="subject" required ' .
            'value="' . \htmlspecialchars($this->fillFormElement('subject'), ENT_QUOTES | ENT_HTML5, 'utf-8') . '" />' .
            '</div>';
    }

    /**
     * Returns the HTML for the message body field of the form. It gets pre-filled
     * depending on the implementation of this abstract class. Shows an error
     * message next to the field if required after validation of this field.
     *
     * @return string HTML for the subject field, optionally with an error message
     */
    protected function createMessageBodyFormElement(): string
    {
        $messageBody = $this->fillFormElement('messageBody');

        return '<div class="mb-3">' .
            '<label for="messageBody"  class="form-label">' .
            $this->getLanguageService()->getLL('eventMailForm_message') . '</label>' .
            '<textarea cols="50" rows="20" class="form-control" id="messageBody" name="messageBody" required>' .
            \htmlspecialchars($messageBody, ENT_QUOTES | ENT_HTML5) . '</textarea>' .
            '</div>';
    }

    /**
     * Returns the HTML for the back button.
     *
     * @return string HTML for the back button, will not be empty
     */
    protected function createBackButton(): string
    {
        return '<button type="button"  class="btn btn-secondary" onclick="window.location=window.location">' .
            $this->getLanguageService()->getLL('eventMailForm_backButton') .
            '</button>';
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
     * Returns either a default value or the value that was sent via POST data
     * for a given field.
     *
     * For the subject field, we fill in the event's title and date after the
     * default subject for confirming an event.
     *
     * @param non-empty-string $fieldName the field name
     *
     * @return string either the data from POST array or a default value for this field
     */
    protected function fillFormElement(string $fieldName): string
    {
        if ($this->isSubmitted()) {
            $result = $this->getPostData($fieldName);
        } else {
            $result = '';
        }

        return $result;
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
                $registration = $registrationMapper->find($oldRegistration->getUid());
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
     * Marks an event according to the status to set (if any) and commits the change to the database.
     */
    protected function setEventStatus(): void
    {
    }

    /**
     * Redirects to the list view.
     */
    private function redirectToListView(): void
    {
        $urlParameters = ['id' => PageFinder::getInstance()->getPageUid()];
        $url = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);

        HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Location: ' . $url);
    }

    /**
     * Returns the HTML for the submit button.
     *
     * @return string HTML for the submit button, will not be empty
     */
    protected function createSubmitButton(): string
    {
        return '<button class="btn btn-primary">' . $this->getSubmitButtonLabel() . '</button>';
    }

    /**
     * Returns the label for the submit button.
     *
     * @return string label for the submit button, will not be empty
     */
    abstract protected function getSubmitButtonLabel(): string;

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
     * @return array<int, BackEndModule>
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

    /**
     * Returns the URL to a given module.
     *
     * @param string $moduleName name of the module
     * @param array $urlParameters URL parameters that should be added as key-value pairs
     *
     * @return string calculated URL
     */
    protected function getRouteUrl(string $moduleName, array $urlParameters = []): string
    {
        $uriBuilder = $this->getUriBuilder();
        try {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
        } catch (RouteNotFoundException $e) {
            // no route registered, use the fallback logic to check for a module
            // @phpstan-ignore-next-line This line is for TYPO3 9LTS only, and we check with 10LTS.
            $uri = $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
        }

        return (string)$uri;
    }

    protected function getUriBuilder(): UriBuilder
    {
        return GeneralUtility::makeInstance(UriBuilder::class);
    }
}
