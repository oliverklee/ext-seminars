<?php

/**
 * This test case holds all tests specific to event dates.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_EventDateTest extends Tx_Phpunit_TestCase
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
     * @expectedException \BadMethodCallException
     */
    public function getTopicWithoutTopicThrowsException()
    {
        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->fixture->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]
        );

        $model->getTopic();
    }

    /**
     * @test
     */
    public function getTopicWithTopicReturnsEventInstance()
    {
        $topic = $this->fixture->getNewGhost();

        self::assertInstanceOf(
            \Tx_Seminars_Model_Event::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'topic' => $topic->getUid(),
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                ]
            )->getTopic()
        );
    }

    //////////////////////////////////////
    // Tests regarding getCategories().
    //////////////////////////////////////

    /**
     * @test
     */
    public function getCategoriesForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )->getCategories()
        );
    }

    /**
     * @test
     */
    public function getCategoriesForEventDateWithOneCategoryReturnsListOfCategories()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $category = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getCategoriesForEventDateWithOneCategoryReturnsOneCategory()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $category = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getEventTypeForEventDateWithoutEventTypeReturnsNull()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);

        self::assertNull(
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topic,
                ]
            )->getEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeForEventDateWithEventTypeReturnsEventTypeInstance()
    {
        $eventType = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_EventType::class)
            ->getLoadedTestingModel([]);
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['event_type' => $eventType->getUid()]);

        self::assertInstanceOf(
            Tx_Seminars_Model_EventType::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topic->getUid(),
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
    public function getPaymentMethodsForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsListOfPaymentMethods()
    {
        $paymentMethod = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
            $paymentMethod->getUid()
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(Tx_Seminars_Model_PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsOnePaymentMethod()
    {
        $paymentMethod = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
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
    public function getTargetGroupsForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )->getTargetGroups()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventDateWithOneTargetGroupReturnsListOfTargetGroups()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroup = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getTargetGroupsForEventDateWithOneTargetGroupReturnsOneTargetGroup()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroup = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getCheckboxesForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )->getCheckboxes()
        );
    }

    /**
     * @test
     */
    public function getCheckboxesForEventDateWithOneCheckboxReturnsListOfCheckboxes()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkbox = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getCheckboxesForEventDateWithOneCheckboxReturnsOneCheckbox()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkbox = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getRequirementsForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )->getRequirements()
        );
    }

    /**
     * @test
     */
    public function getRequirementsForEventDateWithOneRequirementReturnsListOfEvents()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $event = $this->fixture->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getRequirementsForEventDateWithOneRequirementsReturnsOneRequirement()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $event = $this->fixture->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getDependenciesForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->fixture->getLoadedTestingModel(
                [
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )->getDependencies()
        );
    }

    /**
     * @test
     */
    public function getDependenciesForEventDateWithOneDependencyReturnsListOfEvents()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $relatedUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $relatedUid,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $relatedUid,
            $topicUid,
            'dependencies'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertTrue(
            $model->getDependencies()->first() instanceof Tx_Seminars_Model_Event
        );
    }

    /**
     * @test
     */
    public function getDependenciesForEventDateWithOneDependencyReturnsOneDependency()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $relatedUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $relatedUid,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $relatedUid,
            $topicUid,
            'dependencies'
        );

        /** @var Tx_Seminars_Model_Event $model */
        $model = $this->fixture->find($uid);
        self::assertEquals(
            $topicUid,
            $model->getDependencies()->getUids()
        );
    }
}
