<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_seminars_BackEndExtJs_Ajax_RegistrationsList class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_RegistrationsListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingRegistrationsList
	 */
	private $fixture;

	/**
	 * back-up of $GLOBALS['BE_USER']
	 *
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackUp;

	public function setUp() {
		$this->fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingRegistrationsList();

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
			'tx_seminars_Mapper_Registration',
			$this->fixture->getMapperName()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding getAsArray().
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingRegistrationUid() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(array());
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array(
				'user' => $frontEndUser, 'seminar' => $event,
			));

		$result = $this->fixture->getAsArray($registration);

		$this->assertEquals(
			$registration->getUid(),
			$result['uid']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingRegistrationPageUid() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(array());
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array(
				'user' => $frontEndUser, 'seminar' => $event, 'pid' => 42,
			));

		$result = $this->fixture->getAsArray($registration);

		$this->assertEquals(
			42,
			$result['pid']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingHtmlspecialcharedFrontEndUserName() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(array('name' => 'John & Doe'));
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array(
				'user' => $frontEndUser, 'seminar' => $event,
			));

		$result = $this->fixture->getAsArray($registration);

		$this->assertSame(
			'John &amp; Doe',
			$result['name']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingHtmlspecialcharedEventAccreditationNumber() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(array());
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array(
				'accreditation_number' => '42 & 3',
			));
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array(
				'user' => $frontEndUser, 'seminar' => $event,
			));

		$result = $this->fixture->getAsArray($registration);

		$this->assertSame(
			'42 &amp; 3',
			$result['event_accreditation_number']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingHtmlspecialcharedEventTitle() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(array());
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array(
				'title' => 'testing & event',
			));
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array(
				'user' => $frontEndUser, 'seminar' => $event,
			));

		$result = $this->fixture->getAsArray($registration);

		$this->assertSame(
			'testing &amp; event',
			$result['event_title']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingEventBeginDate() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(array());
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array(
				'begin_date' => $GLOBALS['SIM_ACCESS_TIME'],
			));
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array(
				'user' => $frontEndUser, 'seminar' => $event,
			));

		$result = $this->fixture->getAsArray($registration);

		$this->assertEquals(
			date('r', $GLOBALS['SIM_ACCESS_TIME']),
			$result['event_begin_date']
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayReturnsArrayContainingEventEndDate() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(array());
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array(
				'end_date' => $GLOBALS['SIM_ACCESS_TIME'],
			));
		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array(
				'user' => $frontEndUser, 'seminar' => $event,
			));

		$result = $this->fixture->getAsArray($registration);

		$this->assertEquals(
			date('r', $GLOBALS['SIM_ACCESS_TIME']),
			$result['event_end_date']
		);
	}


	///////////////////////////////////////////
	// Tests regarding isAllowedToListView().
	///////////////////////////////////////////

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
			->with('tables_select', 'tx_seminars_attendances')
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
			->with('tables_select', 'tx_seminars_attendances')
			->will($this->returnValue(FALSE));

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}
}
?>