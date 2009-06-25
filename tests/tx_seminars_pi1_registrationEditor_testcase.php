<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

require_once(t3lib_extMgm::extPath('lang') . 'lang.php');

/**
 * Testcase for the registrationEditor class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi1_registrationEditor_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_pi1_registrationEditor
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

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createFakeFrontEnd($frontEndPageUid);

		$this->session = new tx_oelib_FakeSession();
		tx_oelib_Session::setInstance(
			tx_oelib_Session::TYPE_USER, $this->session
		);

		$seminar = new tx_seminars_seminar($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('payment_methods' => '1')
		));

		$this->fixture = new tx_seminars_pi1_registrationEditor(
			array(
				'pageToShowAfterUnregistrationPID' => $frontEndPageUid,
				'sendParametersToThankYouAfterRegistrationPageUrl' => 1,
				'thankYouAfterRegistrationPID' => $frontEndPageUid,
				'sendParametersToPageToShowAfterUnregistrationUrl' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
				'logOutOneTimeAccountsAfterRegistration' => 1,
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
		$this->session->setAsBoolean('onetimeaccount', true);

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
			$this->session->getAsString(
				'tx_seminars_registration_editor_zip'
			)
		);
	}

	public function testSaveDataToSessionCanWriteNonEmptyZipToUserSession() {
		$this->fixture->processRegistration(array('zip' => '12345'));

		$this->assertEquals(
			'12345',
			$this->session->getAsString(
				'tx_seminars_registration_editor_zip'
			)
		);
	}

	public function testSaveDataToSessionCanOverwriteNonEmptyZipWithEmptyZipInUserSession() {
		$this->session->setAsString(
			'tx_seminars_registration_editor_zip', '12345'
		);
		$this->fixture->processRegistration(array('zip' => ''));

		$this->assertEquals(
			'',
			$this->session->getAsString(
				'tx_seminars_registration_editor_zip'
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

	public function test_isFormFieldEnabled_ForEnabledRegisteredThemselvesField_ReturnsTrue() {
		$fixture = new tx_seminars_pi1_registrationEditor(
			array('showRegistrationFields' => 'registered_themselves'),
			$GLOBALS['TSFE']->cObj
		);

		$this->assertTrue(
			$fixture->isFormFieldEnabled('registered_themselves')
		);

		$fixture->__destruct();
	}

	public function test_isFormFieldEnabled_ForEnabledRegisteredThemselvesField_ReturnsTrueForMoreSeats() {
		$fixture = new tx_seminars_pi1_registrationEditor(
			array('showRegistrationFields' => 'registered_themselves'),
			$GLOBALS['TSFE']->cObj
		);

		$this->assertTrue(
			$fixture->isFormFieldEnabled('more_seats')
		);

		$fixture->__destruct();
	}

	public function test_isFormFieldEnabled_NoEnabledRegistrationFields_ReturnsFalseForRegisteredThemselves() {
		$fixture = new tx_seminars_pi1_registrationEditor(
			array('showRegistrationFields' => ''),
			$GLOBALS['TSFE']->cObj
		);

		$this->assertFalse(
			$fixture->isFormFieldEnabled('registered_themselves')
		);

		$fixture->__destruct();
	}
}
?>