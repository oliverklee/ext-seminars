<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Bag;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Bag\TestingBag;

final class AbstractBagTest extends TestCase
{
    /**
     * @var TestingBag
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int the UID of the first test record in the DB
     */
    private $uidOfFirstRecord = 0;

    /**
     * @var int the UID of the second test record in the DB
     */
    private $uidOfSecondRecord = 0;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->uidOfFirstRecord = $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'test 1']
        );
        $this->uidOfSecondRecord = $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'test 2']
        );

        $this->subject = new TestingBag('is_dummy_record=1');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic bag functionality.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function emptyBagHasNoUids()
    {
        $bag = new TestingBag('1 = 2');

        self::assertEquals(
            '',
            $bag->getUids()
        );
    }

    /**
     * @test
     */
    public function bagCanHaveOneUid()
    {
        $bag = new TestingBag('uid = ' . $this->uidOfFirstRecord);

        self::assertEquals(
            (string)$this->uidOfFirstRecord,
            $bag->getUids()
        );
    }

    /**
     * @test
     */
    public function bagCanHaveTwoUids()
    {
        self::assertEquals(
            $this->uidOfFirstRecord . ',' . $this->uidOfSecondRecord,
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function bagSortsByUidByDefault()
    {
        self::assertEquals(
            $this->uidOfFirstRecord,
            $this->subject->current()->getUid()
        );

        self::assertEquals(
            $this->uidOfSecondRecord,
            $this->subject->next()->getUid()
        );
    }

    ///////////////////////////
    // Tests concerning count
    ///////////////////////////

    /**
     * @test
     */
    public function countForEmptyBagReturnsZero()
    {
        $bag = new TestingBag('1 = 2');

        self::assertEquals(
            0,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function countForBagWithOneElementReturnsOne()
    {
        $bag = new TestingBag('uid=' . $this->uidOfFirstRecord);

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function countForBagWithTwoElementsReturnsTwo()
    {
        self::assertEquals(
            2,
            $this->subject->count()
        );
    }

    /**
     * @test
     */
    public function countAfterCallingNextForBagWithTwoElementsReturnsTwo()
    {
        $this->subject->rewind();
        $this->subject->next();

        self::assertEquals(
            2,
            $this->subject->count()
        );
    }

    /**
     * @test
     */
    public function countForBagWithTwoMatchesElementsAndLimitOfOneReturnsOne()
    {
        $bag = new TestingBag('is_dummy_record = 1', '', '', '', '1');

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    ///////////////////////////////////////
    // Tests concerning countWithoutLimit
    ///////////////////////////////////////

    /**
     * @test
     */
    public function countWithoutLimitForEmptyBagReturnsZero()
    {
        $bag = new TestingBag('1 = 2');

        self::assertEquals(
            0,
            $bag->countWithoutLimit()
        );
    }

    /**
     * @test
     */
    public function countWithoutLimitForBagWithOneElementReturnsOne()
    {
        $bag = new TestingBag('uid = ' . $this->uidOfFirstRecord);

        self::assertEquals(
            1,
            $bag->countWithoutLimit()
        );
    }

    /**
     * @test
     */
    public function countWithoutLimitForBagWithTwoElementsReturnsTwo()
    {
        self::assertEquals(
            2,
            $this->subject->countWithoutLimit()
        );
    }

    /**
     * @test
     */
    public function countWithoutLimitAfterCallingNextForBagWithTwoElementsReturnsTwo()
    {
        $this->subject->rewind();
        $this->subject->next();

        self::assertEquals(
            2,
            $this->subject->countWithoutLimit()
        );
    }

    /**
     * @test
     */
    public function countWithoutLimitForBagWithTwoMatchesElementsAndLimitOfOneReturnsTwo()
    {
        $bag = new TestingBag('is_dummy_record = 1', '', '', '', '1');

        self::assertEquals(
            2,
            $bag->countWithoutLimit()
        );
    }

    /////////////////////////////
    // Tests concerning isEmpty
    /////////////////////////////

    /**
     * @test
     */
    public function isEmptyForEmptyBagReturnsTrue()
    {
        $bag = new TestingBag('1=2');

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForBagWithOneElementReturnsFalse()
    {
        $bag = new TestingBag('uid = ' . $this->uidOfFirstRecord);

        self::assertFalse(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForBagWithTwoElementsReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isEmpty()
        );
    }
}
