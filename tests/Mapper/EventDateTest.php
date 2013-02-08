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
 * Testcase for the 'event mapper' class in the 'seminars' extension.
 *
 * This testcase holds all tests specific to event dates.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Mapper_EventDateTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_Mapper_Event
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}


	/////////////////////////////////
	// Tests regarding getTopic().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getTopicWithoutTopicReturnsNull() {
		$this->assertNull(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
			)->getTopic()
		);
	}

	/**
	 * @test
	 */
	public function getTopicWithTopicReturnsEventInstance() {
		$topic = $this->fixture->getNewGhost();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'topic' => $topic->getUid(),
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				)
			)->getTopic() instanceof tx_seminars_Model_Event
		);
	}


	//////////////////////////////////////
	// Tests regarding getCategories().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getCategoriesForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getCategories() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventDateWithOneCategoryReturnsListOfCategories() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $category->getUid(), 'categories'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCategories()->first() instanceof
				tx_seminars_Model_Category
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventDateWithOneCategoryReturnsOneCategory() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $category->getUid(), 'categories'
		);

		$this->assertEquals(
			$category->getUid(),
			$this->fixture->find($uid)->getCategories()->getUids()
		);
	}


	////////////////////////////////////
	// Tests regarding getEventType().
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getEventTypeForEventDateWithoutEventTypeReturnsNull() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());

		$this->assertNull(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topic,
				)
			)->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForEventDateWithEventTypeReturnsEventTypeInstance() {
		$eventType = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_EventType')
			->getLoadedTestingModel(array());
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('event_type' => $eventType->getUid()));

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topic->getUid(),
				)
			)->getEventType() instanceof tx_seminars_Model_EventType
		);
	}


	/////////////////////////////////////////
	// Tests regarding getPaymentMethods().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getPaymentMethods() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsListOfPaymentMethods() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('payment_methods' => 1)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm', $topicUid,
			$paymentMethod->getUid()
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getPaymentMethods()->first() instanceof
				tx_seminars_Model_PaymentMethod
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsOnePaymentMethod() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('payment_methods' => 1)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm', $topicUid,
			$paymentMethod->getUid()
		);

		$this->assertEquals(
			$paymentMethod->getUid(),
			$this->fixture->find($uid)->getPaymentMethods()->getUids()
		);
	}


	///////////////////////////////////////
	// Tests regarding getTargetGroups().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getTargetGroupsForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getTargetGroups() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventDateWithOneTargetGroupReturnsListOfTargetGroups() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getTargetGroups()->first() instanceof
				tx_seminars_Model_TargetGroup
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventDateWithOneTargetGroupReturnsOneTargetGroup() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertEquals(
			$targetGroup->getUid(),
			$this->fixture->find($uid)->getTargetGroups()->getUids()
		);
	}


	/////////////////////////////////////
	// Tests regarding getCheckboxes().
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getCheckboxesForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getCheckboxes() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventDateWithOneCheckboxReturnsListOfCheckboxes() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCheckboxes()->first() instanceof
				tx_seminars_Model_Checkbox
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventDateWithOneCheckboxReturnsOneCheckbox() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertEquals(
			$checkbox->getUid(),
			$this->fixture->find($uid)->getCheckboxes()->getUids()
		);
	}


	///////////////////////////////////////
	// Tests regarding getRequirements().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getRequirementsForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getRequirements() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventDateWithOneRequirementReturnsListOfEvents() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $event->getUid(), 'requirements'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getRequirements()->first() instanceof
				tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventDateWithOneRequirementsReturnsOneRequirement() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $event->getUid(), 'requirements'
		);

		$this->assertEquals(
			$event->getUid(),
			$this->fixture->find($uid)->getRequirements()->getUids()
		);
	}


	///////////////////////////////////////
	// Tests regarding getDependencies().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getDependenciesForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getDependencies() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventDateWithOneDependencyReturnsListOfEvents() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $relatedUid,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $topicUid, 'dependencies'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getDependencies()->first() instanceof
				tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventDateWithOneDependencyReturnsOneDependency() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $relatedUid,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $topicUid, 'dependencies'
		);

		$this->assertEquals(
			$topicUid,
			$this->fixture->find($uid)->getDependencies()->getUids()
		);
	}
}
?>