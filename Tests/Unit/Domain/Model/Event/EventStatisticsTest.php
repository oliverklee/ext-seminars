<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Event\EventStatistics;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventStatistics
 */
final class EventStatisticsTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getRegularSeatsCountReturnsSumOfRegularSeatsFromRegistrationsAndOfflineRegistrations(): void
    {
        $seatsFromRegistations = 42;
        $offlineRegistrations = 13;
        $subject = new EventStatistics($seatsFromRegistations, $offlineRegistrations, 0, 0, 0);

        self::assertSame($seatsFromRegistations + $offlineRegistrations, $subject->getRegularSeatsCount());
    }

    /**
     * @test
     */
    public function getWaitingListSeatsCountReturnsValueProvidedToConstructor(): void
    {
        $waitingListRegistrations = 13;
        $subject = new EventStatistics(0, 0, $waitingListRegistrations, 0, 0);

        self::assertSame($waitingListRegistrations, $subject->getWaitingListSeatsCount());
    }

    /**
     * @test
     */
    public function getMinimumRequiredSeatsReturnsValueProvidedToConstructor(): void
    {
        $minimumSeats = 13;
        $subject = new EventStatistics(0, 0, 0, $minimumSeats, 0);

        self::assertSame($minimumSeats, $subject->getMinimumRequiredSeats());
    }

    /**
     * @test
     */
    public function getSeatsLimitReturnsValueProvidedToConstructor(): void
    {
        $seatsLimit = 13;
        $subject = new EventStatistics(0, 0, 0, 0, $seatsLimit);

        self::assertSame($seatsLimit, $subject->getSeatsLimit());
    }

    /**
     * @test
     */
    public function hasUnlimitedSeatsForPositiveSeatsLimitReturnsFalse(): void
    {
        $subject = new EventStatistics(0, 0, 0, 0, 1);

        self::assertFalse($subject->hasUnlimitedSeats());
    }

    /**
     * @test
     */
    public function hasUnlimitedSeatsForZeroSeatsLimitReturnsTrue(): void
    {
        $subject = new EventStatistics(0, 0, 0, 0, 0);

        self::assertTrue($subject->hasUnlimitedSeats());
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForOneMissingRegistrationReturnsFalse(): void
    {
        $subject = new EventStatistics(5, 4, 0, 10, 0);

        self::assertFalse($subject->hasEnoughRegistrations());
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForJustEnoughRegistrationReturnsTrue(): void
    {
        $subject = new EventStatistics(5, 5, 0, 10, 0);

        self::assertTrue($subject->hasEnoughRegistrations());
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForMoreRegistrationReturnsTrue(): void
    {
        $subject = new EventStatistics(5, 6, 0, 10, 0);

        self::assertTrue($subject->hasEnoughRegistrations());
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsIgnoresWaitingListRegistrations(): void
    {
        $subject = new EventStatistics(5, 4, 1, 10, 0);

        self::assertFalse($subject->hasEnoughRegistrations());
    }

    /**
     * @test
     */
    public function isFullyBookedForOneVacancyReturnsFalse(): void
    {
        $subject = new EventStatistics(5, 4, 0, 0, 10);

        self::assertFalse($subject->isFullyBooked());
    }

    /**
     * @test
     */
    public function isFullyBookedForFullyBookedReturnsTrue(): void
    {
        $subject = new EventStatistics(5, 5, 0, 0, 10);

        self::assertTrue($subject->isFullyBooked());
    }

    /**
     * @test
     */
    public function isFullyBookedForOverbookedReturnsTrue(): void
    {
        $subject = new EventStatistics(5, 6, 0, 0, 10);

        self::assertTrue($subject->isFullyBooked());
    }

    /**
     * @test
     */
    public function isFullyBookedIgnoresWaitingListRegistrations(): void
    {
        $subject = new EventStatistics(5, 4, 1, 0, 10);

        self::assertFalse($subject->isFullyBooked());
    }

    /**
     * @test
     */
    public function isFullyBookedWithRegistrationsAndNoLimitReturnsFalse(): void
    {
        $subject = new EventStatistics(5, 4, 0, 0, 0);

        self::assertFalse($subject->isFullyBooked());
    }

    /**
     * @test
     */
    public function hasExportableRegularRegistrationsNoRegistrationsAtAllReturnsFalse(): void
    {
        $subject = new EventStatistics(0, 0, 0, 0, 0);

        self::assertFalse($subject->hasExportableRegularRegistrations());
    }

    /**
     * @test
     */
    public function hasExportableRegularRegistrationsForNonZeroRegularRegistrationsReturnsTrue(): void
    {
        $subject = new EventStatistics(1, 0, 0, 0, 0);

        self::assertTrue($subject->hasExportableRegularRegistrations());
    }

    /**
     * @test
     */
    public function hasExportableRegularRegistrationsForNonOfflineListRegistrationsReturnsFalse(): void
    {
        $subject = new EventStatistics(0, 1, 0, 0, 0);

        self::assertFalse($subject->hasExportableRegularRegistrations());
    }

    /**
     * @test
     */
    public function hasExportableRegularRegistrationsForNonZeroWaitingListRegistrationsReturnsFalse(): void
    {
        $subject = new EventStatistics(0, 0, 1, 0, 0);

        self::assertFalse($subject->hasExportableRegularRegistrations());
    }

    /**
     * @test
     */
    public function getVacanciesForOneVacancyReturnsOne(): void
    {
        $subject = new EventStatistics(5, 4, 0, 0, 10);

        self::assertSame(1, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesForFullyBookedReturnsZero(): void
    {
        $subject = new EventStatistics(5, 5, 0, 0, 10);

        self::assertSame(0, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesForOverbookedReturnsZero(): void
    {
        $subject = new EventStatistics(5, 6, 0, 0, 10);

        self::assertSame(0, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesForNoLimitReturnsNull(): void
    {
        $subject = new EventStatistics(5, 6, 0, 0, 0);

        self::assertNull($subject->getVacancies());
    }
}
