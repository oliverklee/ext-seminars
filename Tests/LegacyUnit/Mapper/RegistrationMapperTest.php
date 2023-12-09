<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\CheckboxMapper;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\FoodMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\LodgingMapper;
use OliverKlee\Seminars\Mapper\PaymentMethodMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\Food;
use OliverKlee\Seminars\Model\Lodging;
use OliverKlee\Seminars\Model\PaymentMethod;
use OliverKlee\Seminars\Model\Registration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\RegistrationMapper
 */
final class RegistrationMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var RegistrationMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new RegistrationMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        parent::tearDown();
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
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)->getNewGhost();
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
        $paymentMethod = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $testingModel = $this->subject->getLoadedTestingModel(['method_of_payment' => $paymentMethod->getUid()]);

        self::assertInstanceOf(PaymentMethod::class, $testingModel->getPaymentMethod());
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
        $lodgingUid = MapperRegistry::get(LodgingMapper::class)->getNewGhost()->getUid();
        \assert($lodgingUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $lodgingUid,
            'lodgings'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Lodging::class, $model->getLodgings()->first());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsOneLodging(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $lodgingUid = MapperRegistry::get(LodgingMapper::class)->getNewGhost()->getUid();
        \assert($lodgingUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $lodgingUid,
            'lodgings'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            $lodgingUid,
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
        $foodUid = MapperRegistry::get(FoodMapper::class)->getNewGhost()->getUid();
        \assert($foodUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $foodUid,
            'foods'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Food::class, $model->getFoods()->first());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsOneFood(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $foodUid = MapperRegistry::get(FoodMapper::class)->getNewGhost()->getUid();
        \assert($foodUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $foodUid,
            'foods'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            $foodUid,
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
        $checkboxUid = MapperRegistry::get(CheckboxMapper::class)->getNewGhost()->getUid();
        \assert($checkboxUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $checkboxUid,
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            $checkboxUid,
            $model->getCheckboxes()->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function getCheckboxesWithOneCheckboxReturnsOneCheckbox(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_attendances');
        $checkboxUid = MapperRegistry::get(CheckboxMapper::class)->getNewGhost()->getUid();
        \assert($checkboxUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_attendances',
            $uid,
            $checkboxUid,
            'checkboxes'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            $checkboxUid,
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
