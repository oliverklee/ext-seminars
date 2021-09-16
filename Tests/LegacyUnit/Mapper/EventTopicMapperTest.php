<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * This test case holds all tests specific to event topics.
 */
final class EventTopicMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
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
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'This function may only be called for date records.'
        );

        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        $testingModel->getTopic();
    }

    // Tests regarding getCategories().

    /**
     * @test
     */
    public function getCategoriesForEventTopicReturnsListInstance()
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCategories());
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
        $category = MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $category->getUid(),
            'categories'
        );

        $model = $this->subject->find($uid);
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
        $category = MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $category->getUid(),
            'categories'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $category->getUid(),
            $model->getCategories()->getUids()
        );
    }

    // Tests regarding getEventType().

    /**
     * @test
     */
    public function getEventTypeForEventTopicWithoutEventTypeReturnsNull()
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForEventTopicWithEventTypeReturnsEventTypeInstance()
    {
        $eventType = MapperRegistry::get(\Tx_Seminars_Mapper_EventType::class)
            ->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'event_type' => $eventType->getUid(),
            ]
        );

        self::assertInstanceOf(\Tx_Seminars_Model_EventType::class, $testingModel->getEventType());
    }

    // Tests regarding getPaymentMethods().

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicReturnsListInstance()
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsListOfPaymentMethods()
    {
        $paymentMethod = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsOnePaymentMethod()
    {
        $paymentMethod = MapperRegistry::
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $paymentMethod->getUid(),
            $model->getPaymentMethods()->getUids()
        );
    }

    // Tests regarding getTargetGroups().

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicReturnsListInstance()
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getTargetGroups());
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
        $targetGroup = MapperRegistry::
        get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        $model = $this->subject->find($uid);
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
        $targetGroup = MapperRegistry::
        get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroup->getUid(),
            'target_groups'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $targetGroup->getUid(),
            $model->getTargetGroups()->getUids()
        );
    }

    // Tests regarding getCheckboxes().

    /**
     * @test
     */
    public function getCheckboxesForEventTopicReturnsListInstance()
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCheckboxes());
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
        $checkbox = MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        $model = $this->subject->find($uid);
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
        $checkbox = MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $checkbox->getUid(),
            $model->getCheckboxes()->getUids()
        );
    }
}
