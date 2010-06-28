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

/**
 * Testcase for the tx_seminars_BackEndExtJs_Ajax_EventsList class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_EventsListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();
	}

	public function tearDown() {
		tx_oelib_MapperRegistry::purgeInstance();
		unset($this->fixture);
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
	public function getAsArrayReturnsArrayContainingTheEventUid() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());

		$result = $fixture->getAsArray($event);

		$this->assertEquals(
			$event->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingTheEventRecordType() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
			);

		$result = $fixture->getAsArray($event);

		$this->assertEquals(
			$event->getRecordType(),
			$result['record_type']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingWetherTheEventIsHidden() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('hidden' => 1));

		$result = $fixture->getAsArray($event);

		$this->assertEquals(
			$event->isHidden(),
			$result['hidden']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingTheEventStatus() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('status' => tx_seminars_Model_Event::STATUS_CANCELED)
			);

		$result = $fixture->getAsArray($event);

		$this->assertEquals(
			$event->getStatus(),
			$result['status']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingTheEventTitle() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('title' => 'testing event'));

		$result = $fixture->getAsArray($event);

		$this->assertEquals(
			$event->getTitle(),
			$result['title']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingTheEventBeginDate() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('begin_date' => 42));

		$result = $fixture->getAsArray($event);

		$this->assertEquals(
			$event->getBeginDateAsUnixTimeStamp(),
			$result['begin_date']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingTheEventEndDate() {
		$fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingEventsList();

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('end_date' => 42));

		$result = $fixture->getAsArray($event);

		$this->assertEquals(
			$event->getEndDateAsUnixTimeStamp(),
			$result['end_date']
		);
	}
}
?>