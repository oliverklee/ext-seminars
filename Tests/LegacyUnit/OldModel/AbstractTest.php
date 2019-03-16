<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_OldModel_AbstractTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing
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
        $this->subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing($this->subjectUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ////////////////////////////////
    // Tests for creating objects.
    ////////////////////////////////

    public function testCreateFromUid()
    {
        self::assertTrue(
            $this->subject->isOk()
        );
    }

    public function testCreateFromUidFailsForInvalidUid()
    {
        $test = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(
            $this->subjectUid + 99
        );

        self::assertFalse(
            $test->isOk()
        );
    }

    public function testCreateFromUidFailsForZeroUid()
    {
        $test = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0);

        self::assertFalse(
            $test->isOk()
        );
    }

    public function testCreateFromDbResult()
    {
        $dbResult = \Tx_Oelib_Db::select(
            '*',
            'tx_seminars_test',
            'uid = ' . $this->subjectUid
        );

        $test = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(
            0,
            $dbResult
        );

        self::assertTrue(
            $test->isOk()
        );
    }

    /**
     * @test
     */
    public function createFromDirectDataResultsInOkay()
    {
        $subject = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0, false, false, ['title' => 'Foo']);

        self::assertTrue($subject->isOk());
    }

    /**
     * @test
     */
    public function createFromDbResultFailsForFalse()
    {
        $test = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0, false);

        self::assertFalse($test->isOk());
    }

    /**
     * @test
     */
    public function createFromDbResultFailsForHiddenRecord()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_test',
            $this->subjectUid,
            ['hidden' => 1]
        );

        $test = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing($this->subjectUid);

        self::assertFalse(
            $test->isOk()
        );
    }

    /**
     * @test
     */
    public function createFromDbResultWithAllowedHiddenRecordsGetsHiddenRecordFromDb()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_test',
            $this->subjectUid,
            ['hidden' => 1]
        );

        $test = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(
            $this->subjectUid,
            false,
            true
        );

        self::assertTrue(
            $test->isOk()
        );
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
        $virginFixture = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0);

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

    /**
     * @test
     */
    public function dataCanBeSetDirectlyInConstructor()
    {
        $subject = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0, false, false, ['title' => 'Foo']);

        self::assertSame('Foo', $subject->getTitle());
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

    public function testTypoScriptConfigurationIsLoaded()
    {
        self::assertTrue(
            $this->subject->getConfValueBoolean('isStaticTemplateLoaded')
        );
    }

    ///////////////////////////////////
    // Tests for commiting to the DB.
    ///////////////////////////////////

    public function testCommitToDbCanInsertNewRecord()
    {
        $title = 'Test record (with a unique title)';
        self::assertEquals(
            0,
            $this->testingFramework->countRecords('tx_seminars_test', 'title = "' . $title . '"')
        );

        $virginFixture = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0);
        $virginFixture->setTitle($title);
        $virginFixture->enableTestMode();
        $this->testingFramework->markTableAsDirty('tx_seminars_test');

        self::assertTrue(
            $virginFixture->isOk(),
            'The virgin fixture has not been completely initialized yet.'
        );

        self::assertTrue(
            $virginFixture->commitToDb()
        );
        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_test',
                'title = "' . $title . '"'
            )
        );
    }

    public function testCommitToDbCanUpdateExistingRecord()
    {
        $title = 'Test record (with a unique title)';
        $this->subject->setTitle($title);

        self::assertTrue(
            $this->subject->commitToDb()
        );
        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_test',
                'title = "' . $title . '"'
            )
        );
    }

    public function testSaveToDatabaseCanUpdateExistingRecord()
    {
        $this->subject->saveToDatabase(['title' => 'new title']);

        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_test',
                'title = "new title"'
            )
        );
    }

    public function testCommitToDbWillNotWriteIncompleteRecords()
    {
        $virginFixture = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0);
        $this->testingFramework->markTableAsDirty('tx_seminars_test');

        self::assertFalse(
            $virginFixture->isOk()
        );
        self::assertFalse(
            $virginFixture->commitToDb()
        );
    }

    /////////////////////////////////////
    // Tests concerning createMmRecords
    /////////////////////////////////////

    public function testCreateMmRecordsForEmptyTableNameThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            '$mmTable must not be empty.'
        );

        $this->subject->createMmRecords('', []);
    }

    public function testCreateMmRecordsOnObjectWithoutUidThrowsException()
    {
        $this->setExpectedException(
            \BadMethodCallException::class,
            'createMmRecords may only be called on objects that have a UID.'
        );

        $virginFixture = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(0);
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

    /*
     * Tests concerning recordExists
     */

    /**
     * @test
     */
    public function recordExistsForHiddenRecordAndNoHiddenRecordsAllowedReturnsFalse()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_test',
            $this->subjectUid,
            ['hidden' => 1]
        );

        self::assertFalse(
            \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing::recordExists($this->subjectUid, 'tx_seminars_test')
        );
    }

    /**
     * @test
     */
    public function recordExistsForHiddenRecordAndHiddenRecordsAllowedReturnsTrue()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_test',
            $this->subjectUid,
            ['hidden' => 1]
        );

        self::assertTrue(
            \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing::recordExists($this->subjectUid, 'tx_seminars_test', true)
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
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(
            $this->subjectUid
        );

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
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_Testing(
            $this->subjectUid
        );

        self::assertEquals(
            0,
            $subject->getPageUid()
        );
    }
}
