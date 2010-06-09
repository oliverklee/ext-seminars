<?php
/***************************************************************
* Copyright notice
*
* (c) 2010 Niels Pardon (mail@niels-pardon.de)
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
require_once(PATH_typo3 . 'classes/class.typo3ajax.php');

/**
 * Testcase for the tx_seminars_BackEndExtJs_Ajax class in the "seminars"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_AjaxTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEndExtJs_Ajax
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * back-up of $_POST
	 *
	 * @var array
	 */
	private $postBackup;

	public function setUp() {
		$this->postBackup = $_POST;
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		tx_oelib_MapperRegistry::getInstance()->activateTestingMode(
			$this->testingFramework
		);
		$this->fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid')
		);
		$this->fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
	}

	public function tearDown() {
		$_POST = $this->postBackup;
		tx_oelib_MapperRegistry::purgeInstance();
		$this->testingFramework->cleanUp();
		unset($this->fixture, $this->testingFramework, $this->postBackup);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Returns an array containing the model's UID.
	 *
	 * This function is used as a return callback function.
	 *
	 * @param tx_oelib_Model $model the model to use
	 *
	 * @return array containing the model's UID
	 */
	static public function getArrayFromModel(tx_oelib_Model $model) {
		return array('uid' => $model->getUid());
	}


	//////////////////////////////////////
	// Tests regarding isPageUidValid().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function isPageUidValidWithZeroPageUidReturnsFalse() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$this->assertFalse(
			$fixture->isPageUidValid(0)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithNegativePageUidReturnsFalse() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$this->assertFalse(
			$fixture->isPageUidValid(-1)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithNonExistingPageUidReturnsFalse() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$this->assertFalse(
			$fixture->isPageUidValid(
				$this->testingFramework->getAutoIncrement('pages')
			)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithExistingNonSystemFolderUidReturnsFalse() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$this->assertFalse(
			$fixture->isPageUidValid(
				$this->testingFramework->createFrontEndPage()
			)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithExistingSystemFolderUidReturnsTrue() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$this->assertTrue(
			$fixture->isPageUidValid(
				$this->testingFramework->createSystemFolder()
			)
		);
	}


	//////////////////////////////////////
	// Tests regarding retrieveModels().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function retrieveModelsWithoutIdPostParameterCallsIsPageUidValidWithZero() {
		$ajaxObject = new TYPO3AJAX('');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(0);

		unset($_POST['id']);
		$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithNegativeIdPostParameterCallsIsPageUidValidWithNegativePageUid() {
		$ajaxObject = new TYPO3AJAX('');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(-1);

		$_POST['id'] = -1;
		$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithZeroIdPostParameterCallsIsPageUidValidWithZeroPageUid() {
		$ajaxObject = new TYPO3AJAX('');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(0);

		$_POST['id'] = 0;
		$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithPositiveIdPostParameterCallsIsPageUidValidWithPositivePageUid() {
		$ajaxObject = new TYPO3AJAX('');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(42);

		$_POST['id'] = 42;
		$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithNonIntegerPostParameterCallsIsPageUidValidWithZeroPageUid() {
		$ajaxObject = new TYPO3AJAX('');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(0);

		$_POST['id'] = 'foo';
		$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject);
	}

	/**
	 * @test
	 */
	public function retrieveModelsSetsResponseContentFormatToJson() {
		$ajaxObject = $this->getMock(
			'TYPO3AJAX',
			array('setContentFormat', 'setContent')
		);

		$ajaxObject->expects($this->atLeastOnce())
			->method('setContentFormat')
			->with('json');

		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithInvalidPageUidSetsSuccessFalseAndReturnsNull() {
		$ajaxObject = new TYPO3AJAX('');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax',
			array('isPageUidValid')
		);
		$fixture->expects($this->atLeastOnce())
			->method('isPageUidValid')
			->will($this->returnValue(FALSE));

		$this->assertNull(
			$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject)
		);
		$this->assertEquals(
			array('success' => FALSE),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithNonExistingMapperClassNameThrowsInvalidArgumentException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'A mapper with the name "tx_seminars_Mapper_Foo" could not be found.'
		);

		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$ajaxObject = new TYPO3AJAX('');

		$fixture->retrieveModels('tx_seminars_Mapper_Foo', $ajaxObject);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithOneModelReturnsModelsReturnedByMappersFindByPageUid() {
		$ajaxObject = new TYPO3AJAX('');

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax',
			array('isPageUidValid')
		);
		$fixture->expects($this->atLeastOnce())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Event',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Event', $mapper);

		$list = new tx_oelib_List();

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$this->assertSame(
			$list,
			$fixture->retrieveModels('tx_seminars_Mapper_Event', $ajaxObject)
		);
	}


	/////////////////////////////////
	// Tests regarding getEvents().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getEventsForRetrieveModelsReturningListInstanceSetsSuccessTrue() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax', array('retrieveModels')
		);

		$ajaxObject = new TYPO3AJAX('');

		$fixture->expects($this->once())
			->method('retrieveModels')
			->with('tx_seminars_Mapper_Event', $this->isInstanceOf('TYPO3AJAX'))
			->will($this->returnValue(new tx_oelib_List()));

		$fixture->getEvents(array(), $ajaxObject);
		$this->assertContains(
			array('success' => TRUE),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getEventsWithoutEventsSetsEmptyRowsInResponseContent() {
		$ajaxObject = new TYPO3AJAX('');

		$this->fixture->getEvents(array(), $ajaxObject);

		$this->assertEquals(
			array('success' => TRUE, 'rows' => array()),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getEventsWithOneEventSetsOneEventInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromEvent')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromEvent')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Event',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Event', $mapper);

		$list = new tx_oelib_list();

		$event = $mapper->getLoadedTestingModel(array());
		$list->add($event);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getEvents(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(array('uid' => $event->getUid())),
			),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getEventsWithTwoEventsSetsTwoEventsInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromEvent')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromEvent')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Event',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Event', $mapper);

		$list = new tx_oelib_list();

		$event1 = $mapper->getLoadedTestingModel(array());
		$list->add($event1);
		$event2 = $mapper->getLoadedTestingModel(array());
		$list->add($event2);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getEvents(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(
					array('uid' => $event1->getUid()),
					array('uid' => $event2->getUid()),
				),
			),
			$ajaxObject->getContent()
		);
	}


	/////////////////////////////////////////
	// Tests regarding getArrayFromEvent().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getArrayFromEventReturnsArrayContainingTheEventUid() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());

		$result = $fixture->getArrayFromEvent($event);

		$this->assertEquals(
			$event->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromEventReturnsArrayContainingTheEventRecordType() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
			);

		$result = $fixture->getArrayFromEvent($event);

		$this->assertEquals(
			$event->getRecordType(),
			$result['record_type']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromEventReturnsArrayContainingWetherTheEventIsHidden() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('hidden' => 1));

		$result = $fixture->getArrayFromEvent($event);

		$this->assertEquals(
			$event->isHidden(),
			$result['hidden']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromEventReturnsArrayContainingTheEventStatus() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('status' => tx_seminars_Model_Event::STATUS_CANCELED)
			);

		$result = $fixture->getArrayFromEvent($event);

		$this->assertEquals(
			$event->getStatus(),
			$result['status']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromEventReturnsArrayContainingTheEventTitle() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('title' => 'testing event'));

		$result = $fixture->getArrayFromEvent($event);

		$this->assertEquals(
			$event->getTitle(),
			$result['title']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromEventReturnsArrayContainingTheEventBeginDate() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('begin_date' => 42));

		$result = $fixture->getArrayFromEvent($event);

		$this->assertEquals(
			$event->getBeginDateAsUnixTimeStamp(),
			$result['begin_date']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromEventReturnsArrayContainingTheEventEndDate() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('end_date' => 42));

		$result = $fixture->getArrayFromEvent($event);

		$this->assertEquals(
			$event->getEndDateAsUnixTimeStamp(),
			$result['end_date']
		);
	}


	////////////////////////////////////////
	// Tests regarding getRegistrations().
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegistrationsForRetrieveModelsReturningListInstanceSetsSuccessTrue() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax', array('retrieveModels')
		);

		$ajaxObject = new TYPO3AJAX('');

		$fixture->expects($this->once())
			->method('retrieveModels')
			->with('tx_seminars_Mapper_Registration', $this->isInstanceOf('TYPO3AJAX'))
			->will($this->returnValue(new tx_oelib_List()));

		$fixture->getRegistrations(array(), $ajaxObject);
		$this->assertContains(
			array('success' => TRUE),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationsWithoutRegistrationsSetsEmptyRowsInResponseContent() {
		$ajaxObject = new TYPO3AJAX('');

		$this->fixture->getRegistrations(array(), $ajaxObject);

		$this->assertEquals(
			array('success' => TRUE, 'rows' => array()),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationsWithOneRegistrationSetsOneRegistrationInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromRegistration')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromRegistration')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Registration',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Registration', $mapper);

		$list = new tx_oelib_list();

		$registration = $mapper->getLoadedTestingModel(array());
		$list->add($registration);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getRegistrations(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(array('uid' => $registration->getUid())),
			),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationsWithTwoRegistrationsSetsTwoRegistrationsInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromRegistration')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromRegistration')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Registration',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Registration', $mapper);

		$list = new tx_oelib_list();

		$registration1 = $mapper->getLoadedTestingModel(array());
		$list->add($registration1);
		$registration2 = $mapper->getLoadedTestingModel(array());
		$list->add($registration2);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getRegistrations(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(
					array('uid' => $registration1->getUid()),
					array('uid' => $registration2->getUid()),
				),
			),
			$ajaxObject->getContent()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding getArrayFromRegistration().
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getArrayFromRegistrationReturnsArrayContainingRegistrationUid() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array());

		$result = $fixture->getArrayFromRegistration($registration);

		$this->assertEquals(
			$registration->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromRegistrationReturnsArrayContainingRegistrationTitle() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array('title' => 'testing registration'));

		$result = $fixture->getArrayFromRegistration($registration);

		$this->assertEquals(
			$registration->getTitle(),
			$result['title']
		);
	}


	///////////////////////////////////
	// Tests regarding getSpeakers().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getSpeakersForRetrieveModelsReturningListInstanceSetsSuccessTrue() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax', array('retrieveModels')
		);

		$ajaxObject = new TYPO3AJAX('');

		$fixture->expects($this->once())
			->method('retrieveModels')
			->with('tx_seminars_Mapper_Speaker', $this->isInstanceOf('TYPO3AJAX'))
			->will($this->returnValue(new tx_oelib_List()));

		$fixture->getSpeakers(array(), $ajaxObject);

		$this->assertContains(
			array('success' => TRUE),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithoutSpeakersSetsEmptyRowsInResponseContent() {
		$ajaxObject = new TYPO3AJAX('');

		$this->fixture->getSpeakers(array(), $ajaxObject);

		$this->assertEquals(
			array('success' => TRUE, 'rows' => array()),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithOneSpeakerSetsOneSpeakerInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromSpeaker')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromSpeaker')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Speaker',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Speaker', $mapper);

		$list = new tx_oelib_List();

		$speaker = $mapper->getLoadedTestingModel(array());
		$list->add($speaker);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getSpeakers(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(array('uid' => $speaker->getUid())),
			),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithTwoSpeakersSetsTwoSpeakersInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromSpeaker')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromSpeaker')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Speaker',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Speaker', $mapper);

		$list = new tx_oelib_List();

		$speaker1 = $mapper->getLoadedTestingModel(array());
		$list->add($speaker1);
		$speaker2 = $mapper->getLoadedTestingModel(array());
		$list->add($speaker2);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getSpeakers(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(
					array('uid' => $speaker1->getUid()),
					array('uid' => $speaker2->getUid()),
				),
			),
			$ajaxObject->getContent()
		);
	}


	///////////////////////////////////////////
	// Tests regarding getArrayFromSpeaker().
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getArrayFromSpeakerReturnsArrayContainingSpeakerUid() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getLoadedTestingModel(array());

		$result = $fixture->getArrayFromSpeaker($speaker);

		$this->assertEquals(
			$speaker->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromSpeakerReturnsArrayContainingSpeakerName() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getLoadedTestingModel(array('title' => 'testing speaker'));

		$result = $fixture->getArrayFromSpeaker($speaker);

		$this->assertEquals(
			$speaker->getName(),
			$result['title']
		);
	}


	/////////////////////////////////////
	// Tests regarding getOrganizers().
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizersForRetrieveModelsReturningListInstanceSetsSuccessTrue() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax', array('retrieveModels')
		);

		$ajaxObject = new TYPO3AJAX('');

		$fixture->expects($this->once())
			->method('retrieveModels')
			->with('tx_seminars_Mapper_Organizer', $this->isInstanceOf('TYPO3AJAX'))
			->will($this->returnValue(new tx_oelib_List()));

		$fixture->getOrganizers(array(), $ajaxObject);

		$this->assertContains(
			array('success' => TRUE),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithoutOrganizersSetsEmptyRowsInResponseContent() {
		$ajaxObject = new TYPO3AJAX('');

		$this->fixture->getOrganizers(array(), $ajaxObject);

		$this->assertEquals(
			array('success' => TRUE, 'rows' => array()),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithOneOrganizerSetsOneOrganizerInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromOrganizer')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromOrganizer')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Organizer',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Organizer', $mapper);

		$list = new tx_oelib_List();

		$organizer = $mapper->getLoadedTestingModel(array());
		$list->add($organizer);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getOrganizers(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(array('uid' => $organizer->getUid())),
			),
			$ajaxObject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithTwoOrganizersSetsTwoOrganizersInResponseContent() {
		$fixture = $this->getMock(
			'tx_seminars_BackEndExtJs_Ajax',
			array('isPageUidValid', 'getArrayFromOrganizer')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())
			->method('getArrayFromOrganizer')
			->will($this->returnCallback(array(get_class($this), 'getArrayFromModel')));

		$mapper = $this->getMock(
			'tx_seminars_Mapper_Organizer',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set('tx_seminars_Mapper_Organizer', $mapper);

		$list = new tx_oelib_List();

		$organizer1 = $mapper->getLoadedTestingModel(array());
		$list->add($organizer1);
		$organizer2 = $mapper->getLoadedTestingModel(array());
		$list->add($organizer2);

		$mapper->expects($this->any())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$ajaxObject = new TYPO3AJAX('');

		$fixture->getOrganizers(array(), $ajaxObject);
		$this->assertEquals(
			array(
				'success' => TRUE,
				'rows' => array(
					array('uid' => $organizer1->getUid()),
					array('uid' => $organizer2->getUid()),
				),
			),
			$ajaxObject->getContent()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding getArrayFromOrganizer().
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getArrayFromOrganizerReturnsArrayContainingOrganizerUid() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$organizer = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer')
			->getLoadedTestingModel(array());

		$result = $fixture->getArrayFromOrganizer($organizer);

		$this->assertEquals(
			$organizer->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getArrayFromOrganizerReturnsArrayContainingOrganizerName() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingAjax();

		$organizer = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer')
			->getLoadedTestingModel(array('title' => 'testing organizer'));

		$result = $fixture->getArrayFromOrganizer($organizer);

		$this->assertEquals(
			$organizer->getName(),
			$result['title']
		);
	}
}
?>