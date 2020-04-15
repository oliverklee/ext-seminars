<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingEvent;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
final class EventTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    const CONFIGURATION = [
        'dateFormatYMD' => '%d.%m.%Y',
        'timeFormat' => '%H:%M',
    ];

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @test
     */
    public function fromUidMapsDataFromDatabase()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(1);
        $subject->overrideConfiguration(self::CONFIGURATION);
        self::assertSame('event with all scalar data set', $subject->getTitle());
        self::assertSame('Cooking for beginners', $subject->getSubtitle());
        self::assertSame('Never be hungry again.', $subject->getTeaser());
        self::assertSame('Never ever.', $subject->getDescription());
        self::assertSame('ABC-12345', $subject->getAccreditationNumber());
        self::assertSame('12', $subject->getCreditPoints());
        self::assertSame(1575026911, $subject->getBeginDateAsTimestamp());
        self::assertSame(1575926911, $subject->getEndDateAsTimestamp());
        self::assertSame(1570026911, $subject->getRegistrationBeginAsUnixTimestamp());
        self::assertSame('17.11.2019', $subject->getRegistrationDeadline());
        self::assertSame('14.10.2019', $subject->getEarlyBirdDeadline());
        self::assertSame(1573026911, $subject->getUnregistrationDeadlineAsTimestamp());
        self::assertSame('11.12.2019', $subject->getExpiry());
        self::assertSame('12', $subject->getDetailsPage());
        self::assertSame('the first one to the left', $subject->getRoom());
        self::assertSame('1234.56', $subject->getPriceRegular());
        self::assertSame('234.56', $subject->getEarlyBirdPriceRegular());
        self::assertSame('2234.56', $subject->getPriceRegularBoard());
        self::assertSame('1134.54', $subject->getPriceSpecial());
        self::assertSame('1034.54', $subject->getEarlyBirdPriceSpecial());
        self::assertSame('1334.54', $subject->getPriceSpecialBoard());
        self::assertSame('Nothing to see here.', $subject->getAdditionalInformation());
        self::assertTrue($subject->needsRegistration());
        self::assertTrue($subject->allowsMultipleRegistrations());
        self::assertSame(4, $subject->getAttendancesMin());
        self::assertSame(20, $subject->getAttendancesMax());
        self::assertTrue($subject->hasRegistrationQueue());
        self::assertSame(3, $subject->getOfflineRegistrations());
        self::assertTrue($subject->isCanceled());
        self::assertTrue($subject->hasTerms2());
        self::assertSame('abc34345', $subject->getPublicationHash());
        self::assertTrue($subject->haveOrganizersBeenNotifiedAboutEnoughAttendees());
        self::assertTrue($subject->shouldMuteNotificationEmails());
        self::assertTrue($subject->shouldAutomaticallyConfirmOrCancel());
        self::assertTrue($subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getAttendancesForNoRegistrationsReturnsZero()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(2);

        self::assertSame(0, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesCountsOfflineRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(3);

        self::assertSame(2, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesSumsSeatsOfRegistrationsWithSeats()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(4);

        self::assertSame(3, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesCalculatesSeatsOfRegistrationsWithoutSeatsAsOneEach()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(5);

        self::assertSame(2, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesIgnoresRegistrationsOnQueue()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(6);

        self::assertSame(2, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesOnRegistrationQueueCountsQueueRegistrationsOnly()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(6);

        self::assertSame(3, $subject->getAttendancesOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function getAttendancesPaidNotCountsNonQueueUnpaidRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(4);

        self::assertSame(0, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesPaidNotCountsOfflineRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(3);

        self::assertSame(0, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesPaidCountsNonQueuePaidRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(7);

        self::assertSame(2, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesPaidCountsQueuePaidRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(7);

        self::assertSame(2, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidCountsNonQueueUnpaidRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(4);

        self::assertSame(3, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidCountsOfflineRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(3);

        self::assertSame(2, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidNotCountsNonQueuePaidRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(7);

        self::assertSame(0, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidNotCountsQueuePaidRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(7);

        self::assertSame(0, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function calculateStatisticsTakesNewOfflineRegistrationsIntoAccount()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(2);
        self::assertSame(0, $subject->getAttendances());

        $offlineRegistrations = 4;
        $subject->setOfflineRegistrationNumber($offlineRegistrations);
        $subject->calculateStatistics();

        self::assertSame($offlineRegistrations, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function calculateStatisticsTakesNewRegistrationRecordsIntoAccount()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $eventUid = 4;
        $subject = TestingEvent::fromUid($eventUid);
        self::assertSame(3, $subject->getAttendances());

        $this->getDatabaseConnection()->insertArray('tx_seminars_attendances', ['seminar' => $eventUid, 'seats' => 2]);
        $subject->calculateStatistics();

        self::assertSame(5, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getVacanciesForNoMaxAttendancesAndNoRegistrationsReturnsZero()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(2);

        self::assertSame(0, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesForMaxAttendancesAndNoRegistrationsReturnsMaxAttendances()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(9);

        self::assertSame(12, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesReturnsMaxVacanciesMinusOfflineRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(3);

        self::assertSame(3, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesReturnsMaxVacanciesMinusRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(4);

        self::assertSame(2, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesForMoreRegisteredSeatsThanAllowedReturnsZero()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(5);

        self::assertSame(0, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesIgnoresQueueRegistrations()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingEvent::fromUid(6);

        self::assertSame(2, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getCheckboxesForNoCheckboxesReturnsEmptyArray()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingEvent::fromUid(1);
        $result = $subject->getCheckboxes();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getCheckboxesReturnsCaptionAndUidOfAssociatedCheckboxesForSingleEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingEvent::fromUid(2);
        $result = $subject->getCheckboxes();

        $expected = [['caption' => 'Checkbox one', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getCheckboxesReturnsCaptionAndUidOfAssociatedCheckboxesForEventDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingEvent::fromUid(4);
        $result = $subject->getCheckboxes();

        $expected = [['caption' => 'Checkbox one', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getCheckboxesReturnsAssociatedCheckboxesOrderedBySorting()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingEvent::fromUid(3);
        $result = $subject->getCheckboxes();

        $expected = [['caption' => 'Checkbox two', 'value' => 2], ['caption' => 'Checkbox one', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayForNoTargetGroupsReturnsEmptyArray()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingEvent::fromUid(1);
        $result = $subject->getTargetGroupsAsArray();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayReturnsTitlesOfAssociatedTargetGroups()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingEvent::fromUid(2);
        $result = $subject->getTargetGroupsAsArray();

        $expected = ['Target group one'];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayReturnsAssociatedTargetGroupsOrderedBySorting()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingEvent::fromUid(3);
        $result = $subject->getTargetGroupsAsArray();

        $expected = ['Target group two', 'Target group one'];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayForDateReturnsTitlesOfTopicTargetGroups()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingEvent::fromUid(5);
        $result = $subject->getTargetGroupsAsArray();

        $expected = ['Target group one'];
        self::assertSame($expected, $result);
    }
}
