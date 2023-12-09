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
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * This test case holds tests which are specific to single events.
 *
 * @covers \OliverKlee\Seminars\Mapper\EventMapper
 */
final class SingleEventMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

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
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = MapperRegistry::get(EventMapper::class);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
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
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function getCategoriesForSingleEventWithOneCategoryReturnsOneCategory(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function getEventTypeForSingleEventWithoutEventTypeReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForSingleEventWithEventTypeReturnsEventTypeInstance(): void
    {
        $eventType = MapperRegistry::get(EventTypeMapper::class)
            ->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
                'event_type' => $eventType->getUid(),
            ]
        );

        self::assertInstanceOf(EventType::class, $testingModel->getEventType());
    }

    // Tests regarding getPaymentMethods().

    /**
     * @test
     */
    public function getPaymentMethodsForSingleEventReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertInstanceOf(Collection::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsListOfPaymentMethods(): void
    {
        $paymentMethodUid = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost()->getUid();
        \assert($paymentMethodUid > 0);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsOnePaymentMethod(): void
    {
        $paymentMethodUid = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost()->getUid();
        \assert($paymentMethodUid > 0);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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

    ///////////////////////////////////////
    // Tests regarding getTargetGroups().
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getTargetGroupsForSingleEventReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsOneTargetGroup(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function getCheckboxesForSingleEventReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function getCheckboxesForSingleEventWithOneCheckboxReturnsOneCheckbox(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
