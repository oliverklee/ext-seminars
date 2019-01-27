<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_CategoryTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Model_Category
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new \Tx_Seminars_Model_Category();
    }

    ///////////////////////////////
    // Tests regarding the title.
    ///////////////////////////////

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $title must not be empty.'
        );

        $this->fixture->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->fixture->setTitle('Lecture');

        self::assertEquals(
            'Lecture',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Lecture']);

        self::assertEquals(
            'Lecture',
            $this->fixture->getTitle()
        );
    }

    //////////////////////////////
    // Tests regarding the icon.
    //////////////////////////////

    /**
     * @test
     */
    public function getIconInitiallyReturnsAnEmptyString()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getIcon()
        );
    }

    /**
     * @test
     */
    public function getIconWithNonEmptyIconReturnsIcon()
    {
        $this->fixture->setData(['icon' => 'icon.gif']);

        self::assertEquals(
            'icon.gif',
            $this->fixture->getIcon()
        );
    }

    /**
     * @test
     */
    public function setIconSetsIcon()
    {
        $this->fixture->setIcon('icon.gif');

        self::assertEquals(
            'icon.gif',
            $this->fixture->getIcon()
        );
    }

    /**
     * @test
     */
    public function hasIconInitiallyReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasIcon()
        );
    }

    /**
     * @test
     */
    public function hasIconWithIconReturnsTrue()
    {
        $this->fixture->setIcon('icon.gif');

        self::assertTrue(
            $this->fixture->hasIcon()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning the single view page UID
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function getSingleViewPageUidReturnsSingleViewPageUid()
    {
        $this->fixture->setData(['single_view_page' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForZeroPageUidReturnsFalse()
    {
        $this->fixture->setData(['single_view_page' => 0]);

        self::assertFalse(
            $this->fixture->hasSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForNonZeroPageUidReturnsTrue()
    {
        $this->fixture->setData(['single_view_page' => 42]);

        self::assertTrue(
            $this->fixture->hasSingleViewPageUid()
        );
    }
}
