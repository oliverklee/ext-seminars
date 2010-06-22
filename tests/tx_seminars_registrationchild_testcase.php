<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the registration class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_registrationchild_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_registrationchild
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	/**
	 * @var integer the UID of the registration the fixture relates to
	 */
	private $registrationUid;

	/**
	 * @var integer the UID of the user the registration relates to
	 */
	private $feUserUid;

	public function setUp() {
		tx_seminars_registrationchild::purgeCachedSeminars();

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'test organizer',
				'email' => 'mail@example.com',
			)
		);

		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('organizers' => 1, 'title' => 'foo_event')
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm',
			$this->seminarUid,
			$organizerUid
		);

		$this->feUserUid = $this->testingFramework->createFrontEndUser(
			'',
			array(
				'name' => 'foo_user',
				'email' => 'foo@bar.com',
			)
		);
		$this->registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'title' => 'test title',
				'seminar' => $this->seminarUid,
				'interests' => 'nothing',
				'expectations' => '',
				'user' => $this->feUserUid,
			)
		);

		$this->fixture = new tx_seminars_registrationchild($this->registrationUid);
		$this->fixture->setConfigurationValue(
			'templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->testingFramework);
	}

	public function testIsOk() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Inserts a payment method record into the database and creates a relation
	 * to it from the fixture.
	 *
	 * @param array data of the payment method to add, may be empty
	 *
	 * @return integer the UID of the created record, will always be > 0
	 */
	private function setPaymentMethodRelation(array $paymentMethodData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', $paymentMethodData
		);

		$this->fixture->setPaymentMethod($uid);

		return $uid;
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testSetPaymentMethodRelationReturnsUid() {
		$this->assertTrue(
			$this->setPaymentMethodRelation(array()) > 0
		);
	}

	public function testSetPaymentMethodRelationCreatesNewUid() {
		$this->assertNotEquals(
			$this->setPaymentMethodRelation(array()),
			$this->setPaymentMethodRelation(array())
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests concerning the payment method in setRegistrationData
	///////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setRegistrationDataUsesPaymentMethodUidFromSetRegistrationData() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('method_of_payment' => 42)
		);

		$this->assertEquals(
			42,
			$this->fixture->getMethodOfPaymentUid()
		);

		$seminar->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNoPaymentMethodSetAndPositiveTotalPriceWithSeminarWithOnePaymentMethodSelectsThatPaymentMethod() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('currency', 'EUR');
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('price_regular' => 31.42)
		);
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid, $paymentMethodUid,
			'payment_methods'
		);

		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, 0, array());

		$this->assertEquals(
			$paymentMethodUid,
			$this->fixture->getMethodOfPaymentUid()
		);

		$seminar->__destruct();
	}


	////////////////////////////////////////////
	// Tests regarding the registration queue.
	////////////////////////////////////////////

	public function testIsOnRegistrationQueue() {
		$this->assertFalse(
			$this->fixture->isOnRegistrationQueue()
		);

		$this->fixture->setIsOnRegistrationQueue(1);
		$this->assertTrue(
			$this->fixture->isOnRegistrationQueue()
		);
	}

	public function testStatusIsInitiallyRegular() {
		$this->assertEquals(
			'regular',
			$this->fixture->getStatus()
		);
	}

	public function testStatusIsRegularIfNotOnQueue() {
		$this->fixture->setIsOnRegistrationQueue(FALSE);

		$this->assertEquals(
			'regular',
			$this->fixture->getStatus()
		);
	}

	public function testStatusIsWaitingListIfOnQueue() {
		$this->fixture->setIsOnRegistrationQueue(TRUE);

		$this->assertEquals(
			'waiting list',
			$this->fixture->getStatus()
		);
	}


	///////////////////////////////////////////////////
	// Tests regarding getting the registration data.
	///////////////////////////////////////////////////

	public function testGetRegistrationDataIsEmptyForEmptyKey() {
		$this->assertEquals(
			'',
			$this->fixture->getRegistrationData('')
		);
	}

	public function testGetRegistrationDataCanGetUid() {
		$this->assertEquals(
			$this->fixture->getUid(),
			$this->fixture->getRegistrationData('uid')
		);
	}

	public function testGetRegistrationDataWithKeyMethodOfPaymentReturnsMethodOfPayment() {
		$title = 'Test payment method';
		$this->setPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getRegistrationData('method_of_payment')
		);
	}

	public function test_getRegistrationData_ForRegisteredThemselvesZero_ReturnsLabelNo() {
		$this->fixture->setRegisteredThemselves(0);

		$this->assertEquals(
			$this->fixture->translate('label_no'),
			$this->fixture->getRegistrationData('registered_themselves')
		);
	}

	public function test_getRegistrationData_ForRegisteredThemselvesOne_ReturnsLabelYes() {
		$this->fixture->setRegisteredThemselves(1);

		$this->assertEquals(
			$this->fixture->translate('label_yes'),
			$this->fixture->getRegistrationData('registered_themselves')
		);
	}

	public function test_getRegistrationDataForNotesWithCarriageReturn_RemovesCarriageReturnFromNotes() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('notes' => 'foo' . CRLF . 'bar')
		);

		$this->assertNotContains(
			CRLF,
			$this->fixture->getRegistrationData('notes')
		);

		$seminar->__destruct();
	}

	public function test_getRegistrationDataForNotesWithCarriageReturnAndLineFeed_ReturnsNotesWithLinefeedAndNoCarriageReturn() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('notes' => 'foo' . CRLF . 'bar')
		);

		$this->assertEquals(
			'foo' . LF . 'bar',
			$this->fixture->getRegistrationData('notes')
		);

		$seminar->__destruct();
	}

	public function test_getRegistrationDataForMultipleAttendeeNames_ReturnsAttendeeNamesWithEnumeration() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => 'foo' . LF . 'bar')
		);

		$this->assertEquals(
			'1. foo' . LF . '2. bar',
			$this->fixture->getRegistrationData('attendees_names')
		);

		$seminar->__destruct();
	}


	//////////////////////////////////////////
	// Tests concerning dumpAttendanceValues
	//////////////////////////////////////////

	public function testDumpAttendanceValuesCanContainUid() {
		$this->assertContains(
			(string) $this->fixture->getUid(),
			$this->fixture->dumpAttendanceValues('uid')
		);
	}

	public function testDumpAttendanceValuesContainsInterestsIfRequested() {
		$this->assertContains(
			'nothing',
			$this->fixture->dumpAttendanceValues('interests')
		);
	}

	public function testDumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceAfterCommaInKeyList() {
		$this->assertContains(
			'nothing',
			$this->fixture->dumpAttendanceValues('email, interests')
		);
	}

	public function testDumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceBeforeCommaInKeyList() {
		$this->assertContains(
			'nothing',
			$this->fixture->dumpAttendanceValues('interests ,email')
		);
	}

	public function testDumpAttendanceValuesContainsLabelForInterestsIfRequested() {
		$this->assertContains(
			$this->fixture->translate('label_interests'),
			$this->fixture->dumpAttendanceValues('interests')
		);
	}

	public function testDumpAttendanceValuesContainsLabelEvenForSpaceAfterCommaInKeyList() {
		$this->assertContains(
			$this->fixture->translate('label_interests'),
			$this->fixture->dumpAttendanceValues('interests, expectations')
		);
	}

	public function testDumpAttendanceValuesContainsLabelEvenForSpaceBeforeCommaInKeyList() {
		$this->assertContains(
			$this->fixture->translate('label_interests'),
			$this->fixture->dumpAttendanceValues('interests ,expectations')
		);
	}


	/////////////////////////////////////////////////////////////
	// Tests regarding commiting registrations to the database.
	/////////////////////////////////////////////////////////////

	public function testCommitToDbCanCreateNewRecord() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData($seminar, 0, array());
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty('tx_seminars_attendances');

		$this->assertTrue(
			$registration->isOk()
		);
		$this->assertTrue(
			$registration->commitToDb()
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
	}

	public function testCommitToDbCanCreateLodgingsRelation() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$lodgingsUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings'
		);

		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData(
			$seminar, 0, array('lodgings' => array($lodgingsUid))
		);
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty('tx_seminars_attendances');
		$this->testingFramework->markTableAsDirty(
			'tx_seminars_attendances_lodgings_mm'
		);

		$this->assertTrue(
			$registration->isOk()
		);
		$this->assertTrue(
			$registration->commitToDb()
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances_lodgings_mm',
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$lodgingsUid
			),
			'The relation record cannot be found in the DB.'
		);
	}

	public function testCommitToDbCanCreateFoodsRelation() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$foodsUid = $this->testingFramework->createRecord(
			'tx_seminars_foods'
		);

		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData(
			$seminar, 0, array('foods' => array($foodsUid))
		);
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty('tx_seminars_attendances');
		$this->testingFramework->markTableAsDirty(
			'tx_seminars_attendances_foods_mm'
		);

		$this->assertTrue(
			$registration->isOk()
		);
		$this->assertTrue(
			$registration->commitToDb()
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances_foods_mm',
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$foodsUid
			),
			'The relation record cannot be found in the DB.'
		);
	}

	public function testCommitToDbCanCreateCheckboxesRelation() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$checkboxesUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes'
		);

		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData(
			$seminar, 0, array('checkboxes' => array($checkboxesUid))
		);
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty('tx_seminars_attendances');
		$this->testingFramework->markTableAsDirty(
			'tx_seminars_attendances_checkboxes_mm'
		);

		$this->assertTrue(
			$registration->isOk()
		);
		$this->assertTrue(
			$registration->commitToDb()
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances_checkboxes_mm',
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$checkboxesUid
			),
			'The relation record cannot be found in the DB.'
		);
	}


	//////////////////////////////////////
	// Tests concerning getSeminarObject
	//////////////////////////////////////

	public function testGetSeminarObjectReturnsSeminarInstance() {
		$this->assertTrue(
			$this->fixture->getSeminarObject() instanceof tx_seminars_seminar
		);
	}

	public function testGetSeminarObjectForRegistrationWithoutSeminarReturnsSeminarInstance() {
		$this->testingFramework->changeRecord(
			'tx_seminars_attendances', $this->registrationUid,
			array(
				'seminar' => 0,
				'user' => 0,
			)
		);

		$fixture = new tx_seminars_registrationchild($this->registrationUid);

		$this->assertTrue(
			$this->fixture->getSeminarObject() instanceof tx_seminars_seminar
		);

		$fixture->__destruct();
	}

	public function testGetSeminarObjectReturnsSeminarWithUidFromRelation() {
		$this->assertEquals(
			$this->seminarUid,
			$this->fixture->getSeminarObject()->getUid()
		);
	}


	/////////////////////////////////////////
	// Tests regarding the cached seminars.
	/////////////////////////////////////////

	public function testPurgeCachedSeminarsResultsInDifferentDataForSameSeminarUid() {
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'test title 1')
		);

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $seminarUid)
		);

		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$seminarUid,
			array('title' => 'test title 2')
		);

		tx_seminars_registrationchild::purgeCachedSeminars();
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertEquals(
			'test title 2',
			$fixture->getSeminarObject()->getTitle()
		);
	}


	////////////////////////////////////////////////
	// Tests for setting and getting the user data
	////////////////////////////////////////////////

	public function testInstantiationWithoutLoggedInUserDoesNotThrowException() {
		$this->testingFramework->logoutFrontEndUser();

		new tx_seminars_registrationchild(
			$this->testingFramework->createRecord(
				'tx_seminars_attendances',
				array('seminar' => $this->seminarUid)
			)
		);
	}

	public function testSetUserDataThrowsExceptionForEmptyUserData() {
		$this->setExpectedException(
			'Exception', '$userData must not be empty.'
		);

		$this->fixture->setUserData(array());
	}

	public function testGetUserDataIsEmptyForEmptyKey() {
		$this->assertEquals(
			'',
			$this->fixture->getUserData('')
		);
	}

	public function testGetUserDataReturnsEmptyStringForInexistentKeyName() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		$this->assertEquals(
			'',
			$this->fixture->getUserData('foo')
		);
	}

	public function testGetUserDataCanReturnWwwSetViaSetUserData() {
		$this->fixture->setUserData(array('www' => 'www.foo.com'));

		$this->assertEquals(
			'www.foo.com',
			$this->fixture->getUserData('www')
		);
	}

	public function testGetUserDataCanReturnNumericPidAsString() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		$this->assertTrue(
			is_string($this->fixture->getUserData('pid'))
		);
		$this->assertEquals(
			(string) $pid,
			$this->fixture->getUserData('pid')
		);
	}

	public function test_getUserData_ForUserWithName_ReturnsUsersName() {
		$this->assertEquals(
			'foo_user',
			$this->fixture->getUserData('name')
		);
	}

	public function test_getUserData_ForUserWithOutNameButFirstName_ReturnsFirstName() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid,
			array('name' => '', 'first_name' => 'first_foo')
		);

		$this->assertEquals(
			'first_foo',
			$this->fixture->getUserData('name')
		);
	}

	public function test_getUserData_ForUserWithOutNameButLastName_ReturnsLastName() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid,
			array('name' => '', 'last_name' => 'last_foo')
		);

		$this->assertEquals(
			'last_foo',
			$this->fixture->getUserData('name')
		);
	}

	public function test_getUserData_ForUserWithOutNameButFirstAndLastName_ReturnsFirstAndLastName() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid,
			array('name' => '', 'first_name' => 'first', 'last_name' => 'last')
		);

		$this->assertEquals(
			'first last',
			$this->fixture->getUserData('name')
		);
	}


	////////////////////////////////////
	// Tests concerning dumpUserValues
	////////////////////////////////////

	public function testDumpUserValuesContainsUserNameIfRequested() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('name' => 'John Doe')
		);

		$this->assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('name')
		);
	}

	public function testDumpUserValuesContainsUserNameIfRequestedEvenForSpaceAfterCommaInKeyList() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('name' => 'John Doe')
		);

		$this->assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('email, name')
		);
	}

	public function testDumpUserValuesContainsUserNameIfRequestedEvenForSpaceBeforeCommaInKeyList() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('name' => 'John Doe')
		);

		$this->assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('name ,email')
		);
	}

	public function testDumpUserValuesContainsLabelForUserNameIfRequested() {
		$this->assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('name')
		);
	}

	public function testDumpUserValuesContainsLabelEvenForSpaceAfterCommaInKeyList() {
		$this->assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('email, name')
		);
	}

	public function testDumpUserValuesContainsLabelEvenForSpaceBeforeCommaInKeyList() {
		$this->assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('name ,email')
		);
	}

	public function testDumpUserValuesContainsPidIfRequested() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		$this->assertTrue(
			is_string($this->fixture->getUserData('pid'))
		);

		$this->assertContains(
			(string) $pid,
			$this->fixture->dumpUserValues('pid')
		);
	}

	public function testDumpUserValuesContainsFieldNameAsLabelForPid() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		$this->assertContains(
			'Pid',
			$this->fixture->dumpUserValues('pid')
		);
	}

	public function testDumpUserValuesDoesNotContainRawLabelNameAsLabelForPid() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		$this->assertNotContains(
			'label_pid',
			$this->fixture->dumpUserValues('pid')
		);
	}


	///////////////////////
	// Tests for isPaid()
	///////////////////////

	public function testIsPaidInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isPaid()
		);
	}

	public function testIsPaidForPaidRegistrationReturnsTrue() {
		$this->fixture->setPaymentDateAsUnixTimestamp($GLOBALS['SIM_EXEC_TIME']);

		$this->assertTrue(
			$this->fixture->isPaid()
		);
	}

	public function testIsPaidForUnpaidRegistrationReturnsFalse() {
		$this->fixture->setPaymentDateAsUnixTimestamp(0);

		$this->assertFalse(
			$this->fixture->isPaid()
		);
	}


	///////////////////////////////////////////////
	// Tests regarding hasExistingFrontEndUser().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasExistingFrontEndUserWithExistingFrontEndUserReturnsTrue() {
		$this->assertTrue(
			$this->fixture->hasExistingFrontEndUser()
		);
	}

	/**
	 * @test
	 */
	public function hasExistingFrontEndUserWithInexistentFrontEndUserReturnsFalse() {
		$this->testingFramework->changeRecord(
			'fe_users',
			$this->fixture->getUser(),
			array('deleted' => 1)
		);

		$this->assertFalse(
			$this->fixture->hasExistingFrontEndUser()
		);
	}

	/**
	 * @test
	 */
	public function hasExistingFrontEndUserWithZeroFrontEndUserUIDReturnsFalse() {
		$this->fixture->setFrontEndUserUID(0);

		$this->assertFalse(
			$this->fixture->hasExistingFrontEndUser()
		);
	}


	///////////////////////////////////////
	// Tests regarding getFrontEndUser().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getFrontEndUserWithExistingFrontEndUserReturnsFrontEndUser() {
		$this->assertTrue(
			$this->fixture->getFrontEndUser() instanceof tx_oelib_Model_FrontEndUser
		);
	}


	/////////////////////////////////////////
	// Tests concerning setRegistrationData
	/////////////////////////////////////////

	public function test_SetRegistrationData_WithNoFoodOptions_InitializesFoodOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array()
		);

		$this->assertTrue(
			is_array($this->fixture->getFoodsData())
		);
	}

	public function test_SetRegistrationData_FoodOptions_StoresFoodOptionsInFoodsVariable() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$foods = array('foo' => 'foo', 'bar' => 'bar');
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(),
			$userUid,
			array('foods' => $foods)
		);

		$this->assertEquals(
			$foods,
			$this->fixture->getFoodsData()
		);
	}

	public function test_SetRegistrationData_WithEmptyFoodOptions_InitializesFoodOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array('foods' => '')
		);

		$this->assertTrue(
			is_array($this->fixture->getFoodsData())
		);
	}

	public function test_SetRegistrationData_WithNoLodgingOptions_InitializesLodgingOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array()
		);

		$this->assertTrue(
			is_array($this->fixture->getLodgingsData())
		);
	}

	public function test_SetRegistrationData_WithLodgingOptions_StoresLodgingOptionsInLodgingVariable() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$lodgings = array('foo' => 'foo', 'bar' => 'bar');
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(),
			$userUid,
			array('lodgings' => $lodgings)
		);

		$this->assertEquals(
			$lodgings,
			$this->fixture->getLodgingsData()
		);
	}

	public function test_SetRegistrationData_WithEmptyLodgingOptions_InitializesLodgingOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array('lodgings' => '')
		);

		$this->assertTrue(
			is_array($this->fixture->getLodgingsData())
		);
	}

	public function test_SetRegistrationData_WithNoCheckboxOptions_InitializesCheckboxOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array()
		);

		$this->assertTrue(
			is_array($this->fixture->getCheckboxesData())
		);
	}

	public function test_SetRegistrationData_WithCheckboxOptions_StoresCheckboxOptionsInCheckboxVariable() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$checkboxes = array('foo' => 'foo', 'bar' => 'bar');
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(),
			$userUid,
			array('checkboxes' => $checkboxes)
		);

		$this->assertEquals(
			$checkboxes,
			$this->fixture->getCheckboxesData()
		);
	}

	public function test_SetRegistrationData_WithEmptyCheckboxOptions_InitializesCheckboxOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array('checkboxes' => '')
		);

		$this->assertTrue(
			is_array($this->fixture->getCheckboxesData())
		);
	}

	public function test_SetRegistrationData_WithRegisteredThemselvesGiven_StoresRegisteredThemselvesIntoTheObject() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid,
			array('registered_themselves' => 1)
		);

		$this->assertEquals(
			$this->fixture->translate('label_yes'),
			$this->fixture->getRegistrationData('registered_themselves')
		);
	}

	public function test_SetRegistrationData_WithCompanyGiven_StoresCompanyIntoTheObject() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid,
			array('company' => 'Foo' . LF . 'Bar Inc')
		);

		$this->assertEquals(
			'Foo' . LF . 'Bar Inc',
			$this->fixture->getRegistrationData('company')
		);
	}


	///////////////////////////////
	// Tests regarding the seats.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getSeatsWithoutSeatsReturnsOne() {
		$this->assertEquals(
			1,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setSeatsWithNegativeSeatsThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $seats must be >= 0.'
		);

		$this->fixture->setSeats(-1);
	}

	/**
	 * @test
	 */
	public function setSeatsWithZeroSeatsSetsSeats() {
		$this->fixture->setSeats(0);

		$this->assertEquals(
			1,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setSeatsWithPositiveSeatsSetsSeats() {
		$this->fixture->setSeats(42);

		$this->assertEquals(
			42,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function hasSeatsWithoutSeatsReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasSeats()
		);
	}

	/**
	 * @test
	 */
	public function hasSeatsWithSeatsReturnsTrue() {
		$this->fixture->setSeats(42);

		$this->assertTrue(
			$this->fixture->hasSeats()
		);
	}


	/////////////////////////////////////////
	// Tests regarding the attendees names.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAttendeesNamesWithoutAttendeesNamesReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function setAttendeesNamesWithAttendeesNamesSetsAttendeesNames() {
		$this->fixture->setAttendeesNames('John Doe');

		$this->assertEquals(
			'John Doe',
			$this->fixture->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendeesNamesWithoutAttendeesNamesReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendeesNamesWithAttendeesNamesReturnsTrue() {
		$this->fixture->setAttendeesNames('John Doe');

		$this->assertTrue(
			$this->fixture->hasAttendeesNames()
		);
	}


	//////////////////////////////
	// Tests regarding the kids.
	//////////////////////////////

	/**
	 * @test
	 */
	public function getNumberOfKidsWithoutKidsReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfKids()
		);
	}

	/**
	 * @test
	 */
	public function setNumberOfKidsWithNegativeNumberOfKidsThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $numberOfKids must be >= 0.'
		);

		$this->fixture->setNumberOfKids(-1);
	}

	/**
	 * @test
	 */
	public function setNumberOfKidsWithZeroNumberOfKidsSetsNumberOfKids() {
		$this->fixture->setNumberOfKids(0);

		$this->assertEquals(
			0,
			$this->fixture->getNumberOfKids()
		);
	}

	/**
	 * @test
	 */
	public function setNumberOfKidsWithPositiveNumberOfKidsSetsNumberOfKids() {
		$this->fixture->setNumberOfKids(42);

		$this->assertEquals(
			42,
			$this->fixture->getNumberOfKids()
		);
	}

	/**
	 * @test
	 */
	public function hasKidsWithoutKidsReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasKids()
		);
	}

	/**
	 * @test
	 */
	public function hasKidsWithKidsReturnsTrue() {
		$this->fixture->setNumberOfKids(42);

		$this->assertTrue(
			$this->fixture->hasKids()
		);
	}


	///////////////////////////////
	// Tests regarding the price.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getPriceWithoutPriceReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function setPriceWithPriceSetsPrice() {
		$this->fixture->setPrice('Regular price: 42.42');

		$this->assertEquals(
			'Regular price: 42.42',
			$this->fixture->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasPriceWithoutPriceReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasPriceWithPriceReturnsTrue() {
		$this->fixture->setPrice('Regular price: 42.42');

		$this->assertTrue(
			$this->fixture->hasPrice()
		);
	}


	/////////////////////////////////////
	// Tests regarding the total price.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getTotalPriceWithoutTotalPriceReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function setTotalPriceWithTotalPriceSetsTotalPrice() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('currency', 'EUR');
		$this->fixture->setTotalPrice('42.42');

		$this->assertEquals(
			'€ 42,42',
			$this->fixture->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasTotalPriceWithoutTotalPriceReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasTotalPriceWithTotalPriceReturnsTrue() {
		$this->fixture->setTotalPrice('42.42');

		$this->assertTrue(
			$this->fixture->hasTotalPrice()
		);
	}


	///////////////////////////////////////////
	// Tests regarding the method of payment.
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getMethodOfPaymentUidWithoutMethodOfPaymentReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getMethodOfPaymentUid()
		);
	}

	/**
	 * @test
	 */
	public function setMethodOfPaymentUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $uid must be >= 0.'
		);

		$this->fixture->setMethodOfPaymentUid(-1);
	}

	/**
	 * @test
	 */
	public function setMethodOfPaymentUidWithZeroUidSetsMethodOfPaymentUid() {
		$this->fixture->setMethodOfPaymentUid(0);

		$this->assertEquals(
			0,
			$this->fixture->getMethodOfPaymentUid()
		);
	}

	/**
	 * @test
	 */
	public function setMethodOfPaymentUidWithPositiveUidSetsMethodOfPaymentUid() {
		$this->fixture->setMethodOfPaymentUid(42);

		$this->assertEquals(
			42,
			$this->fixture->getMethodOfPaymentUid()
		);
	}

	/**
	 * @test
	 */
	public function hasMethodOfPaymentWithoutMethodOfPaymentReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasMethodOfPayment()
		);
	}

	/**
	 * @test
	 */
	public function hasMethodOfPaymentWithMethodOfPaymentReturnsTrue() {
		$this->fixture->setMethodOfPaymentUid(42);

		$this->assertTrue(
			$this->fixture->hasMethodOfPayment()
		);
	}


	/////////////////////////////////////////
	// Tests regarding the billing address.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getBillingAddressWithGenderMaleContainsLabelForGenderMale() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('gender' => '0')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			$fixture->translate('label_gender.I.0'),
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithGenderFemaleContainsLabelForGenderFemale() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('gender' => '1')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			$fixture->translate('label_gender.I.1'),
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithNameContainsName() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('name' => 'John Doe')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			'John Doe',
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithAddressContainsAddress() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('address' => 'Main Street 123')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			'Main Street 123',
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithZipCodeContainsZipCode() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('zip' => '12345')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			'12345',
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithCityContainsCity() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('city' => 'Big City')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			'Big City',
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithCountryContainsCountry() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('country' => 'Takka-Tukka-Land')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			'Takka-Tukka-Land',
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithTelephoneNumberContainsTelephoneNumber() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('telephone' => '01234-56789')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			'01234-56789',
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithEMailAddressContainsEMailAddress() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('email' => 'john@doe.com')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		$this->assertContains(
			'john@doe.com',
			$fixture->getBillingAddress()
		);

		$fixture->__destruct();
	}


	////////////////////////////////////////////////
	// Tests concerning getEnumeratedAttendeeNames
	////////////////////////////////////////////////

	public function test_getEnumeratedAttendeeNames_WithUseHtml_SeparatesAttendeesNamesWithListItems() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => 'foo' . LF . 'bar')
		);

		$this->assertEquals(
			'<ol><li>foo</li><li>bar</li></ol>',
			$this->fixture->getEnumeratedAttendeeNames(TRUE)
		);

		$seminar->__destruct();
	}

	public function test_getEnumeratedAttendeeNames_WithUseHtmlAndEmptyAttendeesNames_ReturnsEmptyString() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => '')
		);

		$this->assertEquals(
			'',
			$this->fixture->getEnumeratedAttendeeNames(TRUE)
		);

		$seminar->__destruct();
	}

	public function test_getEnumeratedAttendeeNames_WithUsePlainText_SeparatesAttendeesNamesWithLineFeed() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => 'foo' . LF . 'bar')
		);

		$this->assertEquals(
			'1. foo' . LF . '2. bar',
			$this->fixture->getEnumeratedAttendeeNames()
		);

		$seminar->__destruct();
	}

	public function test_getEnumeratedAttendeeNames_WithUsePlainTextAndEmptyAttendeesNames_ReturnsEmptyString() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => '')
		);

		$this->assertEquals(
			'',
			$this->fixture->getEnumeratedAttendeeNames()
		);

		$seminar->__destruct();
	}

	public function test_getEnumeratedAttendeeNames_ForSelfRegisteredUserAndNoAttendeeNames_ReturnsUsersName() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, $this->feUserUid, array('attendees_names' => '')
		);
		$this->fixture->setRegisteredThemselves(1);

		$this->assertEquals(
			'1. foo_user',
			$this->fixture->getEnumeratedAttendeeNames()
		);

		$seminar->__destruct();
	}

	public function test_getEnumeratedAttendeeNames_ForSelfRegisteredUserAndAttendeeNames_ReturnsUserInFirstPosition() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, $this->feUserUid, array('attendees_names' => 'foo')
		);
		$this->fixture->setRegisteredThemselves(1);

		$this->assertEquals(
			'1. foo_user' . LF . '2. foo',
			$this->fixture->getEnumeratedAttendeeNames()
		);

		$seminar->__destruct();
	}


	/////////////////////////////////////////
	// Tests concerning hasRegisteredMySelf
	/////////////////////////////////////////

	public function test_hasRegisteredMySelf_ForRegisteredThemselvesFalse_ReturnsFalse() {
		$this->fixture->setRegisteredThemselves(0);

		$this->assertFalse(
			$this->fixture->hasRegisteredMySelf()
		);
	}

	public function test_hasRegisteredMySelf_ForRegisteredThemselvesTrue_ReturnsTrue() {
		$this->fixture->setRegisteredThemselves(1);

		$this->assertTrue(
			$this->fixture->hasRegisteredMySelf()
		);
	}
}
?>