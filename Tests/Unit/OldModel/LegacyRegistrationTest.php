<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\OldModel\LegacyRegistration;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyRegistration
 */
final class LegacyRegistrationTest extends UnitTestCase
{
    /**
     * @var LegacyRegistration
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = LegacyRegistration::fromData([]);
    }

    /**
     * @test
     */
    public function isAbstractModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass(): void
    {
        $result = LegacyRegistration::fromData([]);

        self::assertInstanceOf(LegacyRegistration::class, $result);
    }

    /**
     * @test
     */
    public function getFrontEndUserWithoutUserUidReturnsNull(): void
    {
        $result = $this->subject->getFrontEndUser();

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function setFrontEndUserSetsFrontEndUser(): void
    {
        $user = new \Tx_Seminars_Model_FrontEndUser();

        $this->subject->setFrontEndUser($user);

        self::assertSame($user, $this->subject->getFrontEndUser());
    }

    /**
     * @test
     */
    public function isOnRegistrationByDefaultReturnsFalse(): void
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
    public function isOnRegistrationReturnsRegistrationQueue(): void
    {
        $subject = LegacyRegistration::fromData(['registration_queue' => 1]);

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
    public function setIsOnRegistrationQueueSetsOnRegistrationQueue(bool $value): void
    {
        $this->subject->setIsOnRegistrationQueue($value);

        self::assertSame($value, $this->subject->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function getMethodOfPaymentUidInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidReturnsMethodOfPaymentUid(): void
    {
        $uid = 42;

        $subject = LegacyRegistration::fromData(['method_of_payment' => $uid]);

        self::assertSame($uid, $subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidSetsMethodOfPaymentUid(): void
    {
        $value = 123456;
        $this->subject->setMethodOfPaymentUid($value);

        self::assertSame($value, $this->subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidCanSetMethodOfPaymentUidToZero(): void
    {
        $value = 0;
        $this->subject->setMethodOfPaymentUid($value);

        self::assertSame($value, $this->subject->getMethodOfPaymentUid());
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidNegativeValueThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setMethodOfPaymentUid(-1);
    }

    /**
     * @test
     */
    public function getUserDataForEmptyKeyReturnsEmptyString(): void
    {
        $result = $this->subject->getUserData('');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getUserDataForInexistentKeyNameReturnsEmptyString(): void
    {
        $this->subject->setUserData(['name' => 'John Doe']);

        $result = $this->subject->getUserData('foo');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function setUserDataSetsUserData(): void
    {
        $key = 'www';
        $value = 'https://www.example.com/';

        $this->subject->setUserData([$key => $value]);

        self::assertSame($value, $this->subject->getUserData($key));
    }

    /**
     * @test
     */
    public function getUserDataReturnsIntegersAsString(): void
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
    public function getUserDataForNameAssemblesNameFromSeveralSources(array $data, string $expectedName): void
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
    public function hasRegisteredThemselvesForRegisteredThemselvesByDefaultFalseReturnsFalse(): void
    {
        $result = $this->subject->hasRegisteredThemselves();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function hasRegisteredThemselvesReturnsRegisteredThemselves(): void
    {
        $subject = LegacyRegistration::fromData(['registered_themselves' => 1]);

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
    public function setRegisteredThemselvesSetsRegisteredThemselves(bool $value): void
    {
        $this->subject->setRegisteredThemselves($value);

        $result = $this->subject->hasRegisteredThemselves();

        self::assertSame($value, $result);
    }

    // Tests regarding the billing address

    /**
     * @test
     */
    public function getBillingAddressWithCompanyContainsCompany(): void
    {
        $value = 'Psijic Order';
        $subject = LegacyRegistration::fromData(['company' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithNameContainsName(): void
    {
        $value = 'Max Doe';
        $subject = LegacyRegistration::fromData(['name' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithAddressContainsAddress(): void
    {
        $value = 'Main Street 123';
        $subject = LegacyRegistration::fromData(['address' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithZipCodeContainsZipCode(): void
    {
        $value = '12345';
        $subject = LegacyRegistration::fromData(['zip' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithCityContainsCity(): void
    {
        $value = 'Big City';
        $subject = LegacyRegistration::fromData(['city' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithCountryContainsCountry(): void
    {
        $value = 'Takka-Tukka-Land';
        $subject = LegacyRegistration::fromData(['country' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }
}
