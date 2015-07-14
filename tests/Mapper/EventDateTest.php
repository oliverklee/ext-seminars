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
 * This test case holds all tests specific to event dates.
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

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	/////////////////////////////////
	// Tests regarding getTopic().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getTopicWithoutTopicReturnsNull() {
		self::assertNull(
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

		self::assertTrue(
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

		self::assertTrue(
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getCategories()->first() instanceof tx_seminars_Model_Category
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$category->getUid(),
			$model->getCategories()->getUids()
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

		self::assertNull(
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

		self::assertTrue(
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

		self::assertTrue(
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getPaymentMethods()->first() instanceof tx_seminars_Model_PaymentMethod
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$paymentMethod->getUid(),
			$model->getPaymentMethods()->getUids()
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

		self::assertTrue(
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getTargetGroups()->first() instanceof tx_seminars_Model_TargetGroup
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$targetGroup->getUid(),
			$model->getTargetGroups()->getUids()
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

		self::assertTrue(
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getCheckboxes()->first() instanceof
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$checkbox->getUid(),
			$model->getCheckboxes()->getUids()
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

		self::assertTrue(
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getRequirements()->first() instanceof tx_seminars_Model_Event
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$event->getUid(),
			$model->getRequirements()->getUids()
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

		self::assertTrue(
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getDependencies()->first() instanceof tx_seminars_Model_Event
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$topicUid,
			$model->getDependencies()->getUids()
		);
	}
}