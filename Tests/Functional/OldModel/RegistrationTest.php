<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;

/**
 * @covers \Tx_Seminars_OldModel_Registration
 */
final class RegistrationTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var string
     */
    const DATE_FORMAT = '%d.%m.%Y';

    /**
     * @var string
     */
    const TIME_FORMAT = '%H:%M';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_OldModel_Registration
     */
    private $subject = null;

    /**
     * @var array<string, string>
     */
    private const CONFIGURATION = [
        'dateFormatYMD' => self::DATE_FORMAT,
        'timeFormat' => self::TIME_FORMAT,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeBackEndLanguage();
        $configuration = new DummyConfiguration(self::CONFIGURATION);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        $this->subject = new \Tx_Seminars_OldModel_Registration();
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();
    }

    /**
     * @test
     */
    public function fromUidMapsDataFromDatabase(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $subject = \Tx_Seminars_OldModel_Registration::fromUid(1);

        self::assertSame(4, $subject->getSeats());
        self::assertSame(1, $subject->getUser());
        self::assertSame(1, $subject->getSeminar());
        self::assertTrue($subject->isPaid());
        self::assertSame('coding', $subject->getInterests());
        self::assertSame('good coffee', $subject->getExpectations());
        self::assertSame('latte art', $subject->getKnowledge());
        self::assertSame('word of mouth', $subject->getKnownFrom());
        self::assertSame('Looking forward to it!', $subject->getNotes());
        self::assertSame('Standard: 500.23€', $subject->getPrice());
        self::assertSame('vegetarian', $subject->getFood());
        self::assertSame('at home', $subject->getAccommodation());
        self::assertSame('Max Moe', $subject->getAttendeesNames());
        self::assertSame(2, $subject->getNumberOfKids());
        self::assertTrue($subject->hasRegisteredThemselves());
    }

    /**
     * @test
     */
    public function mapsFrontEndUser(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $subject = \Tx_Seminars_OldModel_Registration::fromUid(1);

        $user = $subject->getFrontEndUser();

        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $user);
        self::assertSame(1, $user->getUid());
    }

    /**
     * @test
     */
    public function mapsEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations.xml');

        $subject = \Tx_Seminars_OldModel_Registration::fromUid(1);

        $event = $subject->getSeminarObject();

        self::assertInstanceOf(\Tx_Seminars_OldModel_Event::class, $event);
        self::assertSame(1, $event->getUid());
    }

    // Tests concerning getUserData

    /**
     * @test
     */
    public function getUserDataForNoGroupReturnsEmptyString(): void
    {
        $this->subject->setUserData(['usergroup' => '']);

        $result = $this->subject->getUserData('usergroup');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getUserDataForInexistentGroupReturnsEmptyString(): void
    {
        $this->subject->setUserData(['usergroup' => '1234']);

        $result = $this->subject->getUserData('usergroup');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getUserDataForOneGroupReturnsGroupTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations/Users.xml');
        $this->subject->setUserData(['usergroup' => '1']);

        $result = $this->subject->getUserData('usergroup');

        self::assertSame('Group 1', $result);
    }

    /**
     * @test
     */
    public function getUserDataForTwoGroupReturnsCommaSeparatedTitlesInGivenOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations/Users.xml');
        $this->subject->setUserData(['usergroup' => '2,1']);

        $result = $this->subject->getUserData('usergroup');

        self::assertSame('Group 2, Group 1', $result);
    }

    // Tests concerning dumpUserValues

    /**
     * @test
     */
    public function dumpUserValuesCanDumpName(): void
    {
        $name = 'Max Doe';
        $userData = ['name' => $name];
        $this->subject->setUserData($userData);

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData($userData);
        $this->subject->setFrontEndUser($user);

        $result = $this->subject->dumpUserValues('name');

        self::assertStringContainsString($name, $result);
    }

    /**
     * @test
     */
    public function dumpUserValuesForSpaceAroundCommaCanDumpTwoFields(): void
    {
        $name = 'Max Doe';
        $email = 'max@example.com';
        $userData = ['name' => $name, 'email' => $email];
        $this->subject->setUserData($userData);

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData($userData);
        $this->subject->setFrontEndUser($user);

        $result = $this->subject->dumpUserValues('name , email');

        self::assertStringContainsString($name, $result);
        self::assertStringContainsString($email, $result);
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsLabel(): void
    {
        $email = 'max@example.com';
        $userData = ['email' => $email];
        $this->subject->setUserData($userData);

        $result = $this->subject->dumpUserValues('email');

        self::assertStringContainsString($this->getLanguageService()->getLL('label_email'), $result);
    }

    /**
     * @test
     */
    public function dumpUserValuesForSpaceAroundCommaCanHaveTwoLabels(): void
    {
        $name = 'Max Doe';
        $email = 'max@example.com';
        $userData = ['name' => $name, 'email' => $email];
        $this->subject->setUserData($userData);

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData($userData);
        $this->subject->setFrontEndUser($user);

        $result = $this->subject->dumpUserValues('name , email');

        self::assertStringContainsString($this->getLanguageService()->getLL('label_name'), $result);
        self::assertStringContainsString($this->getLanguageService()->getLL('label_email'), $result);
    }

    /**
     * @test
     */
    public function dumpUserValuesDoesNotContainRawLabelNameAsLabelForPid(): void
    {
        $this->subject->setUserData(['pid' => 1234]);

        $result = $this->subject->dumpUserValues('pid');

        self::assertStringNotContainsString('label_pid', $result);
    }

    /**
     * @test
     */
    public function dumpUserValuesCanContainNonRegisteredField(): void
    {
        $this->subject->setUserData(['is_dummy_record' => true]);

        $result = $this->subject->dumpUserValues('is_dummy_record');

        self::assertStringContainsString('Is_dummy_record: 1', $result);
    }

    /**
     * @return string[][]
     */
    public function userDateAndTimeFieldsDataProvider(): array
    {
        $fields = [
            'crdate',
            'tstamp',
        ];

        return $this->expandForDataProvider($fields);
    }

    /**
     * @test
     *
     * @param string $fieldName
     *
     * @dataProvider userDateAndTimeFieldsDataProvider
     */
    public function dumpUserValuesCanDumpDateAndTimeField(string $fieldName): void
    {
        $value = 1579816569;
        $this->subject->setUserData([$fieldName => $value]);

        $result = $this->subject->dumpUserValues($fieldName);

        $expected = \strftime(self::DATE_FORMAT, $value) . ' ' . \strftime(self::TIME_FORMAT, $value);
        self::assertStringContainsString($expected, $result);
    }

    /**
     * @return string[][]
     */
    public function userDateFieldsDataProvider(): array
    {
        $fields = [
            'date_of_birth',
        ];

        return $this->expandForDataProvider($fields);
    }

    /**
     * @test
     *
     * @param string $fieldName
     *
     * @dataProvider userDateFieldsDataProvider
     */
    public function dumpUserValuesCanDumpDate(string $fieldName): void
    {
        $value = 1579816569;
        $this->subject->setUserData([$fieldName => $value]);

        $result = $this->subject->dumpUserValues($fieldName);

        $expected = \strftime(self::DATE_FORMAT, $value);
        self::assertContains($expected, $result);
    }

    /**
     * @return string[][]
     */
    public function dumpableUserFieldsDataProvider(): array
    {
        $fields = [
            'uid',
            'username',
            'name',
            'first_name',
            'middle_name',
            'last_name',
            'address',
            'telephone',
            'fax',
            'email',
            'crdate',
            'title',
            'zip',
            'city',
            'country',
            'www',
            'company',
            'pseudonym',
            'gender',
            'date_of_birth',
            'mobilephone',
            'comments',
        ];

        return $this->expandForDataProvider($fields);
    }

    /**
     * @param string[] $fields
     *
     * @return string[][]
     */
    private function expandForDataProvider(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = [$field];
        }

        return $result;
    }

    /**
     * @test
     *
     * @param string $fieldName
     *
     * @dataProvider dumpableUserFieldsDataProvider
     */
    public function dumpUserValuesCreatesNoDoubleColonsAfterLabel(string $fieldName): void
    {
        $userData = [$fieldName => '1234 some value'];
        $this->subject->setUserData($userData);

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData($userData);
        $this->subject->setFrontEndUser($user);

        $result = $this->subject->dumpUserValues($fieldName);

        self::assertStringNotContainsString('::', $result);
    }

    /**
     * @return string[][]
     */
    public function dumpableStringUserFieldsDataProvider(): array
    {
        $fields = [
            'username',
            'name',
            'first_name',
            'middle_name',
            'last_name',
            'address',
            'telephone',
            'fax',
            'email',
            'title',
            'zip',
            'city',
            'country',
            'www',
            'company',
            'pseudonym',
            'mobilephone',
            'comments',
        ];

        return $this->expandForDataProvider($fields);
    }

    /**
     * @test
     *
     * @param string $fieldName
     *
     * @dataProvider dumpableStringUserFieldsDataProvider
     */
    public function dumpUserValuesCanDumpStringValues(string $fieldName): void
    {
        $value = 'some value';
        $userData = [$fieldName => $value];
        $this->subject->setUserData($userData);

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData($userData);
        $this->subject->setFrontEndUser($user);

        $result = $this->subject->dumpUserValues($fieldName);

        self::assertStringContainsString($value, $result);
    }

    /**
     * @return string[][]
     */
    public function dumpableIntegerUserFieldsDataProvider(): array
    {
        $fields = [
            'uid',
            'pid',
        ];

        return $this->expandForDataProvider($fields);
    }

    /**
     * @test
     *
     * @param string $fieldName
     *
     * @dataProvider dumpableIntegerUserFieldsDataProvider
     */
    public function dumpUserValuesCanDumpIntegerValues(string $fieldName): void
    {
        $value = 1234;
        $userData = [$fieldName => $value];
        $this->subject->setUserData($userData);

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData($userData);
        $this->subject->setFrontEndUser($user);

        $result = $this->subject->dumpUserValues($fieldName);

        self::assertStringContainsString((string)$value, $result);
    }

    /**
     * @return int[][]
     */
    public function genderDataProvider(): array
    {
        return [
            'male' => [0],
            'female' => [1],
        ];
    }

    /**
     * @test
     *
     * @param int $value
     *
     * @dataProvider genderDataProvider
     */
    public function dumpUserValuesCanDumpGender(int $value): void
    {
        $userData = ['gender' => $value];
        $this->subject->setUserData($userData);

        $result = $this->subject->dumpUserValues('gender');

        self::assertStringContainsString($this->getLanguageService()->getLL('label_gender.I.' . $value), $result);
    }

    /**
     * @test
     */
    public function dumpUserValuesForOneGroupDumpsGroupTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations/Users.xml');
        $this->subject->setUserData(['usergroup' => '1']);

        $result = $this->subject->dumpUserValues('usergroup');

        self::assertStringContainsString('Group 1', $result);
    }

    /**
     * @test
     */
    public function dumpUserValuesForTwoGroupsDumpsGroupTitlesInGivenOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Registrations/Users.xml');
        $this->subject->setUserData(['usergroup' => '2,1']);

        $result = $this->subject->dumpUserValues('usergroup');

        self::assertStringContainsString('Group 2, Group 1', $result);
    }

    // Tests regarding the billing address

    /**
     * @test
     */
    public function getBillingAddressWithGenderMaleContainsLabelForGenderMale(): void
    {
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['gender' => 0]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($this->getLanguageService()->getLL('label_gender.I.0'), $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithGenderFemaleContainsLabelForGenderFemale(): void
    {
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['gender' => 1]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($this->getLanguageService()->getLL('label_gender.I.1'), $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithTelephoneNumberContainsTelephoneNumber(): void
    {
        $value = '01234-56789';
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['telephone' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }

    /**
     * @test
     */
    public function getBillingAddressWithEmailAddressContainsEmailAddress(): void
    {
        $value = 'max@example.com';
        $subject = \Tx_Seminars_OldModel_Registration::fromData(['email' => $value]);

        $result = $subject->getBillingAddress();

        self::assertStringContainsString($value, $result);
    }
}
