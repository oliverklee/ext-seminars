<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Service\OneTimeAccountConnector;
use OliverKlee\Seminars\Service\RegistrationGuard;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationGuard
 */
final class RegistrationGuardTest extends UnitTestCase
{
    /**
     * @var non-empty-string
     */
    private const NOW = '2022-04-01 10:00:00';

    /**
     * @var Context&MockObject
     */
    private $contextMock;

    /**
     * @var RegistrationRepository&MockObject
     */
    private $registrationRepositoryMock;

    /**
     * @var OneTimeAccountConnector&MockObject
     */
    private $oneTimeAccountConnectorMock;

    /**
     * @var RegistrationGuard
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextMock = $this->createMock(Context::class);
        GeneralUtility::setSingletonInstance(Context::class, $this->contextMock);

        $this->subject = new RegistrationGuard();

        $this->registrationRepositoryMock = $this->createMock(RegistrationRepository::class);
        $this->subject->injectRegistrationRepository($this->registrationRepositoryMock);

        $this->oneTimeAccountConnectorMock = $this->createMock(OneTimeAccountConnector::class);
        $this->subject->injectOneTimeAccountConnector($this->oneTimeAccountConnectorMock);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @deprecated #1960 will be removed in seminars 6.0, use `DateTIme::createFromImmutable()` instead (PHP >= 7.3)
     */
    private function createFromImmutable(\DateTimeInterface $dateTime): \DateTime
    {
        return \DateTime::createFromFormat(\DateTimeInterface::ATOM, $dateTime->format(\DateTime::ATOM));
    }

    /**
     * @test
     */
    public function createFromImmutableKeepsDatesComparable(): void
    {
        $dateTimeImmutable = new \DateTimeImmutable(self::NOW);
        $dateTime = $this->createFromImmutable($dateTimeImmutable);

        self::assertFalse($dateTimeImmutable < $dateTime);
        self::assertFalse($dateTimeImmutable > $dateTime);
    }

    private function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(self::NOW);
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assertBookableEventTypeWithSingleEventThrowsNoException(): void
    {
        $this->subject->assertBookableEventType(new SingleEvent());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assertBookableEventTypeWithEventDateThrowsNoException(): void
    {
        $this->subject->assertBookableEventType(new EventDate());
    }

    /**
     * @test
     */
    public function assertBookableEventTypeWithEventTopicThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1669377348);
        $this->expectExceptionMessage('The event must be a SingleEvent or an EventDate.');

