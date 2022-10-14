<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
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
            ['object_type' => Event::TYPE_DATE]
        );

        $model->getTopic();
    }

    /**
     * @test
     */
    public function getTopicWithTopicReturnsEventInstance(): void
    {
        $topic = $this->subject->getNewGhost();

        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'topic' => $topic->getUid(),
                'object_type' => Event::TYPE_DATE,
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
                'object_type' => Event::TYPE_DATE,
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
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $category = MapperRegistry::get(CategoryMapper::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $category->getUid(),
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
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $category = MapperRegistry::get(CategoryMapper::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
    public function getEventTypeForEventDateWithoutEventTypeReturnsNull(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic->getUid(),
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
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['event_type' => $eventType->getUid()]);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic->getUid(),
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
                'object_type' => Event::TYPE_DATE,
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
        $paymentMethod = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
            $paymentMethod->getUid()
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsOnePaymentMethod(): void
    {
        $paymentMethod = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
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
    public function getTargetGroupsForEventDateReturnsListInstance(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => Event::TYPE_DATE,
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
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroup = MapperRegistry::get(TargetGroupMapper::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $targetGroup->getUid(),
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
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroup = MapperRegistry::get(TargetGroupMapper::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $targetGroup->getUid(),
            'target_groups'
        );

        $model = $this->subject->find($uid);
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
    public function getCheckboxesForEventDateReturnsListInstance(): void
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => Event::TYPE_DATE,
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
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkbox = MapperRegistry::get(CheckboxMapper::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $checkbox->getUid(),
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
                'object_type' => Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkbox = MapperRegistry::get(CheckboxMapper::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
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
