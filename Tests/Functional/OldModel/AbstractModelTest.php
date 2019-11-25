<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractModelTest extends FunctionalTestCase
{
    /**
     * @var int
     */
    const NOW = 1574714414;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['SIM_EXEC_TIME'] = self::NOW;
    }

    /**
     * @test
     */
    public function fromUidWithZeroReturnsNull()
    {
        $result = TestingModel::fromUid(0);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function fromUidForNonExistentRecordReturnsNull()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(99);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultIgnoresHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(2);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultIgnoresNotStartedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(4);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultIgnoresExpiredRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(5);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(3);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultForHiddenAllowedFindsHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(2, true);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultForHiddenAllowedFindsNotStartedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(4, true);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultForHiddenAllowedFindsExpiredRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(5, true);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultForHiddenAllowedFindsVisibleRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(1, true);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function fromUidByDefaultForHiddenAllowedIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(3, true);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function fromUidForExistingRecordCreatesInstanceOfSubclass()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = TestingModel::fromUid(1);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function fromUidMapsDataFromDatabase()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        /** @var TestingModel $result */
        $result = TestingModel::fromUid(1);

        self::assertSame('the first one', $result->getTitle());
    }

    /**
     * @test
     */
    public function constructionByUidForNonExistentRecordReturnsModelWithoutUid()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = new TestingModel(99);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionByUidByDefaultIgnoresHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = new TestingModel(2);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionByUidByDefaultIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = new TestingModel(3);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionByUidByDefaultForHiddenAllowedFindsHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = new TestingModel(2, false, true);

        self::assertTrue($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionByUidByDefaultForHiddenAllowedFindsVisibleRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = new TestingModel(1, false, true);

        self::assertTrue($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionByUidByDefaultForHiddenAllowedIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $result = new TestingModel(3, false, true);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionByUidSetsDataFromDatabase()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        /** @var TestingModel $result */
        $result = new TestingModel(1);

        self::assertSame('the first one', $result->getTitle());
    }

    /**
     * @test
     */
    public function comesFromDatabaseWithModelReadFromDatabaseIsTrue()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        /** @var TestingModel $result */
        $result = TestingModel::fromUid(1);

        self::assertTrue($result->comesFromDatabase());
    }

    /**
     * @test
     */
    public function comesFromDatabaseWithModelReadFromDatabaseViaUidInConstructorIsTrue()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        /** @var TestingModel $result */
        $result = new TestingModel(1);

        self::assertTrue($result->comesFromDatabase());
    }

    /**
     * @test
     */
    public function comesFromDatabaseWithInexistentModelViaUidInConstructorIsFalse()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        /** @var TestingModel $result */
        $result = new TestingModel(99);

        self::assertFalse($result->comesFromDatabase());
    }

    /**
     * @test
     */
    public function saveToDatabaseForModelWithoutUidWithEmptyDataNotCreatesRecordInDatabase()
    {
        $model = new TestingModel();

        $model->saveToDatabase([]);

        self::assertSame(0, $this->getDatabaseConnection()->selectCount('*', 'tx_seminars_test'));
    }

    /**
     * @test
     */
    public function saveToDatabaseForModelWithUidWithNonEmptyDataUpdatesRecordInDatabase()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $model = TestingModel::fromUid(1);

        $newTitle = 'new title';
        $model->saveToDatabase(['title' => $newTitle]);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_seminars_test', 'title = "' . $newTitle . '"')
        );
    }

    /**
     * @test
     */
    public function saveToDatabaseForModelWithUidWithNonEmptyDataNotChanesRecordData()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $model = TestingModel::fromUid(1);
        $oldTitle = $model->getTitle();

        $model->saveToDatabase(['title' => 'new title']);

        self::assertSame($oldTitle, $model->getTitle());
    }

    /**
     * @test
     */
    public function commitToDatabaseCanCreateNewRecord()
    {
        $title = 'There is no spoon.';
        $model = new TestingModel();
        $model->setTitle($title);

        $model->commitToDatabase();

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_seminars_test', 'title = "' . $title . '"')
        );
    }

    /**
     * @test
     */
    public function commitToDatabaseForNewRecordReturnsTrue()
    {
        $title = 'There is no spoon.';
        $model = new TestingModel();
        $model->setTitle($title);

        $result = $model->commitToDatabase();

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function commitToDatabaseProvidesNewRecordWithUid()
    {
        $model = new TestingModel();
        $model->setTitle('There is no spoon.');

        $model->commitToDatabase();

        self::assertGreaterThan(0, $model->getUid());
    }

    /**
     * @test
     */
    public function commitToDatabaseSetsCreationDateOfNewRecordToNow()
    {
        $model = new TestingModel();
        $model->setTitle('There is no spoon.');

        $model->commitToDatabase();

        $recordInDatabase = $this->getDatabaseConnection()
            ->selectSingleRow('*', 'tx_seminars_test', 'uid = ' . $model->getUid());
        self::assertSame(self::NOW, (int)$recordInDatabase['crdate']);
    }

    /**
     * @test
     */
    public function commitToDatabaseSetsTimestampOfNewRecordToNow()
    {
        $model = new TestingModel();
        $model->setTitle('There is no spoon.');

        $model->commitToDatabase();

        $recordInDatabase = $this->getDatabaseConnection()
            ->selectSingleRow('*', 'tx_seminars_test', 'uid = ' . $model->getUid());
        self::assertSame(self::NOW, (int)$recordInDatabase['tstamp']);
    }

    /**
     * @test
     */
    public function commitToDatabaseMarksNewRecordAsFromDatabase()
    {
        $model = new TestingModel();
        $model->setTitle('There is no spoon.');

        $model->commitToDatabase();

        self::assertTrue($model->comesFromDatabase());
    }

    /**
     * @test
     */
    public function commitToDatabaseNotPersistsEmptyRecord()
    {
        $model = new TestingModel();

        $model->commitToDatabase();

        self::assertSame(0, $this->getDatabaseConnection()->selectCount('*', 'tx_seminars_test'));
    }

    /**
     * @test
     */
    public function commitToDatabaseForEmptyRecordReturnsFalse()
    {
        $model = new TestingModel();

        $result = $model->commitToDatabase();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function commitToDatabaseCanUpdateExistingRecord()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $model = TestingModel::fromUid(1);
        $newTitle = 'new title';
        $model->setTitle($newTitle);

        $model->commitToDatabase();

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_seminars_test', 'title = "' . $newTitle . '"')
        );
    }

    /**
     * @test
     */
    public function commitToDatabaseForExistingRecordReturnsTrue()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $model = TestingModel::fromUid(1);
        $newTitle = 'new title';
        $model->setTitle($newTitle);

        $result = $model->commitToDatabase();

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function commitToDatabaseKeepsCreationDateOfExistingRecordUnchanged()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');

        $model = TestingModel::fromUid(1);
        $newTitle = 'new title';
        $model->setTitle($newTitle);

        $model->commitToDatabase();

        $recordInDatabase = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_seminars_test', 'uid = 1');
        self::assertSame(1574714377, (int)$recordInDatabase['crdate']);
    }

    /**
     * @test
     */
    public function commitToDatabaseSetsTimestampOfExistingRecordToNow()
    {
        $model = new TestingModel();
        $model->setTitle('There is no spoon.');

        $model->commitToDatabase();

        $recordInDatabase = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_seminars_test', 'uid = 1');
        self::assertSame(self::NOW, (int)$recordInDatabase['tstamp']);
    }
}
