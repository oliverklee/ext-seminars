<?php

use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This class represents a registration/attendance.
 *
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_OldModel_Registration extends \Tx_Seminars_OldModel_Abstract
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected $tableName = 'tx_seminars_attendances';

    /**
     * the same as the class name
     *
     * @var string
     */
    public $prefixId = \Tx_Seminars_OldModel_Registration::class;

    /**
     * @var \Tx_Seminars_OldModel_Event the event to which this registration relates
     */
    private $seminar = null;

    /**
     * @var bool whether the user data has already been retrieved
     */
    private $userDataHasBeenRetrieved = false;

    /**
     * This variable stores the data of the user as an array and makes it
     * available without further database queries. It will get filled with data
     * in the constructor.
     *
     * @var string[]|null
     */
    private $userData = null;

    /**
     * UIDs of lodging options associated with this record
     *
     * @var int[]
     */
    protected $lodgings = [];

    /**
     * UIDs of food options associated with this record
     *
     * @var int[]
     */
    protected $foods = [];

    /**
     * UIDs of option checkboxes associated with this record
     *
     * @var int[]
     */
    protected $checkboxes = [];

    /**
     * cached seminar objects with the seminar UIDs as keys and the objects as values
     *
     * @var \Tx_Seminars_OldModel_Event[]
     */
    private static $cachedSeminars = [];

    /**
     * The constructor.
     *
     * @param ContentObjectRenderer $contentObjectRenderer content object
     * @param \mysqli_result|bool $dbResult
     *        MySQL result (of SELECT query) object. If this parameter is not provided or FALSE,
     *        setRegistrationData() needs to be called directly after construction or this object will not be usable.
     */
    public function __construct(ContentObjectRenderer $contentObjectRenderer, $dbResult = false)
    {
        $this->cObj = $contentObjectRenderer;
        $this->init();

        if ($dbResult === false) {
            return;
        }

        $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
        if ($data !== false) {
            $this->setData($data);
        }
    }

    /**
     * Purges our cached seminars array.
     *
     * This function is intended for testing purposes only.
     *
     * @return void
     */
    public static function purgeCachedSeminars()
    {
        self::$cachedSeminars = [];
    }

    /**
     * Sets this registration's data if this registration is newly created instead of from a DB query.
     *
     * This function must be called directly after construction or this object will not be usable.
     *
     * @param \Tx_Seminars_OldModel_Event $event the seminar object (that's the seminar we would like to register for)
     * @param int $userUid UID of the FE user who wants to sign up
     * @param array $registrationData associative array with the registration data the user has just entered, may be empty
     *
     * @return void
     */
    public function setRegistrationData(\Tx_Seminars_OldModel_Event $event, $userUid, array $registrationData)
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
        $this->recordData['registered_themselves'] = $registrationData['registered_themselves'] ? 1 : 0;

        $availablePrices = $event->getAvailablePrices();
        // If no (available) price is selected, use the first price by default.
        $selectedPrice = (isset($registrationData['price']) && $event->isPriceAvailable($registrationData['price']))
            ? $registrationData['price'] : key($availablePrices);
        $this->recordData['price'] = $availablePrices[$selectedPrice]['caption'];

        $this->recordData['total_price'] = $seats * $availablePrices[$selectedPrice]['amount'];

        $this->recordData['attendees_names'] = $registrationData['attendees_names'];

        $this->recordData['kids'] = $registrationData['kids'];

        $methodOfPayment = $registrationData['method_of_payment'];
        // Auto-select the only payment method if no payment method has been
        // selected, there actually is anything to pay and only one payment
        // method is provided.
        if (!$methodOfPayment && ($this->recordData['total_price'] > 0.00) && ($event->getNumberOfPaymentMethods(
                ) == 1)) {
            $rows = \Tx_Oelib_Db::selectMultiple(
                'uid',
                'tx_seminars_payment_methods, tx_seminars_seminars_payment_methods_mm',
                'tx_seminars_payment_methods.uid = tx_seminars_seminars_payment_methods_mm.uid_foreign ' .
                'AND tx_seminars_seminars_payment_methods_mm.uid_local = ' . $event->getTopicUid() .
                \Tx_Oelib_Db::enableFields('tx_seminars_payment_methods'),
                '',
                'tx_seminars_seminars_payment_methods_mm.sorting'
            );
            $methodOfPayment = $rows[0]['uid'];
        }
        $this->recordData['method_of_payment'] = $methodOfPayment;

        $this->recordData['account_number'] = $registrationData['account_number'];
        $this->recordData['bank_code'] = $registrationData['bank_code'];
        $this->recordData['bank_name'] = $registrationData['bank_name'];
        $this->recordData['account_owner'] = $registrationData['account_owner'];

        $this->recordData['company'] = $registrationData['company'];
        $this->recordData['gender'] = $registrationData['gender'];
        $this->recordData['name'] = $registrationData['name'];
        $this->recordData['address'] = $registrationData['address'];
        $this->recordData['zip'] = $registrationData['zip'];
        $this->recordData['city'] = $registrationData['city'];
        $this->recordData['country'] = $registrationData['country'];
        $this->recordData['telephone'] = $registrationData['telephone'];
        $this->recordData['email'] = $registrationData['email'];

        $this->lodgings = (isset($registrationData['lodgings']) && is_array($registrationData['lodgings']))
            ? $registrationData['lodgings'] : [];
        $this->recordData['lodgings'] = count($this->lodgings);

        $this->foods = (isset($registrationData['foods']) && is_array($registrationData['foods']))
            ? $registrationData['foods'] : [];
        $this->recordData['foods'] = count($this->foods);

        $this->checkboxes = (isset($registrationData['checkboxes']) && is_array($registrationData['checkboxes']))
            ? $registrationData['checkboxes'] : [];
        $this->recordData['checkboxes'] = count($this->checkboxes);

        $this->recordData['interests'] = $registrationData['interests'];
        $this->recordData['expectations'] = $registrationData['expectations'];
        $this->recordData['background_knowledge'] = $registrationData['background_knowledge'];
        $this->recordData['accommodation'] = $registrationData['accommodation'];
        $this->recordData['food'] = $registrationData['food'];
        $this->recordData['known_from'] = $registrationData['known_from'];
        $this->recordData['notes'] = $registrationData['notes'];

        $this->recordData['pid'] = $this->seminar->hasAttendancesPid()
            ? $this->seminar->getAttendancesPid() : $this->getConfValueInteger('attendancesPID');

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
     *
     * @return void
     */
    protected function processAdditionalRegistrationData(array $registrationData)
    {
    }

    /**
     * Gets the number of seats that are registered with this registration.
     *
     * If no value is saved in the record, 1 will be returned.
     *
     * @return int the number of seats
     */
    public function getSeats()
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
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setSeats($seats)
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
    public function hasSeats()
    {
        return $this->hasRecordPropertyInteger('seats');
    }

    /**
     * Creates our title and writes it to $this->title.
     *
     * The title is constructed like this:
     *   Name of Attendee / Title of Seminar seminardate
     *
     * @return void
     */
    private function createTitle()
    {
        $this->recordData['title'] = $this->getUserName() .
            ' / ' . $this->getSeminarObject()->getTitle() . ', ' . $this->getSeminarObject()->getDate('-');
    }

    /**
     * Gets the complete FE user data as an array.
     *
     * The attendee's user data (from fe_users) will be written to $this->userData.
     *
     * $this->userData will be NULL if retrieving the user data fails.
     *
     * @return void
     *
     * @throws \Tx_Oelib_Exception_Database
     * @throws \Tx_Oelib_Exception_NotFound
     */
    private function retrieveUserData()
    {
        $uid = $this->getUser();
        if ($uid === 0) {
            $this->userData = null;
            return;
        }

        $dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            '*',
            'fe_users',
            'uid = ' . $uid . \Tx_Oelib_Db::enableFields('fe_users')
        );
        if ($dbResult === false) {
            throw new \Tx_Oelib_Exception_Database();
        }
        $userData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
        if ($userData === false) {
            throw new \Tx_Oelib_Exception_NotFound(
                'The FE user with the UID ' . $uid . ' could not be retrieved.',
                1390065114
            );
        }

        $this->setUserData($userData);
    }

    /**
     * Sets the data of the FE user of this registration.
     *
     * @param string[] $userData data of the front-end user, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function setUserData(array $userData)
    {
        if (empty($userData)) {
            throw new \InvalidArgumentException('$userData must not be empty.', 1333291766);
        }

        $this->userData = $userData;
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
     * @param string $key the key of the data to retrieve, need not be trimmed
     *
     * @return string the trimmed value retrieved from $this->recordData with CR replaced by LF, may be empty empty
     */
    public function getRegistrationData($key)
    {
        $trimmedKey = trim($key);

        switch ($trimmedKey) {
            case 'crdate':
                // The fallthrough is intended.
            case 'tstamp':
                $format = $this->getConfValueString('dateFormatYMD') . ' ' . $this->getConfValueString('timeFormat');
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
                $result = \strftime(
                    $this->getConfValueString('dateFormatYMD'),
                    $this->getRecordPropertyInteger($trimmedKey)
                );
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

        $carriageReturnRemoved = \str_replace(CR, LF, (string)$result);

        return \trim(\preg_replace('/\\x0a{2,}/', LF, $carriageReturnRemoved));
    }

    /**
     * Retrieves a value out of the userData array. The return value will be an
     * empty string if the key is not defined in the $this->userData array.
     *
     * If the data needs to be decoded to be readable (eg. the gender, the date
     * of birth or the status), this function will already return the clear text version.
     *
     * @param string $key key of the data to retrieve, may contain leading or trailing spaces, must not be empty
     *
     * @return string the trimmed value retrieved from $this->userData, may be empty
     */
    public function getUserData($key)
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
                $format = $this->getConfValueString('dateFormatYMD') . ' ' . $this->getConfValueString('timeFormat');
                $result = \strftime($format, (int)$rawData);
                break;
            case 'date_of_birth':
                $result = \strftime($this->getConfValueString('dateFormatYMD'), (int)$rawData);
                break;
            case 'name':
                $result = $this->getFrontEndUser()->getName();
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
    public function getUserDataAsHtml($keys, AbstractPlugin $plugin)
    {
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
     * @return int the attendee's feuser uid
     */
    public function getUser()
    {
        return $this->getRecordPropertyInteger('user');
    }

    /**
     * Gets the attendee's (real) name
     *
     * @return string the attendee's name
     */
    public function getUserName()
    {
        return $this->getUserData('name');
    }

    /**
     * Gets the attendee's name and e-mail address in the format
     * '"John Doe" <john.doe@example.com>'.
     *
     * @return string the attendee's name and e-mail address
     */
    public function getUserNameAndEmail()
    {
        return '"' . $this->getUserData('name') . '" <' . $this->getUserData('email') . '>';
    }

    /**
     * Returns the front-end user of the registration.
     *
     * @return \Tx_Seminars_Model_FrontEndUser the front-end user of the registration
     */
    public function getFrontEndUser()
    {
        /** @var \Tx_Seminars_Mapper_FrontEndUser $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class);
        return $mapper->find($this->getUser());
    }

    /**
     * Returns whether the registration has an existing front-end user.
     *
     * @return bool TRUE if the registration has an existing front-end user, FALSE otherwise
     */
    public function hasExistingFrontEndUser()
    {
        if ($this->getUser() <= 0) {
            return false;
        }

        /** @var \Tx_Seminars_Mapper_FrontEndUser $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class);

        return $mapper->existsModel($this->getUser());
    }

    /**
     * Sets the front-end user UID of the registration.
     *
     * @param int $frontEndUserUID the front-end user UID of the attendee, must be > 0
     *
     * @return void
     */
    public function setFrontEndUserUID($frontEndUserUID)
    {
        $this->setRecordPropertyInteger('user', $frontEndUserUID);
    }

    /**
     * Gets the seminar's UID.
     *
     * @return int the seminar's UID
     */
    public function getSeminar()
    {
        return $this->getRecordPropertyInteger('seminar');
    }

    /**
     * Gets the seminar to which this registration belongs.
     *
     * @return \Tx_Seminars_OldModel_Event the seminar to which this registration belongs
     */
    public function getSeminarObject()
    {
        if (!$this->seminar && $this->isOk()) {
            $seminarUid = $this->getRecordPropertyInteger('seminar');
            if (isset(self::$cachedSeminars[$seminarUid])) {
                $this->seminar = self::$cachedSeminars[$seminarUid];
            } else {
                $this->seminar = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_Event::class, $seminarUid);
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
    public function isPaid()
    {
        return $this->getRecordPropertyInteger('datepaid') > 0;
    }

    /**
     * Sets the date when this registration has been paid for.
     *
     * @param int $paymentDate
     *        the date of the payment as UNIX timestamp, must be >= 0
     *
     * @return void
     */
    public function setPaymentDateAsUnixTimestamp($paymentDate)
    {
        $this->setRecordPropertyInteger('datepaid', $paymentDate);
    }

    /**
     * Gets the attendee's special interests in the subject.
     *
     * @return string a description of the attendee's special interests (may be empty)
     */
    public function getInterests()
    {
        return $this->getRecordPropertyString('interests');
    }

    /**
     * Checks whether the attendee has stated any special interests.
     *
     * @return bool
     */
    public function hasInterests()
    {
        return $this->hasRecordPropertyString('interests');
    }

    /**
     * Gets the attendee's expectations for the event.
     *
     * @return string a description of the attendee's expectations for the
     *                event (may be empty)
     */
    public function getExpectations()
    {
        return $this->getRecordPropertyString('expectations');
    }

    /**
     * Gets the attendee's background knowledge on the subject.
     *
     * @return string a description of the attendee's background knowledge
     *                (may be empty)
     */
    public function getKnowledge()
    {
        return $this->getRecordPropertyString('background_knowledge');
    }

    /**
     * Gets where the attendee has heard about this event.
     *
     * @return string a description of where the attendee has heard about
     *                this event (may be empty)
     */
    public function getKnownFrom()
    {
        return $this->getRecordPropertyString('known_from');
    }

    /**
     * Gets text from the "additional notes" field the attendee could fill at
     * online registration.
     *
     * @return string additional notes on registration (may be empty)
     */
    public function getNotes()
    {
        return $this->getRecordPropertyString('notes');
    }

    /**
     * Gets the saved price category name and its single price, all in one long
     * string.
     *
     * @return string the saved price category name and its single price or an empty string if no price had been saved
     */
    public function getPrice()
    {
        return $this->getRecordPropertyString('price');
    }

    /**
     * Sets our price category name and its single price.
     *
     * @param string $price the price category name and its single price, may be empty
     *
     * @return void
     */
    public function setPrice($price)
    {
        $this->setRecordPropertyString('price', $price);
    }

    /**
     * Returns whether this registration has a saved price category name and its single price.
     *
     * @return bool TRUE if this registration has a price, FALSE otherwise
     */
    public function hasPrice()
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
    public function getTotalPrice()
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
     *
     * @return void
     */
    public function setTotalPrice($price)
    {
        $this->setRecordPropertyString('total_price', $price);
    }

    /**
     * Returns whether this registration has a total price.
     *
     * @return bool TRUE if this registration has a total price, FALSE otherwise
     */
    public function hasTotalPrice()
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
    public function dumpUserValues($keysList)
    {
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
            // If not, we don't use  the padding and break the line directly after the label.
            if ($value !== '') {
                $result .= \str_pad($label . ': ', $maximumLabelLength + 2, ' ') . $value . LF;
            } else {
                $result .= $label . ':' . LF;
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
    public function dumpAttendanceValues($keysList)
    {
        $keys = GeneralUtility::trimExplode(',', $keysList, true);
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
        foreach ($labels as $key => $currentLabel) {
            $value = $this->getRegistrationData($key);
            if ($value === '') {
                $result .= $currentLabel . ':' . LF;
                continue;
            }

            if (\strpos($value, LF) !== false) {
                $result .= $currentLabel . ': ' . LF;
            } else {
                $result .= \str_pad($currentLabel . ': ', $maximumLabelLength + 2, ' ');
            }
            $result .= $value . LF;
        }

        return $result;
    }

    /**
     * Gets the billing address, formatted as plain text.
     *
     * @return string the billing address
     */
    public function getBillingAddress()
    {
        /**
         * the keys of the corresponding fields and whether to add a LF after
         * the entry (instead of just a space)
         *
         * @var bool[]
         */
        $billingAddressFields = [
            'gender' => false,
            'name' => true,
            'address' => true,
            'zip' => false,
            'city' => true,
            'country' => true,
            'telephone' => true,
            'email' => true,
        ];

        $result = '';

        foreach ($billingAddressFields as $key => $useLf) {
            if ($this->hasRecordPropertyString($key)) {
                // Add labels before the phone number and the e-mail address.
                if (($key === 'telephone') || ($key === 'email')) {
                    $result .= $this->translate('label_' . $key) . ': ';
                }
                $result .= $this->getRegistrationData($key);
                if ($useLf) {
                    $result .= LF;
                } else {
                    $result .= ' ';
                }
            }
        }

        return $result;
    }

    /**
     * Retrieves the localized string corresponding to the key in the "gender" field.
     *
     * @return string the localized gender as entered for the billing address (Mr. or Mrs.)
     */
    public function getGender()
    {
        return $this->translate('label_gender.I.' . $this->getRecordPropertyInteger('gender'));
    }

    /**
     * Checks whether there are any lodging options referenced by this record.
     *
     * @return bool TRUE if at least one lodging option is referenced by this record, FALSE otherwise
     */
    public function hasLodgings()
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
    public function getLodgings()
    {
        if (!$this->hasLodgings()) {
            return '';
        }

        return $this->getMmRecords('tx_seminars_lodgings', 'tx_seminars_attendances_lodgings_mm');
    }

    /**
     * Returns the food free-text content.
     *
     * @return string
     */
    public function getFood()
    {
        return $this->getRecordPropertyString('food');
    }

    /**
     * Checks whether this registration has non-empty data in the food field.
     *
     * @return bool
     */
    public function hasFood()
    {
        return $this->hasRecordPropertyString('food');
    }

    /**
     * Returns the accommodation free-text content.
     *
     * @return string
     */
    public function getAccommodation()
    {
        return $this->getRecordPropertyString('accommodation');
    }

    /**
     * Checks whether this registration has non-empty data in the accommodation field.
     *
     * @return bool
     */
    public function hasAccommodation()
    {
        return $this->hasRecordPropertyString('accommodation');
    }

    /**
     * Checks whether there are any food options referenced by this record.
     *
     * @return bool TRUE if at least one food option is referenced by this record, FALSE otherwise
     */
    public function hasFoods()
    {
        return $this->hasRecordPropertyInteger('foods');
    }

    /**
     * Gets the selected food options separated by LF. If there is no
     * food option selected, this function will return an empty string.
     *
     * @return string the titles of the selected lodging options separated by LF or an empty string if no food option is selected
     */
    public function getFoods()
    {
        if (!$this->hasFoods()) {
            return '';
        }

        return $this->getMmRecords('tx_seminars_foods', 'tx_seminars_attendances_foods_mm');
    }

    /**
     * Checks whether any option checkboxes are referenced by this record.
     *
     * @return bool TRUE if at least one option checkbox is referenced by this record, FALSE otherwise
     */
    public function hasCheckboxes()
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
    public function getCheckboxes()
    {
        if (!$this->hasCheckboxes()) {
            return '';
        }

        return $this->getMmRecords('tx_seminars_checkboxes', 'tx_seminars_attendances_checkboxes_mm');
    }

    /**
     * Gets a LF-separated list of the titles of records referenced by this record.
     *
     * @param string $foreignTable the name of the foreign table (must not be empty), must have the fields uid and title
     * @param string $mmTable the name of the m:m table, having the fields uid_local, uid_foreign and sorting, must not be empty
     *
     * @return string the titles of the referenced records separated by LF,
     *                might be empty if no records are referenced
     */
    private function getMmRecords($foreignTable, $mmTable)
    {
        $result = '';

        $dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'title, sorting',
            $foreignTable . ', ' . $mmTable,
            'uid_local = ' . $this->getUid() . ' AND uid_foreign = uid' . \Tx_Oelib_Db::enableFields($foreignTable),
            '',
            'sorting'
        );

        if ($dbResult) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
                if ($result !== '') {
                    $result .= LF;
                }
                $result .= $row['title'];
            }
        }

        return $result;
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
     * @return bool TRUE if everything went OK, FALSE otherwise
     */
    public function commitToDb()
    {
        $this->fillEmptyDefaultFields();

        if (!parent::commitToDb()) {
            return false;
        }

        $this->recordData['uid'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
        if ($this->hasUid()) {
            $this->createMmRecords('tx_seminars_attendances_lodgings_mm', $this->lodgings);
            $this->createMmRecords('tx_seminars_attendances_foods_mm', $this->foods);
            $this->createMmRecords('tx_seminars_attendances_checkboxes_mm', $this->checkboxes);
        }

        /** @var ReferenceIndex $referenceIndex */
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);
        $referenceIndex->updateRefIndexTable('tx_seminars_attendances', $this->getUid());

        return true;
    }

    /**
     * Fills the default fields that don't have any data.
     *
     * @return void
     */
    protected function fillEmptyDefaultFields()
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
    public function isOnRegistrationQueue()
    {
        return $this->getRecordPropertyBoolean('registration_queue');
    }

    /**
     * Gets this registration's status as a localized string.
     *
     * @return string a localized version of either "waiting list" or "regular", will not be empty
     */
    public function getStatus()
    {
        $languageKey = 'label_' . ($this->isOnRegistrationQueue() ? 'waiting_list' : 'regular');

        return $this->translate($languageKey);
    }

    /**
     * Returns our attendees names.
     *
     * @return string our attendees names, will be empty if this registration has no attendees names
     */
    public function getAttendeesNames()
    {
        return $this->getRecordPropertyString('attendees_names');
    }

    /**
     * Sets our attendees names.
     *
     * @param string $attendeesNames our attendees names, may be empty
     *
     * @return void
     */
    public function setAttendeesNames($attendeesNames)
    {
        $this->setRecordPropertyString('attendees_names', $attendeesNames);
    }

    /**
     * Returns whether this registration has attendees names.
     *
     * @return bool TRUE if this registration has attendees names, FALSE otherwise
     */
    public function hasAttendeesNames()
    {
        return $this->hasRecordPropertyString('attendees_names');
    }

    /**
     * Returns our number of kids.
     *
     * @return int the number of kids, will be >= 0, will be 0 if this registration has no kids
     */
    public function getNumberOfKids()
    {
        return $this->getRecordPropertyInteger('kids');
    }

    /**
     * Sets the number of kids.
     *
     * @param int $numberOfKids the number of kids, must be >= 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setNumberOfKids($numberOfKids)
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
    public function hasKids()
    {
        return $this->hasRecordPropertyInteger('kids');
    }

    /**
     * Returns our method of payment UID.
     *
     * @return int our method of payment UID, will be >= 0, will be 0 if this registration has no method of payment
     */
    public function getMethodOfPaymentUid()
    {
        return $this->getRecordPropertyInteger('method_of_payment');
    }

    /**
     * Sets our method of payment UID.
     *
     * @param int $uid our method of payment UID, must be >= 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setMethodOfPaymentUid($uid)
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
    public function hasMethodOfPayment()
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
    public function getEnumeratedAttendeeNames($useHtml = false)
    {
        if (!$this->hasAttendeesNames() && !$this->hasRegisteredMySelf()) {
            return '';
        }

        $names = GeneralUtility::trimExplode(LF, $this->getAttendeesNames(), true);
        if ($this->hasRegisteredMySelf()) {
            $names = array_merge([$this->getFrontEndUser()->getName()], $names);
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
            $result = implode(LF, $enumeratedNames);
        }

        return $result;
    }

    /**
     * Checks whether the user has registered themselves.
     *
     * @return bool TRUE if the user registered themselves, FALSE otherwise
     */
    public function hasRegisteredMySelf()
    {
        return $this->getRecordPropertyBoolean('registered_themselves');
    }
}
