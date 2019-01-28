<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_OldModel_CategoryTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Category
     */
    private $subject;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * UID of the fixture's data in the DB
     *
     * @var int
     */
    private $subjectUid = 0;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Test category']
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    public function testCreateFromUid()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category($this->subjectUid);

        self::assertTrue(
            $this->subject->isOk()
        );
    }

    public function testCreateFromUidFailsForInvalidUid()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category($this->subjectUid + 99);

        self::assertFalse(
            $this->subject->isOk()
        );
    }

    public function testCreateFromUidFailsForZeroUid()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category(0);

        self::assertFalse(
            $this->subject->isOk()
        );
    }

    public function testCreateFromDbResult()
    {
        $dbResult = \Tx_Oelib_Db::select(
            '*',
            'tx_seminars_categories',
            'uid = ' . $this->subjectUid
        );

        $this->subject = new \Tx_Seminars_OldModel_Category(0, $dbResult);

        self::assertTrue(
            $this->subject->isOk()
        );
    }

    public function testCreateFromDbResultFailsForNull()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category(0, null);

        self::assertFalse(
            $this->subject->isOk()
        );
    }

    public function testGetTitle()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category($this->subjectUid);

        self::assertEquals(
            'Test category',
            $this->subject->getTitle()
        );
    }

    public function testGetIconReturnsIcon()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category(
            $this->testingFramework->createRecord(
                'tx_seminars_categories',
                [
                    'title' => 'Test category',
                    'icon' => 'foo.gif',
                ]
            )
        );

        self::assertEquals(
            'foo.gif',
            $this->subject->getIcon()
        );
    }

    public function testGetIconReturnsEmptyStringIfCategoryHasNoIcon()
    {
        $this->subject = new \Tx_Seminars_OldModel_Category($this->subjectUid);

        self::assertEquals(
            '',
            $this->subject->getIcon()
        );
    }
}
