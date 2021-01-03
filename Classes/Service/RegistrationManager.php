<?php

declare(strict_types=1);

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminar\Email\Salutation;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;
use OliverKlee\Seminars\Hooks\RegistrationEmailHookInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This utility class checks and creates registrations for seminars.
 *
 * This file does not include the locallang file in the BE because objectfromdb already does that.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Service_RegistrationManager extends TemplateHelper
{
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
     * @var \Tx_Seminars_Service_RegistrationManager
     */
    private static $instance = null;

    /**
     * @var \Tx_Seminars_OldModel_Registration|null
     */
    private $registration = null;

    /**
     * @var bool whether we have already initialized the templates
     *              (which is done lazily)
     */
    private $isTemplateInitialized = false;

    /**
     * hook objects for this class
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
     * @var HookProvider|null
     */
    protected $registrationEmailHookProvider = null;

    /**
     * @var int use text format for e-mails to attendees
     */
    const SEND_TEXT_MAIL = 0;

    /**
     * @var int use HTML format for e-mails to attendees
     */
    const SEND_HTML_MAIL = 1;

    /**
     * @var int use user-specific format for e-mails to attendees
     */
    const SEND_USER_MAIL = 2;

    /**
     * a link builder instance
     *
     * @var \Tx_Seminars_Service_SingleViewLinkBuilder
     */
    private $linkBuilder = null;

    /**
     * The constructor.
     *
     * It still is public due to the templatehelper base class. Nevertheless,
     * getInstance should be used so the Singleton property is retained.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Returns the instance of this class.
     *
     * @return \Tx_Seminars_Service_RegistrationManager the current Singleton instance
     */
    public static function getInstance(): \Tx_Seminars_Service_RegistrationManager
    {
        if (self::$instance === null) {
            self::$instance = GeneralUtility::makeInstance(static::class);
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     *
     * @return void
     */
    public static function purgeInstance()
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
     * @param \Tx_Seminars_OldModel_Event $event
     *        am event for which we'll check if it is possible to register
     *
     * @return bool TRUE if it is okay to register, FALSE otherwise
     */
    public function canRegisterIfLoggedIn(\Tx_Seminars_OldModel_Event $event): bool
    {
        if ($event->getPriceOnRequest() || !$event->canSomebodyRegister()) {
            return false;
        }
        if (!FrontEndLoginManager::getInstance()->isLoggedIn()) {
            return true;
        }

        $canRegister = $this->couldThisUserRegister($event);

        /** @var \Tx_Seminars_Model_FrontEndUser $user */
        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
        foreach ($this->getHooks() as $hook) {
            if (method_exists($hook, 'canRegisterForSeminar')) {
                $canRegister = $canRegister && $hook->canRegisterForSeminar($event, $user);
            }
        }

        return $canRegister;
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
     * @param \Tx_Seminars_OldModel_Event $event a seminar for which we'll check if it is possible to register
     *
     * @return string error message or empty string
     */
    public function canRegisterIfLoggedInMessage(\Tx_Seminars_OldModel_Event $event): string
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

        if ($isLoggedIn && $message === '') {
            /** @var \Tx_Seminars_Model_FrontEndUser $user */
            $user = FrontEndLoginManager::getInstance()
                ->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
            foreach ($this->getHooks() as $hook) {
                if (method_exists($hook, 'canRegisterForSeminarMessage')) {
                    $message = (string)$hook->canRegisterForSeminarMessage($event, $user);
                    if ($message !== '') {
                        break;
                    }
                }
            }
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
     * @param \Tx_Seminars_OldModel_Event $event a seminar for which we'll check if it is possible to register
     *
     * @return bool TRUE if the user could register for the given event, FALSE otherwise
     */
    private function couldThisUserRegister(\Tx_Seminars_OldModel_Event $event): bool
    {
        // A user can register either if the event allows multiple registrations
        // or the user isn't registered yet and isn't blocked either.
        return $event->allowsMultipleRegistrations()
            || (!$this->isUserRegistered($event) && !$this->isUserBlocked($event));
    }

    /**
     * Creates an HTML link to the registration or login page.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $plugin the pi1 object with configuration data
     * @param \Tx_Seminars_OldModel_Event $event the seminar to create the registration link for
     *
     * @return string the HTML tag, will be empty if the event needs no registration, nobody can register to this event or the
     *                currently logged in user is already registered to this event and the event does not allow multiple
     *                registrations by one user
     */
    public function getRegistrationLink(
        \Tx_Seminars_FrontEnd_DefaultController $plugin,
        \Tx_Seminars_OldModel_Event $event
    ): string {
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
     * @param \Tx_Seminars_FrontEnd_DefaultController $plugin
     * @param \Tx_Seminars_OldModel_Event $event a seminar for which we'll check if it is possible to register
     *
     * @return string HTML code with the link
     */
    public function getLinkToRegistrationOrLoginPage(
        \Tx_Seminars_FrontEnd_DefaultController $plugin,
        \Tx_Seminars_OldModel_Event $event
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
     * @param \Tx_Seminars_OldModel_Event $event a seminar to which the registration should relate
     *
     * @return string label for the registration link, will not be empty
     */
    private function getRegistrationLabel(TemplateHelper $plugin, \Tx_Seminars_OldModel_Event $event): string
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
     * @param \Tx_Seminars_FrontEnd_DefaultController $plugin
     * @param \Tx_Seminars_OldModel_Event $event a seminar for which we'll check if it is possible to register
     * @param string $label label for the link, will not be empty
     *
     * @return string HTML code with the link
     */
    private function getLinkToStandardRegistrationOrLoginPage(
        \Tx_Seminars_FrontEnd_DefaultController $plugin,
        \Tx_Seminars_OldModel_Event $event,
        string $label
    ): string {
        if (FrontEndLoginManager::getInstance()->isLoggedIn()) {
            // provides the registration link
            $result = $plugin->cObj->getTypoLink(
                $label,
                $plugin->getConfValueInteger('registerPID'),
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
     * @param \Tx_Seminars_OldModel_Registration $registration a registration from which we'll get the UID for our GET parameters
     *
     * @return string HTML code with the link
     */
    public function getLinkToUnregistrationPage(
        TemplateHelper $plugin,
        \Tx_Seminars_OldModel_Registration $registration
    ): string {
        return $plugin->cObj->getTypoLink(
            $plugin->translate('label_onlineUnregistration'),
            $plugin->getConfValueInteger('registerPID'),
            ['tx_seminars_pi1[registration]' => $registration->getUid(), 'tx_seminars_pi1[action]' => 'unregister']
        );
    }

    /**
     * Checks whether a seminar UID is valid, ie., a non-deleted and non-hidden seminar with the given number exists.
     *
     * This function can be called even if no seminar object exists.
     *
     * @param int $uid
     *
     * @return bool TRUE the UID is valid, FALSE otherwise
     */
    public function existsSeminar(int $uid): bool
    {
        return \Tx_Seminars_OldModel_Event::fromUid($uid) instanceof \Tx_Seminars_OldModel_Event;
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
            \Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
            return $this->translate('message_missingSeminarNumber');
        }
        if (!$this->existsSeminar($uid)) {
            \Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
            return $this->translate('message_wrongSeminarNumber');
        }

        return '';
    }

    /**
     * Checks whether a front-end user is already registered for this seminar.
     *
     * This method must not be called when no front-end user is logged in!
     *
     * @param \Tx_Seminars_OldModel_Event $event a seminar for which we'll check if it is possible to register
     *
     * @return bool TRUE if user is already registered, FALSE otherwise.
     */
    public function isUserRegistered(\Tx_Seminars_OldModel_Event $event): bool
    {
        return $event->isUserRegistered($this->getLoggedInFrontEndUserUid());
    }

    /**
     * Checks whether a certain user already is registered for this seminar.
     *
     * This method must not be called when no front-end user is logged in!
     *
     * @param \Tx_Seminars_OldModel_Event $event a seminar for which we'll check if it is possible to register
     *
     * @return string empty string if everything is OK, else a localized error message
     */
    public function isUserRegisteredMessage(\Tx_Seminars_OldModel_Event $event): string
    {
        return $event->isUserRegisteredMessage($this->getLoggedInFrontEndUserUid());
    }

    /**
     * Checks whether a front-end user is already blocked during the time for a given event by other booked events.
     *
     * For this, only events that forbid multiple registrations are checked.
     *
     * @param \Tx_Seminars_OldModel_Event $event a seminar for which we'll check whether the user already is blocked by an other seminars
     *
     * @return bool TRUE if user is blocked by another registration, FALSE otherwise
     */
    private function isUserBlocked(\Tx_Seminars_OldModel_Event $event): bool
    {
        return $event->isUserBlocked($this->getLoggedInFrontEndUserUid());
    }

    /**
     * Checks whether the data the user has just entered is okay for creating
     * a registration, e.g. mandatory fields are filled, number fields only
     * contain numbers, the number of seats to register is not too high etc.
     *
     * Please note that this function does not create a registration - it just checks.
     *
     * @param \Tx_Seminars_OldModel_Event $event the seminar object (that's the seminar we would like to register for)
     * @param array $registrationData associative array with the registration data the user has just entered
     *
     * @return bool TRUE if the data is okay, FALSE otherwise
     */
    public function canCreateRegistration(\Tx_Seminars_OldModel_Event $event, array $registrationData): bool
    {
        return $this->canRegisterSeats($event, (int)$registrationData['seats']);
    }

    /**
     * Checks whether a registration with a given number of seats could be
     * created, ie. an actual number is given and there are at least that many vacancies.
     *
     * @param \Tx_Seminars_OldModel_Event $event the seminar object (that's the seminar we would like to register for)
     * @param int $numberOfSeats the number of seats to check
     *
     * @return bool
     */
    public function canRegisterSeats(\Tx_Seminars_OldModel_Event $event, int $numberOfSeats): bool
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
     * @param \Tx_Seminars_OldModel_Event $event the seminar we would like to register for
     * @param array $formData the raw registration data from the registration form
     * @param AbstractPlugin $plugin live plugin object
     *
     * @return \Tx_Seminars_Model_Registration the created, saved registration
     */
    public function createRegistration(
        \Tx_Seminars_OldModel_Event $event,
        array $formData,
        AbstractPlugin $plugin
    ): \Tx_Seminars_Model_Registration {
        $this->registration = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_Registration::class);
        $this->registration->setContentObject($plugin->cObj);
        $this->registration->setRegistrationData($event, $this->getLoggedInFrontEndUserUid(), $formData);
        $this->registration->commitToDatabase();
        $event->increaseNumberOfAssociatedRegistrationRecords();
        $event->calculateStatistics();
        $event->commitToDatabase();

        $event->getAttendances();

        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
        foreach ($this->getHooks() as $hook) {
            if (method_exists($hook, 'seminarRegistrationCreated')) {
                $hook->seminarRegistrationCreated($this->registration, $user);
            }
        }

        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = $mapper->find($this->registration->getUid());

        return $registration;
    }

    /**
     * Sends the e-mails for a new registration.
     *
     * @return void
     */
    public function sendEmailsForNewRegistration(TemplateHelper $plugin)
    {
        if ($this->registration->isOnRegistrationQueue()) {
            $this->notifyAttendee($this->registration, $plugin, 'confirmationOnRegistrationForQueue');
            $this->notifyOrganizers($this->registration, 'notificationOnRegistrationForQueue');
        } else {
            $this->notifyAttendee($this->registration, $plugin);
            $this->notifyOrganizers($this->registration);
        }

        if ($this->getConfValueBoolean('sendAdditionalNotificationEmails')) {
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
     * @param \Tx_Seminars_Model_Registration $registration the registration to fill, must already have an event assigned
     * @param array $formData the raw data submitted via the form, may be empty
     *
     * @return void
     */
    protected function setRegistrationData(\Tx_Seminars_Model_Registration $registration, array $formData)
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
                        /** @var \Tx_Seminars_Mapper_PaymentMethod $mapper */
                        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class);
                        /** @var \Tx_Seminars_Model_PaymentMethod $paymentMethod */
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

        $validGenderMale = (string)FrontEndUser::GENDER_MALE;
        $validGenderFemale = (string)FrontEndUser::GENDER_FEMALE;
        if (
            isset($formData['gender'])
            && (
                ($formData['gender'] === $validGenderMale) || ($formData['gender'] === $validGenderFemale)
            )
        ) {
            $gender = (int)$formData['gender'];
        } else {
            $gender = FrontEndUser::GENDER_UNKNOWN;
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
     * @return string $rawString with all whitespace changed to regular spaces
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
     *
     * @return void
     */
    public function removeRegistration(int $uid, TemplateHelper $plugin)
    {
        $this->registration = \Tx_Seminars_OldModel_Registration::fromUid($uid);
        if (!($this->registration instanceof \Tx_Seminars_OldModel_Registration)) {
            return;
        }

        $this->registration->setContentObject($plugin->cObj);
        if ($this->registration->getUser() !== $this->getLoggedInFrontEndUserUid()) {
            return;
        }

        /** @var \Tx_Seminars_Model_FrontEndUser $user */
        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
        foreach ($this->getHooks() as $hook) {
            if (method_exists($hook, 'seminarRegistrationRemoved')) {
                $hook->seminarRegistrationRemoved($this->registration, $user);
            }
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
     *
     * @return void
     */
    private function fillVacancies(TemplateHelper $plugin)
    {
        $seminar = $this->registration->getSeminarObject();
        if (!$seminar->hasVacancies()) {
            return;
        }

        $vacancies = $seminar->getVacancies();

        /** @var \Tx_Seminars_BagBuilder_Registration $registrationBagBuilder */
        $registrationBagBuilder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Registration::class);
        $registrationBagBuilder->limitToEvent($seminar->getUid());
        $registrationBagBuilder->limitToOnQueue();
        $registrationBagBuilder->limitToSeatsAtMost($vacancies);

        /** @var \Tx_Seminars_OldModel_Registration $registration */
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

                $user = $registration->getFrontEndUser();
                if ($user instanceof \Tx_Seminars_Model_FrontEndUser) {
                    foreach ($this->getHooks() as $hook) {
                        if (method_exists($hook, 'seminarRegistrationMovedFromQueue')) {
                            $hook->seminarRegistrationMovedFromQueue($registration, $user);
                        }
                    }
                }

                $this->notifyAttendee($registration, $plugin, 'confirmationOnQueueUpdate');
                $this->notifyOrganizers($registration, 'notificationOnQueueUpdate');

                if ($this->getConfValueBoolean('sendAdditionalNotificationEmails')) {
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
     * @param \Tx_Seminars_OldModel_Event $event the event to check
     *
     * @return bool TRUE if the user fulfills all requirements, FALSE otherwise
     */
    public function userFulfillsRequirements(\Tx_Seminars_OldModel_Event $event): bool
    {
        if (!$event->hasRequirements()) {
            return true;
        }
        return $this->getMissingRequiredTopics($event)->isEmpty();
    }

    /**
     * Returns the event topics the user still needs to register for in order to be able to register for $event.
     *
     * @param \Tx_Seminars_OldModel_Event $event the event to check
     *
     * @return \Tx_Seminars_Bag_Event the event topics which still need the user's registration, may be empty
     */
    public function getMissingRequiredTopics(\Tx_Seminars_OldModel_Event $event): \Tx_Seminars_Bag_Event
    {
        /** @var \Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Event::class);
        $builder->limitToRequiredEventTopics($event->getTopicOrSelfUid());
        $builder->limitToTopicsWithoutRegistrationByUser($this->getLoggedInFrontEndUserUid());
        /** @var \Tx_Seminars_Bag_Event $bag */
        $bag = $builder->build();

        return $bag;
    }

    /**
     * Sends an e-mail to the attendee with a message concerning his/her registration or unregistration.
     *
     * @param \Tx_Seminars_OldModel_Registration $oldRegistration the registration for which the notification should be sent
     * @param string $helloSubjectPrefix
     *        prefix for the locallang key of the localized hello and subject
     *        string; allowed values are:
     *        - confirmation
     *        - confirmationOnUnregistration
     *        - confirmationOnRegistrationForQueue
     *        - confirmationOnQueueUpdate
     *        In the following the parameter is prefixed with "email_" and
     *        postfixed with "Hello" or "Subject".
     *
     * @return void
     */
    public function notifyAttendee(
        \Tx_Seminars_OldModel_Registration $oldRegistration,
        TemplateHelper $plugin,
        string $helloSubjectPrefix = 'confirmation'
    ) {
        if (!$this->getConfValueBoolean('send' . ucfirst($helloSubjectPrefix))) {
            return;
        }

        /** @var \Tx_Seminars_OldModel_Event $event */
        $event = $oldRegistration->getSeminarObject();
        if (!$event->hasOrganizers()) {
            return;
        }

        if (!$oldRegistration->hasExistingFrontEndUser()) {
            return;
        }

        $user = $oldRegistration->getFrontEndUser();
        if (!$user instanceof \Tx_Seminars_Model_FrontEndUser || !$user->hasEmailAddress()) {
            return;
        }

        /** @var \Tx_Oelib_Mail $eMailNotification */
        $eMailNotification = GeneralUtility::makeInstance(\Tx_Oelib_Mail::class);
        $eMailNotification->addRecipient($user);
        $eMailNotification->setSender($event->getEmailSender());
        $eMailNotification->setReplyTo($event->getFirstOrganizer());
        $eMailNotification->setSubject(
            $this->translate('email_' . $helloSubjectPrefix . 'Subject') . ': ' . $event->getTitleAndDate('-')
        );

        $this->initializeTemplate();

        $emailFormat = \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->getAsInteger('eMailFormatForAttendees');
        if (
            $emailFormat === self::SEND_HTML_MAIL || ($emailFormat === self::SEND_USER_MAIL && $user->wantsHtmlEmail())
        ) {
            $eMailNotification->setCssFile($this->getConfValueString('cssFileForAttendeeMail'));
            $eMailNotification->setHTMLMessage(
                $this->buildEmailContent(
                    $oldRegistration,
                    $plugin,
                    $helloSubjectPrefix,
                    true
                )
            );
        }

        $eMailNotification->setMessage($this->buildEmailContent($oldRegistration, $plugin, $helloSubjectPrefix));

        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = $mapper->find($oldRegistration->getUid());

        $this->addCalendarAttachment($eMailNotification, $registration);

        $this->getRegistrationEmailHookProvider()
            ->executeHook('modifyAttendeeEmail', $eMailNotification, $registration, $helloSubjectPrefix);
        $this->callPostProcessAttendeeEmailHooks($eMailNotification, $registration);

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->getMailer()->send($eMailNotification);
    }

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4;
     *      use `->getRegistrationEmailHookProvider()->executeHook('modifyAttendeeEmail')` instead
     */
    protected function callPostProcessAttendeeEmailHooks(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_Model_Registration $registration
    ) {
        foreach ($this->getHooks() as $hook) {
            // The RegistrationEmailHookInterface should be preferred against the old
            // modifyThankYouEmail variant.
            if ($hook instanceof RegistrationEmailHookInterface) {
                $hook->postProcessAttendeeEmail($mail, $registration);
            } elseif (method_exists($hook, 'modifyThankYouEmail')) {
                GeneralUtility::deprecationLog(
                    \get_class($hook) . '::modifyThankYouEmail() - since seminars 3.0,'
                    . ' will be removed in seminars 4.0'
                );
                $hook->modifyThankYouEmail($mail, $registration);
            }
        }
    }

    /**
     * Adds an iCalendar attachment with the event's most important data to $email.
     *
     * @param \Tx_Oelib_Mail $email
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return void
     */
    private function addCalendarAttachment(\Tx_Oelib_Mail $email, \Tx_Seminars_Model_Registration $registration)
    {
        $event = $registration->getEvent();
        $timeZone = $event->getTimeZone() ?: $this->getConfValueString('defaultTimeZone');

        /** @var \Tx_Oelib_Attachment $calendarEntry */
        $calendarEntry = GeneralUtility::makeInstance(\Tx_Oelib_Attachment::class);
        $calendarEntry->setContentType('text/calendar; charset="utf-8"; component="vevent"; method="publish"');
        $calendarEntry->setFileName('event.ics');
        $content = 'BEGIN:VCALENDAR' . CRLF .
            'VERSION:2.0' . CRLF .
            'PRODID:TYPO3 CMS' . CRLF .
            'METHOD:PUBLISH' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'UID:' . uniqid('event/' . $event->getUid() . '/', true) . CRLF .
            'DTSTAMP:' . strftime('%Y%m%dT%H%M%S', $GLOBALS['SIM_EXEC_TIME']) . CRLF .
            'SUMMARY:' . $event->getTitle() . CRLF .
            'DESCRIPTION:' . $event->getSubtitle() . CRLF .
            'DTSTART' . $this->formatDateForWithZone($event->getBeginDateAsUnixTimeStamp(), $timeZone) . CRLF;

        if ($event->hasEndDate()) {
            $content .= 'DTEND' . $this->formatDateForWithZone($event->getEndDateAsUnixTimeStamp(), $timeZone) . CRLF;
        }
        if (!$event->getPlaces()->isEmpty()) {
            /** @var \Tx_Seminars_Model_Place $firstPlace */
            $firstPlace = $event->getPlaces()->first();
            $normalizedPlaceTitle = str_replace(
                [CRLF, LF],
                ', ',
                trim($firstPlace->getTitle() . ', ' . $firstPlace->getAddress())
            );
            $content .= 'LOCATION:' . $normalizedPlaceTitle . CRLF;
        }

        $organizer = $event->getFirstOrganizer();
        $content .= 'ORGANIZER;CN="' . addcslashes($organizer->getTitle(), '"') .
            '":mailto:' . $organizer->getEMailAddress() . CRLF;
        $content .= 'END:VEVENT' . CRLF .
            'END:VCALENDAR';
        $calendarEntry->setContent($content);

        $email->addAttachment($calendarEntry);
    }

    /**
     * @param int $dateAsUnixTimeStamp
     * @param string $timeZone
     *
     * @return string
     */
    private function formatDateForWithZone(int $dateAsUnixTimeStamp, string $timeZone): string
    {
        return ';TZID=/' . $timeZone . ':' . strftime('%Y%m%dT%H%M%S', $dateAsUnixTimeStamp);
    }

    /**
     * Sends an e-mail to all organizers with a message about a registration or unregistration.
     *
     * @param \Tx_Seminars_OldModel_Registration $registration
     *        the registration for which the notification should be send
     * @param string $helloSubjectPrefix
     *        prefix for the locallang key of the localized hello and subject string, Allowed values are:
     *        - notification
     *        - notificationOnUnregistration
     *        - notificationOnRegistrationForQueue
     *        - notificationOnQueueUpdate
     *        In the following, the parameter is prefixed with "email_" and postfixed with "Hello" or "Subject".
     *
     * @return void
     */
    public function notifyOrganizers(
        \Tx_Seminars_OldModel_Registration $registration,
        string $helloSubjectPrefix = 'notification'
    ) {
        if (!$this->getConfValueBoolean('send' . ucfirst($helloSubjectPrefix))) {
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
        /** @var \Tx_Oelib_Mail $eMailNotification */
        $eMailNotification = GeneralUtility::makeInstance(\Tx_Oelib_Mail::class);
        $eMailNotification->setSender($event->getEmailSender());
        $eMailNotification->setReplyTo($event->getFirstOrganizer());

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($organizers as $organizer) {
            $eMailNotification->addRecipient($organizer);
        }

        $eMailNotification->setSubject(
            $this->translate('email_' . $helloSubjectPrefix . 'Subject') . ': ' . $registration->getTitle()
        );

        $this->initializeTemplate();
        $this->hideSubparts($this->getConfValueString('hideFieldsInNotificationMail'), 'field_wrapper');

        $this->setMarker('hello', $this->translate('email_' . $helloSubjectPrefix . 'Hello'));
        $this->setMarker('summary', $registration->getTitle());

        if ($this->hasConfValueString('showSeminarFieldsInNotificationMail')) {
            $this->setMarker(
                'seminardata',
                $event->dumpSeminarValues($this->getConfValueString('showSeminarFieldsInNotificationMail'))
            );
        } else {
            $this->hideSubparts('seminardata', 'field_wrapper');
        }

        if ($this->hasConfValueString('showFeUserFieldsInNotificationMail')) {
            $this->setMarker(
                'feuserdata',
                $registration->dumpUserValues($this->getConfValueString('showFeUserFieldsInNotificationMail'))
            );
        } else {
            $this->hideSubparts('feuserdata', 'field_wrapper');
        }

        if ($this->hasConfValueString('showAttendanceFieldsInNotificationMail')) {
            $this->setMarker(
                'attendancedata',
                $registration->dumpAttendanceValues($this->getConfValueString('showAttendanceFieldsInNotificationMail'))
            );
        } else {
            $this->hideSubparts('attendancedata', 'field_wrapper');
        }

        $eMailNotification->setMessage($this->getSubpart('MAIL_NOTIFICATION'));

        /** @var \Tx_Seminars_Mapper_Registration $registrationMapper */
        $registrationMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registrationNew */
        $registrationNew = $registrationMapper->find($registration->getUid());

        $this->getRegistrationEmailHookProvider()
            ->executeHook('modifyOrganizerEmail', $eMailNotification, $registrationNew, $helloSubjectPrefix);
        $this->callPostProcessOrganizerEmailHooks($eMailNotification, $registration);

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->getMailer()->send($eMailNotification);
    }

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4;
     *      use `->getRegistrationEmailHookProvider()->executeHook('modifyOrganizerEmail')` instead
     */
    protected function callPostProcessOrganizerEmailHooks(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_OldModel_Registration $registration
    ) {
        foreach ($this->getHooks() as $hook) {
            if ($hook instanceof RegistrationEmailHookInterface) {
                $hook->postProcessOrganizerEmail($mail, $registration);
            }
        }
    }

    /**
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Oelib_Template $emailTemplate
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4;
     *      use `->getRegistrationEmailHookProvider()->executeHook('modifyAttendeeEmailBodyPlainText')`
     *      or `->getRegistrationEmailHookProvider()->executeHook('modifyAttendeeEmailBodyHtml')` instead
     */
    protected function callPostProcessAttendeeEmailTextHooks(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Oelib_Template $emailTemplate
    ) {
        foreach ($this->getHooks() as $hook) {
            if ($hook instanceof RegistrationEmailHookInterface) {
                $hook->postProcessAttendeeEmailText($registration, $emailTemplate);
            }
        }
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
     * @param \Tx_Seminars_OldModel_Registration $registration the registration for which the notification should be send
     *
     * @return void
     */
    public function sendAdditionalNotification(\Tx_Seminars_OldModel_Registration $registration)
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

        /** @var \Tx_Oelib_Mail $eMail */
        $eMail = GeneralUtility::makeInstance(\Tx_Oelib_Mail::class);
        $eMail->setSender($event->getEmailSender());
        $eMail->setReplyTo($event->getFirstOrganizer());
        $eMail->setMessage($this->getMessageForNotification($registration, $emailReason));
        $eMail->setSubject(
            sprintf(
                $this->translate('email_additionalNotification' . $emailReason . 'Subject'),
                $event->getUid(),
                $event->getTitleAndDate('-')
            )
        );

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($event->getOrganizerBag() as $organizer) {
            $eMail->addRecipient($organizer);
        }

        /** @var \Tx_Seminars_Mapper_Registration $registrationMapper */
        $registrationMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registrationNew */
        $registrationNew = $registrationMapper->find($registration->getUid());

        $this->getRegistrationEmailHookProvider()
            ->executeHook('modifyAdditionalEmail', $eMail, $registrationNew, $emailReason);
        $this->callPostProcessAdditionalEmailHooks($eMail, $registration, $emailReason);

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->getMailer()->send($eMail);

        if ($event->hasEnoughAttendances() && !$event->haveOrganizersBeenNotifiedAboutEnoughAttendees()) {
            $event->setOrganizersBeenNotifiedAboutEnoughAttendees();
            $event->commitToDatabase();
        }
    }

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4;
     *      use `->getRegistrationEmailHookProvider()->executeHook('modifyAdditionalEmail')` instead
     */
    protected function callPostProcessAdditionalEmailHooks(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_OldModel_Registration $registration,
        string $emailReason
    ) {
        foreach ($this->getHooks() as $hook) {
            if ($hook instanceof RegistrationEmailHookInterface) {
                $hook->postProcessAdditionalEmail($mail, $registration, $emailReason);
            }
        }
    }

    /**
     * Returns the topic for the additional notification e-mail.
     *
     * @param \Tx_Seminars_OldModel_Registration $registration the registration for which the notification should be send
     *
     * @return string "EnoughRegistrations" if the event has enough attendances,
     *                "IsFull" if the event is fully booked, otherwise an empty string
     */
    private function getReasonForNotification(\Tx_Seminars_OldModel_Registration $registration): string
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
     * @param \Tx_Seminars_OldModel_Registration $registration
     *        the registration for which the notification should be send
     * @param string $reasonForNotification
     *        reason for the notification, must be either "IsFull" or "EnoughRegistrations", must not be empty
     *
     * @return string the message, will not be empty
     */
    private function getMessageForNotification(
        \Tx_Seminars_OldModel_Registration $registration,
        string $reasonForNotification
    ): string {
        $localLanguageKey = 'email_additionalNotification' . $reasonForNotification;
        $this->initializeTemplate();

        $this->setMarker('message', $this->translate($localLanguageKey));
        $showSeminarFields = $this->getConfValueString('showSeminarFieldsInNotificationMail');
        if ($showSeminarFields != '') {
            $this->setMarker('seminardata', $registration->getSeminarObject()->dumpSeminarValues($showSeminarFields));
        } else {
            $this->hideSubparts('seminardata', 'field_wrapper');
        }

        return $this->getSubpart('MAIL_ADDITIONALNOTIFICATION');
    }

    /**
     * Reads and initializes the templates.
     * If this has already been called for this instance, this function does nothing.
     *
     * This function will read the template file as it is set in the TypoScript setup. If there is a template file set in the
     * flexform of pi1, this will be ignored!
     *
     * @return void
     */
    private function initializeTemplate()
    {
        if (!$this->isTemplateInitialized) {
            $this->getTemplateCode(true);
            $this->setLabels();

            $this->isTemplateInitialized = true;
        }
    }

    /**
     * Builds the e-mail body for an e-mail to the attendee.
     *
     * @param \Tx_Seminars_OldModel_Registration $registration
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
        \Tx_Seminars_OldModel_Registration $registration,
        TemplateHelper $plugin,
        string $helloSubjectPrefix,
        $useHtml = false
    ): string {
        if ($this->linkBuilder === null) {
            /** @var \Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder */
            $linkBuilder = GeneralUtility::makeInstance(\Tx_Seminars_Service_SingleViewLinkBuilder::class);
            $this->injectLinkBuilder($linkBuilder);
        }
        $this->linkBuilder->setPlugin($plugin);

        $wrapperPrefix = ($useHtml ? 'html_' : '') . 'field_wrapper';

        $this->setMarker('html_mail_charset', 'utf-8');
        $this->hideSubparts($this->getConfValueString('hideFieldsInThankYouMail'), $wrapperPrefix);

        $this->setEMailIntroduction($helloSubjectPrefix, $registration);
        $event = $registration->getSeminarObject();
        $this->fillOrHideUnregistrationNotice($helloSubjectPrefix, $registration, $useHtml);

        $this->setMarker('uid', $event->getUid());

        $this->setMarker('registration_uid', $registration->getUid());

        if ($registration->hasSeats()) {
            $this->setMarker('seats', $registration->getSeats());
        } else {
            $this->hideSubparts('seats', $wrapperPrefix);
        }

        $this->fillOrHideAttendeeMarker($registration, $useHtml);

        if ($registration->hasLodgings()) {
            $this->setMarker('lodgings', $registration->getLodgings());
        } else {
            $this->hideSubparts('lodgings', $wrapperPrefix);
        }

        if ($registration->hasAccommodation()) {
            $this->setMarker('accommodation', $registration->getAccommodation());
        } else {
            $this->hideSubparts('accommodation', $wrapperPrefix);
        }

        if ($registration->hasFoods()) {
            $this->setMarker('foods', $registration->getFoods());
        } else {
            $this->hideSubparts('foods', $wrapperPrefix);
        }

        if ($registration->hasFood()) {
            $this->setMarker('food', $registration->getFood());
        } else {
            $this->hideSubparts('food', $wrapperPrefix);
        }

        if ($registration->hasCheckboxes()) {
            $this->setMarker('checkboxes', $registration->getCheckboxes());
        } else {
            $this->hideSubparts('checkboxes', $wrapperPrefix);
        }

        if ($registration->hasKids()) {
            $this->setMarker('kids', $registration->getNumberOfKids());
        } else {
            $this->hideSubparts('kids', $wrapperPrefix);
        }

        if ($event->hasAccreditationNumber()) {
            $this->setMarker('accreditation_number', $event->getAccreditationNumber());
        } else {
            $this->hideSubparts('accreditation_number', $wrapperPrefix);
        }

        if ($event->hasCreditPoints()) {
            $this->setMarker('credit_points', $event->getCreditPoints());
        } else {
            $this->hideSubparts('credit_points', $wrapperPrefix);
        }

        $this->setMarker('date', $event->getDate(($useHtml ? '&#8212;' : '-')));
        $this->setMarker('time', $event->getTime(($useHtml ? '&#8212;' : '-')));

        $this->fillPlacesMarker($event, $useHtml);

        if ($event->hasRoom()) {
            $this->setMarker('room', $event->getRoom());
        } else {
            $this->hideSubparts('room', $wrapperPrefix);
        }

        if ($registration->hasPrice()) {
            $this->setMarker('price', $registration->getPrice());
        } else {
            $this->hideSubparts('price', $wrapperPrefix);
        }

        if ($registration->hasTotalPrice()) {
            $this->setMarker('total_price', $registration->getTotalPrice());
        } else {
            $this->hideSubparts('total_price', $wrapperPrefix);
        }

        // We don't need to check $this->seminar->hasPaymentMethods() here as
        // method_of_payment can only be set (using the registration form) if
        // the event has at least one payment method.
        if ($registration->hasMethodOfPayment()) {
            $this->setMarker(
                'paymentmethod',
                $event->getSinglePaymentMethodPlain($registration->getMethodOfPaymentUid())
            );
        } else {
            $this->hideSubparts('paymentmethod', $wrapperPrefix);
        }

        $this->setMarker('billing_address', $registration->getBillingAddress());

        if ($registration->hasInterests()) {
            $this->setMarker('interests', $registration->getInterests());
        } else {
            $this->hideSubparts('interests', $wrapperPrefix);
        }

        /** @var \Tx_Seminars_Mapper_Event $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        /** @var \Tx_Seminars_Model_Event $newEvent */
        $newEvent = $mapper->find($event->getUid());
        $singleViewUrl = $this->linkBuilder->createAbsoluteUrlForEvent($newEvent);
        $this->setMarker('url', $useHtml ? \htmlspecialchars($singleViewUrl, ENT_QUOTES | ENT_HTML5) : $singleViewUrl);

        if ($event->isPlanned()) {
            $this->unhideSubparts('planned_disclaimer', $wrapperPrefix);
        } else {
            $this->hideSubparts('planned_disclaimer', $wrapperPrefix);
        }

        $footers = $event->getOrganizersFooter();
        $this->setMarker('footer', !empty($footers) ? LF . '-- ' . LF . $footers[0] : '');

        /** @var \Tx_Seminars_Mapper_Registration $registrationMapper */
        $registrationMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registrationNew */
        $registrationNew = $registrationMapper->find($registration->getUid());

        $this->getRegistrationEmailHookProvider()->executeHook(
            $useHtml ? 'modifyAttendeeEmailBodyHtml' : 'modifyAttendeeEmailBodyPlainText',
            $this->getTemplate(),
            $registrationNew,
            $helloSubjectPrefix
        );
        $this->callPostProcessAttendeeEmailTextHooks($registration, $this->getTemplate());

        return $this->getSubpart($useHtml ? 'MAIL_THANKYOU_HTML' : 'MAIL_THANKYOU');
    }

    /**
     * Checks whether the given event allows registration, as far as its date is concerned.
     *
     * @param \Tx_Seminars_OldModel_Event $event the event to check the registration for
     *
     * @return bool TRUE if the event allows registration by date, FALSE otherwise
     */
    public function allowsRegistrationByDate(\Tx_Seminars_OldModel_Event $event): bool
    {
        if ($event->hasDate()) {
            $result = !$event->isRegistrationDeadlineOver();
        } else {
            $result = $event->getConfValueBoolean('allowRegistrationForEventsWithoutDate');
        }

        return $result && $this->registrationHasStarted($event);
    }

    /**
     * Checks whether the given event allows registration as far as the number of vacancies are concerned.
     *
     * @param \Tx_Seminars_OldModel_Event $event the event to check the registration for
     *
     * @return bool TRUE if the event has enough seats for registration, FALSE otherwise
     */
    public function allowsRegistrationBySeats(\Tx_Seminars_OldModel_Event $event): bool
    {
        return $event->hasRegistrationQueue() || $event->hasUnlimitedVacancies() || $event->hasVacancies();
    }

    /**
     * Checks whether the registration for this event has started.
     *
     * @param \Tx_Seminars_OldModel_Event $event the event to check the registration for
     *
     * @return bool TRUE if registration for this event already has started, FALSE otherwise
     */
    public function registrationHasStarted(\Tx_Seminars_OldModel_Event $event): bool
    {
        if (!$event->hasRegistrationBegin()) {
            return true;
        }

        return $GLOBALS['SIM_EXEC_TIME'] >= $event->getRegistrationBeginAsUnixTimestamp();
    }

    /**
     * Fills the attendees_names marker or hides it if necessary.
     *
     * @param \Tx_Seminars_OldModel_Registration $registration the current registration
     * @param bool $useHtml whether to create HTML instead of plain text
     *
     * @return void
     */
    private function fillOrHideAttendeeMarker(\Tx_Seminars_OldModel_Registration $registration, bool $useHtml)
    {
        if (!$registration->hasAttendeesNames()) {
            $this->hideSubparts('attendees_names', ($useHtml ? 'html_' : '') . 'field_wrapper');
            return;
        }

        $this->setMarker('attendees_names', $registration->getEnumeratedAttendeeNames($useHtml));
    }

    /**
     * Sets the places marker for the attendee notification.
     *
     * @param \Tx_Seminars_OldModel_Event $event event of this registration
     * @param bool $useHtml whether to create HTML instead of plain text
     *
     * @return void
     */
    private function fillPlacesMarker(\Tx_Seminars_OldModel_Event $event, bool $useHtml)
    {
        if (!$event->hasPlace()) {
            $this->setMarker('place', $this->translate('message_willBeAnnounced'));
            return;
        }

        $newline = $useHtml ? '<br />' : LF;

        $formattedPlaces = [];
        /** @var \Tx_Seminars_Model_Place $place */
        foreach ($event->getPlaces() as $place) {
            $formattedPlaces[] = $this->formatPlace($place, $newline);
        }

        $this->setMarker('place', implode($newline . $newline, $formattedPlaces));
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
     * @param \Tx_Seminars_OldModel_Registration $registration the registration the introduction should be created for
     *
     * @return void
     */
    private function setEMailIntroduction(string $helloSubjectPrefix, \Tx_Seminars_OldModel_Registration $registration)
    {
        /** @var Salutation $salutation */
        $salutation = GeneralUtility::makeInstance(Salutation::class);
        $user = $registration->getFrontEndUser();
        if ($user instanceof \Tx_Seminars_Model_FrontEndUser) {
            $salutationText = $salutation->getSalutation($user);
        } else {
            $salutationText = '';
        }
        $this->setMarker('salutation', $salutationText);

        $event = $registration->getSeminarObject();
        $introductionTemplate = $this->translate('email_' . $helloSubjectPrefix . 'Hello');
        $introduction = $salutation->createIntroduction($introductionTemplate, $event);

        if ($registration->hasTotalPrice()) {
            $introduction .= ' ' . sprintf($this->translate('email_price'), $registration->getTotalPrice());
        }

        $this->setMarker('introduction', \trim($introduction . '.'));
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
     * @param \Tx_Seminars_OldModel_Registration $registration the registration the introduction should be created for
     * @param bool $useHtml whether to send HTML instead of plain text e-mail
     *
     * @return void
     */
    private function fillOrHideUnregistrationNotice(
        string $helloSubjectPrefix,
        \Tx_Seminars_OldModel_Registration $registration,
        bool $useHtml
    ) {
        $event = $registration->getSeminarObject();
        if (($helloSubjectPrefix === 'confirmationOnUnregistration') || !$event->isUnregistrationPossible()) {
            $this->hideSubparts('unregistration_notice', ($useHtml ? 'html_' : '') . 'field_wrapper');
            return;
        }

        $this->setMarker('unregistration_notice', $this->getUnregistrationNotice($event));
    }

    /**
     * Returns the unregistration notice for the notification mails.
     *
     * @param \Tx_Seminars_OldModel_Event $event the event to get the unregistration deadline from
     *
     * @return string the unregistration notice with the event's unregistration deadline, will not be empty
     */
    protected function getUnregistrationNotice(\Tx_Seminars_OldModel_Event $event): string
    {
        $unregistrationDeadline = $event->getUnregistrationDeadlineFromModelAndConfiguration();

        return sprintf(
            $this->translate('email_unregistrationNotice'),
            strftime($this->getConfValueString('dateFormatYMD'), $unregistrationDeadline)
        );
    }

    /**
     * Returns the (old) registration created via createRegistration.
     *
     * @return \Tx_Seminars_OldModel_Registration|null
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Gets all hooks for this class.
     *
     * @return array the hook objects, will be empty if no hooks have been set
     *
     * @deprecated Using index 'registration' for email related hooks will be removed in seminars 4;
     *      use `->getRegistrationEmailHookProvider()` instead
     */
    private function getHooks(): array
    {
        if (!$this->hooksHaveBeenRetrieved) {
            $hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'];
            if (is_array($hookClasses)) {
                foreach ($hookClasses as $hookClass) {
                    $hookObject = GeneralUtility::getUserObj($hookClass);
                    $this->hooks[] = $hookObject;
                    if ($hookObject instanceof RegistrationEmailHookInterface) {
                        GeneralUtility::deprecationLog(
                            $hookClass . ' - since seminars 3.0,'
                            . ' interface \\OliverKlee\\Seminars\\Hooks\\RegistrationEmailHookInterface'
                            . ' will be removed in seminars 4.0'
                        );
                    }
                }
            }

            $this->hooksHaveBeenRetrieved = true;
        }

        return $this->hooks;
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

    /**
     * Injects a link builder.
     *
     * @param \Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder the link builder instance to use
     *
     * @return void
     */
    public function injectLinkBuilder(\Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder)
    {
        $this->linkBuilder = $linkBuilder;
    }

    /**
     * Returns the UID of the logged-in front-end user (or 0 if no user is logged in).
     *
     * @return int
     */
    protected function getLoggedInFrontEndUserUid(): int
    {
        $loginManager = FrontEndLoginManager::getInstance();
        return $loginManager->isLoggedIn() ? $loginManager->getLoggedInUser(
            \Tx_Seminars_Mapper_FrontEndUser::class
        )->getUid() : 0;
    }

    /**
     * @return \Tx_Seminars_Mapper_Registration
     */
    protected function getRegistrationMapper(): \Tx_Seminars_Mapper_Registration
    {
        return MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
    }

    /**
     * Returns the prices that are actually available for the given user, depending on whether automatic prices are
     * enabled using the plugin.tx_seminars.automaticSpecialPriceForSubsequentRegistrationsBySameUser setting.
     *
     * @param Tx_Seminars_OldModel_Event $event
     * @param Tx_Seminars_Model_FrontEndUser $user
     *
     * @return string[][] the available prices as a reset array of arrays with the keys "caption" (for the title)
     *                    and "value (for the price code), might be empty
     */
    public function getPricesAvailableForUser(
        \Tx_Seminars_OldModel_Event $event,
        \Tx_Seminars_Model_FrontEndUser $user
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
