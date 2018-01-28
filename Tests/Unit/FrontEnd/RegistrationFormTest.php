<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Philipp Kitzberger <philipp@cron-it.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_RegistrationFormTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_FrontEnd_RegistrationForm
     */
    protected $fixture = null;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var Tx_Oelib_FakeSession a fake session
     */
    protected $session = null;

    /**
     * @var int the UID of the event the fixture relates to
     */
    protected $seminarUid = 0;

    /**
     * @var Tx_Seminars_OldModel_Event
     */
    protected $seminar = null;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        $this->session = new Tx_Oelib_FakeSession();
        Tx_Oelib_Session::setInstance(Tx_Oelib_Session::TYPE_USER, $this->session);

        $configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
        $configuration = new Tx_Oelib_Configuration();
        $configuration->setAsString('currency', 'EUR');
        $configurationRegistry->set('plugin.tx_seminars', $configuration);
        $configurationRegistry->set(
            'plugin.tx_staticinfotables_pi1', new Tx_Oelib_Configuration()
        );

        $this->seminar = new Tx_Seminars_OldModel_Event($this->testingFramework->createRecord(
            'tx_seminars_seminars', ['payment_methods' => '1']
        ));
        $this->seminarUid = $this->seminar->getUid();

        $this->fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
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
                    'registration.'    => [
                        'step1.' => [],
                        'step2.' => [],
                    ],
                ],
            ],
            $GLOBALS['TSFE']->cObj
        );
        $this->fixture->setAction('register');
        $this->fixture->setSeminar($this->seminar);
        $this->fixture->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    ////////////////////////////////////////////////////////////////
    // Tests for getting the page-to-show-after-unregistration URL
    ////////////////////////////////////////////////////////////////

    public function testGetPageToShowAfterUnregistrationUrlReturnsUrlStartingWithHttp()
    {
        self::assertRegExp(
            '/^http:\/\/./',
            $this->fixture->getPageToShowAfterUnregistrationUrl()
        );
    }

    public function testGetPageToShowAfterUnregistrationUrlReturnsUrlWithEncodedBrackets()
    {
        self::assertContains(
            '%5BshowUid%5D',
            $this->fixture->getPageToShowAfterUnregistrationUrl()
        );

        self::assertNotContains(
            '[showUid]',
            $this->fixture->getPageToShowAfterUnregistrationUrl()
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests for getting the thank-you-after-registration URL
    ///////////////////////////////////////////////////////////

    public function testGetThankYouAfterRegistrationUrlReturnsUrlStartingWithHttp()
    {
        self::assertRegExp(
            '/^http:\/\/./',
            $this->fixture->getThankYouAfterRegistrationUrl()
        );
    }

    public function testGetThankYouAfterRegistrationUrlReturnsUrlWithEncodedBrackets()
    {
        self::assertContains(
            '%5BshowUid%5D',
            $this->fixture->getThankYouAfterRegistrationUrl()
        );

        self::assertNotContains(
            '[showUid]',
            $this->fixture->getThankYouAfterRegistrationUrl()
        );
    }

    public function testGetThankYouAfterRegistrationUrlLeavesUserLoggedInByDefault()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $this->fixture->getThankYouAfterRegistrationUrl();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    public function testGetThankYouAfterRegistrationUrlWithOneTimeAccountMarkerInUserSessionLogsOutUser()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->session->setAsBoolean('onetimeaccount', true);

        $this->fixture->getThankYouAfterRegistrationUrl();

        self::assertFalse(
            $this->testingFramework->isLoggedIn()
        );
    }

    /*
     * Test concerning getAllFeUserData
     */

    /**
     * @test
     */
    public function getAllFeUserContainsNonEmptyNameOfFrontEndUser()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertContains(
            'John Doe',
            $this->fixture->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserContainsLabelForNonEmptyEmailOfFrontEndUser()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['email' => 'john@example.com']);

        self::assertContains(
            'mail',
            $this->fixture->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserDoesNotContainEmptyLinesForMissingCompanyName()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertNotRegExp(
            '/<br *\\/>\s*<br *\\/>/',
            $this->fixture->getAllFeUserData()
        );
    }

    /**
     * @test
     */
    public function getAllFeUserContainsNoUnreplacedMarkers()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['name' => 'John Doe']);

        self::assertNotContains(
            '###',
            $this->fixture->getAllFeUserData()
        );
    }

    ///////////////////////////////////////
    // Tests concerning saveDataToSession
    ///////////////////////////////////////

    public function testSaveDataToSessionCanWriteEmptyZipToUserSession()
    {
        $this->fixture->processRegistration(['zip' => '']);

        self::assertEquals(
            '',
            $this->session->getAsString('tx_seminars_registration_editor_zip')
        );
    }

    public function testSaveDataToSessionCanWriteNonEmptyZipToUserSession()
    {
        $this->fixture->processRegistration(['zip' => '12345']);

        self::assertEquals(
            '12345',
            $this->session->getAsString('tx_seminars_registration_editor_zip')
        );
    }

    public function testSaveDataToSessionCanOverwriteNonEmptyZipWithEmptyZipInUserSession()
    {
        $this->session->setAsString(
            'tx_seminars_registration_editor_zip', '12345'
        );
        $this->fixture->processRegistration(['zip' => '']);

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
        $this->fixture->processRegistration(['company' => 'foo inc.']);

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
        $this->fixture->processRegistration(['name' => 'foo']);

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
            $this->fixture->retrieveDataFromSession(['key' => 'foo'])
        );
    }

    public function testRetrieveDataFromSessionWithKeySetInUserSessionReturnsDataForThatKey()
    {
        $this->session->setAsString(
            'tx_seminars_registration_editor_zip', '12345'
        );

        self::assertEquals(
            '12345',
            $this->fixture->retrieveDataFromSession(['key' => 'zip'])
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning populateListPaymentMethods
    ////////////////////////////////////////////////

    public function testPopulateListPaymentMethodsDoesNotCrash()
    {
        $this->fixture->populateListPaymentMethods();
    }

    public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsOneItem()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid,
            'payment_methods'
        );

        self::assertEquals(
            1,
            count($this->fixture->populateListPaymentMethods())
        );
    }

    public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsThisMethodsTitle()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods', ['title' => 'foo']
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid,
            'payment_methods'
        );

        $paymentMethods = $this->fixture->populateListPaymentMethods();

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
            $paymentMethodUid,
            'payment_methods'
        );

        $paymentMethods = $this->fixture->populateListPaymentMethods();

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
            $this->testingFramework->createRecord('tx_seminars_payment_methods'),
            'payment_methods'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $this->testingFramework->createRecord('tx_seminars_payment_methods'),
            'payment_methods'
        );

        self::assertEquals(
            2,
            count($this->fixture->populateListPaymentMethods())
        );
    }

    ////////////////////////////////////
    // Tests concerning getStepCounter
    ////////////////////////////////////

    public function testGetStepCounterReturnsNumberOfCurrentPageIfCurrentPageNumberIsLowerThanNumberOfLastPage()
    {
        $this->fixture->setConfigurationValue(
            'numberOfFirstRegistrationPage',
            1
        );
        $this->fixture->setConfigurationValue(
            'numberOfLastRegistrationPage',
            2
        );

        $this->fixture->setPage(['next_page' => 0]);

        self::assertContains(
            '1',
            $this->fixture->getStepCounter()
        );
    }

    public function testGetStepCounterReturnsNumberOfLastRegistrationPage()
    {
        $this->fixture->setConfigurationValue(
            'numberOfFirstRegistrationPage',
            1
        );
        $this->fixture->setConfigurationValue(
            'numberOfLastRegistrationPage',
            2
        );
        $this->fixture->setPage(['next_page' => 0]);

        self::assertContains(
            '2',
            $this->fixture->getStepCounter()
        );
    }

    public function testGetStepCounterReturnsNumberOfLastRegistrationPageAsCurrentPageIfPageNumberIsAboveLastRegistrationPage()
    {
        $this->fixture->setConfigurationValue(
            'numberOfFirstRegistrationPage',
            1
        );
        $this->fixture->setConfigurationValue(
            'numberOfLastRegistrationPage',
            2
        );

        $this->fixture->setPage(['next_page' => 5]);

        self::assertEquals(
            sprintf($this->fixture->translate('label_step_counter'), 2, 2),
            $this->fixture->getStepCounter()
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
        $backUpLanguage = $GLOBALS['LANG'];
        $GLOBALS['LANG'] = new LanguageService();
        $GLOBALS['LANG']->init('default');

        self::assertNotContains(
            ['caption' => 'Germany', 'value' => 'Germany'],
            $this->fixture->populateListCountries()
        );

        $GLOBALS['LANG'] = $backUpLanguage;
    }

    /**
     * @test
     */
    public function populateListCountriesContainsLocalCountryNameForGermany()
    {
        self::assertContains(
            ['caption' => 'Deutschland', 'value' => 'Deutschland'],
            $this->fixture->populateListCountries()
        );
    }

    //////////////////////////////////////
    // Tests concerning getFeUserData().
    //////////////////////////////////////

    /**
     * @test
     */
    public function getFeUserDataWithKeyCountryAndNoCountrySetReturnsDefaultCountrySetViaTypoScriptSetup()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_staticinfotables_pi1')->
            setAsString('countryCode', 'DEU');

        self::assertEquals(
            'Deutschland',
            $this->fixture->getFeUserData(['key' => 'country'])
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
            '', ['static_info_country' => 'GBR']
        );

        self::assertEquals(
            'United Kingdom',
            $this->fixture->getFeUserData(['key' => 'country'])
        );
    }

    /**
     * @test
     */
    public function getFeUserDataWithKeyCountryAndCountrySetReturnsCountry()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '', ['country' => 'Taka-Tuka-Land']
        );

        self::assertEquals(
            'Taka-Tuka-Land',
            $this->fixture->getFeUserData(['key' => 'country'])
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
    public function formFieldsDataProvider()
    {
        return [
            'step_counter' => [
                'key' => 'step_counter', 'self-contained' => true,
            ],
            'price' => [
                'key' => 'price', 'self-contained' => true,
            ],
            'method_of_payment' => [
                'key' => 'method_of_payment', 'self-contained' => false,
            ],
            'account_number' => [
                'key' => 'account_number', 'self-contained' => false,
            ],
            'bank_code' => [
                'key' => 'bank_code', 'self-contained' => false,
            ],
            'bank_name' => [
                'key' => 'bank_name', 'self-contained' => false,
            ],
            'account_owner' => [
                'key' => 'account_owner', 'self-contained' => false,
            ],
            'billing_address' => [
                'key' => 'billing_address', 'self-contained' => false,
            ],
            'company' => [
                'key' => 'company', 'self-contained' => true,
            ],
            'gender' => [
                'key' => 'gender', 'self-contained' => true,
            ],
            'name' => [
                'key' => 'name', 'self-contained' => true,
            ],
            'address' => [
                'key' => 'address', 'self-contained' => true,
            ],
            'zip' => [
                'key' => 'zip', 'self-contained' => true,
            ],
            'city' => [
                'key' => 'city', 'self-contained' => true,
            ],
            'country' => [
                'key' => 'country', 'self-contained' => true,
            ],
            'telephone' => [
                'key' => 'telephone', 'self-contained' => true,
            ],
            'email' => [
                'key' => 'email', 'self-contained' => true,
            ],
            'interests' => [
                'key' => 'interests', 'self-contained' => true,
            ],
            'expectations' => [
                'key' => 'expectations', 'self-contained' => true,
            ],
            'background_knowledge' => [
                'key' => 'background_knowledge', 'self-contained' => true,
            ],
            'accommodation' => [
                'key' => 'accommodation', 'self-contained' => true,
            ],
            'food' => [
                'key' => 'food', 'self-contained' => true,
            ],
            'known_from' => [
                'key' => 'known_from', 'self-contained' => true,
            ],
            'seats' => [
                'key' => 'seats', 'self-contained' => true,
            ],
            'registered_themselves' => [
                'key' => 'registered_themselves', 'self-contained' => true,
            ],
            'attendees_names' => [
                'key' => 'attendees_names', 'self-contained' => true,
            ],
            'kids' => [
                'key' => 'kids', 'self-contained' => true,
            ],
            'lodgings' => [
                'key' => 'lodgings', 'self-contained' => false,
            ],
            'foods' => [
                'key' => 'foods', 'self-contained' => false,
            ],
            'checkboxes' => [
                'key' => 'checkboxes', 'self-contained' => false,
            ],
            'notes' => [
                'key' => 'notes', 'self-contained' => true,
            ],
            'total_price' => [
                'key' => 'total_price', 'self-contained' => true,
            ],
            'feuser_data' => [
                'key' => 'feuser_data', 'self-contained' => true,
            ],
            'registration_data' => [
                'key' => 'registration_data', 'self-contained' => true,
            ],
            'terms' => [
                'key' => 'terms', 'self-contained' => true,
            ],
            'terms_2' => [
                'key' => 'terms_2', 'self-contained' => false,
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
    public function isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField(
        $key
    ) {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => ''],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setSeminar($this->getMock(Tx_Seminars_OldModel_Event::class, [], [], '', false));

        self::assertFalse(
            $fixture->isFormFieldEnabled($key)
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
     *
     * @dataProvider formFieldsDataProvider
     */
    public function isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields(
        $key, $isSelfContained
    ) {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => $key],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setSeminar($this->getMock(Tx_Seminars_OldModel_Event::class, [], [], '', false));

        self::assertEquals(
            $isSelfContained,
            $fixture->isFormFieldEnabled($key)
        );
    }

    /**
     * @test
     */
    public function isFormFieldEnabledForEnabledRegisteredThemselvesFieldOnlyReturnsFalseForMoreSeats()
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => 'registered_themselves'],
            $GLOBALS['TSFE']->cObj
        );

        self::assertFalse(
            $fixture->isFormFieldEnabled('more_seats')
        );
    }

    /**
     * @test
     */
    public function isFormFieldEnabledForEnabledCompanyFieldReturnsTrueForBillingAddress()
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            ['showRegistrationFields' => 'company, billing_address'],
            $GLOBALS['TSFE']->cObj
        );

        self::assertTrue(
            $fixture->isFormFieldEnabled('billing_address')
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
            $this->fixture->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataForNoEmptyReturnsEmptyArray()
    {
        $this->fixture->setFakedFormValue('structured_attendees_names', '');

        self::assertEquals(
            [],
            $this->fixture->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataCanReturnDataOfOnePerson()
    {
        $this->fixture->setFakedFormValue(
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
            $this->fixture->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataCanReturnDataOfTwoPersons()
    {
        $this->fixture->setFakedFormValue(
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
            $this->fixture->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataForNonArrayDataReturnsEmptyArray()
    {
        $this->fixture->setFakedFormValue('structured_attendees_names', '"Foo"');

        self::assertEquals(
            [],
            $this->fixture->getAdditionalRegisteredPersonsData()
        );
    }

    /**
     * @test
     */
    public function getAdditionalRegisteredPersonsDataForInvalidJsonReturnsEmptyArray()
    {
        $this->fixture->setFakedFormValue('structured_attendees_names', 'argh');

        self::assertEquals(
            [],
            $this->fixture->getAdditionalRegisteredPersonsData()
        );
    }

    /*
     * Tests concerning the validation of the number of persons to register
     */

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForEmptyFormDataReturnsZero()
    {
        self::assertSame(
            0,
            $this->fixture->getNumberOfEnteredPersons()
        );
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForNoSelfRegistrationReturnsZero()
    {
        $this->fixture->setFakedFormValue('registered_themselves', 0);

        self::assertSame(0, $this->fixture->getNumberOfEnteredPersons());
    }

    /**
     * @return int[][]
     */
    public function registerThemselvesDataProvider()
    {
        return [
            '0' => [0],
            '1' => [1]
        ];
    }

    /**
     * @test
     * @param bool $configurationValue
     * @dataProvider registerThemselvesDataProvider
     */
    public function getNumberOfEnteredPersonsForFieldHiddenReturnsValueFromConfiguration($configurationValue)
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'showRegistrationFields' => 'seats',
                'form.' => [
                    'registration.'    => [
                        'step1.' => ['seats' => []],
                        'step2.' => [],
                    ],
                ],
                'registerThemselvesByDefaultForHiddenCheckbox' => (string)$configurationValue,
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setAction('register');
        $fixture->setTestMode();

        self::assertSame($configurationValue, $fixture->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForSelfRegistrationReturnsOne()
    {
        $this->fixture->setFakedFormValue('registered_themselves', 1);

        self::assertSame(1, $this->fixture->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForOneAdditionalAndNoSelfRegistrationPersonReturnsOne()
    {
        $this->fixture->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );

        self::assertSame(1, $this->fixture->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForTwoAdditionalPersonsAndNoSelfRegistrationReturnsTwo()
    {
        $this->fixture->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"],' .
                '["Jane", "Doe", "Sales", "jane@example.com"]]'
        );

        self::assertSame(2, $this->fixture->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function getNumberOfEnteredPersonsForSelfRegistrationAndOneAdditionalPersonReturnsTwo()
    {
        $this->fixture->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );
        $this->fixture->setFakedFormValue('registered_themselves', 1);

        self::assertSame(2, $this->fixture->getNumberOfEnteredPersons());
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForZeroSeatsReturnsFalse()
    {
        $this->fixture->setFakedFormValue('seats', 0);

        self::assertFalse(
            $this->fixture->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForNegativeSeatsReturnsFalse()
    {
        $this->fixture->setFakedFormValue('seats', -1);

        self::assertFalse(
            $this->fixture->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForOnePersonAndOneSeatReturnsTrue()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getNumberOfEnteredPersons')
            ->will(self::returnValue(1));
        $fixture->setTestMode();

        $fixture->setFakedFormValue('seats', 1);

        self::assertTrue(
            $fixture->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForOnePersonAndTwoSeatsReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getNumberOfEnteredPersons')
            ->will(self::returnValue(1));
        $fixture->setTestMode();

        $fixture->setFakedFormValue('seats', 2);

        self::assertFalse(
            $fixture->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForTwoPersonsAndOneSeatReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getNumberOfEnteredPersons')
            ->will(self::returnValue(2));
        $fixture->setTestMode();

        $fixture->setFakedFormValue('seats', 1);

        self::assertFalse(
            $fixture->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForTwoPersonsAndTwoSeatsReturnsTrue()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getNumberOfEnteredPersons', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getNumberOfEnteredPersons')
            ->will(self::returnValue(2));
        $fixture->setTestMode();

        $fixture->setFakedFormValue('seats', 2);

        self::assertTrue(
            $fixture->validateNumberOfRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndOneSeatReturnsEmptyString()
    {
        $this->fixture->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );
        $this->fixture->setFakedFormValue('seats', 1);

        self::assertEquals(
            '',
            $this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndTwoSeatsReturnsMessage()
    {
        $this->fixture->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"]]'
        );
        $this->fixture->setFakedFormValue('seats', 2);

        self::assertEquals(
            $this->fixture->translate('message_lessAttendeesThanSeats'),
            $this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function getMessageForSeatsNotMatchingRegisteredPersonsForTwoPersonsAndOneSeatReturnsMessage()
    {
        $this->fixture->setFakedFormValue(
            'structured_attendees_names',
            '[["John", "Doe", "Key account", "john@example.com"],' .
                '["Jane", "Doe", "Sales", "jane@example.com"]]'
        );
        $this->fixture->setFakedFormValue('seats', 1);

        self::assertEquals(
            $this->fixture->translate('message_moreAttendeesThanSeats'),
            $this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
        );
    }

    /**
     * @test
     */
    public function validateNumberOfRegisteredPersonsForAttendeesNamesHiddenAndManySeatsReturnsTrue()
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'showRegistrationFields' => 'seats',
                'form.' => [
                    'registration.'    => [
                        'step1.' => ['seats' => []],
                        'step2.' => [],
                    ],
                ],
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setAction('register');
        $fixture->setTestMode();

        $fixture->setFakedFormValue('seats', 8);

        self::assertTrue(
            $fixture->validateNumberOfRegisteredPersons()
        );
    }

    /////////////////////////////////////////////////////////////
    // Tests concerning validateAdditionalPersonsEMailAddresses
    /////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForDisabledFrontEndUserCreationReturnsTrue()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue([]));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', false
        );

        self::assertTrue(
            $fixture->validateAdditionalPersonsEMailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForDisabledFormFieldReturnsTrue()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(false));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue([]));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', true
        );

        self::assertTrue(
            $fixture->validateAdditionalPersonsEMailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForNoPersonsReturnsTrue()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue([]));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', true
        );

        self::assertTrue(
            $fixture->validateAdditionalPersonsEMailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForOneValidEMailAddressReturnsTrue()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue(
                [['John', 'Doe', '', 'john@example.com']]
            ));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', true
        );

        self::assertTrue(
            $fixture->validateAdditionalPersonsEMailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForOneInvalidEMailAddressReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue(
                [['John', 'Doe', '', 'potato salad!']]
            ));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', true
        );

        self::assertFalse(
            $fixture->validateAdditionalPersonsEMailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForOneEmptyAddressReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue(
                [['John', 'Doe', '', '']]
            ));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', true
        );

        self::assertFalse(
            $fixture->validateAdditionalPersonsEMailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForOneMissingAddressReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue(
                [['John', 'Doe', '']]
            ));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', true
        );

        self::assertFalse(
            $fixture->validateAdditionalPersonsEMailAddresses()
        );
    }

    /**
     * @test
     */
    public function validateAdditionalPersonsEMailAddressesForOneValidAndOneInvalidEMailAddressReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_RegistrationForm::class,
            ['getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'],
            [], '', false
        );
        $fixture->expects(self::any())->method('isFormFieldEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
            ->will(self::returnValue(
                [
                    ['John', 'Doe', '', 'john@example.com'],
                    ['Jane', 'Doe', '', 'tomato salad!'],
                ]
            ));
        $fixture->setTestMode();
        $fixture->setConfigurationValue(
            'createAdditionalAttendeesAsFrontEndUsers', true
        );

        self::assertFalse(
            $fixture->validateAdditionalPersonsEMailAddresses()
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
            'tx_seminars_payment_methods', ['title' => 'foo']
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid,
            'payment_methods'
        );

        self::assertEquals(
            $paymentMethodUid,
            $this->fixture->getPreselectedPaymentMethod()
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
            $this->testingFramework->createRecord('tx_seminars_payment_methods'),
            'payment_methods'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $this->testingFramework->createRecord('tx_seminars_payment_methods'),
            'payment_methods'
        );

        self::assertEquals(
            0,
            $this->fixture->getPreselectedPaymentMethod()
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
            $this->testingFramework->createRecord('tx_seminars_payment_methods'),
            'payment_methods'
        );
        $selectedPaymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $selectedPaymentMethodUid,
            'payment_methods'
        );

        $this->session->setAsInteger(
            'tx_seminars_registration_editor_method_of_payment', $selectedPaymentMethodUid
        );

        self::assertEquals(
            $selectedPaymentMethodUid,
            $this->fixture->getPreselectedPaymentMethod()
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
            'tx_seminars_payment_methods', ['title' => 'payment foo']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $selectedPaymentMethodUid,
            'payment_methods'
        );
        $this->fixture->setFakedFormValue(
            'method_of_payment', $selectedPaymentMethodUid
        );

        self::assertEquals(
            '',
            $this->fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForEnabledPriceFieldReturnsSelectedPriceValue()
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'price',
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['price_regular' => 42]
        );
        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('price', 42);

        self::assertContains(
            '42',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataHtmlspecialcharsInterestsField()
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'interests',
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('interests', 'A, B & C');

        self::assertContains(
            'A, B &amp; C',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataReplacesCarriageReturnInInterestsFieldWithBr()
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'interests',
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('interests', 'Love' . CR . 'Peace');

        self::assertContains(
            'Love<br />Peace',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataCanContainAttendeesNames()
    {
        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names',
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('attendees_names', 'John Doe');

        self::assertContains(
            'John Doe',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForAttendeesNamesAndThemselvesSelectedContainsUserName()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '', ['name' => 'Jane Doe']
        );

        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('attendees_names', 'John Doe');
        $fixture->setFakedFormValue('registered_themselves', '1');

        self::assertContains(
            'Jane Doe',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForAttendeesNamesEnabledAndThemselvesNotSelectedNotContainsUserName()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '', ['name' => 'Jane Doe']
        );

        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('attendees_names', 'John Doe');
        $fixture->setFakedFormValue('registered_themselves', '');

        self::assertNotContains(
            'Jane Doe',
            $fixture->getAllRegistrationDataForConfirmation()
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

        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => false,
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('registered_themselves', '1');

        self::assertNotContains(
            'facility manager',
            $fixture->getAllRegistrationDataForConfirmation()
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

        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => true,
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('registered_themselves', '1');

        self::assertContains(
            'facility manager',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsDisabledNotContainsEMailAddress()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe', 'email' => 'jane@example.com']
        );

        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => false,
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('registered_themselves', '1');

        self::assertNotContains(
            'jane@example.com',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsEnabledContainsEMailAddress()
    {
        $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'Jane Doe', 'email' => 'jane@example.com']
        );

        $fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'showRegistrationFields' => 'attendees_names,registered_themselves',
                'createAdditionalAttendeesAsFrontEndUsers' => true,
            ],
            $GLOBALS['TSFE']->cObj
        );
        $fixture->setTestMode();

        $event = new Tx_Seminars_OldModel_Event($this->seminarUid);
        $fixture->setSeminar($event);
        $fixture->setFakedFormValue('registered_themselves', '1');

        self::assertContains(
            'jane@example.com',
            $fixture->getAllRegistrationDataForConfirmation()
        );
    }

    /*
     * Tests concerning getSeminar and getEvent
     */

    /**
     * @test
     */
    public function getSeminarReturnsSeminarFromSetSeminar()
    {
        self::assertSame(
            $this->seminar,
            $this->fixture->getSeminar()
        );
    }

    /**
     * @test
     */
    public function getEventReturnsEventWithSeminarUid()
    {
        $event = $this->fixture->getEvent();
        self::assertInstanceOf(
            Tx_Seminars_Model_Event::class,
            $event
        );

        self::assertSame(
            $this->seminarUid,
            $event->getUid()
        );
    }

    /*
     * Tests concerning populateSeats
     */

    /**
     * @test
     */
    public function populateSeatsForOneVacancyReturnsItemOfOne()
    {
        $event = $this->fixture->getEvent();
        $event->setMaximumAttendees(1);
        self::assertSame(1, $event->getVacancies());

        $result = $this->fixture->populateSeats();

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
        $event = $this->fixture->getEvent();
        $event->setMaximumAttendees(9);
        self::assertSame(9, $event->getVacancies());

        $result = $this->fixture->populateSeats();

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
        $event = $this->fixture->getEvent();
        $event->setMaximumAttendees(10);
        self::assertSame(10, $event->getVacancies());

        $result = $this->fixture->populateSeats();

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
        $event = $this->fixture->getEvent();
        $event->setMaximumAttendees(11);
        self::assertSame(11, $event->getVacancies());

        $result = $this->fixture->populateSeats();

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
        $event = $this->fixture->getEvent();
        $event->setMaximumAttendees(11);
        self::assertSame(11, $event->getVacancies());

        $subject = new Tx_Seminars_FrontEnd_RegistrationForm(
            ['maximumBookableSeats' => 3],
            $GLOBALS['TSFE']->cObj
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
        $event = $this->fixture->getEvent();
        $event->setMaximumAttendees(1);
        $event->setOfflineRegistrations(1);
        self::assertSame(0, $event->getVacancies());

        $result = $this->fixture->populateSeats();

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
        $event = $this->fixture->getEvent();
        self::assertTrue($event->hasVacancies());

        $result = $this->fixture->populateSeats();

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
