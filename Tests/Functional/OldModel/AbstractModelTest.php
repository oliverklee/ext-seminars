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
    public function fromUidSetsDataFromDatabase()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');
        /** @var TestingModel $result */
        $result = TestingModel::fromUid(1);

        self::assertSame('the first one', $result->getTitle());
    }

    /**
     * @test
     */
    public function constructionFromUidForNonExistentRecordReturnsModelWithoutUid()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');
        $result = new TestingModel(99);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionFromUidByDefaultIgnoresHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');
        $result = new TestingModel(2);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionFromUidByDefaultIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');
        $result = new TestingModel(3);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionFromUidByDefaultForHiddenAllowedFindsHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');
        $result = new TestingModel(2, false, true);

        self::assertTrue($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionFromUidByDefaultForHiddenAllowedFindsVisibleRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');
        $result = new TestingModel(1, false, true);

        self::assertTrue($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionFromUidByDefaultForHiddenAllowedIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Test.xml');
        $result = new TestingModel(3, false, true);

        self::assertFalse($result->hasUid());
    }

    /**
     * @test
     */
    public function constructionFromUidSetsDataFromDatabase()
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
}
