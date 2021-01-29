<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingEvent;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var TestingEvent
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int
     */
    private $beginDate = 0;

    /**
     * @var int
     */
    private $unregistrationDeadline = 0;

    /**
     * @var int
     */
    private $now = 1524751343;

    /**
     * @var \Tx_Seminars_FrontEnd_DefaultController
     */
    private $pi1 = null;

    /**
     * @var int
     */
    private $placeRelationSorting = 1;

    /** @var ConnectionPool */
    private $connectionPool = null;

    protected function setUp()
    {
        // Make sure that the test results do not depend on the machine's PHP time zone.
        \date_default_timezone_set('UTC');

        $GLOBALS['SIM_EXEC_TIME'] = $this->now;
        $this->beginDate = ($this->now + Time::SECONDS_PER_WEEK);
        $this->unregistrationDeadline = ($this->now + Time::SECONDS_PER_WEEK);

        $this->testingFramework = new TestingFramework('tx_seminars');

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'deadline_unregistration' => $this->unregistrationDeadline,
                'attendees_min' => 5,
                'attendees_max' => 10,
                'object_type' => 0,
                'queue_size' => 0,
                'needs_registration' => 1,
            ]
        );

        $this->subject = new TestingEvent($uid);
        $this->subject->overrideConfiguration(
            [
                'dateFormatYMD' => '%d.%m.%Y',
                'timeFormat' => '%H:%M',
                'showTimeOfUnregistrationDeadline' => 0,
                'unregistrationDeadlineDaysBeforeBeginDate' => 0,
            ]
        );

        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    /*
     * Utility functions
     */

    /**
     * Creates a fake front end and a pi1 instance in $this->pi1.
     *
     * @param int $detailPageUid UID of the detail view page
     *
     * @return void
     */
    private function createPi1(int $detailPageUid = 0)
    {
        $this->testingFramework->createFakeFrontEnd();

        $this->pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $this->pi1->init(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'detailPID' => $detailPageUid,
            ]
        );
        $this->pi1->getTemplateCode();
    }

    /**
     * Inserts a place record into the database and creates a relation to it
     * from the fixture.
     *
     * @param array $placeData data of the place to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addPlaceRelation(array $placeData = []): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            $placeData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $this->subject->getUid(),
            $uid,
            $this->placeRelationSorting
        );
        $this->placeRelationSorting++;
        $this->subject->setNumberOfPlaces(
            $this->subject->getNumberOfPlaces() + 1
        );

        return $uid;
    }

    /**
     * Inserts a target group record into the database and creates a relation to
     * it from the fixture.
     *
     * @param array $targetGroupData data of the target group to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addTargetGroupRelation(array $targetGroupData = []): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            $targetGroupData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_target_groups_mm',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfTargetGroups(
            $this->subject->getNumberOfTargetGroups() + 1
        );

        return $uid;
    }

    /**
     * Inserts a payment method record into the database and creates a relation
     * to it from the fixture.
     *
     * @param array $paymentMethodData data of the payment method to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addPaymentMethodRelation(
        array $paymentMethodData = []
    ): int {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            $paymentMethodData
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfPaymentMethods(
            $this->subject->getNumberOfPaymentMethods() + 1
        );

        return $uid;
    }

    /**
     * Inserts an organizer record into the database and creates a relation to
     * it from the fixture as a organizing partner.
     *
     * @param array $organizerData data of the organizer to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addOrganizingPartnerRelation(
        array $organizerData = []
    ): int {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            $organizerData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizing_partners_mm',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfOrganizingPartners(
            $this->subject->getNumberOfOrganizingPartners() + 1
        );

        return $uid;
    }

    /**
     * Inserts a category record into the database and creates a relation to it
     * from the fixture.
     *
     * @param array $categoryData data of the category to add, may be empty
     * @param int $sorting the sorting index of the category to add, must be >= 0
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addCategoryRelation(
        array $categoryData = [],
        int $sorting = 0
    ): int {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            $categoryData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $this->subject->getUid(),
            $uid,
            $sorting
        );
        $this->subject->setNumberOfCategories(
            $this->subject->getNumberOfCategories() + 1
        );

        return $uid;
    }

    /**
     * Inserts a organizer record into the database and creates a relation to it
     * from the fixture.
     *
     * @param array $organizerData data of the organizer to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addOrganizerRelation(array $organizerData = []): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            $organizerData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfOrganizers(
            $this->subject->getNumberOfOrganizers() + 1
        );

        return $uid;
    }

    /**
     * Inserts a speaker record into the database and creates a relation to it
     * from the fixture.
     *
     * @param array $speakerData data of the speaker to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addSpeakerRelation(array $speakerData): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            $speakerData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfSpeakers(
            $this->subject->getNumberOfSpeakers() + 1
        );

        return $uid;
    }

    /**
     * Inserts a speaker record into the database and creates a relation to it
     * from the fixture as partner.
     *
     * @param array $speakerData data of the speaker to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addPartnerRelation(array $speakerData): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            $speakerData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm_partners',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfPartners(
            $this->subject->getNumberOfPartners() + 1
        );

        return $uid;
    }

    /**
     * Inserts a speaker record into the database and creates a relation to it
     * from the fixture as tutor.
     *
     * @param array $speakerData data of the speaker to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addTutorRelation(array $speakerData): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            $speakerData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm_tutors',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfTutors(
            $this->subject->getNumberOfTutors() + 1
        );

        return $uid;
    }

    /**
     * Inserts a speaker record into the database and creates a relation to it
     * from the fixture as leader.
     *
     * @param array $speakerData data of the speaker to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addLeaderRelation(array $speakerData): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            $speakerData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm_leaders',
            $this->subject->getUid(),
            $uid
        );
        $this->subject->setNumberOfLeaders(
            $this->subject->getNumberOfLeaders() + 1
        );

        return $uid;
    }

    /**
     * Inserts an event type record into the database and creates a relation to
     * it from the fixture.
     *
     * @param array $eventTypeData data of the event type to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addEventTypeRelation(array $eventTypeData): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            $eventTypeData
        );

        $this->subject->setEventType($uid);

        return $uid;
    }

    /*
     * Tests for the utility functions
     */

    /**
     * @test
     */
    public function createPi1CreatesFakeFrontEnd()
    {
        $GLOBALS['TSFE'] = null;

        $this->createPi1();

        self::assertNotNull($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function createPi1CreatesPi1Instance()
    {
        $this->pi1 = null;

        $this->createPi1();

        self::assertInstanceOf(\Tx_Seminars_FrontEnd_DefaultController::class, $this->pi1);
    }

    /**
     * @test
     */
    public function addPlaceRelationReturnsUid()
    {
        $uid = $this->addPlaceRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addPlaceRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addPlaceRelation(),
            $this->addPlaceRelation()
        );
    }

    /**
     * @test
     */
    public function addPlaceRelationIncreasesTheNumberOfPlaces()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfPlaces()
        );

        $this->addPlaceRelation();
        self::assertSame(
            1,
            $this->subject->getNumberOfPlaces()
        );

        $this->addPlaceRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfPlaces()
        );
    }

    /**
     * @test
     */
    public function addPlaceRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_place_mm');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_place_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addPlaceRelation();
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_place_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addPlaceRelation();
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_place_mm', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationReturnsUid()
    {
        $uid = $this->addCategoryRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addCategoryRelation(),
            $this->addCategoryRelation()
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationIncreasesTheNumberOfCategories()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfCategories()
        );

        $this->addCategoryRelation();
        self::assertSame(
            1,
            $this->subject->getNumberOfCategories()
        );

        $this->addCategoryRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfCategories()
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_categories_mm');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_categories_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addCategoryRelation();
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_categories_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addCategoryRelation();
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_categories_mm', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationCanSetSortingInRelationTable()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_categories_mm');

        $this->addCategoryRelation([], 42);

        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_seminars_categories_mm',
                ['uid_local' => $this->subject->getUid(), 'sorting' => 42]
            )
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationReturnsUid()
    {
        self::assertTrue(
            $this->addTargetGroupRelation() > 0
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addTargetGroupRelation(),
            $this->addTargetGroupRelation()
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationIncreasesTheNumberOfTargetGroups()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfTargetGroups()
        );

        $this->addTargetGroupRelation();
        self::assertSame(
            1,
            $this->subject->getNumberOfTargetGroups()
        );

        $this->addTargetGroupRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfTargetGroups()
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_target_groups_mm');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_target_groups_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addTargetGroupRelation();
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_target_groups_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addTargetGroupRelation();
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_target_groups_mm', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addPaymentMethodRelationReturnsUid()
    {
        $uid = $this->addPaymentMethodRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addPaymentMethodRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addPaymentMethodRelation(),
            $this->addPaymentMethodRelation()
        );
    }

    /**
     * @test
     */
    public function addPaymentMethodRelationIncreasesTheNumberOfPaymentMethods()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfPaymentMethods()
        );

        $this->addPaymentMethodRelation();
        self::assertSame(
            1,
            $this->subject->getNumberOfPaymentMethods()
        );

        $this->addPaymentMethodRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function addOrganizingPartnerRelationReturnsUid()
    {
        $uid = $this->addOrganizingPartnerRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addOrganizingPartnerRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addOrganizingPartnerRelation(),
            $this->addOrganizingPartnerRelation()
        );
    }

    /**
     * @test
     */
    public function addOrganizingPartnerRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_organizing_partners_mm');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_organizing_partners_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addOrganizingPartnerRelation();
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_organizing_partners_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addOrganizingPartnerRelation();
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_organizing_partners_mm', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addOrganizerRelationReturnsUid()
    {
        $uid = $this->addOrganizerRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addOrganizerRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addOrganizerRelation(),
            $this->addOrganizerRelation()
        );
    }

    /**
     * @test
     */
    public function addOrganizerRelationIncreasesTheNumberOfOrganizers()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfOrganizers()
        );

        $this->addOrganizerRelation();
        self::assertSame(
            1,
            $this->subject->getNumberOfOrganizers()
        );

        $this->addOrganizerRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfOrganizers()
        );
    }

    /**
     * @test
     */
    public function addSpeakerRelationReturnsUid()
    {
        $uid = $this->addSpeakerRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addSpeakerRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addSpeakerRelation([]),
            $this->addSpeakerRelation([])
        );
    }

    /**
     * @test
     */
    public function addSpeakerRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_speakers_mm');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addSpeakerRelation([]);
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm', ['uid_local' => $this->subject->getUid()])
        );

        $this->addSpeakerRelation([]);
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addPartnerRelationReturnsUid()
    {
        $uid = $this->addPartnerRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addPartnerRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addPartnerRelation([]),
            $this->addPartnerRelation([])
        );
    }

    /**
     * @test
     */
    public function addPartnerRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_speakers_mm_partners');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_partners', ['uid_local' => $this->subject->getUid()])
        );

        $this->addPartnerRelation([]);
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_partners', ['uid_local' => $this->subject->getUid()])
        );

        $this->addPartnerRelation([]);
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_partners', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addTutorRelationReturnsUid()
    {
        $uid = $this->addTutorRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addTutorRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addTutorRelation([]),
            $this->addTutorRelation([])
        );
    }

    /**
     * @test
     */
    public function addTutorRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_speakers_mm_tutors');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_tutors', ['uid_local' => $this->subject->getUid()])
        );

        $this->addTutorRelation([]);
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_tutors', ['uid_local' => $this->subject->getUid()])
        );

        $this->addTutorRelation([]);
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_tutors', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addLeaderRelationReturnsUid()
    {
        $uid = $this->addLeaderRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addLeaderRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addLeaderRelation([]),
            $this->addLeaderRelation([])
        );
    }

    /**
     * @test
     */
    public function addLeaderRelationCreatesRelations()
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_speakers_mm_leaders');

        self::assertSame(
            0,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_leaders', ['uid_local' => $this->subject->getUid()])
        );

        $this->addLeaderRelation([]);
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_leaders', ['uid_local' => $this->subject->getUid()])
        );

        $this->addLeaderRelation([]);
        self::assertSame(
            2,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm_leaders', ['uid_local' => $this->subject->getUid()])
        );
    }

    /**
     * @test
     */
    public function addEventTypeRelationReturnsUid()
    {
        $uid = $this->addEventTypeRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addEventTypeRelationCreatesNewUids()
    {
        self::assertNotSame(
            $this->addLeaderRelation([]),
            $this->addLeaderRelation([])
        );
    }

    /*
     * Tests for some basic functionality
     */

    /**
     * @test
     */
    public function isOk()
    {
        self::assertTrue(
            $this->subject->isOk()
        );
    }

    /*
     * Tests concerning getTitle
     */

    /**
     * @test
     */
    public function getTitleForSingleEventReturnsTitle()
    {
        self::assertSame(
            'a test event',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForTopicReturnsTitle()
    {
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'a test topic',
            ]
        );
        $topic = new \Tx_Seminars_OldModel_Event($topicRecordUid);

        self::assertSame(
            'a test topic',
            $topic->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForDateReturnsTopicTitle()
    {
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'a test topic',
            ]
        );
        $dateRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicRecordUid,
                'title' => 'a test date',
            ]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateRecordUid);

        self::assertSame(
            'a test topic',
            $date->getTitle()
        );
    }

    /*
     * Tests regarding the ability to register for an event
     */

    /**
     * @test
     */
    public function canSomebodyRegisterIsTrueForEventWithFutureDate()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsTrueForEventWithFutureDateAndRegistrationWithoutDateActivated()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForPastEvent()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForPastEventWithRegistrationWithoutDateActivated()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForCurrentlyRunningEvent()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForCurrentlyRunningEventWithRegistrationWithoutDateActivated()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForEventWithoutDate()
    {
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsTrueForEventWithoutDateAndRegistrationWithoutDateActivated()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithUnlimitedVacanciesReturnsTrue()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        $this->subject->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForCancelledEventReturnsFalse()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);

        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithoutNeedeRegistrationReturnsFalse()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setNeedsRegistration(false);

        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForFullyBookedEventReturnsFalse()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(10);
        $this->subject->setNumberOfAttendances(10);

        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithRegistrationQueueAndNoRegularVacanciesReturnsTrue()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(10);
        $this->subject->setNumberOfAttendances(10);
        $this->subject->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithRegistrationQueueAndRegularVacanciesReturnsTrue()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(10);
        $this->subject->setNumberOfAttendances(5);
        $this->subject->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithRegistrationBeginInFutureReturnsFalse()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] + 20
        );

        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithRegistrationBeginInPastReturnsTrue()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] - 20
        );

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithoutRegistrationBeginReturnsTrue()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(0);

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /*
     * Tests concerning canSomebodyRegisterMessage
     */

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithFutureDateReturnsEmptyString()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

        self::assertSame(
            '',
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForPastEventReturnsSeminarRegistrationClosedMessage()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);

        self::assertSame(
            $this->getLanguageService()->getLL('message_seminarRegistrationIsClosed'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForPastEventWithRegistrationWithoutDateActivatedReturnsRegistrationDeadlineOverMessage()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);

        self::assertSame(
            $this->getLanguageService()->getLL('message_seminarRegistrationIsClosed'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForCurrentlyRunningEventReturnsSeminarRegistrationClosesMessage()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

        self::assertSame(
            $this->getLanguageService()->getLL('message_seminarRegistrationIsClosed'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForCurrentlyRunningEventWithRegistrationWithoutDateActivatedReturnsSeminarRegistrationClosesMessage()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

        self::assertSame(
            $this->getLanguageService()->getLL('message_seminarRegistrationIsClosed'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithoutDateReturnsNoDateMessage()
    {
        self::assertSame(
            $this->getLanguageService()->getLL('message_noDate'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithoutDateAndRegistrationWithoutDateActivatedReturnsEmptyString()
    {
        // Activates the configuration switch "canRegisterForEventsWithoutDate".
        $this->subject->setAllowRegistrationForEventsWithoutDate(1);
        $this->subject->setBeginDate(0);
        $this->subject->setRegistrationDeadline(0);

        self::assertSame(
            '',
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithUnlimitedVacanviesReturnsEmptyString()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        $this->subject->setUnlimitedVacancies();

        self::assertSame(
            '',
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForCancelledEventReturnsSeminarCancelledMessage()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertSame(
            $this->getLanguageService()->getLL('message_seminarCancelled'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithoutNeedeRegistrationReturnsNoRegistrationNecessaryMessage()
    {
        $this->subject->setNeedsRegistration(false);

        self::assertSame(
            $this->getLanguageService()->getLL('message_noRegistrationNecessary'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForFullyBookedEventReturnsNoVacanciesMessage()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(10);
        $this->subject->setNumberOfAttendances(10);

        self::assertSame(
            $this->getLanguageService()->getLL('message_noVacancies'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForFullyBookedEventWithRegistrationQueueReturnsEmptyString()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(10);
        $this->subject->setNumberOfAttendances(10);
        $this->subject->setRegistrationQueue(true);

        self::assertSame(
            '',
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithRegistrationBeginInFutureReturnsRegistrationOpensOnMessage()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] + 20
        );

        self::assertSame(
            sprintf(
                $this->getLanguageService()->getLL('message_registrationOpensOn'),
                $this->subject->getRegistrationBegin()
            ),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithRegistrationBeginInPastReturnsEmptyString()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] - 20
        );

        self::assertSame(
            '',
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithoutRegistrationBeginReturnsEmptyString()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(0);

        self::assertSame(
            '',
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /*
     * Tests regarding the language of an event
     */

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithValidLanguage()
    {
        self::assertSame(
            'Deutsch',
            $this->subject->getLanguageNameFromIsoCode('de')
        );
    }

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithInvalidLanguage()
    {
        self::assertSame(
            '',
            $this->subject->getLanguageNameFromIsoCode('xy')
        );
    }

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithVeryInvalidLanguage()
    {
        self::assertSame(
            '',
            $this->subject->getLanguageNameFromIsoCode('foobar')
        );
    }

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithEmptyLanguage()
    {
        self::assertSame(
            '',
            $this->subject->getLanguageNameFromIsoCode('')
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithLanguageReturnsTrue()
    {
        $this->subject->setLanguage('de');
        self::assertTrue(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithNoLanguageReturnsFalse()
    {
        $this->subject->setLanguage('');
        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageNameWithDefaultLanguageOnSingleEvent()
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'Deutsch',
            $this->subject->getLanguageName()
        );
    }

    /**
     * @test
     */
    public function getLanguageNameWithValidLanguageOnSingleEvent()
    {
        $this->subject->setLanguage('en');
        self::assertSame(
            'English',
            $this->subject->getLanguageName()
        );
    }

    /**
     * @test
     */
    public function getLanguageNameWithInvalidLanguageOnSingleEvent()
    {
        $this->subject->setLanguage('xy');
        self::assertSame(
            '',
            $this->subject->getLanguageName()
        );
    }

    /**
     * @test
     */
    public function getLanguageNameWithNoLanguageOnSingleEvent()
    {
        $this->subject->setLanguage('');
        self::assertSame(
            '',
            $this->subject->getLanguageName()
        );
    }

    /**
     * @test
     */
    public function getLanguageNameOnDateRecord()
    {
        // This was an issue with bug #1518 and #1517.
        // The method getLanguage() needs to return the language from the date
        // record instead of the topic record.
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => 'de']
        );

        $dateRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicRecordUid,
                'language' => 'it',
            ]
        );

        $seminar = new \Tx_Seminars_OldModel_Event($dateRecordUid);

        self::assertSame(
            'Italiano',
            $seminar->getLanguageName()
        );
    }

    /**
     * @test
     */
    public function getLanguageOnSingleRecordThatWasADateRecord()
    {
        // This test comes from bug 1518 and covers the following situation:
        // We have an event record that has the topic field set as it was a
        // date record. But then it was switched to be a single event record.
        // In that case, the language from the single event record must be
        // returned, not the one from the referenced topic record.

        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => 'de']
        );

        $singleRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'topic' => $topicRecordUid,
                'language' => 'it',
            ]
        );

        $seminar = new \Tx_Seminars_OldModel_Event($singleRecordUid);

        self::assertSame(
            'Italiano',
            $seminar->getLanguageName()
        );
    }

    /*
     * Tests regarding the registration.
     */

    /**
     * @test
     */
    public function needsRegistrationForNeedsRegistrationTrueReturnsTrue()
    {
        $this->subject->setNeedsRegistration(true);

        self::assertTrue(
            $this->subject->needsRegistration()
        );
    }

    /**
     * @test
     */
    public function needsRegistrationForNeedsRegistrationFalseReturnsFalse()
    {
        $this->subject->setNeedsRegistration(false);

        self::assertFalse(
            $this->subject->needsRegistration()
        );
    }

    /*
     * Tests concerning hasUnlimitedVacancies
     */

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForNeedsRegistrationTrueAndMaxAttendeesZeroReturnsTrue()
    {
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(0);

        self::assertTrue(
            $this->subject->hasUnlimitedVacancies()
        );
    }

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForNeedsRegistrationTrueAndMaxAttendeesOneReturnsFalse()
    {
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(1);

        self::assertFalse(
            $this->subject->hasUnlimitedVacancies()
        );
    }

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForNeedsRegistrationFalseAndMaxAttendeesZeroReturnsFalse()
    {
        $this->subject->setNeedsRegistration(false);
        $this->subject->setAttendancesMax(0);

        self::assertFalse(
            $this->subject->hasUnlimitedVacancies()
        );
    }

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForNeedsRegistrationFalseAndMaxAttendeesOneReturnsFalse()
    {
        $this->subject->setNeedsRegistration(false);
        $this->subject->setAttendancesMax(1);

        self::assertFalse(
            $this->subject->hasUnlimitedVacancies()
        );
    }

    /*
     * Tests concerning isFull
     */

    /**
     * @test
     */
    public function isFullForUnlimitedVacanciesAndZeroAttendancesReturnsFalse()
    {
        $this->subject->setUnlimitedVacancies();
        $this->subject->setNumberOfAttendances(0);

        self::assertFalse(
            $this->subject->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForUnlimitedVacanciesAndOneAttendanceReturnsFalse()
    {
        $this->subject->setUnlimitedVacancies();
        $this->subject->setNumberOfAttendances(1);

        self::assertFalse(
            $this->subject->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForOneVacancyAndNoAttendancesReturnsFalse()
    {
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(1);
        $this->subject->setNumberOfAttendances(0);

        self::assertFalse(
            $this->subject->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForOneVacancyAndOneAttendanceReturnsTrue()
    {
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(1);
        $this->subject->setNumberOfAttendances(1);

        self::assertTrue(
            $this->subject->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForTwoVacanciesAndOneAttendanceReturnsFalse()
    {
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(2);
        $this->subject->setNumberOfAttendances(1);

        self::assertFalse(
            $this->subject->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForTwoVacanciesAndTwoAttendancesReturnsTrue()
    {
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(2);
        $this->subject->setNumberOfAttendances(2);

        self::assertTrue(
            $this->subject->isFull()
        );
    }

    /*
     * Tests regarding the unregistration and the queue
     */

    /**
     * @test
     */
    public function getUnregistrationDeadlineAsTimestampForNonZero()
    {
        $this->subject->setUnregistrationDeadline($this->unregistrationDeadline);

        self::assertSame(
            $this->unregistrationDeadline,
            $this->subject->getUnregistrationDeadlineAsTimestamp()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineAsTimestampForZero()
    {
        $this->subject->setUnregistrationDeadline(0);

        self::assertSame(
            0,
            $this->subject->getUnregistrationDeadlineAsTimestamp()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineWithoutTimeForNonZero()
    {
        $this->subject->setUnregistrationDeadline(1893488400);

        self::assertSame(
            '01.01.2030',
            $this->subject->getUnregistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function getNonUnregistrationDeadlineWithTimeForZero()
    {
        $this->subject->setUnregistrationDeadline(1893488400);
        $this->subject->setShowTimeOfUnregistrationDeadline(1);

        self::assertSame('01.01.2030 09:00', $this->subject->getUnregistrationDeadline());
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineIsEmptyForZero()
    {
        $this->subject->setUnregistrationDeadline(0);

        self::assertSame(
            '',
            $this->subject->getUnregistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineIsTrueForNonZeroDeadline()
    {
        $this->subject->setUnregistrationDeadline($this->unregistrationDeadline);

        self::assertTrue(
            $this->subject->hasUnregistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineIsFalseForZeroDeadline()
    {
        $this->subject->setUnregistrationDeadline(0);

        self::assertFalse(
            $this->subject->hasUnregistrationDeadline()
        );
    }

    /*
     * Tests concerning isUnregistrationPossible()
     */

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutDeadlineReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(0);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithNoBeginDateAndNoDeadlineReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(0);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithGlobalDeadlineInFutureReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithGlobalDeadlineInPastReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(5);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithGlobalDeadlineReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithFutureEventDeadlineReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(0);
        $this->subject->setUnregistrationDeadline(
            $this->now + Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithPastEventDeadlineReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(0);
        $this->subject->setUnregistrationDeadline(
            $this->now - Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithFutureEventDeadlineReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(0);
        $this->subject->setUnregistrationDeadline(
            $this->now + Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithPastEventDeadlineReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(0);
        $this->subject->setUnregistrationDeadline(
            $this->now - Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithBothDeadlinesInFutureReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithBothDeadlinesInPastReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(2);
        $this->subject->setAttendancesMax(10);
        $this->subject->setUnregistrationDeadline(
            $this->now - Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_DAY);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInFutureReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInPastReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);
        $this->subject->setUnregistrationDeadline(
            $this->now - Time::SECONDS_PER_DAY
        );

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithPassedEventUnregistrationDeadlineReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setBeginDate($this->now + 2 * Time::SECONDS_PER_DAY);
        $this->subject->setUnregistrationDeadline(
            $this->now - Time::SECONDS_PER_DAY
        );
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithNonZeroAttendancesMaxReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setAttendancesMax(10);
        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleForNeedsRegistrationFalseReturnsFalse()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setNeedsRegistration(false);
        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleForEventWithEmptyWaitingListAndAllowUnregistrationWithEmptyWaitingListReturnsTrue()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setAttendancesMax(10);
        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + Time::SECONDS_PER_DAY
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendancesOnQueue(0);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /*
     * Tests concerning getUnregistrationDeadlineFromModelAndConfiguration
     */

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndNoUnregistrationDeadlineReturnsZero()
    {
        $this->subject->setBeginDate(0);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setGlobalUnregistrationDeadline(0);

        self::assertSame(
            0,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlineSetInEventReturnsUnregistrationDeadline()
    {
        $this->subject->setBeginDate(0);
        $this->subject->setUnregistrationDeadline($this->now);
        $this->subject->setGlobalUnregistrationDeadline(0);

        self::assertSame(
            $this->now,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlinInEventAndUnregistrationDeadlineSetInConfigurationReturnsZero()
    {
        $this->subject->setBeginDate(0);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setGlobalUnregistrationDeadline($this->now);

        self::assertSame(
            0,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlineSetInEventReturnsThisDeadline()
    {
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline($this->now);
        $this->subject->setGlobalUnregistrationDeadline(0);

        self::assertSame(
            $this->now,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndNoDeadlineConfigurationSetReturnsZero()
    {
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setGlobalUnregistrationDeadline(0);

        self::assertSame(
            0,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndDeadlineConfigurationSetReturnsCalculatedDeadline()
    {
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline(0);
        $this->subject->setGlobalUnregistrationDeadline(1);

        self::assertSame(
            $this->now + Time::SECONDS_PER_WEEK - Time::SECONDS_PER_DAY,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlinesSetInEventAndConfigurationReturnsEventsDeadline()
    {
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline($this->now);
        $this->subject->setGlobalUnregistrationDeadline(1);

        self::assertSame(
            $this->now,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /*
     * Tests concerning hasRegistrationQueue
     */

    /**
     * @test
     */
    public function hasRegistrationQueueWithQueueReturnsTrue()
    {
        $this->subject->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->hasRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationQueueWithoutQueueReturnsFalse()
    {
        $this->subject->setRegistrationQueue(false);

        self::assertFalse(
            $this->subject->hasRegistrationQueue()
        );
    }

    /*
     * Tests concerning getAttendancesOnRegistrationQueue
     */

    /**
     * @test
     */
    public function getAttendancesOnRegistrationQueueIsInitiallyZero()
    {
        self::assertSame(
            0,
            $this->subject->getAttendancesOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function getAttendancesOnRegistrationQueueForNonEmptyRegistrationQueue()
    {
        $this->subject->setNumberOfAttendancesOnQueue(4);
        self::assertSame(
            4,
            $this->subject->getAttendancesOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasAttendancesOnRegistrationQueueIsFalseForNoRegistrations()
    {
        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(false);
        $this->subject->setNumberOfAttendances(0);
        $this->subject->setNumberOfAttendancesOnQueue(0);

        self::assertFalse(
            $this->subject->hasAttendancesOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasAttendancesOnRegistrationQueueIsFalseForRegularRegistrationsOnly()
    {
        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(false);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(0);

        self::assertFalse(
            $this->subject->hasAttendancesOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasAttendancesOnRegistrationQueueIsTrueForQueueRegistrations()
    {
        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(1);

        self::assertTrue(
            $this->subject->hasAttendancesOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleIsTrueWithNonEmptyQueueByDefault()
    {
        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(1);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + (6 * Time::SECONDS_PER_DAY)
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleIsFalseWithEmptyQueueByDefault()
    {
        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(0);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + (6 * Time::SECONDS_PER_DAY)
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleIsTrueWithEmptyQueueIfAllowedByConfiguration()
    {
        $this->subject->setAllowUnregistrationWithEmptyWaitingList(true);

        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(0);

        $this->subject->setGlobalUnregistrationDeadline(1);
        $this->subject->setUnregistrationDeadline(
            $this->now + (6 * Time::SECONDS_PER_DAY)
        );
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /*
     * Tests regarding the country field of the place records
     */

    /**
     * @test
     */
    public function getPlacesWithCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => 'ch',
            ]
        );

        self::assertSame(
            ['ch'],
            $this->subject->getPlacesWithCountry()
        );
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWithNoCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => '',
            ]
        );

        self::assertSame(
            [],
            $this->subject->getPlacesWithCountry()
        );
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWithInvalidCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => 'xy',
            ]
        );

        self::assertSame(
            ['xy'],
            $this->subject->getPlacesWithCountry()
        );
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWithNoPlace()
    {
        self::assertSame(
            [],
            $this->subject->getPlacesWithCountry()
        );
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWithDeletedPlace()
    {
        $this->addPlaceRelation(
            [
                'country' => 'at',
                'deleted' => 1,
            ]
        );

        self::assertSame(
            [],
            $this->subject->getPlacesWithCountry()
        );
    }

    /**
     * @test
     */
    public function getPlacesWithCountryWithMultipleCountries()
    {
        $this->addPlaceRelation(
            [
                'country' => 'ch',
            ]
        );
        $this->addPlaceRelation(
            [
                'country' => 'de',
            ]
        );

        self::assertSame(
            ['ch', 'de'],
            $this->subject->getPlacesWithCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => 'ch',
            ]
        );

        self::assertTrue(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithNoCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => '',
            ]
        );

        self::assertFalse(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithInvalicCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => 'xy',
            ]
        );

        // We expect a TRUE even if the country code is invalid! See function's
        // comment on this.
        self::assertTrue(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithNoPlace()
    {
        self::assertFalse(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function hasCountryWithMultipleCountries()
    {
        $this->addPlaceRelation(
            [
                'country' => 'ch',
            ]
        );
        $this->addPlaceRelation(
            [
                'country' => 'de',
            ]
        );

        self::assertTrue(
            $this->subject->hasCountry()
        );
    }

    /**
     * @test
     */
    public function getCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => 'ch',
            ]
        );

        self::assertSame(
            'Schweiz',
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithNoCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => '',
            ]
        );

        self::assertSame(
            '',
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithInvalidCountry()
    {
        $this->addPlaceRelation(
            [
                'country' => 'xy',
            ]
        );

        self::assertSame(
            '',
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithMultipleCountries()
    {
        $this->addPlaceRelation(
            [
                'country' => 'ch',
            ]
        );
        $this->addPlaceRelation(
            [
                'country' => 'de',
            ]
        );

        self::assertSame(
            'Schweiz, Deutschland',
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryWithNoPlace()
    {
        self::assertSame(
            '',
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryNameFromIsoCode()
    {
        self::assertSame(
            'Schweiz',
            $this->subject->getCountryNameFromIsoCode('ch')
        );

        self::assertSame(
            '',
            $this->subject->getCountryNameFromIsoCode('xy')
        );

        self::assertSame(
            '',
            $this->subject->getCountryNameFromIsoCode('')
        );
    }

    /*
     * Tests regarding the target groups
     */

    /**
     * @test
     */
    public function hasTargetGroupsIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasTargetGroups()
        );
    }

    /**
     * @test
     */
    public function hasTargetGroups()
    {
        $this->addTargetGroupRelation();

        self::assertTrue(
            $this->subject->hasTargetGroups()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupNamesWithNoTargetGroup()
    {
        self::assertSame(
            '',
            $this->subject->getTargetGroupNames()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupNamesWithSingleTargetGroup()
    {
        $title = 'TEST target group 1';
        $this->addTargetGroupRelation(['title' => $title]);

        self::assertSame(
            $title,
            $this->subject->getTargetGroupNames()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupNamesWithMultipleTargetGroups()
    {
        $titleTargetGroup1 = 'TEST target group 1';
        $this->addTargetGroupRelation(['title' => $titleTargetGroup1]);

        $titleTargetGroup2 = 'TEST target group 2';
        $this->addTargetGroupRelation(['title' => $titleTargetGroup2]);

        self::assertSame(
            $titleTargetGroup1 . ', ' . $titleTargetGroup2,
            $this->subject->getTargetGroupNames()
        );
    }

    /*
     * Tests regarding the payment methods
     */

    /**
     * @test
     */
    public function hasPaymentMethodsReturnsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function canHaveOnePaymentMethod()
    {
        $this->addPaymentMethodRelation();

        self::assertTrue(
            $this->subject->hasPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainWithNoPaymentMethodReturnsAnEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getPaymentMethodsPlain()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainWithSinglePaymentMethodReturnsASinglePaymentMethod()
    {
        $title = 'Test title';
        $this->addPaymentMethodRelation(['title' => $title]);

        self::assertContains(
            $title,
            $this->subject->getPaymentMethodsPlain()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainWithMultiplePaymentMethodsReturnsMultiplePaymentMethods()
    {
        $firstTitle = 'Payment Method 1';
        $secondTitle = 'Payment Method 2';
        $this->addPaymentMethodRelation(['title' => $firstTitle]);
        $this->addPaymentMethodRelation(['title' => $secondTitle]);

        self::assertContains(
            $firstTitle,
            $this->subject->getPaymentMethodsPlain()
        );
        self::assertContains(
            $secondTitle,
            $this->subject->getPaymentMethodsPlain()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithoutPaymentMethodsReturnsAnEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithOnePaymentMethodReturnsOnePaymentMethod()
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method']);

        self::assertSame(
            ['Payment Method'],
            $this->subject->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethods()
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);

        self::assertSame(
            ['Payment Method 1', 'Payment Method 2'],
            $this->subject->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethodsSorted()
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);

        self::assertSame(
            ['Payment Method 2', 'Payment Method 1'],
            $this->subject->getPaymentMethods()
        );
    }

    /*
     * Tests concerning getPaymentMethodsPlainShort
     */

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortWithNoPaymentMethodReturnsAnEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortWithSinglePaymentMethodReturnsASinglePaymentMethod()
    {
        $title = 'Test title';
        $this->addPaymentMethodRelation(['title' => $title]);

        self::assertContains(
            $title,
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortWithMultiplePaymentMethodsReturnsMultiplePaymentMethods()
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);

        self::assertContains(
            'Payment Method 1',
            $this->subject->getPaymentMethodsPlainShort()
        );
        self::assertContains(
            'Payment Method 2',
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortSeparatesMultiplePaymentMethodsWithLineFeeds()
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);

        self::assertContains(
            'Payment Method 1' . LF . 'Payment Method 2',
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortDoesNotSeparateMultiplePaymentMethodsWithCarriageReturnsAndLineFeeds()
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);

        self::assertNotContains(
            'Payment Method 1' . CRLF . 'Payment Method 2',
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /*
     * Tests concerning getSinglePaymentMethodPlain
     */

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithInvalidPaymentMethodUidReturnsAnEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getSinglePaymentMethodPlain(0)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithValidPaymentMethodUidWithoutDescriptionReturnsTitle()
    {
        $title = 'Test payment method';
        $uid = $this->addPaymentMethodRelation(['title' => $title]);

        self::assertSame(
            $title . LF . LF,
            $this->subject->getSinglePaymentMethodPlain($uid)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithValidPaymentMethodUidWithDescriptionReturnsTitleAndDescription()
    {
        $title = 'Test payment method';
        $description = 'some description';
        $uid = $this->addPaymentMethodRelation(['title' => $title, 'description' => $description]);

        self::assertSame(
            $title . ': ' . $description . LF . LF,
            $this->subject->getSinglePaymentMethodPlain($uid)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithNonExistentPaymentMethodUidReturnsAnEmptyString()
    {
        $uid = $this->addPaymentMethodRelation();

        self::assertSame(
            '',
            $this->subject->getSinglePaymentMethodPlain($uid + 1)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodShortWithInvalidPaymentMethodUidReturnsAnEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getSinglePaymentMethodShort(0)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodShortWithValidPaymentMethodUidReturnsTheTitleOfThePaymentMethod()
    {
        $title = 'Test payment method';
        $uid = $this->addPaymentMethodRelation(['title' => $title]);

        self::assertContains(
            $title,
            $this->subject->getSinglePaymentMethodShort($uid)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodShortWithNonExistentPaymentMethodUidReturnsAnEmptyString()
    {
        $uid = $this->addPaymentMethodRelation();

        self::assertSame(
            '',
            $this->subject->getSinglePaymentMethodShort($uid + 1)
        );
    }

    /*
     * Tests regarding the event type
     */

    /**
     * @test
     */
    public function setEventTypeThrowsExceptionForNegativeArgument()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventType must be >= 0.'
        );

        $this->subject->setEventType(-1);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setEventTypeIsAllowedWithZero()
    {
        $this->subject->setEventType(0);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setEventTypeIsAllowedWithPositiveInteger()
    {
        $this->subject->setEventType(1);
    }

    /**
     * @test
     */
    public function hasEventTypeInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasEventType()
        );
    }

    /**
     * @test
     */
    public function hasEventTypeReturnsTrueIfSingleEventHasNonZeroEventType()
    {
        $this->subject->setEventType(
            $this->testingFramework->createRecord('tx_seminars_event_types')
        );

        self::assertTrue(
            $this->subject->hasEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeReturnsEmptyStringForSingleEventWithoutType()
    {
        self::assertSame(
            '',
            $this->subject->getEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeReturnsTitleOfRelatedEventTypeForSingleEvent()
    {
        $this->subject->setEventType(
            $this->testingFramework->createRecord(
                'tx_seminars_event_types',
                ['title' => 'foo type']
            )
        );

        self::assertSame(
            'foo type',
            $this->subject->getEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeForDateRecordReturnsTitleOfEventTypeFromTopicRecord()
    {
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'event_type' => $this->testingFramework->createRecord(
                    'tx_seminars_event_types',
                    ['title' => 'foo type']
                ),
            ]
        );
        $dateRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicRecordUid,
            ]
        );
        $seminar = new \Tx_Seminars_OldModel_Event($dateRecordUid);

        self::assertSame(
            'foo type',
            $seminar->getEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeForTopicRecordReturnsTitleOfRelatedEventType()
    {
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'event_type' => $this->testingFramework->createRecord(
                    'tx_seminars_event_types',
                    ['title' => 'foo type']
                ),
            ]
        );
        $seminar = new \Tx_Seminars_OldModel_Event($topicRecordUid);

        self::assertSame(
            'foo type',
            $seminar->getEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeUidReturnsUidFromTopicRecord()
    {
        // This test comes from bug #1515.
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'event_type' => 99999,
            ]
        );
        $dateRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicRecordUid,
                'event_type' => 199999,
            ]
        );
        $seminar = new \Tx_Seminars_OldModel_Event($dateRecordUid);

        self::assertSame(
            99999,
            $seminar->getEventTypeUid()
        );
    }

    /**
     * @test
     */
    public function getEventTypeUidInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getEventTypeUid()
        );
    }

    /**
     * @test
     */
    public function getEventTypeUidWithEventTypeReturnsEventTypeUid()
    {
        $eventTypeUid = $this->addEventTypeRelation([]);
        self::assertSame(
            $eventTypeUid,
            $this->subject->getEventTypeUid()
        );
    }

    /*
     * Tests regarding the organizing partners
     */

    /**
     * @test
     */
    public function hasOrganizingPartnersReturnsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasOrganizingPartners()
        );
    }

    /**
     * @test
     */
    public function canHaveOneOrganizingPartner()
    {
        $this->addOrganizingPartnerRelation();

        self::assertTrue(
            $this->subject->hasOrganizingPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizingPartnersWithNoOrganizingPartnerReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfOrganizingPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizingPartnersWithSingleOrganizingPartnerReturnsOne()
    {
        $this->addOrganizingPartnerRelation();
        self::assertSame(
            1,
            $this->subject->getNumberOfOrganizingPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizingPartnersWithMultipleOrganizingPartnersReturnsTwo()
    {
        $this->addOrganizingPartnerRelation();
        $this->addOrganizingPartnerRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfOrganizingPartners()
        );
    }

    /*
     * Tests regarding the categories
     */

    /**
     * @test
     */
    public function initiallyHasNoCategories()
    {
        self::assertFalse(
            $this->subject->hasCategories()
        );
        self::assertSame(
            0,
            $this->subject->getNumberOfCategories()
        );
        self::assertSame(
            [],
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function getCategoriesCanReturnOneCategory()
    {
        $categoryUid = $this->addCategoryRelation(['title' => 'Test']);

        self::assertTrue(
            $this->subject->hasCategories()
        );
        self::assertSame(
            1,
            $this->subject->getNumberOfCategories()
        );
        self::assertSame(
            [$categoryUid => ['title' => 'Test', 'icon' => '']],
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function canHaveTwoCategories()
    {
        $categoryUid1 = $this->addCategoryRelation(['title' => 'Test 1']);
        $categoryUid2 = $this->addCategoryRelation(['title' => 'Test 2']);

        self::assertTrue(
            $this->subject->hasCategories()
        );
        self::assertSame(
            2,
            $this->subject->getNumberOfCategories()
        );

        $categories = $this->subject->getCategories();

        self::assertCount(
            2,
            $categories
        );
        self::assertSame(
            'Test 1',
            $categories[$categoryUid1]['title']
        );
        self::assertSame(
            'Test 2',
            $categories[$categoryUid2]['title']
        );
    }

    /**
     * @test
     */
    public function getCategoriesReturnsIconOfCategory()
    {
        $categoryUid = $this->addCategoryRelation(
            [
                'title' => 'Test 1',
                'icon' => 'foo.gif',
            ]
        );

        $categories = $this->subject->getCategories();

        self::assertSame(
            'foo.gif',
            $categories[$categoryUid]['icon']
        );
    }

    /**
     * @test
     */
    public function getCategoriesReturnsCategoriesOrderedBySorting()
    {
        $categoryUid1 = $this->addCategoryRelation(['title' => 'Test 1'], 2);
        $categoryUid2 = $this->addCategoryRelation(['title' => 'Test 2'], 1);

        self::assertTrue(
            $this->subject->hasCategories()
        );

        self::assertSame(
            [
                $categoryUid2 => ['title' => 'Test 2', 'icon' => ''],
                $categoryUid1 => ['title' => 'Test 1', 'icon' => ''],
            ],
            $this->subject->getCategories()
        );
    }

    /*
     * Tests regarding the time slots
     */

    /**
     * @test
     */
    public function getTimeSlotsAsArrayWithMarkersReturnsArraySortedByDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->subject->getUid(),
                'begin_date' => 200,
                'room' => 'Room1',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->subject->getUid(),
                'begin_date' => 100,
                'room' => 'Room2',
            ]
        );

        $timeSlotsWithMarkers = $this->subject->getTimeSlotsAsArrayWithMarkers();
        self::assertSame(
            $timeSlotsWithMarkers[0]['room'],
            'Room2'
        );
        self::assertSame(
            $timeSlotsWithMarkers[1]['room'],
            'Room1'
        );
    }

    /*
     * Tests regarding the organizers
     */

    /**
     * @test
     */
    public function hasOrganizersReturnsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasOrganizers()
        );
    }

    /**
     * @test
     */
    public function canHaveOneOrganizer()
    {
        $this->addOrganizerRelation();

        self::assertTrue(
            $this->subject->hasOrganizers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizersWithNoOrganizerReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfOrganizers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizersWithSingleOrganizerReturnsOne()
    {
        $this->addOrganizerRelation();
        self::assertSame(
            1,
            $this->subject->getNumberOfOrganizers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizersWithMultipleOrganizersReturnsTwo()
    {
        $this->addOrganizerRelation();
        $this->addOrganizerRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfOrganizers()
        );
    }

    /*
     * Tests concerning getOrganizers
     */

    /**
     * @test
     */
    public function getOrganizersWithNoOrganizersReturnsEmptyString()
    {
        $this->createPi1();

        self::assertSame(
            '',
            $this->subject->getOrganizers($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getOrganizersForOneOrganizerReturnsOrganizerName()
    {
        $this->createPi1();
        $this->addOrganizerRelation(['title' => 'foo']);

        self::assertContains(
            'foo',
            $this->subject->getOrganizers($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getOrganizersForOneOrganizerWithHomepageReturnsOrganizerLinkedToOrganizersHomepage()
    {
        $this->createPi1();
        $this->addOrganizerRelation(
            [
                'title' => 'foo',
                'homepage' => 'www.bar.com',
            ]
        );

        self::assertContains(
            '<a href="http://www.bar.com',
            $this->subject->getOrganizers($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getOrganizersWithTwoOrganizersReturnsBothOrganizerNames()
    {
        $this->createPi1();
        $this->addOrganizerRelation(['title' => 'foo']);
        $this->addOrganizerRelation(['title' => 'bar']);

        $organizers = $this->subject->getOrganizers($this->pi1);

        self::assertContains(
            'foo',
            $organizers
        );
        self::assertContains(
            'bar',
            $organizers
        );
    }

    /*
     * Tests concerning getOrganizersRaw
     */

    /**
     * @test
     */
    public function getOrganizersRawWithNoOrganizersReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawWithSingleOrganizerWithoutHomepageReturnsSingleOrganizer()
    {
        $organizer = [
            'title' => 'test organizer 1',
            'homepage' => '',
        ];
        $this->addOrganizerRelation($organizer);
        self::assertSame(
            $organizer['title'],
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawWithSingleOrganizerWithHomepageReturnsSingleOrganizerWithHomepage()
    {
        $organizer = [
            'title' => 'test organizer 1',
            'homepage' => 'test homepage 1',
        ];
        $this->addOrganizerRelation($organizer);
        self::assertSame(
            $organizer['title'] . ', ' . $organizer['homepage'],
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawForTwoOrganizersWithoutHomepageReturnsTwoOrganizers()
    {
        $this->addOrganizerRelation(
            ['title' => 'test organizer 1', 'homepage' => '']
        );
        $this->addOrganizerRelation(
            ['title' => 'test organizer 2', 'homepage' => '']
        );

        self::assertContains(
            'test organizer 1',
            $this->subject->getOrganizersRaw()
        );
        self::assertContains(
            'test organizer 2',
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawForTwoOrganizersWithHomepageReturnsTwoOrganizersWithHomepage()
    {
        $this->addOrganizerRelation(
            [
                'title' => 'test organizer 1',
                'homepage' => 'test homepage 1',
            ]
        );
        $this->addOrganizerRelation(
            [
                'title' => 'test organizer 2',
                'homepage' => 'test homepage 2',
            ]
        );

        self::assertContains(
            'test homepage 1',
            $this->subject->getOrganizersRaw()
        );
        self::assertContains(
            'test homepage 2',
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawSeparatesMultipleOrganizersWithLineFeeds()
    {
        $this->addOrganizerRelation(['title' => 'test organizer 1']);
        $this->addOrganizerRelation(['title' => 'test organizer 2']);

        self::assertContains(
            'test organizer 1' . LF . 'test organizer 2',
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawDoesNotSeparateMultipleOrganizersWithCarriageReturnsAndLineFeeds()
    {
        $this->addOrganizerRelation(['title' => 'test organizer 1']);
        $this->addOrganizerRelation(['title' => 'test organizer 2']);

        self::assertNotContains(
            'test organizer 1' . CRLF . 'test organizer 2',
            $this->subject->getOrganizersRaw()
        );
    }

    /*
     * Tests concerning getOrganizersNameAndEmail
     */

    /**
     * @test
     */
    public function getOrganizersNameAndEmailWithNoOrganizersReturnsEmptyString()
    {
        self::assertSame(
            [],
            $this->subject->getOrganizersNameAndEmail()
        );
    }

    /**
     * @test
     */
    public function getOrganizersNameAndEmailWithSingleOrganizerReturnsSingleOrganizer()
    {
        $organizer = [
            'title' => 'test organizer',
            'email' => 'test@organizer.org',
        ];
        $this->addOrganizerRelation($organizer);
        self::assertSame(
            ['"' . $organizer['title'] . '" <' . $organizer['email'] . '>'],
            $this->subject->getOrganizersNameAndEmail()
        );
    }

    /**
     * @test
     */
    public function getOrganizersNameAndEmailWithMultipleOrganizersReturnsTwoOrganizers()
    {
        $firstOrganizer = [
            'title' => 'test organizer 1',
            'email' => 'test1@organizer.org',
        ];
        $secondOrganizer = [
            'title' => 'test organizer 2',
            'email' => 'test2@organizer.org',
        ];
        $this->addOrganizerRelation($firstOrganizer);
        $this->addOrganizerRelation($secondOrganizer);
        self::assertSame(
            [
                '"' . $firstOrganizer['title'] . '" <' . $firstOrganizer['email'] . '>',
                '"' . $secondOrganizer['title'] . '" <' . $secondOrganizer['email'] . '>',
            ],
            $this->subject->getOrganizersNameAndEmail()
        );
    }

    /**
     * @test
     */
    public function getOrganizersEmailWithNoOrganizersReturnsEmptyString()
    {
        self::assertSame(
            [],
            $this->subject->getOrganizersEmail()
        );
    }

    /**
     * @test
     */
    public function getOrganizersEmailWithSingleOrganizerReturnsSingleOrganizer()
    {
        $organizer = ['email' => 'test@organizer.org'];
        $this->addOrganizerRelation($organizer);
        self::assertSame(
            [$organizer['email']],
            $this->subject->getOrganizersEmail()
        );
    }

    /**
     * @test
     */
    public function getOrganizersEmailWithMultipleOrganizersReturnsTwoOrganizers()
    {
        $firstOrganizer = ['email' => 'test1@organizer.org'];
        $secondOrganizer = ['email' => 'test2@organizer.org'];
        $this->addOrganizerRelation($firstOrganizer);
        $this->addOrganizerRelation($secondOrganizer);
        self::assertSame(
            [$firstOrganizer['email'], $secondOrganizer['email']],
            $this->subject->getOrganizersEmail()
        );
    }

    /*
     * Tests concerning getOrganizersFooter
     */

    /**
     * @test
     */
    public function getOrganizersFootersWithNoOrganizersReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getOrganizersFooter()
        );
    }

    /**
     * @test
     */
    public function getOrganizersFootersWithSingleOrganizerReturnsSingleOrganizerFooter()
    {
        $organizer = ['email_footer' => 'test email footer'];
        $this->addOrganizerRelation($organizer);
        self::assertSame(
            [$organizer['email_footer']],
            $this->subject->getOrganizersFooter()
        );
    }

    /**
     * @test
     */
    public function getOrganizersFootersWithMultipleOrganizersReturnsTwoOrganizerFooters()
    {
        $firstOrganizer = ['email_footer' => 'test email footer'];
        $secondOrganizer = ['email_footer' => 'test email footer'];
        $this->addOrganizerRelation($firstOrganizer);
        $this->addOrganizerRelation($secondOrganizer);
        self::assertSame(
            [
                $firstOrganizer['email_footer'],
                $secondOrganizer['email_footer'],
            ],
            $this->subject->getOrganizersFooter()
        );
    }

    /**
     * @test
     */
    public function getOrganizersFootersWithSingleOrganizerWithoutEMailFooterReturnsEmptyArray()
    {
        $this->addOrganizerRelation();

        self::assertSame(
            [],
            $this->subject->getOrganizersFooter()
        );
    }

    /**
     * @test
     */
    public function getOrganizersFootersWithTwoOrganizersOneWithFooterOneWithoutrReturnsOnlyTheNonEmptyFooter()
    {
        $secondOrganizer = ['email_footer' => 'test email footer'];
        $this->addOrganizerRelation();
        $this->addOrganizerRelation($secondOrganizer);
        self::assertSame(
            [$secondOrganizer['email_footer']],
            $this->subject->getOrganizersFooter()
        );
    }

    /*
     * Tests concerning getFirstOrganizer
     */

    /**
     * @test
     */
    public function getFirstOrganizerWithNoOrganizersReturnsNull()
    {
        self::assertNull(
            $this->subject->getFirstOrganizer()
        );
    }

    /**
     * @test
     */
    public function getFirstOrganizerForOneOrganizerReturnsThatOrganizer()
    {
        $organizerUid = $this->addOrganizerRelation();

        self::assertSame(
            $organizerUid,
            $this->subject->getFirstOrganizer()->getUid()
        );
    }

    /**
     * @test
     */
    public function getFirstOrganizerForTwoOrganizerReturnsFirstOrganizer()
    {
        $firstOrganizerUid = $this->addOrganizerRelation();
        $this->addOrganizerRelation();

        self::assertSame(
            $firstOrganizerUid,
            $this->subject->getFirstOrganizer()->getUid()
        );
    }

    /*
     * Tests concerning getAttendancesPid
     */

    /**
     * @test
     */
    public function getAttendancesPidWithNoOrganizerReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getAttendancesPid()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPidWithSingleOrganizerReturnsPid()
    {
        $this->addOrganizerRelation(['attendances_pid' => 99]);
        self::assertSame(
            99,
            $this->subject->getAttendancesPid()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPidWithMultipleOrganizerReturnsFirstPid()
    {
        $this->addOrganizerRelation(['attendances_pid' => 99]);
        $this->addOrganizerRelation(['attendances_pid' => 66]);
        self::assertSame(
            99,
            $this->subject->getAttendancesPid()
        );
    }

    /*
     * Tests regarding getOrganizerBag().
     */

    /**
     * @test
     */
    public function getOrganizerBagWithoutOrganizersThrowsException()
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'There are no organizers related to this event.'
        );

        $this->subject->getOrganizerBag();
    }

    /**
     * @test
     */
    public function getOrganizerBagWithOrganizerReturnsOrganizerBag()
    {
        $this->addOrganizerRelation();

        self::assertInstanceOf(\Tx_Seminars_Bag_Organizer::class, $this->subject->getOrganizerBag());
    }

    /*
     * Tests regarding the speakers
     */

    /**
     * @test
     */
    public function getNumberOfSpeakersWithNoSpeakerReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfSpeakers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfSpeakersWithSingleSpeakerReturnsOne()
    {
        $this->addSpeakerRelation([]);
        self::assertSame(
            1,
            $this->subject->getNumberOfSpeakers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfSpeakersWithMultipleSpeakersReturnsTwo()
    {
        $this->addSpeakerRelation([]);
        $this->addSpeakerRelation([]);
        self::assertSame(
            2,
            $this->subject->getNumberOfSpeakers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfPartnersWithNoPartnerReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfPartnersWithSinglePartnerReturnsOne()
    {
        $this->addPartnerRelation([]);
        self::assertSame(
            1,
            $this->subject->getNumberOfPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfPartnersWithMultiplePartnersReturnsTwo()
    {
        $this->addPartnerRelation([]);
        $this->addPartnerRelation([]);
        self::assertSame(
            2,
            $this->subject->getNumberOfPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfTutorsWithNoTutorReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfTutors()
        );
    }

    /**
     * @test
     */
    public function getNumberOfTutorsWithSingleTutorReturnsOne()
    {
        $this->addTutorRelation([]);
        self::assertSame(
            1,
            $this->subject->getNumberOfTutors()
        );
    }

    /**
     * @test
     */
    public function getNumberOfTutorsWithMultipleTutorsReturnsTwo()
    {
        $this->addTutorRelation([]);
        $this->addTutorRelation([]);
        self::assertSame(
            2,
            $this->subject->getNumberOfTutors()
        );
    }

    /**
     * @test
     */
    public function getNumberOfLeadersWithNoLeaderReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfLeaders()
        );
    }

    /**
     * @test
     */
    public function getNumberOfLeadersWithSingleLeaderReturnsOne()
    {
        $this->addLeaderRelation([]);
        self::assertSame(
            1,
            $this->subject->getNumberOfLeaders()
        );
    }

    /**
     * @test
     */
    public function getNumberOfLeadersWithMultipleLeadersReturnsTwo()
    {
        $this->addLeaderRelation([]);
        $this->addLeaderRelation([]);
        self::assertSame(
            2,
            $this->subject->getNumberOfLeaders()
        );
    }

    /**
     * @test
     */
    public function hasSpeakersOfTypeIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasSpeakersOfType()
        );
        self::assertFalse(
            $this->subject->hasSpeakersOfType('partners')
        );
        self::assertFalse(
            $this->subject->hasSpeakersOfType('tutors')
        );
        self::assertFalse(
            $this->subject->hasSpeakersOfType('leaders')
        );
    }

    /**
     * @test
     */
    public function hasSpeakersOfTypeWithSingleSpeakerOfTypeReturnsTrue()
    {
        $this->addSpeakerRelation([]);
        self::assertTrue(
            $this->subject->hasSpeakersOfType()
        );

        $this->addPartnerRelation([]);
        self::assertTrue(
            $this->subject->hasSpeakersOfType('partners')
        );

        $this->addTutorRelation([]);
        self::assertTrue(
            $this->subject->hasSpeakersOfType('tutors')
        );

        $this->addLeaderRelation([]);
        self::assertTrue(
            $this->subject->hasSpeakersOfType('leaders')
        );
    }

    /**
     * @test
     */
    public function hasSpeakersIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasSpeakers()
        );
    }

    /**
     * @test
     */
    public function canHaveOneSpeaker()
    {
        $this->addSpeakerRelation([]);
        self::assertTrue(
            $this->subject->hasSpeakers()
        );
    }

    /**
     * @test
     */
    public function hasPartnersIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasPartners()
        );
    }

    /**
     * @test
     */
    public function canHaveOnePartner()
    {
        $this->addPartnerRelation([]);
        self::assertTrue(
            $this->subject->hasPartners()
        );
    }

    /**
     * @test
     */
    public function hasTutorsIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasTutors()
        );
    }

    /**
     * @test
     */
    public function canHaveOneTutor()
    {
        $this->addTutorRelation([]);
        self::assertTrue(
            $this->subject->hasTutors()
        );
    }

    /**
     * @test
     */
    public function hasLeadersIsInitiallyFalse()
    {
        self::assertFalse(
            $this->subject->hasLeaders()
        );
    }

    /**
     * @test
     */
    public function canHaveOneLeader()
    {
        $this->addLeaderRelation([]);
        self::assertTrue(
            $this->subject->hasLeaders()
        );
    }

    /*
     * Tests concerning getSpeakersWithDescriptionRaw
     */

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawWithNoSpeakersReturnsAnEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawReturnsTitleOfSpeaker()
    {
        $this->addSpeakerRelation(['title' => 'test speaker']);

        self::assertContains(
            'test speaker',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationReturnsSpeakerWithOrganization()
    {
        $this->addSpeakerRelation(['organization' => 'test organization']);

        self::assertContains(
            'test organization',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithHomepageReturnsSpeakerWithHomepage()
    {
        $this->addSpeakerRelation(['homepage' => 'test homepage']);

        self::assertContains(
            'test homepage',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationAndHomepageReturnsSpeakerWithOrganizationAndHomepage()
    {
        $this->addSpeakerRelation(
            [
                'organization' => 'test organization',
                'homepage' => 'test homepage',
            ]
        );

        self::assertRegExp(
            '/test organization.*test homepage/',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithDescriptionReturnsSpeakerWithDescription()
    {
        $this->addSpeakerRelation(['description' => 'test description']);

        self::assertContains(
            'test description',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationAndDescriptionReturnsOrganizationAndDescription()
    {
        $this->addSpeakerRelation(
            [
                'organization' => 'foo',
                'description' => 'bar',
            ]
        );
        self::assertRegExp(
            '/foo.*bar/s',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithHomepageAndDescriptionReturnsHomepageAndDescription()
    {
        $this->addSpeakerRelation(
            [
                'homepage' => 'test homepage',
                'description' => 'test description',
            ]
        );

        self::assertRegExp(
            '/test homepage.*test description/s',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForTwoSpeakersReturnsTwoSpeakers()
    {
        $this->addSpeakerRelation(['title' => 'test speaker 1']);
        $this->addSpeakerRelation(['title' => 'test speaker 2']);

        self::assertContains(
            'test speaker 1',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
        self::assertContains(
            'test speaker 2',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForTwoSpeakersWithOrganizationReturnsTwoSpeakersWithOrganization()
    {
        $this->addSpeakerRelation(
            ['organization' => 'test organization 1']
        );
        $this->addSpeakerRelation(
            ['organization' => 'test organization 2']
        );

        self::assertContains(
            'test organization 1',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
        self::assertContains(
            'test organization 2',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawOnlyReturnsSpeakersOfGivenType()
    {
        $this->addSpeakerRelation(['title' => 'test speaker']);
        $this->addPartnerRelation(['title' => 'test partner']);

        self::assertNotContains(
            'test partner',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypePartner()
    {
        $this->addPartnerRelation(['title' => 'test partner']);

        self::assertContains(
            'test partner',
            $this->subject->getSpeakersWithDescriptionRaw('partners')
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypeLeaders()
    {
        $this->addLeaderRelation(['title' => 'test leader']);

        self::assertContains(
            'test leader',
            $this->subject->getSpeakersWithDescriptionRaw('leaders')
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypeTutors()
    {
        $this->addTutorRelation(['title' => 'test tutor']);

        self::assertContains(
            'test tutor',
            $this->subject->getSpeakersWithDescriptionRaw('tutors')
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawSeparatesMultipleSpeakersWithLineFeeds()
    {
        $this->addSpeakerRelation(['title' => 'foo']);
        $this->addSpeakerRelation(['title' => 'bar']);

        self::assertContains(
            'foo' . LF . 'bar',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawDoesNotSeparateMultipleSpeakersWithCarriageReturnsAndLineFeeds()
    {
        $this->addSpeakerRelation(['title' => 'foo']);
        $this->addSpeakerRelation(['title' => 'bar']);

        self::assertNotContains(
            'foo' . CRLF . 'bar',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawDoesNotSeparateSpeakersDescriptionAndTitleWithCarriageReturnsAndLineFeeds()
    {
        $this->addSpeakerRelation(
            [
                'title' => 'foo',
                'description' => 'bar',
            ]
        );

        self::assertNotRegExp(
            '/foo' . CRLF . 'bar/',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawSeparatesSpeakersDescriptionAndTitleWithLineFeeds()
    {
        $this->addSpeakerRelation(
            [
                'title' => 'foo',
                'description' => 'bar',
            ]
        );

        self::assertRegExp(
            '/foo' . LF . 'bar/',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /*
     * Tests concerning getSpeakersShort
     */

    /**
     * @test
     */
    public function getSpeakersShortWithNoSpeakersReturnsAnEmptyString()
    {
        $this->createPi1();

        self::assertSame(
            '',
            $this->subject->getSpeakersShort($this->pi1)
        );
        self::assertSame(
            '',
            $this->subject->getSpeakersShort($this->pi1, 'partners')
        );
        self::assertSame(
            '',
            $this->subject->getSpeakersShort($this->pi1, 'tutors')
        );
        self::assertSame(
            '',
            $this->subject->getSpeakersShort($this->pi1, 'leaders')
        );
    }

    /**
     * @test
     */
    public function getSpeakersShortWithSingleSpeakersReturnsSingleSpeaker()
    {
        $this->createPi1();
        $speaker = ['title' => 'test speaker'];

        $this->addSpeakerRelation($speaker);
        self::assertSame(
            $speaker['title'],
            $this->subject->getSpeakersShort($this->pi1)
        );

        $this->addPartnerRelation($speaker);
        self::assertSame(
            $speaker['title'],
            $this->subject->getSpeakersShort($this->pi1, 'partners')
        );

        $this->addTutorRelation($speaker);
        self::assertSame(
            $speaker['title'],
            $this->subject->getSpeakersShort($this->pi1, 'tutors')
        );

        $this->addLeaderRelation($speaker);
        self::assertSame(
            $speaker['title'],
            $this->subject->getSpeakersShort($this->pi1, 'leaders')
        );
    }

    /**
     * @test
     */
    public function getSpeakersShortWithMultipleSpeakersReturnsTwoSpeakers()
    {
        $firstSpeaker = ['title' => 'test speaker 1'];
        $secondSpeaker = ['title' => 'test speaker 2'];

        $this->addSpeakerRelation($firstSpeaker);
        $this->addSpeakerRelation($secondSpeaker);
        $this->createPi1();
        self::assertSame(
            $firstSpeaker['title'] . ', ' . $secondSpeaker['title'],
            $this->subject->getSpeakersShort($this->pi1)
        );

        $this->addPartnerRelation($firstSpeaker);
        $this->addPartnerRelation($secondSpeaker);
        self::assertSame(
            $firstSpeaker['title'] . ', ' . $secondSpeaker['title'],
            $this->subject->getSpeakersShort($this->pi1, 'partners')
        );

        $this->addTutorRelation($firstSpeaker);
        $this->addTutorRelation($secondSpeaker);
        self::assertSame(
            $firstSpeaker['title'] . ', ' . $secondSpeaker['title'],
            $this->subject->getSpeakersShort($this->pi1, 'tutors')
        );

        $this->addLeaderRelation($firstSpeaker);
        $this->addLeaderRelation($secondSpeaker);
        self::assertSame(
            $firstSpeaker['title'] . ', ' . $secondSpeaker['title'],
            $this->subject->getSpeakersShort($this->pi1, 'leaders')
        );
    }

    /**
     * @test
     */
    public function getSpeakersShortReturnsSpeakerLinkedToSpeakerHomepage()
    {
        $speakerWithLink = [
            'title' => 'test speaker',
            'homepage' => 'http://www.foo.com',
        ];
        $this->addSpeakerRelation($speakerWithLink);
        $this->createPi1();

        self::assertRegExp(
            '/href="http:\\/\\/www.foo.com".*>test speaker/',
            $this->subject->getSpeakersShort($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getSpeakersForSpeakerWithoutHomepageReturnsSpeakerNameWithoutLinkTag()
    {
        $speaker = [
            'title' => 'test speaker',
        ];

        $this->addSpeakerRelation($speaker);
        $this->createPi1();

        $shortSpeakerOutput
            = $this->subject->getSpeakersShort($this->pi1);

        self::assertContains(
            'test speaker',
            $shortSpeakerOutput
        );
        self::assertNotContains(
            '<a',
            $shortSpeakerOutput
        );
    }

    /*
     * Test concerning the collision check
     */

    /**
     * @test
     */
    public function isUserBlockForZeroUserUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->isUserBlocked(0);
    }

    /**
     * @test
     */
    public function isUserBlockForNegativeUserUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->isUserBlocked(-1);
    }

    /**
     * @return int[][]
     */
    public function overlappingEventsDataProvider(): array
    {
        return [
            'exact same dates' => [$this->now, $this->now + 1000, $this->now, $this->now + 1000],
            'registered event starts first' => [$this->now, $this->now + 1000, $this->now + 100, $this->now + 1000],
            'check event starts first' => [$this->now + 100, $this->now + 1000, $this->now, $this->now + 1000],
            'registered event in check event' => [$this->now + 100, $this->now + 500, $this->now, $this->now + 1000],
            'check event in registered event' => [$this->now, $this->now + 1000, $this->now + 100, $this->now + 500],
        ];
    }

    /**
     * @test
     *
     * @param int $registrationBegin
     * @param int $registrationEnd
     * @param int $checkBegin
     * @param int $checkEnd
     *
     * @dataProvider overlappingEventsDataProvider
     */
    public function overlappingEventsCollide(
        int $registrationBegin,
        int $registrationEnd,
        int $checkBegin,
        int $checkEnd
    ) {
        $this->subject->setBeginDate($checkBegin);
        $this->subject->setEndDate($checkEnd);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $registrationBegin, 'end_date' => $registrationEnd]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertTrue($this->subject->isUserBlocked($userUid));
    }

    /**
     * @return int[][]
     */
    public function nonOverlappingEventsDataProvider(): array
    {
        return [
            'registered first' => [$this->now, $this->now + 100, $this->now + 500, $this->now + 1000],
            'check event first' => [$this->now + 500, $this->now + 1000, $this->now, $this->now + 100],
        ];
    }

    /**
     * @test
     *
     * @param int $registrationBegin
     * @param int $registrationEnd
     * @param int $checkBegin
     * @param int $checkEnd
     *
     * @dataProvider nonOverlappingEventsDataProvider
     */
    public function nonOverlappingEventsDoNotCollide(
        int $registrationBegin,
        int $registrationEnd,
        int $checkBegin,
        int $checkEnd
    ) {
        $this->subject->setBeginDate($checkBegin);
        $this->subject->setEndDate($checkEnd);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $registrationBegin, 'end_date' => $registrationEnd]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertFalse($this->subject->isUserBlocked($userUid));
    }

    /**
     * @test
     */
    public function collidingEventsDoNotCollideIfCollisionSkipIsEnabledInConfiguration()
    {
        $userUid = $this->testingFramework->createFrontEndUser();

        $begin = $this->now;
        $end = $begin + 1000;
        $this->subject->setBeginDate($begin);
        $this->subject->setEndDate($end);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $begin, 'end_date' => $end]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        $this->subject->setConfigurationValue('skipRegistrationCollisionCheck', true);

        self::assertFalse($this->subject->isUserBlocked($userUid));
    }

    /**
     * @test
     */
    public function collidingEventsDoNoCollideIfCollisionSkipIsEnabledForThisEvent()
    {
        $userUid = $this->testingFramework->createFrontEndUser();

        $begin = $this->now;
        $end = $begin + 1000;
        $this->subject->setBeginDate($begin);
        $this->subject->setEndDate($end);
        $this->subject->setSkipCollisionCheck(true);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $begin, 'end_date' => $end]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertFalse($this->subject->isUserBlocked($userUid));
    }

    /**
     * @test
     */
    public function collidingEventsDoNoCollideIfCollisionSkipIsEnabledForAnotherEvent()
    {
        $userUid = $this->testingFramework->createFrontEndUser();

        $begin = $this->now;
        $end = $begin + 1000;
        $this->subject->setBeginDate($begin);
        $this->subject->setEndDate($end);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $begin, 'end_date' => $end, 'skip_collision_check' => 1]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertFalse($this->subject->isUserBlocked($userUid));
    }

    /**
     * @test
     */
    public function notCollidesWithEventWithSurroundingTimeSlots()
    {
        $this->subject->setBeginDate($this->now + 200);
        $this->subject->setEndDate($this->now + 300);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $this->now, 'end_date' => $this->now + 500, 'timeslots' => 2]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now, 'end_date' => $this->now + 100]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now + 400, 'end_date' => $this->now + 500]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertFalse($this->subject->isUserBlocked($userUid));
    }

    /**
     * @return int[][]
     */
    public function eventsOverlappingWithTimeSlotDataProvider(): array
    {
        return [
            'starts before 1st and ends after 1st' => [$this->now - 50, $this->now + 150],
            'starts in 1st and ends in 1st' => [$this->now + 25, $this->now + 75],
            'starts in 1st and ends in 2nd' => [$this->now + 50, $this->now + 450],
            'starts in 1st and ends before 2nd' => [$this->now + 50, $this->now + 300],
            'starts after 1st and ends in 2nd' => [$this->now + 150, $this->now + 450],
            'starts before 1st and ends after 2nd' => [$this->now - 50, $this->now + 550],
            'starts in 1st and ends after it' => [$this->now + 50, $this->now + 150],
            'starts in 2nd and ends after it' => [$this->now + 450, $this->now + 550],
        ];
    }

    /**
     * @test
     *
     * @param int $registrationBegin
     * @param int $registrationEnd
     *
     * @dataProvider eventsOverlappingWithTimeSlotDataProvider
     */
    public function collidesWithEventWithTimeSlots(int $registrationBegin, int $registrationEnd)
    {
        $this->subject->setBeginDate($registrationBegin);
        $this->subject->setEndDate($registrationEnd);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $this->now, 'end_date' => $this->now + 500, 'timeslots' => 2]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now, 'end_date' => $this->now + 100]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now + 400, 'end_date' => $this->now + 500]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertTrue($this->subject->isUserBlocked($userUid));
    }

    /**
     * @return int[][][]
     */
    public function timeSlotsCollidingWithTimeSlotsDataProvider(): array
    {
        return [
            'one time slot starting in 1st and ending in it' => [[[$this->now + 25, $this->now + 75]]],
        ];
    }

    /**
     * @test
     *
     * @param array $timeSlotDates
     * @dataProvider timeSlotsCollidingWithTimeSlotsDataProvider
     */
    public function timeSlotsCollideWithCollidingTimeSlots(array $timeSlotDates)
    {
        $checkEventBegin = PHP_INT_MAX;
        $checkEventEnd = 0;
        $checkEventUid = $this->subject->getUid();
        foreach ($timeSlotDates as list($beginDate, $endDate)) {
            $this->testingFramework->createRecord(
                'tx_seminars_timeslots',
                ['seminar' => $checkEventUid, 'begin_date' => $beginDate, 'end_date' => $endDate]
            );

            $checkEventBegin = min($checkEventBegin, $beginDate);
            $checkEventEnd = max($checkEventEnd, $endDate);
        }
        $this->subject->setNumberOfTimeSlots(count($timeSlotDates));
        $this->subject->setBeginDate($checkEventBegin);
        $this->subject->setEndDate($checkEventEnd);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $this->now, 'end_date' => $this->now + 500, 'timeslots' => 2]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now, 'end_date' => $this->now + 100]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now + 400, 'end_date' => $this->now + 500]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertTrue($this->subject->isUserBlocked($userUid));
    }

    /**
     * @return int[][][]
     */
    public function timeSlotsNotCollidingWithTimeSlotsDataProvider(): array
    {
        return [
            'one time slot before first' => [[[$this->now - 200, $this->now - 100]]],
            'one time slot after second' => [[[$this->now + 600, $this->now + 700]]],
            'one time slot between first and second' => [[[$this->now + 200, $this->now + 300]]],
            'one time slot before first and one after second' => [
                [
                    [$this->now - 200, $this->now - 100],
                    [$this->now + 600, $this->now + 700],
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @param array $timeSlotDates
     * @dataProvider timeSlotsNotCollidingWithTimeSlotsDataProvider
     */
    public function timeSlotsDoNotCollideWithCollisionFreeTimeSlots(array $timeSlotDates)
    {
        $checkEventBegin = PHP_INT_MAX;
        $checkEventEnd = 0;
        $checkEventUid = $this->subject->getUid();
        foreach ($timeSlotDates as list($beginDate, $endDate)) {
            $this->testingFramework->createRecord(
                'tx_seminars_timeslots',
                ['seminar' => $checkEventUid, 'begin_date' => $beginDate, 'end_date' => $endDate]
            );

            $checkEventBegin = min($checkEventBegin, $beginDate);
            $checkEventEnd = max($checkEventEnd, $endDate);
        }
        $this->subject->setNumberOfTimeSlots(count($timeSlotDates));
        $this->subject->setBeginDate($checkEventBegin);
        $this->subject->setEndDate($checkEventEnd);

        $registeredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $this->now, 'end_date' => $this->now + 500, 'timeslots' => 2]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now, 'end_date' => $this->now + 100]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $registeredEventUid, 'begin_date' => $this->now + 400, 'end_date' => $this->now + 500]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $registeredEventUid, 'user' => $userUid]
        );

        self::assertFalse($this->subject->isUserBlocked($userUid));
    }

    /*
     * Tests for the icons
     */

    /**
     * @test
     */
    public function usesCorrectIconForSingleEvent()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);

        self::assertContains(
            'EventComplete.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForTopic()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);

        self::assertContains(
            'EventTopic.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForDateRecord()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);

        self::assertContains(
            'EventDate.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForHiddenSingleEvent()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);
        $this->subject->setHidden(true);

        self::assertContains(
            'overlay-hidden.svg',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForHiddenTopic()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);
        $this->subject->setHidden(true);

        self::assertContains(
            'overlay-hidden.svg',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForHiddenDate()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);
        $this->subject->setHidden(true);

        self::assertContains(
            'overlay-hidden.svg',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForVisibleTimedSingleEvent()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);
        $this->subject->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertContains(
            'EventComplete.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForVisibleTimedTopic()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);
        $this->subject->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertContains(
            'EventTopic.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForVisibleTimedDate()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);
        $this->subject->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertContains(
            'EventDate.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForExpiredSingleEvent()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);
        $this->subject->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertContains('overlay-endtime.svg', $this->subject->getRecordIcon());
    }

    /**
     * @test
     */
    public function usesCorrectIconForExpiredTimedTopic()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);
        $this->subject->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertContains('overlay-endtime.svg', $this->subject->getRecordIcon());
    }

    /**
     * @test
     */
    public function usesCorrectIconForExpiredTimedDate()
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);
        $this->subject->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertContains('overlay-endtime.svg', $this->subject->getRecordIcon());
    }

    /*
     * Tests for hasSeparateDetailsPage
     */

    /**
     * @test
     */
    public function hasSeparateDetailsPageIsFalseByDefault()
    {
        self::assertFalse(
            $this->subject->hasSeparateDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasSeparateDetailsPageReturnsTrueForInternalSeparateDetailsPage()
    {
        $detailsPageUid = $this->testingFramework->createFrontEndPage();
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'details_page' => $detailsPageUid,
            ]
        );
        $event = new TestingEvent($eventUid);

        self::assertTrue(
            $event->hasSeparateDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasSeparateDetailsPageReturnsTrueForExternalSeparateDetailsPage()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'details_page' => 'www.test.com',
            ]
        );
        $event = new TestingEvent($eventUid);

        self::assertTrue(
            $event->hasSeparateDetailsPage()
        );
    }

    /*
     * Tests for getDetailsPage
     */

    /**
     * @test
     */
    public function getDetailsPageForNoSeparateDetailsPageSetReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function getDetailsPageForInternalSeparateDetailsPageSetReturnsThisPage()
    {
        $detailsPageUid = $this->testingFramework->createFrontEndPage();
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'details_page' => $detailsPageUid,
            ]
        );
        $event = new TestingEvent($eventUid);

        self::assertSame(
            (string)$detailsPageUid,
            $event->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function getDetailsPageForExternalSeparateDetailsPageSetReturnsThisPage()
    {
        $externalUrl = 'www.test.com';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'details_page' => $externalUrl,
            ]
        );
        $event = new TestingEvent($eventUid);

        self::assertSame(
            $externalUrl,
            $event->getDetailsPage()
        );
    }

    /*
     * Tests concerning getPlaceWithDetails
     */

    /**
     * @test
     */
    public function getPlaceWithDetailsReturnsWillBeAnnouncedForNoPlace()
    {
        $this->createPi1();
        self::assertContains(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsTitleOfOnePlace()
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place']);

        self::assertContains(
            'a place',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsTitleOfAllRelatedPlaces()
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertContains(
            'a place',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
        self::assertContains(
            'another place',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsAddressOfOnePlace()
    {
        $this->createPi1();
        $this->addPlaceRelation(
            ['title' => 'a place', 'address' => 'a street']
        );

        self::assertContains(
            'a street',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsForNonEmptyZipAndCityContainsZip()
    {
        $this->createPi1();
        $this->addPlaceRelation(
            ['title' => 'a place', 'zip' => '12345', 'city' => 'Hamm']
        );

        self::assertContains(
            '12345',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsForNonEmptyZipAndEmptyCityNotContainsZip()
    {
        $this->createPi1();
        $this->addPlaceRelation(
            ['title' => 'a place', 'zip' => '12345', 'city' => '']
        );

        self::assertNotContains(
            '12345',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsCityOfOnePlace()
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place', 'city' => 'Emden']);

        self::assertContains(
            'Emden',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsCountryOfOnePlace()
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place', 'country' => 'de']);

        self::assertContains(
            'Deutschland',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsHomepageLinkOfOnePlace()
    {
        $this->createPi1();
        $this->addPlaceRelation(['homepage' => 'www.test.com']);

        self::assertContains(
            ' href="http://www.test.com',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsDirectionsOfOnePlace()
    {
        $this->createPi1();
        $this->addPlaceRelation(['directions' => 'Turn right.']);

        self::assertContains(
            'Turn right.',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /*
     * Tests concerning getPlaceWithDetailsRaw
     */

    /**
     * @test
     */
    public function getPlaceWithDetailsRawReturnsWillBeAnnouncedForNoPlace()
    {
        $this->testingFramework->createFakeFrontEnd();

        self::assertContains(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsTitleOfOnePlace()
    {
        $this->addPlaceRelation(['title' => 'a place']);

        self::assertContains(
            'a place',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsTitleOfAllRelatedPlaces()
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertContains(
            'a place',
            $this->subject->getPlaceWithDetailsRaw()
        );
        self::assertContains(
            'another place',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsAddressOfOnePlace()
    {
        $this->addPlaceRelation(
            ['title' => 'a place', 'address' => 'a street']
        );

        self::assertContains(
            'a street',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsCityOfOnePlace()
    {
        $this->addPlaceRelation(['title' => 'a place', 'city' => 'Emden']);

        self::assertContains(
            'Emden',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsCountryOfOnePlace()
    {
        $this->addPlaceRelation(['title' => 'a place', 'country' => 'de']);

        self::assertContains(
            'Deutschland',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsHomepageUrlOfOnePlace()
    {
        $this->addPlaceRelation(['homepage' => 'www.test.com']);

        self::assertContains(
            'www.test.com',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsDirectionsOfOnePlace()
    {
        $this->addPlaceRelation(['directions' => 'Turn right.']);

        self::assertContains(
            'Turn right.',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawSeparatesMultiplePlacesWithLineFeeds()
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertContains(
            'a place' . LF . 'another place',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawDoesNotSeparateMultiplePlacesWithCarriageReturnsAndLineFeeds()
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertNotContains(
            'another place' . CRLF . 'a place',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /*
     * Tests for getPlaceShort
     */

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
        $this->addPlaceRelation(['title' => 'a place']);

        self::assertSame(
            'a place',
            $this->subject->getPlaceShort()
        );
    }

    /**
     * @test
     */
    public function getPlaceShortReturnsPlaceNamesWithCommaForTwoPlaces()
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertContains(
            'a place',
            $this->subject->getPlaceShort()
        );
        self::assertContains(
            ', ',
            $this->subject->getPlaceShort()
        );
        self::assertContains(
            'another place',
            $this->subject->getPlaceShort()
        );
    }

    /*
     * Tests concerning getPlaces
     */

    /**
     * @test
     */
    public function getPlacesForEventWithNoPlacesReturnsEmptyList()
    {
        self::assertInstanceOf(Collection::class, $this->subject->getPlaces());
    }

    /**
     * @test
     */
    public function getPlacesForSeminarWithOnePlacesReturnsListWithPlaceModel()
    {
        $this->addPlaceRelation();

        self::assertInstanceOf(\Tx_Seminars_Model_Place::class, $this->subject->getPlaces()->first());
    }

    /**
     * @test
     */
    public function getPlacesForSeminarWithOnePlacesReturnsListWithOnePlace()
    {
        $this->addPlaceRelation();

        self::assertSame(
            1,
            $this->subject->getPlaces()->count()
        );
    }

    /*
     * Tests concerning isOwnerFeUser
     */

    /**
     * @test
     */
    public function isOwnerFeUserForNoOwnerReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isOwnerFeUser()
        );
    }

    /**
     * @test
     */
    public function isOwnerFeUserForLoggedInUserOtherThanOwnerReturnsFalse()
    {
        $this->testingFramework->createFakeFrontEnd();
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setOwnerUid($userUid + 1);

        self::assertFalse(
            $this->subject->isOwnerFeUser()
        );
    }

    /**
     * @test
     */
    public function isOwnerFeUserForLoggedInUserOtherThanOwnerReturnsTrue()
    {
        $this->testingFramework->createFakeFrontEnd();
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setOwnerUid($ownerUid);

        self::assertTrue(
            $this->subject->isOwnerFeUser()
        );
    }

    /*
     * Tests concerning getOwner
     */

    /**
     * @test
     */
    public function getOwnerForExistingOwnerReturnsFrontEndUserInstance()
    {
        $this->testingFramework->createFakeFrontEnd();
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setOwnerUid($ownerUid);

        self::assertInstanceOf(FrontEndUser::class, $this->subject->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerForExistingOwnerReturnsUserWithOwnersUid()
    {
        $this->testingFramework->createFakeFrontEnd();
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setOwnerUid($ownerUid);

        self::assertSame(
            $ownerUid,
            $this->subject->getOwner()->getUid()
        );
    }

    /**
     * @test
     */
    public function getOwnerForNoOwnerReturnsNull()
    {
        self::assertNull(
            $this->subject->getOwner()
        );
    }

    /*
     * Tests concerning hasOwner
     */

    /**
     * @test
     */
    public function hasOwnerForExistingOwnerReturnsTrue()
    {
        $this->testingFramework->createFakeFrontEnd();
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setOwnerUid($ownerUid);

        self::assertTrue(
            $this->subject->hasOwner()
        );
    }

    /**
     * @test
     */
    public function hasOwnerForNoOwnerReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasOwner()
        );
    }

    /*
     * Tests concerning getVacanciesString
     */

    /**
     * @test
     */
    public function getVacanciesStringForCanceledEventWithVacanciesReturnsEmptyString()
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 10);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setAttendancesMax(5);
        $this->subject->setNumberOfAttendances(0);
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertSame('', $this->subject->getVacanciesString());
    }

    /**
     * @test
     */
    public function getVacanciesStringWithoutRegistrationNeededReturnsEmptyString()
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 10);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setNeedsRegistration(false);

        self::assertSame('', $this->subject->getVacanciesString());
    }

    /**
     * @test
     */
    public function getVacanciesStringForNonZeroVacanciesAndPastDeadlineReturnsEmptyString()
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 10);
        $this->subject->setAttendancesMax(5);
        $this->subject->setNumberOfAttendances(0);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setRegistrationDeadline($this->now - 10000);

        self::assertSame('', $this->subject->getVacanciesString());
    }

    /**
     * @test
     */
    public function getVacanciesStringForNonZeroVacanciesBelowThresholdReturnsNumberOfVacancies()
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 10);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setAttendancesMax(5);
        $this->subject->setNumberOfAttendances(0);

        self::assertSame(
            '5',
            $this->subject->getVacanciesString()
        );
    }

    /**
     * @test
     */
    public function getVacanciesStringForNoVancanciesReturnsFullyBooked()
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 10);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setAttendancesMax(5);
        $this->subject->setNumberOfAttendances(5);

        self::assertSame(
            $this->getLanguageService()->getLL('message_fullyBooked'),
            $this->subject->getVacanciesString()
        );
    }

    /**
     * @test
     */
    public function getVacanciesStringForVacanciesGreaterThanThresholdReturnsEnough()
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 10);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setAttendancesMax(42);
        $this->subject->setNumberOfAttendances(0);

        self::assertSame(
            $this->getLanguageService()->getLL('message_enough'),
            $this->subject->getVacanciesString()
        );
    }

    /**
     * @test
     */
    public function getVacanciesStringForVacanciesEqualToThresholdReturnsEnough()
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 42);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setAttendancesMax(42);
        $this->subject->setNumberOfAttendances(0);

        self::assertSame(
            $this->getLanguageService()->getLL('message_enough'),
            $this->subject->getVacanciesString()
        );
    }

    /**
     * @test
     */
    public function getVacanciesStringForUnlimitedVacanciesAndZeroRegistrationsReturnsEnough()
    {
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setNumberOfAttendances(0);

        self::assertSame(
            $this->getLanguageService()->getLL('message_enough'),
            $this->subject->getVacanciesString()
        );
    }

    /**
     * @test
     */
    public function getVacanciesStringForUnlimitedVacanciesAndOneRegistrationReturnsEnough()
    {
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setNumberOfAttendances(1);

        self::assertSame(
            $this->getLanguageService()->getLL('message_enough'),
            $this->subject->getVacanciesString()
        );
    }

    /*
     * Tests for the getImage function
     */

    /**
     * @test
     */
    public function getImageForNonEmptyImageReturnsImageFileName()
    {
        $this->subject->setImage('foo.gif');

        self::assertSame(
            'foo.gif',
            $this->subject->getImage()
        );
    }

    /**
     * @test
     */
    public function getImageForEmptyImageReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getImage()
        );
    }

    /*
     * Tests for the hasImage function
     */

    /**
     * @test
     */
    public function hasImageForNonEmptyImageReturnsTrue()
    {
        $this->subject->setImage('foo.gif');

        self::assertTrue(
            $this->subject->hasImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForEmptyImageReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasImage()
        );
    }

    /*
     * Tests for getLanguageKeySuffixForType
     */

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeReturnsSpeakerType()
    {
        $this->addLeaderRelation([]);

        self::assertContains(
            'leaders_',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMaleSpeakerReturnsMaleMarkerPart()
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertContains(
            '_male',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForFemaleSpeakerReturnsFemaleMarkerPart()
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );

        self::assertContains(
            '_female',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForSingleSpeakerWithoutGenderReturnsUnknownMarkerPart()
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN]
        );

        self::assertContains(
            '_unknown',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForSingleSpeakerReturnsSingleMarkerPart()
    {
        $this->addSpeakerRelation([]);

        self::assertContains(
            '_single_',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleSpeakersWithoutGenderReturnsSpeakerType()
    {
        $this->addSpeakerRelation([]);
        $this->addSpeakerRelation([]);

        self::assertContains(
            'speakers',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleMaleSpeakerReturnsMultipleAndMaleMarkerPart()
    {
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertContains(
            '_multiple_male',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleFemaleSpeakerReturnsMultipleAndFemaleMarkerPart()
    {
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );

        self::assertContains(
            '_multiple_female',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleSpeakersWithMixedGendersReturnsSpeakerType()
    {
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );

        self::assertContains(
            'speakers',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForOneSpeakerWithoutGenderAndOneWithGenderReturnsSpeakerType()
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN]
        );
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertContains(
            'leaders',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForSingleMaleTutorReturnsCorrespondingMarkerPart()
    {
        $this->addTutorRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertSame(
            'tutors_single_male',
            $this->subject->getLanguageKeySuffixForType('tutors')
        );
    }

    /*
     * Tests concerning hasRequirements
     */

    /**
     * @test
     */
    public function hasRequirementsForTopicWithoutRequirementsReturnsFalse()
    {
        $topic = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                    'requirements' => 0,
                ]
            )
        );

        self::assertFalse(
            $topic->hasRequirements()
        );
    }

    /**
     * @test
     */
    public function hasRequirementsForDateOfTopicWithoutRequirementsReturnsFalse()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 0,
            ]
        );
        $date = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        self::assertFalse(
            $date->hasRequirements()
        );
    }

    /**
     * @test
     */
    public function hasRequirementsForTopicWithOneRequirementReturnsTrue()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );
        $topic = new TestingEvent($topicUid);

        self::assertTrue(
            $topic->hasRequirements()
        );
    }

    /**
     * @test
     */
    public function hasRequirementsForDateOfTopicWithOneRequirementReturnsTrue()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );
        $date = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        self::assertTrue(
            $date->hasRequirements()
        );
    }

    /**
     * @test
     */
    public function hasRequirementsForTopicWithTwoRequirementsReturnsTrue()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid1,
            'requirements'
        );
        $requiredTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );
        $topic = new TestingEvent($topicUid);

        self::assertTrue(
            $topic->hasRequirements()
        );
    }

    /*
     * Tests concerning hasDependencies
     */

    /**
     * @test
     */
    public function hasDependenciesForTopicWithoutDependenciesReturnsFalse()
    {
        $topic = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                    'dependencies' => 0,
                ]
            )
        );

        self::assertFalse(
            $topic->hasDependencies()
        );
    }

    /**
     * @test
     */
    public function hasDependenciesForDateOfTopicWithoutDependenciesReturnsFalse()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 0,
            ]
        );
        $date = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        self::assertFalse(
            $date->hasDependencies()
        );
    }

    /**
     * @test
     */
    public function hasDependenciesForTopicWithOneDependencyReturnsTrue()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependentTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid,
            $topicUid
        );
        $topic = new TestingEvent($topicUid);

        self::assertTrue(
            $topic->hasDependencies()
        );
    }

    /**
     * @test
     */
    public function hasDependenciesForDateOfTopicWithOneDependencyReturnsTrue()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependentTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid,
            $topicUid
        );
        $date = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        self::assertTrue(
            $date->hasDependencies()
        );
    }

    /**
     * @test
     */
    public function hasDependenciesForTopicWithTwoDependenciesReturnsTrue()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 2,
            ]
        );
        $dependentTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid1,
            $topicUid
        );
        $dependentTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid2,
            $topicUid
        );

        $result = (new TestingEvent($topicUid))->hasDependencies();

        self::assertTrue(
            $result
        );
    }

    /*
     * Tests concerning getRequirements
     */

    /**
     * @test
     */
    public function getRequirementsReturnsSeminarBag()
    {
        self::assertInstanceOf(\Tx_Seminars_Bag_Event::class, $this->subject->getRequirements());
    }

    /**
     * @test
     */
    public function getRequirementsForNoRequirementsReturnsEmptyBag()
    {
        self::assertTrue(
            $this->subject->getRequirements()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getRequirementsForOneRequirementReturnsBagWithOneTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $result = (new TestingEvent($topicUid))->getRequirements();

        self::assertSame(
            1,
            $result->count()
        );
        self::assertSame(
            $requiredTopicUid,
            $result->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function getRequirementsForDateOfTopicWithOneRequirementReturnsBagWithOneTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );
        $date = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        $result = $date->getRequirements();

        self::assertSame(
            1,
            $result->count()
        );
        self::assertSame(
            $requiredTopicUid,
            $result->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function getRequirementsForTwoRequirementsReturnsBagWithTwoItems()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid1,
            'requirements'
        );
        $requiredTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );

        $requirements = (new TestingEvent($topicUid))->getRequirements();

        self::assertSame(
            2,
            $requirements->count()
        );
    }

    /*
     * Tests concerning getDependencies
     */

    /**
     * @test
     */
    public function getDependenciesReturnsSeminarBag()
    {
        self::assertInstanceOf(\Tx_Seminars_Bag_Event::class, $this->subject->getDependencies());
    }

    /**
     * @test
     */
    public function getDependenciesForNoDependenciesReturnsEmptyBag()
    {
        self::assertTrue(
            $this->subject->getDependencies()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getDependenciesForOneDependencyReturnsBagWithOneTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependentTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid,
            $topicUid
        );

        $result = (new TestingEvent($topicUid))->getDependencies();

        self::assertSame(
            1,
            $result->count()
        );
        self::assertSame(
            $dependentTopicUid,
            $result->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function getDependenciesForDateOfTopicWithOneDependencyReturnsBagWithOneTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependentTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid,
            $topicUid
        );
        $date = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        $result = $date->getDependencies();

        self::assertSame(
            1,
            $result->count()
        );
        self::assertSame(
            $dependentTopicUid,
            $result->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function getDependenciesForTwoDependenciesReturnsBagWithTwoItems()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 2,
            ]
        );
        $dependentTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid1,
            $topicUid
        );
        $dependentTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'requirements' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependentTopicUid2,
            $topicUid
        );

        $dependencies = (new TestingEvent($topicUid))->getDependencies();

        self::assertSame(
            2,
            $dependencies->count()
        );
    }

    /*
     * Tests concerning isConfirmed
     */

    /**
     * @test
     */
    public function isConfirmedForStatusPlannedReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse(
            $this->subject->isConfirmed()
        );
    }

    /**
     * @test
     */
    public function isConfirmedForStatusConfirmedReturnsTrue()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertTrue(
            $this->subject->isConfirmed()
        );
    }

    /**
     * @test
     */
    public function isConfirmedForStatusCanceledReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->isConfirmed()
        );
    }

    /*
     * Tests concerning isCanceled
     */

    /**
     * @test
     */
    public function isCanceledForPlannedEventReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse(
            $this->subject->isCanceled()
        );
    }

    /**
     * @test
     */
    public function isCanceledForCanceledEventReturnsTrue()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertTrue(
            $this->subject->isCanceled()
        );
    }

    /**
     * @test
     */
    public function isCanceledForConfirmedEventReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse(
            $this->subject->isCanceled()
        );
    }

    /*
     * Tests concerning isPlanned
     */

    /**
     * @test
     */
    public function isPlannedForStatusPlannedReturnsTrue()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertTrue(
            $this->subject->isPlanned()
        );
    }

    /**
     * @test
     */
    public function isPlannedForStatusConfirmedReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse(
            $this->subject->isPlanned()
        );
    }

    /**
     * @test
     */
    public function isPlannedForStatusCanceledReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->isPlanned()
        );
    }

    /*
     * Tests concerning setEventTakesPlaceReminderSentFlag
     */

    /**
     * @test
     */
    public function setEventTakesPlaceReminderSentFlagSetsFlagToTrue()
    {
        $this->subject->setEventTakesPlaceReminderSentFlag();

        self::assertTrue(
            $this->subject->getRecordPropertyBoolean(
                'event_takes_place_reminder_sent'
            )
        );
    }

    /*
     * Tests concerning setCancelationDeadlineReminderSentFlag
     */

    /**
     * @test
     */
    public function setCancellationDeadlineReminderSentFlagToTrue()
    {
        $this->subject->setCancelationDeadlineReminderSentFlag();

        self::assertTrue(
            $this->subject->getRecordPropertyBoolean(
                'cancelation_deadline_reminder_sent'
            )
        );
    }

    /*
     * Tests concerning getCancelationDeadline
     */

    /**
     * @test
     */
    public function getCancellationDeadlineForEventWithoutSpeakerReturnsBeginDateOfEvent()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);

        self::assertSame(
            $this->subject->getBeginDateAsTimestamp(),
            $this->subject->getCancelationDeadline()
        );
    }

    /**
     * @test
     */
    public function getCancellationDeadlineForEventWithSpeakerWithoutCancellationPeriodReturnsBeginDateOfEvent()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->addSpeakerRelation(['cancelation_period' => 0]);

        self::assertSame(
            $this->subject->getBeginDateAsTimestamp(),
            $this->subject->getCancelationDeadline()
        );
    }

    /**
     * @test
     */
    public function getCancellationDeadlineForEventWithTwoSpeakersWithoutCancellationPeriodReturnsBeginDateOfEvent()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->addSpeakerRelation(['cancelation_period' => 0]);
        $this->addSpeakerRelation(['cancelation_period' => 0]);

        self::assertSame(
            $this->subject->getBeginDateAsTimestamp(),
            $this->subject->getCancelationDeadline()
        );
    }

    /**
     * @test
     */
    public function getCancellationDeadlineForEventWithOneSpeakersWithCancellationPeriodReturnsBeginDateMinusCancelationPeriod()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->addSpeakerRelation(['cancelation_period' => 1]);

        self::assertSame(
            $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            $this->subject->getCancelationDeadline()
        );
    }

    /**
     * @test
     */
    public function getCancellationDeadlineForEventWithTwoSpeakersWithCancellationPeriodsReturnsBeginDateMinusBiggestCancelationPeriod()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->addSpeakerRelation(['cancelation_period' => 21]);
        $this->addSpeakerRelation(['cancelation_period' => 42]);

        self::assertSame(
            $GLOBALS['SIM_EXEC_TIME'] - (42 * Time::SECONDS_PER_DAY),
            $this->subject->getCancelationDeadline()
        );
    }

    /**
     * @test
     */
    public function getCancellationDeadlineForEventWithoutBeginDateThrowsException()
    {
        $this->subject->setBeginDate(0);

        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'The event has no begin date. Please call this function only if the event has a begin date.'
        );

        $this->subject->getCancelationDeadline();
    }

    /*
     * Tests concerning the license expiry
     */

    /**
     * @test
     */
    public function hasExpiryForNoExpiryReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasExpiry()
        );
    }

    /**
     * @test
     */
    public function hasExpiryForNonZeroExpiryReturnsTrue()
    {
        $this->subject->setExpiry(42);

        self::assertTrue(
            $this->subject->hasExpiry()
        );
    }

    /**
     * @test
     */
    public function getExpiryForNoExpiryReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getExpiry()
        );
    }

    /**
     * @test
     */
    public function getExpiryForNonZeroExpiryReturnsFormattedDate()
    {
        $this->subject->setExpiry(mktime(0, 0, 0, 12, 31, 2000));

        self::assertSame(
            '31.12.2000',
            $this->subject->getExpiry()
        );
    }

    /*
     * Tests concerning getEventData
     */

    /**
     * @test
     */
    public function getEventDataReturnsFormattedUnregistrationDeadline()
    {
        $this->subject->setUnregistrationDeadline(1893488400);
        $this->subject->setShowTimeOfUnregistrationDeadline(0);
        self::assertSame(
            '01.01.2030',
            $this->subject->getEventData('deadline_unregistration')
        );
    }

    /**
     * @test
     */
    public function getEventDataForShowTimeOfUnregistrationDeadlineTrueReturnsFormattedUnregistrationDeadlineWithTime()
    {
        $this->subject->setUnregistrationDeadline(1893488400);
        $this->subject->setShowTimeOfUnregistrationDeadline(1);

        self::assertSame('01.01.2030 09:00', $this->subject->getEventData('deadline_unregistration'));
    }

    /**
     * @test
     */
    public function getEventDataForUnregistrationDeadlineZeroReturnsEmptyString()
    {
        $this->subject->setUnregistrationDeadline(0);
        self::assertSame(
            '',
            $this->subject->getEventData('deadline_unregistration')
        );
    }

    /**
     * @test
     */
    public function getEventDataForEventWithMultipleLodgingsSeparatesLodgingsWithLineFeeds()
    {
        $lodgingUid1 = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['title' => 'foo']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_lodgings_mm',
            $this->subject->getUid(),
            $lodgingUid1
        );

        $lodgingUid2 = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['title' => 'bar']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_lodgings_mm',
            $this->subject->getUid(),
            $lodgingUid2
        );

        $this->subject->setNumberOfLodgings(2);

        self::assertContains(
            'foo' . LF . 'bar',
            $this->subject->getEventData('lodgings')
        );
    }

    /**
     * @test
     */
    public function getEventDataForEventWithMultipleLodgingsDoesNotSeparateLodgingsWithCarriageReturnsAndLineFeeds()
    {
        $lodgingUid1 = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['title' => 'foo']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_lodgings_mm',
            $this->subject->getUid(),
            $lodgingUid1
        );

        $lodgingUid2 = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['title' => 'bar']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_lodgings_mm',
            $this->subject->getUid(),
            $lodgingUid2
        );

        $this->subject->setNumberOfLodgings(2);

        self::assertNotContains(
            'foo' . CRLF . 'bar',
            $this->subject->getEventData('lodgings')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithCarriageReturnAndLinefeedGetsConvertedToLineFeedOnly()
    {
        $this->subject->setDescription('foo' . CRLF . 'bar');

        self::assertContains(
            'foo' . LF . 'bar',
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithTwoAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed()
    {
        $this->subject->setDescription('foo' . LF . LF . 'bar');

        self::assertContains(
            'foo' . LF . 'bar',
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithThreeAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed()
    {
        $this->subject->setDescription('foo' . LF . LF . LF . 'bar');

        self::assertContains(
            'foo' . LF . 'bar',
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithFourAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed()
    {
        $this->subject->setDescription('foo' . LF . LF . LF . LF . 'bar');

        self::assertContains(
            'foo' . LF . 'bar',
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataForEventWithDateUsesHyphenAsDateSeparator()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY);

        self::assertContains(
            '-',
            $this->subject->getEventData('date')
        );
    }

    /**
     * @test
     */
    public function getEventDataForEventWithTimeUsesHyphenAsTimeSeparator()
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

        self::assertContains(
            '-',
            $this->subject->getEventData('time')
        );
    }

    /**
     * @test
     */
    public function getEventDataSeparatesPlacePartsByCommaAndSpace()
    {
        $place = [
            'title' => 'Hotel Ibis',
            'homepage' => '',
            'address' => 'Kaiser-Karl-Ring 91',
            'city' => 'Bonn',
            'country' => '',
            'directions' => '',
        ];

        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['getPlacesAsArray', 'hasPlace']);
        $subject->method('getPlacesAsArray')->willReturn([$place]);
        $subject->method('hasPlace')->willReturn(true);

        self::assertSame(
            'Hotel Ibis, Kaiser-Karl-Ring 91, Bonn',
            $subject->getEventData('place')
        );
    }

    /**
     * @test
     */
    public function getEventDataSeparatesTwoPlacesByLineFeed()
    {
        $place1 = [
            'title' => 'Hotel Ibis',
            'homepage' => '',
            'address' => '',
            'city' => '',
            'country' => '',
            'directions' => '',
        ];
        $place2 = [
            'title' => 'Wasserwerk',
            'homepage' => '',
            'address' => '',
            'city' => '',
            'country' => '',
            'directions' => '',
        ];

        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['getPlacesAsArray', 'hasPlace']);
        $subject->method('getPlacesAsArray')->willReturn([$place1, $place2]);
        $subject->method('hasPlace')->willReturn(true);

        self::assertSame(
            'Hotel Ibis' . LF . 'Wasserwerk',
            $subject->getEventData('place')
        );
    }

    /**
     * @test
     */
    public function getEventDataForPlaceWithoutZipContainsTitleAndAddressAndCity()
    {
        $place = [
            'title' => 'Hotel Ibis',
            'address' => 'Kaiser-Karl-Ring 91',
            'zip' => '',
            'city' => 'Bonn',
        ];

        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['getPlacesAsArray', 'hasPlace']);
        $subject->method('getPlacesAsArray')->willReturn([$place]);
        $subject->method('hasPlace')->willReturn(true);

        self::assertSame(
            'Hotel Ibis, Kaiser-Karl-Ring 91, Bonn',
            $subject->getEventData('place')
        );
    }

    /**
     * @test
     */
    public function getEventDataForPlaceWithZipContainsTitleAndAddressAndZipAndCity()
    {
        $place = [
            'title' => 'Hotel Ibis',
            'address' => 'Kaiser-Karl-Ring 91',
            'zip' => '53111',
            'city' => 'Bonn',
        ];

        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['getPlacesAsArray', 'hasPlace']);
        $subject->method('getPlacesAsArray')->willReturn([$place]);
        $subject->method('hasPlace')->willReturn(true);

        self::assertSame(
            'Hotel Ibis, Kaiser-Karl-Ring 91, 53111 Bonn',
            $subject->getEventData('place')
        );
    }

    /*
     * Tests concerning dumpSeminarValues
     */

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleGivenReturnsTitle()
    {
        self::assertContains(
            $this->subject->getTitle(),
            $this->subject->dumpSeminarValues('title')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleGivenReturnsLabelForTitle()
    {
        self::assertContains(
            $this->getLanguageService()->getLL('label_title'),
            $this->subject->dumpSeminarValues('title')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleGivenReturnsTitleWithLineFeedAtEndOfLine()
    {
        self::assertRegExp(
            '/\\n$/',
            $this->subject->dumpSeminarValues('title')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleAndDescriptionGivenReturnsTitleAndDescription()
    {
        $this->subject->setDescription('foo bar');

        self::assertRegExp(
            '/.*' . $this->subject->getTitle() . '.*\\n.*' .
            $this->subject->getRecordPropertyString('description') . '/',
            $this->subject->dumpSeminarValues('title,description')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForEventWithoutDescriptionAndDescriptionGivenReturnsDescriptionLabelWithColonsAndLineFeed()
    {
        $this->subject->setDescription('');

        self::assertSame(
            $this->getLanguageService()->getLL('label_description') . ':' . LF,
            $this->subject->dumpSeminarValues('description')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForEventWithNoVacanciesAndVacanciesGivenReturnsVacanciesLabelWithNumber()
    {
        $this->subject->setNumberOfAttendances(2);
        $this->subject->setAttendancesMax(2);
        $this->subject->setNeedsRegistration(true);

        self::assertSame(
            $this->getLanguageService()->getLL('label_vacancies') . ': 0' . LF,
            $this->subject->dumpSeminarValues('vacancies')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForEventWithOneVacancyAndVacanciesGivenReturnsNumberOfVacancies()
    {
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setAttendancesMax(2);
        $this->subject->setNeedsRegistration(true);

        self::assertSame(
            $this->getLanguageService()->getLL('label_vacancies') . ': 1' . LF,
            $this->subject->dumpSeminarValues('vacancies')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForEventWithUnlimitedVacanciesAndVacanciesGivenReturnsVacanciesUnlimitedString()
    {
        $this->subject->setUnlimitedVacancies();

        self::assertSame(
            $this->getLanguageService()->getLL('label_vacancies') . ': ' .
            $this->getLanguageService()->getLL('label_unlimited') . LF,
            $this->subject->dumpSeminarValues('vacancies')
        );
    }

    /**
     * @return string[][]
     */
    public function dumpableEventFieldsDataProvider(): array
    {
        $fields = [
            'uid',
            'event_type',
            'title',
            'subtitle',
            'titleanddate',
            'date',
            'time',
            'accreditation_number',
            'credit_points',
            'room',
            'place',
            'speakers',
            'price_regular',
            'price_regular_early',
            'price_special',
            'price_special_early',
            'allows_multiple_registrations',
            'attendees',
            'attendees_min',
            'attendees_max',
            'vacancies',
            'enough_attendees',
            'is_full',
            'notes',
        ];

        $result = [];
        foreach ($fields as $field) {
            $result[$field] = [$field];
        }

        return $result;
    }

    /**
     * @test
     *
     * @param string $fieldName
     *
     * @dataProvider dumpableEventFieldsDataProvider
     */
    public function dumpSeminarValuesCreatesNoDoubleColonsAfterLabel(string $fieldName)
    {
        $this->subject->setRecordPropertyString($fieldName, '1234 some value');

        $result = $this->subject->dumpSeminarValues($fieldName);

        self::assertNotContains('::', $result);
    }

    /*
     * Tests regarding the registration begin date
     */

    /**
     * @test
     */
    public function hasRegistrationBeginForNoRegistrationBeginReturnsFalse()
    {
        $this->subject->setRegistrationBeginDate(0);

        self::assertFalse(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationBeginForEventWithRegistrationBeginReturnsTrue()
    {
        $this->subject->setRegistrationBeginDate(42);

        self::assertTrue(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithoutRegistrationBeginReturnsZero()
    {
        $this->subject->setRegistrationBeginDate(0);

        self::assertSame(
            0,
            $this->subject->getRegistrationBeginAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithRegistrationBeginReturnsRegistrationBeginAsUnixTimestamp()
    {
        $this->subject->setRegistrationBeginDate(42);

        self::assertSame(
            42,
            $this->subject->getRegistrationBeginAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginForEventWithoutRegistrationBeginReturnsEmptyString()
    {
        $this->subject->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
        $this->subject->setConfigurationValue('timeFormat', '%H:%M');

        $this->subject->setRegistrationBeginDate(0);

        self::assertSame(
            '',
            $this->subject->getRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginForEventWithRegistrationBeginReturnsFormattedRegistrationBegin()
    {
        $this->subject->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
        $this->subject->setConfigurationValue('timeFormat', '%H:%M');

        $this->subject->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME']);

        self::assertSame(
            strftime('%d.%m.%Y %H:%M', $GLOBALS['SIM_EXEC_TIME']),
            $this->subject->getRegistrationBegin()
        );
    }

    /*
     * Tests regarding the description.
     */

    /**
     * @test
     */
    public function getDescriptionWithoutDescriptionReturnEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription()
    {
        $this->subject->setDescription('this is a great event.');

        self::assertSame(
            'this is a great event.',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithoutDescriptionReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue()
    {
        $this->subject->setDescription('this is a great event.');

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    /*
     * Tests regarding the additional information.
     */

    /**
     * @test
     */
    public function getAdditionalInformationWithoutAdditionalInformationReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function setAdditionalInformationSetsAdditionalInformation()
    {
        $this->subject->setAdditionalInformation('this is good to know');

        self::assertSame(
            'this is good to know',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationWithoutAdditionalInformationReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationWithAdditionalInformationReturnsTrue()
    {
        $this->subject->setAdditionalInformation('this is good to know');

        self::assertTrue(
            $this->subject->hasAdditionalInformation()
        );
    }

    /*
     * Tests concerning getLatestPossibleRegistrationTime
     */

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithoutAnyDatesReturnsZero()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );
        $subject = new TestingEvent($uid);

        self::assertSame(
            0,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateReturnsBeginDate()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => $this->now,
                'end_date' => 0,
            ]
        );
        $subject = new TestingEvent($uid);

        self::assertSame(
            $this->now,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineReturnsRegistrationDeadline()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => $this->now,
                'begin_date' => $this->now + 1000,
                'end_date' => 0,
            ]
        );
        $subject = new TestingEvent($uid);

        self::assertSame(
            $this->now,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithBeginAndEndDateAndRegistrationForStartedEventsAllowedReturnsEndDate()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => $this->now,
                'end_date' => $this->now + 1000,
            ]
        );
        $subject = new TestingEvent($uid);
        $subject->overrideConfiguration(['allowRegistrationForStartedEvents' => 1]);

        self::assertSame(
            $this->now + 1000,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineAndRegistrationForStartedEventsAllowedReturnsRegistrationDeadline()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => $this->now - 1000,
                'begin_date' => $this->now,
                'end_date' => $this->now + 1000,
            ]
        );
        $subject = new TestingEvent($uid);
        $subject->overrideConfiguration(['allowRegistrationForStartedEvents' => 1]);

        self::assertSame(
            $this->now - 1000,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndWithoutEndDateAndRegistrationForStartedEventsAllowedReturnsBeginDate()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => $this->now,
                'end_date' => 0,
            ]
        );
        $subject = new TestingEvent($uid);
        $subject->overrideConfiguration(['allowRegistrationForStartedEvents' => 1]);

        self::assertSame(
            $this->now,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /*
     * Tests concerning getTopicInteger
     */

    /**
     * @test
     */
    public function getTopicIntegerForSingleEventReturnsDataFromRecord()
    {
        $this->subject->setRecordPropertyInteger('credit_points', 42);

        self::assertSame(
            42,
            $this->subject->getTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function getTopicIntegerForDateReturnsDataFromTopic()
    {
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'credit_points' => 42,
            ]
        );
        $dateRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicRecordUid,
            ]
        );

        $date = new TestingEvent($dateRecordUid);

        self::assertSame(
            42,
            $date->getTopicInteger('credit_points')
        );
    }

    /*
     * Tests concerning hasTopicInteger
     */

    /**
     * @test
     */
    public function hasTopicIntegerForSingleEventForZeroReturnsFalse()
    {
        $this->subject->setRecordPropertyInteger('credit_points', 0);

        self::assertFalse(
            $this->subject->hasTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function hasTopicIntegerForSingleEventForPositiveIntegerReturnsFalse()
    {
        $this->subject->setRecordPropertyInteger('credit_points', 1);

        self::assertTrue(
            $this->subject->hasTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function hasTopicIntegerForSingleEventForNegativeIntegerReturnsFalse()
    {
        $this->subject->setRecordPropertyInteger('credit_points', -1);

        self::assertTrue(
            $this->subject->hasTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function hasTopicIntegerForDateForZeroInTopicReturnsFalse()
    {
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'credit_points' => 0,
            ]
        );
        $dateRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicRecordUid,
            ]
        );

        $date = new TestingEvent($dateRecordUid);

        self::assertFalse(
            $date->hasTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function hasTopicIntegerForDateForPositiveIntegerInTopicReturnsTrue()
    {
        $topicRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'credit_points' => 1,
            ]
        );
        $dateRecordUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicRecordUid,
            ]
        );

        $date = new TestingEvent($dateRecordUid);

        self::assertTrue(
            $date->hasTopicInteger('credit_points')
        );
    }

    /*
     * Tests concerning the publication status
     */

    /**
     * @test
     */
    public function getPublicationHashReturnsPublicationHash()
    {
        $this->subject->setRecordPropertyString(
            'publication_hash',
            '5318761asdf35as5sad35asd35asd'
        );

        self::assertSame(
            '5318761asdf35as5sad35asd35asd',
            $this->subject->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function setPublicationHashSetsPublicationHash()
    {
        $this->subject->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertSame(
            '5318761asdf35as5sad35asd35asd',
            $this->subject->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithoutPublicationHashIsTrue()
    {
        $this->subject->setPublicationHash('');

        self::assertTrue(
            $this->subject->isPublished()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithPublicationHashIsFalse()
    {
        $this->subject->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertFalse(
            $this->subject->isPublished()
        );
    }

    /*
     * Tests concerning canViewRegistrationsList
     */

    /**
     * Data provider for testing the canViewRegistrationsList function
     * with default access and access only for attendees and managers.
     *
     * @return mixed[][] test data for canViewRegistrationsList with each row
     *               having the following elements:
     *               [expected] boolean: expected value (TRUE or FALSE)
     *               [loggedIn] boolean: whether a user is logged in
     *               [isRegistered] boolean: whether the logged-in user is
     *                              registered for that event
     *               [isVip] boolean: whether the logged-in user is a VIP
     *                                that event
     *               [whichPlugin] string: value for that parameter
     *               [registrationsListPID] integer: value for that parameter
     *               [registrationsVipListPID] integer: value for that parameter
     */
    public function canViewRegistrationsListDataProvider(): array
    {
        return [
            'seminarListWithNothingElse' => [
                'expected' => false,
                'loggedIn' => false,
                'isRegistered' => false,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'seminarListLoggedInWithListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsRegisteredWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsRegisteredWithoutListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsVipWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => true,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 1,
            ],
            'seminarListIsVipWithoutListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'myEventsIsRegisteredWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'my_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myEventsIsVipWithListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'my_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myVipEventsIsRegisteredWithListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'my_vip_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myVipEventsIsVipWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'my_vip_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'listRegistrationsIsRegistered' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'list_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listRegistrationsIsVip' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'list_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listVipRegistrationsIsRegistered' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'list_vip_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listVipRegistrationsIsVip' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'list_vip_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider canViewRegistrationsListDataProvider
     *
     * @param bool $expected
     * @param bool $loggedIn
     * @param bool $isRegistered
     * @param bool $isVip
     * @param string $whichPlugin
     * @param int $registrationsListPID
     * @param int $registrationsVipListPID
     *
     * @return void
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndDefaultAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ) {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'isUserRegistered', 'isUserVip']
        );
        $subject->method('needsRegistration')
            ->willReturn(true);
        $subject->method('isUserRegistered')
            ->willReturn($isRegistered);
        $subject->method('isUserVip')
            ->willReturn($isVip);

        if ($loggedIn) {
            $this->testingFramework->createFakeFrontEnd();
            $this->testingFramework->createAndLoginFrontEndUser();
        }

        self::assertSame(
            $expected,
            $subject->canViewRegistrationsList(
                $whichPlugin,
                $registrationsListPID,
                $registrationsVipListPID
            )
        );
    }

    /**
     * @test
     *
     * @dataProvider canViewRegistrationsListDataProvider
     *
     * @param bool $expected
     * @param bool $loggedIn
     * @param bool $isRegistered
     * @param bool $isVip
     * @param string $whichPlugin
     * @param int $registrationsListPID
     * @param int $registrationsVipListPID
     *
     * @return void
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndAttendeesManagersAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ) {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'isUserRegistered', 'isUserVip']
        );
        $subject->method('needsRegistration')
            ->willReturn(true);
        $subject->method('isUserRegistered')
            ->willReturn($isRegistered);
        $subject->method('isUserVip')
            ->willReturn($isVip);

        if ($loggedIn) {
            $this->testingFramework->createFakeFrontEnd();
            $this->testingFramework->createAndLoginFrontEndUser();
        }

        self::assertSame(
            $expected,
            $subject->canViewRegistrationsList(
                $whichPlugin,
                $registrationsListPID,
                $registrationsVipListPID
            )
        );
    }

    /**
     * Data provider for the canViewRegistrationsForCsvExportListDataProvider
     * test.
     *
     * @return bool[][] test data for canViewRegistrationsList with each row
     *               having the following elements:
     *               [expected] boolean: expected value (TRUE or FALSE)
     *               [loggedIn] boolean: whether a user is logged in
     *               [isVip] boolean: whether the logged-in user is a VIP
     *                                that event
     *               [allowCsvExportForVips] boolean: that configuration value
     */
    public function canViewRegistrationsForCsvExportListDataProvider(): array
    {
        return [
            'notLoggedInButCsvExportAllowed' => [
                'expected' => false,
                'loggedIn' => false,
                'isVip' => false,
                'allowCsvExportForVips' => true,
            ],
            'loggedInAndCsvExportAllowedButNoVip' => [
                'expected' => false,
                'loggedIn' => true,
                'isVip' => false,
                'allowCsvExportForVips' => true,
            ],
            'loggedInAndCsvExportAllowedAndVip' => [
                'expected' => true,
                'loggedIn' => true,
                'isVip' => true,
                'allowCsvExportForVips' => true,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider canViewRegistrationsForCsvExportListDataProvider
     *
     * @param bool $expected
     * @param bool $loggedIn
     * @param bool $isVip
     * @param bool $allowCsvExportForVips
     *
     * @return void
     */
    public function canViewRegistrationsListForCsvExport(
        bool $expected,
        bool $loggedIn,
        bool $isVip,
        bool $allowCsvExportForVips
    ) {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration', 'isUserVip']);
        $subject->method('needsRegistration')
            ->willReturn(true);
        $subject->method('isUserVip')
            ->willReturn($isVip);
        $subject->init(
            ['allowCsvExportForVips' => $allowCsvExportForVips]
        );

        if ($loggedIn) {
            $this->testingFramework->createFakeFrontEnd();
            $this->testingFramework->createAndLoginFrontEndUser();
        }

        self::assertSame(
            $expected,
            $subject->canViewRegistrationsList('csv_export')
        );
    }

    /**
     * Data provider for testing the canViewRegistrationsList function
     * with login access.
     *
     * @return mixed[][] test data for canViewRegistrationsList with each row
     *               having the following elements:
     *               [expected] boolean: expected value (TRUE or FALSE)
     *               [loggedIn] boolean: whether a user is logged in
     *               [isRegistered] boolean: whether the logged-in user is
     *                              registered for that event
     *               [isVip] boolean: whether the logged-in user is a VIP
     *                                that event
     *               [whichPlugin] string: value for that parameter
     *               [registrationsListPID] integer: value for that parameter
     *               [registrationsVipListPID] integer: value for that parameter
     */
    public function canViewRegistrationsListDataProviderForLoggedIn(): array
    {
        return [
            'seminarListWithNothingElse' => [
                'expected' => false,
                'loggedIn' => false,
                'isRegistered' => false,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'seminarListLoggedInWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsRegisteredWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsRegisteredWithoutListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsVipWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => true,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsVipWithVipListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => true,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 1,
            ],
            'seminarListIsVipWithoutListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'myEventsIsRegisteredWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'my_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myEventsIsVipWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'my_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myVipEventsIsRegisteredWithListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'my_vip_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myVipEventsIsVipWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'my_vip_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'listRegistrationsIsRegistered' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'list_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listRegistrationsIsVip' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'list_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listVipRegistrationsIsRegistered' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'list_vip_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listVipRegistrationsIsVip' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'list_vip_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider canViewRegistrationsListDataProviderForLoggedIn
     *
     * @param bool $expected
     * @param bool $loggedIn
     * @param bool $isRegistered
     * @param bool $isVip
     * @param string $whichPlugin
     * @param int $registrationsListPID
     * @param int $registrationsVipListPID
     *
     * @return void
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndLoginAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ) {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'isUserRegistered', 'isUserVip']
        );
        $subject->method('needsRegistration')
            ->willReturn(true);
        $subject->method('isUserRegistered')
            ->willReturn($isRegistered);
        $subject->method('isUserVip')
            ->willReturn($isVip);

        if ($loggedIn) {
            $this->testingFramework->createFakeFrontEnd();
            $this->testingFramework->createAndLoginFrontEndUser();
        }

        self::assertSame(
            $expected,
            $subject->canViewRegistrationsList(
                $whichPlugin,
                $registrationsListPID,
                $registrationsVipListPID,
                0,
                'login'
            )
        );
    }

    /**
     * Data provider for testing the canViewRegistrationsList function
     * with world access.
     *
     * @return mixed[][] test data for canViewRegistrationsList with each row
     *               having the following elements:
     *               [expected] boolean: expected value (TRUE or FALSE)
     *               [loggedIn] boolean: whether a user is logged in
     *               [isRegistered] boolean: whether the logged-in user is
     *                              registered for that event
     *               [isVip] boolean: whether the logged-in user is a VIP
     *                                that event
     *               [whichPlugin] string: value for that parameter
     *               [registrationsListPID] integer: value for that parameter
     *               [registrationsVipListPID] integer: value for that parameter
     */
    public function canViewRegistrationsListDataProviderForWorld(): array
    {
        return [
            'seminarListWithNothingElse' => [
                'expected' => false,
                'loggedIn' => false,
                'isRegistered' => false,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'seminarListWithListPid' => [
                'expected' => true,
                'loggedIn' => false,
                'isRegistered' => false,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListLoggedInWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsRegisteredWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsRegisteredWithoutListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'seminarListIsVipWithListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => true,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 1,
            ],
            'seminarListIsVipWithoutListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'seminar_list',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'myEventsIsRegisteredWithListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'my_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myEventsIsVipWithVipListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'my_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myVipEventsIsRegisteredWithVipListPid' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'my_vip_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'myVipEventsIsVipWithVipListPid' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'my_vip_events',
                'registrationsListPID' => 1,
                'registrationsVipListPID' => 1,
            ],
            'listRegistrationsIsRegistered' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'list_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listRegistrationsIsVip' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'list_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listVipRegistrationsIsRegistered' => [
                'expected' => false,
                'loggedIn' => true,
                'isRegistered' => true,
                'isVip' => false,
                'whichPlugin' => 'list_vip_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
            'listVipRegistrationsIsVip' => [
                'expected' => true,
                'loggedIn' => true,
                'isRegistered' => false,
                'isVip' => true,
                'whichPlugin' => 'list_vip_registrations',
                'registrationsListPID' => 0,
                'registrationsVipListPID' => 0,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider canViewRegistrationsListDataProviderForWorld
     *
     * @param bool $expected
     * @param bool $loggedIn
     * @param bool $isRegistered
     * @param bool $isVip
     * @param string $whichPlugin
     * @param int $registrationsListPID
     * @param int $registrationsVipListPID
     *
     * @return void
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndWorldAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ) {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'isUserRegistered', 'isUserVip']
        );
        $subject->method('needsRegistration')
            ->willReturn(true);
        $subject->method('isUserRegistered')
            ->willReturn($isRegistered);
        $subject->method('isUserVip')
            ->willReturn($isVip);

        if ($loggedIn) {
            $this->testingFramework->createFakeFrontEnd();
            $this->testingFramework->createAndLoginFrontEndUser();
        }

        self::assertSame(
            $expected,
            $subject->canViewRegistrationsList(
                $whichPlugin,
                $registrationsListPID,
                $registrationsVipListPID,
                0,
                'world'
            )
        );
    }

    /*
     * Tests concerning canViewRegistrationsListMessage
     */

    /**
     * @test
     */
    public function canViewRegistrationsListMessageWithoutNeededRegistrationReturnsNoRegistrationMessage()
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration']);
        $subject->method('needsRegistration')->willReturn(false);
        $subject->init();

        self::assertSame(
            $this->getLanguageService()->getLL('message_noRegistrationNecessary'),
            $subject->canViewRegistrationsListMessage('list_registrations')
        );
    }

    /**
     * @test
     */
    public function canViewRegistrationsListMessageForListAndNoLoginAndAttendeesAccessReturnsPleaseLoginMessage()
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration']);
        $subject->method('needsRegistration')->willReturn(true);
        $subject->init();

        self::assertSame(
            $this->getLanguageService()->getLL('message_notLoggedIn'),
            $subject->canViewRegistrationsListMessage('list_registrations')
        );
    }

    /**
     * @test
     */
    public function canViewRegistrationsListMessageForListAndNoLoginAndLoginAccessReturnsPleaseLoginMessage()
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration']);
        $subject->method('needsRegistration')->willReturn(true);
        $subject->init();

        self::assertSame(
            $this->getLanguageService()->getLL('message_notLoggedIn'),
            $subject->canViewRegistrationsListMessage('list_registrations', 'login')
        );
    }

    /**
     * @test
     */
    public function canViewRegistrationsListMessageForListAndNoLoginAndWorldAccessReturnsEmptyString()
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration']);
        $subject->method('needsRegistration')->willReturn(true);
        $subject->init();

        self::assertSame(
            '',
            $subject->canViewRegistrationsListMessage('list_registrations', 'world')
        );
    }

    /**
     * Data provider that returns all possible access level codes for the
     * FE registration lists.
     *
     * @return string[][] the possible access levels, will not be empty
     */
    public function registrationListAccessLevelsDataProvider(): array
    {
        return [
            'attendeesAndManagers' => ['attendees_and_managers'],
            'login' => ['login'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider registrationListAccessLevelsDataProvider
     *
     * @param string $accessLevel
     *
     * @return void
     */
    public function canViewRegistrationsListMessageForVipListAndNoLoginReturnsPleaseLoginMessage(string $accessLevel)
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration']);
        $subject->method('needsRegistration')->willReturn(true);
        $subject->init();

        self::assertSame(
            $this->getLanguageService()->getLL('message_notLoggedIn'),
            $subject->canViewRegistrationsListMessage('list_vip_registrations', $accessLevel)
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function canViewRegistrationsListMessageForVipListAndWorldAccessAndNoLoginReturnsEmptyString()
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration']);
        $subject->method('needsRegistration')->willReturn(true);
        $subject->init();

        self::assertSame(
            '',
            $subject->canViewRegistrationsListMessage('list_vip_registrations', 'world')
        );
    }

    /**
     * Data provider that returns all possible parameter combinations for
     * canViewRegistrationsList as called from canViewRegistrationsListMessage.
     *
     * @return string[][] the possible parameter combinations, will not be empty
     */
    public function registrationListParametersDataProvider(): array
    {
        return [
            'attendeesAndManagers' => ['list_registrations', 'attendees_and_managers'],
            'login' => ['list_registrations', 'login'],
            'world' => ['list_registrations', 'world'],
            'attendeesAndManagersVip' => ['list_vip_registrations', 'attendees_and_managers'],
            'loginVip' => ['list_vip_registrations', 'login'],
            'worldVip' => ['list_vip_registrations', 'world'],
        ];
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     *
     * @dataProvider registrationListParametersDataProvider
     *
     * @param string $whichPlugin
     * @param string $accessLevel
     */
    public function canViewRegistrationsListMessageWithLoginRoutesParameters(string $whichPlugin, string $accessLevel)
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'canViewRegistrationsList']
        );
        $subject->method('needsRegistration')->willReturn(true);
        $subject->method('canViewRegistrationsList')
            ->with($whichPlugin, 0, 0, 0, $accessLevel)
            ->willReturn(true);

        $this->testingFramework->createFakeFrontEnd();
        $this->testingFramework->createAndLoginFrontEndUser();

        $subject->canViewRegistrationsListMessage($whichPlugin, $accessLevel);
    }

    /**
     * @test
     */
    public function canViewRegistrationsListMessageWithLoginAndAccessGrantedReturnsEmptyString()
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'canViewRegistrationsList']
        );
        $subject->method('needsRegistration')->willReturn(true);
        $subject->method('canViewRegistrationsList')->willReturn(true);

        $this->testingFramework->createFakeFrontEnd();
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $subject->canViewRegistrationsListMessage('list_registrations')
        );
    }

    /**
     * @test
     */
    public function canViewRegistrationsListMessageWithLoginAndAccessDeniedReturnsAccessDeniedMessage()
    {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'canViewRegistrationsList']
        );
        $subject->method('needsRegistration')->willReturn(true);
        $subject->method('canViewRegistrationsList')->willReturn(false);

        $this->testingFramework->createFakeFrontEnd();
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            $this->getLanguageService()->getLL('message_accessDenied'),
            $subject->canViewRegistrationsListMessage('list_registrations')
        );
    }

    /*
     * Tests concerning hasAnyPrice
     */

    /**
     * Data provider for hasAnyPriceWithDataProvider.
     *
     * @return bool[][] two-dimensional array with the following inner keys:
     *               [expectedHasAnyPrice] the expected return value of hasAnyPrice
     *               [hasPriceRegular] the return value of that function
     *               [hasPriceSpecial] the return value of that function
     *               [earlyBirdApplies] the return value of that function
     *               [hasEarlyBirdPriceRegular] the return value of that function
     *               [hasEarlyBirdPriceSpecial] the return value of that function
     *               [hasPriceRegularBoard] the return value of that function
     *               [hasPriceSpecialBoard] the return value of that function
     */
    public function hasAnyPriceDataProvider(): array
    {
        return [
            'noPriceAtAll' => [
                'expectedHasAnyPrice' => false,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => false,
                'hasEarlyBirdPriceRegular' => false,
                'hasEarlyBirdPriceSpecial' => false,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => false,
            ],
            'regularPrice' => [
                'expectedHasAnyPrice' => true,
                'hasPriceRegular' => true,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => false,
                'hasEarlyBirdPriceRegular' => false,
                'hasEarlyBirdPriceSpecial' => false,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => false,
            ],
            'specialPrice' => [
                'expectedHasAnyPrice' => true,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => true,
                'earlyBirdApplies' => false,
                'hasEarlyBirdPriceRegular' => false,
                'hasEarlyBirdPriceSpecial' => false,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => false,
            ],
            'regularEarlyBirdApplies' => [
                'expectedHasAnyPrice' => true,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => true,
                'hasEarlyBirdPriceRegular' => true,
                'hasEarlyBirdPriceSpecial' => false,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => false,
            ],
            'regularEarlyBirdNotApplies' => [
                'expectedHasAnyPrice' => false,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => false,
                'hasEarlyBirdPriceRegular' => true,
                'hasEarlyBirdPriceSpecial' => false,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => false,
            ],
            'specialEarlyBirdApplies' => [
                'expectedHasAnyPrice' => true,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => true,
                'hasEarlyBirdPriceRegular' => false,
                'hasEarlyBirdPriceSpecial' => true,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => false,
            ],
            'specialEarlyBirdNotApplies' => [
                'expectedHasAnyPrice' => false,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => false,
                'hasEarlyBirdPriceRegular' => false,
                'hasEarlyBirdPriceSpecial' => true,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => false,
            ],
            'regularBoard' => [
                'expectedHasAnyPrice' => true,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => false,
                'hasEarlyBirdPriceRegular' => false,
                'hasEarlyBirdPriceSpecial' => false,
                'hasPriceRegularBoard' => true,
                'hasPriceSpecialBoard' => false,
            ],
            'specialBoard' => [
                'expectedHasAnyPrice' => true,
                'hasPriceRegular' => false,
                'hasPriceSpecial' => false,
                'earlyBirdApplies' => false,
                'hasEarlyBirdPriceRegular' => false,
                'hasEarlyBirdPriceSpecial' => false,
                'hasPriceRegularBoard' => false,
                'hasPriceSpecialBoard' => true,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider hasAnyPriceDataProvider
     *
     * @param bool $expectedHasAnyPrice
     *        the expected return value of hasAnyPrice
     * @param bool $hasPriceRegular the return value of hasPriceRegular
     * @param bool $hasPriceSpecial the return value of hasPriceRegular
     * @param bool $earlyBirdApplies the return value of earlyBirdApplies
     * @param bool $hasEarlyBirdPriceRegular the return value of earlyBirdApplies
     * @param bool $hasEarlyBirdPriceSpecial
     *        the return value of hasEarlyBirdPriceSpecial
     * @param bool $hasPriceRegularBoard
     *        the return value of hasPriceRegularBoard
     * @param bool $hasPriceSpecialBoard
     *        the return value of hasPriceSpecialBoard
     */
    public function hasAnyPriceWithDataProvider(
        bool $expectedHasAnyPrice,
        bool $hasPriceRegular,
        bool $hasPriceSpecial,
        bool $earlyBirdApplies,
        bool $hasEarlyBirdPriceRegular,
        bool $hasEarlyBirdPriceSpecial,
        bool $hasPriceRegularBoard,
        bool $hasPriceSpecialBoard
    ) {
        /** @var \Tx_Seminars_OldModel_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            [
                'hasPriceRegular',
                'hasPriceSpecial',
                'earlyBirdApplies',
                'hasEarlyBirdPriceRegular',
                'hasEarlyBirdPriceSpecial',
                'hasPriceRegularBoard',
                'hasPriceSpecialBoard',
            ]
        );

        $subject->method('hasPriceRegular')
            ->willReturn($hasPriceRegular);
        $subject->method('hasPriceSpecial')
            ->willReturn($hasPriceSpecial);
        $subject->method('earlyBirdApplies')
            ->willReturn($earlyBirdApplies);
        $subject->method('hasEarlyBirdPriceRegular')
            ->willReturn($hasEarlyBirdPriceRegular);
        $subject->method('hasEarlyBirdPriceSpecial')
            ->willReturn($hasEarlyBirdPriceSpecial);
        $subject->method('hasPriceRegularBoard')
            ->willReturn($hasPriceRegularBoard);
        $subject->method('hasPriceSpecialBoard')
            ->willReturn($hasPriceSpecialBoard);

        self::assertSame(
            $expectedHasAnyPrice,
            $subject->hasAnyPrice()
        );
    }

    /*
     * Tests regarding the flag for organizers having been notified about enough attendees.
     */

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesByDefaultReturnsFalse()
    {
        self::assertFalse(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesReturnsTrueValueFromDatabase()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers_notified_about_minimum_reached' => 1]
        );
        $subject = new TestingEvent($uid);

        self::assertTrue(
            $subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function setOrganizersBeenNotifiedAboutEnoughAttendeesMarksItAsTrue()
    {
        $this->subject->setOrganizersBeenNotifiedAboutEnoughAttendees();

        self::assertTrue(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /*
     * Tests regarding the flag for organizers having been notified about enough attendees.
     */

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsByDefaultReturnsFalse()
    {
        self::assertFalse(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsReturnsTrueValueFromDatabase()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['mute_notification_emails' => 1]
        );
        $subject = new TestingEvent($uid);

        self::assertTrue(
            $subject->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function muteNotificationEmailsSetsShouldMute()
    {
        $this->subject->muteNotificationEmails();

        self::assertTrue(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    /*
     * Tests regarding the flag for automatic cancelation/confirmation
     */

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelByDefaultReturnsFalse()
    {
        self::assertFalse(
            $this->subject->shouldAutomaticallyConfirmOrCancel()
        );
    }

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelReturnsTrueValueFromDatabase()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['automatic_confirmation_cancelation' => 1]
        );
        $subject = new TestingEvent($uid);

        self::assertTrue(
            $subject->shouldAutomaticallyConfirmOrCancel()
        );
    }

    /*
     * Tests regarding the number of associated registration records
     */

    /**
     * @test
     */
    public function getNumberOfAssociatedRegistrationRecordsByDefaultReturnsZero()
    {
        self::assertSame(0, $this->subject->getNumberOfAssociatedRegistrationRecords());
    }

    /**
     * @test
     */
    public function getNumberOfAssociatedRegistrationRecordsReturnsValueFromDatabase()
    {
        $numberOfRegistrations = 3;
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['registrations' => $numberOfRegistrations]
        );
        $subject = new TestingEvent($uid);

        self::assertSame($numberOfRegistrations, $subject->getNumberOfAssociatedRegistrationRecords());
    }

    /**
     * @test
     */
    public function increaseNumberOfAssociatedRegistrationRecordsCanIncreaseItFromZeroToOne()
    {
        $this->subject->increaseNumberOfAssociatedRegistrationRecords();

        self::assertSame(1, $this->subject->getNumberOfAssociatedRegistrationRecords());
    }

    /**
     * @test
     */
    public function increaseNumberOfAssociatedRegistrationRecordsCanIncreaseItFromTwoToThree()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['registrations' => 2]
        );
        $subject = new TestingEvent($uid);

        $subject->increaseNumberOfAssociatedRegistrationRecords();

        self::assertSame(3, $subject->getNumberOfAssociatedRegistrationRecords());
    }

    /*
     * Tests concerning the price
     */

    /**
     * @test
     */
    public function getPriceOnRequestByDefaultReturnsFalse()
    {
        self::assertFalse($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getPriceOnRequestReturnsPriceOnRequest()
    {
        $this->subject->setRecordPropertyInteger('price_on_request', 1);

        self::assertTrue($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function setPriceOnRequestSetsPriceOnRequest()
    {
        $this->subject->setPriceOnRequest(true);

        self::assertTrue($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getPriceOnRequestForEventDateReturnsFalseValueFromTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC, 'price_on_request' => false]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE, 'topic' => $topicUid]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateUid);

        self::assertFalse($date->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getPriceOnRequestForEventDateReturnsTrueValueFromTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC, 'price_on_request' => true]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE, 'topic' => $topicUid]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateUid);

        self::assertTrue($date->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getCurrentPriceRegularForZeroPriceReturnsForFree()
    {
        $this->subject->setRecordPropertyString('price_regular', '0.00');

        $result = $this->subject->getCurrentPriceRegular();

        self::assertSame($this->getLanguageService()->getLL('message_forFree'), $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceRegularForNonZeroPriceReturnsPrice()
    {
        $this->subject->setRecordPropertyString('price_regular', '123.45');

        $result = $this->subject->getCurrentPriceRegular();

        self::assertSame('123.45', $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceRegularForPriceOnRequestReturnsLocalizedString()
    {
        $this->subject->setRecordPropertyInteger('price_on_request', 1);
        $this->subject->setRecordPropertyString('price_regular', '123.45');

        $result = $this->subject->getCurrentPriceRegular();

        self::assertSame($this->getLanguageService()->getLL('message_onRequest'), $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceSpecialReturnsRegularNonZeroPrice()
    {
        $this->subject->setRecordPropertyString('price_regular', '57');
        $this->subject->setRecordPropertyString('price_special', '123.45');

        $result = $this->subject->getCurrentPriceSpecial();

        self::assertSame('123.45', $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceSpecialForPriceOnRequestReturnsLocalizedString()
    {
        $this->subject->setRecordPropertyInteger('price_on_request', 1);
        $this->subject->setRecordPropertyString('price_regular', '57');
        $this->subject->setRecordPropertyString('price_special', '123.45');

        $result = $this->subject->getCurrentPriceSpecial();

        self::assertSame($this->getLanguageService()->getLL('message_onRequest'), $result);
    }

    /**
     * @test
     */
    public function getAvailablePricesForAllPricesAvailableWithoutEarlyBirdDeadlineReturnsAllLatePrices()
    {
        $this->subject->setRecordPropertyString('price_regular', '100.00');
        $this->subject->setRecordPropertyString('price_regular_early', '90.00');
        $this->subject->setRecordPropertyString('price_regular_board', '150.00');
        $this->subject->setRecordPropertyString('price_special', '50.00');
        $this->subject->setRecordPropertyString('price_special_early', '45.00');
        $this->subject->setRecordPropertyString('price_special_board', '75.00');

        self::assertSame(
            ['regular', 'regular_board', 'special', 'special_board'],
            array_keys($this->subject->getAvailablePrices())
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForAllPricesAvailableWithPastEarlyBirdDeadlineReturnsAllLatePrices()
    {
        $this->subject->setRecordPropertyString('price_regular', '100.00');
        $this->subject->setRecordPropertyString('price_regular_early', '90.00');
        $this->subject->setRecordPropertyString('price_regular_board', '150.00');
        $this->subject->setRecordPropertyString('price_special', '50.00');
        $this->subject->setRecordPropertyString('price_special_early', '45.00');
        $this->subject->setRecordPropertyString('price_special_board', '75.00');
        $this->subject->setRecordPropertyInteger('deadline_early_bird', $this->now - 1000);

        self::assertSame(
            ['regular', 'regular_board', 'special', 'special_board'],
            array_keys($this->subject->getAvailablePrices())
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForAllPricesAvailableWithFuturEarlyBirdDeadlineReturnsAllEarlyBirdPrices()
    {
        $this->subject->setRecordPropertyString('price_regular', '100.00');
        $this->subject->setRecordPropertyString('price_regular_early', '90.00');
        $this->subject->setRecordPropertyString('price_regular_board', '150.00');
        $this->subject->setRecordPropertyString('price_special', '50.00');
        $this->subject->setRecordPropertyString('price_special_early', '45.00');
        $this->subject->setRecordPropertyString('price_special_board', '75.00');
        $this->subject->setRecordPropertyInteger('deadline_early_bird', $this->now + 1000);

        self::assertSame(
            ['regular_early', 'regular_board', 'special_early', 'special_board'],
            array_keys($this->subject->getAvailablePrices())
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForNoPricesSetReturnsRegularPriceOnly()
    {
        self::assertSame(['regular'], array_keys($this->subject->getAvailablePrices()));
    }
}
