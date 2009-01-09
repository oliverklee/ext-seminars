<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_registrationchild.php');

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
	/** @var tx_seminars_registrationchild */
	private $fixture;
	/** @var tx_oelib_testingFramework */
	private $testingFramework;

	/** @var integer the UID of a seminar to which the fixture relates */
	private $seminarUid;

	/**
	 * @var integer the UID of the registration the fixture relates to
	 */
	private $registrationUid;

	public function setUp() {
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		tx_seminars_registrationchild::purgeCachedSeminars();

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
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
			SEMINARS_TABLE_SEMINARS_ORGANIZERS_MM,
			$this->seminarUid,
			$organizerUid
		);

		$feUserUid = $this->testingFramework->createFrontEndUser(
			'',
			array(
				'name' => 'foo_user',
				'email' => 'foo@bar.com',
			)
		);
		$this->registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->seminarUid,
				'interests' => 'nothing',
				'expectations' => '',
				'user' => $feUserUid,
			)
		);

		$this->fixture = new tx_seminars_registrationchild($this->registrationUid);
		$this->fixture->setConfigurationValue(
			'templateFile', 'EXT:seminars/seminars.tmpl'
		);
	}

	public function tearDown() {
		tx_oelib_mailerFactory::getInstance()->discardInstance();
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
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
			SEMINARS_TABLE_PAYMENT_METHODS, $paymentMethodData
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
		$this->fixture->setIsOnRegistrationQueue(false);

		$this->assertEquals(
			'regular',
			$this->fixture->getStatus()
		);
	}

	public function testStatusIsWaitingListIfOnQueue() {
		$this->fixture->setIsOnRegistrationQueue(true);

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

	public function testGetRegistrationDataWithKeyReferrerReturnsReferrer() {
		$this->fixture->setReferrer('test referrer');
		$this->assertEquals(
			'test referrer',
			$this->fixture->getRegistrationData('referrer')
		);
	}

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
		$this->testingFramework->markTableAsDirty(SEMINARS_TABLE_ATTENDANCES);

		$this->assertTrue(
			$registration->isOk()
		);
		$this->assertTrue(
			$registration->commitToDb()
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_ATTENDANCES,
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
	}

	public function testCommitToDbCanCreateLodgingsRelation() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$lodgingsUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_LODGINGS
		);

		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData(
			$seminar, 0, array('lodgings' => array($lodgingsUid))
		);
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty(SEMINARS_TABLE_ATTENDANCES);
		$this->testingFramework->markTableAsDirty(
			SEMINARS_TABLE_ATTENDANCES_LODGINGS_MM
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
				SEMINARS_TABLE_ATTENDANCES,
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_ATTENDANCES_LODGINGS_MM,
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$lodgingsUid
			),
			'The relation record cannot be found in the DB.'
		);
	}

	public function testCommitToDbCanCreateFoodsRelation() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$foodsUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_FOODS
		);

		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData(
			$seminar, 0, array('foods' => array($foodsUid))
		);
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty(SEMINARS_TABLE_ATTENDANCES);
		$this->testingFramework->markTableAsDirty(
			SEMINARS_TABLE_ATTENDANCES_FOODS_MM
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
				SEMINARS_TABLE_ATTENDANCES,
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_ATTENDANCES_FOODS_MM,
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$foodsUid
			),
			'The relation record cannot be found in the DB.'
		);
	}

	public function testCommitToDbCanCreateCheckboxesRelation() {
		$seminar = new tx_seminars_seminar($this->seminarUid);
		$checkboxesUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CHECKBOXES
		);

		$registration = new tx_seminars_registrationchild(0);
		$registration->setRegistrationData(
			$seminar, 0, array('checkboxes' => array($checkboxesUid))
		);
		$registration->enableTestMode();
		$this->testingFramework->markTableAsDirty(SEMINARS_TABLE_ATTENDANCES);
		$this->testingFramework->markTableAsDirty(
			SEMINARS_TABLE_ATTENDANCES_CHECKBOXES_MM
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
				SEMINARS_TABLE_ATTENDANCES,
				'uid='.$registration->getUid()
			),
			'The registration record cannot be found in the DB.'
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_ATTENDANCES_CHECKBOXES_MM,
				'uid_local='.$registration->getUid()
					.' AND uid_foreign='.$checkboxesUid
			),
			'The relation record cannot be found in the DB.'
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
			SEMINARS_TABLE_ATTENDANCES,
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


	//////////////////////////////////
	// Tests regarding the referrer.
	//////////////////////////////////

	public function testGetReferrerInitiallyReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getReferrer()
		);
	}

	public function testGetReferrerWithSetReferrerReturnsReferrer() {
		$this->fixture->setReferrer('test referrer');
		$this->assertEquals(
			'test referrer',
			$this->fixture->getReferrer()
		);
	}

	public function testHasReferrerInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasReferrer()
		);
	}

	public function testHasReferrerWithSetReferrerReturnsTrue() {
		$this->fixture->setReferrer('test referrer');
		$this->assertTrue(
			$this->fixture->hasReferrer()
		);
	}


	///////////////////////////////////////////////////
	// Tests regarding the notification of organizers
	///////////////////////////////////////////////////

	public function testNotifyOrganizersWithSetReferrerContainsReferrer() {
		$this->fixture->setReferrer('test referrer');
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'showAttendanceFieldsInNotificationMail', 'referrer'
		);

		$this->fixture->notifyOrganizers();

		$this->assertContains(
			'Referrer: test referrer',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	public function testNotifyOrganizersIncludesHelloIfNotHidden() {
		$this->fixture->setReferrer('test referrer');
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'hideFieldsInNotificationMail', ''
		);

		$this->fixture->notifyOrganizers();
		$this->assertContains(
			'Hello',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	public function testNotifyOrganizersCanHideHelloByConfiguration() {
		$this->fixture->setReferrer('test referrer');
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'hideFieldsInNotificationMail', 'hello'
		);

		$this->fixture->notifyOrganizers();
		$this->assertNotContains(
			'Hello',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}


	////////////////////////////////////////////////
	// Tests for setting and getting the user data
	////////////////////////////////////////////////

	public function testInstantiationWithoutLoggedInUserDoesNotThrowException() {
		$this->testingFramework->logoutFrontEndUser();

		new tx_seminars_registrationchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_ATTENDANCES,
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

	public function testGetUserDataCanReturnNameSetViaSetUserData() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		$this->assertEquals(
			'John Doe',
			$this->fixture->getUserData('name')
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

	public function testDumpUserValuesContainsUserNameIfRequested() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		$this->assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('name')
		);
	}

	public function testDumpUserValuesContainsUserNameIfRequestedEvenForSpaceAfterCommaInKeyList() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		$this->assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('email, name')
		);
	}

	public function testDumpUserValuesContainsUserNameIfRequestedEvenForSpaceBeforeCommaInKeyList() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		$this->assertContains(
			'John Doe',
			$this->fixture->dumpUserValues('name ,email')
		);
	}

	public function testDumpUserValuesContainsLabelForUserNameIfRequested() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		$this->assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('name')
		);
	}

	public function testDumpUserValuesContainsLabelEvenForSpaceAfterCommaInKeyList() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

		$this->assertContains(
			$this->fixture->translate('label_name'),
			$this->fixture->dumpUserValues('email, name')
		);
	}

	public function testDumpUserValuesContainsLabelEvenForSpaceBeforeCommaInKeyList() {
		$this->fixture->setUserData(array('name' => 'John Doe'));

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
		$this->fixture->setIsPaid(true);

		$this->assertTrue(
			$this->fixture->isPaid()
		);
	}

	public function testIsPaidForUnpaidRegistrationReturnsFalse() {
		$this->fixture->setIsPaid(false);

		$this->assertFalse(
			$this->fixture->isPaid()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning sendAdditionalNotification
	////////////////////////////////////////////////

	public function testSendAdditionalNotificationCanSendEmailToOneOrganizer() {
		$this->fixture->sendAdditionalNotification();

		$this->assertContains(
			'mail@example.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastRecipient()
		);
	}

	public function testSendAdditionalNotificationCanSendEmailsToTwoOrganizers() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'test organizer 2',
				'email' => 'mail2@example.com',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			$organizerUid, 'organizers'
		);

		$this->fixture->sendAdditionalNotification();

		$this->assertEquals(
			2,
			count(
				tx_oelib_mailerFactory::getInstance()
					->getMailer()->getAllEmail()
			)
		);
	}

	public function testSendAdditionalNotificationForEventWithEnoughAttendancesSendsEnoughAttendancesMail() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('attendees_min' => 1, 'attendees_max' => 42)
		);

		tx_seminars_registrationchild::purgeCachedSeminars();
		$fixture = new tx_seminars_registrationchild($this->registrationUid);

		$fixture->sendAdditionalNotification();
		$fixture->__destruct();

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_additionalNotificationEnoughRegistrationsSubject'
				),
				$this->seminarUid,
				''
			),
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastSubject()
		);
	}

	public function testSendAdditionalNotificationForBookedOutEventSendsBookedOutMail() {
		$this->fixture->sendAdditionalNotification();

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_additionalNotificationIsFullSubject'
				),
				$this->seminarUid,
				''
			),
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastSubject()
		);
	}

	public function testSendAdditionalNotificationforEventWithNotEnoughAttendancesAndNotBookedOutSendsNoEmail() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('attendees_min' => 5, 'attendees_max' => 5)
		);

		tx_seminars_registrationchild::purgeCachedSeminars();
		$fixture = new tx_seminars_registrationchild($this->registrationUid);

		$fixture->sendAdditionalNotification();
		$fixture->__destruct();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getAllEmail())
		);
	}


	////////////////////////////////////
	// Tests concerning notifyAttendee
	////////////////////////////////////

	public function testNotifyAttendeeSendsMailToAttendeesMailAdress() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($pi1);
		$pi1->__destruct();

		$this->assertEquals(
			'foo@bar.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastRecipient()
		);
	}

	public function testNotifyAttendeeMailSubjectContainsConfirmationSubject() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($pi1);
		$pi1->__destruct();

		$this->assertContains(
			$this->fixture->translate('email_confirmationSubject'),
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastSubject()
		);
	}

	public function testNotifyAttendeeMailBodyContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($pi1);
		$pi1->__destruct();

		$this->assertContains(
			'foo_event',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function testNotifyAttendeeMailSubjectContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($pi1);
		$pi1->__destruct();

		$this->assertContains(
			'foo_event',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastSubject()
		);
	}

	public function testNotifyAttendeeSetsOrganizerAsSender() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($pi1);
		$pi1->__destruct();

		$this->assertContains(
			'From: "test organizer" <mail@example.com>',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastHeaders()
		);
	}
}
?>