        $this->subject->assertBookableEventType(new EventTopic());
    }

    /**
     * @test
     */
    public function isRegistrationPossibleAtAnyTimeAtAllForEventTopicReturnsFalse(): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());
        $event = new EventTopic();

        self::assertFalse($this->subject->isRegistrationPossibleAtAnyTimeAtAll($event));
    }

    /**
     * @return array<string, array{0: SingleEvent|EventDate}>
     */
    public function nonTopicEventDataProvider(): array
    {
        $eventDate = new EventDate();
        $eventDate->setTopic(new EventTopic());

        return [
            'single event' => [new SingleEvent()],
            'event date with topic' => [$eventDate],
        ];
    }

    /**
     * @test
     *
     * @param SingleEvent|EventDate $event
     *
     * @dataProvider nonTopicEventDataProvider
     */
    public function isRegistrationPossibleAtAnyTimeAtAllEventThatRequiresNoRegistrationReturnsFalse(Event $event): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());
        $event->setRegistrationRequired(false);

        self::assertFalse($this->subject->isRegistrationPossibleAtAnyTimeAtAll($event));
    }

    /**
     * @test
     *
     * @param SingleEvent|EventDate $event
     *
     * @dataProvider nonTopicEventDataProvider
     */
    public function isRegistrationPossibleAtAnyTimeAtAllForEventThatRequiresRegistrationReturnsTrue(Event $event): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());
        $event->setRegistrationRequired(true);

        self::assertTrue($this->subject->isRegistrationPossibleAtAnyTimeAtAll($event));
    }

    /**
     * @test
     */
    public function isRegistrationPossibleAtAnyTimeAtAllForEventDateWithoutTopicReturnsFalse(): void
    {
        $eventDate = new EventDate();
        $eventDate->setRegistrationRequired(true);

        self::assertFalse($this->subject->isRegistrationPossibleAtAnyTimeAtAll($eventDate));
    }

    /**
     * @return array<string, array{
     *             start: ?\DateTime,
     *             registrationStart: ?\DateTime,
     *             registrationDeadline: ?\DateTime,
     *         }>
     */
    public function registrationPossibleDataProvider(): array
    {
        $now = $this->createFromImmutable($this->now());
        // We need the clone because `modify` on `DateTime` modifies the original object instead of returning a new one
        // (which would be the case for `DateTimeImmutable`.
        $future = (clone $now)->modify('+1 day');
        $past = (clone $now)->modify('-1 day');

        return [
            'start in the future' => [
                'start' => $future,
                'registrationStart' => null,
                'registrationDeadline' => null,
            ],
            'start in the future and registration start in the past' => [
                'start' => $future,
                'registrationStart' => $past,
                'registrationDeadline' => null,
            ],
            'start in the future and registration start now' => [
                'start' => $future,
                'registrationStart' => $now,
                'registrationDeadline' => null,
            ],
            'registration deadline in the future' => [
                'start' => null,
                'registrationStart' => null,
                'registrationDeadline' => $future,
            ],
            'start now and registration deadline in the future' => [
                'start' => $now,
                'registrationStart' => null,
                'registrationDeadline' => $future,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *             start: ?\DateTimeImmutable,
     *             registrationStart: ?\DateTimeImmutable,
     *             registrationDeadline: ?\DateTimeImmutable,
     *         }>
     */
    public function registrationNotPossibleDataProvider(): array
    {
        $now = $this->createFromImmutable($this->now());
        // We need the clone because `modify` on `DateTime` modifies the original object instead of returning a new one
        // (which would be the case for `DateTimeImmutable`.
        $future = (clone $now)->modify('+1 day');
        $past = (clone $now)->modify('-1 day');

        return [
            'no dates at all' => [
                'start' => null,
                'registrationStart' => null,
                'registrationDeadline' => null,
            ],
            'start in the past' => [
                'start' => $past,
                'registrationStart' => null,
                'registrationDeadline' => null,
            ],
            'start now' => [
                'start' => $now,
                'registrationStart' => null,
                'registrationDeadline' => null,
            ],
            'no start, but registration start in the past' => [
                'start' => null,
                'registrationStart' => $now,
                'registrationDeadline' => null,
            ],
            'no start, but registration start now' => [
                'start' => null,
                'registrationStart' => $now,
                'registrationDeadline' => null,
            ],
            'no start, but registration start in the future' => [
                'start' => null,
                'registrationStart' => $future,
                'registrationDeadline' => null,
            ],
            'start now and registration start in the past' => [
                'start' => $now,
                'registrationStart' => $past,
                'registrationDeadline' => null,
            ],
            'start and registration start in the future' => [
                'start' => $future,
                'registrationStart' => $future,
                'registrationDeadline' => null,
            ],
            'registration deadline in the past' => [
                'start' => null,
                'registrationStart' => null,
                'registrationDeadline' => $past,
            ],
            'registration deadline now' => [
                'start' => null,
                'registrationStart' => null,
                'registrationDeadline' => $now,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider registrationPossibleDataProvider
     */
    public function isRegistrationPossibleByDateForEventDateWithRegistrationPossibleReturnsTrue(
        ?\DateTime $start,
        ?\DateTime $registrationStart,
        ?\DateTime $registrationDeadline
    ): void {
        $this->contextMock->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());

        $event = new EventDate();
        $event->setStart($start);
        $event->setRegistrationStart($registrationStart);
        $event->setRegistrationDeadline($registrationDeadline);

        self::assertTrue($this->subject->isRegistrationPossibleByDate($event));
    }

    /**
     * @test
     *
     * @dataProvider registrationNotPossibleDataProvider
     */
    public function isRegistrationPossibleByDateForEventDateWithRegistrationNotPossibleReturnsFalse(
        ?\DateTime $start,
        ?\DateTime $registrationStart,
        ?\DateTime $registrationDeadline
    ): void {
        $this->contextMock->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());

        $event = new EventDate();
        $event->setStart($start);
        $event->setRegistrationStart($registrationStart);
        $event->setRegistrationDeadline($registrationDeadline);

        self::assertFalse($this->subject->isRegistrationPossibleByDate($event));
    }

    /**
     * @test
     *
     * @dataProvider registrationPossibleDataProvider
     */
    public function isRegistrationPossibleByDateForSingleEventWithRegistrationPossibleReturnsTrue(
        ?\DateTime $start,
        ?\DateTime $registrationStart,
        ?\DateTime $registrationDeadline
    ): void {
        $this->contextMock->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());

        $event = new SingleEvent();
        $event->setStart($start);
        $event->setRegistrationStart($registrationStart);
        $event->setRegistrationDeadline($registrationDeadline);

        self::assertTrue($this->subject->isRegistrationPossibleByDate($event));
    }

    /**
     * @test
     *
     * @dataProvider registrationNotPossibleDataProvider
     */
    public function isRegistrationPossibleByDateForSingleEventWithRegistrationNotPossibleReturnsFalse(
        ?\DateTime $start,
        ?\DateTime $registrationStart,
        ?\DateTime $registrationDeadline
    ): void {
        $this->contextMock->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());

        $event = new SingleEvent();
        $event->setStart($start);
        $event->setRegistrationStart($registrationStart);
        $event->setRegistrationDeadline($registrationDeadline);

        self::assertFalse($this->subject->isRegistrationPossibleByDate($event));
    }

    /**
     * @test
     */
    public function isFreeFromRegistrationConflictsForNoConflictAndNoMultipleRegistrationsPossibleReturnsTrue(): void
    {
        $userUid = 47;
        $event = new SingleEvent();
        $this->registrationRepositoryMock->method('existsRegistrationForEventAndUser')
            ->with($event, $userUid)->willReturn(false);
        $event->setMultipleRegistrationPossible(true);

        self::assertTrue($this->subject->isFreeFromRegistrationConflicts($event, $userUid));
    }

    /**
     * @test
     */
    public function isFreeFromRegistrationConflictsForNoConflictAndMultipleRegistrationsPossibleReturnsTrue(): void
    {
        $userUid = 47;
        $event = new SingleEvent();
        $this->registrationRepositoryMock->method('existsRegistrationForEventAndUser')
            ->with($event, $userUid)->willReturn(false);
        $event->setMultipleRegistrationPossible(true);

        self::assertTrue($this->subject->isFreeFromRegistrationConflicts($event, $userUid));
    }

    /**
     * @test
     */
    public function isFreeFromRegistrationConflictsForConflictAndMultipleRegistrationsPossibleReturnsTrue(): void
    {
        $userUid = 47;
        $event = new SingleEvent();
        $this->registrationRepositoryMock->method('existsRegistrationForEventAndUser')
            ->with($event, $userUid)->willReturn(true);
        $event->setMultipleRegistrationPossible(true);

        self::assertTrue($this->subject->isFreeFromRegistrationConflicts($event, $userUid));
    }

    /**
     * @test
     */
    public function isFreeFromRegistrationConflictsForConflictAndMultipleRegistrationsNotPossibleReturnsFalse(): void
    {
        $userUid = 47;
        $event = new SingleEvent();
        $this->registrationRepositoryMock->method('existsRegistrationForEventAndUser')
            ->with($event, $userUid)->willReturn(true);
        $event->setMultipleRegistrationPossible(false);

        self::assertFalse($this->subject->isFreeFromRegistrationConflicts($event, $userUid));
    }

    /**
     * @test
     */
    public function existsFrontEndUserUidInSessionForNoLoginAndNoOneTimeAccountDataReturnsFalse(): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'isLoggedIn')->willReturn(false);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn(null);

        self::assertFalse($this->subject->existsFrontEndUserUidInSession());
    }

    /**
     * @test
     */
    public function existsFrontEndUserUidInSessionForLoginAndNoOneTimeAccountDataReturnsTrue(): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'isLoggedIn')->willReturn(true);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn(5);

        self::assertTrue($this->subject->existsFrontEndUserUidInSession());
    }

    /**
     * @test
     */
    public function existsFrontEndUserUidInSessionForNoLoginAndOneTimeAccountDataReturnsTrue(): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'isLoggedIn')->willReturn(false);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn(5);

        self::assertTrue($this->subject->existsFrontEndUserUidInSession());
    }

    /**
     * @test
     */
    public function existsFrontEndUserUidInSessionForLoginAndOneTimeAccountDataReturnsTrue(): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'isLoggedIn')->willReturn(true);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn(5);

        self::assertTrue($this->subject->existsFrontEndUserUidInSession());
    }

    /**
     * @test
     */
    public function getFrontEndUserUidFromSessionForNoLoginAndNoOneTimeAccountDataReturnsNull(): void
    {
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(0);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn(null);

        self::assertNull($this->subject->getFrontEndUserUidFromSession());
    }

    /**
     * @test
     */
    public function getFrontEndUserUidFromSessionForLoginAndNoOneTimeAccountDataReturnsUidFromLogin(): void
    {
        $userUidFromLogin = 12;
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($userUidFromLogin);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn(null);

        self::assertSame($userUidFromLogin, $this->subject->getFrontEndUserUidFromSession());
    }

    /**
     * @test
     */
    public function getFrontEndUserUidFromSessionForNoLoginAndOneTimeAccountDataReturnsUidFromOneTimeAccount(): void
    {
        $userUidFromOneTimeAccount = 12;
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(0);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn($userUidFromOneTimeAccount);

        self::assertSame($userUidFromOneTimeAccount, $this->subject->getFrontEndUserUidFromSession());
    }

    /**
     * @test
     */
    public function getFrontEndUserUidFromSessionForLoginAndOneTimeAccountDataReturnsUidFromLogin(): void
    {
        $userUidFromLogin = 12;
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($userUidFromLogin);
        $userUidFromOneTimeAccount = 9;
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn($userUidFromOneTimeAccount);

        self::assertSame($userUidFromLogin, $this->subject->getFrontEndUserUidFromSession());
    }

    /**
     * @test
     */
    public function getVacanciesWithEventTopicThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1669377348);
        $this->expectExceptionMessage('The event must be a SingleEvent or an EventDate.');

        $this->subject->getVacancies(new EventTopic());
    }

    /**
     * @test
     */
    public function getVacanciesWithSingleEventWithoutLimitAndNoOfflineRegistrationsReturnsNull(): void
    {
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);
        $event->setMaximumNumberOfRegistrations(0);
        $event->setNumberOfOfflineRegistrations(0);

        self::assertNull($this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesWithEventDateWithoutLimitAndNoOfflineRegistrationsReturnsNull(): void
    {
        $event = new EventDate();
        $event->setRegistrationRequired(true);
        $event->setMaximumNumberOfRegistrations(0);
        $event->setNumberOfOfflineRegistrations(0);

        self::assertNull($this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesWithoutLimitAndOfflineRegistrationsReturnsNull(): void
    {
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);
        $event->setMaximumNumberOfRegistrations(0);
        $event->setNumberOfOfflineRegistrations(1);

        self::assertNull($this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesWithLimitAndAllSeatsTakeByOfflineRegistrationsReturnsZero(): void
    {
        $maximumNumberOfRegistrations = 10;
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);
        $event->setMaximumNumberOfRegistrations($maximumNumberOfRegistrations);
        $event->setNumberOfOfflineRegistrations($maximumNumberOfRegistrations);

        self::assertSame(0, $this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesWithLimitAndMoreThanAllSeatsTakeByOfflineRegistrationsReturnsZero(): void
    {
        $maximumNumberOfRegistrations = 10;
        $event = new SingleEvent();
        $event->setRegistrationRequired(true);
        $event->setMaximumNumberOfRegistrations($maximumNumberOfRegistrations);
        $event->setNumberOfOfflineRegistrations($maximumNumberOfRegistrations + 1);

        self::assertSame(0, $this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesForNoRegistrationsAtAllReturnMaximumNumberOfRegistrations(): void
    {
        $maximumNumberOfRegistrations = 10;
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $eventUid = 5;
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);
        $event->setMaximumNumberOfRegistrations($maximumNumberOfRegistrations);

        $this->registrationRepositoryMock->method('countRegularSeatsByEvent')->with($eventUid)->willReturn(0);

        self::assertSame($maximumNumberOfRegistrations, $this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesEventWithRegistrationsReducesVacanciesBySumOfRegistrationSeats(): void
    {
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $eventUid = 5;
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);
        $maximumNumberOfRegistrations = 10;
        $event->setMaximumNumberOfRegistrations($maximumNumberOfRegistrations);

        $registeredSeats = 3;
        $this->registrationRepositoryMock->expects(self::once())->method('countRegularSeatsByEvent')->with($eventUid)
            ->willReturn($registeredSeats);

        self::assertSame($maximumNumberOfRegistrations - $registeredSeats, $this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesEventForAllSeatsBookedReturnsZero(): void
    {
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $eventUid = 5;
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);
        $maximumNumberOfRegistrations = 10;
        $event->setMaximumNumberOfRegistrations($maximumNumberOfRegistrations);

        $this->registrationRepositoryMock->expects(self::once())->method('countRegularSeatsByEvent')->with($eventUid)
            ->willReturn($maximumNumberOfRegistrations);

        self::assertSame(0, $this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesEventForTooManySeatsBookedReturnsZero(): void
    {
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $eventUid = 5;
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);
        $maximumNumberOfRegistrations = 10;
        $event->setMaximumNumberOfRegistrations($maximumNumberOfRegistrations);

        $this->registrationRepositoryMock->expects(self::once())->method('countRegularSeatsByEvent')->with($eventUid)
            ->willReturn($maximumNumberOfRegistrations + 1);

        self::assertSame(0, $this->subject->getVacancies($event));
    }

    /**
     * @test
     */
    public function getVacanciesEventWithRegistrationsReducesVacanciesBySeatsAndOfflineRegistrations(): void
    {
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $eventUid = 5;
        $event->method('getUid')->willReturn($eventUid);
        $event->setRegistrationRequired(true);
        $maximumNumberOfRegistrations = 10;
        $event->setMaximumNumberOfRegistrations($maximumNumberOfRegistrations);
        $numberOfOfflineRegistrations = 2;
        $event->setNumberOfOfflineRegistrations($numberOfOfflineRegistrations);

        $registeredSeats = 3;
        $this->registrationRepositoryMock->expects(self::once())->method('countRegularSeatsByEvent')->with($eventUid)
            ->willReturn(3);

        self::assertSame(
            $maximumNumberOfRegistrations - $registeredSeats - $numberOfOfflineRegistrations,
            $this->subject->getVacancies($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesCalledTwiceForDifferentEventUidsQueryTheRepositoryTwice(): void
    {
        $event1 = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $event1->method('getUid')->willReturn(1);
        $event1->setRegistrationRequired(true);
        $event1->setMaximumNumberOfRegistrations(10);
        $event2 = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $event2->method('getUid')->willReturn(2);
        $event2->setRegistrationRequired(true);
        $event2->setMaximumNumberOfRegistrations(10);

        $this->registrationRepositoryMock->expects(self::exactly(2))->method('countRegularSeatsByEvent')
            ->with(self::anything())->willReturn(0);

        $this->subject->getVacancies($event1);
        $this->subject->getVacancies($event2);
    }

    /**
     * @test
     */
    public function getVacanciesCalledOnceForTheSameEventUidQueryTheRepositoryOnlyOnce(): void
    {
        $event = $this->getMockBuilder(SingleEvent::class)->onlyMethods(['getUid'])->getMock();
        $event->method('getUid')->willReturn(1);
        $event->setRegistrationRequired(true);
        $event->setMaximumNumberOfRegistrations(10);

        $this->registrationRepositoryMock->expects(self::once())->method('countRegularSeatsByEvent')
            ->with(self::anything())->willReturn(0);

        $this->subject->getVacancies($event);
        $this->subject->getVacancies($event);
    }
}
