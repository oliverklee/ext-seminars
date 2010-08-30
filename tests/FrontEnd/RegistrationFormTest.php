<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Niels Pardon (mail@niels-pardon.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_seminars_FrontEnd_RegistrationForm class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_FrontEnd_RegistrationFormTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_RegistrationForm
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_oelib_FakeSession a fake session
	 */
	private $session;

	/**
	 * @var integer the UID of the event the fixture relates to
	 */
	private $seminarUid = 0;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createFakeFrontEnd($frontEndPageUid);

		$this->session = new tx_oelib_FakeSession();
		tx_oelib_Session::setInstance(
			tx_oelib_Session::TYPE_USER, $this->session
		);

		$configurationRegistry = tx_oelib_ConfigurationRegistry::getInstance();
		$configuration = new tx_oelib_Configuration();
		$configuration->setAsString('currency', 'EUR');
		$configurationRegistry->set('plugin.tx_seminars', $configuration);
		$configurationRegistry->set(
			'plugin.tx_staticinfotables_pi1', new tx_oelib_Configuration()
		);

		$seminar = new tx_seminars_seminar($this->testingFramework->createRecord(
			'tx_seminars_seminars', array('payment_methods' => '1')
		));
		$this->seminarUid = $seminar->getUid();

		$this->fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'pageToShowAfterUnregistrationPID' => $frontEndPageUid,
				'sendParametersToThankYouAfterRegistrationPageUrl' => 1,
				'thankYouAfterRegistrationPID' => $frontEndPageUid,
				'sendParametersToPageToShowAfterUnregistrationUrl' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'logOutOneTimeAccountsAfterRegistration' => 1,
				'showRegistrationFields' => 'registered_themselves,attendees_names',
				'form.' => array(
					'unregistration.' => array(),
					'registration.'	=> array(
						'step1.' => array(),
						'step2.' => array(),
					)
				),
			),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->setAction('register');
		$this->fixture->setSeminar($seminar);
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->session, $this->testingFramework);
	}


	////////////////////////////////////////////////////////////////
	// Tests for getting the page-to-show-after-unregistration URL
	////////////////////////////////////////////////////////////////

	public function testGetPageToShowAfterUnregistrationUrlReturnsUrlStartingWithHttp() {
		$this->assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);
	}

	public function testGetPageToShowAfterUnregistrationUrlReturnsUrlWithEncodedBrackets() {
		$this->assertContains(
			'%5BshowUid%5D',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);

		$this->assertNotContains(
			'[showUid]',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);
	}


	///////////////////////////////////////////////////////////
	// Tests for getting the thank-you-after-registration URL
	///////////////////////////////////////////////////////////

	public function testGetThankYouAfterRegistrationUrlReturnsUrlStartingWithHttp() {
		$this->assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getThankYouAfterRegistrationUrl()
		);
	}

	public function testGetThankYouAfterRegistrationUrlReturnsUrlWithEncodedBrackets() {
		$this->assertContains(
			'%5BshowUid%5D',
			$this->fixture->getThankYouAfterRegistrationUrl()
		);

		$this->assertNotContains(
			'[showUid]',
			$this->fixture->getThankYouAfterRegistrationUrl()
		);
	}

	public function testGetThankYouAfterRegistrationUrlLeavesUserLoggedInByDefault() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->getThankYouAfterRegistrationUrl();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testGetThankYouAfterRegistrationUrlWithOneTimeAccountMarkerInUserSessionLogsOutUser() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->session->setAsBoolean('onetimeaccount', TRUE);

		$this->fixture->getThankYouAfterRegistrationUrl();

		$this->assertFalse(
			$this->testingFramework->isLoggedIn()
		);
	}


	/////////////////////////////////////
	// Test concerning getAllFeUserData
	/////////////////////////////////////

	public function testGetAllFeUserContainsNonEmptyNameOfFrontEndUser() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'John Doe')
		);

		$this->assertContains(
			'John Doe',
			$this->fixture->getAllFeUserData()
		);
	}

	public function testGetAllFeUserDoesNotContainEmptyLinesForMissingCompanyName() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'John Doe')
		);

		$this->assertNotRegExp(
			'/<br \/>\s*<br \/>/',
			$this->fixture->getAllFeUserData()
		);
	}


	///////////////////////////////////////
	// Tests concerning saveDataToSession
	///////////////////////////////////////

	public function testSaveDataToSessionCanWriteEmptyZipToUserSession() {
		$this->fixture->processRegistration(array('zip' => ''));

		$this->assertEquals(
			'',
			$this->session->getAsString('tx_seminars_registration_editor_zip')
		);
	}

	public function testSaveDataToSessionCanWriteNonEmptyZipToUserSession() {
		$this->fixture->processRegistration(array('zip' => '12345'));

		$this->assertEquals(
			'12345',
			$this->session->getAsString('tx_seminars_registration_editor_zip')
		);
	}

	public function testSaveDataToSessionCanOverwriteNonEmptyZipWithEmptyZipInUserSession() {
		$this->session->setAsString(
			'tx_seminars_registration_editor_zip', '12345'
		);
		$this->fixture->processRegistration(array('zip' => ''));

		$this->assertEquals(
			'',
			$this->session->getAsString('tx_seminars_registration_editor_zip')
		);
	}

	public function test_SaveDataToSession_CanStoreCompanyInSession() {
		$this->fixture->processRegistration(array('company' => 'foo inc.'));

		$this->assertEquals(
			'foo inc.',
			$this->session->getAsString(
				'tx_seminars_registration_editor_company'
			)
		);
	}

	public function test_SaveDataToSession_CanStoreNameInSession() {
		$this->fixture->processRegistration(array('name' => 'foo'));

		$this->assertEquals(
			'foo',
			$this->session->getAsString(
				'tx_seminars_registration_editor_name'
			)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning retrieveDataFromSession
	/////////////////////////////////////////////

	public function testRetrieveDataFromSessionWithUnusedKeyReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->retrieveDataFromSession('', array('key' => 'foo'))
		);
	}

	public function testRetrieveDataFromSessionWithKeySetInUserSessionReturnsDataForThatKey() {
		$this->session->setAsString(
			'tx_seminars_registration_editor_zip', '12345'
		);

		$this->assertEquals(
			'12345',
			$this->fixture->retrieveDataFromSession('', array('key' => 'zip'))
		);
	}


	////////////////////////////////////////////////
	// Tests concerning populateListPaymentMethods
	////////////////////////////////////////////////

	public function testPopulateListPaymentMethodsDoesNotCrash() {
		$this->fixture->populateListPaymentMethods(array());
	}

	public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsOneItem() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		$this->assertEquals(
			1,
			count($this->fixture->populateListPaymentMethods(array()))
		);
	}

	public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsThisMethodsTitle() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('title' => 'foo')
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		$paymentMethods = $this->fixture->populateListPaymentMethods(array());

		$this->assertContains(
			'foo',
			$paymentMethods[0]['caption']
		);
	}

	public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsThisMethodsUid() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		$paymentMethods = $this->fixture->populateListPaymentMethods(array());

		$this->assertEquals(
			$paymentMethodUid,
			$paymentMethods[0]['value']
		);
	}

	public function testPopulateListPaymentMethodsForEventWithTwoPaymentMethodsReturnsBothPaymentMethods() {
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

		$this->assertEquals(
			2,
			count($this->fixture->populateListPaymentMethods(array()))
		);
	}


	////////////////////////////////////
	// Tests concerning getStepCounter
	////////////////////////////////////

	public function testGetStepCounterReturnsNumberOfCurrentPageIfCurrentPageNumberIsLowerThanNumberOfLastPage() {
		$this->fixture->setConfigurationValue(
			'numberOfFirstRegistrationPage',
			1
		);
		$this->fixture->setConfigurationValue(
			'numberOfLastRegistrationPage',
			2
		);

		$this->fixture->setPage(array('next_page' => 0));

		$this->assertContains(
			'1',
			$this->fixture->getStepCounter()
		);
	}

	public function testGetStepCounterReturnsNumberOfLastRegistrationPage() {
		$this->fixture->setConfigurationValue(
			'numberOfFirstRegistrationPage',
			1
		);
		$this->fixture->setConfigurationValue(
			'numberOfLastRegistrationPage',
			2
		);
		$this->fixture->setPage(array('next_page' => 0));

		$this->assertContains(
			'2',
			$this->fixture->getStepCounter()
		);
	}

	public function testGetStepCounterReturnsNumberOfLastRegistrationPageAsCurrentPageIfPageNumberIsAboveLastRegistrationPage() {
		$this->fixture->setConfigurationValue(
			'numberOfFirstRegistrationPage',
			1
		);
		$this->fixture->setConfigurationValue(
			'numberOfLastRegistrationPage',
			2
		);

		$this->fixture->setPage(array('next_page' => 5));

		$this->assertEquals(
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
	public function populateListCountriesWithLanguageSetToDefaultNotContainsEnglishCountryNameForGermany() {
		$backUpLanguage = $GLOBALS['LANG'];
		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init('default');

		$this->assertNotContains(
			array('caption' => 'Germany', 'value' => 'Germany'),
			$this->fixture->populateListCountries()
		);

		$GLOBALS['LANG'] = $backUpLanguage;
	}

	/**
	 * @test
	 */
	public function populateListCountriesContainsLocalCountryNameForGermany() {
		$this->assertContains(
			array('caption' => 'Deutschland', 'value' => 'Deutschland'),
			$this->fixture->populateListCountries()
		);
	}


	//////////////////////////////////////
	// Tests concerning getFeUserData().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getFeUserDataWithKeyCountryAndNoCountrySetReturnsDefaultCountrySetViaTypoScriptSetup() {
		$this->testingFramework->createAndLoginFrontEndUser();

		tx_oelib_ConfigurationRegistry::get('plugin.tx_staticinfotables_pi1')->
			setAsString('countryCode', 'DEU');

		$this->assertEquals(
			'Deutschland',
			$this->fixture->getFeUserData(null, array('key' => 'country'))
		);
	}

	/**
	 * @test
	 */
	public function getFeUserDataWithKeyCountryAndStaticInfoCountrySetReturnsStaticInfoCountry() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('static_info_country' => 'GBR')
		);

		$this->assertEquals(
			'United Kingdom',
			$this->fixture->getFeUserData(null, array('key' => 'country'))
		);
	}

	/**
	 * @test
	 */
	public function getFeUserDataWithKeyCountryAndCountrySetReturnsCountry() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('country' => 'Taka-Tuka-Land')
		);

		$this->assertEquals(
			'Taka-Tuka-Land',
			$this->fixture->getFeUserData(null, array('key' => 'country'))
		);
	}


	////////////////////////////////////////
	// Tests concerning isFormFieldEnabled
	////////////////////////////////////////

	/**
	 * Data provider that returns the keys of all available form fields.
	 *
	 * @return array two-dimensional array with the inner array being:
	 *               [key] string: the form field key
	 *               [self-contained] boolean: whether the field is visible
	 *                                if no other fields are visible
	 *
	 * @see isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField
	 * @see isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields
	 */
	public function formFieldsDataProvider() {
		return array(
			'step_counter' => array(
				'key' => 'step_counter', 'self-contained' => TRUE
			),
			'price' => array(
				'key' => 'price', 'self-contained' => TRUE
			),
			'method_of_payment' => array(
				'key' => 'method_of_payment', 'self-contained' => FALSE
			),
			'account_number' => array(
				'key' => 'account_number', 'self-contained' => FALSE
			),
			'bank_code' => array(
				'key' => 'bank_code', 'self-contained' => FALSE
			),
			'bank_name' => array(
				'key' => 'bank_name', 'self-contained' => FALSE
			),
			'account_owner' => array(
				'key' => 'account_owner', 'self-contained' => FALSE
			),
			'billing_address' => array(
				'key' => 'billing_address', 'self-contained' => FALSE
			),
			'company' => array(
				'key' => 'company', 'self-contained' => TRUE
			),
			'gender' => array(
				'key' => 'gender', 'self-contained' => TRUE
			),
			'name' => array(
				'key' => 'name', 'self-contained' => TRUE
			),
			'address' => array(
				'key' => 'address', 'self-contained' => TRUE
			),
			'zip' => array(
				'key' => 'zip', 'self-contained' => TRUE
			),
			'city' => array(
				'key' => 'city', 'self-contained' => TRUE
			),
			'country' => array(
				'key' => 'country', 'self-contained' => TRUE
			),
			'telephone' => array(
				'key' => 'telephone', 'self-contained' => TRUE
			),
			'email' => array(
				'key' => 'email', 'self-contained' => TRUE
			),
			'interests' => array(
				'key' => 'interests', 'self-contained' => TRUE
			),
			'expectations' => array(
				'key' => 'expectations', 'self-contained' => TRUE
			),
			'background_knowledge' => array(
				'key' => 'background_knowledge', 'self-contained' => TRUE
			),
			'accommodation' => array(
				'key' => 'accommodation', 'self-contained' => TRUE
			),
			'food' => array(
				'key' => 'food', 'self-contained' => TRUE
			),
			'known_from' => array(
				'key' => 'known_from', 'self-contained' => TRUE
			),
			'seats' => array(
				'key' => 'seats', 'self-contained' => TRUE
			),
			'registered_themselves' => array(
				'key' => 'registered_themselves', 'self-contained' => TRUE
			),
			'attendees_names' => array(
				'key' => 'attendees_names', 'self-contained' => TRUE
			),
			'kids' => array(
				'key' => 'kids', 'self-contained' => TRUE
			),
			'lodgings' => array(
				'key' => 'lodgings', 'self-contained' => FALSE
			),
			'foods' => array(
				'key' => 'foods', 'self-contained' => FALSE
			),
			'checkboxes' => array(
				'key' => 'checkboxes', 'self-contained' => FALSE
			),
			'notes' => array(
				'key' => 'notes', 'self-contained' => TRUE
			),
			'total_price' => array(
				'key' => 'total_price', 'self-contained' => TRUE
			),
			'feuser_data' => array(
				'key' => 'feuser_data', 'self-contained' => TRUE
			),
			'registration_data' => array(
				'key' => 'registration_data', 'self-contained' => TRUE
			),
			'terms' => array(
				'key' => 'terms', 'self-contained' => TRUE
			),
			'terms_2' => array(
				'key' => 'terms_2', 'self-contained' => FALSE
			),
		);
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
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => ''),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setSeminar($this->getMock('tx_seminars_seminar'));

		$this->assertFalse(
			$fixture->isFormFieldEnabled($key)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 *
	 * @param string $key the key of the field to check for, must not be empty
	 * @param boolean $isSelfContained
	 *        whether the field will be visible if no other fields are enabled
	 *        and the event has no special features enabled
	 *
	 *
	 * @dataProvider formFieldsDataProvider
	 */
	public function isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields(
		$key, $isSelfContained
	) {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => $key),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setSeminar($this->getMock('tx_seminars_seminar'));

		$this->assertEquals(
			$isSelfContained,
			$fixture->isFormFieldEnabled($key)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function isFormFieldEnabled_ForEnabledRegisteredThemselvesFieldOnlyReturnsFalseForMoreSeats() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => 'registered_themselves'),
			$GLOBALS['TSFE']->cObj
		);

		$this->assertFalse(
			$fixture->isFormFieldEnabled('more_seats')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function isFormFieldEnabled_ForEnabledCompanyFieldReturnsTrueForBillingAddress() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => 'company, billing_address'),
			$GLOBALS['TSFE']->cObj
		);

		$this->assertTrue(
			$fixture->isFormFieldEnabled('billing_address')
		);

		$fixture->__destruct();
	}


	/////////////////////////////////////////////////////////////////////////
	// Tests concerning the validation of the number of persons to register
	/////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForEmptyFormDataReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForNoSelfRegistrationReturnsZero() {
		$this->fixture->setFakedFormValue('registered_themselves', 0);

		$this->assertEquals(
			0,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForSelfRegistrationHiddenReturnsOne() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'showRegistrationFields' => 'seats',
				'form.' => array(
					'registration.'	=> array(
						'step1.' => array('seats' => array()),
						'step2.' => array(),
					)
				),
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setAction('register');
		$fixture->setTestMode();

		$this->assertEquals(
			1,
			$fixture->getNumberOfEnteredPersons()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForSelfRegistrationReturnsOne() {
		$this->fixture->setFakedFormValue('registered_themselves', 1);

		$this->assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForOneWordAsNamesReturnsOne() {
		$this->fixture->setFakedFormValue('attendees_names', 'John');

		$this->assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForOneWordWithLeadingLfAsNamesReturnsOne() {
		$this->fixture->setFakedFormValue('attendees_names', LF . 'John');

		$this->assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForOneWordWithTrailingLfAsNamesReturnsOne() {
		$this->fixture->setFakedFormValue('attendees_names', 'John' . LF);

		$this->assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForOneWordWithTrailingSpaceLineAsNamesReturnsOne() {
		$this->fixture->setFakedFormValue(
			'attendees_names', 'John' . LF . '  ' . LF
		);

		$this->assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForTwoWordsAsNamesReturnsOne() {
		$this->fixture->setFakedFormValue('attendees_names', 'John Doe');

		$this->assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForTwoLinesSeparatedByLfAsNamesReturnsTwo() {
		$this->fixture->setFakedFormValue(
			'attendees_names', 'John Doe' . LF . 'Jane Doe'
		);

		$this->assertEquals(
			2,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForTwoLinesSeparatedByCrLfAsNamesReturnsTwo() {
		$this->fixture->setFakedFormValue(
			'attendees_names', 'John Doe' . CRLF . 'Jane Doe'
		);

		$this->assertEquals(
			2,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForSelfRegistrationAndOneNameReturnsTwo() {
		$this->fixture->setFakedFormValue(
			'attendees_names', 'John Doe' . LF . '  ' . LF
		);
		$this->fixture->setFakedFormValue('registered_themselves', 1);

		$this->assertEquals(
			2,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function numberOfSeatsMatchesRegisteredPersonsForZeroSeatsReturnsFalse() {
		$this->fixture->setFakedFormValue('seats', 0);

		$this->assertFalse(
			$this->fixture->numberOfSeatsMatchesRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function numberOfSeatsMatchesRegisteredPersonsForNegativeSeatsReturnsFalse() {
		$this->fixture->setFakedFormValue('seats', -4);

		$this->assertFalse(
			$this->fixture->numberOfSeatsMatchesRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function numberOfSeatsMatchesRegisteredPersonsForOnePersonAndOneSeatReturnsTrue() {
		$this->fixture->setFakedFormValue('attendees_names', 'John Doe');
		$this->fixture->setFakedFormValue('seats', 1);

		$this->assertTrue(
			$this->fixture->numberOfSeatsMatchesRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function numberOfSeatsMatchesRegisteredPersonsForOnePersonAndTwoSeatsReturnsFalse() {
		$this->fixture->setFakedFormValue('attendees_names', 'John Doe');
		$this->fixture->setFakedFormValue('seats', 2);

		$this->assertFalse(
			$this->fixture->numberOfSeatsMatchesRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function numberOfSeatsMatchesRegisteredPersonsForTwoPersonsAndOneSeatReturnsFalse() {
		$this->fixture->setFakedFormValue('attendees_names', 'John' . LF . 'Jane');
		$this->fixture->setFakedFormValue('seats', 1);

		$this->assertFalse(
			$this->fixture->numberOfSeatsMatchesRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndOneSeatReturnsEmptyString() {
		$this->fixture->setFakedFormValue('attendees_names', 'John Doe');
		$this->fixture->setFakedFormValue('seats', 1);

		$this->assertEquals(
			'',
			$this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndTwoSeatsReturnsMessage() {
		$this->fixture->setFakedFormValue('attendees_names', 'John Doe');
		$this->fixture->setFakedFormValue('seats', 2);

		$this->assertEquals(
			$this->fixture->translate('message_lessAttendeesThanSeats'),
			$this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getMessageForSeatsNotMatchingRegisteredPersonsForTwoPersonsAndOneSeatReturnsMessage() {
		$this->fixture->setFakedFormValue('attendees_names', 'John' . LF . 'Jane');
		$this->fixture->setFakedFormValue('seats', 1);

		$this->assertEquals(
			$this->fixture->translate('message_moreAttendeesThanSeats'),
			$this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function numberOfSeatsMatchesRegisteredPersonsForAttendeesNamesHiddenAndManySeatsReturnsTrue() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'showRegistrationFields' => 'seats',
				'form.' => array(
					'registration.'	=> array(
						'step1.' => array('seats' => array()),
						'step2.' => array(),
					)
				),
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setAction('register');
		$fixture->setTestMode();

		$fixture->setFakedFormValue('seats', 8);

		$this->assertTrue(
			$fixture->numberOfSeatsMatchesRegisteredPersons()
		);

		$fixture->__destruct();
	}


	/////////////////////////////////////////////////
	// Tests concerning getPreselectedPaymentMethod
	/////////////////////////////////////////////////

	public function test_getPreselectedPaymentMethodForOnePaymentMethod_ReturnsItsUid() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('title' => 'foo')
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		$this->assertEquals(
			$paymentMethodUid,
			$this->fixture->getPreselectedPaymentMethod()
		);
	}

	public function test_getPreselectedPaymentMethodForTwoNotSelectedPaymentMethods_ReturnsZero() {
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

		$this->assertEquals(
			0,
			$this->fixture->getPreselectedPaymentMethod()
		);
	}

	public function test_getPreselectedPaymentMethodForTwoPaymentMethodsOneSelectedOneNot_ReturnsUidOfSelectedRecord() {
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

		$this->assertEquals(
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
	public function getRegistrationDataForDisabledPaymentMethodFieldReturnsEmptyString() {
		$selectedPaymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('title' => 'payment foo')
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

		$this->assertEquals(
			'',
			$this->fixture->getRegistrationData()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForEnabledPriceFieldReturnsSelectedPriceValue() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'price',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->seminarUid,
			array('price_regular' => 42)
		);
		$event = new tx_seminars_seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('price', 42);

		$this->assertContains(
			'42',
			$fixture->getRegistrationData()
		);

		$event->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegistrationDataHtmlspecialcharsInterestsField() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'interests',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new tx_seminars_seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('interests', 'A, B & C');

		$this->assertContains(
			'A, B &amp; C',
			$fixture->getRegistrationData()
		);

		$event->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegistrationDataReplacesCarriageReturnInInterestsFieldWithBr() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'interests',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new tx_seminars_seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('interests', 'Love' . CR . 'Peace');

		$this->assertContains(
			'Love<br />Peace',
			$fixture->getRegistrationData()
		);

		$event->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegistrationDataCanContainAttendeesNames() {
		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new tx_seminars_seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('attendees_names', 'John Doe');

		$this->assertContains(
			'John Doe',
			$fixture->getRegistrationData()
		);

		$event->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForAttendeesNamesAndThemselvesSelectedContainsUserName() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'Jane Doe')
		);

		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new tx_seminars_seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('attendees_names', 'John Doe');
		$fixture->setFakedFormValue('registered_themselves', '1');

		$this->assertContains(
			'Jane Doe',
			$fixture->getRegistrationData()
		);

		$event->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForAttendeesNamesEnabledAndThemselvesNotSelectedNotContainsUserName() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'Jane Doe')
		);

		$fixture = new tx_seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new tx_seminars_seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('attendees_names', 'John Doe');
		$fixture->setFakedFormValue('registered_themselves', '');

		$this->assertNotContains(
			'Jane Doe',
			$fixture->getRegistrationData()
		);

		$event->__destruct();
		$fixture->__destruct();
	}
}
?>