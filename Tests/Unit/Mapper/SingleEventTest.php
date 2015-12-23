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
 * This test case holds tests which are specific to single events.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Mapper_SingleEventTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	/**
	 * @var Tx_Seminars_Mapper_Event
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$this->fixture = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class);
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
	public function getTopicForSingleRecordThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'This function may only be called for date records.'
		);

		$this->fixture->getLoadedTestingModel(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		)->getTopic();
	}


	//////////////////////////////////////
	// Tests regarding getCategories().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getCategoriesForSingleEventReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getCategories()
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForSingleEventWithOneCategoryReturnsListOfCategories() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$category = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $category->getUid(), 'categories'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertInstanceOf(Tx_Seminars_Model_Category::class, $model->getCategories()->first());
	}

	/**
	 * @test
	 */
	public function getCategoriesForSingleEventWithOneCategoryReturnsOneCategory() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$category = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
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
	public function getEventTypeForSingleEventWithoutEventTypeReturnsNull() {
		self::assertNull(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForSingleEventWithEventTypeReturnsEventTypeInstance() {
		$eventType = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_EventType::class)
			->getLoadedTestingModel(array());

		self::assertInstanceOf(
			Tx_Seminars_Model_EventType::class,
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
					'event_type' => $eventType->getUid(),
				)
			)->getEventType()
		);
	}


	/////////////////////////////////////////
	// Tests regarding getPaymentMethods().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentMethodsForSingleEventReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsListOfPaymentMethods() {
		$paymentMethod = Tx_Oelib_MapperRegistry::
			get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'payment_methods' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm', $uid, $paymentMethod->getUid()
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertInstanceOf(Tx_Seminars_Model_PaymentMethod::class, $model->getPaymentMethods()->first());
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsOnePaymentMethod() {
		$paymentMethod = Tx_Oelib_MapperRegistry::
			get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
	public function getTargetGroupsForSingleEventReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getTargetGroups()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsListOfTargetGroups() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$targetGroup = Tx_Oelib_MapperRegistry::
			get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getTargetGroups()->first() instanceof Tx_Seminars_Model_TargetGroup
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsOneTargetGroup() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$targetGroup = Tx_Oelib_MapperRegistry::
			get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
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

	/**
	 * @test
	 */
	public function getTargetGroupsForEventTopicReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getTargetGroups()
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
		$targetGroup = Tx_Oelib_MapperRegistry::
			get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getTargetGroups()->first() instanceof Tx_Seminars_Model_TargetGroup
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
		$targetGroup = Tx_Oelib_MapperRegistry::
			get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
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
	public function getCheckboxesForSingleEventReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getCheckboxes()
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForSingleEventWithOneCheckboxReturnsListOfCheckboxes() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$checkbox = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Checkbox::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $checkbox->getUid(), 'checkboxes'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertInstanceOf(Tx_Seminars_Model_Checkbox::class, $model->getCheckboxes()->first());
	}

	/**
	 * @test
	 */
	public function getCheckboxesForSingleEventWithOneCheckboxReturnsOneCheckbox() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$checkbox = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Checkbox::class)
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
	public function getRequirementsForSingleReturnsEmptyList() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getRequirements()->isEmpty()
		);
	}


	///////////////////////////////////////
	// Tests regarding getDependencies().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getDependenciesForSingleEventReturnsEmptyList() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getDependencies()->isEmpty()
		);
	}
}