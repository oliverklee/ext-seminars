<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_OldModel_TimeSlotTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingTimeSlot
     */
    private $fixture = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $fixtureUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $seminarUid,
                'entry_date' => 0,
                'place' => 0,
            ]
        );

        $this->fixture = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingTimeSlot($fixtureUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////////////////////
    // Tests for creating time slot objects.
    //////////////////////////////////////////

    public function testCreateFromUid()
    {
        self::assertTrue(
            $this->fixture->isOk()
        );
    }

    /////////////////////////////////////
    // Tests for the time slot's sites.
    /////////////////////////////////////

    public function testPlaceIsInitiallyZero()
    {
        self::assertEquals(
            0,
            $this->fixture->getPlace()
        );
    }

    public function testHasPlaceInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->fixture->hasPlace()
        );
    }

    public function testGetPlaceReturnsUidOfPlaceSetViaSetPlace()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites'
        );
        $this->fixture->setPlace($placeUid);

        self::assertEquals(
            $placeUid,
            $this->fixture->getPlace()
        );
    }

    public function testHasPlaceReturnsTrueIfPlaceIsSet()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites'
        );
        $this->fixture->setPlace($placeUid);

        self::assertTrue(
            $this->fixture->hasPlace()
        );
    }

    ////////////////////////////
    // Tests for getPlaceShort
    ////////////////////////////

    public function testGetPlaceShortReturnsWillBeAnnouncedForNoPlaces()
    {
        self::assertEquals(
            $this->fixture->translate('message_willBeAnnounced'),
            $this->fixture->getPlaceShort()
        );
    }

    public function testGetPlaceShortReturnsPlaceNameForOnePlace()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->fixture->setPlace($placeUid);

        self::assertEquals(
            'a place',
            $this->fixture->getPlaceShort()
        );
    }

    public function testGetPlaceShortThrowsExceptionForInexistentPlaceUid()
    {
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $this->setExpectedException(
            \Tx_Oelib_Exception_NotFound::class,
            'The related place with the UID ' . $placeUid . ' could not be found in the DB.'
        );

        $this->fixture->setPlace($placeUid);
        $this->testingFramework->deleteRecord('tx_seminars_sites', $placeUid);

        $this->fixture->getPlaceShort();
    }

    public function testGetPlaceShortThrowsExceptionForDeletedPlace()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['deleted' => 1]
        );
        $this->setExpectedException(
            \Tx_Oelib_Exception_NotFound::class,
            'The related place with the UID ' . $placeUid . ' could not be found in the DB.'
        );

        $this->fixture->setPlace($placeUid);

        $this->fixture->getPlaceShort();
    }

    //////////////////////////////////////////
    // Tests for the time slot's entry date.
    //////////////////////////////////////////

    public function testHasEntryDateIsInitiallyFalse()
    {
        self::assertFalse(
            $this->fixture->hasEntryDate()
        );
    }

    public function testHasEntryDate()
    {
        $this->fixture->setEntryDate(42);
        self::assertTrue(
            $this->fixture->hasEntryDate()
        );
    }

    public function testGetEntryDateWithBeginDateOnSameDayAsEntryDateReturnsTime()
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->fixture->setEntryDate($time);
        $this->fixture->setBeginDate($time);
        $this->fixture->setConfigurationValue('dateFormatYMD', '%d - %m - %Y');
        $this->fixture->setConfigurationValue('timeFormat', '%H:%M');

        self::assertEquals(
            strftime('%H:%M', $time),
            $this->fixture->getEntryDate()
        );
    }

    public function testGetEntryDateWithBeginDateOnDifferentDayAsEntryDateReturnsTimeAndDate()
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->fixture->setEntryDate($time);
        $this->fixture->setBeginDate($time + 86400);
        $this->fixture->setConfigurationValue('dateFormatYMD', '%d - %m - %Y');
        $this->fixture->setConfigurationValue('timeFormat', '%H:%M');

        self::assertEquals(
            strftime('%d - %m - %Y %H:%M', $time),
            $this->fixture->getEntryDate()
        );
    }
}
