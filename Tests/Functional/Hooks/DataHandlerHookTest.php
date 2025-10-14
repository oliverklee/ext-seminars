<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Hooks;

use OliverKlee\Seminars\Hooks\DataHandlerHook;
use OliverKlee\Seminars\Hooks\Interfaces\DataSanitization;
use OliverKlee\Seminars\Tests\Support\BackEndTestsTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Hooks\DataHandlerHook
 */
final class DataHandlerHookTest extends FunctionalTestCase
{
    use BackEndTestsTrait;

    /**
     * @var string
     */
    private const TABLE_SEMINARS = 'tx_seminars_seminars';

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var DataHandlerHook
     */
    private $subject;

    /**
     * @var DataHandler
     */
    private $dataHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataHandler = new DataHandler();

        $this->subject = new DataHandlerHook();
    }

    private function initializeBackEndUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DataHandlerHook/BackEndUser.csv');
        $this->setUpBackendUser(1);
        $this->unifyBackEndLanguage();
    }

    private function getProcessDataMapConfigurationForSeminars(): string
    {
        $dataMapperConfiguration = (array)$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php'];

        return (string)$dataMapperConfiguration['processDatamapClass']['seminars'];
    }

    /**
     * @test
     */
    public function tceMainProcessDataMapHookReferencesExistingClass(): void
    {
        $reference = $this->getProcessDataMapConfigurationForSeminars();

        self::assertSame(DataHandlerHook::class, $reference);
    }

    private function getProcessCommandMapConfigurationForSeminars(): string
    {
        $dataMapperConfiguration = (array)$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php'];

        return (string)$dataMapperConfiguration['processCmdmapClass']['seminars'];
    }

    /**
     * @test
     */
    public function tceMainProcessCommandMapHookReferencesExistingClass(): void
    {
        $reference = $this->getProcessCommandMapConfigurationForSeminars();

        self::assertSame(DataHandlerHook::class, $reference);
    }

    private function processUpdateActionForSeminarsTable(int $uid): void
    {
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $this->dataHandler->datamap[self::TABLE_SEMINARS][$uid] = $data;

        $this->subject->processDatamap_afterAllOperations($this->dataHandler);
    }

    private function processNewActionForSeminarsTable(int $uid): void
    {
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $temporaryUid = 'NEW5e0f43477dcd4869591288';
        $this->dataHandler->datamap[self::TABLE_SEMINARS][$temporaryUid] = $data;
        $this->dataHandler->substNEWwithIDs[$temporaryUid] = $uid;

        $this->subject->processDatamap_afterAllOperations($this->dataHandler);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnUpdateCallsSanitizeEventDataHook(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');
        $uid = 1;
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }

        $hook = $this->createMock(DataSanitization::class);
        $hook->expects(self::once())->method('sanitizeEventData')
            ->with($uid, $data)
            ->willReturn([]);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DataSanitization::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->processUpdateActionForSeminarsTable($uid);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnUpdateSanitizeHookWillModifyData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');
        $uid = 1;
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $expectedTitle = 'ModifiedUpdateTitle';

        $hook = $this->createMock(DataSanitization::class);
        $hook->expects(self::once())->method('sanitizeEventData')
            ->with($uid, $data)
            ->willReturn(['title' => $expectedTitle]);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DataSanitization::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedTitle, $row['title']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnNewCallsSanitizeEventDataHook(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');
        $uid = 1;
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }

        $hook = $this->createMock(DataSanitization::class);
        $hook->expects(self::once())->method('sanitizeEventData')
            ->with($uid, $data)
            ->willReturn([]);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DataSanitization::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->processNewActionForSeminarsTable($uid);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnNewSanitizeHookWillModifyData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');
        $uid = 1;
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $expectedTitle = 'ModifiedNewTitle';

        $hook = $this->createMock(DataSanitization::class);
        $hook->expects(self::once())->method('sanitizeEventData')
            ->with($uid, $data)
            ->willReturn(['title' => $expectedTitle]);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DataSanitization::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->processNewActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedTitle, $row['title']);
    }

    /**
     * @return int[][]
     */
    public function validRegistrationDeadlineDataProvider(): array
    {
        return [
            'no begin date and no deadline' => [1],
            'begin date and no deadline' => [2],
            'begin date and same deadline' => [3],
            'begin date and earlier deadline' => [4],
        ];
    }

    /**
     * @test
     *
     * @dataProvider validRegistrationDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateKeepsValidRegistrationDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $expectedDeadline = $data['deadline_registration'];

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDeadline, $row['deadline_registration']);
    }

    /**
     * @return int[][]
     */
    public function invalidRegistrationDeadlineDataProvider(): array
    {
        return [
            'begin date before deadline' => [5],
            'no begin date, but deadline' => [6],
        ];
    }

    /**
     * @test
     *
     * @dataProvider invalidRegistrationDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateResetsInvalidRegistrationDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame(0, $row['deadline_registration']);
    }

    /**
     * @test
     *
     * @dataProvider invalidRegistrationDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnNewResetsInvalidRegistrationDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');

        $this->processNewActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame(0, $row['deadline_registration']);
    }

    /**
     * @return int[][]
     */
    public function validEarlyBirdDeadlineDataProvider(): array
    {
        return [
            'no begin date and no deadline' => [1],
            'begin date and no deadline' => [2],
            'begin date and same deadline' => [3],
            'begin date and earlier deadline' => [4],
        ];
    }

    /**
     * @test
     *
     * @dataProvider validEarlyBirdDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateKeepsValidEarlyBirdDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $expectedDeadline = $data['deadline_early_bird'];

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDeadline, $row['deadline_early_bird']);
    }

    /**
     * @return int[][]
     */
    public function invalidEarlyBirdDeadlineDataProvider(): array
    {
        return [
            'begin date before deadline' => [7],
            'no begin date, but deadline' => [8],
            'early-bird deadline after registration deadline' => [9],
        ];
    }

    /**
     * @test
     *
     * @dataProvider invalidEarlyBirdDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateResetsInvalidEarlyBirdDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame(0, $row['deadline_early_bird']);
    }

    /**
     * @test
     *
     * @dataProvider invalidEarlyBirdDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnNewResetsInvalidEarlyBirdDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook.xml');

        $this->processNewActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame(0, $row['deadline_early_bird']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnUpdateWithoutTimeSlotsKeepsBeginDate(): void
    {
        $uid = 1;
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TimeSlots.xml');
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $expectedDate = $data['begin_date'];

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDate, $row['begin_date']);
    }

    /**
     * @return int[][]
     */
    public function beginDateWithTimeSlotsDataProvider(): array
    {
        return [
            '1 time slot' => [2, 3000],
            '2 time slots' => [3, 500],
        ];
    }

    /**
     * @test
     *
     * @dataProvider beginDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnUpdateWithTimeSlotsOverwritesBeginDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDate, $row['begin_date']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnNewWithoutTimeSlotsKeepsBeginDate(): void
    {
        $uid = 1;
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TimeSlots.xml');
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $expectedDate = $data['begin_date'];

        $this->processNewActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDate, $row['begin_date']);
    }

    /**
     * @test
     *
     * @dataProvider beginDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnNewWithTimeSlotsOverwritesBeginDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TimeSlots.xml');

        $this->processNewActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDate, $row['begin_date']);
    }

    /**
     * @return int[][]
     */
    public function endDateWithTimeSlotsDataProvider(): array
    {
        return [
            '1 time slot' => [2, 3500],
            '2 time slots' => [3, 3500],
        ];
    }

    /**
     * @test
     *
     * @dataProvider endDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnUpdateWithTimeSlotsOverwritesEndDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDate, $row['end_date']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnNewWithoutTimeSlotsKeepsEndDate(): void
    {
        $uid = 1;
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TimeSlots.xml');
        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $data = $result->fetchAssociative();
        } else {
            $data = $result->fetch();
        }
        $expectedDate = $data['end_date'];

        $this->processNewActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDate, $row['end_date']);
    }

    /**
     * @test
     *
     * @dataProvider endDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnNewWithTimeSlotsOverwritesEndDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TimeSlots.xml');

        $this->processNewActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame($expectedDate, $row['end_date']);
    }

    /**
     * @return int[][]
     */
    public function noPlacesToAddFromTimeSlotsDataProvider(): array
    {
        return [
            'no time slots' => [1],
            'time slots without place' => [2],
        ];
    }

    /**
     * @test
     *
     * @dataProvider noPlacesToAddFromTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnUpdateForNoPlacesFromTimeSlotsNotAddsPlaces(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/NoPlacesFromTimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $associationCount = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_seminars_seminars_place_mm')
            ->count('*', 'tx_seminars_seminars_place_mm', ['uid_local' => $uid]);
        self::assertSame(0, $associationCount);
    }

    /**
     * @return array<string, array{0: positive-int, 1: positive-int}>
     */
    public function placesToAddFromTimeSlotsDataProvider(): array
    {
        return [
            '1 time slot with place' => [1, 1],
            '2 time slots with the same place' => [2, 1],
            '2 time slots with different places' => [3, 2],
        ];
    }

    /**
     * @test
     *
     * @dataProvider placesToAddFromTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnUpdateForFromTimeSlotsAddsPlacesToEvent(int $uid, int $expected): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/PlacesFromTimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $associationCount = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_seminars_seminars_place_mm')
            ->count('*', 'tx_seminars_seminars_place_mm', ['uid_local' => $uid]);
        self::assertSame($expected, $associationCount);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsForSingleEventWithSlugKeepsSlugUnchanged(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/SingleEventWithSlug.xml');
        $uid = 1;

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame('unchanged-slug', $row['slug']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsForTopicWithSlugKeepsSlugUnchanged(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TopicWithSlug.xml');
        $uid = 1;

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame('unchanged-slug', $row['slug']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsForEventDateWithSlugKeepsSlugUnchanged(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/EventDateWithSlug.xml');
        $uid = 1;

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame('unchanged-slug', $row['slug']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsForSingleEventWithoutSlugSetsSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/SingleEventWithoutSlug.xml');
        $uid = 1;

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame('single-event-without-slug', $row['slug']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsForTopicWithoutSlugSetsSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/TopicWithoutSlug.xml');
        $uid = 1;

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame('topic-without-slug', $row['slug']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsForEventDateWithoutSlugSetsSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataHandlerHook/EventDateWithoutSlug.xml');
        $uid = 1;

        $this->processUpdateActionForSeminarsTable($uid);

        $result = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE_SEMINARS)
            ->select(['*'], self::TABLE_SEMINARS, ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            $row = $result->fetchAssociative();
        } else {
            $row = $result->fetch();
        }
        self::assertSame('topic-with-slug', $row['slug']);
    }

    /**
     * @test
     */
    public function eventCanBeCopied(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DataHandlerHook/copy/SingleEventOnPage.csv');

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], ['tx_seminars_seminars' => [1 => ['copy' => -1]]]);
        $dataHandler->process_cmdmap();

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DataHandlerHook/copy/SingleEventOnPageAndDuplicate.csv');
    }

    /**
     * @test
     */
    public function doesNotDuplicateRegistrationsWhenCopyingEvent(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DataHandlerHook/copy/SingleEventWithOneRegistration.csv');

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], ['tx_seminars_seminars' => [1 => ['copy' => -1]]]);
        $dataHandler->process_cmdmap();

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/DataHandlerHook/copy/SingleEventWithOneRegistrationAndDuplicateWithRegistrations.csv'
        );
    }

    /**
     * @test
     */
    public function canMoveRegistration(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DataHandlerHook/move/SingleEventOnPage.csv');

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], ['tx_seminars_seminars' => [1 => ['move' => 2]]]);
        $dataHandler->process_cmdmap();

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/DataHandlerHook/move/SingleEventOnPageAfterMoving.csv'
        );
    }

    /**
     * @test
     */
    public function doesNotMoveRegistrationsWhenMovingEvent(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DataHandlerHook/move/SingleEventWithOneRegistrationOnPage.csv');

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], ['tx_seminars_seminars' => [1 => ['move' => 2]]]);
        $dataHandler->process_cmdmap();

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/DataHandlerHook/move/SingleEventWithOneRegistrationOnPageAfterMoving.csv'
        );
    }
}
