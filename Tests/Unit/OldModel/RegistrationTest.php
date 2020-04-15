<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class RegistrationTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Registration
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = \Tx_Seminars_OldModel_Registration::fromData([]);
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = \Tx_Seminars_OldModel_Registration::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Registration::class, $result);
    }

    /**
     * @test
     */
    public function getFrontEndUserWithoutUserUidReturnsNull()
    {
        $result = $this->subject->getFrontEndUser();

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function setFrontEndUserSetsFrontEndUser()
    {
        $user = new \Tx_Seminars_Model_FrontEndUser();

        $this->subject->setFrontEndUser($user);

        self::assertSame($user, $this->subject->getFrontEndUser());
    }

    /**
     * @test
     */
    public function isOnRegistrationByDefaultReturnsFalse()
    {
        self::assertFalse($this->subject->isOnRegistrationQueue());

        $this->subject->setIsOnRegistrationQueue(true);
        self::assertTrue(
            $this->subject->isOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function isOnRegistrationReturnsRegistrationQueue()
    {
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['registration_queue' => 1]);

        self::assertTrue($subject->isOnRegistrationQueue());
    }

    /**
     * @return bool[][]
     */
    public function booleanDataProvider(): array
    {
        return [
            'false' => [false],
            'true' => [true],
        ];
    }

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function setIsOnRegistrationQueueSetsOnRegistrationQueue(bool $value)
    {
        $this->subject->setIsOnRegistrationQueue($value);

        self::assertSame($value, $this->subject->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function getMethodOfPaymentUidInitiallyReturnsZero()
    {
        self::assertSame(0, $this->subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidReturnsMethodOfPaymentUid()
    {
        $uid = 42;

        $subject = \Tx_Seminars_OldModel_Registration::fromData(['method_of_payment' => $uid]);

        self::assertSame($uid, $subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidSetsMethodOfPaymentUid()
    {
        $value = 123456;
        $this->subject->setMethodOfPaymentUid($value);

        self::assertSame($value, $this->subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidCanSetMethodOfPaymentUidToZero()
    {
        $value = 0;
        $this->subject->setMethodOfPaymentUid($value);

        self::assertSame($value, $this->subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidNegativeValueThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setMethodOfPaymentUid(-1);
    }

    /**
     * @test
     */
    public function getUserDataForEmptyKeyReturnsEmptyString()
    {
        $result = $this->subject->getUserData('');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getUserDataForInexistentKeyNameReturnsEmptyString()
    {
        $this->subject->setUserData(['name' => 'John Doe']);

        $result = $this->subject->getUserData('foo');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function setUserDataSetsUserData()
    {
        $key = 'www';
        $value = 'https://www.example.com/';

        $this->subject->setUserData([$key => $value]);

        self::assertSame($value, $this->subject->getUserData($key));
    }

    /**
     * @test
     */
    public function getUserDataReturnsIntegersAsString()
    {
        $key = 'pid';
        $value = 42;

        $this->subject->setUserData([$key => $value]);

        self::assertSame((string)$value, $this->subject->getUserData($key));
    }

    /**
     * @return array[]
     */
    public function nameUserDataDataProvider(): array
    {
        return [
            'user name only' => [['username' => 'max', 'name' => ''], 'max'],
            'first name' => [['username' => 'max', 'name' => '', 'first_name' => 'Max'], 'Max'],
            'last name' => [['username' => 'max', 'name' => '', 'last_name' => 'Caulfield'], 'Caulfield'],
            'first and last name' => [
                ['username' => 'max', 'name' => '', 'first_name' => 'Max', 'last_name' => 'Caulfield'],
                'Max Caulfield',
            ],
            'full only' => [['username' => 'max', 'name' => 'Max Caulfield'], 'Max Caulfield'],
        ];
    }

    /**
     * @test
     *
     * @param string[] $data
     * @param string $expectedName
     *
     * @dataProvider nameUserDataDataProvider
     */
    public function getUserDataForNameAssemblesNameFromSeveralSources(array $data, string $expectedName)
    {
        $this->subject->setUserData($data);

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData($data);
        $this->subject->setFrontEndUser($user);

        self::assertSame($expectedName, $this->subject->getUserData('name'));
    }

    /**
     * @test
     */
    public function hasRegisteredThemselvesForRegisteredThemselvesByDefaultFalseReturnsFalse()
    {
        $result = $this->subject->hasRegisteredThemselves();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function hasRegisteredThemselvesReturnsRegisteredThemselves()
    {
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['registered_themselves' => 1]);

        $result = $subject->hasRegisteredThemselves();

        self::assertTrue($result);
    }

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function setRegisteredThemselvesSetsRegisteredThemselves(bool $value)
    {
        $this->subject->setRegisteredThemselves($value);

        $result = $this->subject->hasRegisteredThemselves();

        self::assertSame($value, $result);
    }

    /*
     * Tests regarding the billing address
     */

    /**
     * @test
     */
    public function getBillingAddressWithNameContainsName()
    {
        $value = 'Max Doe';
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['name' => $value]);

        $result = $subject->getBillingAddress();

        self::assertContains($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithAddressContainsAddress()
    {
        $value = 'Main Street 123';
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['address' => $value]);

        $result = $subject->getBillingAddress();

        self::assertContains($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithZipCodeContainsZipCode()
    {
        $value = '12345';
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['zip' => $value]);

        $result = $subject->getBillingAddress();

        self::assertContains($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithCityContainsCity()
    {
        $value = 'Big City';
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['city' => $value]);

        $result = $subject->getBillingAddress();

        self::assertContains($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithCountryContainsCountry()
    {
        $value = 'Takka-Tukka-Land';
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['country' => $value]);

        $result = $subject->getBillingAddress();

        self::assertContains($value, $result);
    }
}
