<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the EmailSalutation class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_EmailSalutation_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework the testing framework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_EmailSalutation the fixture the tests relate to
	 */
	private $fixture;

	/**
	 * @var array backed-up extension configuration of the TYPO3 configuration
	 *            variables
	 */
	private $extConfBackup = array();

	/**
	 * @var array backed-up T3_VAR configuration
	 */
	private $t3VarBackup = array();

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixture = new tx_seminars_EmailSalutation();
		$configuration = new tx_oelib_Configuration();
		$configuration->setAsString('salutation', 'formal');
		tx_oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_seminars', $configuration);
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
	}

	public function tearDown() {
		$this->fixture->__destruct();
		$this->testingFramework->cleanUp();
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;

		unset($this->testingFramework, $this->fixture);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates an FE-user with the given gender and the name "Foo".
	 *
	 * @param integer $gender
	 *        the gender for the FE user, must be one of
	 *        "tx_oelib_Model_FrontEndUser::GENDER_MALE",
	 *        "tx_oelib_Model_FrontEndUser::GENDER_FEMALE" or
	 *        "tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN", may be empty
	 *
	 * @return tx_seminars_Model_FrontEndUser the loaded testing model of a
	 *                                        FE user
	 */
	private function createFrontEndUser(
		$gender = tx_oelib_Model_FrontEndUser::GENDER_MALE
	) {
		return tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(
				array('name' => 'Foo', 'gender' => $gender)
		);
	}


	///////////////////////////////////////////
	// Tests concerning the utility functions
	///////////////////////////////////////////

	public function test_createFrontEndUser_ReturnsFeUserModel() {
		$this->assertTrue(
			$this->createFrontEndUser() instanceof tx_seminars_Model_FrontEndUser
		);
	}

	public function test_createFrontEndUserForGivenGender_AssignesGenderToFrontEndUser() {
		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->createFrontEndUser(tx_oelib_Model_FrontEndUser::GENDER_FEMALE)
				->getGender()
		);
	}


	///////////////////////////////////
	// Tests concerning getSalutation
	///////////////////////////////////

	public function test_getSalutation_ReturnsUsernameOfRegistration() {
		$this->assertContains(
			'Foo',
			$this->fixture->getSalutation($this->createFrontEndUser())
		);
	}

	public function test_getSalutationForMaleUser_ReturnsMaleSalutation() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_MALE
		);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_hello_formal_0'),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForMaleUser_ReturnsUsersNameWithGenderSpecificTitle() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_MALE
		);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_salutation_title_0') . ' ' .
				$user->getLastOrFullName(),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForFemaleUser_ReturnsFemaleSalutation() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE
		);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_hello_formal_1'),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForFemaleUser_ReturnsUsersNameWithGenderSpecificTitle() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE
		);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_salutation_title_1') . ' ' .
				$user->getLastOrFullName(),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForUnknownUser_ReturnsUnknownSalutation() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN
		);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_hello_formal_2'),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForUnknownUser_ReturnsUsersNameWithGenderSpecificTitle() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN
		);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_salutation_title_2') . ' ' .
				$user->getLastOrFullName(),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForInformalSalutation_ReturnsInformalSalutation() {
		$user = $this->createFrontEndUser();
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('salutation', 'informal');

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
					->translate('email_hello_informal'),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForInformalSalutation_ReturnsUsersName() {
		$user = $this->createFrontEndUser();
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('salutation', 'informal');

		$this->assertContains(
			$user->getLastOrFullName(),
			$this->fixture->getSalutation($user)
		);
	}


	///////////////////////////////
	// Tests concerning the hooks
	///////////////////////////////

	public function test_getSalutationForHookSetInConfigurationCallsThisHook() {
		$hookClassName = uniqid('tx_salutationHook');
		$salutationHookMock = $this->getMock(
			$hookClassName, array('modifySalutation')
		);
		$salutationHookMock->expects($this->atLeastOnce())
			->method('modifySalutation');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $salutationHookMock;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
			['modifyEmailSalutation'][$hookClassName] = $hookClassName;

		$this->fixture->getSalutation($this->createFrontEndUser());
	}

	public function test_getSalutationCanCallMultipleSetHooks() {
		$hookClassName1 = uniqid('tx_salutationHook1');
		$salutationHookMock1 = $this->getMock(
			$hookClassName1, array('modifySalutation')
		);
		$salutationHookMock1->expects($this->atLeastOnce())
			->method('modifySalutation');
		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName1] = $salutationHookMock1;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
			['modifyEmailSalutation'][$hookClassName1] = $hookClassName1;

		$hookClassName2 = uniqid('tx_salutationHook2');
		$salutationHookMock2 = $this->getMock(
			$hookClassName2, array('modifySalutation')
		);
		$salutationHookMock2->expects($this->atLeastOnce())
			->method('modifySalutation');
		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName2] = $salutationHookMock2;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
			['modifyEmailSalutation'][$hookClassName2] = $hookClassName2;

		$this->fixture->getSalutation($this->createFrontEndUser());
	}


	////////////////////////////////////////
	// Tests concerning createIntroduction
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function createIntroductionForEventWithDateReturnsEventsDate() {
		$dateFormatYMD = '%d.%m.%Y';
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'])
		);

		$event = new tx_seminars_seminarchild($eventUid, array(
			'dateFormatYMD' => $dateFormatYMD
		));

		$this->assertContains(
			strftime($dateFormatYMD, $GLOBALS['SIM_EXEC_TIME']),
			$this->fixture->createIntroduction('%s', $event)
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function createIntroductionForEventWithBeginAndEndDateOnDifferentDaysReturnsEventsDateFromTo() {
		$dateFormatYMD = '%d.%m.%Y';
		$dateFormatD = '%d';
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
			)
		);

		$event = new tx_seminars_seminarchild($eventUid, array(
			'dateFormatYMD' => $dateFormatYMD,
			'dateFormatD' => $dateFormatD,
			'abbreviateDateRanges' => 1,
		));

		$this->assertContains(
			strftime($dateFormatD, $GLOBALS['SIM_EXEC_TIME']) .
				'-' .
				strftime($dateFormatYMD, $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY),
			$this->fixture->createIntroduction('%s', $event)
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function createIntroductionForEventWithTimeReturnsEventsTime() {
		$timeFormat = '%H:%M';
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$event = new tx_seminars_seminarchild($eventUid, array(
			'timeFormat' => $timeFormat,
		));

		$this->assertContains(
			strftime($timeFormat, $GLOBALS['SIM_EXEC_TIME']),
			$this->fixture->createIntroduction('%s', $event)
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function createIntroductionForEventWithStartAndEndOnOneDayReturnsTimeFromTo() {
		$timeFormat = '%H:%M';
		$endDate = $GLOBALS['SIM_EXEC_TIME'] + 3600;
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
				'end_date' => $endDate,
			)
		);

		$event = new tx_seminars_seminarchild($eventUid, array(
			'timeFormat' => $timeFormat,
		));
		$translator = tx_oelib_TranslatorRegistry::getInstance()->get('seminars');
		$timeInsert = strftime($timeFormat, $GLOBALS['SIM_EXEC_TIME']) . ' ' .
			$translator->translate('email_timeTo') . ' ' .
			strftime($timeFormat, $endDate);

		$this->assertContains(
			sprintf(
				$translator->translate('email_timeFrom'),
				$timeInsert
			),
			$this->fixture->createIntroduction('%s', $event)
		);

		$event->__destruct();
	}
}
?>