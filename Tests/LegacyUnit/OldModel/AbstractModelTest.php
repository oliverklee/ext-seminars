<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;

/**
 * @covers \OliverKlee\Seminars\OldModel\AbstractModel
 */
final class AbstractModelTest extends TestCase
{
    /**
     * @var TestingModel
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int UID of the minimal fixture's data in the DB
     */
    private $subjectUid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new TestingFramework('tx_seminars');
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

    //////////////////////////////////
    // Tests for getting attributes.
    //////////////////////////////////

    /**
     * @test
     */
    public function getUid()
    {
        self::assertEquals(
            $this->subjectUid,
            $this->subject->getUid()
        );
    }

    /**
     * @test
     */
    public function hasUidIsTrueForObjectsWithAUid()
    {
        self::assertNotEquals(
            0,
            $this->subjectUid
        );
        self::assertTrue(
            $this->subject->hasUid()
        );
    }

    /**
     * @test
     */
    public function hasUidIsFalseForObjectsWithoutUid()
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

    /**
     * @test
     */
    public function getTitle()
    {
        self::assertEquals(
            'Test',
            $this->subject->getTitle()
        );
    }

    //////////////////////////////////
    // Tests for setting attributes.
    //////////////////////////////////

    /**
     * @test
     */
    public function setAndGetRecordBooleanTest()
    {
        self::assertFalse(
            $this->subject->getBooleanTest()
        );

        $this->subject->setBooleanTest(true);
        self::assertTrue(
            $this->subject->getBooleanTest()
        );
    }

    /**
     * @test
     */
    public function setAndGetTitle()
    {
        $title = 'Test';
        $this->subject->setTitle($title);

        self::assertEquals(
            $title,
            $this->subject->getTitle()
        );
    }

    // Tests concerning getPageUid

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
