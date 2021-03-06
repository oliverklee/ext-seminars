<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Email\Mail;
use OliverKlee\Oelib\Email\MailerFactory;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminar\Email\Salutation;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * This is the base class for e-mail forms in the back end.
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class AbstractEventMailForm
{
    /**
     * @var string
     */
    const MODULE_NAME = 'web_seminars';

    /**
     * @var \Tx_Seminars_OldModel_Event the event which this e-mail form refers to
     */
    private $oldEvent = null;

    /**
     * @var \Tx_Seminars_Model_Event the event which this e-mail form refers to
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
     * @var array
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
     * @param int $eventUid UID of an event, must be > 0
     *
     * @throws \InvalidArgumentException
     * @throws NotFoundException if event could not be instantiated
     */
    public function __construct(int $eventUid)
    {
        if ($eventUid <= 0) {
            throw new \InvalidArgumentException('$eventUid must be > 0.');
        }

        $this->oldEvent = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_Event::class, $eventUid);

        if (!$this->oldEvent->comesFromDatabase()) {
            throw new NotFoundException('There is no event with this UID.', 1333292164);
        }

        /** @var \Tx_Seminars_Mapper_Event $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
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
     *
     * @return void
     */
    public function markAsIncomplete()
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
     * @return \Tx_Seminars_OldModel_Event the event object
     */
    protected function getOldEvent(): \Tx_Seminars_OldModel_Event
    {
        return $this->oldEvent;
    }

    /**
     * Returns the event this e-mail form refers to.
     *
     * @return \Tx_Seminars_Model_Event the event
     */
    protected function getEvent(): \Tx_Seminars_Model_Event
    {
        return $this->event;
    }

    /**
     * Returns all error messages set via setErrorMessage for the given field name.
     *
     * @param string $fieldName
     *        the field name for which the error message should be returned,
     *        must not be empty
     *
     * @return string HTML with error message for the field, will be empty if there's no error message for this field
     *
     * @throws \InvalidArgumentException
     */
    protected function getErrorMessage(string $fieldName): string
    {
        if ($fieldName === '') {
            throw new \InvalidArgumentException('$fieldName must not be empty.', 1333292174);
        }
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
     * @param string $fieldName the field name, must not be empty
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
     *
     * @return void
     */
    public function setPostData(array $postData)
    {
        $this->postData = $postData;
    }

    /**
     * Returns an entry from the stored POST data or an empty string if that
     * key is not set.
     *
     * @param string $key the key of the field to return, must not be empty
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
     * @param string $key the key of the field to check for, must not be empty
     *
     * @return bool TRUE if the stored POST data contains an entry, FALSE otherwise
     *
     * @throws \InvalidArgumentException
     */
    protected function hasPostData(string $key): bool
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1333292184);
        }

        return isset($this->postData[$key]);
    }

    /**
     * Sends an e-mail to the attendees to inform about the changed event status.
     *
     * @return void
     */
    private function sendEmailToAttendees()
    {
        $event = $this->getEvent();
        $organizer = $event->getFirstOrganizer();
        $sender = $event->getEmailSender();

        /** @var \Tx_Seminars_BagBuilder_Registration $registrationBagBuilder */
        $registrationBagBuilder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Registration::class);
        $registrationBagBuilder->limitToEvent($event->getUid());
        $registrations = $registrationBagBuilder->build();

        if (!$registrations->isEmpty()) {
            /** @var MailerFactory $mailerFactory */
            $mailerFactory = GeneralUtility::makeInstance(MailerFactory::class);
            $mailer = $mailerFactory->getMailer();

            /** @var \Tx_Seminars_Mapper_Registration $registrationMapper */
            $registrationMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
            /** @var \Tx_Seminars_OldModel_Registration $oldRegistration */
            foreach ($registrations as $oldRegistration) {
                /** @var \Tx_Seminars_Model_Registration $registration */
                $registration = $registrationMapper->find($oldRegistration->getUid());
                $user = $registration->getFrontEndUser();
                if (($user === null) || !$user->hasEmailAddress()) {
                    continue;
                }
                /** @var Mail $eMail */
                $eMail = GeneralUtility::makeInstance(Mail::class);
                $eMail->setSender($sender);
                $eMail->setReplyTo($organizer);
                $eMail->setSubject($this->getPostData('subject'));
                $eMail->addRecipient($registration->getFrontEndUser());
                $eMail->setMessage($this->createMessageBody($user, $organizer));

                $this->modifyEmailWithHook($registration, $eMail);

                $mailer->send($eMail);
            }

            /** @var FlashMessage $message */
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
     *
     * @return void
     */
    protected function addFlashMessage(FlashMessage $flashMessage)
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Calls all registered hooks for modifying the e-mail.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to which the e-mail refers
     * @param Mail $eMail
     *        the e-mail to be sent
     *
     * @return void
     */
    protected function modifyEmailWithHook(\Tx_Seminars_Model_Registration $registration, Mail $eMail)
    {
    }

    /**
     * Marks an event according to the status to set (if any) and commits the
     * change to the database.
     *
     * @return void
     */
    protected function setEventStatus()
    {
    }

    /**
     * Redirects to the list view.
     *
     * @return void
     */
    private function redirectToListView()
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
        /** @var Salutation $salutation */
        $salutation = GeneralUtility::makeInstance(Salutation::class);
        $eventDetails = $salutation->createIntroduction(
            '"%s"',
            $this->getOldEvent()
        );
        $introduction = sprintf($this->getLanguageService()->getLL($prefix . 'introduction'), $eventDetails);

        return '%salutation' . LF . LF . $introduction . LF
            . $this->getLanguageService()->getLL($prefix . 'messageBody');
    }

    /**
     * Creates the message body for the e-mail.
     *
     * @param \Tx_Seminars_Model_FrontEndUser $user the recipient of the e-mail
     * @param \Tx_Seminars_Model_Organizer $organizer
     *        the organizer which is selected as sender
     *
     * @return string the message with the salutation replaced by the user's
     *                name, will be empty if no message has been set in the POST
     *                data
     */
    private function createMessageBody(
        \Tx_Seminars_Model_FrontEndUser $user,
        \Tx_Seminars_Model_Organizer $organizer
    ): string {
        /** @var Salutation $salutation */
        $salutation = GeneralUtility::makeInstance(Salutation::class);
        $messageText = str_replace(
            '%salutation',
            $salutation->getSalutation($user),
            $this->getPostData('messageBody')
        );
        $messageFooter = $organizer->hasEMailFooter()
            ? LF . '-- ' . LF . $organizer->getEMailFooter() : '';

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
     * @param string $fieldName
     *        the field name to set the error message for, must be "messageBody"
     *        or "subject"
     * @param string $message the error message to set, may be empty
     *
     * @return void
     */
    protected function setErrorMessage(string $fieldName, string $message)
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
     * @return \Tx_Seminars_Interface_Hook_BackEndModule[]
     *         the hook objects, will be empty if no hooks have been set
     *
     * @throws \UnexpectedValueException
     *          if there are registered hook classes that do not implement the
     *          \Tx_Seminars_Interface_Hook_BackEndModule interface
     */
    protected function getHooks(): array
    {
        if (!$this->hooksHaveBeenRetrieved) {
            $hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'];
            if (\is_array($hookClasses)) {
                foreach ($hookClasses as $hookClass) {
                    $hookInstance = GeneralUtility::makeInstance($hookClass);
                    if (!($hookInstance instanceof \Tx_Seminars_Interface_Hook_BackEndModule)) {
                        throw new \UnexpectedValueException(
                            'The class ' . \get_class($hookInstance) . ' is used for the event list view hook, ' .
                            'but does not implement the \\Tx_Seminars_Interface_Hook_BackEndModule interface.',
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
        } catch (\TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException $e) {
            // no route registered, use the fallback logic to check for a module
            $uri = $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
        }

        return (string)$uri;
    }

    protected function getUriBuilder(): UriBuilder
    {
        return GeneralUtility::makeInstance(UriBuilder::class);
    }
}
