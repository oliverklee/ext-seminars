<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

final class CategoryTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Category
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Category();
    }

    ///////////////////////////////
    // Tests regarding the title.
    ///////////////////////////////

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $title must not be empty.'
        );

        $this->subject->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('Lecture');

        self::assertEquals(
            'Lecture',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'Lecture']);

        self::assertEquals(
            'Lecture',
            $this->subject->getTitle()
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
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getIcon()
        );
    }

    /**
     * @test
     */
    public function getIconWithNonEmptyIconReturnsIcon()
    {
        $this->subject->setData(['icon' => 'icon.gif']);

        self::assertEquals(
            'icon.gif',
            $this->subject->getIcon()
        );
    }

    /**
     * @test
     */
    public function setIconSetsIcon()
    {
        $this->subject->setIcon('icon.gif');

        self::assertEquals(
            'icon.gif',
            $this->subject->getIcon()
        );
    }

    /**
     * @test
     */
    public function hasIconInitiallyReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasIcon()
        );
    }

    /**
     * @test
     */
    public function hasIconWithIconReturnsTrue()
    {
        $this->subject->setIcon('icon.gif');

        self::assertTrue(
            $this->subject->hasIcon()
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
        $this->subject->setData(['single_view_page' => 42]);

        self::assertEquals(
            42,
            $this->subject->getSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForZeroPageUidReturnsFalse()
    {
        $this->subject->setData(['single_view_page' => 0]);

        self::assertFalse(
            $this->subject->hasSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForNonZeroPageUidReturnsTrue()
    {
        $this->subject->setData(['single_view_page' => 42]);

        self::assertTrue(
            $this->subject->hasSingleViewPageUid()
        );
    }
}
