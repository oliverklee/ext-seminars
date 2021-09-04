<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RegistrationMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Registration
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_Registration();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsRegistrationInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_Registration::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'registration for event']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'registration for event',
            $model->getTitle()
        );
    }

    // Tests concerning the event.

    /**
     * @test
     */
    public function getEventWithEventReturnsEventInstance()
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['seminar' => $event->getUid()]);

        self::assertInstanceOf(\Tx_Seminars_Model_Event::class, $testingModel->getEvent());
    }

    /**
     * @test
     */
    public function getSeminarWithEventReturnsEventInstance()
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['seminar' => $event->getUid()]);

        self::assertInstanceOf(\Tx_Seminars_Model_Event::class, $testingModel->getSeminar());
    }

    // Tests concerning the front-end user.

    /**
     * @test
     */
    public function getFrontEndUserWithFrontEndUserReturnsSameFrontEndUser()
    {
        $frontEndUser = MapperRegistry::
        get(\Tx_Seminars_Mapper_FrontEndUser::class)->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['user' => $frontEndUser->getUid()]);

        self::assertSame($frontEndUser, $testingModel->getFrontEndUser());
    }

    // Tests concerning the payment method.

    /**
     * @test
     */
    public function getPaymentMethodWithoutPaymentMethodReturnsNull()
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getPaymentMethod());
    }

    /**
     * @test
     */
    public function getPaymentMethodWithPaymentMethodReturnsPaymentMethodInstance()
    {
        $paymentMethod = MapperRegistry::
        get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['method_of_payment' => $paymentMethod->getUid()]);

        self::assertInstanceOf(\Tx_Seminars_Model_PaymentMethod::class, $testingModel->getPaymentMethod());
    }

    // Tests concerning the lodgings.

    /**
     * @test
     */
    public function getLodgingsReturnsListInstance()
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getLodgings());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsListOfLodgings()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $lodging = MapperRegistry::get(\Tx_Seminars_Mapper_Lodging::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $lodging->getUid(),
            'lodgings'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Lodging::class, $model->getLodgings()->first());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsOneLodging()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $lodging = MapperRegistry::get(\Tx_Seminars_Mapper_Lodging::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $lodging->getUid(),
            'lodgings'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $lodging->getUid(),
            $model->getLodgings()->first()->getUid()
        );
    }

    // Tests concerning the foods.

    /**
     * @test
     */
    public function getFoodsReturnsListInstance()
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getFoods());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsListOfFoods()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $food = MapperRegistry::get(\Tx_Seminars_Mapper_Food::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $food->getUid(),
            'foods'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Food::class, $model->getFoods()->first());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsOneFood()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $food = MapperRegistry::get(\Tx_Seminars_Mapper_Food::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $food->getUid(),
            'foods'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $food->getUid(),
            $model->getFoods()->first()->getUid()
        );
    }

    // Tests concerning the checkboxes.

    /**
     * @test
     */
    public function getCheckboxesReturnsListInstance()
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getCheckboxes());
    }

    /**
     * @test
     */
    public function getCheckboxesWithOneCheckboxReturnsListOfCheckboxes()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $checkbox = MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $checkbox->getUid(),
            $model->getCheckboxes()->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function getCheckboxesWithOneCheckboxReturnsOneCheckbox()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $checkbox = MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $checkbox->getUid(),
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $checkbox->getUid(),
            $model->getCheckboxes()->first()->getUid()
        );
    }

    // Tests concerning the relation to the additional registered persons

    /**
     * @test
     */
    public function relationToAdditionalPersonsReturnsPersonsFromDatabase()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['additional_persons' => 1]
        );
        $personUid = $this->testingFramework->createFrontEndUser(
            '',
            ['tx_seminars_registration' => $registrationUid]
        );

        $model = $this->subject->find($registrationUid);
        self::assertEquals(
            (string)$personUid,
            $model->getAdditionalPersons()->getUids()
        );
    }
}
