<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This class represents an event registration.
 */
class LegacyRegistration extends AbstractModel
{
    /**
     * @var string[][]
     */
    private const BILLING_ADDRESS_FIELDS = [
        'company' => ['separator' => "\n"],
        'gender' => ['separator' => ' '],
        'name' => ['separator' => "\n"],
        'address' => ['separator' => "\n"],
        'zip' => ['separator' => ' '],
        'city' => ['separator' => "\n"],
        'country' => ['separator' => "\n"],
        'telephone' => ['separator' => "\n", 'labelKey' => 'label_telephone'],
        'email' => ['separator' => "\n", 'labelKey' => 'label_email'],
    ];

    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static $tableName = 'tx_seminars_attendances';

    /**
     * the same as the class name
     *
     * @var string
     */
    public $prefixId = LegacyRegistration::class;

    /**
     * @var LegacyEvent|null the event to which this registration relates
     */
    private $seminar;

    /**
     * @var bool
     */
    private $userDataHasBeenRetrieved = false;

    /**
     * This variable stores the data of the user as an array and makes it
     * available without further database queries.
     *
     * @var array<string, string|int|bool>|null
     */
    private $userData;

    /**
     * UIDs of lodging options associated with this record
     *
     * @var array<int, int>
     */
    protected $lodgings = [];

    /**
     * UIDs of food options associated with this record
     *
     * @var array<int, int>
     */
    protected $foods = [];

    /**
     * UIDs of option checkboxes associated with this record
     *
     * @var array<int, int>
     */
    protected $checkboxes = [];

    /**
     * cached seminar objects with the seminar UIDs as keys and the objects as values
     *
     * @var LegacyEvent[]
     */
    private static $cachedSeminars = [];

    /**
     * @var FrontEndUser|null
     */
    protected $user;

    /**
     * @var FrontendUserGroupRepository|null
     */
    private $frontEndUserGroupRepository;

    /**
     * Purges our cached seminars array.
     *
     * This function is intended for testing purposes only.
     */
    public static function purgeCachedSeminars(): void
    {
        self::$cachedSeminars = [];
    }

    protected function getFrontEndUserGroupRepository(): FrontendUserGroupRepository
    {
        if (!$this->frontEndUserGroupRepository instanceof FrontendUserGroupRepository) {
            $repository = GeneralUtility::makeInstance(ObjectManager::class)->get(FrontendUserGroupRepository::class);
            $this->frontEndUserGroupRepository = $repository;
        }

        return $this->frontEndUserGroupRepository;
    }

    /**
     * Sets this registration's data if this registration is newly created instead of from a DB query.
     *
     * This function must be called directly after construction or this object will not be usable.
     *
     * @param LegacyEvent $event the seminar object (that's the seminar we would like to register for)
     * @param int $userUid UID of the FE user who wants to sign up
     * @param array $registrationData associative array with the registration data the user has just entered, may be empty
     */
    public function setRegistrationData(LegacyEvent $event, int $userUid, array $registrationData): void
    {
        $this->seminar = $event;

        $this->recordData = [];

        $this->recordData['seminar'] = $event->getUid();
        $this->recordData['user'] = $userUid;
        $this->recordData['registration_queue'] = (!$event->hasVacancies()) ? 1 : 0;

        $seats = (int)$registrationData['seats'];
        if ($seats < 1) {
            $seats = 1;
        }
        $this->recordData['seats'] = $seats;
        $this->recordData['registered_themselves'] = (int)$registrationData['registered_themselves'] ? 1 : 0;

        $availablePrices = $event->getAvailablePrices();
        // If no (available) price is selected, use the first price by default.
        $selectedPrice = isset($registrationData['price']) ? $registrationData['price'] : key($availablePrices);
        $this->recordData['price'] = $availablePrices[$selectedPrice]['caption'];

        $this->recordData['total_price'] = $seats * (float)$availablePrices[$selectedPrice]['amount'];

        $this->recordData['attendees_names'] = $registrationData['attendees_names'];

        $this->recordData['kids'] = $registrationData['kids'];

        $methodOfPayment = (int)$registrationData['method_of_payment'];
        // Auto-select the only payment method if no payment method has been
        // selected, there actually is anything to pay and only one payment
        // method is provided.
        if (!$methodOfPayment && $this->recordData['total_price'] > 0.00 && $event->getNumberOfPaymentMethods() === 1) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_seminars_payment_methods');
            $rows = $queryBuilder
                ->select('uid')
                ->from('tx_seminars_payment_methods')
                ->join(
                    'tx_seminars_payment_methods',
                    'tx_seminars_seminars_payment_methods_mm',
                    'mm',
                    $queryBuilder->expr()->eq(
                        'mm.uid_foreign',
                        $queryBuilder->quoteIdentifier('tx_seminars_payment_methods.uid')
                    )
                )
                ->where(
                    $queryBuilder->expr()->eq(
                        'mm.uid_local',
                        $queryBuilder->createNamedParameter($event->getTopicOrSelfUid(), \PDO::PARAM_INT)
                    )
                )
                ->orderBy('mm.sorting')
                ->execute()
                ->fetchAll();

            $methodOfPayment = (int)$rows[0]['uid'];
        }
        $this->setMethodOfPaymentUid($methodOfPayment);

