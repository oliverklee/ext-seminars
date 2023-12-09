<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingLegacyTimeSlot;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyTimeSlot
 */
final class LegacyTimeSlotTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingLegacyTimeSlot
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->subject = new TestingLegacyTimeSlot($subjectUid);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    //////////////////////////////////////////
    // Tests for creating time slot objects.
    //////////////////////////////////////////

    /**
     * @test
     */
    public function createFromUid(): void
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
    public function placeIsInitiallyZero(): void
    {
        self::assertEquals(
            0,
            $this->subject->getPlace()
        );
    }

    /**
     * @test
     */
    public function hasPlaceInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasPlace()
        );
    }

    /**
     * @test
     */
    public function getPlaceReturnsUidOfPlaceSetViaSetPlace(): void
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
    public function hasPlaceReturnsTrueIfPlaceIsSet(): void
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
    public function getPlaceShortReturnsWillBeAnnouncedForNoPlaces(): void
    {
        self::assertSame(
            $this->translate('message_willBeAnnounced'),
            $this->subject->getPlaceShort()
        );
    }

    /**
     * @test
     */
    public function getPlaceShortReturnsPlaceNameForOnePlace(): void
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
    public function getPlaceShortForInexistentPlaceUidReturnsEmptyString(): void
    {
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $this->subject->setPlace($placeUid);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_seminars_attendances');
        $connection->delete('tx_seminars_sites', ['uid' => $placeUid]);

        self::assertSame('', $this->subject->getPlaceShort());
    }

    /**
     * @test
     */
    public function getPlaceShortForDeletedPlaceReturnsEmptyString(): void
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
    public function hasEntryDateIsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasEntryDate()
        );
    }

    /**
     * @test
     */
    public function hasEntryDate(): void
    {
        $this->subject->setEntryDate(42);
        self::assertTrue(
            $this->subject->hasEntryDate()
        );
    }

    /**
     * @test
     */
    public function getEntryDateWithBeginDateOnSameDayAsEntryDateReturnsTime(): void
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->subject->setEntryDate($time);
        $this->subject->setBeginDate($time);
        $this->configuration->setAsString('dateFormatYMD', '%d - %m - %Y');
        $this->configuration->setAsString('timeFormat', '%H:%M');

        self::assertSame(\date('H:i', $time), $this->subject->getEntryDate());
    }

    /**
     * @test
     */
    public function getEntryDateWithBeginDateOnDifferentDayAsEntryDateReturnsTimeAndDate(): void
    {
        // chosen randomly 2001-01-01 13:01
        $time = 978354060;
        $this->subject->setEntryDate($time);
        $this->subject->setBeginDate($time + 86400);
        $this->configuration->setAsString('dateFormatYMD', '%d - %m - %Y');
        $this->configuration->setAsString('timeFormat', '%H:%M');

        self::assertSame(\date('d - m - Y H:i', $time), $this->subject->getEntryDate());
    }
}
