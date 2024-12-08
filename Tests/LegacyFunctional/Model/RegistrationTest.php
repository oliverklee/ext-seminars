<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Model;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Registration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Registration
 */
final class RegistrationTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected bool $initializeDatabase = false;

    private Registration $subject;

    private TestingFramework $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        GeneralUtility::makeInstance(Context::class)
            ->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('2018-04-26 12:42:23')));

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->subject = new Registration();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    ////////////////////////////////////////
    // Tests regarding the front-end user.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function setFrontEndUserSetsFrontEndUser(): void
    {
        $frontEndUser = new FrontEndUser();
        $this->subject->setFrontEndUser($frontEndUser);

        self::assertSame($frontEndUser, $this->subject->getFrontEndUser());
    }

    ///////////////////////////////
    // Tests regarding the event.
    ///////////////////////////////

    /**
     * @test
     */
    public function getEventReturnsEvent(): void
    {
        $event = MapperRegistry::get(EventMapper::class)
            ->getNewGhost();
        $this->subject->setData(['seminar' => $event]);

        self::assertSame(
            $event,
            $this->subject->getEvent()
        );
    }

    /**
     * @test
     */
    public function getSeminarReturnsEvent(): void
    {
        $event = MapperRegistry::get(EventMapper::class)
            ->getNewGhost();
        $this->subject->setData(['seminar' => $event]);

        self::assertSame(
            $event,
            $this->subject->getSeminar()
        );
    }

    /**
     * @test
     */
    public function setEventSetsEvent(): void
    {
        /** @var Event $event */
        $event = MapperRegistry::get(EventMapper::class)->getNewGhost();
        $this->subject->setEvent($event);

        self::assertSame(
            $event,
            $this->subject->getEvent()
        );
    }

    /**
     * @test
     */
    public function setSeminarSetsEvent(): void
    {
        /** @var Event $event */
        $event = MapperRegistry::get(EventMapper::class)->getNewGhost();
        $this->subject->setSeminar($event);

        self::assertSame(
            $event,
            $this->subject->getEvent()
        );
    }

    /////////////////////////////////////////////////////////////////////
    // Tests regarding isOnRegistrationQueue and setOnRegistrationQueue
    /////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function isOnRegistrationQueueForRegularRegistrationReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->isOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function isOnRegistrationQueueForQueueRegistrationReturnsTrue(): void
    {
        $this->subject->setData(['registration_queue' => true]);

        self::assertTrue(
            $this->subject->isOnRegistrationQueue()
        );
    }

    ///////////////////////////////
    // Tests regarding the seats.
    ///////////////////////////////

    /**
     * @test
     */
    public function getSeatsWithoutSeatsReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getSeats()
        );
    }

    /**
     * @test
     */
    public function getSeatsWithNonZeroSeatsReturnsSeats(): void
    {
        $this->subject->setData(['seats' => 42]);

        self::assertEquals(
            42,
            $this->subject->getSeats()
        );
    }
}
