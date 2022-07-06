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
    private $oldEvent = null;

    /**
     * @var Event the event which this e-mail form refers to
     */
    private $event = null;

    /**
     * @var bool whether the form is complete
     */
    private $isComplete = true;

    /**
     * @var string[]
     */
    private $errorMessages = [];

    /**
     * @var array
     */
    private $postData = [];

    /**
     * @var string the action of this form
     */
    protected $action = '';

    /**
     * @var string the prefix for all locallang keys for prefilling the form,
     *             must not be empty
     */
    protected $formFieldPrefix = '';

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
     * @param positive-int $eventUid UID of an event, must be > 0
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

        if ($this->isSubmitted() && $this->validateFormData()) {
            $this->setEventStatus();
            $this->sendEmailToAttendees();
            $this->redirectToListView();
        }

        $urlParameters = ['id' => PageFinder::getInstance()->getPageUid()];
        $formAction = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);

        return '<fieldset id="EventMailForm"><form action="' . \htmlspecialchars($formAction, ENT_QUOTES | ENT_HTML5) .
            '" method="post">' .
            $this->createSubjectFormElement() .
            $this->createMessageBodyFormElement() .
            $this->createBackButton() .
            $this->createSubmitButton() .
            '<p><input type="hidden" name="action" value="' . $this->action .
            '" /><input type="hidden" name="eventUid" value="' .
            $this->getEvent()->getUid() . '" /><input type="hidden" ' .
            'name="isSubmitted" value="1" /></p></form></fieldset>';
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
     * Validates the input that comes via POST data. If a field contains invalid
     * data, an error message for this field is stored in $this->errorMessages.
     *
     * The following fields are tested for being non-empty:
     * - subject
     * - messageBody
     *
     * @return bool TRUE if the form data is valid, FALSE otherwise
     */
    private function validateFormData(): bool
    {
        if ($this->getPostData('subject') === '') {
            $this->markAsIncomplete();
            $this->setErrorMessage(
                'subject',
                $this->getLanguageService()->getLL('eventMailForm_error_subjectMustNotBeEmpty')
            );
        }

        if ($this->getPostData('messageBody') === '') {
            $this->markAsIncomplete();
            $this->setErrorMessage(
                'messageBody',
                $this->getLanguageService()->getLL('eventMailForm_error_messageBodyMustNotBeEmpty')
            );
        }

        return $this->isComplete;
    }

    /**
     * Marks the form as incomplete (i.e. some fields were empty or not filled
     * with valid data). This will hinder the later process to really send the
     * mail and do any further processing with the event.
     *
     * This method is public for testing only.
     */
    public function markAsIncomplete(): void
    {
        $this->isComplete = false;
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
        $classMarker = $this->hasErrorMessage('subject') ? 'class="error" ' : '';

        return '<p><label for="subject">' .
            $this->getLanguageService()->getLL('eventMailForm_subject') . '</label>' .
            '<input type="text" id="subject" name="subject" value="' .
            \htmlspecialchars($this->fillFormElement('subject'), ENT_QUOTES | ENT_HTML5, 'utf-8') . '" ' .
            $classMarker . '/>' . $this->getErrorMessage('subject') . '</p>';
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
        $classMarker = $this->hasErrorMessage('messageBody') ? ', error' : '';

        return '<p><label for="messageBody">' .
            $this->getLanguageService()->getLL('eventMailForm_message') . '</label>' .
            '<textarea cols="50" rows="20" class="eventMailForm_message' .
            $classMarker . '" id="messageBody" name="messageBody">' .
            \htmlspecialchars($messageBody, ENT_QUOTES | ENT_HTML5) . '</textarea>' .
            $this->getErrorMessage('messageBody') . '</p>';
    }

    /**
     * Returns the HTML for the back button.
     *
     * @return string HTML for the back button, will not be empty
     */
    protected function createBackButton(): string
    {
        return '<p><input type="button" value="' .
            $this->getLanguageService()->getLL('eventMailForm_backButton') .
            '" class="backButton" onclick="window.location=window.location" />' .
            '</p>';
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
     * Returns all error messages set via setErrorMessage for the given field name.
     *
     * @param non-empty-string $fieldName the field name for which the error message should be returned
     *
     * @return string HTML with error message for the field, will be empty if there's no error message for this field
     *
     * @throws \InvalidArgumentException
     */
    protected function getErrorMessage(string $fieldName): string
    {
        if (!$this->hasErrorMessage($fieldName)) {
            return '';
        }

        return '<p>' . \htmlspecialchars($this->errorMessages[$fieldName], ENT_QUOTES | ENT_HTML5) . '</p>';
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
            $result = $this->getInitialValue($fieldName);
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
    private function sendEmailToAttendees(): void
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
                $this->getLanguageService()->getLL('message_emailToAttendeesSent'),
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
        return '<p><button class="submitButton ' . $this->action . '">' .
            '<p>' . $this->getSubmitButtonLabel() . '</p></button></p>';
    }

    /**
     * Returns the label for the submit button.
     *
     * @return string label for the submit button, will not be empty
     */
    abstract protected function getSubmitButtonLabel(): string;

    /**
     * Returns the initial value for a certain field.
     *
     * @param string $fieldName
     *        the name of the field for which to get the initial value, must be
     *        either 'subject' or 'messageBody'
     *
     * @return string the initial value of the field, will be empty if no initial value is defined
     *
     * @throws \InvalidArgumentException
     */
    protected function getInitialValue(string $fieldName): string
    {
        switch ($fieldName) {
            case 'subject':
                $result = $this->appendTitleAndDate($this->formFieldPrefix);
                break;
            case 'messageBody':
                $result = $this->getMessageBodyFormContent();
                break;
            default:
                throw new \InvalidArgumentException(
                    'There is no initial value for the field "' . $fieldName . '" defined.',
                    1333292199
                );
        }

        return $result;
    }

    /**
     * Appends the title and the date to the subject.
     *
     * @param string $prefix
     *        the prefix for the locallang key of the subject, must be either
     *        "cancelMailForm_prefillField_" or "confirmMailForm_prefillField_"
     *        and always have a trailing underscore
     *
     * @return string the subject for the mail form suffixed with the event
     *                title and date, will be empty if no locallang label
     *                could be found for the given prefix
     */
    private function appendTitleAndDate(string $prefix): string
    {
        return $this->getLanguageService()->getLL($prefix . 'subject') . ' ' . $this->getOldEvent()->getTitleAndDate();
    }

    /**
     * Replaces the string placeholder "%s" with a localized placeholder for
     * the salutation.
     *
     * @param string $prefix
     *        the prefix for the locallang key of the messageBody, must be
     *        either "cancelMailForm_prefillField_" or
     *        "confirmMailForm_prefillField_" and always have a trailing
     *        underscore
     *
     * @return string the content for the prefilled messageBody field with the
     *                replaced placeholders, will be empty if no locallang label
     *                for the given prefix could be found
     */
    protected function localizeSalutationPlaceholder(string $prefix): string
    {
        $eventDetails = GeneralUtility::makeInstance(Salutation::class)->createIntroduction(
            '"%s"',
            $this->getOldEvent()
        );
        $introduction = sprintf($this->getLanguageService()->getLL($prefix . 'introduction'), $eventDetails);

        return "%salutation\n\n" . $introduction . "\n"
            . $this->getLanguageService()->getLL($prefix . 'messageBody');
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
     * Gets the content of the message body for the e-mail.
     *
     * @return string the content for the message body, will not be empty
     */
    protected function getMessageBodyFormContent(): string
    {
        return $this->localizeSalutationPlaceholder($this->formFieldPrefix);
    }

    /**
     * Sets an error message.
     *
     * @param string $fieldName the field name to set the error message for, must be "messageBody" or "subject"
     * @param string $message the error message to set, may be empty
     */
    protected function setErrorMessage(string $fieldName, string $message): void
    {
        if ($this->hasErrorMessage($fieldName)) {
            $this->errorMessages[$fieldName] .= '<br />' . $message;
        } else {
            $this->errorMessages[$fieldName] = $message;
        }
    }

    /**
     * Checks whether an error message has been set for the given field name.
     *
     * @param string $fieldName the field to check the error message for, must not be empty
     *
     * @return bool whether an error message has been stored for the given field name
     */
    private function hasErrorMessage(string $fieldName): bool
    {
        return isset($this->errorMessages[$fieldName]);
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
            $hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'];
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
