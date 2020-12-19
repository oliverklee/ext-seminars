<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EventTypeTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_EventType
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_EventType();
    }

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
        $this->subject->setTitle('Workshop');

        self::assertEquals(
            'Workshop',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'Workshop']);

        self::assertEquals(
            'Workshop',
            $this->subject->getTitle()
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
