<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Oelib\Templating\TemplateRegistry;
use OliverKlee\Seminar\Email\Salutation;
use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\BagBuilder\EventBagBuilder;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Configuration\Traits\SharedPluginConfiguration;
use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Registration;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyOrganizer;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use Pelago\Emogrifier\CssInliner;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This utility class checks and creates registrations for seminars.
 *
 * This file does not include the locallang file in the BE because objectfromdb already does that.
 */
class RegistrationManager extends TemplateHelper
{
    use SharedPluginConfiguration;

    /**
     * @var int use text format for e-mails to attendees
     */
    public const SEND_TEXT_MAIL = 0;

    /**
     * @var int use HTML format for e-mails to attendees
     */
    public const SEND_HTML_MAIL = 1;

    /**
     * @var int use user-specific format for e-mails to attendees
     */
    public const SEND_USER_MAIL = 2;

    /**
     * faking $this->scriptRelPath so the locallang.xlf file is found
     *
     * @var string
     */
    public $scriptRelPath = 'Resources/Private/Language/locallang.xlf';

    /**
     * @var string the extension key
     */
    public $extKey = 'seminars';

    /**
     * @var static|null
     */
    private static $instance = null;

    /**
     * @var LegacyRegistration|null
     */
    private $registration = null;

    /**
     * @var Template|null
     */
    private $emailTemplate;

    /**
     * @var HookProvider|null
     */
    protected $registrationEmailHookProvider = null;

    /**
     * @var SingleViewLinkBuilder
     */
    private $linkBuilder = null;

