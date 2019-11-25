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
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['SIM_EXEC_TIME'] = 1574712537;
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
}
