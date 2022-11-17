<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
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
     * @var RegistrationRepository&MockObject
     */
    private $registrationRepositoryMock;

    /**
     * @var RegistrationGuard
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $context = $this->createMock(Context::class);
        $context->method('getPropertyFromAspect')->with('date', 'full')->willReturn($this->now());
        GeneralUtility::setSingletonInstance(Context::class, $context);

        $this->subject = new RegistrationGuard();

        $this->registrationRepositoryMock = $this->createMock(RegistrationRepository::class);
        $this->subject->injectRegistrationRepository($this->registrationRepositoryMock);
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
}
