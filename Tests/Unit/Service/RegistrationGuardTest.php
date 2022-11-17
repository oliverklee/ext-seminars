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
        return [
            'single event' => [new SingleEvent()],
            'event date' => [new EventDate()],
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
     * @return array<string, array{
     *             start: ?\DateTimeImmutable,
     *             registrationStart: ?\DateTimeImmutable,
     *             registrationDeadline: ?\DateTimeImmutable,
     *         }>
     */
    public function registrationPossibleDataProvider(): array
    {
        $now = $this->now();
        $future = $now->modify('+1 second');
        $past = $now->modify('-1 second');

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
        $now = $this->now();
        $future = $now->modify('+1 second');
        $past = $now->modify('-1 second');

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
        ?\DateTimeImmutable $start,
        ?\DateTimeImmutable $registrationStart,
        ?\DateTimeImmutable $registrationDeadline
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
        ?\DateTimeImmutable $start,
        ?\DateTimeImmutable $registrationStart,
        ?\DateTimeImmutable $registrationDeadline
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
        ?\DateTimeImmutable $start,
        ?\DateTimeImmutable $registrationStart,
        ?\DateTimeImmutable $registrationDeadline
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
        ?\DateTimeImmutable $start,
        ?\DateTimeImmutable $registrationStart,
        ?\DateTimeImmutable $registrationDeadline
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

        self::assertNull($this->subject->getFrontEndUserUidInSession());
    }

    /**
     * @test
     */
    public function getFrontEndUserUidFromSessionForLoginAndNoOneTimeAccountDataReturnsUidFromLogin(): void
    {
        $userUidFromLogin = 12;
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($userUidFromLogin);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn(null);

        self::assertSame($userUidFromLogin, $this->subject->getFrontEndUserUidInSession());
    }

    /**
     * @test
     */
    public function getFrontEndUserUidFromSessionForNoLoginAndOneTimeAccountDataReturnsUidFromOneTimeAccount(): void
    {
        $userUidFromOneTimeAccount = 12;
        $this->contextMock->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(0);
        $this->oneTimeAccountConnectorMock->method('getOneTimeAccountUserUid')->willReturn($userUidFromOneTimeAccount);

        self::assertSame($userUidFromOneTimeAccount, $this->subject->getFrontEndUserUidInSession());
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

        self::assertSame($userUidFromLogin, $this->subject->getFrontEndUserUidInSession());
    }
}