    /**
     * It still is public due to the TemplateHelper base class. Nevertheless,
     * `getInstance` should be used so the Singleton property is retained.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @return static the current Singleton instance
     */
    public static function getInstance(): RegistrationManager
    {
        if (!self::$instance instanceof static) {
            /** @var static $instance */
            $instance = GeneralUtility::makeInstance(static::class);
            self::$instance = $instance;
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Checks whether is possible to register for a given event at all:
     * if a possibly logged-in user has not registered yet for this event,
     * if the event isn't canceled, full etc.
     *
     * If no user is logged in, it is just checked whether somebody could
     * register for this event.
     *
     * This function works even if no user is logged in.
     *
     * @param LegacyEvent $event an event for which we'll check if it is possible to register
     */
    public function canRegisterIfLoggedIn(LegacyEvent $event): bool
    {
        if ($event->getPriceOnRequest() || !$event->canSomebodyRegister()) {
            return false;
        }
        if (!FrontEndLoginManager::getInstance()->isLoggedIn()) {
            return true;
        }

        return $this->couldThisUserRegister($event);
    }

    /**
     * Checks whether is possible to register for a given seminar at all:
     * if a possibly logged-in user has not registered yet for this seminar, if the seminar isn't canceled, full etc.
     *
     * If no user is logged in, it is just checked whether somebody could register for this seminar.
     *
     * Returns a message if there is anything to complain about and an empty string otherwise.
     *
     * This function even works if no user is logged in.
     *
     * Note: This function does not check whether a logged-in front-end user fulfills all requirements for an event.
     *
     * @param LegacyEvent $event a seminar for which we'll check if it is possible to register
     *
     * @return string error message or empty string
     */
    public function canRegisterIfLoggedInMessage(LegacyEvent $event): string
    {
        $message = '';

        $isLoggedIn = FrontEndLoginManager::getInstance()->isLoggedIn();

        if ($isLoggedIn && $this->isUserBlocked($event)) {
            $message = $this->translate('message_userIsBlocked');
        } elseif ($isLoggedIn && !$this->couldThisUserRegister($event)) {
            $message = $this->translate('message_alreadyRegistered');
        } elseif (!$event->canSomebodyRegister()) {
            $message = $event->canSomebodyRegisterMessage();
        }

        return $message;
    }

    /**
     * Checks whether the current FE user (if any is logged in) could register
     * for the current event, not checking the event's vacancies yet.
     * So this function only checks whether the user is logged in and isn't blocked for the event's duration yet.
     *
     * Note: This function does not check whether a logged-in front-end user fulfills all requirements for an event.
     *
     * @param LegacyEvent $event a seminar for which we'll check if it is possible to register
     *
     * @return bool TRUE if the user could register for the given event, FALSE otherwise
     */
    private function couldThisUserRegister(LegacyEvent $event): bool
    {
        // A user can register either if the event allows multiple registrations
        // or the user isn't registered yet and isn't blocked either.
        return $event->allowsMultipleRegistrations()
            || (!$this->isUserRegistered($event) && !$this->isUserBlocked($event));
    }

    /**
     * Creates an HTML link to the registration or login page.
     *
     * @param DefaultController $plugin the pi1 object with configuration data
     * @param LegacyEvent $event the seminar to create the registration link for
     *
     * @return string the HTML tag, will be empty if the event needs no registration, nobody can register to this event or the
     *                currently logged in user is already registered to this event and the event does not allow multiple
     *                registrations by one user
     */
    public function getRegistrationLink(DefaultController $plugin, LegacyEvent $event): string
    {
        if (!$event->needsRegistration() || !$this->canRegisterIfLoggedIn($event)) {
            return '';
        }

        return $this->getLinkToRegistrationOrLoginPage($plugin, $event);
    }

    /**
     * Creates an HTML link to either the registration page (if a user is logged in) or the login page (if no user is logged in).
     *
     * Before you can call this function, you should make sure that the link makes sense (ie. the seminar still has vacancies, the
     * user has not registered for this seminar etc.).
     *
     * @param LegacyEvent $event a seminar for which we'll check if it is possible to register
     *
     * @return string HTML with the link
     */
    public function getLinkToRegistrationOrLoginPage(
        DefaultController $plugin,
        LegacyEvent $event
    ): string {
        return $this->getLinkToStandardRegistrationOrLoginPage(
            $plugin,
            $event,
            $this->getRegistrationLabel($plugin, $event)
        );
    }

    /**
     * Creates the label for the registration link.
     *
     * @param LegacyEvent $event a seminar to which the registration should relate
     *
     * @return string label for the registration link, will not be empty
     */
    private function getRegistrationLabel(TemplateHelper $plugin, LegacyEvent $event): string
    {
        if ($event->hasVacancies()) {
            if ($event->hasDate()) {
                $label = $plugin->translate('label_onlineRegistration');
            } else {
                $label = $plugin->translate('label_onlinePrebooking');
            }
        } elseif ($event->hasRegistrationQueue()) {
            $label = \sprintf(
                $plugin->translate('label_onlineRegistrationOnQueue'),
                $event->getAttendancesOnRegistrationQueue()
            );
        } else {
            $label = $plugin->translate('label_onlineRegistration');
        }

        return $label;
    }

    /**
     * Creates an HTML link to either the registration page (if a user is logged in) or the login page (if no user is logged in).
     *
     * This function only creates the link to the standard registration or login
     * page; it should not be used if the seminar has a separate details page.
     *
     * @param LegacyEvent $event a seminar for which we'll check if it is possible to register
     * @param string $label label for the link, must not be empty
     *
     * @return string HTML with the link
     */
    private function getLinkToStandardRegistrationOrLoginPage(
        DefaultController $plugin,
        LegacyEvent $event,
        string $label
    ): string {
        if (FrontEndLoginManager::getInstance()->isLoggedIn()) {
            // provides the registration link
            $result = $plugin->cObj->getTypoLink(
                $label,
                (string)$plugin->getConfValueInteger('registerPID'),
                ['tx_seminars_pi1[seminar]' => $event->getUid(), 'tx_seminars_pi1[action]' => 'register']
            );
        } else {
            // provides the login link
            $result = $plugin->getLoginLink($label, $plugin->getConfValueInteger('registerPID'), $event->getUid());
        }

        return $result;
    }

    /**
     * Creates an HTML link to the unregistration page (if a user is logged in).
     *
     * @param LegacyRegistration $registration a registration from which we'll get the UID
     *        for our GET parameters
     *
     * @return string HTML code with the link
     */
    public function getLinkToUnregistrationPage(
        TemplateHelper $plugin,
        LegacyRegistration $registration
    ): string {
        return $plugin->cObj->getTypoLink(
            $plugin->translate('label_onlineUnregistration'),
            (string)$plugin->getConfValueInteger('registerPID'),
            ['tx_seminars_pi1[registration]' => $registration->getUid(), 'tx_seminars_pi1[action]' => 'unregister']
        );
    }

    /**
     * Checks whether a seminar UID is valid, i.e., a non-deleted and non-hidden seminar with the given number exists.
     *
     * This function can be called even even if no seminar object exists.
     */
    public function existsSeminar(int $uid): bool
    {
        return LegacyEvent::fromUid($uid) instanceof LegacyEvent;
    }

    /**
     * Checks whether a seminar UID is valid, ie., a non-deleted and non-hidden seminar with the given number exists.
     *
     * This method can be called even if no seminar object exists.
     *
     * For invalid or inexistent UIDs, this method also send a 404 HTTP header.
     *
     * @param int $uid a given seminar UID
     *
     * @return string an empty string if the UID is valid, otherwise a localized error message
     */
    public function existsSeminarMessage(int $uid): string
    {
        if ($uid <= 0) {
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
            return $this->translate('message_missingSeminarNumber');
        }
        if (!$this->existsSeminar($uid)) {
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
            return $this->translate('message_wrongSeminarNumber');
        }

        return '';
    }

    /**
     * Checks whether a front-end user is already registered for this seminar.
     *
     * This method must not be called when no front-end user is logged in!
     *
     * @param LegacyEvent $event a seminar for which we'll check if it is possible to register
     *
     * @return bool TRUE if user is already registered, FALSE otherwise.
     */
    public function isUserRegistered(LegacyEvent $event): bool
    {
        return $event->isUserRegistered($this->getLoggedInFrontEndUserUid());
    }

    /**
     * Checks whether a certain user already is registered for this seminar.
     *
     * This method must not be called when no front-end user is logged in!
     *
     * @param LegacyEvent $event a seminar for which we'll check if it is possible to register
     *
     * @return string empty string if everything is OK, else a localized error message
     */
    public function isUserRegisteredMessage(LegacyEvent $event): string
    {
        return $event->isUserRegisteredMessage($this->getLoggedInFrontEndUserUid());
    }

    /**
     * Checks whether a front-end user is already blocked during the time for a given event by other booked events.
     *
     * For this, only events that forbid multiple registrations are checked.
     *
     * @param LegacyEvent $event a seminar for which we'll check whether the user already is blocked
     *        by an other event
     *
     * @return bool TRUE if user is blocked by another registration, FALSE otherwise
     */
    private function isUserBlocked(LegacyEvent $event): bool
    {
        return $event->isUserBlocked($this->getLoggedInFrontEndUserUid());
    }

    /**
     * Checks whether the data the user has just entered is okay for creating
     * a registration, e.g., mandatory fields are filled, number fields only
     * contain numbers, the number of seats to register is not too high etc.
     *
     * Please note that this function does not create a registration - it just checks.
     *
     * @param LegacyEvent $event the seminar object (that's the seminar we would like to register for)
     * @param array $registrationData associative array with the registration data the user has just entered
     *
     * @return bool TRUE if the data is okay, FALSE otherwise
     */
    public function canCreateRegistration(LegacyEvent $event, array $registrationData): bool
    {
        return $this->canRegisterSeats($event, (int)$registrationData['seats']);
    }

    /**
     * Checks whether a registration with a given number of seats could be
     * created, ie. an actual number is given and there are at least that many vacancies.
     *
     * @param LegacyEvent $event the seminar object (that's the seminar we would like to register for)
     * @param int $numberOfSeats the number of seats to check
     */
    public function canRegisterSeats(LegacyEvent $event, int $numberOfSeats): bool
    {
        // If no number of seats is given, ie. the user has not entered anything
        // or the field is not shown at all, assume 1.
        if ($numberOfSeats === 0) {
            $numberOfSeats = 1;
        }

        return $event->hasUnlimitedVacancies()
            || $event->hasRegistrationQueue() || $event->getVacancies() >= $numberOfSeats;
    }

    /**
     * Creates a registration to $this->registration, writes it to DB,
     * and notifies the organizer and the user (both via e-mail).
     *
     * The additional notifications will only be sent if this is enabled in the
     * TypoScript setup (which is the default).
     *
     * @param LegacyEvent $event the seminar we would like to register for
     * @param array $formData the raw registration data from the registration form
     * @param AbstractPlugin $plugin live plugin object
     *
     * @return Registration the created, saved registration
     */
    public function createRegistration(LegacyEvent $event, array $formData, AbstractPlugin $plugin): Registration
    {
        $this->registration = GeneralUtility::makeInstance(LegacyRegistration::class);
        $this->registration->setContentObject($plugin->cObj);
        $this->registration->setRegistrationData($event, $this->getLoggedInFrontEndUserUid(), $formData);
        $this->registration->commitToDatabase();
        $event->increaseNumberOfAssociatedRegistrationRecords();
        $event->calculateStatistics();
        $event->commitToDatabase();

        $event->getAttendances();

        return MapperRegistry::get(RegistrationMapper::class)->find($this->registration->getUid());
    }

    /**
     * Sends the e-mails for a new registration.
     */
    public function sendEmailsForNewRegistration(TemplateHelper $plugin): void
    {
        if ($this->registration->isOnRegistrationQueue()) {
            $this->notifyAttendee($this->registration, $plugin, 'confirmationOnRegistrationForQueue');
            $this->notifyOrganizers($this->registration, 'notificationOnRegistrationForQueue');
        } else {
            $this->notifyAttendee($this->registration, $plugin);
            $this->notifyOrganizers($this->registration);
        }

        if ($this->getSharedConfiguration()->getAsBoolean('sendAdditionalNotificationEmails')) {
            $this->sendAdditionalNotification($this->registration);
        }
    }

    /**
     * Fills $registration with $formData (as submitted via the registration form).
     *
     * This function sets all necessary registration data except for three
     * things:
     * - event
     * - user
     * - whether the registration is on the queue
     *
     * Note: This functions does not check whether registration is possible at all.
     *
     * @param Registration $registration the registration to fill, must already have an event assigned
     * @param array $formData the raw data submitted via the form, may be empty
     */
    protected function setRegistrationData(Registration $registration, array $formData): void
    {
        $event = $registration->getEvent();

        $seats = isset($formData['seats']) ? (int)$formData['seats'] : 1;
        if ($seats < 1) {
            $seats = 1;
        }
        $registration->setSeats($seats);

        $registeredThemselves = isset($formData['registered_themselves'])
            ? (bool)$formData['registered_themselves'] : false;
        $registration->setRegisteredThemselves($registeredThemselves);

        $availablePrices = $event->getAvailablePrices();
        if (isset($formData['price'], $availablePrices[$formData['price']])) {
            $priceCode = $formData['price'];
        } else {
            reset($availablePrices);
            $priceCode = key($availablePrices);
        }
        $registration->setPrice($priceCode);
        $totalPrice = $availablePrices[$priceCode] * $seats;
        $registration->setTotalPrice($totalPrice);

        $attendeesNames = isset($formData['attendees_names']) ? strip_tags($formData['attendees_names']) : '';
        $registration->setAttendeesNames($attendeesNames);

        $kids = isset($formData['kids']) ? max(0, (int)$formData['kids']) : 0;
        $registration->setKids($kids);

        $paymentMethod = null;
        if ($totalPrice > 0) {
            $availablePaymentMethods = $event->getPaymentMethods();
            if (!$availablePaymentMethods->isEmpty()) {
                if ($availablePaymentMethods->count() == 1) {
                    $paymentMethod = $availablePaymentMethods->first();
                } else {
                    $paymentMethodUid = isset($formData['method_of_payment'])
                        ? max(0, (int)$formData['method_of_payment']) : 0;
                    if (($paymentMethodUid > 0) && $availablePaymentMethods->hasUid($paymentMethodUid)) {
                        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class);
                        $paymentMethod = $mapper->find($paymentMethodUid);
                    }
                }
            }
        }
        $registration->setPaymentMethod($paymentMethod);

        $accountNumber = isset($formData['account_number'])
            ? strip_tags($this->unifyWhitespace($formData['account_number'])) : '';
        $registration->setAccountNumber($accountNumber);
        $bankCode = isset($formData['bank_code']) ? strip_tags($this->unifyWhitespace($formData['bank_code'])) : '';
        $registration->setBankCode($bankCode);
        $bankName = isset($formData['bank_name']) ? strip_tags($this->unifyWhitespace($formData['bank_name'])) : '';
        $registration->setBankName($bankName);
        $accountOwner = isset($formData['account_owner']) ? strip_tags(
            $this->unifyWhitespace(
                $formData['account_owner']
            )
        ) : '';
        $registration->setAccountOwner($accountOwner);

        $company = isset($formData['company']) ? strip_tags($formData['company']) : '';
        $registration->setCompany($company);

        $validGenderMale = (string)OelibFrontEndUser::GENDER_MALE;
        $validGenderFemale = (string)OelibFrontEndUser::GENDER_FEMALE;
        if (
            isset($formData['gender'])
            && (
                ($formData['gender'] === $validGenderMale) || ($formData['gender'] === $validGenderFemale)
            )
        ) {
            $gender = (int)$formData['gender'];
        } else {
            $gender = OelibFrontEndUser::GENDER_UNKNOWN;
        }
        $registration->setGender($gender);

        $name = isset($formData['name']) ? strip_tags($this->unifyWhitespace($formData['name'])) : '';
        $registration->setName($name);
        $address = isset($formData['address']) ? strip_tags($formData['address']) : '';
        $registration->setAddress($address);
        $zip = isset($formData['zip']) ? strip_tags($this->unifyWhitespace($formData['zip'])) : '';
        $registration->setZip($zip);
        $city = isset($formData['city']) ? strip_tags($this->unifyWhitespace($formData['city'])) : '';
        $registration->setCity($city);
        $country = isset($formData['country']) ? strip_tags($this->unifyWhitespace($formData['country'])) : '';
        $registration->setCountry($country);
    }

    /**
     * Replaces all non-space whitespace in $rawString with single regular spaces.
     *
     * @param string $rawString the string to unify, may be empty
     *
     * @return string the given string with all whitespace changed to regular spaces
     */
    private function unifyWhitespace(string $rawString): string
    {
        return preg_replace('/[\\r\\n\\t ]+/', ' ', $rawString);
    }

    /**
     * Removes the given registration (if it exists and if it belongs to the
     * currently logged-in FE user).
     *
     * @param int $uid the UID of the registration that should be removed
     */
    public function removeRegistration(int $uid, TemplateHelper $plugin): void
    {
        $this->registration = LegacyRegistration::fromUid($uid);
        if (!($this->registration instanceof LegacyRegistration)) {
            return;
        }

        $this->registration->setContentObject($plugin->cObj);
        if ($this->registration->getUser() !== $this->getLoggedInFrontEndUserUid()) {
            return;
        }

        $this->getConnectionForTable('tx_seminars_attendances')->update(
            'tx_seminars_attendances',
            ['hidden' => 1, 'tstamp' => $GLOBALS['SIM_EXEC_TIME']],
            ['uid' => $uid]
        );

        $this->notifyAttendee($this->registration, $plugin, 'confirmationOnUnregistration');
        $this->notifyOrganizers($this->registration, 'notificationOnUnregistration');

        $this->fillVacancies($plugin);
    }

    /**
     * Fills vacancies created through a unregistration with attendees from the registration queue.
     */
    private function fillVacancies(TemplateHelper $plugin): void
    {
        $seminar = $this->registration->getSeminarObject();
        if (!$seminar->hasVacancies()) {
            return;
        }

        $vacancies = $seminar->getVacancies();

        /** @var RegistrationBagBuilder $registrationBagBuilder */
        $registrationBagBuilder = GeneralUtility::makeInstance(RegistrationBagBuilder::class);
        $registrationBagBuilder->limitToEvent($seminar->getUid());
        $registrationBagBuilder->limitToOnQueue();
        $registrationBagBuilder->limitToSeatsAtMost($vacancies);

        $configuration = $this->getSharedConfiguration();
        /** @var LegacyRegistration $registration */
        foreach ($registrationBagBuilder->build() as $registration) {
            if ($vacancies <= 0) {
                break;
            }

            if ($registration->getSeats() <= $vacancies) {
                $this->getConnectionForTable('tx_seminars_attendances')->update(
                    'tx_seminars_attendances',
                    ['registration_queue' => 0],
                    ['uid' => $registration->getUid()]
                );
                $vacancies -= $registration->getSeats();

                $this->notifyAttendee($registration, $plugin, 'confirmationOnQueueUpdate');
                $this->notifyOrganizers($registration, 'notificationOnQueueUpdate');

                if ($configuration->getAsBoolean('sendAdditionalNotificationEmails')) {
                    $this->sendAdditionalNotification($registration);
                }
            }
        }
    }

    /**
     * Checks if the logged-in user fulfills all requirements for registration for the event $event.
     *
     * A front-end user needs to be logged in when this function is called.
     *
     * @param LegacyEvent $event the event to check
     *
     * @return bool TRUE if the user fulfills all requirements, FALSE otherwise
     */
    public function userFulfillsRequirements(LegacyEvent $event): bool
    {
        if (!$event->hasRequirements()) {
            return true;
        }
        return $this->getMissingRequiredTopics($event)->isEmpty();
    }

    /**
     * Returns the event topics the user still needs to register for in order to be able to register for $event.
     *
     * @param LegacyEvent $event the event to check
     *
     * @return EventBag the event topics which still need the user's registration, may be empty
     */
    public function getMissingRequiredTopics(LegacyEvent $event): EventBag
    {
        /** @var EventBagBuilder $builder */
        $builder = GeneralUtility::makeInstance(EventBagBuilder::class);
        $builder->limitToRequiredEventTopics($event->getTopicOrSelfUid());
        $builder->limitToTopicsWithoutRegistrationByUser($this->getLoggedInFrontEndUserUid());
        /** @var EventBag $bag */
        $bag = $builder->build();

        return $bag;
    }

    /**
     * Sends an e-mail to the attendee with a message concerning his/her registration or unregistration.
     *
     * @param LegacyRegistration $oldRegistration the registration for which the notification should be sent
     * @param string $helloSubjectPrefix
     *        prefix for the locallang key of the localized hello and subject
     *        string; allowed values are:
     *        - confirmation
     *        - confirmationOnUnregistration
     *        - confirmationOnRegistrationForQueue
     *        - confirmationOnQueueUpdate
     *        In the following the parameter is prefixed with "email_" and
     *        postfixed with "Hello" or "Subject".
     */
    public function notifyAttendee(
        LegacyRegistration $oldRegistration,
        TemplateHelper $plugin,
        string $helloSubjectPrefix = 'confirmation'
    ): void {
        if (!$this->getSharedConfiguration()->getAsBoolean('send' . ucfirst($helloSubjectPrefix))) {
            return;
        }

        /** @var LegacyEvent $event */
        $event = $oldRegistration->getSeminarObject();
        if (!$event->hasOrganizers()) {
            return;
        }

        if (!$oldRegistration->hasExistingFrontEndUser()) {
            return;
        }

        $user = $oldRegistration->getFrontEndUser();
        if (!$user instanceof FrontEndUser || !$user->hasEmailAddress()) {
            return;
        }

        /** @var MailMessage $eMailNotification */
        $eMailNotification = GeneralUtility::makeInstance(MailMessage::class);
        $eMailNotification->setTo($user->getEmailAddress(), $user->getName());
        $sender = $event->getEmailSender();
        $eMailNotification->setFrom($sender->getEmailAddress(), $sender->getName());
        $firstOrganizer = $event->getFirstOrganizer();
        $eMailNotification->setReplyTo($firstOrganizer->getEmailAddress(), $firstOrganizer->getName());
        $eMailNotification->setSubject(
            $this->translate('email_' . $helloSubjectPrefix . 'Subject') . ': ' . $event->getTitleAndDate('-')
        );

        $emailFormat = ConfigurationProxy::getInstance('seminars')->getAsInteger('eMailFormatForAttendees');
        if (
            $emailFormat === self::SEND_HTML_MAIL || ($emailFormat === self::SEND_USER_MAIL && $user->wantsHtmlEmail())
        ) {
            $eMailNotification->addPart(
                $this->buildEmailContent($oldRegistration, $plugin, $helloSubjectPrefix, true),
                'text/html'
            );
        }

        $eMailNotification->setBody($this->buildEmailContent($oldRegistration, $plugin, $helloSubjectPrefix));

        $mapper = MapperRegistry::get(RegistrationMapper::class);
        $registration = $mapper->find($oldRegistration->getUid());

        $this->addCalendarAttachment($eMailNotification, $registration);

        $this->getRegistrationEmailHookProvider()
            ->executeHook('modifyAttendeeEmail', $eMailNotification, $registration, $helloSubjectPrefix);

        $eMailNotification->send();
    }

    /**
     * Adds an iCalendar attachment with the event's most important data to $email.
     */
    private function addCalendarAttachment(MailMessage $email, Registration $registration): void
    {
        $event = $registration->getEvent();
        $timeZone = $event->getTimeZone() ?: $this->getConfValueString('defaultTimeZone');

        $content = "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:TYPO3 CMS\r\n" .
            "METHOD:PUBLISH\r\n" .
            "BEGIN:VEVENT\r\n" .
            'UID:' . uniqid('event/' . $event->getUid() . '/', true) . "\r\n" .
            'DTSTAMP:' . strftime('%Y%m%dT%H%M%S', $GLOBALS['SIM_EXEC_TIME']) . "\r\n" .
            'SUMMARY:' . $event->getTitle() . "\r\n" .
            'DESCRIPTION:' . $event->getSubtitle() . "\r\n" .
            'DTSTART' . $this->formatDateForWithZone($event->getBeginDateAsUnixTimeStamp(), $timeZone) . "\r\n";

        if ($event->hasEndDate()) {
            $content .= 'DTEND' . $this->formatDateForWithZone($event->getEndDateAsUnixTimeStamp(), $timeZone) . "\r\n";
        }
        if (!$event->getPlaces()->isEmpty()) {
            /** @var \Tx_Seminars_Model_Place $firstPlace */
            $firstPlace = $event->getPlaces()->first();
            $normalizedPlaceTitle = str_replace(
                ["\r\n", "\n"],
                ', ',
                trim($firstPlace->getTitle() . ', ' . $firstPlace->getAddress())
            );
            $content .= 'LOCATION:' . $normalizedPlaceTitle . "\r\n";
        }

        $organizer = $event->getFirstOrganizer();
        $content .= 'ORGANIZER;CN="' . addcslashes($organizer->getTitle(), '"') .
            '":mailto:' . $organizer->getEmailAddress() . "\r\n";
        $content .= "END:VEVENT\r\nEND:VCALENDAR";

        $email->addPart($content, 'text/calendar; charset="utf-8"; component="vevent"; method="publish"');
    }

    private function formatDateForWithZone(int $dateAsUnixTimeStamp, string $timeZone): string
    {
        return ';TZID=/' . $timeZone . ':' . strftime('%Y%m%dT%H%M%S', $dateAsUnixTimeStamp);
    }

    /**
     * Sends an e-mail to all organizers with a message about a registration or unregistration.
     *
     * @param LegacyRegistration $registration
     *        the registration for which the notification should be send
     * @param string $helloSubjectPrefix
     *        prefix for the locallang key of the localized hello and subject string, Allowed values are:
     *        - notification
     *        - notificationOnUnregistration
     *        - notificationOnRegistrationForQueue
     *        - notificationOnQueueUpdate
     *        In the following, the parameter is prefixed with "email_" and postfixed with "Hello" or "Subject".
     */
    public function notifyOrganizers(
        LegacyRegistration $registration,
        string $helloSubjectPrefix = 'notification'
    ): void {
        $configuration = $this->getSharedConfiguration();
        if (!$configuration->getAsBoolean('send' . ucfirst($helloSubjectPrefix))) {
            return;
        }
        if (!$registration->hasExistingFrontEndUser()) {
            return;
        }
        $event = $registration->getSeminarObject();
        if ($event->shouldMuteNotificationEmails() || !$event->hasOrganizers()) {
            return;
        }

        $organizers = $event->getOrganizerBag();
        /** @var MailMessage $eMailNotification */
        $eMailNotification = GeneralUtility::makeInstance(MailMessage::class);
        $sender = $event->getEmailSender();
        $eMailNotification->setFrom($sender->getEmailAddress(), $sender->getName());
        $firstOrganizer = $event->getFirstOrganizer();
        $eMailNotification->setReplyTo($firstOrganizer->getEmailAddress(), $firstOrganizer->getName());

        /** @var LegacyOrganizer $organizer */
        foreach ($organizers as $organizer) {
            $eMailNotification->addTo($organizer->getEmailAddress(), $organizer->getName());
        }

        $eMailNotification->setSubject(
            $this->translate('email_' . $helloSubjectPrefix . 'Subject') . ': ' . $registration->getTitle()
        );

        $template = $this->getInitializedEmailTemplate();
        $template->hideSubparts($this->getConfValueString('hideFieldsInNotificationMail'), 'field_wrapper');

        $template->setMarker('hello', $this->translate('email_' . $helloSubjectPrefix . 'Hello'));
        $template->setMarker('summary', $registration->getTitle());

        if ($this->hasConfValueString('showSeminarFieldsInNotificationMail')) {
            $template->setMarker(
                'seminardata',
                $event->dumpSeminarValues($this->getConfValueString('showSeminarFieldsInNotificationMail'))
            );
        } else {
            $template->hideSubparts('seminardata', 'field_wrapper');
        }

        if ($this->hasConfValueString('showFeUserFieldsInNotificationMail')) {
            $template->setMarker(
                'feuserdata',
                $registration->dumpUserValues($this->getConfValueString('showFeUserFieldsInNotificationMail'))
            );
        } else {
            $template->hideSubparts('feuserdata', 'field_wrapper');
        }

        if ($this->hasConfValueString('showAttendanceFieldsInNotificationMail')) {
            $template->setMarker(
                'attendancedata',
                $registration->dumpAttendanceValues($this->getConfValueString('showAttendanceFieldsInNotificationMail'))
            );
        } else {
            $template->hideSubparts('attendancedata', 'field_wrapper');
        }

        $eMailNotification->setBody($template->getSubpart('MAIL_NOTIFICATION'));

        $registrationMapper = MapperRegistry::get(RegistrationMapper::class);
        $registrationNew = $registrationMapper->find($registration->getUid());

        $this->getRegistrationEmailHookProvider()
            ->executeHook('modifyOrganizerEmail', $eMailNotification, $registrationNew, $helloSubjectPrefix);

        $eMailNotification->send();
    }

    /**
     * Checks if additional notifications to the organizers are necessary.
     * In that case, the notification e-mails will be sent to all organizers.
     *
     * Additional notifications mails will be sent out upon the following events:
     * - an event now has enough registrations
     * - an event is fully booked
     * If both things happen at the same time (minimum and maximum count of
     * attendees are the same), only the "event is full" message will be sent.
     *
     * @param LegacyRegistration $registration the registration
     *        for which the notification should be sent
     */
    public function sendAdditionalNotification(LegacyRegistration $registration): void
    {
        if ($registration->isOnRegistrationQueue()) {
            return;
        }
        $emailReason = $this->getReasonForNotification($registration);
        if ($emailReason === '') {
            return;
        }
        $event = $registration->getSeminarObject();
        if ($event->shouldMuteNotificationEmails()) {
            return;
        }

        /** @var MailMessage $eMail */
        $eMail = GeneralUtility::makeInstance(MailMessage::class);
        $sender = $event->getEmailSender();
        $eMail->setFrom($sender->getEmailAddress(), $sender->getName());
        $firstOrganizer = $event->getFirstOrganizer();
        $eMail->setReplyTo($firstOrganizer->getEmailAddress(), $firstOrganizer->getName());
        $eMail->setBody($this->getMessageForNotification($registration, $emailReason));
        $eMail->setSubject(
            sprintf(
                $this->translate('email_additionalNotification' . $emailReason . 'Subject'),
                $event->getUid(),
                $event->getTitleAndDate('-')
            )
        );

        /** @var LegacyOrganizer $organizer */
        foreach ($event->getOrganizerBag() as $organizer) {
            $eMail->addTo($organizer->getEmailAddress(), $organizer->getName());
        }

        $registrationMapper = MapperRegistry::get(RegistrationMapper::class);
        $registrationNew = $registrationMapper->find($registration->getUid());

        $this->getRegistrationEmailHookProvider()
            ->executeHook('modifyAdditionalEmail', $eMail, $registrationNew, $emailReason);

        $eMail->send();

        if ($event->hasEnoughAttendances() && !$event->haveOrganizersBeenNotifiedAboutEnoughAttendees()) {
            $event->setOrganizersBeenNotifiedAboutEnoughAttendees();
            $event->commitToDatabase();
        }
    }

    /**
     * Returns the topic for the additional notification e-mail.
     *
     * @param LegacyRegistration $registration the registration for which the notification
     *        should be sent
     *
     * @return string "EnoughRegistrations" if the event has enough attendances,
     *                "IsFull" if the event is fully booked, otherwise an empty string
     */
    private function getReasonForNotification(LegacyRegistration $registration): string
    {
        $event = $registration->getSeminarObject();
        if ($event->isFull()) {
            return 'IsFull';
        }

        $minimumNeededRegistrations = $event->getAttendancesMin();
        if (
            $minimumNeededRegistrations > 0
            && !$event->haveOrganizersBeenNotifiedAboutEnoughAttendees()
            && $event->hasEnoughAttendances()
        ) {
            $result = 'EnoughRegistrations';
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Returns the message for an e-mail according to the reason
     * $reasonForNotification provided.
     *
     * @param LegacyRegistration $registration the registration for which the notification should be sent
     * @param string $reasonForNotification reason for the notification, must be either "IsFull" or
     *        "EnoughRegistrations", must not be empty
     *
     * @return string the message, will not be empty
     */
    private function getMessageForNotification(
        LegacyRegistration $registration,
        string $reasonForNotification
    ): string {
        $localLanguageKey = 'email_additionalNotification' . $reasonForNotification;
        $template = $this->getInitializedEmailTemplate();

        $template->setMarker('message', $this->translate($localLanguageKey));
        $showSeminarFields = $this->getConfValueString('showSeminarFieldsInNotificationMail');
        if ($showSeminarFields != '') {
            $template->setMarker(
                'seminardata',
                $registration->getSeminarObject()->dumpSeminarValues($showSeminarFields)
            );
        } else {
            $template->hideSubparts('seminardata', 'field_wrapper');
        }

        return $template->getSubpart('MAIL_ADDITIONALNOTIFICATION');
    }

    /**
     * Reads and initializes the templates.
     *
     * If this has already been called for this instance, this function does nothing.
     *
     * This function will read the template file as it is set in the TypoScript setup.
     */
    private function getInitializedEmailTemplate(): Template
    {
        if ($this->emailTemplate instanceof Template) {
            return $this->emailTemplate;
        }

        $templateFileName = $this->getSharedConfiguration()->getAsString('templateFile');
        $template = TemplateRegistry::get($templateFileName);
        foreach ($template->getLabelMarkerNames() as $label) {
            $template->setMarker($label, $this->translate($label));
        }
        $this->emailTemplate = $template;

        return $template;
    }

    /**
     * Builds the e-mail body for an e-mail to the attendee.
     *
     * @param LegacyRegistration $registration
     *        the registration for which the notification should be send
     * @param string $helloSubjectPrefix
     *        prefix for the locallang key of the localized hello and subject
     *        string; allowed values are:
     *        - confirmation
     *        - confirmationOnUnregistration
     *        - confirmationOnRegistrationForQueue
     *        - confirmationOnQueueUpdate
     *        In the following, the parameter is prefixed with "email_" and postfixed with "Hello" or "Subject".
     * @param bool $useHtml whether to create HTML instead of plain text
     *
     * @return string the e-mail body for the attendee e-mail, will not be empty
     */
    private function buildEmailContent(
        LegacyRegistration $registration,
        TemplateHelper $plugin,
        string $helloSubjectPrefix,
        bool $useHtml = false
    ): string {
        if ($this->linkBuilder === null) {
            /** @var SingleViewLinkBuilder $linkBuilder */
            $linkBuilder = GeneralUtility::makeInstance(SingleViewLinkBuilder::class);
            $this->injectLinkBuilder($linkBuilder);
        }
        $this->linkBuilder->setPlugin($plugin);

        $wrapperPrefix = ($useHtml ? 'html_' : '') . 'field_wrapper';

        $template = $this->getInitializedEmailTemplate();
        $template->setMarker('html_mail_charset', 'utf-8');
        $template->hideSubparts($this->getConfValueString('hideFieldsInThankYouMail'), $wrapperPrefix);

        $this->setEmailIntroduction($helloSubjectPrefix, $registration);
        $event = $registration->getSeminarObject();
        $this->fillOrHideUnregistrationNotice($helloSubjectPrefix, $registration, $useHtml);

        $template->setMarker('uid', $event->getUid());

        $template->setMarker('registration_uid', $registration->getUid());

        if ($registration->hasSeats()) {
            $template->setMarker('seats', $registration->getSeats());
        } else {
            $template->hideSubparts('seats', $wrapperPrefix);
        }

        $this->fillOrHideAttendeeMarker($registration, $useHtml);

        if ($registration->hasLodgings()) {
            $template->setMarker('lodgings', $registration->getLodgings());
        } else {
            $template->hideSubparts('lodgings', $wrapperPrefix);
        }

        if ($registration->hasAccommodation()) {
            $template->setMarker('accommodation', $registration->getAccommodation());
        } else {
            $template->hideSubparts('accommodation', $wrapperPrefix);
        }

        if ($registration->hasFoods()) {
            $template->setMarker('foods', $registration->getFoods());
        } else {
            $template->hideSubparts('foods', $wrapperPrefix);
        }

        if ($registration->hasFood()) {
            $template->setMarker('food', $registration->getFood());
        } else {
            $template->hideSubparts('food', $wrapperPrefix);
        }

        if ($registration->hasCheckboxes()) {
            $template->setMarker('checkboxes', $registration->getCheckboxes());
        } else {
            $template->hideSubparts('checkboxes', $wrapperPrefix);
        }

        if ($registration->hasKids()) {
            $template->setMarker('kids', $registration->getNumberOfKids());
        } else {
            $template->hideSubparts('kids', $wrapperPrefix);
        }

        if ($event->hasAccreditationNumber()) {
            $template->setMarker('accreditation_number', $event->getAccreditationNumber());
        } else {
            $template->hideSubparts('accreditation_number', $wrapperPrefix);
        }

        if ($event->hasCreditPoints()) {
            $template->setMarker('credit_points', $event->getCreditPoints());
        } else {
            $template->hideSubparts('credit_points', $wrapperPrefix);
        }

        $template->setMarker('date', $event->getDate(($useHtml ? '&#8212;' : '-')));
        $template->setMarker('time', $event->getTime(($useHtml ? '&#8212;' : '-')));

        $this->fillPlacesMarker($event, $useHtml);

        if ($event->hasRoom()) {
            $template->setMarker('room', $event->getRoom());
        } else {
            $template->hideSubparts('room', $wrapperPrefix);
        }

        if ($registration->hasPrice()) {
            $template->setMarker('price', $registration->getPrice());
        } else {
            $template->hideSubparts('price', $wrapperPrefix);
        }

        if ($registration->hasTotalPrice()) {
            $template->setMarker('total_price', $registration->getTotalPrice());
        } else {
            $template->hideSubparts('total_price', $wrapperPrefix);
        }

        // We don't need to check $this->seminar->hasPaymentMethods() here as
        // method_of_payment can only be set (using the registration form) if
        // the event has at least one payment method.
        if ($registration->hasMethodOfPayment()) {
            $template->setMarker(
                'paymentmethod',
                $event->getSinglePaymentMethodPlain($registration->getMethodOfPaymentUid())
            );
        } else {
            $template->hideSubparts('paymentmethod', $wrapperPrefix);
        }

        $template->setMarker('billing_address', $registration->getBillingAddress());

        if ($registration->hasInterests()) {
            $template->setMarker('interests', $registration->getInterests());
        } else {
            $template->hideSubparts('interests', $wrapperPrefix);
        }

        $mapper = MapperRegistry::get(EventMapper::class);
        $newEvent = $mapper->find($event->getUid());
        $singleViewUrl = $this->linkBuilder->createAbsoluteUrlForEvent($newEvent);
        $template->setMarker(
            'url',
            $useHtml ? \htmlspecialchars($singleViewUrl, ENT_QUOTES | ENT_HTML5) : $singleViewUrl
        );

        if ($event->isPlanned()) {
            $template->unhideSubparts('planned_disclaimer', $wrapperPrefix);
        } else {
            $template->hideSubparts('planned_disclaimer', $wrapperPrefix);
        }

        $footers = $event->getOrganizersFooter();
        $template->setMarker('footer', !empty($footers) ? "\n-- \n" . $footers[0] : '');

        $registrationMapper = MapperRegistry::get(RegistrationMapper::class);
        $registrationNew = $registrationMapper->find($registration->getUid());

        $this->getRegistrationEmailHookProvider()->executeHook(
            $useHtml ? 'modifyAttendeeEmailBodyHtml' : 'modifyAttendeeEmailBodyPlainText',
            $template,
            $registrationNew,
            $helloSubjectPrefix
        );

        if ($useHtml) {
            $emailBody = $this->addCssToHtmlEmail($template->getSubpart('MAIL_THANKYOU_HTML'));
        } else {
            $emailBody = $template->getSubpart('MAIL_THANKYOU');
        }

        return $emailBody;
    }

    private function addCssToHtmlEmail(string $emailBody): string
    {
        // The CSS inlining uses a Composer-provided library and hence is a Composer-only feature.
        if (!$this->hasConfValueString('cssFileForAttendeeMail') || !\class_exists(CssInliner::class)) {
            return $emailBody;
        }

        $cssFile = $this->getConfValueString('cssFileForAttendeeMail');
        $absolutePath = GeneralUtility::getFileAbsFileName($cssFile);
        if (\is_readable($absolutePath)) {
            $css = \file_get_contents($absolutePath);
            $htmlWithCss = CssInliner::fromHtml($emailBody)->inlineCss($css)->render();
        } else {
            $htmlWithCss = $emailBody;
        }

        return $htmlWithCss;
    }

    /**
     * Checks whether the given event allows registration, as far as its date is concerned.
     *
     * @param LegacyEvent $event the event to check the registration for
     *
     * @return bool TRUE if the event allows registration by date, FALSE otherwise
     */
    public function allowsRegistrationByDate(LegacyEvent $event): bool
    {
        if ($event->hasDate()) {
            $result = !$event->isRegistrationDeadlineOver();
        } else {
            $result = $this->getSharedConfiguration()->getAsBoolean('allowRegistrationForEventsWithoutDate');
        }

        return $result && $this->registrationHasStarted($event);
    }

    /**
     * Checks whether the given event allows registration as far as the number of vacancies are concerned.
     *
     * @param LegacyEvent $event the event to check the registration for
     *
     * @return bool TRUE if the event has enough seats for registration, FALSE otherwise
     */
    public function allowsRegistrationBySeats(LegacyEvent $event): bool
    {
        return $event->hasRegistrationQueue() || $event->hasUnlimitedVacancies() || $event->hasVacancies();
    }

    /**
     * Checks whether the registration for this event has started.
     *
     * @param LegacyEvent $event the event to check the registration for
     *
     * @return bool TRUE if registration for this event already has started, FALSE otherwise
     */
    public function registrationHasStarted(LegacyEvent $event): bool
    {
        if (!$event->hasRegistrationBegin()) {
            return true;
        }

        return $GLOBALS['SIM_EXEC_TIME'] >= $event->getRegistrationBeginAsUnixTimestamp();
    }

    /**
     * Fills the attendees_names marker or hides it if necessary.
     *
     * @param LegacyRegistration $registration the current registration
     * @param bool $useHtml whether to create HTML instead of plain text
     */
    private function fillOrHideAttendeeMarker(LegacyRegistration $registration, bool $useHtml): void
    {
        $template = $this->getInitializedEmailTemplate();
        if (!$registration->hasAttendeesNames()) {
            $template->hideSubparts('attendees_names', ($useHtml ? 'html_' : '') . 'field_wrapper');
            return;
        }

        $template->setMarker('attendees_names', $registration->getEnumeratedAttendeeNames($useHtml));
    }

    /**
     * Sets the places marker for the attendee notification.
     *
     * @param LegacyEvent $event event of this registration
     * @param bool $useHtml whether to create HTML instead of plain text
     */
    private function fillPlacesMarker(LegacyEvent $event, bool $useHtml): void
    {
        $template = $this->getInitializedEmailTemplate();
        if (!$event->hasPlace()) {
            $template->setMarker('place', $this->translate('message_willBeAnnounced'));
            return;
        }

        $newline = $useHtml ? '<br />' : "\n";

        $formattedPlaces = [];
        /** @var \Tx_Seminars_Model_Place $place */
        foreach ($event->getPlaces() as $place) {
            $formattedPlaces[] = $this->formatPlace($place, $newline);
        }

        $template->setMarker('place', implode($newline . $newline, $formattedPlaces));
    }

    /**
     * Formats a place for the thank-you e-mail.
     *
     * @param \Tx_Seminars_Model_Place $place the place to format
     * @param string $newline the newline to use in formatting, must be either LF or '<br />'
     *
     * @return string the formatted place, will not be empty
     */
    private function formatPlace(\Tx_Seminars_Model_Place $place, string $newline): string
    {
        $address = preg_replace('/[\\n|\\r]+/', ' ', str_replace('<br />', ' ', strip_tags($place->getAddress())));

        $countryName = $place->hasCountry() ? ', ' . $place->getCountry()->getLocalShortName() : '';
        $zipAndCity = trim($place->getZip() . ' ' . $place->getCity());

        return $place->getTitle() . $newline . $address . $newline . $zipAndCity . $countryName;
    }

    /**
     * Sets the introductory part of the e-mail to the attendees.
     *
     * @param string $helloSubjectPrefix
     *        prefix for the locallang key of the localized hello and subject
     *        string, allowed values are:
     *          - confirmation
     *          - confirmationOnUnregistration
     *          - confirmationOnRegistrationForQueue
     *          - confirmationOnQueueUpdate
     *          In the following the parameter is prefixed with
     *          "email_" and postfixed with "Hello".
     * @param LegacyRegistration $registration the registration the introduction should be created for
     */
    private function setEmailIntroduction(
        string $helloSubjectPrefix,
        LegacyRegistration $registration
    ): void {
        $template = $this->getInitializedEmailTemplate();
        /** @var Salutation $salutation */
        $salutation = GeneralUtility::makeInstance(Salutation::class);
        $user = $registration->getFrontEndUser();
        if ($user instanceof FrontEndUser) {
            $salutationText = $salutation->getSalutation($user);
        } else {
            $salutationText = '';
        }
        $template->setMarker('salutation', $salutationText);

        $event = $registration->getSeminarObject();
        $introductionTemplate = $this->translate('email_' . $helloSubjectPrefix . 'Hello');
        $introduction = $salutation->createIntroduction($introductionTemplate, $event);

        if ($registration->hasTotalPrice()) {
            $introduction .= ' ' . sprintf($this->translate('email_price'), $registration->getTotalPrice());
        }

        $template->setMarker('introduction', \trim($introduction . '.'));
    }

    /**
     * Fills or hides the unregistration notice depending on the notification
     * e-mail type.
     *
     * @param string $helloSubjectPrefix
     *        prefix for the locallang key of the localized hello and subject
     *        string, allowed values are:
     *          - confirmation
     *          - confirmationOnUnregistration
     *          - confirmationOnRegistrationForQueue
     *          - confirmationOnQueueUpdate
     * @param LegacyRegistration $registration the registration the introduction should be created for
     * @param bool $useHtml whether to send HTML instead of plain text e-mail
     */
    private function fillOrHideUnregistrationNotice(
        string $helloSubjectPrefix,
        LegacyRegistration $registration,
        bool $useHtml
    ): void {
        $event = $registration->getSeminarObject();
        $template = $this->getInitializedEmailTemplate();
        if (($helloSubjectPrefix === 'confirmationOnUnregistration') || !$event->isUnregistrationPossible()) {
            $template->hideSubparts('unregistration_notice', ($useHtml ? 'html_' : '') . 'field_wrapper');
            return;
        }

        $template->setMarker('unregistration_notice', $this->getUnregistrationNotice($event));
    }

    /**
     * Returns the unregistration notice for the notification mails.
     *
     * @param LegacyEvent $event the event to get the unregistration deadline from
     *
     * @return string the unregistration notice with the event's unregistration deadline, will not be empty
     */
    protected function getUnregistrationNotice(LegacyEvent $event): string
    {
        $unregistrationDeadline = $event->getUnregistrationDeadlineFromModelAndConfiguration();

        return \sprintf(
            $this->translate('email_unregistrationNotice'),
            \strftime($this->getDateFormat(), $unregistrationDeadline)
        );
    }

    /**
     * Returns the (old) registration created via createRegistration.
     */
    public function getRegistration(): ?LegacyRegistration
    {
        return $this->registration;
    }

    /**
     * Gets the hook provider for the registration emails.
     *
     * @return HookProvider
     */
    protected function getRegistrationEmailHookProvider(): HookProvider
    {
        if (!$this->registrationEmailHookProvider instanceof HookProvider) {
            $this->registrationEmailHookProvider =
                GeneralUtility::makeInstance(HookProvider::class, RegistrationEmail::class);
        }

        return $this->registrationEmailHookProvider;
    }

    public function injectLinkBuilder(SingleViewLinkBuilder $linkBuilder): void
    {
        $this->linkBuilder = $linkBuilder;
    }

    /**
     * Returns the UID of the logged-in front-end user (or 0 if no user is logged in).
     */
    protected function getLoggedInFrontEndUserUid(): int
    {
        $loginManager = FrontEndLoginManager::getInstance();
        return $loginManager->isLoggedIn() ? $loginManager->getLoggedInUser(
            FrontEndUserMapper::class
        )->getUid() : 0;
    }

    protected function getRegistrationMapper(): RegistrationMapper
    {
        return MapperRegistry::get(RegistrationMapper::class);
    }

    /**
     * Returns the prices that are actually available for the given user, depending on whether automatic prices are
     * enabled using the plugin.tx_seminars.automaticSpecialPriceForSubsequentRegistrationsBySameUser setting.
     *
     * @return string[][] the available prices as a reset array of arrays with the keys "caption" (for the title)
     *                    and "value (for the price code), might be empty
     */
    public function getPricesAvailableForUser(
        LegacyEvent $event,
        FrontEndUser $user
    ): array {
        $prices = $event->getAvailablePrices();
        if (!$this->getConfValueBoolean('automaticSpecialPriceForSubsequentRegistrationsBySameUser')) {
            return $prices;
        }

        $useSpecialPrice = $event->hasPriceSpecial() && $this->getRegistrationMapper()->countByFrontEndUser($user) > 0;

        if ($useSpecialPrice) {
            unset($prices['regular'], $prices['regular_early'], $prices['regular_board']);
        } else {
            unset($prices['special'], $prices['special_early'], $prices['special_board']);
        }

        return $prices;
    }

    private function getConnectionForTable(string $table): Connection
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $connectionPool->getConnectionForTable($table);
    }
}
