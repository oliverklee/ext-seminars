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
 * This test case holds all tests specific to event topics.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Mapper_EventTopicTest extends tx_phpunit_testcase {
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
	public function getTopicForTopicRecordThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'This function may only be called for date records.'
		);

		$this->fixture->getLoadedTestingModel(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		)->getTopic();
	}


	//////////////////////////////////////
	// Tests regarding getCategories().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getCategoriesForEventTopicReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getCategories() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventTopicWithOneCategoryReturnsListOfCategories() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $category->getUid(), 'categories'
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
	public function getCategoriesForEventTopicWithOneCategoryReturnsOneCategory() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $category->getUid(), 'categories'
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
	public function getEventTypeForEventTopicWithoutEventTypeReturnsNull() {
		self::assertNull(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForEventTopicWithEventTypeReturnsEventTypeInstance() {
		$eventType = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_EventType')
			->getLoadedTestingModel(array());

		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
					'event_type' => $eventType->getUid(),
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
	public function getPaymentMethodsForEventTopicReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getPaymentMethods() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsListOfPaymentMethods() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'payment_methods' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm', $uid, $paymentMethod->getUid()
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
	public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsOnePaymentMethod() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'payment_methods' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm', $uid, $paymentMethod->getUid()
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
	public function getTargetGroupsForEventTopicReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getTargetGroups() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsListOfTargetGroups() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
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
	public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsOneTargetGroup() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
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
	public function getCheckboxesForEventTopicReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getCheckboxes() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventTopicWithOneCheckboxReturnsListOfCheckboxes() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $checkbox->getUid(), 'checkboxes'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getCheckboxes()->first() instanceof tx_seminars_Model_Checkbox
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventTopicWithOneCheckboxReturnsOneCheckbox() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $checkbox->getUid(), 'checkboxes'
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
	public function getRequirementsForEventTopicReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getRequirements() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventTopicWithOneRequirementReturnsListOfEvents() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $event->getUid(), 'requirements'
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
	public function getRequirementsForEventTopicWithOneRequirementsReturnsOneRequirement() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $event->getUid(), 'requirements'
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
	public function getDependenciesForEventTopicReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getDependencies() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventTopicWithOneDependencyReturnsListOfEvents() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $uid, 'dependencies'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($relatedUid);
		self::assertTrue(
			$model->getDependencies()->first() instanceof tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventTopicWithOneDependencyReturnsOneDependency() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $uid, 'dependencies'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($relatedUid);
		self::assertEquals(
			$uid,
			$model->getDependencies()->getUids()
		);
	}
}