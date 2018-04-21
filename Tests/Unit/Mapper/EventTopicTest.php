<?php

/**
 * This test case holds all tests specific to event topics.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_EventTopicTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var Tx_Seminars_Mapper_Event
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class);
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
            'BadMethodCallException',
            'This function may only be called for date records.'
        );

        $this->fixture->getLoadedTestingModel(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        )->getTopic();
    }

    //////////////////////////////////////
    // Tests regarding getCategories().
    //////////////////////////////////////

    /**
     * @test
     */
    public function getCategoriesForEventTopicReturnsListInstance()
    {
        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
            )->getCategories()
        );
    }

    /**
     * @test
     */
    public function getCategoriesForEventTopicWithOneCategoryReturnsListOfCategories()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $category = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $category->getUid(),
            'categories'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(Tx_Seminars_Model_Category::class, $model->getCategories()->first());
    }

    /**
     * @test
     */
    public function getCategoriesForEventTopicWithOneCategoryReturnsOneCategory()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $category = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $category->getUid(),
            'categories'
        );

        /** @var Tx_Seminars_Model_Event $model */
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
        self::assertNull(
            $this->fixture->getLoadedTestingModel(
                ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
            )->getEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeForEventTopicWithEventTypeReturnsEventTypeInstance()
    {
        $eventType = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_EventType::class)
            ->getLoadedTestingModel([]);

        self::assertInstanceOf(
            Tx_Seminars_Model_EventType::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                    'event_type' => $eventType->getUid(),
                ]
            )->getEventType()
        );
    }

    /////////////////////////////////////////
    // Tests regarding getPaymentMethods().
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicReturnsListInstance()
    {
        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
            )->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsListOfPaymentMethods()
    {
        $paymentMethod = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'payment_methods' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $uid,
            $paymentMethod->getUid()
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(Tx_Seminars_Model_PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsOnePaymentMethod()
    {
        $paymentMethod = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'payment_methods' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $uid,
            $paymentMethod->getUid()
        );

        /** @var Tx_Seminars_Model_Event $model */
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
        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
            )->getTargetGroups()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsListOfTargetGroups()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $targetGroup = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertTrue(
            $model->getTargetGroups()->first() instanceof Tx_Seminars_Model_TargetGroup
        );
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsOneTargetGroup()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $targetGroup = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var Tx_Seminars_Model_Event $model */
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
        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
            )->getCheckboxes()
        );
    }

    /**
     * @test
     */
    public function getCheckboxesForEventTopicWithOneCheckboxReturnsListOfCheckboxes()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $checkbox = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(Tx_Seminars_Model_Checkbox::class, $model->getCheckboxes()->first());
    }

    /**
     * @test
     */
    public function getCheckboxesForEventTopicWithOneCheckboxReturnsOneCheckbox()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $checkbox = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        /** @var Tx_Seminars_Model_Event $model */
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
        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
            )->getRequirements()
        );
    }

    /**
     * @test
     */
    public function getRequirementsForEventTopicWithOneRequirementReturnsListOfEvents()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $event = $this->fixture->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $event->getUid(),
            'requirements'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertTrue(
            $model->getRequirements()->first() instanceof Tx_Seminars_Model_Event
        );
    }

    /**
     * @test
     */
    public function getRequirementsForEventTopicWithOneRequirementsReturnsOneRequirement()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $event = $this->fixture->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $event->getUid(),
            'requirements'
        );

        /** @var Tx_Seminars_Model_Event $model */
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
        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
            )->getDependencies()
        );
    }

    /**
     * @test
     */
    public function getDependenciesForEventTopicWithOneDependencyReturnsListOfEvents()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $relatedUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $relatedUid,
            $uid,
            'dependencies'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($relatedUid);
        self::assertTrue(
            $model->getDependencies()->first() instanceof Tx_Seminars_Model_Event
        );
    }

    /**
     * @test
     */
    public function getDependenciesForEventTopicWithOneDependencyReturnsOneDependency()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $relatedUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $relatedUid,
            $uid,
            'dependencies'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($relatedUid);
        self::assertEquals(
            $uid,
            $model->getDependencies()->getUids()
        );
    }
}
