<?php

/**
 * This test case holds tests which are specific to single events.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_SingleEventTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /////////////////////////////////
    // Tests regarding getTopic().
    /////////////////////////////////

    /**
     * @test
     */
    public function getTopicForSingleRecordThrowsException()
    {
        $this->setExpectedException(
            \BadMethodCallException::class,
            'This function may only be called for date records.'
        );

        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $testingModel->getTopic();
    }

    //////////////////////////////////////
    // Tests regarding getCategories().
    //////////////////////////////////////

    /**
     * @test
     */
    public function getCategoriesForSingleEventReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getCategories());
    }

    /**
     * @test
     */
    public function getCategoriesForSingleEventWithOneCategoryReturnsListOfCategories()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $category = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $category->getUid(),
            'categories'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Category::class, $model->getCategories()->first());
    }

    /**
     * @test
     */
    public function getCategoriesForSingleEventWithOneCategoryReturnsOneCategory()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $category = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $category->getUid(),
            'categories'
        );

        /** @var \Tx_Seminars_Model_Event $model */
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
    public function getEventTypeForSingleEventWithoutEventTypeReturnsNull()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForSingleEventWithEventTypeReturnsEventTypeInstance()
    {
        $eventType = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_EventType::class)
            ->getLoadedTestingModel([]);
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $eventType->getUid(),
            ]
        );

        self::assertInstanceOf(\Tx_Seminars_Model_EventType::class, $testingModel->getEventType());
    }

    /////////////////////////////////////////
    // Tests regarding getPaymentMethods().
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getPaymentMethodsForSingleEventReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsListOfPaymentMethods()
    {
        $paymentMethod = \Tx_Oelib_MapperRegistry::
            get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'payment_methods' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $uid,
            $paymentMethod->getUid()
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsOnePaymentMethod()
    {
        $paymentMethod = \Tx_Oelib_MapperRegistry::
            get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'payment_methods' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $uid,
            $paymentMethod->getUid()
        );

        /** @var \Tx_Seminars_Model_Event $model */
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
    public function getTargetGroupsForSingleEventReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getTargetGroups());
    }

    /**
     * @test
     */
    public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsListOfTargetGroups()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $targetGroup = \Tx_Oelib_MapperRegistry::
            get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_TargetGroup::class,
            $model->getTargetGroups()->first()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsOneTargetGroup()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $targetGroup = \Tx_Oelib_MapperRegistry::
            get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertEquals(
            $targetGroup->getUid(),
            $model->getTargetGroups()->getUids()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getTargetGroups());
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsListOfTargetGroups()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $targetGroup = \Tx_Oelib_MapperRegistry::
            get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_TargetGroup::class,
            $model->getTargetGroups()->first()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsOneTargetGroup()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $targetGroup = \Tx_Oelib_MapperRegistry::
            get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var \Tx_Seminars_Model_Event $model */
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
    public function getCheckboxesForSingleEventReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getCheckboxes());
    }

    /**
     * @test
     */
    public function getCheckboxesForSingleEventWithOneCheckboxReturnsListOfCheckboxes()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $checkbox = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Checkbox::class, $model->getCheckboxes()->first());
    }

    /**
     * @test
     */
    public function getCheckboxesForSingleEventWithOneCheckboxReturnsOneCheckbox()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $checkbox = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        /** @var \Tx_Seminars_Model_Event $model */
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
    public function getRequirementsForSingleReturnsEmptyList()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertTrue($testingModel->getRequirements()->isEmpty());
    }

    ///////////////////////////////////////
    // Tests regarding getDependencies().
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getDependenciesForSingleEventReturnsEmptyList()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertTrue($testingModel->getDependencies()->isEmpty());
    }
}
