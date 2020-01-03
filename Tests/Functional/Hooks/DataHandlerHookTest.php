<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Hooks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Hooks\DataHandlerHook;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
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

    protected function setUp()
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
    public function tceMainHookReferencesExistingClass()
    {
        $reference = $this->getDataMapperConfigurationForSeminars();

        self::assertSame(DataHandlerHook::class, $reference);
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
    public function afterDatabaseOperationsOnUpdateKeepsValidRegistrationDeadline(int $uid)
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedDeadline = $data['deadline_registration'];

        $this->subject
            ->processDatamap_afterDatabaseOperations('update', self::TABLE_SEMINARS, $uid, $data, $this->dataHandler);
        $this->subject->processDatamap_afterAllOperations();

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
    public function afterDatabaseOperationsOnUpdateResetsInvalidRegistrationDeadline(int $uid)
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);

        $this->subject
            ->processDatamap_afterDatabaseOperations('update', self::TABLE_SEMINARS, $uid, $data, $this->dataHandler);
        $this->subject->processDatamap_afterAllOperations();

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
    public function afterDatabaseOperationsOnNewResetsInvalidRegistrationDeadline(int $uid)
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $temporaryUid = 'NEW5e0f43477dcd4869591288';
        $this->dataHandler->substNEWwithIDs[$temporaryUid] = $uid;

        $this->subject->processDatamap_afterDatabaseOperations(
            'new',
            self::TABLE_SEMINARS,
            $temporaryUid,
            $data,
            $this->dataHandler
        );
        $this->subject->processDatamap_afterAllOperations();

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
    public function afterDatabaseOperationsOnUpdateKeepsValidEarlyBirdDeadline(int $uid)
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $expectedDeadline = $data['deadline_early_bird'];

        $this->subject
            ->processDatamap_afterDatabaseOperations('update', self::TABLE_SEMINARS, $uid, $data, $this->dataHandler);
        $this->subject->processDatamap_afterAllOperations();

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
    public function afterDatabaseOperationsOnUpdateResetsInvalidEarlyBirdDeadline(int $uid)
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);

        $this->subject
            ->processDatamap_afterDatabaseOperations('update', self::TABLE_SEMINARS, $uid, $data, $this->dataHandler);
        $this->subject->processDatamap_afterAllOperations();

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
    public function afterDatabaseOperationsOnNewResetsInvalidEarlyBirdDeadline(int $uid)
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DataMapperHook.xml');
        $data = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        $temporaryUid = 'NEW5e0f43477dcd4869591288';
        $this->dataHandler->substNEWwithIDs[$temporaryUid] = $uid;

        $this->subject->processDatamap_afterDatabaseOperations(
            'new',
            self::TABLE_SEMINARS,
            $temporaryUid,
            $data,
            $this->dataHandler
        );
        $this->subject->processDatamap_afterAllOperations();

        $row = $this->getDatabaseConnection()->selectSingleRow('*', self::TABLE_SEMINARS, 'uid = ' . $uid);
        self::assertSame(0, $row['deadline_early_bird']);
    }
}
