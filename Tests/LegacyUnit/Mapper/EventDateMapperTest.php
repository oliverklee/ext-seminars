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
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\EventType;
use OliverKlee\Seminars\Model\PaymentMethod;
use OliverKlee\Seminars\Model\TargetGroup;
use PHPUnit\Framework\TestCase;

/**
 * This test case holds all tests specific to event dates.
 *
 * @covers \OliverKlee\Seminars\Mapper\EventMapper
 */
final class EventDateMapperTest extends TestCase
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
    public function getTopicWithoutTopicThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $model = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_EVENT_DATE]
        );

        $model->getTopic();
    }

    /**
     * @test
     */
    public function getTopicWithTopicReturnsEventInstance(): void
    {
        $topicUid = $this->subject->getNewGhost()->getUid();
        \assert($topicUid > 0);

        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'topic' => $topicUid,
                'object_type' => EventInterface::TYPE_EVENT_DATE,
            ]
        );

        self::assertInstanceOf(Event::class, $testingModel->getTopic());
    }

    //////////////////////////////////////
    // Tests regarding getCategories().
    //////////////////////////////////////

    /**
     * @test
     */
    public function getCategoriesForEventDateReturnsListInstance(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCategories());
    }

    /**
     * @test
     */
    public function getCategoriesForEventDateWithOneCategoryReturnsListOfCategories(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $categoryUid = MapperRegistry::get(CategoryMapper::class)->getNewGhost()->getUid();
        \assert($categoryUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $categoryUid,
            'categories'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Category::class, $model->getCategories()->first());
    }

    /**
     * @test
     */
    public function getCategoriesForEventDateWithOneCategoryReturnsOneCategory(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $categoryUid = MapperRegistry::get(CategoryMapper::class)->getNewGhost()->getUid();
        \assert($categoryUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getEventTypeForEventDateWithoutEventTypeReturnsNull(): void
    {
        $topicUid = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([])->getUid();
        \assert($topicUid > 0);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForEventDateWithEventTypeReturnsEventTypeInstance(): void
    {
        $eventType = MapperRegistry::get(EventTypeMapper::class)->getLoadedTestingModel([]);
        $topicUid = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['event_type' => $eventType->getUid()])->getUid();
        \assert($topicUid > 0);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(EventType::class, $testingModel->getEventType());
    }

    // Tests regarding getPaymentMethods().

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateReturnsListInstance(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsListOfPaymentMethods(): void
    {
        $paymentMethodUid = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost()->getUid();
        \assert($paymentMethodUid > 0);
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
            $paymentMethodUid
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsOnePaymentMethod(): void
    {
        $paymentMethodUid = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost()->getUid();
        \assert($paymentMethodUid > 0);
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
            $paymentMethodUid
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$paymentMethodUid,
            $model->getPaymentMethods()->getUids()
        );
    }

    ///////////////////////////////////////
    // Tests regarding getTargetGroups().
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getTargetGroupsForEventDateReturnsListInstance(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getTargetGroups());
    }

    /**
     * @test
     */
    public function getTargetGroupsForEventDateWithOneTargetGroupReturnsListOfTargetGroups(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroupUid = MapperRegistry::get(TargetGroupMapper::class)->getNewGhost()->getUid();
        \assert($targetGroupUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getTargetGroupsForEventDateWithOneTargetGroupReturnsOneTargetGroup(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroupUid = MapperRegistry::get(TargetGroupMapper::class)->getNewGhost()->getUid();
        \assert($targetGroupUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $targetGroupUid,
            'target_groups'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$targetGroupUid,
            $model->getTargetGroups()->getUids()
        );
    }

    /////////////////////////////////////
    // Tests regarding getCheckboxes().
    /////////////////////////////////////

    /**
     * @test
     */
    public function getCheckboxesForEventDateReturnsListInstance(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getCheckboxes());
    }

    /**
     * @test
     */
    public function getCheckboxesForEventDateWithOneCheckboxReturnsListOfCheckboxes(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkboxUid = MapperRegistry::get(CheckboxMapper::class)->getNewGhost()->getUid();
        \assert($checkboxUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $checkboxUid,
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Checkbox::class, $model->getCheckboxes()->first());
    }

    /**
     * @test
     */
    public function getCheckboxesForEventDateWithOneCheckboxReturnsOneCheckbox(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkboxUid = MapperRegistry::get(CheckboxMapper::class)->getNewGhost()->getUid();
        \assert($checkboxUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
