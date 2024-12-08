<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\Registration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\RegistrationMapper
 */
final class RegistrationMapperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private TestingFramework $testingFramework;

    private RegistrationMapper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new RegistrationMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

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
        self::assertInstanceOf(Registration::class, $model);
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
}
