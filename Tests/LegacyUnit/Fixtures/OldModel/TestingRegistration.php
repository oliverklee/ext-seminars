<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
final class TestingRegistration extends \Tx_Seminars_OldModel_Registration
{
    /**
     * The constructor.
     *
     * @param int $registrationUid UID of the registration record, must be > 0
     */
    public function __construct(int $registrationUid = 0)
    {
        if ($registrationUid > 0) {
            $dbResult = \Tx_Oelib_Db::select(
                '*',
                $this->tableName,
                'uid = ' . $registrationUid
            );
        } else {
            $dbResult = false;
        }

        $contentObject = new ContentObjectRenderer();
        $contentObject->start([]);

        parent::__construct($contentObject, $dbResult);
    }

    /**
     * Sets the "registration_queue" field of the registration record.
     *
     * @param bool $isOnRegistrationQueueValue TRUE if the registration should be on the waiting list, FALSE otherwise
     *
     * @return void
     */
    public function setIsOnRegistrationQueue(bool $isOnRegistrationQueueValue)
    {
        $this->setRecordPropertyInteger(
            'registration_queue',
            (int)$isOnRegistrationQueueValue
        );
    }

    /**
     * Sets the payment method of this registration.
     *
     * @param int $uid the UID of the payment method to set
     *
     * @return void
     */
    public function setPaymentMethod(int $uid)
    {
        if ($uid <= 0) {
            throw new \InvalidArgumentException('Invalid payment method UID.', 1333293343);
        }

        $this->setRecordPropertyInteger(
            'method_of_payment',
            $uid
        );
    }

    /**
     * Sets the data of the FE user of this registration.
     *
     * @param array $userData data of the front-end user, may be empty
     *
     * @return void
     */
    public function setUserData(array $userData)
    {
        parent::setUserData($userData);
    }

    /**
     * Returns the content of the member variable foods.
     *
     * @return int[] the content of the member variable foods, will be empty if foods is empty
     */
    public function getFoodsData(): array
    {
        return $this->foods;
    }

    /**
     * Returns the content of the member variable lodgings.
     *
     * @return int[] the content of the member variable lodgings, will be empty if lodgings is empty
     */
    public function getLodgingsData(): array
    {
        return $this->lodgings;
    }

    /**
     * Returns the content of the member variable checkboxes.
     *
     * @return int[] the content of the member variable checkboxes, will be empty if checkboxes is empty
     */
    public function getCheckboxesData(): array
    {
        return $this->checkboxes;
    }

    /**
     * Sets the value for 'registered_themselves'.
     *
     * @param bool $registeredThemselves the value for the registered_themselves property
     *
     * @return void
     */
    public function setRegisteredThemselves(bool $registeredThemselves)
    {
        $this->setRecordPropertyBoolean('registered_themselves', $registeredThemselves);
    }
}
