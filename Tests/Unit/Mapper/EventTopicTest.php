<?php

/**
 * This test case holds all tests specific to event topics.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_EventTopicTest extends \Tx_Phpunit_TestCase
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
    public function getTopicForTopicRecordThrowsException()
    {
        $this->setExpectedException(
            \BadMethodCallException::class,
            'This function may only be called for date records.'
        );

        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        $testingModel->getTopic();
    }

    //////////////////////////////////////
    // Tests regarding getCategories().
    //////////////////////////////////////

    /**
     * @test
     */
    public function getCategoriesForEventTopicReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getCategories());
    }

    /**
     * @test
     */
    public function getCategoriesForEventTopicWithOneCategoryReturnsListOfCategories()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
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
    public function getCategoriesForEventTopicWithOneCategoryReturnsOneCategory()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
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
    public function getEventTypeForEventTopicWithoutEventTypeReturnsNull()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForEventTopicWithEventTypeReturnsEventTypeInstance()
    {
        $eventType = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_EventType::class)
            ->getLoadedTestingModel([]);
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
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
    public function getPaymentMethodsForEventTopicReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsListOfPaymentMethods()
    {
        $paymentMethod = \Tx_Oelib_MapperRegistry::
        get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
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
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsOnePaymentMethod()
    {
        $paymentMethod = \Tx_Oelib_MapperRegistry::
        get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
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
    public function getCheckboxesForEventTopicReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getCheckboxes());
    }

    /**
     * @test
     */
    public function getCheckboxesForEventTopicWithOneCheckboxReturnsListOfCheckboxes()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
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
    public function getCheckboxesForEventTopicWithOneCheckboxReturnsOneCheckbox()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
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
    public function getRequirementsForEventTopicReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getRequirements());
    }

    /**
     * @test
     */
    public function getRequirementsForEventTopicWithOneRequirementReturnsListOfEvents()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $event = $this->fixture->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $event->getUid(),
            'requirements'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Event::class,
            $model->getRequirements()->first()
        );
    }

    /**
     * @test
     */
    public function getRequirementsForEventTopicWithOneRequirementsReturnsOneRequirement()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $event = $this->fixture->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $event->getUid(),
            'requirements'
        );

        /** @var \Tx_Seminars_Model_Event $model */
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
    public function getDependenciesForEventTopicReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getDependencies());
    }

    /**
     * @test
     */
    public function getDependenciesForEventTopicWithOneDependencyReturnsListOfEvents()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $relatedUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $relatedUid,
            $uid,
            'dependencies'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($relatedUid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Event::class,
            $model->getDependencies()->first()
        );
    }

    /**
     * @test
     */
    public function getDependenciesForEventTopicWithOneDependencyReturnsOneDependency()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $relatedUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $relatedUid,
            $uid,
            'dependencies'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($relatedUid);
        self::assertEquals(
            $uid,
            $model->getDependencies()->getUids()
        );
    }
}
