<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingTimeSlot;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \Tx_Seminars_OldModel_TimeSlot
 */
final class TimeSlotTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var TestingTimeSlot
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->configuration = new DummyConfiguration([]);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

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

        $this->subject = new TestingTimeSlot($subjectUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////////////////////
    // Tests for creating time slot objects.
    //////////////////////////////////////////

    /**
     * @test
     */
    public function createFromUid()
    {
        self::assertTrue(
            $this->subject->isOk()
        );
    }

    /////////////////////////////////////
    // Tests for the time slot's sites.
    /////////////////////////////////////

    /**
     * @test
     */
    public function placeIsInitiallyZero()
    {
        self::assertEquals(
            0,
            $this->subject->getPlace()
        );
    }

    /**
     * @test
     */
    public function hasPlaceInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasPlace()
        );
    }

    /**
     * @test
     */
    public function getPlaceReturnsUidOfPlaceSetViaSetPlace()
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

    /**
     * @test
     */
    public function hasPlaceReturnsTrueIfPlaceIsSet()
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

    /**
     * @test
     */
    public function getPlaceShortReturnsWillBeAnnouncedForNoPlaces()
    {
        self::assertSame(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getPlaceShort()
        );
    }

    /**
     * @test
     */
    public function getPlaceShortReturnsPlaceNameForOnePlace()
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

    /**
     * @test
     */
    public function getPlaceShortForInexistentPlaceUidReturnsEmptyString()
    {
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $this->subject->setPlace($placeUid);
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionForTable('tx_seminars_attendances');
        $connection->delete('tx_seminars_sites', ['uid' => $placeUid]);

        self::assertSame('', $this->subject->getPlaceShort());
    }

    /**
     * @test
     */
    public function getPlaceShortForDeletedPlaceReturnsEmptyString()
    {
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites', ['deleted' => 1]);

        $this->subject->setPlace($placeUid);

        self::assertSame('', $this->subject->getPlaceShort());
    }

    //////////////////////////////////////////
    // Tests for the time slot's entry date.
    //////////////////////////////////////////

    /**
     * @test
     */
    public function hasEntryDateIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasEntryDate()
        );
    }

    /**
     * @test
     */
    public function hasEntryDate()
    {
        $this->subject->setEntryDate(42);
        self::assertTrue(
            $this->subject->hasEntryDate()
        );
    }

    /**
     * @test
     */
    public function getEntryDateWithBeginDateOnSameDayAsEntryDateReturnsTime()
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->subject->setEntryDate($time);
        $this->subject->setBeginDate($time);
        $this->configuration->setAsString('dateFormatYMD', '%d - %m - %Y');
        $this->configuration->setAsString('timeFormat', '%H:%M');

        self::assertEquals(
            strftime('%H:%M', $time),
            $this->subject->getEntryDate()
        );
    }

    /**
     * @test
     */
    public function getEntryDateWithBeginDateOnDifferentDayAsEntryDateReturnsTimeAndDate()
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->subject->setEntryDate($time);
        $this->subject->setBeginDate($time + 86400);
        $this->configuration->setAsString('dateFormatYMD', '%d - %m - %Y');
        $this->configuration->setAsString('timeFormat', '%H:%M');

        self::assertEquals(
            strftime('%d - %m - %Y %H:%M', $time),
            $this->subject->getEntryDate()
        );
    }
}
