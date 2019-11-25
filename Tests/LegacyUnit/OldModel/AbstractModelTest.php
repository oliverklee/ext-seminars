<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModelWithConfiguration;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class AbstractModelTest extends TestCase
{
    /**
     * @var TestingModel
     */
    protected $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var int UID of the minimal fixture's data in the DB
     */
    private $subjectUid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $systemFolderUid = $this->testingFramework->createSystemFolder();
        $this->testingFramework->createTemplate(
            $systemFolderUid,
            [
                'tstamp' => $GLOBALS['SIM_EXEC_TIME'],
                'sorting' => 256,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'cruser_id' => 1,
                'title' => 'TEST',
                'root' => 1,
                'clear' => 3,
                'include_static_file' => 'EXT:seminars/Configuration/TypoScript/',
            ]
        );
        $this->subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_test',
            [
                'pid' => $systemFolderUid,
                'title' => 'Test',
            ]
        );
        $this->subject = new TestingModel($this->subjectUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ////////////////////////////////
    // Tests for creating objects.
    ////////////////////////////////

    public function testCreateFromDbResult()
    {
        $dbResult = \Tx_Oelib_Db::select(
            '*',
            'tx_seminars_test',
            'uid = ' . $this->subjectUid
        );

        $test = new TestingModel(
            0,
            $dbResult
        );

        self::assertTrue($test->comesFromDatabase());
    }

    /**
     * @test
     */
    public function createFromDbResultFailsForFalse()
    {
        $test = new TestingModel(0, false);

        self::assertFalse($test->isOk());
    }

    //////////////////////////////////
    // Tests for getting attributes.
    //////////////////////////////////

    public function testGetUid()
    {
        self::assertEquals(
            $this->subjectUid,
            $this->subject->getUid()
        );
    }

    public function testHasUidIsTrueForObjectsWithAUid()
    {
        self::assertNotEquals(
            0,
            $this->subjectUid
        );
        self::assertTrue(
            $this->subject->hasUid()
        );
    }

    public function testHasUidIsFalseForObjectsWithoutUid()
    {
        $virginFixture = new TestingModel();

        self::assertEquals(
            0,
            $virginFixture->getUid()
        );
        self::assertFalse(
            $virginFixture->hasUid()
        );
    }

    public function testGetTitle()
    {
        self::assertEquals(
            'Test',
            $this->subject->getTitle()
        );
    }

    //////////////////////////////////
    // Tests for setting attributes.
    //////////////////////////////////

    public function testSetAndGetRecordBooleanTest()
    {
        self::assertFalse(
            $this->subject->getBooleanTest()
        );

        $this->subject->setBooleanTest(true);
        self::assertTrue(
            $this->subject->getBooleanTest()
        );
    }

    public function testSetAndGetTitle()
    {
        $title = 'Test';
        $this->subject->setTitle($title);

        self::assertEquals(
            $title,
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function typoScriptConfigurationIsLoaded()
    {
        $subject = new TestingModelWithConfiguration($this->subjectUid);

        self::assertTrue($subject->getConfValueBoolean('isStaticTemplateLoaded'));
    }

    /////////////////////////////////////
    // Tests concerning createMmRecords
    /////////////////////////////////////

    public function testCreateMmRecordsForEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$mmTable must not be empty.'
        );

        $this->subject->createMmRecords('', []);
    }

    public function testCreateMmRecordsOnObjectWithoutUidThrowsException()
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'createMmRecords may only be called on objects that have a UID.'
        );

        $virginFixture = new TestingModel();
        $virginFixture->createMmRecords('tx_seminars_test_test_mm', []);
    }

    public function testCreateMmRecordsWithEmptyReferencesReturnsZero()
    {
        self::assertEquals(
            0,
            $this->subject->createMmRecords(
                'tx_seminars_test_test_mm',
                []
            )
        );
    }

    public function testCreateMmRecordsWithOneReferenceReturnsOne()
    {
        $this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');

        self::assertEquals(
            1,
            $this->subject->createMmRecords(
                'tx_seminars_test_test_mm',
                [42]
            )
        );
    }

    public function testCreateMmRecordsWithTwoReferencesReturnsTwo()
    {
        $this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');

        self::assertEquals(
            2,
            $this->subject->createMmRecords(
                'tx_seminars_test_test_mm',
                [42, 31]
            )
        );
    }

    public function testCreateMmRecordsWithOneReferenceCreatesMmRecord()
    {
        $this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');
        $this->subject->createMmRecords(
            'tx_seminars_test_test_mm',
            [42]
        );

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_test_test_mm',
                'uid_local = ' . $this->subjectUid . ' AND uid_foreign = 42'
            )
        );
    }

    public function testCreateMmRecordsWithCreatesFirstMmRecordWithSortingOne()
    {
        $this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');
        $this->subject->createMmRecords(
            'tx_seminars_test_test_mm',
            [42]
        );

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_test_test_mm',
                'uid_local = ' . $this->subjectUid . ' AND sorting = 1'
            )
        );
    }

    public function testCreateMmRecordsWithCreatesSecondMmRecordWithSortingTwo()
    {
        $this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');
        $this->subject->createMmRecords(
            'tx_seminars_test_test_mm',
            [42, 31]
        );

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_test_test_mm',
                'uid_local = ' . $this->subjectUid . ' AND uid_foreign = 31 ' .
                'AND sorting = 2'
            )
        );
    }

    ////////////////////////////////
    // Tests concerning getPageUid
    ////////////////////////////////

    /**
     * @test
     */
    public function getPageUidCanReturnRecordsPageUid()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_test',
            $this->subjectUid,
            ['pid' => 42]
        );
        $subject = new TestingModel($this->subjectUid);

        self::assertEquals(
            42,
            $subject->getPageUid()
        );
    }

    /**
     * @test
     */
    public function getPageUidForRecordWithPageUidZeroReturnsZero()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_test',
            $this->subjectUid,
            ['pid' => 0]
        );
        $subject = new TestingModel($this->subjectUid);

        self::assertEquals(
            0,
            $subject->getPageUid()
        );
    }
}
