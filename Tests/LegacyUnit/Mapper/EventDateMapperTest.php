<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * This test case holds all tests specific to event dates.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class EventDateMapperTest extends TestCase
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

        $this->subject = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
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
    public function getTopicWithoutTopicThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->getLoadedTestingModel(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]
        );

        $model->getTopic();
    }

    /**
     * @test
     */
    public function getTopicWithTopicReturnsEventInstance()
    {
        $topic = $this->subject->getNewGhost();

        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'topic' => $topic->getUid(),
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );

        self::assertInstanceOf(\Tx_Seminars_Model_Event::class, $testingModel->getTopic());
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

        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getCategories());
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $category = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $category->getUid(),
            'categories'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Category::class, $model->getCategories()->first());
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $category = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $category->getUid(),
            'categories'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertNull($testingModel->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeForEventDateWithEventTypeReturnsEventTypeInstance()
    {
        $eventType = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_EventType::class)->getLoadedTestingModel([]);
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['event_type' => $eventType->getUid()]);
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic->getUid(),
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
    public function getPaymentMethodsForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getPaymentMethods());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsListOfPaymentMethods()
    {
        $paymentMethod = \Tx_Oelib_MapperRegistry::
        get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
            $paymentMethod->getUid()
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_PaymentMethod::class, $model->getPaymentMethods()->first());
    }

    /**
     * @test
     */
    public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsOnePaymentMethod()
    {
        $paymentMethod = \Tx_Oelib_MapperRegistry::
        get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['payment_methods' => 1]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $topicUid,
            $paymentMethod->getUid()
        );

        /** @var \Tx_Seminars_Model_Event $model */
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
    public function getTargetGroupsForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getTargetGroups());
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroup = \Tx_Oelib_MapperRegistry::
        get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_TargetGroup::class,
            $model->getTargetGroups()->first()
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $targetGroup = \Tx_Oelib_MapperRegistry::
        get(\Tx_Seminars_Mapper_TargetGroup::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $targetGroup->getUid(),
            'target_groups'
        );

        /** @var \Tx_Seminars_Model_Event $model */
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
    public function getCheckboxesForEventDateReturnsListInstance()
    {
        $topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        self::assertInstanceOf(\Tx_Oelib_List::class, $testingModel->getCheckboxes());
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkbox = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $checkbox->getUid(),
            'checkboxes'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Checkbox::class, $model->getCheckboxes()->first());
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $checkbox = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $checkbox->getUid(),
            'checkboxes'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $checkbox->getUid(),
            $model->getCheckboxes()->getUids()
        );
    }
}
