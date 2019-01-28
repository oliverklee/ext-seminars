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
    private $subject = null;

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
        $subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $seminarUid,
                'entry_date' => 0,
                'place' => 0,
            ]
        );

        $this->subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingTimeSlot($subjectUid);
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
            $this->subject->isOk()
        );
    }

    /////////////////////////////////////
    // Tests for the time slot's sites.
    /////////////////////////////////////

    public function testPlaceIsInitiallyZero()
    {
        self::assertEquals(
            0,
            $this->subject->getPlace()
        );
    }

    public function testHasPlaceInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasPlace()
        );
    }

    public function testGetPlaceReturnsUidOfPlaceSetViaSetPlace()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites'
        );
        $this->subject->setPlace($placeUid);

        self::assertEquals(
            $placeUid,
            $this->subject->getPlace()
        );
    }

    public function testHasPlaceReturnsTrueIfPlaceIsSet()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites'
        );
        $this->subject->setPlace($placeUid);

        self::assertTrue(
            $this->subject->hasPlace()
        );
    }

    ////////////////////////////
    // Tests for getPlaceShort
    ////////////////////////////

    public function testGetPlaceShortReturnsWillBeAnnouncedForNoPlaces()
    {
        self::assertEquals(
            $this->subject->translate('message_willBeAnnounced'),
            $this->subject->getPlaceShort()
        );
    }

    public function testGetPlaceShortReturnsPlaceNameForOnePlace()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->subject->setPlace($placeUid);

        self::assertEquals(
            'a place',
            $this->subject->getPlaceShort()
        );
    }

    public function testGetPlaceShortThrowsExceptionForInexistentPlaceUid()
    {
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $this->setExpectedException(
            \Tx_Oelib_Exception_NotFound::class,
            'The related place with the UID ' . $placeUid . ' could not be found in the DB.'
        );

        $this->subject->setPlace($placeUid);
        $this->testingFramework->deleteRecord('tx_seminars_sites', $placeUid);

        $this->subject->getPlaceShort();
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

        $this->subject->setPlace($placeUid);

        $this->subject->getPlaceShort();
    }

    //////////////////////////////////////////
    // Tests for the time slot's entry date.
    //////////////////////////////////////////

    public function testHasEntryDateIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasEntryDate()
        );
    }

    public function testHasEntryDate()
    {
        $this->subject->setEntryDate(42);
        self::assertTrue(
            $this->subject->hasEntryDate()
        );
    }

    public function testGetEntryDateWithBeginDateOnSameDayAsEntryDateReturnsTime()
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->subject->setEntryDate($time);
        $this->subject->setBeginDate($time);
        $this->subject->setConfigurationValue('dateFormatYMD', '%d - %m - %Y');
        $this->subject->setConfigurationValue('timeFormat', '%H:%M');

        self::assertEquals(
            strftime('%H:%M', $time),
            $this->subject->getEntryDate()
        );
    }

    public function testGetEntryDateWithBeginDateOnDifferentDayAsEntryDateReturnsTimeAndDate()
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->subject->setEntryDate($time);
        $this->subject->setBeginDate($time + 86400);
        $this->subject->setConfigurationValue('dateFormatYMD', '%d - %m - %Y');
        $this->subject->setConfigurationValue('timeFormat', '%H:%M');

        self::assertEquals(
            strftime('%d - %m - %Y %H:%M', $time),
            $this->subject->getEntryDate()
        );
    }
}
