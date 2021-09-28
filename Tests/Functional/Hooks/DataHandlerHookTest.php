<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Hooks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Hooks\DataHandlerHook;
use OliverKlee\Seminars\Hooks\Interfaces\DataSanitization;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DataHandlerHookTest extends FunctionalTestCase
{
    /**
     * @var string
     */
    const TABLE_SEMINARS = 'tx_seminars_seminars';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/seminars'];

    /**
     * @var DataHandlerHook
     */
    private $subject = null;

    /**
     * @var DataHandler
     */
    private $dataHandler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataHandler = new DataHandler();

        $this->subject = new DataHandlerHook();
    }

    private function getDataMapperConfigurationForSeminars(): string
    {
        $dataMapperConfiguration = (array)$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php'];

        return (string)$dataMapperConfiguration['processDatamapClass']['seminars'];
    }

    /**
     * @test
     */
    public function tceMainHookReferencesExistingClass(): void
    {
        $reference = $this->getDataMapperConfigurationForSeminars();

        self::assertSame(DataHandlerHook::class, $reference);
    }

    /**
     * @param int $uid
     */
    private function processUpdateActionForSeminarsTable(int $uid): void
    {
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $this->dataHandler->datamap[self::TABLE_SEMINARS][$uid] = $data;

        $this->subject->processDatamap_afterAllOperations($this->dataHandler);
    }

    /**
     * @param int $uid
     */
    private function processNewActionForSeminarsTable(int $uid): void
    {
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $uid = 1;
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);

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
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $uid = 1;
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedTitle = 'ModifiedUpdateTitle';

        $hook = $this->createMock(DataSanitization::class);
        $hook->expects(self::once())->method('sanitizeEventData')
            ->with($uid, $data)
            ->willReturn(['title' => $expectedTitle]);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DataSanitization::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame($expectedTitle, $row['title']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnNewCallsSanitizeEventDataHook(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $uid = 1;
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);

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
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $uid = 1;
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedTitle = 'ModifiedNewTitle';

        $hook = $this->createMock(DataSanitization::class);
        $hook->expects(self::once())->method('sanitizeEventData')
            ->with($uid, $data)
            ->willReturn(['title' => $expectedTitle]);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DataSanitization::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->processNewActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
     * @param int $uid
     *
     * @dataProvider validRegistrationDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateKeepsValidRegistrationDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedDeadline = $data['deadline_registration'];

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
     * @param int $uid
     *
     * @dataProvider invalidRegistrationDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateResetsInvalidRegistrationDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame(0, $row['deadline_registration']);
    }

    /**
     * @test
     *
     * @param int $uid
     *
     * @dataProvider invalidRegistrationDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnNewResetsInvalidRegistrationDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');

        $this->processNewActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
     * @param int $uid
     *
     * @dataProvider validEarlyBirdDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateKeepsValidEarlyBirdDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedDeadline = $data['deadline_early_bird'];

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
     * @param int $uid
     *
     * @dataProvider invalidEarlyBirdDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnUpdateResetsInvalidEarlyBirdDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame(0, $row['deadline_early_bird']);
    }

    /**
     * @test
     *
     * @param int $uid
     *
     * @dataProvider invalidEarlyBirdDeadlineDataProvider
     */
    public function afterDatabaseOperationsOnNewResetsInvalidEarlyBirdDeadline(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');

        $this->processNewActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame(0, $row['deadline_early_bird']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnUpdateWithoutTimeSlotsKeepsBeginDate(): void
    {
        $uid = 1;
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/TimeSlots.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedDate = $data['begin_date'];

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
     * @param int $uid
     * @param int $expectedDate
     *
     * @dataProvider beginDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnUpdateWithTimeSlotsOverwritesBeginDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/TimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame($expectedDate, $row['begin_date']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnNewWithoutTimeSlotsKeepsBeginDate(): void
    {
        $uid = 1;
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/TimeSlots.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedDate = $data['begin_date'];

        $this->processNewActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame($expectedDate, $row['begin_date']);
    }

    /**
     * @test
     *
     * @param int $uid
     * @param int $expectedDate
     *
     * @dataProvider beginDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnNewWithTimeSlotsOverwritesBeginDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/TimeSlots.xml');

        $this->processNewActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
     * @param int $uid
     * @param int $expectedDate
     *
     * @dataProvider endDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnUpdateWithTimeSlotsOverwritesEndDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/TimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame($expectedDate, $row['end_date']);
    }

    /**
     * @test
     */
    public function afterDatabaseOperationsOnNewWithoutTimeSlotsKeepsEndDate(): void
    {
        $uid = 1;
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/TimeSlots.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedDate = $data['end_date'];

        $this->processNewActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame($expectedDate, $row['end_date']);
    }

    /**
     * @test
     *
     * @param int $uid
     * @param int $expectedDate
     *
     * @dataProvider endDateWithTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnNewWithTimeSlotsOverwritesEndDate(int $uid, int $expectedDate): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/TimeSlots.xml');

        $this->processNewActionForSeminarsTable($uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
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
     * @param int $uid
     *
     * @dataProvider noPlacesToAddFromTimeSlotsDataProvider
     */
    public function afterDatabaseOperationsOnUpdateForNoPlacesFromTimeSlotsNotAddsPlaces(int $uid): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/NoPlacesFromTimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $associationCount = $this->getDatabaseConnection()
            ->selectCount('*', 'tx_seminars_seminars_place_mm', 'uid_local = ' . $uid);
        self::assertSame(0, $associationCount);
    }

    /**
     * @return array<string, array<int, int>>
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
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook/PlacesFromTimeSlots.xml');

        $this->processUpdateActionForSeminarsTable($uid);

        $associationCount = $this->getDatabaseConnection()
            ->selectCount('*', 'tx_seminars_seminars_place_mm', 'uid_local = ' . $uid);
        self::assertSame($expected, $associationCount);
    }
}
