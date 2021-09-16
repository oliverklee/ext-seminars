<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModelWithConfiguration;

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
