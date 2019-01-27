<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Bag_AbstractTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing
     */
    private $fixture;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

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
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->uidOfFirstRecord = $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'test 1']
        );
        $this->uidOfSecondRecord = $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'test 2']
        );

        $this->fixture = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('is_dummy_record=1');
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
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('1 = 2');

        self::assertEquals(
            '',
            $bag->getUids()
        );
    }

    public function testBagCanHaveOneUid()
    {
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

        self::assertEquals(
            (string)$this->uidOfFirstRecord,
            $bag->getUids()
        );
    }

    public function testBagCanHaveTwoUids()
    {
        self::assertEquals(
            $this->uidOfFirstRecord . ',' . $this->uidOfSecondRecord,
            $this->fixture->getUids()
        );
    }

    public function testBagSortsByUidByDefault()
    {
        self::assertEquals(
            $this->uidOfFirstRecord,
            $this->fixture->current()->getUid()
        );

        self::assertEquals(
            $this->uidOfSecondRecord,
            $this->fixture->next()->getUid()
        );
    }

    ///////////////////////////
    // Tests concerning count
    ///////////////////////////

    public function testCountForEmptyBagReturnsZero()
    {
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('1 = 2');

        self::assertEquals(
            0,
            $bag->count()
        );
    }

    public function testCountForBagWithOneElementReturnsOne()
    {
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('uid=' . $this->uidOfFirstRecord);

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    public function testCountForBagWithTwoElementsReturnsTwo()
    {
        self::assertEquals(
            2,
            $this->fixture->count()
        );
    }

    public function testCountAfterCallingNextForBagWithTwoElementsReturnsTwo()
    {
        $this->fixture->rewind();
        $this->fixture->next();

        self::assertEquals(
            2,
            $this->fixture->count()
        );
    }

    public function testCountForBagWithTwoMatchesElementsAndLimitOfOneReturnsOne()
    {
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('is_dummy_record = 1', '', '', '', 1);

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
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('1 = 2');

        self::assertEquals(
            0,
            $bag->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitForBagWithOneElementReturnsOne()
    {
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

        self::assertEquals(
            1,
            $bag->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitForBagWithTwoElementsReturnsTwo()
    {
        self::assertEquals(
            2,
            $this->fixture->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitAfterCallingNextForBagWithTwoElementsReturnsTwo()
    {
        $this->fixture->rewind();
        $this->fixture->next();

        self::assertEquals(
            2,
            $this->fixture->countWithoutLimit()
        );
    }

    public function testCountWithoutLimitForBagWithTwoMatchesElementsAndLimitOfOneReturnsTwo()
    {
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('is_dummy_record = 1', '', '', '', 1);

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
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('1=2');

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    public function testIsEmptyForBagWithOneElementReturnsFalse()
    {
        $bag = new \Tx_Seminars_Tests_Unit_Fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

        self::assertFalse(
            $bag->isEmpty()
        );
    }

    public function testIsEmptyForBagWithTwoElementsReturnsFalse()
    {
        self::assertFalse(
            $this->fixture->isEmpty()
        );
    }
}
