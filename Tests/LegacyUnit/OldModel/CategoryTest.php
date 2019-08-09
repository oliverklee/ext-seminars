<?php

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_OldModel_CategoryTest extends TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function getTitleReturnsTitle()
    {
        $title = 'Test category';
        $subject = new \Tx_Seminars_OldModel_Category(0, false, false, ['title' => $title]);

        self::assertSame($title, $subject->getTitle());
    }

    /**
     * @test
     */
    public function getIconReturnsIcon()
    {
        $icon = 'foo.gif';
        $subject = new \Tx_Seminars_OldModel_Category(0, false, false, ['icon' => $icon]);

        self::assertSame($icon, $subject->getIcon());
    }

    /**
     * @test
     */
    public function createFromUidMapsAllFields()
    {
        $title = 'Test category';
        $icon = 'foo.gif';
        $subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => $title, 'icon' => $icon]
        );
        $subject = new \Tx_Seminars_OldModel_Category($subjectUid);

        self::assertTrue($subject->isOk());
        self::assertSame($title, $subject->getTitle());
        self::assertSame($icon, $subject->getIcon());
    }
}