        $this->recordData['company'] = $registrationData['company'];
        $this->recordData['gender'] = $registrationData['gender'];
        $this->recordData['name'] = $registrationData['name'];
        $this->recordData['address'] = $registrationData['address'];
        $this->recordData['zip'] = $registrationData['zip'];
        $this->recordData['city'] = $registrationData['city'];
        $this->recordData['country'] = $registrationData['country'];
        $this->recordData['telephone'] = $registrationData['telephone'];
        $this->recordData['email'] = $registrationData['email'];

        $lodgings = (isset($registrationData['lodgings']) && \is_array($registrationData['lodgings']))
            ? $registrationData['lodgings'] : [];
        \array_walk($lodgings, '\\intval');
        $this->lodgings = $lodgings;
        $this->recordData['lodgings'] = \count($lodgings);

        $foods = (isset($registrationData['foods']) && \is_array($registrationData['foods']))
            ? $registrationData['foods'] : [];
        \array_walk($foods, '\\intval');
        $this->foods = $foods;
        $this->recordData['foods'] = \count($foods);

        $checkboxes = (isset($registrationData['checkboxes']) && \is_array($registrationData['checkboxes']))
            ? $registrationData['checkboxes'] : [];
        \array_walk($checkboxes, '\\intval');
        $this->checkboxes = $checkboxes;
        $this->recordData['checkboxes'] = \count($checkboxes);

        $this->recordData['interests'] = $registrationData['interests'];
        $this->recordData['expectations'] = $registrationData['expectations'];
        $this->recordData['background_knowledge'] = $registrationData['background_knowledge'];
        $this->recordData['accommodation'] = $registrationData['accommodation'];
        $this->recordData['food'] = $registrationData['food'];
        $this->recordData['known_from'] = $registrationData['known_from'];
        $this->recordData['notes'] = $registrationData['notes'];

        // @deprecated #1922 organizer-specific registration folders will be removed in seminars 5.0
        $this->recordData['pid'] = $this->seminar->hasAttendancesPid()
            ? $this->seminar->getAttendancesPid() : $this->getSharedConfiguration()->getAsInteger('attendancesPID');

        $this->processAdditionalRegistrationData($registrationData);

