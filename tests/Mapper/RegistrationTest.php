<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Mapper_RegistrationTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_Mapper_Registration
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_Mapper_Registration();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidReturnsRegistrationInstance() {
		$this->assertTrue(
			$this->fixture->find(1) instanceof tx_seminars_Model_Registration
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('title' => 'registration for event')
		);

		$this->assertEquals(
			'registration for event',
			$this->fixture->find($uid)->getTitle()
		);
	}


	////////////////////////////////
	// Tests concerning the event.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getEventWithEventReturnsEventInstance() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getNewGhost();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('seminar' => $event->getUid())
			)->getEvent() instanceof
				tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getSeminarWithEventReturnsEventInstance() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getNewGhost();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('seminar' => $event->getUid())
			)->getSeminar() instanceof
				tx_seminars_Model_Event
		);
	}


	/////////////////////////////////////////
	// Tests concerning the front-end user.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getFrontEndUserWithFrontEndUserReturnsFrontEndUserInstance() {
		$frontEndUser = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_FrontEndUser')->getNewGhost();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('user' => $frontEndUser->getUid())
			)->getFrontEndUser() instanceof
				tx_seminars_Model_FrontEndUser
		);
	}


	/////////////////////////////////////////
	// Tests concerning the payment method.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentMethodWithoutPaymentMethodReturnsNull() {
		$this->assertNull(
			$this->fixture->getLoadedTestingModel(array())->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodWithPaymentMethodReturnsPaymentMethodInstance() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('method_of_payment' => $paymentMethod->getUid())
			)->getPaymentMethod() instanceof
				tx_seminars_Model_PaymentMethod
		);
	}


	///////////////////////////////////
	// Tests concerning the lodgings.
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getLodgingsReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getLodgings()
				instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsListOfLodgings() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$lodging = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Lodging')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $lodging->getUid(), 'lodgings'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getLodgings()->first() instanceof
				tx_seminars_Model_Lodging
		);
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsOneLodging() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$lodging = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Lodging')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $lodging->getUid(), 'lodgings'
		);

		$this->assertEquals(
			$lodging->getUid(),
			$this->fixture->find($uid)->getLodgings()->first()->getUid()
		);
	}


	////////////////////////////////
	// Tests concerning the foods.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getFoodsReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getFoods()
				instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsListOfFoods() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$food = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Food')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $food->getUid(), 'foods'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getFoods()->first() instanceof
				tx_seminars_Model_Food
		);

	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsOneFood() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$food = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Food')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $food->getUid(), 'foods'
		);

		$this->assertEquals(
			$food->getUid(),
			$this->fixture->find($uid)->getFoods()->first()->getUid()
		);
	}


	/////////////////////////////////////
	// Tests concerning the checkboxes.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getCheckboxesReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getCheckboxes()
				instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesWithOneCheckboxReturnsListOfCheckboxes() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertEquals(
			$checkbox->getUid(),
			$this->fixture->find($uid)->getCheckboxes()->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesWithOneCheckboxReturnsOneCheckbox() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertEquals(
			$checkbox->getUid(),
			$this->fixture->find($uid)->getCheckboxes()->first()->getUid()
		);
	}


	///////////////////////////////////////////////////////////////////////
	// Tests concerning the relation to the additional registered persons
	///////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function relationToAdditionalPersonsReturnsPersonsFromDatabase() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('additional_persons' => 1)
		);
		$personUid = $this->testingFramework->createFrontEndUser(
			'', array('tx_seminars_registration' => $registrationUid)
		);

		$this->assertEquals(
			(string) $personUid,
			$this->fixture->find($registrationUid)
				->getAdditionalPersons()->getUids()
		);
	}
}