<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventStatistics;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \OliverKlee\Seminars\Service\EventStatisticsCalculator
 */
final class EventStatisticsCalculatorTest extends UnitTestCase
{
    /**
     * @var RegistrationRepository&MockObject
     */
    private $registrationRepositoryMock;

    /**
     * @var EventStatisticsCalculator
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new EventStatisticsCalculator();

        $this->registrationRepositoryMock = $this->createMock(RegistrationRepository::class);
        $this->subject->injectRegistrationRepository($this->registrationRepositoryMock);
    }

    /**
     * @test
     */
    public function enrichWithStatisticsForEventTopicSetsNoStatistics(): void
    {
        $event = new EventTopic();

        $this->subject->enrichWithStatistics($event);

        self::assertNull($event->getStatistics());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsForEventDateWithoutRegistrationSetsNoStatistics(): void
    {
        $event = new EventDate();

        $this->subject->enrichWithStatistics($event);

        self::assertNull($event->getStatistics());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsForSingleEventWithoutRegistrationSetsNoStatistics(): void
    {
        $event = new SingleEvent();

        $this->subject->enrichWithStatistics($event);

        self::assertNull($event->getStatistics());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsForEventDateWithRegistrationSetsStatistics(): void
    {
        $event = new EventDate();
        $event->setRegistrationRequired(true);

        $this->subject->enrichWithStatistics($event);

        self::assertInstanceOf(EventStatistics::class, $event->getStatistics());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsForSingleEventWithRegistrationSetStatistics(): void
    {
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);

        $this->subject->enrichWithStatistics($event);

        self::assertInstanceOf(EventStatistics::class, $event->getStatistics());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsUsesSeatsLimitFromEvent(): void
    {
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);
        $seatsLimit = 42;
        $event->setMaximumNumberOfRegistrations($seatsLimit);

        $this->subject->enrichWithStatistics($event);

        $statistics = $event->getStatistics();
        self::assertInstanceOf(EventStatistics::class, $statistics);
        self::assertSame($seatsLimit, $statistics->getSeatsLimit());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsUsesMinimumSeatsFromEvent(): void
    {
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);
        $minimumSeats = 42;
        $event->setMinimumNumberOfRegistrations($minimumSeats);

        $this->subject->enrichWithStatistics($event);

        $statistics = $event->getStatistics();
        self::assertInstanceOf(EventStatistics::class, $statistics);
        self::assertSame($minimumSeats, $statistics->getMinimumRequiredSeats());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsUsesOfflineRegistrationsSeatsFromEvent(): void
    {
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);
        $offlineRegistrations = 42;
        $event->setNumberOfOfflineRegistrations($offlineRegistrations);

        $this->subject->enrichWithStatistics($event);

        $statistics = $event->getStatistics();
        self::assertInstanceOf(EventStatistics::class, $statistics);
        self::assertSame($offlineRegistrations, $statistics->getRegularSeatsCount());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsRetrievesRegularSeatsFromRegistrations(): void
    {
        $eventUid = 9;
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);

        $seatsFromRegistrations = 15;
        $this->registrationRepositoryMock->expects(self::once())->method('countRegularSeatsByEvent')
            ->with($eventUid)->willReturn($seatsFromRegistrations);

        $this->subject->enrichWithStatistics($event);

        $statistics = $event->getStatistics();
        self::assertInstanceOf(EventStatistics::class, $statistics);
        self::assertSame($seatsFromRegistrations, $statistics->getRegularSeatsCount());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsRetrievesWaitingListSeatsFromRegistrations(): void
    {
        $eventUid = 9;
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);
        $event->setWaitingList(true);

        $waitingListSeats = 15;
        $this->registrationRepositoryMock->expects(self::once())->method('countWaitingListSeatsByEvent')
            ->with($eventUid)->willReturn($waitingListSeats);

        $this->subject->enrichWithStatistics($event);

        $statistics = $event->getStatistics();
        self::assertInstanceOf(EventStatistics::class, $statistics);
        self::assertSame($waitingListSeats, $statistics->getWaitingListSeatsCount());
    }

    /**
     * @test
     */
    public function enrichWithStatisticsForNoWaitingListDoesNotRetrieveWaitingListSeats(): void
    {
        $eventUid = 9;
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);
        $event->setWaitingList(false);

        $this->registrationRepositoryMock->expects(self::never())->method('countWaitingListSeatsByEvent')
            ->with(self::anything());

        $this->subject->enrichWithStatistics($event);
    }
}