        if ($this->isOk()) {
            // Stores the user data in $this->userData.
            $this->retrieveUserData();
            $this->createTitle();
        }
    }

    /**
     * Processes additional registration data.
     *
     * This function is intended to be overwritten in subclasses.
     *
     * @param array $registrationData associative array with the registration data the user has just entered, may be empty
     */
    protected function processAdditionalRegistrationData(array $registrationData): void
    {
    }

    /**
     * Gets the number of seats that are registered with this registration.
     *
     * If no value is saved in the record, 1 will be returned.
     *
     * @return int the number of seats
     */
    public function getSeats(): int
    {
        if ($this->hasSeats()) {
            $seats = $this->getRecordPropertyInteger('seats');
        } else {
            $seats = 1;
        }

        return $seats;
    }

    /**
     * Sets our number of seats.
     *
     * @param int $seats the number of seats, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setSeats(int $seats): void
    {
        if ($seats < 0) {
            throw new \InvalidArgumentException('The parameter $seats must be >= 0.', 1333291732);
        }

        $this->setRecordPropertyInteger('seats', $seats);
    }

    /**
     * Returns whether this registration has seats.
     *
     * @return bool TRUE if this registration has seats, FALSE otherwise
     */
    public function hasSeats(): bool
    {
        return $this->hasRecordPropertyInteger('seats');
    }

    /**
     * Creates our title and writes it to $this->title.
     *
     * The title is constructed like this:
     *   Name of Attendee / Title of Seminar seminardate
     */
    private function createTitle(): void
    {
        $this->recordData['title'] = $this->getUserName() .
            ' / ' . $this->getSeminarObject()->getTitle() . ', ' . $this->getSeminarObject()->getDate('-');
    }

    /**
     * Gets the complete FE user data as an array.
     *
     * The attendee's user data (from fe_users) will be written to `$this->userData`.
     *
     * `$this->userData` will be null if retrieving the user data fails.
     *
     * @throws NotFoundException
     */
    private function retrieveUserData(): void
    {
        $uid = $this->getUser();
        if ($uid === 0) {
            $this->userData = null;
            return;
        }

        $table = 'fe_users';
        /** @var array<string, string|int|bool>|false $data */
        $data = self::getConnectionForTable($table)->select(['*'], $table, ['uid' => $uid])->fetch();
        if (!\is_array($data)) {
            throw new NotFoundException(
                'The FE user with the UID ' . $uid . ' could not be retrieved.',
                1390065114
            );
        }

        $this->setUserData($data);
    }

    /**
     * Sets the data of the FE user of this registration.
     *
     * @param array<string, string|int|bool> $data
     */
    public function setUserData(array $data): void
    {
        $this->userData = $data;
        $this->userDataHasBeenRetrieved = true;
    }

    /**
     * Retrieves a value from this record. The return value will be an empty
     * string if the key is not defined in $this->recordData or if it has not
     * been filled in.
     *
     * If the data needs to be decoded to be readable (eg. the method of
     * payment or the gender), this function will already return the clear text
     * version.
     *
     * @param non-empty-string $key the key of the data to retrieve, need not be trimmed
     *
     * @return string the trimmed value retrieved from $this->recordData with CR replaced by LF, may be empty empty
     */
    public function getRegistrationData(string $key): string
    {
        /** @var non-empty-string $trimmedKey */
        $trimmedKey = trim($key);

        switch ($trimmedKey) {
            case 'crdate':
                // The fallthrough is intended.
            case 'tstamp':
                $format = $this->getDateFormat() . ' ' . $this->getTimeFormat();
                $result = \strftime($format, $this->getRecordPropertyInteger($trimmedKey));
                break;
            case 'uid':
                $result = $this->getUid();
                break;
            case 'price':
                $result = $this->getPrice();
                break;
            case 'total_price':
                $result = $this->getTotalPrice();
                break;
            case 'registered_themselves':
                // The fallthrough is intended.
            case 'been_there':
                $result = $this->getRecordPropertyBoolean($trimmedKey)
                    ? $this->translate('label_yes') : $this->translate('label_no');
                break;
            case 'datepaid':
                $result = \strftime($this->getDateFormat(), $this->getRecordPropertyInteger($trimmedKey));
                break;
            case 'method_of_payment':
                $result = $this->getSeminarObject()
                    ->getSinglePaymentMethodShort($this->getRecordPropertyInteger($trimmedKey));
                break;
            case 'gender':
                $result = $this->getGender();
                break;
            case 'seats':
                $result = $this->getSeats();
                break;
            case 'lodgings':
                $result = $this->getLodgings();
                break;
            case 'foods':
                $result = $this->getFoods();
                break;
            case 'checkboxes':
                $result = $this->getCheckboxes();
                break;
            case 'attendees_names':
                $result = $this->getEnumeratedAttendeeNames();
                break;
            default:
                $result = $this->getRecordPropertyString($trimmedKey);
        }

        $carriageReturnRemoved = \str_replace("\r", "\n", (string)$result);

        return \trim(\preg_replace('/\\x0a{2,}/', "\n", $carriageReturnRemoved));
    }

    /**
     * Retrieves a value out of the userData array. The return value will be an
     * empty string if the key is not defined in the $this->userData array.
     *
     * If the data needs to be decoded to be readable (e.g., the gender, the date
     * of birth or the status), this function will already return the clear-text version.
     *
     * @param string $key key of the data to retrieve, may contain leading or trailing spaces
     *
     * @return string the trimmed value retrieved from $this->userData, may be empty
     */
    public function getUserData(string $key): string
    {
        if (!$this->userDataHasBeenRetrieved) {
            $this->retrieveUserData();
        }

        $trimmedKey = \trim($key);

        if (!\is_array($this->userData) || $trimmedKey === '' || !\array_key_exists($trimmedKey, $this->userData)) {
            return '';
        }

        $rawData = \trim((string)$this->userData[$trimmedKey]);
        switch ($trimmedKey) {
            case 'gender':
                $result = $this->translate('label_gender.I.' . $rawData);
                break;
            case 'status':
                $result = (int)$rawData !== 0 ? $this->translate('label_status.I.' . $rawData) : '';
                break;
            case 'wheelchair':
                $result = (bool)$rawData ? $this->translate('label_yes') : $this->translate('label_no');
                break;
            case 'crdate':
                // The fallthrough is intended.
            case 'tstamp':
                $format = $this->getDateFormat() . ' ' . $this->getTimeFormat();
                $result = \strftime($format, (int)$rawData);
                break;
            case 'date_of_birth':
                $result = \strftime($this->getDateFormat(), (int)$rawData);
                break;
            case 'name':
                $user = $this->getFrontEndUser();
                $result = $user instanceof FrontEndUser ? $user->getName() : '';
                break;
            case 'usergroup':
                $repository = $this->getFrontEndUserGroupRepository();
                $titles = [];
                /** @var int[] $uids */
                $uids = GeneralUtility::intExplode(',', $rawData, true);
                foreach ($uids as $uid) {
                    $group = $repository->findByUid($uid);
                    if ($group instanceof FrontendUserGroup) {
                        $titles[] = $group->getTitle();
                    }
                }
                $result = \implode(', ', $titles);
                break;
            default:
                $result = $rawData;
        }

        return \trim($result);
    }

    /**
     * Returns values out of the userData array, nicely formatted as safe
     * HTML and with the e-mail address as a mailto: link.
     * If more than one key is provided, the return values are separated
     * by a comma and a space.
     * Empty values will be removed from the output.
     *
     * @param string $keys comma-separated list of keys to retrieve
     * @param AbstractPlugin $plugin an object for a live page
     *
     * @return string the values retrieved from $this->userData, may be empty
     */
    public function getUserDataAsHtml(string $keys, AbstractPlugin $plugin): string
    {
        /** @var array<int, non-empty-string> $singleKeys */
        $singleKeys = GeneralUtility::trimExplode(',', $keys, true);
        $singleValues = [];

        foreach ($singleKeys as $key) {
            $rawValue = $this->getUserData($key);
            if ($rawValue === '') {
                continue;
            }

            $singleValues[$key] = $key === 'email'
                ? $plugin->cObj->mailto_makelinks('mailto:' . $rawValue, [])
                : \htmlspecialchars($rawValue, ENT_QUOTES | ENT_HTML5);
        }

        return \implode(', ', $singleValues);
    }

    /**
     * Gets the attendee's UID.
     *
     * @return int the attendee's FE user uid
     */
    public function getUser(): int
    {
        return $this->getRecordPropertyInteger('user');
    }

    /**
     * Gets the attendee's (real) name
     *
     * @return string the attendee's name
     */
    public function getUserName(): string
    {
        return $this->getUserData('name');
    }

    /**
     * Gets the attendee's name and e-mail address in the format
     * '"John Doe" <john.doe@example.com>'.
     *
     * @return string the attendee's name and e-mail address
     */
    public function getUserNameAndEmail(): string
    {
        return '"' . $this->getUserData('name') . '" <' . $this->getUserData('email') . '>';
    }

    public function getFrontEndUser(): ?FrontEndUser
    {
        if ($this->user instanceof FrontEndUser) {
            return $this->user;
        }
        $uid = $this->getUser();
        if ($uid === 0) {
            return null;
        }

        $mapper = MapperRegistry::get(FrontEndUserMapper::class);
        $this->user = $mapper->find($uid);

        return $this->user;
    }

    /**
     * @param FrontEndUser $user
     */
    public function setFrontEndUser(FrontEndUser $user): void
    {
        $this->user = $user;
    }

    /**
     * Returns whether the registration has an existing front-end user.
     *
     * @return bool TRUE if the registration has an existing front-end user, FALSE otherwise
     */
    public function hasExistingFrontEndUser(): bool
    {
        if ($this->getUser() <= 0) {
            return false;
        }

        $mapper = MapperRegistry::get(FrontEndUserMapper::class);

        return $mapper->existsModel($this->getUser());
    }

    /**
     * Sets the front-end user UID of the registration.
     *
     * @param int $uid the front-end user UID of the attendee, must be > 0
     */
    public function setFrontEndUserUid(int $uid): void
    {
        $this->setRecordPropertyInteger('user', $uid);
    }

    /**
     * Gets the seminar's UID.
     *
     * @return int the seminar's UID
     */
    public function getSeminar(): int
    {
        return $this->getRecordPropertyInteger('seminar');
    }

    /**
     * Gets the seminar to which this registration belongs.
     *
     * @return LegacyEvent the seminar to which this registration belongs
     */
    public function getSeminarObject(): LegacyEvent
    {
        if (!$this->seminar instanceof LegacyEvent && $this->isOk()) {
            $seminarUid = $this->getRecordPropertyInteger('seminar');
            if (isset(self::$cachedSeminars[$seminarUid])) {
                $this->seminar = self::$cachedSeminars[$seminarUid];
            } else {
                $this->seminar = GeneralUtility::makeInstance(LegacyEvent::class, $seminarUid);
                self::$cachedSeminars[$seminarUid] = $this->seminar;
            }
        }

        return $this->seminar;
    }

    /**
     * Gets whether this attendance has already been paid for.
     *
     * @return bool whether this attendance has already been paid for
     */
    public function isPaid(): bool
    {
        return $this->getRecordPropertyInteger('datepaid') > 0;
    }

    /**
     * Sets the date when this registration has been paid for.
     *
     * @param int $paymentDate
     *        the date of the payment as UNIX timestamp, must be >= 0
     */
    public function setPaymentDateAsUnixTimestamp(int $paymentDate): void
    {
        $this->setRecordPropertyInteger('datepaid', $paymentDate);
    }

    /**
     * Gets the attendee's special interests in the subject.
     *
     * @return string a description of the attendee's special interests (may be empty)
     */
    public function getInterests(): string
    {
        return $this->getRecordPropertyString('interests');
    }

    /**
     * Checks whether the attendee has stated any special interests.
     *
     * @return bool
     */
    public function hasInterests(): bool
    {
        return $this->hasRecordPropertyString('interests');
    }

    /**
     * Gets the attendee's expectations for the event.
     *
     * @return string a description of the attendee's expectations for the
     *                event (may be empty)
     */
    public function getExpectations(): string
    {
        return $this->getRecordPropertyString('expectations');
    }

    /**
     * Gets the attendee's background knowledge on the subject.
     *
     * @return string a description of the attendee's background knowledge
     *                (may be empty)
     */
    public function getKnowledge(): string
    {
        return $this->getRecordPropertyString('background_knowledge');
    }

    /**
     * Gets where the attendee has heard about this event.
     *
     * @return string a description of where the attendee has heard about
     *                this event (may be empty)
     */
    public function getKnownFrom(): string
    {
        return $this->getRecordPropertyString('known_from');
    }

    /**
     * Gets text from the "additional notes" field the attendee could fill at
     * online registration.
     *
     * @return string additional notes on registration (may be empty)
     */
    public function getNotes(): string
    {
        return $this->getRecordPropertyString('notes');
    }

    /**
     * Gets the saved price category name and its single price, all in one long
     * string.
     *
     * @return string the saved price category name and its single price or an empty string if no price had been saved
     */
    public function getPrice(): string
    {
        return $this->getRecordPropertyString('price');
    }

    /**
     * Sets our price category name and its single price.
     *
     * @param string $price the price category name and its single price, may be empty
     */
    public function setPrice(string $price): void
    {
        $this->setRecordPropertyString('price', $price);
    }

    /**
     * Returns whether this registration has a saved price category name and its single price.
     *
     * @return bool TRUE if this registration has a price, FALSE otherwise
     */
    public function hasPrice(): bool
    {
        return $this->hasRecordPropertyString('price');
    }

    /**
     * Gets the saved total price and the currency.
     *
     * An empty string will be returned if no total price could be calculated.
     *
     * @return string the total price and the currency or an empty string if no total price could be calculated
     */
    public function getTotalPrice(): string
    {
        $result = '';
        $totalPrice = $this->getRecordPropertyDecimal('total_price');
        if ($totalPrice != '0.00') {
            $result = $this->getSeminarObject()->formatPrice($totalPrice);
        }

        return $result;
    }

    /**
     * Sets our total price.
     *
     * @param string $price the total price, may be empty
     */
    public function setTotalPrice(string $price): void
    {
        $this->setRecordPropertyString('total_price', $price);
    }

    /**
     * Returns whether this registration has a total price.
     *
     * @return bool TRUE if this registration has a total price, FALSE otherwise
     */
    public function hasTotalPrice(): bool
    {
        return $this->hasRecordPropertyDecimal('total_price');
    }

    /**
     * Gets a plain text list of feuser property values (if they exist),
     * formatted as strings (and nicely lined up) in the following format:
     *
     * key1: value1
     *
     * @param string $keysList comma-separated list of key names
     *
     * @return string formatted output (may be empty)
     */
    public function dumpUserValues(string $keysList): string
    {
        /** @var array<int, non-empty-string> $keys */
        $keys = GeneralUtility::trimExplode(',', $keysList, true);
        $labels = [];
        $result = '';

        $maximumLabelLength = 0;
        foreach ($keys as $key) {
            $frontEndUserLabelKey = 'label_feuser_' . $key;
            $frontEndUserLabel = $this->translate($frontEndUserLabelKey);

            $defaultLabelKey = 'label_' . $key;
            $defaultLabel = $this->translate($defaultLabelKey);

            if ($frontEndUserLabel !== '' && $frontEndUserLabel !== $frontEndUserLabelKey) {
                $label = $frontEndUserLabel;
            } elseif ($defaultLabel !== '' && $defaultLabel !== $defaultLabelKey) {
                $label = $defaultLabel;
            } else {
                $label = \ucfirst($key);
            }
            $label = \rtrim($label, ':');

            $labels[$key] = $label;
            $maximumLabelLength = \max($maximumLabelLength, \mb_strlen($label, 'utf-8'));
        }

        foreach ($keys as $key) {
            $label = $labels[$key];
            $value = $this->getUserData($key);
            // Checks whether there is a value to display.
            // If not, we will not use the padding and break the line directly after the label.
            if ($value !== '') {
                $padding = \str_pad('', $maximumLabelLength - \mb_strlen($label, 'utf-8'));
                $result .= $label . ': ' . $padding . $value . "\n";
            } else {
                $result .= $label . ":\n";
            }
        }

        return $result;
    }

    /**
     * Gets a plain text list of attendance (registration) property values (if they exist), formatted as strings (and nicely
     * lined up) in the following format:
     *
     * key1: value1
     *
     * @param string $keysList comma-separated list of key names
     *
     * @return string formatted output (may be empty)
     */
    public function dumpAttendanceValues(string $keysList): string
    {
        /** @var array<int, non-empty-string> $keys */
        $keys = GeneralUtility::trimExplode(',', $keysList, true);
        /** @var array<non-empty-string, string> $labels */
        $labels = [];

        $maximumLabelLength = 0;
        foreach ($keys as $key) {
            if ($key === 'uid') {
                // The UID label is a special case as we also have a UID label for events.
                $currentLabel = $this->translate('label_registration_uid');
            } else {
                $currentLabel = $this->translate('label_' . $key);
            }
            $currentLabel = \rtrim($currentLabel, ':');
            $labels[$key] = $currentLabel;
            $maximumLabelLength = \max($maximumLabelLength, \mb_strlen($currentLabel, 'utf-8'));
        }

        $result = '';
        /** @var non-empty-string $key */
        foreach ($labels as $key => $currentLabel) {
            $value = $this->getRegistrationData($key);
            if ($value === '') {
                $result .= $currentLabel . ":\n";
                continue;
            }

            if (\strpos($value, "\n") !== false) {
                $result .= $currentLabel . ": \n";
            } else {
                $padding = \str_pad('', $maximumLabelLength - \mb_strlen($currentLabel, 'utf-8'));
                $result .= $currentLabel . ': ' . $padding;
            }
            $result .= $value . "\n";
        }

        return $result;
    }

    /**
     * Gets the billing address, formatted as plain text.
     *
     * @return string
     */
    public function getBillingAddress(): string
    {
        $result = '';
        foreach (self::BILLING_ADDRESS_FIELDS as $key => $options) {
            if (!$this->hasRecordPropertyString($key)) {
                continue;
            }

            if (isset($options['labelKey'])) {
                $result .= $this->translate($options['labelKey']) . ': ';
            }
            $result .= $this->getRegistrationData($key) . $options['separator'];
        }

        return $result;
    }

    /**
     * Retrieves the localized string corresponding to the key in the "gender" field.
     *
     * @return string the localized gender as entered for the billing address (Mr. or Mrs.)
     */
    public function getGender(): string
    {
        return $this->translate('label_gender.I.' . $this->getRecordPropertyInteger('gender'));
    }

    /**
     * Checks whether there are any lodging options referenced by this record.
     *
     * @return bool TRUE if at least one lodging option is referenced by this record, FALSE otherwise
     */
    public function hasLodgings(): bool
    {
        return $this->hasRecordPropertyInteger('lodgings');
    }

    /**
     * Gets the selected lodging options separated by LF. If there is no
     * lodging option selected, this function will return an empty string.
     *
     * @return string the titles of the selected lodging options separated by
     *                LF or an empty string if no lodging option is selected
     */
    public function getLodgings(): string
    {
        if (!$this->hasLodgings()) {
            return '';
        }

        return \implode("\n", $this->getMmRecordTitles('tx_seminars_lodgings', 'tx_seminars_attendances_lodgings_mm'));
    }

    /**
     * @return array<int, int>
     */
    public function getLodgingsUids(): array
    {
        return $this->lodgings;
    }

    /**
     * Returns the food free-text content.
     */
    public function getFood(): string
    {
        return $this->getRecordPropertyString('food');
    }

    /**
     * Checks whether this registration has non-empty data in the food field.
     */
    public function hasFood(): bool
    {
        return $this->hasRecordPropertyString('food');
    }

    /**
     * Returns the accommodation free-text content.
     */
    public function getAccommodation(): string
    {
        return $this->getRecordPropertyString('accommodation');
    }

    /**
     * Checks whether this registration has non-empty data in the accommodation field.
     */
    public function hasAccommodation(): bool
    {
        return $this->hasRecordPropertyString('accommodation');
    }

    /**
     * Checks whether there are any food options referenced by this record.
     *
     * @return bool TRUE if at least one food option is referenced by this record, FALSE otherwise
     */
    public function hasFoods(): bool
    {
        return $this->hasRecordPropertyInteger('foods');
    }

    /**
     * Gets the selected food options separated by LF. If there is no
     * food option selected, this function will return an empty string.
     *
     * @return string the titles of the selected lodging options separated by LF or an empty string if no food option is selected
     */
    public function getFoods(): string
    {
        if (!$this->hasFoods()) {
            return '';
        }

        return \implode("\n", $this->getMmRecordTitles('tx_seminars_foods', 'tx_seminars_attendances_foods_mm'));
    }

    /**
     * @return array<int, int>
     */
    public function getFoodsUids(): array
    {
        return $this->foods;
    }

    /**
     * Checks whether any option checkboxes are referenced by this record.
     *
     * @return bool TRUE if at least one option checkbox is referenced by this record, FALSE otherwise
     */
    public function hasCheckboxes(): bool
    {
        return $this->hasRecordPropertyInteger('checkboxes');
    }

    /**
     * Gets the selected option checkboxes separated by LF. If no option
     * checkbox is selected, this function will return an empty string.
     *
     * @return string the titles of the selected option checkboxes separated by
     *                LF or an empty string if no option checkbox is selected
     */
    public function getCheckboxes(): string
    {
        if (!$this->hasCheckboxes()) {
            return '';
        }

        return \implode(
            "\n",
            $this->getMmRecordTitles('tx_seminars_checkboxes', 'tx_seminars_attendances_checkboxes_mm')
        );
    }

    /**
     * @return array<int, int>
     */
    public function getCheckboxesUids(): array
    {
        return $this->checkboxes;
    }

    /**
     * Writes this record to the DB and adds any needed m:n records.
     *
     * This function actually calls the same method in the parent class
     * (which saves the record to the DB) and then adds any necessary m:n relations.
     *
     * The UID of the parent page must be set in $this->recordData['pid'].
     * (otherwise the record will be created in the root page).
     *
     * @return bool true if everything went OK, false otherwise
     */
    public function commitToDatabase(): bool
    {
        $this->fillEmptyDefaultFields();
        if (!parent::commitToDatabase()) {
            return false;
        }

        if ($this->hasUid()) {
            $this->createMmRecords('tx_seminars_attendances_lodgings_mm', $this->lodgings);
            $this->createMmRecords('tx_seminars_attendances_foods_mm', $this->foods);
            $this->createMmRecords('tx_seminars_attendances_checkboxes_mm', $this->checkboxes);
        }

        GeneralUtility::makeInstance(ReferenceIndex::class)
            ->updateRefIndexTable('tx_seminars_attendances', $this->getUid());

        return true;
    }

    /**
     * Fills the default fields that don't have any data.
     */
    protected function fillEmptyDefaultFields(): void
    {
        $integerDefaultFieldKeys = [
            'kids',
            'method_of_payment',
            'gender',
        ];
        $stringDefaultFieldKeys = [
            'name',
            'zip',
            'city',
            'country',
            'telephone',
            'email',
        ];

        foreach ($integerDefaultFieldKeys as $key) {
            if (!$this->hasRecordPropertyInteger($key)) {
                $this->setRecordPropertyInteger($key, 0);
            }
        }

        foreach ($stringDefaultFieldKeys as $key) {
            if (!$this->hasRecordPropertyString($key)) {
                $this->setRecordPropertyString($key, '');
            }
        }
    }

    /**
     * Returns TRUE if this registration is on the registration queue, FALSE otherwise.
     *
     * @return bool TRUE if this registration is on the registration queue, FALSE otherwise
     */
    public function isOnRegistrationQueue(): bool
    {
        return $this->getRecordPropertyBoolean('registration_queue');
    }

    public function setIsOnRegistrationQueue(bool $isOnRegistrationQueue): void
    {
        $this->setRecordPropertyInteger('registration_queue', (int)$isOnRegistrationQueue);
    }

    /**
     * Gets this registration's status as a localized string.
     *
     * @return string a localized version of either "waiting list" or "regular", will not be empty
     */
    public function getStatus(): string
    {
        $languageKey = $this->isOnRegistrationQueue() ? 'status_waiting_list' : 'status_regular';

        return $this->translate($languageKey);
    }

    /**
     * Returns our attendees names.
     *
     * @return string our attendees names, will be empty if this registration has no attendees names
     */
    public function getAttendeesNames(): string
    {
        return $this->getRecordPropertyString('attendees_names');
    }

    /**
     * Sets our attendees names.
     *
     * @param string $attendeesNames our attendees names, may be empty
     */
    public function setAttendeesNames(string $attendeesNames): void
    {
        $this->setRecordPropertyString('attendees_names', $attendeesNames);
    }

    /**
     * Returns whether this registration has attendees names.
     *
     * @return bool TRUE if this registration has attendees names, FALSE otherwise
     */
    public function hasAttendeesNames(): bool
    {
        return $this->hasRecordPropertyString('attendees_names');
    }

    /**
     * Returns our number of kids.
     *
     * @return int the number of kids, will be >= 0, will be 0 if this registration has no kids
     */
    public function getNumberOfKids(): int
    {
        return $this->getRecordPropertyInteger('kids');
    }

    /**
     * Sets the number of kids.
     *
     * @param int $numberOfKids the number of kids, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setNumberOfKids(int $numberOfKids): void
    {
        if ($numberOfKids < 0) {
            throw new \InvalidArgumentException('The parameter $numberOfKids must be >= 0.', 1333291776);
        }

        $this->setRecordPropertyInteger('kids', $numberOfKids);
    }

    /**
     * Returns whether this registration has kids.
     *
     * @return bool TRUE if this registration has kids, FALSE otherwise
     */
    public function hasKids(): bool
    {
        return $this->hasRecordPropertyInteger('kids');
    }

    /**
     * Returns our method of payment UID.
     *
     * @return int will be 0 if this registration has no method of payment
     */
    public function getMethodOfPaymentUid(): int
    {
        return $this->getRecordPropertyInteger('method_of_payment');
    }

    /**
     * Sets our method of payment UID.
     *
     * @param int $uid our method of payment UID, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setMethodOfPaymentUid(int $uid): void
    {
        if ($uid < 0) {
            throw new \InvalidArgumentException('The parameter $uid must be >= 0.', 1333291786);
        }

        $this->setRecordPropertyInteger('method_of_payment', $uid);
    }

    /**
     * Returns whether this registration has a method of payment.
     *
     * @return bool TRUE if this event has a method of payment, FALSE otherwise
     */
    public function hasMethodOfPayment(): bool
    {
        return $this->hasRecordPropertyInteger('method_of_payment');
    }

    /**
     * Returns the enumerated attendees_names.
     *
     * If the enumerated names should be built by using HTML, they will be
     * created as list items of an ordered list. In the plain text case the
     * entries will be separated by LF.
     *
     * @param bool $useHtml whether to use HTML to build the enumeration
     *
     * @return string the names stored in attendees_name enumerated, will be
     *                empty if this registration has no attendees names
     */
    public function getEnumeratedAttendeeNames(bool $useHtml = false): string
    {
        if (!$this->hasAttendeesNames() && !$this->hasRegisteredThemselves()) {
            return '';
        }

        /** @var array<int, non-empty-string> $names */
        $names = GeneralUtility::trimExplode("\n", $this->getAttendeesNames(), true);
        if ($this->hasRegisteredThemselves()) {
            \array_unshift($names, $this->getFrontEndUser()->getName());
        }

        if ($useHtml) {
            $result = '<ol><li>' . implode('</li><li>', $names) . '</li></ol>';
        } else {
            $enumeratedNames = [];
            $attendeeCounter = 1;
            foreach ($names as $name) {
                $enumeratedNames[] = $attendeeCounter . '. ' . $name;
                $attendeeCounter++;
            }
            $result = implode("\n", $enumeratedNames);
        }

        return $result;
    }

    public function hasRegisteredThemselves(): bool
    {
        return $this->getRecordPropertyBoolean('registered_themselves');
    }

    public function setRegisteredThemselves(bool $registeredThemselves): void
    {
        $this->setRecordPropertyBoolean('registered_themselves', $registeredThemselves);
    }
}
