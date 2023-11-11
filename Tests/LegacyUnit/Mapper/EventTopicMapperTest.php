<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Mapper\CategoryMapper;
use OliverKlee\Seminars\Mapper\CheckboxMapper;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\EventTypeMapper;
use OliverKlee\Seminars\Mapper\PaymentMethodMapper;
use OliverKlee\Seminars\Mapper\TargetGroupMapper;
use OliverKlee\Seminars\Model\Category;
use OliverKlee\Seminars\Model\Checkbox;
use OliverKlee\Seminars\Model\EventType;
use OliverKlee\Seminars\Model\PaymentMethod;
use OliverKlee\Seminars\Model\TargetGroup;
use PHPUnit\Framework\TestCase;

/**
 * This test case holds all tests specific to event topics.
 *
 * @covers \OliverKlee\Seminars\Mapper\EventMapper
 */
final class EventTopicMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var EventMapper
     */
    private $subject;

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
    public function getTopicForTopicRecordThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'This function may only be called for date records.'
        );

        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        $testingModel->getTopic();
    }

    // Tests regarding getCategories().

    /**
     * @test
     */
    public function getCategoriesForEventTopicReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCategories());
    }

    /**
     * @test
     */
    public function getCategoriesForEventTopicWithOneCategoryReturnsListOfCategories(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );
        $categoryUid = MapperRegistry::get(CategoryMapper::class)->getNewGhost()->getUid();
        \assert($categoryUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $categoryUid,
            'categories'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Category::class, $model->getCategories()->first());
    }

    /**
     * @test
     */
    public function getCategoriesForEventTopicWithOneCategoryReturnsOneCategory(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );
        $categoryUid = MapperRegistry::get(CategoryMapper::class)->getNewGhost()->getUid();
        \assert($categoryUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $categoryUid,
            'categories'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$categoryUid,
            $model->getCategories()->getUids()
        );
    }

    // Tests regarding getEventType().

    /**
     * @test
     */
    public function getEventTypeForEventTopicWithoutEventTypeReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForEventTopicWithEventTypeReturnsEventTypeInstance(): void
    {
        $eventType = MapperRegistry::get(EventTypeMapper::class)
            ->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_EVENT_TOPIC,
                'event_type' => $eventType->getUid(),
            ]
        );

        self::assertInstanceOf(EventType::class, $testingModel->getEventType());
    }

    // Tests regarding getPaymentMethods().

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsListOfPaymentMethods(): void
    {
        $paymentMethodUid = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost()->getUid();
        \assert($paymentMethodUid > 0);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_TOPIC,
                'payment_methods' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $uid,
            $paymentMethodUid
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsOnePaymentMethod(): void
    {
        $paymentMethodUid = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost()->getUid();
        \assert($paymentMethodUid > 0);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_TOPIC,
                'payment_methods' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $uid,
            $paymentMethodUid
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$paymentMethodUid,
            $model->getPaymentMethods()->getUids()
        );
    }

    // Tests regarding getTargetGroups().

    /**
     * @test
     */
    public function getTargetGroupsForEventTopicReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
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
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );
        $targetGroupUid = MapperRegistry::get(TargetGroupMapper::class)->getNewGhost()->getUid();
        \assert($targetGroupUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroupUid,
            'target_groups'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            TargetGroup::class,
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
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );
        $targetGroupUid = MapperRegistry::get(TargetGroupMapper::class)->getNewGhost()->getUid();
        \assert($targetGroupUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $targetGroupUid,
            'target_groups'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$targetGroupUid,
            $model->getTargetGroups()->getUids()
        );
    }

    // Tests regarding getCheckboxes().

    /**
     * @test
     */
    public function getCheckboxesForEventTopicReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCheckboxes());
    }

    /**
     * @test
     */
    public function getCheckboxesForEventTopicWithOneCheckboxReturnsListOfCheckboxes(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );
        $checkboxUid = MapperRegistry::get(CheckboxMapper::class)->getNewGhost()->getUid();
        \assert($checkboxUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkboxUid,
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Checkbox::class, $model->getCheckboxes()->first());
    }

    /**
     * @test
     */
    public function getCheckboxesForEventTopicWithOneCheckboxReturnsOneCheckbox(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );
        $checkboxUid = MapperRegistry::get(CheckboxMapper::class)->getNewGhost()->getUid();
        \assert($checkboxUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $checkboxUid,
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$checkboxUid,
            $model->getCheckboxes()->getUids()
        );
    }
}
