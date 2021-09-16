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

    public function testEmptyBagHasNoUids()
    {
        $bag = new TestingBag('1 = 2');

        self::assertEquals(
            '',
            $bag->getUids()
        );
    }

    public function testBagCanHaveOneUid()
    {
        $bag = new TestingBag('uid = ' . $this->uidOfFirstRecord);

        self::assertEquals(
            (string)$this->uidOfFirstRecord,
            $bag->getUids()
        );
    }

    public function testBagCanHaveTwoUids()
    {
        self::assertEquals(
            $this->uidOfFirstRecord . ',' . $this->uidOfSecondRecord,
            $this->subject->getUids()
        );
    }

    public function testBagSortsByUidByDefault()
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

    public function testCountForEmptyBagReturnsZero()
    {
        $bag = new TestingBag('1 = 2');

        self::assertEquals(
            0,
            $bag->count()
        );
    }

    public function testCountForBagWithOneElementReturnsOne()
    {
        $bag = new TestingBag('uid=' . $this->uidOfFirstRecord);

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testCountForBagWithTwoElementsReturnsTwo()
    {
        self::assertEquals(
            2,
            $this->subject->count()
        );
    }

    public function testCountAfterCallingNextForBagWithTwoElementsReturnsTwo()
    {
        $this->subject->rewind();
        $this->subject->next();

        self::assertEquals(
            2,
            $this->subject->count()
        );
    }

    public function testCountForBagWithTwoMatchesElementsAndLimitOfOneReturnsOne()
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

    public function testCountWithoutLimitForEmptyBagReturnsZero()
    {
        $bag = new TestingBag('1 = 2');

        self::assertEquals(
            0,
            $bag->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitForBagWithOneElementReturnsOne()
    {
        $bag = new TestingBag('uid = ' . $this->uidOfFirstRecord);

        self::assertEquals(
            1,
            $bag->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitForBagWithTwoElementsReturnsTwo()
    {
        self::assertEquals(
            2,
            $this->subject->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitAfterCallingNextForBagWithTwoElementsReturnsTwo()
    {
        $this->subject->rewind();
        $this->subject->next();

        self::assertEquals(
            2,
            $this->subject->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitForBagWithTwoMatchesElementsAndLimitOfOneReturnsTwo()
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

    public function testIsEmptyForEmptyBagReturnsTrue()
    {
        $bag = new TestingBag('1=2');

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testIsEmptyForBagWithOneElementReturnsFalse()
    {
        $bag = new TestingBag('uid = ' . $this->uidOfFirstRecord);

        self::assertFalse(
            $bag->isEmpty()
        );
    }

    public function testIsEmptyForBagWithTwoElementsReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isEmpty()
        );
    }
}
