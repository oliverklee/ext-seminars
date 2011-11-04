<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2011 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_seminars_BackEndExtJs_Ajax_EventsList class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEndExtJs_Ajax_EventsListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList
	 */
	private $fixture;

	/**
	 * back-up of $GLOBALS['BE_USER']
	 *
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackUp;

	public function setUp() {
		$this->fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$this->backEndUserBackUp = $GLOBALS['BE_USER'];
	}

	public function tearDown() {
		$GLOBALS['BE_USER'] = $this->backEndUserBackUp;
		tx_oelib_MapperRegistry::purgeInstance();
		unset($this->fixture, $this->backEndUserBackUp);
	}


	/**
	 * @test
	 */
	public function mapperNameIsSetToEventsMapper() {
		$this->assertEquals(
			'tx_seminars_Mapper_Event',
			$this->fixture->getMapperName()
		);
	}


	/////////////////////////////////////////
	// Tests regarding getAsArray().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingEventUid() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			$event->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingEventPageUid() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('pid' => 42));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			42,
			$result['pid']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingRecordType() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			);
		$date = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topic->getUid(),
				)
			);

		$result = $this->fixture->getAsArray($date);

		$this->assertEquals(
			tx_seminars_Model_Event::TYPE_DATE,
			$result['record_type']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingWetherEventIsHidden() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('hidden' => 1));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			TRUE,
			$result['hidden']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingAccreditationNumber() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('accreditation_number' => '42'));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			'42',
			$result['accreditation_number']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingTitle() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('title' => 'testing event'));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			'testing event',
			$result['title']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingDateTitleForDateRecord() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
					'title' => 'topic title',
				)
			);
		$date = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topic->getUid(),
					'title' => 'date title',
				)
			);

		$result = $this->fixture->getAsArray($date);

		$this->assertSame(
			'date title',
			$result['title']
		);
	}


	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingBeginDate() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array(
				'begin_date' => $GLOBALS['SIM_ACCESS_TIME']
			));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			date('r', $GLOBALS['SIM_ACCESS_TIME']),
			$result['begin_date']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingEndDate() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array(
				'end_date' => $GLOBALS['SIM_ACCESS_TIME']
			));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			date('r', $GLOBALS['SIM_ACCESS_TIME']),
			$result['end_date']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingNumberOfRegularRegistrations() {
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array());
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$event->attachRegistration($registration);

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			1,
			$result['registrations_regular']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingNumberOfRegistrationsOnQueue() {
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array('registration_queue' => 1));
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$event->attachRegistration($registration);

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			1,
			$result['registrations_queue']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingMinimumNumberOfAttendees() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('attendees_min' => 1));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			1,
			$result['attendees_minimum']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingMaximumNumberOfAttendees() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('attendees_max' => 1));

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			1,
			$result['attendees_maximum']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingWhetherEventHasEnoughAttendees() {
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array('seats' => 1));
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('attendees_min' => 1));
		$event->attachRegistration($registration);

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			TRUE,
			$result['enough_attendees']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingWhetherEventIsFull() {
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array('seats' => 1));
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('attendees_max' => 1));
		$event->attachRegistration($registration);

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			TRUE,
			$result['is_full']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingStatus() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('cancelled' => tx_seminars_Model_Event::STATUS_CANCELED)
			);

		$result = $this->fixture->getAsArray($event);

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_CANCELED,
			$result['status']
		);
	}


	/////////////////////////////////
	// Tests regarding hasAccess().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function hasAccessWithBackEndUserIsAllowedReturnsTrue() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->expects($this->once())
			->method('check')
			->with('tables_select', 'tx_seminars_seminars')
			->will($this->returnValue(TRUE));

		$this->assertTrue(
			$this->fixture->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessWithBackEndUserIsNotAllowedReturnsFalse() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->expects($this->once())
			->method('check')
			->with('tables_select', 'tx_seminars_seminars')
			->will($this->returnValue(FALSE));

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}
}
?>