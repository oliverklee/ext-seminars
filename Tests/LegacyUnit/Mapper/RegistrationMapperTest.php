<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Mapper\CheckboxMapper;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\FoodMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\LodgingMapper;
use OliverKlee\Seminars\Mapper\PaymentMethodMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\Registration;

/**
 * @covers \OliverKlee\Seminars\Mapper\RegistrationMapper
 */
final class RegistrationMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var RegistrationMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new RegistrationMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsRegistrationInstance(): void
    {
        self::assertInstanceOf(Registration::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
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
    public function getEventWithEventReturnsEventInstance(): void
    {
        $event = MapperRegistry::get(EventMapper::class)
            ->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['seminar' => $event->getUid()]);

        self::assertInstanceOf(Event::class, $testingModel->getEvent());
    }

    /**
     * @test
     */
    public function getSeminarWithEventReturnsEventInstance(): void
    {
        $event = MapperRegistry::get(EventMapper::class)
            ->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['seminar' => $event->getUid()]);

        self::assertInstanceOf(Event::class, $testingModel->getSeminar());
    }

    // Tests concerning the front-end user.

    /**
     * @test
     */
    public function getFrontEndUserWithFrontEndUserReturnsSameFrontEndUser(): void
    {
        $frontEndUser = MapperRegistry::
        get(FrontEndUserMapper::class)->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['user' => $frontEndUser->getUid()]);

        self::assertSame($frontEndUser, $testingModel->getFrontEndUser());
    }

    // Tests concerning the payment method.

    /**
     * @test
     */
    public function getPaymentMethodWithoutPaymentMethodReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getPaymentMethod());
    }

    /**
     * @test
     */
    public function getPaymentMethodWithPaymentMethodReturnsPaymentMethodInstance(): void
    {
        $paymentMethod = MapperRegistry::
        get(PaymentMethodMapper::class)->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['method_of_payment' => $paymentMethod->getUid()]);

        self::assertInstanceOf(\Tx_Seminars_Model_PaymentMethod::class, $testingModel->getPaymentMethod());
    }

    // Tests concerning the lodgings.

    /**
     * @test
     */
    public function getLodgingsReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getLodgings());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsListOfLodgings(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $lodging = MapperRegistry::get(LodgingMapper::class)
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
    public function getLodgingsWithOneLodgingReturnsOneLodging(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $lodging = MapperRegistry::get(LodgingMapper::class)
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
    public function getFoodsReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getFoods());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsListOfFoods(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $food = MapperRegistry::get(FoodMapper::class)->getNewGhost();
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
    public function getFoodsWithOneFoodReturnsOneFood(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $food = MapperRegistry::get(FoodMapper::class)
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
    public function getCheckboxesReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getCheckboxes());
    }

    /**
     * @test
     */
    public function getCheckboxesWithOneCheckboxReturnsListOfCheckboxes(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $checkbox = MapperRegistry::get(CheckboxMapper::class)
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
    public function getCheckboxesWithOneCheckboxReturnsOneCheckbox(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $checkbox = MapperRegistry::get(CheckboxMapper::class)
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
    public function relationToAdditionalPersonsReturnsPersonsFromDatabase(): void
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
