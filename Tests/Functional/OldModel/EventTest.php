<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingEvent;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

final class EventTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var array<string, string>
     */
    const CONFIGURATION = [
        'dateFormatYMD' => '%d.%m.%Y',
        'timeFormat' => '%H:%M',
    ];

    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var array<int, string>
     */
    protected $additionalFoldersToCreate = ['uploads/tx_seminars'];

    /**
     * @var array<int, string>
     */
    private $filesToDelete = [];

    /**
     * @var TemplateHelper|null
     */
    private $plugin = null;

    protected function tearDown()
    {
        foreach ($this->filesToDelete as $file) {
            \unlink($this->getInstancePath() . '/' . $file);
        }
    }

    private function buildPlugin()
    {
        $plugin = new TemplateHelper();
        $plugin->init(['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html']);
        $plugin->cObj = new ContentObjectRenderer();
        $this->plugin = $plugin;
    }

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

    /**
     * @test
     */
    public function getAttachedFilesForOneFileReturnsOneElement()
    {
        $this->buildPlugin();

        $fileName = 'test.txt';
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function getAttachedFilesForTwoFilesReturnsTwoElements()
    {
        $this->buildPlugin();

        $fileName = 'test.txt,test2.txt';
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertCount(2, $result);
    }

    /**
     * @test
     */
    public function getAttachedFilesReturnsFileName()
    {
        $this->buildPlugin();

        $fileName = 'test.txt';
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertStringContainsString('uploads/tx_seminars/' . $fileName, $result[0]['name']);
    }

    /**
     * @test
     */
    public function getAttachedFilesReturnsSize()
    {
        $this->buildPlugin();

        $fileName = 'test.txt';
        $this->filesToDelete[] = $fileName;
        \file_put_contents($this->getInstancePath() . '/uploads/tx_seminars/' . $fileName, 'Hello!');
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertSame(GeneralUtility::formatSize(6), $result[0]['size']);
    }

    /**
     * @test
     */
    public function getAttachedFilesReturnsTypeBySuffix()
    {
        $this->buildPlugin();

        $fileName = 'test.txt';
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertSame('txt', $result[0]['type']);
    }

    /**
     * @test
     */
    public function getAttachedFilesForDotAndSuffixOnlyReturnsTypeBySuffix()
    {
        $this->buildPlugin();

        $fileName = '.txt';
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertSame('txt', $result[0]['type']);
    }

    /**
     * @test
     */
    public function getAttachedFilesForDotOnlyReturnsTypeNone()
    {
        $this->buildPlugin();

        $fileName = '.';
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertSame('none', $result[0]['type']);
    }

    /**
     * @test
     */
    public function getAttachedFilesForFileWithoutSuffixReturnsTypeNone()
    {
        $this->buildPlugin();

        $fileName = 'test';
        $subject = \Tx_Seminars_OldModel_Event::fromData(['attached_files' => $fileName]);

        $result = $subject->getAttachedFiles($this->plugin);

        self::assertSame('none', $result[0]['type']);
    }

    /**
     * @test
     */
    public function getAttachedFilesForDateWithFilesReturnsFilesFromDate()
    {
        $this->buildPlugin();

        $fileName = 'test.txt';
        $topic = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]);
        $date = \Tx_Seminars_OldModel_Event::fromData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'attached_files' => $fileName,
            ]
        );
        $date->setTopic($topic);

        $result = $date->getAttachedFiles($this->plugin);

        self::assertStringContainsString('uploads/tx_seminars/' . $fileName, $result[0]['name']);
    }

    /**
     * @test
     */
    public function getAttachedFilesForDateWithoutFilesAndTopicWithFilesReturnsFilesFromTopic()
    {
        $this->buildPlugin();

        $fileName = 'test.txt';
        $topic = \Tx_Seminars_OldModel_Event::fromData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'attached_files' => $fileName,
            ]
        );
        $date = \Tx_Seminars_OldModel_Event::fromData(['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]);
        $date->setTopic($topic);

        $result = $date->getAttachedFiles($this->plugin);

        self::assertStringContainsString('uploads/tx_seminars/' . $fileName, $result[0]['name']);
    }

    /**
     * @test
     */
    public function getAttachedFilesForDateAndTopicWithFilesReturnsFilesFromDTopicAndDate()
    {
        $this->buildPlugin();

        $topicFileName = 'test-topic.txt';
        $topic = \Tx_Seminars_OldModel_Event::fromData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'attached_files' => $topicFileName,
            ]
        );
        $dateFileName = 'test-date.txt';
        $date = \Tx_Seminars_OldModel_Event::fromData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'attached_files' => $dateFileName,
            ]
        );
        $date->setTopic($topic);

        $result = $date->getAttachedFiles($this->plugin);

        self::assertStringContainsString($topicFileName, $result[0]['name']);
        self::assertStringContainsString($dateFileName, $result[1]['name']);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWithoutPlacesReturnsEmptyArray()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(1);
        $result = $subject->getPlacesWithCountry();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryForPlacesWithoutCountryReturnsEmptyArray()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(2);
        $result = $subject->getPlacesWithCountry();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryForPlacesWithValidCountryReturnsCountryCode()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(4);
        $result = $subject->getPlacesWithCountry();

        self::assertSame(['ch'], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryForPlacesWithInvalidCountryReturnsCountryCode()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(5);
        $result = $subject->getPlacesWithCountry();

        self::assertSame(['xy'], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWillReturnCountriesInSortingOrder()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(7);
        $result = $subject->getPlacesWithCountry();

        self::assertSame(['de', 'ch'], $result);
    }

    /**
     * @test
     */
    public function getPlacesWithCountryIgnoresDeletedCountry()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(6);
        $result = $subject->getPlacesWithCountry();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function hasCountryForNoPlacesReturnsFalse()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(1);

        self::assertFalse($subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryForPlaceWithoutCountryReturnsFalse()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(2);

        self::assertFalse($subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryForPlaceWithDeletedCountryReturnsFalse()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(6);

        self::assertFalse($subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryForPlaceWithCountryReturnsTrue()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(4);

        self::assertTrue($subject->hasCountry());
    }

    /**
     * @test
     */
    public function getPlaceShortReturnsPlaceName()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(2);

        self::assertStringContainsString('The Castle', $subject->getPlaceShort());
    }

    /**
     * @test
     */
    public function getPlaceShortIgnoresDuplicatePlaces()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events/EventsWithPlaces.xml');

        $subject = TestingEvent::fromUid(3);

        self::assertSame(1, \substr_count($subject->getPlaceShort(), 'The Castle'));
    }
}
