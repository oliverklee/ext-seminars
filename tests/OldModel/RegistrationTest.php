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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_OldModel_RegistrationTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_registrationchild
	 */
	protected $fixture = NULL;

	/**
	 * @var tx_oelib_testingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var int the UID of a seminar to which the fixture relates
	 */
	protected $seminarUid = 0;

	/**
	 * @var int the UID of the registration the fixture relates to
	 */
	protected $registrationUid = 0;

	/**
	 * @var int the UID of the user the registration relates to
	 */
	protected $feUserUid = 0;

	protected function setUp() {
		tx_seminars_registrationchild::purgeCachedSeminars();

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		tx_oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', new tx_oelib_Configuration());

		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'test organizer',
				'email' => 'mail@example.com',
			)
		);

		$this->seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
				'background_knowledge' => 'foo' . LF . 'bar',
				'known_from' => 'foo' . CR . 'bar',
				'user' => $this->feUserUid,
			)
		);

		$this->fixture = new tx_seminars_registrationchild($this->registrationUid);
		$this->fixture->setConfigurationValue(
			'templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
	}

	/**
	 * @test
	 */
	public function isOk() {
		self::assertTrue(
			$this->fixture->isOk()
		);
	}


	/*
	 * Utility functions.
	 */

	/**
	 * Inserts a payment method record into the database and creates a relation
	 * to it from the fixture.
	 *
	 * @param array $paymentMethodData data of the payment method to add, may be empty
	 *
	 * @return int the UID of the created record, will always be > 0
	 */
	private function setPaymentMethodRelation(array $paymentMethodData) {
		$uid = $this->testingFramework->createRecord('tx_seminars_payment_methods', $paymentMethodData);

		$this->fixture->setPaymentMethod($uid);

		return $uid;
	}


	/*
	 * Tests for the utility functions.
	 */

	/**
	 * @test
	 */
	public function setPaymentMethodRelationReturnsUid() {
		self::assertTrue(
			$this->setPaymentMethodRelation(array()) > 0
		);
	}

	/**
	 * @test
	 */
	public function setPaymentMethodRelationCreatesNewUid() {
		self::assertNotEquals(
			$this->setPaymentMethodRelation(array()),
			$this->setPaymentMethodRelation(array())
		);
	}


	/*
	 * Tests concerning the payment method in setRegistrationData
	 */

	/**
	 * @test
	 */
	public function setRegistrationDataUsesPaymentMethodUidFromSetRegistrationData() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('method_of_payment' => 42)
		);

		self::assertSame(
			42,
			$this->fixture->getMethodOfPaymentUid()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNoPaymentMethodSetAndPositiveTotalPriceWithSeminarWithOnePaymentMethodSelectsThatPaymentMethod() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('currency', 'EUR');
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminarUid,
			array('price_regular' => 31.42)
		);
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $this->seminarUid, $paymentMethodUid,
			'payment_methods'
		);

		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, 0, array());

		self::assertSame(
			$paymentMethodUid,
			$this->fixture->getMethodOfPaymentUid()
		);
	}


	/*
	 * Tests regarding the registration queue.
	 */

	/**
	 * @test
	 */
	public function isOnRegistrationQueue() {
		self::assertFalse(
			$this->fixture->isOnRegistrationQueue()
		);

		$this->fixture->setIsOnRegistrationQueue(1);
		self::assertTrue(
			$this->fixture->isOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function statusIsInitiallyRegular() {
		self::assertSame(
			'regular',
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function statusIsRegularIfNotOnQueue() {
		$this->fixture->setIsOnRegistrationQueue(FALSE);

		self::assertSame(
			'regular',
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function statusIsWaitingListIfOnQueue() {
		$this->fixture->setIsOnRegistrationQueue(TRUE);

		self::assertSame(
			'waiting list',
			$this->fixture->getStatus()
		);
	}


	/*
	 * Tests regarding getting the registration data.
	 */

	/**
	 * @test
	 */
	public function getRegistrationDataIsEmptyForEmptyKey() {
		self::assertSame(
			'',
			$this->fixture->getRegistrationData('')
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataCanGetUid() {
		self::assertSame(
			(string) $this->fixture->getUid(),
			$this->fixture->getRegistrationData('uid')
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataWithKeyMethodOfPaymentReturnsMethodOfPayment() {
		$title = 'Test payment method';
		$this->setPaymentMethodRelation(array('title' => $title));

		self::assertContains(
			$title,
			$this->fixture->getRegistrationData('method_of_payment')
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForRegisteredThemselvesZeroReturnsLabelNo() {
		$this->fixture->setRegisteredThemselves(0);

		self::assertSame(
			$this->fixture->translate('label_no'),
			$this->fixture->getRegistrationData('registered_themselves')
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForRegisteredThemselvesOneReturnsLabelYes() {
		$this->fixture->setRegisteredThemselves(1);

		self::assertSame(
			$this->fixture->translate('label_yes'),
			$this->fixture->getRegistrationData('registered_themselves')
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForNotesWithCarriageReturnRemovesCarriageReturnFromNotes() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('notes' => 'foo' . CRLF . 'bar')
		);

		self::assertNotContains(
			CRLF,
			$this->fixture->getRegistrationData('notes')
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForNotesWithCarriageReturnAndLineFeedReturnsNotesWithLinefeedAndNoCarriageReturn() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('notes' => 'foo' . CRLF . 'bar')
		);

		self::assertSame(
			'foo' . LF . 'bar',
			$this->fixture->getRegistrationData('notes')
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForMultipleAttendeeNamesReturnsAttendeeNamesWithEnumeration() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => 'foo' . LF . 'bar')
		);

		self::assertSame(
			'1. foo' . LF . '2. bar',
			$this->fixture->getRegistrationData('attendees_names')
		);
	}


	/*
	 * Tests concerning dumpAttendanceValues
	 */

	/**
	 * @test
	 */
	public function dumpAttendanceValuesCanContainUid() {
		self::assertContains(
			(string) $this->fixture->getUid(),
			$this->fixture->dumpAttendanceValues('uid')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesContainsInterestsIfRequested() {
		self::assertContains(
			'nothing',
			$this->fixture->dumpAttendanceValues('interests')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceAfterCommaInKeyList() {
		self::assertContains(
			'nothing',
			$this->fixture->dumpAttendanceValues('email, interests')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceBeforeCommaInKeyList() {
		self::assertContains(
			'nothing',
			$this->fixture->dumpAttendanceValues('interests ,email')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesContainsLabelForInterestsIfRequested() {
		self::assertContains(
			$this->fixture->translate('label_interests'),
			$this->fixture->dumpAttendanceValues('interests')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesContainsLabelEvenForSpaceAfterCommaInKeyList() {
		self::assertContains(
			$this->fixture->translate('label_interests'),
			$this->fixture->dumpAttendanceValues('interests, expectations')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesContainsLabelEvenForSpaceBeforeCommaInKeyList() {
		self::assertContains(
			$this->fixture->translate('label_interests'),
			$this->fixture->dumpAttendanceValues('interests ,expectations')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesForDataWithLineFeedStartsDataOnNewLine() {
		self::assertContains(
			LF . 'foo' . LF . 'bar',
			$this->fixture->dumpAttendanceValues('background_knowledge')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesForDataWithCarriageReturnStartsDataOnNewLine() {
		self::assertContains(
			LF . 'foo' . LF . 'bar',
			$this->fixture->dumpAttendanceValues('known_from')
		);
	}

	/**
	 * @test
	 */
	public function dumpAttendanceValuesCanContainNonRegisteredField() {
		self::assertContains(
			'label_is_dummy_record: 1',
			$this->fixture->dumpAttendanceValues('is_dummy_record')
		);
	}


	/*
	 * Tests regarding committing registrations to the database.
	 */

	/**
	 * @test
	 */
	public function commitToDbCanCreateNewRecord() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData($seminar, 0, array());
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty('tx_seminars_attendances');

		self::assertTrue(
			$registration->isOk()
		);
		self::assertTrue(
			$registration->commitToDb()
		);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
	}

	/**
	 * @test
	 */
	public function commitToDbCanCreateLodgingsRelation() {
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

		self::assertTrue(
			$registration->isOk()
		);
		self::assertTrue(
			$registration->commitToDb()
		);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances_lodgings_mm',
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$lodgingsUid
			),
			'The relation record cannot be found in the DB.'
		);
	}

	/**
	 * @test
	 */
	public function commitToDbCanCreateFoodsRelation() {
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

		self::assertTrue(
			$registration->isOk()
		);
		self::assertTrue(
			$registration->commitToDb()
		);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances_foods_mm',
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$foodsUid
			),
			'The relation record cannot be found in the DB.'
		);
	}

	/**
	 * @test
	 */
	public function commitToDbCanCreateCheckboxesRelation() {
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

		self::assertTrue(
			$registration->isOk()
		);
		self::assertTrue(
			$registration->commitToDb()
		);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances',
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_attendances_checkboxes_mm',
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$checkboxesUid
			),
			'The relation record cannot be found in the DB.'
		);
	}


	/*
	 * Tests concerning getSeminarObject
	 */

	/**
	 * @test
	 */
	public function getSeminarObjectReturnsSeminarInstance() {
		self::assertTrue(
			$this->fixture->getSeminarObject() instanceof tx_seminars_seminar
		);
	}

	/**
	 * @test
	 */
	public function getSeminarObjectForRegistrationWithoutSeminarReturnsSeminarInstance() {
		$this->testingFramework->changeRecord(
			'tx_seminars_attendances', $this->registrationUid,
			array(
				'seminar' => 0,
				'user' => 0,
			)
		);

		$fixture = new tx_seminars_registrationchild($this->registrationUid);

		self::assertTrue(
			$fixture->getSeminarObject() instanceof tx_seminars_seminar
		);
	}

	/**
	 * @test
	 */
	public function getSeminarObjectReturnsSeminarWithUidFromRelation() {
		self::assertSame(
			$this->seminarUid,
			$this->fixture->getSeminarObject()->getUid()
		);
	}


	/*
	 * Tests regarding the cached seminars.
	 */

	/**
	 * @test
	 */
	public function purgeCachedSeminarsResultsInDifferentDataForSameSeminarUid() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('title' => 'test title 1')
		);

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $seminarUid)
		);

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$seminarUid,
			array('title' => 'test title 2')
		);

		tx_seminars_registrationchild::purgeCachedSeminars();
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertSame(
			'test title 2',
			$fixture->getSeminarObject()->getTitle()
		);
	}


	/*
	 * Tests for setting and getting the user data
	 */

	/**
	 * @test
	 */
	public function instantiationWithoutLoggedInUserDoesNotThrowException() {
		$this->testingFramework->logoutFrontEndUser();

		new tx_seminars_registrationchild(
			$this->testingFramework->createRecord(
				'tx_seminars_attendances',
				array('seminar' => $this->seminarUid)
			)
		);
	}

	/**
	 * @test
	 */
	public function setUserDataThrowsExceptionForEmptyUserData() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$userData must not be empty.'
		);

		$this->fixture->setUserData(array());
	}

	/**
	 * @test
	 */
	public function getUserDataIsEmptyForEmptyKey() {
		self::assertSame(
			'',
			$this->fixture->getUserData('')
		);
	}

	/**
	 * @test
	 */
	public function getUserDataReturnsEmptyStringForInexistentKeyName() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		self::assertSame(
			'',
			$this->fixture->getUserData('foo')
		);
	}

	/**
	 * @test
	 */
	public function getUserDataCanReturnWwwSetViaSetUserData() {
		$this->fixture->setUserData(array('www' => 'www.foo.com'));

		self::assertSame(
			'www.foo.com',
			$this->fixture->getUserData('www')
		);
	}

	/**
	 * @test
	 */
	public function getUserDataCanReturnNumericPidAsString() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		self::assertTrue(
			is_string($this->fixture->getUserData('pid'))
		);
		self::assertSame(
			(string) $pid,
			$this->fixture->getUserData('pid')
		);
	}

	/**
	 * @test
	 */
	public function getUserDataForUserWithNameReturnsUsersName() {
		self::assertSame(
			'foo_user',
			$this->fixture->getUserData('name')
		);
	}

	/**
	 * @test
	 */
	public function getUserDataForUserWithOutNameButFirstNameReturnsFirstName() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid,
			array('name' => '', 'first_name' => 'first_foo')
		);

		self::assertSame(
			'first_foo',
			$this->fixture->getUserData('name')
		);
	}

	/**
	 * @test
	 */
	public function getUserDataForUserWithOutNameButLastNameReturnsLastName() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid,
			array('name' => '', 'last_name' => 'last_foo')
		);

		self::assertSame(
			'last_foo',
			$this->fixture->getUserData('name')
		);
	}

	/**
	 * @test
	 */
	public function getUserDataForUserWithOutNameButFirstAndLastNameReturnsFirstAndLastName() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid,
			array('name' => '', 'first_name' => 'first', 'last_name' => 'last')
		);

		self::assertSame(
			'first last',
			$this->fixture->getUserData('name')
		);
	}


	/*
	 * Tests concerning dumpUserValues
	 */

	/**
	 * @test
	 */
	public function dumpUserValuesContainsUserNameIfRequested() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('name' => 'John Doe')
		);

		self::assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('name')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesContainsUserNameIfRequestedEvenForSpaceAfterCommaInKeyList() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('name' => 'John Doe')
		);

		self::assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('email, name')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesContainsUserNameIfRequestedEvenForSpaceBeforeCommaInKeyList() {
		$this->testingFramework->changeRecord(
			'fe_users', $this->feUserUid, array('name' => 'John Doe')
		);

		self::assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('name ,email')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesContainsLabelForUserNameIfRequested() {
		self::assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('name')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesContainsLabelEvenForSpaceAfterCommaInKeyList() {
		self::assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('email, name')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesContainsLabelEvenForSpaceBeforeCommaInKeyList() {
		self::assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('name ,email')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesContainsPidIfRequested() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		self::assertTrue(
			is_string($this->fixture->getUserData('pid'))
		);

		self::assertContains(
			(string) $pid,
			$this->fixture->dumpUserValues('pid')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesContainsFieldNameAsLabelForPid() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		self::assertContains(
			'Pid',
			$this->fixture->dumpUserValues('pid')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesDoesNotContainRawLabelNameAsLabelForPid() {
		$pid = $this->testingFramework->createSystemFolder();
		$this->fixture->setUserData(array('pid' => $pid));

		self::assertNotContains(
			'label_pid',
			$this->fixture->dumpUserValues('pid')
		);
	}

	/**
	 * @test
	 */
	public function dumpUserValuesCanContainNonRegisteredField() {
		$this->fixture->setUserData(array('is_dummy_record' => TRUE));

		self::assertContains(
			'Is_dummy_record: 1',
			$this->fixture->dumpUserValues('is_dummy_record')
		);
	}


	/*
	 * Tests for isPaid()
	 */

	/**
	 * @test
	 */
	public function isPaidInitiallyReturnsFalse() {
		self::assertFalse(
			$this->fixture->isPaid()
		);
	}

	/**
	 * @test
	 */
	public function isPaidForPaidRegistrationReturnsTrue() {
		$this->fixture->setPaymentDateAsUnixTimestamp($GLOBALS['SIM_EXEC_TIME']);

		self::assertTrue(
			$this->fixture->isPaid()
		);
	}

	/**
	 * @test
	 */
	public function isPaidForUnpaidRegistrationReturnsFalse() {
		$this->fixture->setPaymentDateAsUnixTimestamp(0);

		self::assertFalse(
			$this->fixture->isPaid()
		);
	}


	/*
	 * Tests regarding hasExistingFrontEndUser().
	 */

	/**
	 * @test
	 */
	public function hasExistingFrontEndUserWithExistingFrontEndUserReturnsTrue() {
		self::assertTrue(
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

		self::assertFalse(
			$this->fixture->hasExistingFrontEndUser()
		);
	}

	/**
	 * @test
	 */
	public function hasExistingFrontEndUserWithZeroFrontEndUserUIDReturnsFalse() {
		$this->fixture->setFrontEndUserUID(0);

		self::assertFalse(
			$this->fixture->hasExistingFrontEndUser()
		);
	}


	/*
	 * Tests regarding getFrontEndUser().
	 */

	/**
	 * @test
	 */
	public function getFrontEndUserWithExistingFrontEndUserReturnsFrontEndUser() {
		self::assertTrue(
			$this->fixture->getFrontEndUser() instanceof tx_oelib_Model_FrontEndUser
		);
	}


	/*
	 * Tests concerning setRegistrationData
	 */

	/**
	 * @test
	 */
	public function setRegistrationDataWithNoFoodOptionsInitializesFoodOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array()
		);

		self::assertTrue(
			is_array($this->fixture->getFoodsData())
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForFoodOptionsStoresFoodOptionsInFoodsVariable() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$foods = array('foo' => 'foo', 'bar' => 'bar');
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(),
			$userUid,
			array('foods' => $foods)
		);

		self::assertSame(
			$foods,
			$this->fixture->getFoodsData()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithEmptyFoodOptionsInitializesFoodOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array('foods' => '')
		);

		self::assertTrue(
			is_array($this->fixture->getFoodsData())
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithNoLodgingOptionsInitializesLodgingOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array()
		);

		self::assertTrue(
			is_array($this->fixture->getLodgingsData())
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithLodgingOptionsStoresLodgingOptionsInLodgingVariable() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$lodgings = array('foo' => 'foo', 'bar' => 'bar');
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(),
			$userUid,
			array('lodgings' => $lodgings)
		);

		self::assertSame(
			$lodgings,
			$this->fixture->getLodgingsData()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithEmptyLodgingOptionsInitializesLodgingOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array('lodgings' => '')
		);

		self::assertTrue(
			is_array($this->fixture->getLodgingsData())
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithNoCheckboxOptionsInitializesCheckboxOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array()
		);

		self::assertTrue(
			is_array($this->fixture->getCheckboxesData())
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithCheckboxOptionsStoresCheckboxOptionsInCheckboxVariable() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$checkboxes = array('foo' => 'foo', 'bar' => 'bar');
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(),
			$userUid,
			array('checkboxes' => $checkboxes)
		);

		self::assertSame(
			$checkboxes,
			$this->fixture->getCheckboxesData()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithEmptyCheckboxOptionsInitializesCheckboxOptionsAsArray() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid, array('checkboxes' => '')
		);

		self::assertTrue(
			is_array($this->fixture->getCheckboxesData())
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithRegisteredThemselvesGivenStoresRegisteredThemselvesIntoTheObject() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid,
			array('registered_themselves' => 1)
		);

		self::assertSame(
			$this->fixture->translate('label_yes'),
			$this->fixture->getRegistrationData('registered_themselves')
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataWithCompanyGivenStoresCompanyIntoTheObject() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setRegistrationData(
			$this->fixture->getSeminarObject(), $userUid,
			array('company' => 'Foo' . LF . 'Bar Inc')
		);

		self::assertSame(
			'Foo' . LF . 'Bar Inc',
			$this->fixture->getRegistrationData('company')
		);
	}


	/*
	 * Tests regarding the seats.
	 */

	/**
	 * @test
	 */
	public function getSeatsWithoutSeatsReturnsOne() {
		self::assertSame(
			1,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setSeatsWithNegativeSeatsThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $seats must be >= 0.'
		);

		$this->fixture->setSeats(-1);
	}

	/**
	 * @test
	 */
	public function setSeatsWithZeroSeatsSetsSeats() {
		$this->fixture->setSeats(0);

		self::assertSame(
			1,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setSeatsWithPositiveSeatsSetsSeats() {
		$this->fixture->setSeats(42);

		self::assertSame(
			42,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function hasSeatsWithoutSeatsReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasSeats()
		);
	}

	/**
	 * @test
	 */
	public function hasSeatsWithSeatsReturnsTrue() {
		$this->fixture->setSeats(42);

		self::assertTrue(
			$this->fixture->hasSeats()
		);
	}


	/*
	 * Tests regarding the attendees names.
	 */

	/**
	 * @test
	 */
	public function getAttendeesNamesWithoutAttendeesNamesReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function setAttendeesNamesWithAttendeesNamesSetsAttendeesNames() {
		$this->fixture->setAttendeesNames('John Doe');

		self::assertSame(
			'John Doe',
			$this->fixture->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendeesNamesWithoutAttendeesNamesReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendeesNamesWithAttendeesNamesReturnsTrue() {
		$this->fixture->setAttendeesNames('John Doe');

		self::assertTrue(
			$this->fixture->hasAttendeesNames()
		);
	}


	/*
	 * Tests regarding the kids.
	 */

	/**
	 * @test
	 */
	public function getNumberOfKidsWithoutKidsReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfKids()
		);
	}

	/**
	 * @test
	 */
	public function setNumberOfKidsWithNegativeNumberOfKidsThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $numberOfKids must be >= 0.'
		);

		$this->fixture->setNumberOfKids(-1);
	}

	/**
	 * @test
	 */
	public function setNumberOfKidsWithZeroNumberOfKidsSetsNumberOfKids() {
		$this->fixture->setNumberOfKids(0);

		self::assertSame(
			0,
			$this->fixture->getNumberOfKids()
		);
	}

	/**
	 * @test
	 */
	public function setNumberOfKidsWithPositiveNumberOfKidsSetsNumberOfKids() {
		$this->fixture->setNumberOfKids(42);

		self::assertSame(
			42,
			$this->fixture->getNumberOfKids()
		);
	}

	/**
	 * @test
	 */
	public function hasKidsWithoutKidsReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasKids()
		);
	}

	/**
	 * @test
	 */
	public function hasKidsWithKidsReturnsTrue() {
		$this->fixture->setNumberOfKids(42);

		self::assertTrue(
			$this->fixture->hasKids()
		);
	}


	/*
	 * Tests regarding the price.
	 */

	/**
	 * @test
	 */
	public function getPriceWithoutPriceReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function setPriceWithPriceSetsPrice() {
		$this->fixture->setPrice('Regular price: 42.42');

		self::assertSame(
			'Regular price: 42.42',
			$this->fixture->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasPriceWithoutPriceReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasPriceWithPriceReturnsTrue() {
		$this->fixture->setPrice('Regular price: 42.42');

		self::assertTrue(
			$this->fixture->hasPrice()
		);
	}


	/*
	 * Tests regarding the total price.
	 */

	/**
	 * @test
	 */
	public function getTotalPriceWithoutTotalPriceReturnsEmptyString() {
		self::assertSame(
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

		self::assertSame(
			'€ 42,42',
			$this->fixture->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasTotalPriceWithoutTotalPriceReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasTotalPriceWithTotalPriceReturnsTrue() {
		$this->fixture->setTotalPrice('42.42');

		self::assertTrue(
			$this->fixture->hasTotalPrice()
		);
	}


	/*
	 * Tests regarding the method of payment.
	 */

	/**
	 * @test
	 */
	public function getMethodOfPaymentUidWithoutMethodOfPaymentReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getMethodOfPaymentUid()
		);
	}

	/**
	 * @test
	 */
	public function setMethodOfPaymentUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $uid must be >= 0.'
		);

		$this->fixture->setMethodOfPaymentUid(-1);
	}

	/**
	 * @test
	 */
	public function setMethodOfPaymentUidWithZeroUidSetsMethodOfPaymentUid() {
		$this->fixture->setMethodOfPaymentUid(0);

		self::assertSame(
			0,
			$this->fixture->getMethodOfPaymentUid()
		);
	}

	/**
	 * @test
	 */
	public function setMethodOfPaymentUidWithPositiveUidSetsMethodOfPaymentUid() {
		$this->fixture->setMethodOfPaymentUid(42);

		self::assertSame(
			42,
			$this->fixture->getMethodOfPaymentUid()
		);
	}

	/**
	 * @test
	 */
	public function hasMethodOfPaymentWithoutMethodOfPaymentReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasMethodOfPayment()
		);
	}

	/**
	 * @test
	 */
	public function hasMethodOfPaymentWithMethodOfPaymentReturnsTrue() {
		$this->fixture->setMethodOfPaymentUid(42);

		self::assertTrue(
			$this->fixture->hasMethodOfPayment()
		);
	}


	/*
	 * Tests regarding the billing address.
	 */

	/**
	 * @test
	 */
	public function getBillingAddressWithGenderMaleContainsLabelForGenderMale() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('gender' => '0')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			$fixture->translate('label_gender.I.0'),
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithGenderFemaleContainsLabelForGenderFemale() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('gender' => '1')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			$fixture->translate('label_gender.I.1'),
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithNameContainsName() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('name' => 'John Doe')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			'John Doe',
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithAddressContainsAddress() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('address' => 'Main Street 123')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			'Main Street 123',
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithZipCodeContainsZipCode() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('zip' => '12345')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			'12345',
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithCityContainsCity() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('city' => 'Big City')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			'Big City',
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithCountryContainsCountry() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('country' => 'Takka-Tukka-Land')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			'Takka-Tukka-Land',
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithTelephoneNumberContainsTelephoneNumber() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('telephone' => '01234-56789')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			'01234-56789',
			$fixture->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getBillingAddressWithEMailAddressContainsEMailAddress() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('email' => 'john@doe.com')
		);
		$fixture = new tx_seminars_registrationchild($registrationUid);

		self::assertContains(
			'john@doe.com',
			$fixture->getBillingAddress()
		);
	}


	/*
	 * Tests concerning getEnumeratedAttendeeNames
	 */

	/**
	 * @test
	 */
	public function getEnumeratedAttendeeNamesWithUseHtmlSeparatesAttendeesNamesWithListItems() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => 'foo' . LF . 'bar')
		);

		self::assertSame(
			'<ol><li>foo</li><li>bar</li></ol>',
			$this->fixture->getEnumeratedAttendeeNames(TRUE)
		);
	}

	/**
	 * @test
	 */
	public function getEnumeratedAttendeeNamesWithUseHtmlAndEmptyAttendeesNamesReturnsEmptyString() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => '')
		);

		self::assertSame(
			'',
			$this->fixture->getEnumeratedAttendeeNames(TRUE)
		);
	}

	/**
	 * @test
	 */
	public function getEnumeratedAttendeeNamesWithUsePlainTextSeparatesAttendeesNamesWithLineFeed() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => 'foo' . LF . 'bar')
		);

		self::assertSame(
			'1. foo' . LF . '2. bar',
			$this->fixture->getEnumeratedAttendeeNames()
		);
	}

	/**
	 * @test
	 */
	public function getEnumeratedAttendeeNamesWithUsePlainTextAndEmptyAttendeesNamesReturnsEmptyString() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, 0, array('attendees_names' => '')
		);

		self::assertSame(
			'',
			$this->fixture->getEnumeratedAttendeeNames()
		);
	}

	/**
	 * @test
	 */
	public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndNoAttendeeNamesReturnsUsersName() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, $this->feUserUid, array('attendees_names' => '')
		);
		$this->fixture->setRegisteredThemselves(1);

		self::assertSame(
			'1. foo_user',
			$this->fixture->getEnumeratedAttendeeNames()
		);
	}

	/**
	 * @test
	 */
	public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndAttendeeNamesReturnsUserInFirstPosition() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData(
			$seminar, $this->feUserUid, array('attendees_names' => 'foo')
		);
		$this->fixture->setRegisteredThemselves(1);

		self::assertSame(
			'1. foo_user' . LF . '2. foo',
			$this->fixture->getEnumeratedAttendeeNames()
		);
	}


	/*
	 * Tests concerning hasRegisteredMySelf
	 */

	/**
	 * @test
	 */
	public function hasRegisteredMySelfForRegisteredThemselvesFalseReturnsFalse() {
		$this->fixture->setRegisteredThemselves(0);

		self::assertFalse(
			$this->fixture->hasRegisteredMySelf()
		);
	}

	/**
	 * @test
	 */
	public function hasRegisteredMySelfForRegisteredThemselvesTrueReturnsTrue() {
		$this->fixture->setRegisteredThemselves(1);

		self::assertTrue(
			$this->fixture->hasRegisteredMySelf()
		);
	}


	/*
	 * Tests concerning the food
	 */

	/**
	 * @test
	 */
	public function getFoodReturnsFood() {
		$food = 'a hamburger';

		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('food' => $food));

		self::assertSame(
			$food,
			$this->fixture->getFood()
		);
	}

	/**
	 * @test
	 */
	public function hasFoodForEmptyFoodReturnsFalse() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('food' => ''));

		self::assertFalse(
			$this->fixture->hasFood()
		);
	}

	/**
	 * @test
	 */
	public function hasFoodForNonEmptyFoodReturnsTrue() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('food' => 'two donuts'));

		self::assertTrue(
			$this->fixture->hasFood()
		);
	}


	/*
	 * Tests concerning the accommodation
	 */

	/**
	 * @test
	 */
	public function getAccommodationReturnsAccommodation() {
		$accommodation = 'a tent in the woods';

		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('accommodation' => $accommodation));

		self::assertSame(
			$accommodation,
			$this->fixture->getAccommodation()
		);
	}

	/**
	 * @test
	 */
	public function hasAccommodationForEmptyAccommodationReturnsFalse() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('accommodation' => ''));

		self::assertFalse(
			$this->fixture->hasAccommodation()
		);
	}

	/**
	 * @test
	 */
	public function hasAccommodationForNonEmptyAccommodationReturnsTrue() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('accommodation' => 'a youth hostel'));

		self::assertTrue(
			$this->fixture->hasAccommodation()
		);
	}


	/*
	 * Tests concerning the interests
	 */

	/**
	 * @test
	 */
	public function getInterestsReturnsInterests() {
		$interests = 'new experiences';

		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('interests' => $interests));

		self::assertSame(
			$interests,
			$this->fixture->getInterests()
		);
	}

	/**
	 * @test
	 */
	public function hasInterestsForEmptyInterestsReturnsFalse() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('interests' => ''));

		self::assertFalse(
			$this->fixture->hasInterests()
		);
	}

	/**
	 * @test
	 */
	public function hasInterestsForNonEmptyInterestsReturnsTrue() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$this->fixture->setRegistrationData($seminar, $this->feUserUid, array('interests' => 'meeting people'));

		self::assertTrue(
			$this->fixture->hasInterests()
		);
	}
}