<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;

/**
 * This test case holds tests which are specific to single events.
 */
final class SingleEventMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var EventMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = MapperRegistry::get(EventMapper::class);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    /////////////////////////////////
    // Tests regarding getTopic().
    /////////////////////////////////

    /**
     * @test
     */
    public function getTopicForSingleRecordThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'This function may only be called for date records.'
        );

        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => Event::TYPE_COMPLETE]
        );

        $testingModel->getTopic();
    }

    // Tests regarding getCategories().

    /**
     * @test
     */
    public function getCategoriesForSingleEventReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCategories());
    }

    /**
     * @test
     */
    public function getCategoriesForSingleEventWithOneCategoryReturnsListOfCategories(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_COMPLETE]
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
    public function getCategoriesForSingleEventWithOneCategoryReturnsOneCategory(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_COMPLETE]
        );
        $category = MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)->getNewGhost();
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
    public function getEventTypeForSingleEventWithoutEventTypeReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => Event::TYPE_COMPLETE]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForSingleEventWithEventTypeReturnsEventTypeInstance(): void
    {
        $eventType = MapperRegistry::get(\Tx_Seminars_Mapper_EventType::class)
            ->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => Event::TYPE_COMPLETE,
                'event_type' => $eventType->getUid(),
            ]
        );

        self::assertInstanceOf(\Tx_Seminars_Model_EventType::class, $testingModel->getEventType());
    }

    // Tests regarding getPaymentMethods().

    /**
     * @test
     */
    public function getPaymentMethodsForSingleEventReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsListOfPaymentMethods(): void
    {
        $paymentMethod = MapperRegistry::
        get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_COMPLETE,
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
    public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsOnePaymentMethod(): void
    {
        $paymentMethod = MapperRegistry::
        get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_COMPLETE,
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

    ///////////////////////////////////////
    // Tests regarding getTargetGroups().
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getTargetGroupsForSingleEventReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getTargetGroups());
    }

    /**
     * @test
     */
    public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsListOfTargetGroups(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_COMPLETE]
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
    public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsOneTargetGroup(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_COMPLETE]
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

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => Event::TYPE_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getTargetGroups());
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsListOfTargetGroups(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
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
    public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsOneTargetGroup(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
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
    public function getCheckboxesForSingleEventReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => Event::TYPE_COMPLETE]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCheckboxes());
    }

    /**
     * @test
     */
    public function getCheckboxesForSingleEventWithOneCheckboxReturnsListOfCheckboxes(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_COMPLETE]
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
    public function getCheckboxesForSingleEventWithOneCheckboxReturnsOneCheckbox(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_COMPLETE]
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
