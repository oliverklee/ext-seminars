<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\Model\Place;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingLegacyEvent;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyEvent
 */
final class LegacyEventTest extends FunctionalTestCase
{
    use FalHelper;
    use LanguageHelper;

    /**
     * @var array<string, non-empty-string>
     */
    private const CONFIGURATION = [
        'dateFormatYMD' => '%d.%m.%Y',
        'timeFormat' => '%H:%M',
    ];

    /**
     * @var array<int, non-empty-string>
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = new DummyConfiguration(self::CONFIGURATION);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        $this->testingFramework = new TestingFramework('tx_seminars');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        ConfigurationRegistry::purgeInstance();
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     *
     * @throws NimutException
     */
    private function importStaticData(): void
    {
        if ($this->getDatabaseConnection()->selectCount('*', 'static_countries') === 0) {
            $this->importDataSet(__DIR__ . '/Fixtures/Events/Countries.xml');
        }
    }

    private function buildFrontEndAndPlugin(): DefaultController
    {
        $this->testingFramework->createFakeFrontEnd();
        $this->initializeBackEndLanguage();

        $plugin = new DefaultController();
        $plugin->main('', ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html']);

        return $plugin;
    }

    /**
     * @test
     */
    public function fromUidMapsDataFromDatabase(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(1);
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
    public function getAttendancesForNoRegistrationsReturnsZero(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(2);

        self::assertSame(0, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesCountsOfflineRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(3);

        self::assertSame(2, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesSumsSeatsOfRegistrationsWithSeats(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(4);

        self::assertSame(3, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesCalculatesSeatsOfRegistrationsWithoutSeatsAsOneEach(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(5);

        self::assertSame(2, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesIgnoresRegistrationsOnQueue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(6);

        self::assertSame(2, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getAttendancesOnRegistrationQueueCountsQueueRegistrationsOnly(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(6);

        self::assertSame(3, $subject->getAttendancesOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function getAttendancesPaidNotCountsNonQueueUnpaidRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(4);

        self::assertSame(0, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesPaidNotCountsOfflineRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(3);

        self::assertSame(0, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesPaidCountsNonQueuePaidRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(7);

        self::assertSame(2, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesPaidCountsQueuePaidRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(7);

        self::assertSame(2, $subject->getAttendancesPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidCountsNonQueueUnpaidRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(4);

        self::assertSame(3, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidCountsOfflineRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(3);

        self::assertSame(2, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidNotCountsNonQueuePaidRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(7);

        self::assertSame(0, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function getAttendancesNotPaidNotCountsQueuePaidRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(7);

        self::assertSame(0, $subject->getAttendancesNotPaid());
    }

    /**
     * @test
     */
    public function calculateStatisticsTakesNewOfflineRegistrationsIntoAccount(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(2);
        self::assertSame(0, $subject->getAttendances());

        $offlineRegistrations = 4;
        $subject->setOfflineRegistrationNumber($offlineRegistrations);
        $subject->calculateStatistics();

        self::assertSame($offlineRegistrations, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function calculateStatisticsTakesNewRegistrationRecordsIntoAccount(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $eventUid = 4;
        $subject = TestingLegacyEvent::fromUid($eventUid);
        self::assertSame(3, $subject->getAttendances());

        $this->getDatabaseConnection()->insertArray('tx_seminars_attendances', ['seminar' => $eventUid, 'seats' => 2]);
        $subject->calculateStatistics();

        self::assertSame(5, $subject->getAttendances());
    }

    /**
     * @test
     */
    public function getVacanciesForNoMaxAttendancesAndNoRegistrationsReturnsZero(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(2);

        self::assertSame(0, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesForMaxAttendancesAndNoRegistrationsReturnsMaxAttendances(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(9);

        self::assertSame(12, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesReturnsMaxVacanciesMinusOfflineRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(3);

        self::assertSame(3, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesReturnsMaxVacanciesMinusRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(4);

        self::assertSame(2, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesForMoreRegisteredSeatsThanAllowedReturnsZero(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(5);

        self::assertSame(0, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getVacanciesIgnoresQueueRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $subject = TestingLegacyEvent::fromUid(6);

        self::assertSame(2, $subject->getVacancies());
    }

    /**
     * @test
     */
    public function getCheckboxesForNoCheckboxesReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingLegacyEvent::fromUid(1);
        $result = $subject->getCheckboxes();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getCheckboxesReturnsCaptionAndUidOfAssociatedCheckboxesForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingLegacyEvent::fromUid(2);
        $result = $subject->getCheckboxes();

        $expected = [['caption' => 'Checkbox one', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getCheckboxesReturnsCaptionAndUidOfAssociatedCheckboxesForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingLegacyEvent::fromUid(4);
        $result = $subject->getCheckboxes();

        $expected = [['caption' => 'Checkbox one', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getCheckboxesReturnsAssociatedCheckboxesOrderedBySorting(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/Checkboxes.xml');

        $subject = TestingLegacyEvent::fromUid(3);
        $result = $subject->getCheckboxes();

        $expected = [['caption' => 'Checkbox two', 'value' => 2], ['caption' => 'Checkbox one', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayForNoTargetGroupsReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingLegacyEvent::fromUid(1);
        $result = $subject->getTargetGroupsAsArray();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayReturnsTitlesOfAssociatedTargetGroups(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingLegacyEvent::fromUid(2);
        $result = $subject->getTargetGroupsAsArray();

        $expected = ['Target group one'];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayReturnsAssociatedTargetGroupsOrderedBySorting(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingLegacyEvent::fromUid(3);
        $result = $subject->getTargetGroupsAsArray();

        $expected = ['Target group two', 'Target group one'];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getTargetGroupsAsArrayForDateReturnsTitlesOfTopicTargetGroups(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/TargetGroups.xml');

        $subject = TestingLegacyEvent::fromUid(5);
        $result = $subject->getTargetGroupsAsArray();

        $expected = ['Target group one'];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function getAttachedFilesForNoAttachedFilesReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAttachments.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyEvent(1);

        self::assertSame([], $subject->getAttachedFiles());
    }

    /**
     * @test
     */
    public function getAttachedFilesForNotMigratedFilesReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAttachments.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyEvent(2);

        self::assertSame([], $subject->getAttachedFiles());
    }

    /**
     * @test
     */
    public function getAttachedFilesWithPositiveFileCountWithoutFileReferenceReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAttachments.xml');

        $subject = new LegacyEvent(3);

        self::assertSame([], $subject->getAttachedFiles());
    }

    /**
     * @test
     */
    public function getAttachedFilesWithOneDirectlyAttachedFileFileReferenceInArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAttachments.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyEvent(4);
        $files = $subject->getAttachedFiles();

        self::assertCount(1, $files);
        self::assertInstanceOf(FileReference::class, $files[0]);
    }

    /**
     * @test
     */
    public function getAttachedFilesForDateReturnsFilesFromTopic(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAttachments.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyEvent(5);
        $files = $subject->getAttachedFiles();

        self::assertCount(1, $files);
        self::assertInstanceOf(FileReference::class, $files[0]);
    }

    /**
     * @test
     */
    public function getAttachedFilesForDateReturnsFilesFromTopicAndDateCombined(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAttachments.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyEvent(6);
        $files = $subject->getAttachedFiles();

        self::assertCount(2, $files);
    }

    // Tests concerning getPlaceWithDetails and getPlaceWithDetailsRaw

    /**
     * @test
     */
    public function getPlaceWithDetailsReturnsWillBeAnnouncedForNoPlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(1);

        $result = $subject->getPlaceWithDetails($plugin);

        $expected = $this->translate('message_willBeAnnounced');
        self::assertStringContainsString($expected, $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsTitleOfPlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertStringContainsString('The Castle (without country)', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsTitlesOfAllRelatedPlaces(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(8);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertStringContainsString('The Castle (without country)', $result);
        self::assertStringContainsString('The garden (without country)', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsListsDuplicateAssociationsOnlyOnce(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(3);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertSame(1, \substr_count($result, 'The Castle (without country)'));
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsAddressOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertStringContainsString('on top of the mountain', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsForNonEmptyZipAndCityContainsZipAndCity(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertStringContainsString('12345', $result);
        self::assertStringContainsString('Hamm', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsCountryOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $this->importStaticData();
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(4);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertStringContainsString('Schweiz', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsHomepageLinkOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertStringContainsString('href="https://www.example.com"', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsDirectionsOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $plugin = $this->buildFrontEndAndPlugin();
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetails($plugin);

        self::assertStringContainsString('3 turns left, then always right', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawReturnsWillBeAnnouncedForNoPlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(1);
        $this->initializeBackEndLanguage();

        $result = $subject->getPlaceWithDetailsRaw();

        $expected = $this->translate('message_willBeAnnounced');
        self::assertStringContainsString($expected, $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsTitleOfPlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertStringContainsString('The Castle (without country)', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawSeparatesPlacesByNewline(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(8);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertStringContainsString("3 turns left, then always right\nThe garden (without country)", $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawListsDuplicateAssociationsOnlyOnce(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(3);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertSame(1, \substr_count($result, 'The Castle (without country)'));
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsAddressOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertStringContainsString('on top of the mountain', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawForNonEmptyZipAndCityContainsZipAndCity(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertStringContainsString('12345', $result);
        self::assertStringContainsString('Hamm', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsCountryOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $this->importStaticData();
        $subject = TestingLegacyEvent::fromUid(4);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertStringContainsString('Schweiz', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsHomepageLinkOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertStringContainsString('www.example.com', $result);
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsDirectionsOfOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceWithDetailsRaw();

        self::assertStringContainsString('3 turns left, then always right', $result);
    }

    // Tests concerning getPlacesWithCountry

    /**
     * @test
     */
    public function getPlacesWithCountryWithoutPlacesReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(1);
        $result = $subject->getPlacesWithCountry();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryForPlacesWithoutCountryReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(2);
        $result = $subject->getPlacesWithCountry();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryForPlacesWithValidCountryReturnsCountryCode(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(4);
        $result = $subject->getPlacesWithCountry();

        self::assertSame(['ch'], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryForPlacesWithInvalidCountryReturnsCountryCode(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(5);
        $result = $subject->getPlacesWithCountry();

        self::assertSame(['xy'], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWillReturnCountriesInSortingOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(7);
        $result = $subject->getPlacesWithCountry();

        self::assertSame(['de', 'ch'], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryIgnoresDeletedCountry(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(6);
        $result = $subject->getPlacesWithCountry();

        self::assertSame([], $result);
    }

    // Tests for getPlaceShort

    /**
     * @test
     */
    public function getPlaceShortReturnsWillBeAnnouncedForNoPlaces(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(1);
        $this->initializeBackEndLanguage();

        $result = $subject->getPlaceShort();

        $expected = $this->translate('message_willBeAnnounced');
        self::assertStringContainsString($expected, $result);
    }

    /**
     * @test
     */
    public function getPlaceShortReturnsPlaceNameForOnePlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaceShort();

        self::assertStringContainsString('The Castle (without country)', $result);
    }

    /**
     * @test
     */
    public function getPlaceShortReturnsPlaceNamesWithCommaForTwoPlaces(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(8);

        $result = $subject->getPlaceShort();

        self::assertStringContainsString('The Castle (without country), The garden (without country)', $result);
    }

    /**
     * @test
     */
    public function getPlaceShortListsDuplicateAssociationsOnlyOnce(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(3);

        $result = $subject->getPlaceShort();

        self::assertSame(1, \substr_count($result, 'The Castle (without country)'));
    }

    // Tests concerning getPlaces

    /**
     * @test
     */
    public function getPlacesForEventWithNoPlacesReturnsEmptyList(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(1);

        $result = $subject->getPlaces();

        self::assertInstanceOf(Collection::class, $result);
        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function getPlacesForSeminarWithOnePlacesReturnsListWithPlace(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getPlaces();

        self::assertInstanceOf(Collection::class, $result);
        self::assertInstanceOf(Place::class, $result->first());
        self::assertSame('1', $result->getUids());
    }

    // Tests for hasCountry

    /**
     * @test
     */
    public function hasCountryForNoPlacesReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(1);

        self::assertFalse($subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryForPlaceWithoutCountryReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(2);

        self::assertFalse($subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryForPlaceWithDeletedCountryReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(6);

        self::assertFalse($subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryForPlaceWithCountryReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingLegacyEvent::fromUid(4);

        self::assertTrue($subject->hasCountry());
    }

    // Tests for getCountry

    /**
     * @test
     */
    public function getCountryForNoPlacesReturnsEmptyString(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(1);

        $result = $subject->getCountry();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getCountryForPlaceWithoutCountryReturnsEmptyString(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(2);

        $result = $subject->getCountry();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getCountryForPlaceWithDeletedCountryReturnsEmptyString(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(6);

        $result = $subject->getCountry();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getCountryForPlaceWithCountryReturnsCountryName(): void
    {
        $this->importStaticData();
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(4);

        $result = $subject->getCountry();

        self::assertSame('Schweiz', $result);
    }

    /**
     * @test
     */
    public function getCountryForMultiplePlacesWithDifferentCountriesReturnsCommaSeparatedCountryNames(): void
    {
        $this->importStaticData();
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');
        $subject = TestingLegacyEvent::fromUid(7);

        $result = $subject->getCountry();

        self::assertSame('Deutschland, Schweiz', $result);
    }

    // Test for getCountryNameFromIsoCode

    /**
     * @test
     */
    public function getCountryForIsoCodeWithValidCountryCodeReturnsCountryName(): void
    {
        $this->importStaticData();
        $subject = new TestingLegacyEvent();

        $result = $subject->getCountryNameFromIsoCode('ch');

        self::assertSame('Schweiz', $result);
    }

    /**
     * @test
     */
    public function getCountryForIsoCodeWithInvalidCountryCodeReturnsEmptyString(): void
    {
        $this->importStaticData();
        $subject = new TestingLegacyEvent();

        $result = $subject->getCountryNameFromIsoCode('xy');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getCountryForIsoCodeWithEmptyCountryCodeReturnsEmptyString(): void
    {
        $this->importStaticData();
        $subject = new TestingLegacyEvent();

        $result = $subject->getCountryNameFromIsoCode('');

        self::assertSame('', $result);
    }

    // Tests for getImage

    /**
     * @test
     */
    public function getImageWithoutImageReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithImages.xml');

        $subject = new LegacyEvent(1);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithNotYetMigratedImageReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithImages.xml');

        $subject = new LegacyEvent(4);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithPositiveImageCountWithoutFileReferenceReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithImages.xml');

        $subject = new LegacyEvent(2);

        self::assertNull($subject->getImage());
    }

    /**
     * @test
     */
    public function getImageWithFileReferenceReturnsFileReference(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithImages.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyEvent(3);

        self::assertInstanceOf(FileReference::class, $subject->getImage());
    }

    /**
     * @test
     */
    public function getImageForDateForSingleEventWithFileReferenceReturnsFileReference(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithImages.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyEvent(5);

        self::assertInstanceOf(FileReference::class, $subject->getImage());
    }
}
