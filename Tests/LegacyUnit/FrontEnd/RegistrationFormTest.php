<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Session\FakeSession;
use OliverKlee\Oelib\Session\Session;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class RegistrationFormTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Seminars_FrontEnd_RegistrationForm
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var FakeSession
     */
    private $session = null;

    /**
     * @var int the UID of the event the fixture relates to
     */
    private $seminarUid = 0;

    /**
     * @var \Tx_Seminars_OldModel_Event
     */
    private $seminar = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        $this->session = new FakeSession();
        Session::setInstance(Session::TYPE_USER, $this->session);

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configuration = new Configuration();
        $configuration->setAsString('currency', 'EUR');
        $configurationRegistry->set('plugin.tx_seminars', $configuration);
        $configurationRegistry->set('plugin.tx_staticinfotables_pi1', new Configuration());

        $this->seminar = new \Tx_Seminars_OldModel_Event(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                ['payment_methods' => '1']
            )
        );
        $this->seminarUid = $this->seminar->getUid();

        $this->subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'pageToShowAfterUnregistrationPID' => $frontEndPageUid,
                'sendParametersToThankYouAfterRegistrationPageUrl' => 1,
                'thankYouAfterRegistrationPID' => $frontEndPageUid,
                'sendParametersToPageToShowAfterUnregistrationUrl' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'logOutOneTimeAccountsAfterRegistration' => 1,
                'showRegistrationFields' => 'registered_themselves,attendees_names',
                'showFeUserFieldsInRegistrationForm' => 'name,email',
                'showFeUserFieldsInRegistrationFormWithLabel' => 'email',
                'maximumBookableSeats' => 10,
                'form.' => [
                    'unregistration.' => [],
                    'registration.' => [
                        'step1.' => [],
                        'step2.' => [],
                    ],
                ],
            ],
            $this->getFrontEndController()->cObj
        );
        $this->subject->setAction('register');
        $this->subject->setSeminar($this->seminar);
        $this->subject->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    // Test concerning getAllFeUserData

    /**
     * @test
     */
    public function getAllFeUserContainsNonEmptyNameOfFrontEndUser()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertStringContainsString(
            'John Doe',
            $this->subject->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserContainsLabelForNonEmptyEmailOfFrontEndUser()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['email' => 'john@example.com']);

        self::assertStringContainsString(
            'mail',
            $this->subject->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserDoesNotContainEmptyLinesForMissingCompanyName()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertNotRegExp(
            '/<br *\\/>\\s*<br *\\/>/',
            $this->subject->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserContainsNoUnreplacedMarkers()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertStringNotContainsString(
            '###',
            $this->subject->getAllFeUserData()
        );
    }

    ///////////////////////////////////////
    // Tests concerning saveDataToSession
    ///////////////////////////////////////

    public function testSaveDataToSessionCanWriteEmptyZipToUserSession()
    {
        $this->subject->processRegistration(['zip' => '']);

        self::assertEquals(
            '',
            $this->session->getAsString('tx_seminars_registration_editor_zip')
        );
    }

    public function testSaveDataToSessionCanWriteNonEmptyZipToUserSession()
    {
        $this->subject->processRegistration(['zip' => '12345']);

        self::assertEquals(
            '12345',
            $this->session->getAsString('tx_seminars_registration_editor_zip')
        );
    }

    public function testSaveDataToSessionCanOverwriteNonEmptyZipWithEmptyZipInUserSession()
    {
        $this->session->setAsString(
            'tx_seminars_registration_editor_zip',
            '12345'
        );
        $this->subject->processRegistration(['zip' => '']);

        self::assertEquals(
            '',
            $this->session->getAsString('tx_seminars_registration_editor_zip')
        );
    }

    /**
     * @test
     */
    public function saveDataToSessionCanStoreCompanyInSession()
    {
        $this->subject->processRegistration(['company' => 'foo inc.']);

        self::assertEquals(
            'foo inc.',
            $this->session->getAsString(
                'tx_seminars_registration_editor_company'
            )
        );
    }

    /**
     * @test
     */
    public function saveDataToSessionCanStoreNameInSession()
    {
        $this->subject->processRegistration(['name' => 'foo']);

        self::assertEquals(
            'foo',
            $this->session->getAsString(
                'tx_seminars_registration_editor_name'
            )
        );
    }

    /////////////////////////////////////////////
    // Tests concerning retrieveDataFromSession
    /////////////////////////////////////////////

    public function testRetrieveDataFromSessionWithUnusedKeyReturnsEmptyString()
    {
        self::assertEquals(
            '',
            $this->subject->retrieveDataFromSession(['key' => 'foo'])
        );
    }

    public function testRetrieveDataFromSessionWithKeySetInUserSessionReturnsDataForThatKey()
    {
        $this->session->setAsString(
            'tx_seminars_registration_editor_zip',
            '12345'
        );

        self::assertEquals(
            '12345',
            $this->subject->retrieveDataFromSession(['key' => 'zip'])
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning populateListPaymentMethods
    ////////////////////////////////////////////////

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function populateListPaymentMethodsDoesNotCrash()
    {
        $this->subject->populateListPaymentMethods();
    }

    public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsOneItem()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid
        );

        self::assertCount(
            1,
            $this->subject->populateListPaymentMethods()
        );
    }

    public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsThisMethodsTitle()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'foo']
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid
        );

        $paymentMethods = $this->subject->populateListPaymentMethods();

        self::assertContains(
            'foo',
            $paymentMethods[0]['caption']
        );
    }

    public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsThisMethodsUid()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid
        );

        $paymentMethods = $this->subject->populateListPaymentMethods();

        self::assertEquals(
            $paymentMethodUid,
            $paymentMethods[0]['value']
        );
    }

    public function testPopulateListPaymentMethodsForEventWithTwoPaymentMethodsReturnsBothPaymentMethods()
    {
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $this->testingFramework->createRecord('tx_seminars_payment_methods')
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $this->testingFramework->createRecord('tx_seminars_payment_methods')
        );

        self::assertCount(
            2,
            $this->subject->populateListPaymentMethods()
        );
    }

    ////////////////////////////////////
    // Tests concerning getStepCounter
    ////////////////////////////////////

    /**
     * @test
     */
    public function getStepCounterReturnsNumberOfCurrentPageIfCurrentPageNumberIsLowerThanNumberOfLastPage()
    {
        $this->subject->setConfigurationValue(
            'numberOfFirstRegistrationPage',
            1
        );
        $this->subject->setConfigurationValue(
            'numberOfLastRegistrationPage',
            2
        );

        $this->subject->setPage(['next_page' => 0]);

        self::assertStringContainsString(
            '1',
            $this->subject->getStepCounter()
        );
    }

    /**
     * @test
     */
    public function getStepCounterReturnsNumberOfLastRegistrationPage()
    {
        $this->subject->setConfigurationValue(
            'numberOfFirstRegistrationPage',
            1
        );
        $this->subject->setConfigurationValue(
            'numberOfLastRegistrationPage',
            2
        );
        $this->subject->setPage(['next_page' => 0]);

        self::assertStringContainsString(
            '2',
            $this->subject->getStepCounter()
        );
    }

    /**
     * @test
     */
    public function getStepCounterForNumberAboveLastRegistrationPageReturnsNumberOfLastRegistrationPageAsCurrentPage()
    {
        $this->subject->setConfigurationValue(
            'numberOfFirstRegistrationPage',
            1
        );
        $this->subject->setConfigurationValue(
            'numberOfLastRegistrationPage',
            2
        );

        $this->subject->setPage(['next_page' => 5]);

        self::assertEquals(
            \sprintf($this->getLanguageService()->getLL('label_step_counter'), 2, 2),
            $this->subject->getStepCounter()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning populateListCountries().
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListCountriesWithLanguageSetToDefaultNotContainsEnglishCountryNameForGermany()
    {
        self::assertNotContains(
            ['caption' => 'Germany', 'value' => 'Germany'],
            $this->subject->populateListCountries()
        );
    }

    /**
     * @test
     */
    public function populateListCountriesContainsLocalCountryNameForGermany()
    {
        self::assertContains(
            ['caption' => 'Deutschland', 'value' => 'Deutschland'],
            $this->subject->populateListCountries()
        );
    }

    // Tests concerning getFeUserData().

    /**
     * @test
     */
    public function getFeUserDataWithKeyCountryAndNoCountrySetReturnsDefaultCountrySetViaTypoScriptSetup()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        /** @var Configuration $configuration */
        $configuration = ConfigurationRegistry::get('plugin.tx_staticinfotables_pi1');
        $configuration->setAsString('countryCode', 'DEU');

        self::assertEquals(
            'Deutschland',
            $this->subject->getFeUserData(['key' => 'country'])
        );
    }

    /**
     * @test
     */
    public function getFeUserDataWithKeyCountryAndStaticInfoCountrySetReturnsStaticInfoCountry()
    {
        if (!ExtensionManagementUtility::isLoaded('sr_feuser_register')) {
            self::markTestSkipped('This test only is available is sr_feuser_register is installed.');
        }

        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['static_info_country' => 'GBR']
        );

        self::assertEquals(
            'United Kingdom',
            $this->subject->getFeUserData(['key' => 'country'])
        );
    }

    /**
     * @test
     */
    public function getFeUserDataWithKeyCountryAndCountrySetReturnsCountry()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['country' => 'Taka-Tuka-Land']
        );

        self::assertEquals(
            'Taka-Tuka-Land',
            $this->subject->getFeUserData(['key' => 'country'])
        );
    }

    ////////////////////////////////////////
    // Tests concerning isFormFieldEnabled
    ////////////////////////////////////////

    /**
     * Data provider that returns the keys of all available form fields.
     *
     * @return array[] two-dimensional array with the inner array being:
     *               [key] string: the form field key
     *               [self-contained] boolean: whether the field is visible
     *                                if no other fields are visible
     *
     * @see isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField
     * @see isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields
     */
    public function formFieldsDataProvider(): array
    {
        return [
            'step_counter' => [
                'key' => 'step_counter',
                'self-contained' => true,
            ],
            'price' => [
                'key' => 'price',
                'self-contained' => true,
            ],
            'method_of_payment' => [
                'key' => 'method_of_payment',
                'self-contained' => false,
            ],
            'account_number' => [
                'key' => 'account_number',
                'self-contained' => false,
            ],
            'bank_code' => [
                'key' => 'bank_code',
                'self-contained' => false,
            ],
            'bank_name' => [
                'key' => 'bank_name',
                'self-contained' => false,
            ],
            'account_owner' => [
                'key' => 'account_owner',
                'self-contained' => false,
            ],
            'billing_address' => [
                'key' => 'billing_address',
                'self-contained' => false,
            ],
            'company' => [
                'key' => 'company',
                'self-contained' => true,
            ],
            'gender' => [
                'key' => 'gender',
                'self-contained' => true,
            ],
            'name' => [
                'key' => 'name',
                'self-contained' => true,
            ],
            'address' => [
                'key' => 'address',
                'self-contained' => true,
            ],
            'zip' => [
                'key' => 'zip',
                'self-contained' => true,
            ],
            'city' => [
                'key' => 'city',
                'self-contained' => true,
            ],
            'country' => [
                'key' => 'country',
                'self-contained' => true,
            ],
            'telephone' => [
                'key' => 'telephone',
                'self-contained' => true,
            ],
            'email' => [
                'key' => 'email',
                'self-contained' => true,
            ],
            'interests' => [
                'key' => 'interests',
                'self-contained' => true,
            ],
            'expectations' => [
                'key' => 'expectations',
                'self-contained' => true,
            ],
            'background_knowledge' => [
                'key' => 'background_knowledge',
                'self-contained' => true,
            ],
            'accommodation' => [
                'key' => 'accommodation',
                'self-contained' => true,
            ],
            'food' => [
                'key' => 'food',
                'self-contained' => true,
            ],
            'known_from' => [
                'key' => 'known_from',
                'self-contained' => true,
            ],
            'seats' => [
                'key' => 'seats',
                'self-contained' => true,
            ],
            'registered_themselves' => [
                'key' => 'registered_themselves',
                'self-contained' => true,
            ],
            'attendees_names' => [
                'key' => 'attendees_names',
                'self-contained' => true,
            ],
            'kids' => [
                'key' => 'kids',
                'self-contained' => true,
            ],
            'lodgings' => [
                'key' => 'lodgings',
                'self-contained' => false,
            ],
            'foods' => [
                'key' => 'foods',
                'self-contained' => false,
            ],
            'checkboxes' => [
                'key' => 'checkboxes',
                'self-contained' => false,
            ],
            'notes' => [
                'key' => 'notes',
                'self-contained' => true,
            ],
            'total_price' => [
                'key' => 'total_price',
                'self-contained' => true,
            ],
            'feuser_data' => [
                'key' => 'feuser_data',
                'self-contained' => true,
            ],
            'registration_data' => [
                'key' => 'registration_data',
                'self-contained' => true,
            ],
            'terms' => [
                'key' => 'terms',
                'self-contained' => true,
            ],
            'terms_2' => [
                'key' => 'terms_2',
                'self-contained' => false,
            ],
        ];
    }

    /**
     * @test
     *
     * @param string $key the key of the field to check for, must not be empty
     *
     * @dataProvider formFieldsDataProvider
     */
    public function isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField(string $key)
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => ''],
            $this->getFrontEndController()->cObj
        );

        /** @var \Tx_Seminars_OldModel_Event&MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
        $subject->setSeminar($event);

        self::assertFalse(
            $subject->isFormFieldEnabled($key)
        );
    }

    /**
     * @test
     *
     * @param string $key the key of the field to check for, must not be empty
     * @param bool $isSelfContained
     *        whether the field will be visible if no other fields are enabled
     *        and the event has no special features enabled
     *
     * @dataProvider formFieldsDataProvider
     */
    public function isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields(
        string $key,
        bool $isSelfContained
    ) {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => $key],
            $this->getFrontEndController()->cObj
        );
        /** @var \Tx_Seminars_OldModel_Event&MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
        $subject->setSeminar($event);

        self::assertEquals(
            $isSelfContained,
            $subject->isFormFieldEnabled($key)
        );
    }

    /**
     * @test
     */
    public function isFormFieldEnabledForEnabledRegisteredThemselvesFieldOnlyReturnsFalseForMoreSeats()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => 'registered_themselves'],
            $this->getFrontEndController()->cObj
        );

        self::assertFalse(
            $subject->isFormFieldEnabled('more_seats')
        );
    }

    /**
     * @test
     */
    public function isFormFieldEnabledForEnabledCompanyFieldReturnsTrueForBillingAddress()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => 'company, billing_address'],
            $this->getFrontEndController()->cObj
        );

        self::assertTrue(
            $subject->isFormFieldEnabled('billing_address')
        );
    }

    ////////////////////////////////////////////////////////
    // Tests concerning getAdditionalRegisteredPersonsData
    ////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataForNoDataReturnsEmptyArray()
    {
        self::assertEquals(
            [],
            $this->subject->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataForNoEmptyReturnsEmptyArray()
    {
        $this->subject->setFakedFormValue('structured_attendees_names', '');

        self::assertEquals(
            [],
            $this->subject->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataCanReturnDataOfOnePerson()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );

        self::assertEquals(
            [
                [
                    0 => 'John',
                    1 => 'Doe',
                    2 => 'Key account',
                    3 => 'john@example.com',
                ],
            ],
            $this->subject->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataCanReturnDataOfTwoPersons()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"],' .
            '["Jane", "Doe", "Sales", "jane@example.com"]]'
        );

        self::assertEquals(
            [
                [
                    0 => 'John',
                    1 => 'Doe',
                    2 => 'Key account',
                    3 => 'john@example.com',
                ],
                [
                    0 => 'Jane',
                    1 => 'Doe',
                    2 => 'Sales',
                    3 => 'jane@example.com',
                ],
            ],
            $this->subject->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataForNonArrayDataReturnsEmptyArray()
    {
        $this->subject->setFakedFormValue('structured_attendees_names', '"Foo"');

        self::assertEquals(
            [],
            $this->subject->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataForInvalidJsonReturnsEmptyArray()
    {
        $this->subject->setFakedFormValue('structured_attendees_names', 'argh');

        self::assertEquals(
            [],
            $this->subject->getAdditionalRegisteredPersonsData()
        );
    }

    // Tests concerning the validation of the number of persons to register

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForEmptyFormDataReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfEnteredPersons()
        );
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForNoSelfRegistrationReturnsZero()
    {
        $this->subject->setFakedFormValue('registered_themselves', 0);

        self::assertSame(0, $this->subject->getNumberOfEnteredPersons());
    }

    /**
     * @return int[][]
     */
    public function registerThemselvesDataProvider(): array
    {
        return [
            '0' => [0],
            '1' => [1],
        ];
    }

    /**
     * @test
     *
     * @param int $configurationValue
     *
     * @dataProvider registerThemselvesDataProvider
     */
    public function getNumberOfEnteredPersonsForFieldHiddenReturnsValueFromConfiguration(int $configurationValue)
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'showRegistrationFields' => 'seats',
                'form.' => [
                    'registration.' => [
                        'step1.' => ['seats' => []],
                        'step2.' => [],
                    ],
                ],
                'registerThemselvesByDefaultForHiddenCheckbox' => (string)$configurationValue,
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setAction('register');
        $subject->setTestMode();

        self::assertSame($configurationValue, $subject->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForSelfRegistrationReturnsOne()
    {
        $this->subject->setFakedFormValue('registered_themselves', 1);

        self::assertSame(1, $this->subject->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForOneAdditionalAndNoSelfRegistrationPersonReturnsOne()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );

        self::assertSame(1, $this->subject->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForTwoAdditionalPersonsAndNoSelfRegistrationReturnsTwo()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"],' .
            '["Jane", "Doe", "Sales", "jane@example.com"]]'
        );

        self::assertSame(2, $this->subject->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForSelfRegistrationAndOneAdditionalPersonReturnsTwo()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );
        $this->subject->setFakedFormValue('registered_themselves', 1);

        self::assertSame(2, $this->subject->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForZeroSeatsReturnsFalse()
    {
        $this->subject->setFakedFormValue('seats', 0);

        self::assertFalse(
            $this->subject->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForNegativeSeatsReturnsFalse()
    {
        $this->subject->setFakedFormValue('seats', -1);

        self::assertFalse(
            $this->subject->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForOnePersonAndOneSeatReturnsTrue()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getNumberOfEnteredPersons')
            ->willReturn(1);
        $subject->setTestMode();

        $subject->setFakedFormValue('seats', 1);

        self::assertTrue(
            $subject->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForOnePersonAndTwoSeatsReturnsFalse()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getNumberOfEnteredPersons')
            ->willReturn(1);
        $subject->setTestMode();

        $subject->setFakedFormValue('seats', 2);

        self::assertFalse(
            $subject->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForTwoPersonsAndOneSeatReturnsFalse()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getNumberOfEnteredPersons')
            ->willReturn(2);
        $subject->setTestMode();

        $subject->setFakedFormValue('seats', 1);

        self::assertFalse(
            $subject->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForTwoPersonsAndTwoSeatsReturnsTrue()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getNumberOfEnteredPersons')
            ->willReturn(2);
        $subject->setTestMode();

        $subject->setFakedFormValue('seats', 2);

        self::assertTrue(
            $subject->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndOneSeatReturnsEmptyString()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );
        $this->subject->setFakedFormValue('seats', 1);

        self::assertEquals(
            '',
            $this->subject->getMessageForSeatsNotMatchingRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndTwoSeatsReturnsMessage()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );
        $this->subject->setFakedFormValue('seats', 2);

        self::assertEquals(
            $this->getLanguageService()->getLL('message_lessAttendeesThanSeats'),
            $this->subject->getMessageForSeatsNotMatchingRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function getMessageForSeatsNotMatchingRegisteredPersonsForTwoPersonsAndOneSeatReturnsMessage()
    {
        $this->subject->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"],' .
            '["Jane", "Doe", "Sales", "jane@example.com"]]'
        );
        $this->subject->setFakedFormValue('seats', 1);

        self::assertEquals(
            $this->getLanguageService()->getLL('message_moreAttendeesThanSeats'),
            $this->subject->getMessageForSeatsNotMatchingRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForAttendeesNamesHiddenAndManySeatsReturnsTrue()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'showRegistrationFields' => 'seats',
                'form.' => [
                    'registration.' => [
                        'step1.' => ['seats' => []],
                        'step2.' => [],
                    ],
                ],
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setAction('register');
        $subject->setTestMode();

        $subject->setFakedFormValue('seats', 8);

        self::assertTrue(
            $subject->validateNumberOfRegisteredPersons()
        );
    }

    /////////////////////////////////////////////////////////////
    // Tests concerning validateAdditionalPersonsEmailAddresses
    /////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForDisabledFrontEndUserCreationReturnsTrue()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn([]);
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            false
        );

        self::assertTrue(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForDisabledFormFieldReturnsTrue()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(false);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn([]);
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            true
        );

        self::assertTrue(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForNoPersonsReturnsTrue()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn([]);
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            true
        );

        self::assertTrue(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForOneValidEmailAddressReturnsTrue()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn(
                [['John', 'Doe', '', 'john@example.com']]
            );
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            true
        );

        self::assertTrue(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForOneInvalidEmailAddressReturnsFalse()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn(
                [['John', 'Doe', '', 'potato salad!']]
            );
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            true
        );

        self::assertFalse(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForOneEmptyAddressReturnsFalse()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn(
                [['John', 'Doe', '', '']]
            );
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            true
        );

        self::assertFalse(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForOneMissingAddressReturnsFalse()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn(
                [['John', 'Doe', '']]
            );
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            true
        );

        self::assertFalse(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEmailAddressesForOneValidAndOneInvalidEmailAddressReturnsFalse()
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled']
        );
        $subject->method('isFormFieldEnabled')
            ->willReturn(true);
        $subject->method('getAdditionalRegisteredPersonsData')
            ->willReturn(
                [['John', 'Doe', '', 'john@example.com'], ['Jane', 'Doe', '', 'tomato salad!']]
            );
        $subject->setTestMode();
        $subject->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers',
            true
        );

        self::assertFalse(
            $subject->validateAdditionalPersonsEmailAddresses()
        );
    }

    /////////////////////////////////////////////////
    // Tests concerning getPreselectedPaymentMethod
    /////////////////////////////////////////////////

    /**
     * @test
     */
    public function getPreselectedPaymentMethodForOnePaymentMethodReturnsItsUid()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'foo']
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid
        );

        self::assertEquals(
            $paymentMethodUid,
            $this->subject->getPreselectedPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function getPreselectedPaymentMethodForTwoNotSelectedPaymentMethodsReturnsZero()
    {
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $this->testingFramework->createRecord('tx_seminars_payment_methods')
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $this->testingFramework->createRecord('tx_seminars_payment_methods')
        );

        self::assertEquals(
            0,
            $this->subject->getPreselectedPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function getPreselectedPaymentMethodForTwoPaymentMethodsOneSelectedOneNotReturnsUidOfSelectedRecord()
    {
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $this->testingFramework->createRecord('tx_seminars_payment_methods')
        );
        $selectedPaymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $selectedPaymentMethodUid
        );

        $this->session->setAsInteger(
            'tx_seminars_registration_editor_method_of_payment',
            $selectedPaymentMethodUid
        );

        self::assertEquals(
            $selectedPaymentMethodUid,
            $this->subject->getPreselectedPaymentMethod()
        );
    }

    /////////////////////////////////////////
    // Tests concerning getRegistrationData
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getRegistrationDataForDisabledPaymentMethodFieldReturnsEmptyString()
    {
        $selectedPaymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'payment foo']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $selectedPaymentMethodUid
        );
        $this->subject->setFakedFormValue(
            'method_of_payment',
            $selectedPaymentMethodUid
        );

        self::assertEquals(
            '',
            $this->subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForEnabledPriceFieldReturnsSelectedPriceValue()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'price',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['price_regular' => 42]
        );
        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('price', 'price_regular');

        self::assertStringContainsString(
            '42',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataHtmlspecialcharsInterestsField()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'interests',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('interests', 'A, B & C');

        self::assertStringContainsString(
            'A, B &amp; C',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataReplacesCarriageReturnInInterestsFieldWithBr()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'interests',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('interests', "Love\rPeace");

        self::assertStringContainsString(
            'Love<br />Peace',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataCanContainAttendeesNames()
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('attendees_names', 'John Doe');

        self::assertStringContainsString(
            'John Doe',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForAttendeesNamesAndThemselvesSelectedContainsUserName()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe']
        );

        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('attendees_names', 'John Doe');
        $subject->setFakedFormValue('registered_themselves', '1');

        self::assertStringContainsString(
            'Jane Doe',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForAttendeesNamesEnabledAndThemselvesNotSelectedNotContainsUserName()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe']
        );

        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('attendees_names', 'John Doe');
        $subject->setFakedFormValue('registered_themselves', '');

        self::assertStringNotContainsString(
            'Jane Doe',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsDisabledNotContainsTitle()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe', 'title' => 'facility manager']
        );

        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => false,
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('registered_themselves', '1');

        self::assertStringNotContainsString(
            'facility manager',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsEnabledContainsTitle()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe', 'title' => 'facility manager']
        );

        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => true,
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('registered_themselves', '1');

        self::assertStringContainsString(
            'facility manager',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsDisabledNotContainsEmailAddress()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe', 'email' => 'jane@example.com']
        );

        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => false,
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('registered_themselves', '1');

        self::assertStringNotContainsString(
            'jane@example.com',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsEnabledContainsEmailAddress()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe', 'email' => 'jane@example.com']
        );

        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => true,
            ],
            $this->getFrontEndController()->cObj
        );
        $subject->setTestMode();

        $event = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $subject->setSeminar($event);
        $subject->setFakedFormValue('registered_themselves', '1');

        self::assertStringContainsString(
            'jane@example.com',
            $subject->getAllRegistrationDataForConfirmation()
        );
    }

    // Tests concerning getSeminar and getEvent

    /**
     * @test
     */
    public function getSeminarReturnsSeminarFromSetSeminar()
    {
        self::assertSame(
            $this->seminar,
            $this->subject->getSeminar()
        );
    }

    /**
     * @test
     */
    public function getEventReturnsEventWithSeminarUid()
    {
        $event = $this->subject->getEvent();
        self::assertInstanceOf(
            \Tx_Seminars_Model_Event::class,
            $event
        );

        self::assertSame(
            $this->seminarUid,
            $event->getUid()
        );
    }

    // Tests concerning populateSeats

    /**
     * @test
     */
    public function populateSeatsForOneVacancyReturnsItemOfOne()
    {
        $event = $this->subject->getEvent();
        $event->setMaximumAttendees(1);
        self::assertSame(1, $event->getVacancies());

        $result = $this->subject->populateSeats();

        self::assertSame(
            [['caption' => 1, 'value' => 1]],
            $result
        );
    }

    /**
     * @test
     */
    public function populateSeatsForLessVacanciesThanMaximumSeatsReturnsVacancyValues()
    {
        $event = $this->subject->getEvent();
        $event->setMaximumAttendees(9);
        self::assertSame(9, $event->getVacancies());

        $result = $this->subject->populateSeats();

        self::assertSame(
            [
                ['caption' => 1, 'value' => 1],
                ['caption' => 2, 'value' => 2],
                ['caption' => 3, 'value' => 3],
                ['caption' => 4, 'value' => 4],
                ['caption' => 5, 'value' => 5],
                ['caption' => 6, 'value' => 6],
                ['caption' => 7, 'value' => 7],
                ['caption' => 8, 'value' => 8],
                ['caption' => 9, 'value' => 9],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function populateSeatsForAsManyVacanciesAsMaximumSeatsReturnsMaximumValues()
    {
        $event = $this->subject->getEvent();
        $event->setMaximumAttendees(10);
        self::assertSame(10, $event->getVacancies());

        $result = $this->subject->populateSeats();

        self::assertSame(
            [
                ['caption' => 1, 'value' => 1],
                ['caption' => 2, 'value' => 2],
                ['caption' => 3, 'value' => 3],
                ['caption' => 4, 'value' => 4],
                ['caption' => 5, 'value' => 5],
                ['caption' => 6, 'value' => 6],
                ['caption' => 7, 'value' => 7],
                ['caption' => 8, 'value' => 8],
                ['caption' => 9, 'value' => 9],
                ['caption' => 10, 'value' => 10],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function populateSeatsForMoreVacanciesThanMaximumSeatsReturnsMaximumValues10FromConfiguration()
    {
        $event = $this->subject->getEvent();
        $event->setMaximumAttendees(11);
        self::assertSame(11, $event->getVacancies());

        $result = $this->subject->populateSeats();

        self::assertSame(
            [
                ['caption' => 1, 'value' => 1],
                ['caption' => 2, 'value' => 2],
                ['caption' => 3, 'value' => 3],
                ['caption' => 4, 'value' => 4],
                ['caption' => 5, 'value' => 5],
                ['caption' => 6, 'value' => 6],
                ['caption' => 7, 'value' => 7],
                ['caption' => 8, 'value' => 8],
                ['caption' => 9, 'value' => 9],
                ['caption' => 10, 'value' => 10],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function populateSeatsForMoreVacanciesThanMaximumSeatsReturnsMaximumValues3FromConfiguration()
    {
        $event = $this->subject->getEvent();
        $event->setMaximumAttendees(11);
        self::assertSame(11, $event->getVacancies());

        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(
            ['maximumBookableSeats' => 3],
            $this->getFrontEndController()->cObj
        );
        $subject->setAction('register');
        $subject->setSeminar($this->seminar);
        $subject->setTestMode();

        $result = $subject->populateSeats();

        self::assertSame(
            [
                ['caption' => 1, 'value' => 1],
                ['caption' => 2, 'value' => 2],
                ['caption' => 3, 'value' => 3],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function populateSeatsForNoVacanciesReturnsMaximumValues()
    {
        $event = $this->subject->getEvent();
        $event->setMaximumAttendees(1);
        $event->setOfflineRegistrations(1);
        self::assertSame(0, $event->getVacancies());

        $result = $this->subject->populateSeats();

        self::assertSame(
            [
                ['caption' => 1, 'value' => 1],
                ['caption' => 2, 'value' => 2],
                ['caption' => 3, 'value' => 3],
                ['caption' => 4, 'value' => 4],
                ['caption' => 5, 'value' => 5],
                ['caption' => 6, 'value' => 6],
                ['caption' => 7, 'value' => 7],
                ['caption' => 8, 'value' => 8],
                ['caption' => 9, 'value' => 9],
                ['caption' => 10, 'value' => 10],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function populateSeatsForUnlimitedRegistrationsReturnsMaximumValues()
    {
        $event = $this->subject->getEvent();
        self::assertTrue($event->hasVacancies());

        $result = $this->subject->populateSeats();

        self::assertSame(
            [
                ['caption' => 1, 'value' => 1],
                ['caption' => 2, 'value' => 2],
                ['caption' => 3, 'value' => 3],
                ['caption' => 4, 'value' => 4],
                ['caption' => 5, 'value' => 5],
                ['caption' => 6, 'value' => 6],
                ['caption' => 7, 'value' => 7],
                ['caption' => 8, 'value' => 8],
                ['caption' => 9, 'value' => 9],
                ['caption' => 10, 'value' => 10],
            ],
            $result
        );
    }
}
