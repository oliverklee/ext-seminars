<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
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
 * @covers \Tx_Seminars_OldModel_Event
 */
final class EventTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var array<string, string|int|bool>
     */
    private const CONFIGURATION = [
        'dateFormatYMD' => '%d.%m.%Y',
        'timeFormat' => '%H:%M',
        'showTimeOfUnregistrationDeadline' => false,
        'unregistrationDeadlineDaysBeforeBeginDate' => 0,
    ];

    /**
     * @var DummyConfiguration
     */
    private $configuration;

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
     * @var \Tx_Seminars_FrontEnd_DefaultController|null
     */
    private $pi1 = null;

    /** @var ConnectionPool */
    private $connectionPool = null;

    protected function setUp(): void
    {
        // Make sure that the test results do not depend on the machine's PHP time zone.
        \date_default_timezone_set('UTC');

        $GLOBALS['SIM_EXEC_TIME'] = $this->now;
        $this->beginDate = ($this->now + Time::SECONDS_PER_WEEK);
        $this->unregistrationDeadline = ($this->now + Time::SECONDS_PER_WEEK);

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        $this->configuration = new DummyConfiguration(self::CONFIGURATION);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

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
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        ConfigurationRegistry::purgeInstance();
        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    // Utility functions

    /**
     * Creates a fake front end and a pi1 instance in `$this->pi1`.
     */
    private function createPi1(int $detailPageUid = 0): void
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);

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

        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $this->subject->getUid(), $uid);
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
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addCategoryRelation(array $categoryData = []): int
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            $categoryData
        );

        $this->testingFramework->createRelation('tx_seminars_seminars_categories_mm', $this->subject->getUid(), $uid);
        $this->subject->setNumberOfCategories($this->subject->getNumberOfCategories() + 1);

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

    // Tests for the utility functions

    /**
     * @test
     */
    public function createPi1CreatesFakeFrontEnd(): void
    {
        $GLOBALS['TSFE'] = null;

        $this->createPi1();

        self::assertNotNull($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function createPi1CreatesPi1Instance(): void
    {
        $this->pi1 = null;

        $this->createPi1();

        self::assertInstanceOf(\Tx_Seminars_FrontEnd_DefaultController::class, $this->pi1);
    }

    /**
     * @test
     */
    public function addPlaceRelationReturnsUid(): void
    {
        $uid = $this->addPlaceRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addPlaceRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addPlaceRelation(),
            $this->addPlaceRelation()
        );
    }

    /**
     * @test
     */
    public function addPlaceRelationIncreasesTheNumberOfPlaces(): void
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
    public function addPlaceRelationCreatesRelations(): void
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
    public function addCategoryRelationReturnsUid(): void
    {
        $uid = $this->addCategoryRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addCategoryRelation(),
            $this->addCategoryRelation()
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationIncreasesTheNumberOfCategories(): void
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
    public function addCategoryRelationCreatesRelations(): void
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
    public function addTargetGroupRelationReturnsUid(): void
    {
        self::assertTrue(
            $this->addTargetGroupRelation() > 0
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addTargetGroupRelation(),
            $this->addTargetGroupRelation()
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationIncreasesTheNumberOfTargetGroups(): void
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
    public function addTargetGroupRelationCreatesRelations(): void
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
    public function addPaymentMethodRelationReturnsUid(): void
    {
        $uid = $this->addPaymentMethodRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addPaymentMethodRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addPaymentMethodRelation(),
            $this->addPaymentMethodRelation()
        );
    }

    /**
     * @test
     */
    public function addPaymentMethodRelationIncreasesTheNumberOfPaymentMethods(): void
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
    public function addOrganizingPartnerRelationReturnsUid(): void
    {
        $uid = $this->addOrganizingPartnerRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addOrganizingPartnerRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addOrganizingPartnerRelation(),
            $this->addOrganizingPartnerRelation()
        );
    }

    /**
     * @test
     */
    public function addOrganizingPartnerRelationCreatesRelations(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_organizing_partners_mm');

        self::assertSame(
            0,
            $connection->count(
                '*',
                'tx_seminars_seminars_organizing_partners_mm',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addOrganizingPartnerRelation();
        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_seminars_organizing_partners_mm',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addOrganizingPartnerRelation();
        self::assertSame(
            2,
            $connection->count(
                '*',
                'tx_seminars_seminars_organizing_partners_mm',
                ['uid_local' => $this->subject->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function addOrganizerRelationReturnsUid(): void
    {
        $uid = $this->addOrganizerRelation();

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addOrganizerRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addOrganizerRelation(),
            $this->addOrganizerRelation()
        );
    }

    /**
     * @test
     */
    public function addOrganizerRelationIncreasesTheNumberOfOrganizers(): void
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
    public function addSpeakerRelationReturnsUid(): void
    {
        $uid = $this->addSpeakerRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addSpeakerRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addSpeakerRelation([]),
            $this->addSpeakerRelation([])
        );
    }

    /**
     * @test
     */
    public function addSpeakerRelationCreatesRelations(): void
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
    public function addPartnerRelationReturnsUid(): void
    {
        $uid = $this->addPartnerRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addPartnerRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addPartnerRelation([]),
            $this->addPartnerRelation([])
        );
    }

    /**
     * @test
     */
    public function addPartnerRelationCreatesRelations(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_speakers_mm_partners');

        self::assertSame(
            0,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_partners',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addPartnerRelation([]);
        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_partners',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addPartnerRelation([]);
        self::assertSame(
            2,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_partners',
                ['uid_local' => $this->subject->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function addTutorRelationReturnsUid(): void
    {
        $uid = $this->addTutorRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addTutorRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addTutorRelation([]),
            $this->addTutorRelation([])
        );
    }

    /**
     * @test
     */
    public function addTutorRelationCreatesRelations(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_speakers_mm_tutors');

        self::assertSame(
            0,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_tutors',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addTutorRelation([]);
        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_tutors',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addTutorRelation([]);
        self::assertSame(
            2,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_tutors',
                ['uid_local' => $this->subject->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function addLeaderRelationReturnsUid(): void
    {
        $uid = $this->addLeaderRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addLeaderRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addLeaderRelation([]),
            $this->addLeaderRelation([])
        );
    }

    /**
     * @test
     */
    public function addLeaderRelationCreatesRelations(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_speakers_mm_leaders');

        self::assertSame(
            0,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_leaders',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addLeaderRelation([]);
        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_leaders',
                ['uid_local' => $this->subject->getUid()]
            )
        );

        $this->addLeaderRelation([]);
        self::assertSame(
            2,
            $connection->count(
                '*',
                'tx_seminars_seminars_speakers_mm_leaders',
                ['uid_local' => $this->subject->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function addEventTypeRelationReturnsUid(): void
    {
        $uid = $this->addEventTypeRelation([]);

        self::assertTrue(
            $uid > 0
        );
    }

    /**
     * @test
     */
    public function addEventTypeRelationCreatesNewUids(): void
    {
        self::assertNotSame(
            $this->addLeaderRelation([]),
            $this->addLeaderRelation([])
        );
    }

    // Tests for some basic functionality

    /**
     * @test
     */
    public function isOk(): void
    {
        self::assertTrue(
            $this->subject->isOk()
        );
    }

    // Tests concerning getTitle

    /**
     * @test
     */
    public function getTitleForSingleEventReturnsTitle(): void
    {
        self::assertSame(
            'a test event',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForTopicReturnsTitle(): void
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
    public function getTitleForDateReturnsTopicTitle(): void
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

    // Tests regarding the ability to register for an event

    /**
     * @test
     */
    public function canSomebodyRegisterIsTrueForEventWithFutureDate(): void
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsTrueForEventWithFutureDateAndRegistrationWithoutDateActivated(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForPastEvent(): void
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
    public function canSomebodyRegisterIsFalseForPastEventWithRegistrationWithoutDateActivated(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForCurrentlyRunningEvent(): void
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
    public function canSomebodyRegisterIsFalseForCurrentlyRunningEventWithRegistrationWithoutDateActivated(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsFalseForEventWithoutDate(): void
    {
        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterIsTrueForEventWithoutDateAndRegistrationWithoutDateActivated(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithUnlimitedVacanciesReturnsTrue(): void
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
    public function canSomebodyRegisterForCancelledEventReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->canSomebodyRegister()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterForEventWithoutNeedeRegistrationReturnsFalse(): void
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
    public function canSomebodyRegisterForFullyBookedEventReturnsFalse(): void
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
    public function canSomebodyRegisterForEventWithRegistrationQueueAndNoRegularVacanciesReturnsTrue(): void
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
    public function canSomebodyRegisterForEventWithRegistrationQueueAndRegularVacanciesReturnsTrue(): void
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
    public function canSomebodyRegisterForEventWithRegistrationBeginInFutureReturnsFalse(): void
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
    public function canSomebodyRegisterForEventWithRegistrationBeginInPastReturnsTrue(): void
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
    public function canSomebodyRegisterForEventWithoutRegistrationBeginReturnsTrue(): void
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(0);

        self::assertTrue(
            $this->subject->canSomebodyRegister()
        );
    }

    // Tests concerning canSomebodyRegisterMessage

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithFutureDateReturnsEmptyString(): void
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
    public function canSomebodyRegisterMessageForPastEventReturnsSeminarRegistrationClosedMessage(): void
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
    public function canSomebodyRegisterMessageForPastEventWithRegistrationWithoutDateActivatedReturnsRegistrationDeadlineOverMessage(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

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
    public function canSomebodyRegisterMessageForCurrentlyRunningEventReturnsSeminarRegistrationClosesMessage(): void
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
    public function canSomebodyRegisterMessageForCurrentlyRunningEventWithRegistrationWithoutDateActivatedReturnsSeminarRegistrationClosesMessage(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

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
    public function canSomebodyRegisterMessageForEventWithoutDateReturnsNoDateMessage(): void
    {
        self::assertSame(
            $this->getLanguageService()->getLL('message_noDate'),
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    /**
     * @test
     */
    public function canSomebodyRegisterMessageForEventWithoutDateAndRegistrationWithoutDateActivatedReturnsEmptyString(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

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
    public function canSomebodyRegisterMessageForEventWithUnlimitedVacanviesReturnsEmptyString(): void
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
    public function canSomebodyRegisterMessageForCancelledEventReturnsSeminarCancelledMessage(): void
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
    public function canSomebodyRegisterMessageForEventWithoutNeedeRegistrationReturnsNoRegistrationNecessaryMessage(): void
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
    public function canSomebodyRegisterMessageForFullyBookedEventReturnsNoVacanciesMessage(): void
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
    public function canSomebodyRegisterMessageForFullyBookedEventWithRegistrationQueueReturnsEmptyString(): void
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
    public function canSomebodyRegisterMessageForEventWithRegistrationBeginInFutureReturnsRegistrationOpensOnMessage(): void
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
    public function canSomebodyRegisterMessageForEventWithRegistrationBeginInPastReturnsEmptyString(): void
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
    public function canSomebodyRegisterMessageForEventWithoutRegistrationBeginReturnsEmptyString(): void
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setRegistrationBeginDate(0);

        self::assertSame(
            '',
            $this->subject->canSomebodyRegisterMessage()
        );
    }

    // Tests regarding the language of an event

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithValidLanguage(): void
    {
        self::assertSame(
            'Deutsch',
            $this->subject->getLanguageNameFromIsoCode('de')
        );
    }

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithInvalidLanguage(): void
    {
        self::assertSame(
            '',
            $this->subject->getLanguageNameFromIsoCode('xy')
        );
    }

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithVeryInvalidLanguage(): void
    {
        self::assertSame(
            '',
            $this->subject->getLanguageNameFromIsoCode('foobar')
        );
    }

    /**
     * @test
     */
    public function getLanguageFromIsoCodeWithEmptyLanguage(): void
    {
        self::assertSame(
            '',
            $this->subject->getLanguageNameFromIsoCode('')
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithLanguageReturnsTrue(): void
    {
        $this->subject->setLanguage('de');
        self::assertTrue(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithNoLanguageReturnsFalse(): void
    {
        $this->subject->setLanguage('');
        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageNameWithDefaultLanguageOnSingleEvent(): void
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
    public function getLanguageNameWithValidLanguageOnSingleEvent(): void
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
    public function getLanguageNameWithInvalidLanguageOnSingleEvent(): void
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
    public function getLanguageNameWithNoLanguageOnSingleEvent(): void
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
    public function getLanguageNameOnDateRecord(): void
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
    public function getLanguageOnSingleRecordThatWasADateRecord(): void
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

    // Tests regarding the registration.

    /**
     * @test
     */
    public function needsRegistrationForNeedsRegistrationTrueReturnsTrue(): void
    {
        $this->subject->setNeedsRegistration(true);

        self::assertTrue(
            $this->subject->needsRegistration()
        );
    }

    /**
     * @test
     */
    public function needsRegistrationForNeedsRegistrationFalseReturnsFalse(): void
    {
        $this->subject->setNeedsRegistration(false);

        self::assertFalse(
            $this->subject->needsRegistration()
        );
    }

    // Tests concerning hasUnlimitedVacancies

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForNeedsRegistrationTrueAndMaxAttendeesZeroReturnsTrue(): void
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
    public function hasUnlimitedVacanciesForNeedsRegistrationTrueAndMaxAttendeesOneReturnsFalse(): void
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
    public function hasUnlimitedVacanciesForNeedsRegistrationFalseAndMaxAttendeesZeroReturnsFalse(): void
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
    public function hasUnlimitedVacanciesForNeedsRegistrationFalseAndMaxAttendeesOneReturnsFalse(): void
    {
        $this->subject->setNeedsRegistration(false);
        $this->subject->setAttendancesMax(1);

        self::assertFalse(
            $this->subject->hasUnlimitedVacancies()
        );
    }

    // Tests concerning isFull

    /**
     * @test
     */
    public function isFullForUnlimitedVacanciesAndZeroAttendancesReturnsFalse(): void
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
    public function isFullForUnlimitedVacanciesAndOneAttendanceReturnsFalse(): void
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
    public function isFullForOneVacancyAndNoAttendancesReturnsFalse(): void
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
    public function isFullForOneVacancyAndOneAttendanceReturnsTrue(): void
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
    public function isFullForTwoVacanciesAndOneAttendanceReturnsFalse(): void
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
    public function isFullForTwoVacanciesAndTwoAttendancesReturnsTrue(): void
    {
        $this->subject->setNeedsRegistration(true);
        $this->subject->setAttendancesMax(2);
        $this->subject->setNumberOfAttendances(2);

        self::assertTrue(
            $this->subject->isFull()
        );
    }

    // Tests regarding the unregistration and the queue

    /**
     * @test
     */
    public function getUnregistrationDeadlineAsTimestampForNonZero(): void
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
    public function getUnregistrationDeadlineAsTimestampForZero(): void
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
    public function getUnregistrationDeadlineWithoutTimeForNonZero(): void
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
    public function getNonUnregistrationDeadlineWithTimeForZero(): void
    {
        $this->configuration->setAsBoolean('showTimeOfUnregistrationDeadline', true);

        $this->subject->setUnregistrationDeadline(1893488400);

        self::assertSame('01.01.2030 09:00', $this->subject->getUnregistrationDeadline());
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineIsEmptyForZero(): void
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
    public function hasUnregistrationDeadlineIsTrueForNonZeroDeadline(): void
    {
        $this->subject->setUnregistrationDeadline($this->unregistrationDeadline);

        self::assertTrue(
            $this->subject->hasUnregistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineIsFalseForZeroDeadline(): void
    {
        $this->subject->setUnregistrationDeadline(0);

        self::assertFalse(
            $this->subject->hasUnregistrationDeadline()
        );
    }

    // Tests concerning isUnregistrationPossible()

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutDeadlineReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

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
    public function isUnregistrationPossibleWithNoBeginDateAndNoDeadlineReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

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
    public function isUnregistrationPossibleWithGlobalDeadlineInFutureReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

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
    public function isUnregistrationPossibleWithGlobalDeadlineInPastReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 5);

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
    public function isUnregistrationPossibleWithoutBeginDateAndWithGlobalDeadlineReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

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
    public function isUnregistrationPossibleWithFutureEventDeadlineReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setUnregistrationDeadline($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithPastEventDeadlineReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setUnregistrationDeadline($this->now - Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithFutureEventDeadlineReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setUnregistrationDeadline($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithPastEventDeadlineReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setUnregistrationDeadline($this->now - Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithBothDeadlinesInFutureReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setUnregistrationDeadline($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithBothDeadlinesInPastReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 2);

        $this->subject->setAttendancesMax(10);
        $this->subject->setUnregistrationDeadline($this->now - Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_DAY);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInFutureReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setUnregistrationDeadline($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInPastReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setBeginDate(0);
        $this->subject->setAttendancesMax(10);
        $this->subject->setUnregistrationDeadline($this->now - Time::SECONDS_PER_DAY);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithPassedEventUnregistrationDeadlineReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setBeginDate($this->now + 2 * Time::SECONDS_PER_DAY);
        $this->subject->setUnregistrationDeadline($this->now - Time::SECONDS_PER_DAY);
        $this->subject->setAttendancesMax(10);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleWithNonZeroAttendancesMaxReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setAttendancesMax(10);
        $this->subject->setUnregistrationDeadline($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleForNeedsRegistrationFalseReturnsFalse(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setNeedsRegistration(false);
        $this->subject->setUnregistrationDeadline($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleForEventWithEmptyWaitingListAndAllowUnregistrationWithEmptyWaitingListReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setAttendancesMax(10);
        $this->subject->setUnregistrationDeadline($this->now + Time::SECONDS_PER_DAY);
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendancesOnQueue(0);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    // Tests concerning getUnregistrationDeadlineFromModelAndConfiguration

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndNoUnregistrationDeadlineReturnsZero(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setBeginDate(0);
        $this->subject->setUnregistrationDeadline(0);

        self::assertSame(
            0,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlineSetInEventReturnsUnregistrationDeadline(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setBeginDate(0);
        $this->subject->setUnregistrationDeadline($this->now);

        self::assertSame(
            $this->now,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlinInEventAndUnregistrationDeadlineSetInConfigurationReturnsZero(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', $this->now);

        $this->subject->setBeginDate(0);
        $this->subject->setUnregistrationDeadline(0);

        self::assertSame(
            0,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlineSetInEventReturnsThisDeadline(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline($this->now);

        self::assertSame(
            $this->now,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndNoDeadlineConfigurationSetReturnsZero(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 0);

        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline(0);

        self::assertSame(
            0,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndDeadlineConfigurationSetReturnsCalculatedDeadline(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline(0);

        self::assertSame(
            $this->now + Time::SECONDS_PER_WEEK - Time::SECONDS_PER_DAY,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlinesSetInEventAndConfigurationReturnsEventsDeadline(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);
        $this->subject->setUnregistrationDeadline($this->now);

        self::assertSame(
            $this->now,
            $this->subject->getUnregistrationDeadlineFromModelAndConfiguration()
        );
    }

    // Tests concerning hasRegistrationQueue

    /**
     * @test
     */
    public function hasRegistrationQueueWithQueueReturnsTrue(): void
    {
        $this->subject->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->hasRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationQueueWithoutQueueReturnsFalse(): void
    {
        $this->subject->setRegistrationQueue(false);

        self::assertFalse(
            $this->subject->hasRegistrationQueue()
        );
    }

    // Tests concerning getAttendancesOnRegistrationQueue

    /**
     * @test
     */
    public function getAttendancesOnRegistrationQueueIsInitiallyZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getAttendancesOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function getAttendancesOnRegistrationQueueForNonEmptyRegistrationQueue(): void
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
    public function hasAttendancesOnRegistrationQueueIsFalseForNoRegistrations(): void
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
    public function hasAttendancesOnRegistrationQueueIsFalseForRegularRegistrationsOnly(): void
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
    public function hasAttendancesOnRegistrationQueueIsTrueForQueueRegistrations(): void
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
    public function isUnregistrationPossibleIsTrueWithNonEmptyQueueByDefault(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(1);
        $this->subject->setUnregistrationDeadline($this->now + (6 * Time::SECONDS_PER_DAY));
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleIsFalseWithEmptyQueueByDefault(): void
    {
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(0);
        $this->subject->setUnregistrationDeadline($this->now + (6 * Time::SECONDS_PER_DAY));
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertFalse(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function isUnregistrationPossibleIsTrueWithEmptyQueueIfAllowedByConfiguration(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);
        $this->configuration->setAsInteger('unregistrationDeadlineDaysBeforeBeginDate', 1);

        $this->subject->setAttendancesMax(1);
        $this->subject->setRegistrationQueue(true);
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setNumberOfAttendancesOnQueue(0);
        $this->subject->setUnregistrationDeadline($this->now + (6 * Time::SECONDS_PER_DAY));
        $this->subject->setBeginDate($this->now + Time::SECONDS_PER_WEEK);

        self::assertTrue(
            $this->subject->isUnregistrationPossible()
        );
    }

    /**
     * @test
     */
    public function getCountry(): void
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
    public function getCountryWithNoCountry(): void
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
    public function getCountryWithInvalidCountry(): void
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
    public function getCountryWithMultipleCountries(): void
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
    public function getCountryWithNoPlace(): void
    {
        self::assertSame(
            '',
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function getCountryNameFromIsoCode(): void
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

    // Tests regarding the target groups

    /**
     * @test
     */
    public function hasTargetGroupsIsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasTargetGroups()
        );
    }

    /**
     * @test
     */
    public function hasTargetGroups(): void
    {
        $this->addTargetGroupRelation();

        self::assertTrue(
            $this->subject->hasTargetGroups()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupNamesWithNoTargetGroup(): void
    {
        self::assertSame(
            '',
            $this->subject->getTargetGroupNames()
        );
    }

    /**
     * @test
     */
    public function getTargetGroupNamesWithSingleTargetGroup(): void
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
    public function getTargetGroupNamesWithMultipleTargetGroups(): void
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

    // Tests regarding the payment methods

    /**
     * @test
     */
    public function hasPaymentMethodsReturnsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function canHaveOnePaymentMethod(): void
    {
        $this->addPaymentMethodRelation();

        self::assertTrue(
            $this->subject->hasPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainWithNoPaymentMethodReturnsAnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getPaymentMethodsPlain()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainWithSinglePaymentMethodReturnsASinglePaymentMethod(): void
    {
        $title = 'Test title';
        $this->addPaymentMethodRelation(['title' => $title]);

        self::assertStringContainsString(
            $title,
            $this->subject->getPaymentMethodsPlain()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainWithMultiplePaymentMethodsReturnsMultiplePaymentMethods(): void
    {
        $firstTitle = 'Payment Method 1';
        $secondTitle = 'Payment Method 2';
        $this->addPaymentMethodRelation(['title' => $firstTitle]);
        $this->addPaymentMethodRelation(['title' => $secondTitle]);

        self::assertStringContainsString(
            $firstTitle,
            $this->subject->getPaymentMethodsPlain()
        );
        self::assertStringContainsString(
            $secondTitle,
            $this->subject->getPaymentMethodsPlain()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithoutPaymentMethodsReturnsAnEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithOnePaymentMethodReturnsOnePaymentMethod(): void
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
    public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethods(): void
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
    public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethodsSorted(): void
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);

        self::assertSame(
            ['Payment Method 2', 'Payment Method 1'],
            $this->subject->getPaymentMethods()
        );
    }

    // Tests concerning getPaymentMethodsPlainShort

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortWithNoPaymentMethodReturnsAnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortWithSinglePaymentMethodReturnsASinglePaymentMethod(): void
    {
        $title = 'Test title';
        $this->addPaymentMethodRelation(['title' => $title]);

        self::assertStringContainsString(
            $title,
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortWithMultiplePaymentMethodsReturnsMultiplePaymentMethods(): void
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);

        self::assertStringContainsString(
            'Payment Method 1',
            $this->subject->getPaymentMethodsPlainShort()
        );
        self::assertStringContainsString(
            'Payment Method 2',
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortSeparatesMultiplePaymentMethodsWithLineFeeds(): void
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);

        self::assertStringContainsString(
            "Payment Method 1\nPayment Method 2",
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    /**
     * @test
     */
    public function getPaymentMethodsPlainShortDoesNotSeparateMultiplePaymentMethodsWithCarriageReturnsAndLineFeeds(): void
    {
        $this->addPaymentMethodRelation(['title' => 'Payment Method 1']);
        $this->addPaymentMethodRelation(['title' => 'Payment Method 2']);

        self::assertStringNotContainsString(
            "Payment Method 1\r\nPayment Method 2",
            $this->subject->getPaymentMethodsPlainShort()
        );
    }

    // Tests concerning getSinglePaymentMethodPlain

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithInvalidPaymentMethodUidReturnsAnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSinglePaymentMethodPlain(0)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithValidPaymentMethodUidWithoutDescriptionReturnsTitle(): void
    {
        $title = 'Test payment method';
        $uid = $this->addPaymentMethodRelation(['title' => $title]);

        self::assertSame(
            $title . "\n\n",
            $this->subject->getSinglePaymentMethodPlain($uid)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithValidPaymentMethodUidWithDescriptionReturnsTitleAndDescription(): void
    {
        $title = 'Test payment method';
        $description = 'some description';
        $uid = $this->addPaymentMethodRelation(['title' => $title, 'description' => $description]);

        self::assertSame(
            $title . ': ' . $description . "\n\n",
            $this->subject->getSinglePaymentMethodPlain($uid)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodPlainWithNonExistentPaymentMethodUidReturnsAnEmptyString(): void
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
    public function getSinglePaymentMethodShortWithInvalidPaymentMethodUidReturnsAnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSinglePaymentMethodShort(0)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodShortWithValidPaymentMethodUidReturnsTheTitleOfThePaymentMethod(): void
    {
        $title = 'Test payment method';
        $uid = $this->addPaymentMethodRelation(['title' => $title]);

        self::assertStringContainsString(
            $title,
            $this->subject->getSinglePaymentMethodShort($uid)
        );
    }

    /**
     * @test
     */
    public function getSinglePaymentMethodShortWithNonExistentPaymentMethodUidReturnsAnEmptyString(): void
    {
        $uid = $this->addPaymentMethodRelation();

        self::assertSame(
            '',
            $this->subject->getSinglePaymentMethodShort($uid + 1)
        );
    }

    // Tests regarding the event type

    /**
     * @test
     */
    public function setEventTypeThrowsExceptionForNegativeArgument(): void
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
    public function setEventTypeIsAllowedWithZero(): void
    {
        $this->subject->setEventType(0);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setEventTypeIsAllowedWithPositiveInteger(): void
    {
        $this->subject->setEventType(1);
    }

    /**
     * @test
     */
    public function hasEventTypeInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasEventType()
        );
    }

    /**
     * @test
     */
    public function hasEventTypeReturnsTrueIfSingleEventHasNonZeroEventType(): void
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
    public function getEventTypeReturnsEmptyStringForSingleEventWithoutType(): void
    {
        self::assertSame(
            '',
            $this->subject->getEventType()
        );
    }

    /**
     * @test
     */
    public function getEventTypeReturnsTitleOfRelatedEventTypeForSingleEvent(): void
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
    public function getEventTypeForDateRecordReturnsTitleOfEventTypeFromTopicRecord(): void
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
    public function getEventTypeForTopicRecordReturnsTitleOfRelatedEventType(): void
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
    public function getEventTypeUidReturnsUidFromTopicRecord(): void
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
    public function getEventTypeUidInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getEventTypeUid()
        );
    }

    /**
     * @test
     */
    public function getEventTypeUidWithEventTypeReturnsEventTypeUid(): void
    {
        $eventTypeUid = $this->addEventTypeRelation([]);
        self::assertSame(
            $eventTypeUid,
            $this->subject->getEventTypeUid()
        );
    }

    // Tests regarding the organizing partners

    /**
     * @test
     */
    public function hasOrganizingPartnersReturnsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasOrganizingPartners()
        );
    }

    /**
     * @test
     */
    public function canHaveOneOrganizingPartner(): void
    {
        $this->addOrganizingPartnerRelation();

        self::assertTrue(
            $this->subject->hasOrganizingPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizingPartnersWithNoOrganizingPartnerReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfOrganizingPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizingPartnersWithSingleOrganizingPartnerReturnsOne(): void
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
    public function getNumberOfOrganizingPartnersWithMultipleOrganizingPartnersReturnsTwo(): void
    {
        $this->addOrganizingPartnerRelation();
        $this->addOrganizingPartnerRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfOrganizingPartners()
        );
    }

    // Tests regarding the categories

    /**
     * @test
     */
    public function initiallyHasNoCategories(): void
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
    public function getCategoriesCanReturnOneCategory(): void
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
    public function canHaveTwoCategories(): void
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
    public function getCategoriesReturnsIconOfCategory(): void
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
    public function getCategoriesReturnsCategoriesOrderedBySorting(): void
    {
        $categoryUid1 = $this->addCategoryRelation(['title' => 'Test 1']);
        $categoryUid2 = $this->addCategoryRelation(['title' => 'Test 2']);

        self::assertTrue(
            $this->subject->hasCategories()
        );

        self::assertSame(
            [
                $categoryUid1 => ['title' => 'Test 1', 'icon' => ''],
                $categoryUid2 => ['title' => 'Test 2', 'icon' => ''],
            ],
            $this->subject->getCategories()
        );
    }

    // Tests regarding the time slots

    /**
     * @test
     */
    public function getTimeSlotsAsArrayWithMarkersReturnsArraySortedByDate(): void
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

    // Tests regarding the organizers

    /**
     * @test
     */
    public function hasOrganizersReturnsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasOrganizers()
        );
    }

    /**
     * @test
     */
    public function canHaveOneOrganizer(): void
    {
        $this->addOrganizerRelation();

        self::assertTrue(
            $this->subject->hasOrganizers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizersWithNoOrganizerReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfOrganizers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfOrganizersWithSingleOrganizerReturnsOne(): void
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
    public function getNumberOfOrganizersWithMultipleOrganizersReturnsTwo(): void
    {
        $this->addOrganizerRelation();
        $this->addOrganizerRelation();
        self::assertSame(
            2,
            $this->subject->getNumberOfOrganizers()
        );
    }

    // Tests concerning getOrganizers

    /**
     * @test
     */
    public function getOrganizersWithNoOrganizersReturnsEmptyString(): void
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
    public function getOrganizersForOneOrganizerReturnsOrganizerName(): void
    {
        $this->createPi1();
        $this->addOrganizerRelation(['title' => 'foo']);

        self::assertStringContainsString(
            'foo',
            $this->subject->getOrganizers($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getOrganizersForOneOrganizerWithHomepageReturnsOrganizerLinkedToOrganizersHomepage(): void
    {
        $this->createPi1();
        $this->addOrganizerRelation(
            [
                'title' => 'foo',
                'homepage' => 'www.bar.com',
            ]
        );

        self::assertStringContainsString(
            '<a href="http://www.bar.com',
            $this->subject->getOrganizers($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getOrganizersWithTwoOrganizersReturnsBothOrganizerNames(): void
    {
        $this->createPi1();
        $this->addOrganizerRelation(['title' => 'foo']);
        $this->addOrganizerRelation(['title' => 'bar']);

        $organizers = $this->subject->getOrganizers($this->pi1);

        self::assertStringContainsString(
            'foo',
            $organizers
        );
        self::assertStringContainsString(
            'bar',
            $organizers
        );
    }

    // Tests concerning getOrganizersRaw

    /**
     * @test
     */
    public function getOrganizersRawWithNoOrganizersReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawWithSingleOrganizerWithoutHomepageReturnsSingleOrganizer(): void
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
    public function getOrganizersRawWithSingleOrganizerWithHomepageReturnsSingleOrganizerWithHomepage(): void
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
    public function getOrganizersRawForTwoOrganizersWithoutHomepageReturnsTwoOrganizers(): void
    {
        $this->addOrganizerRelation(
            ['title' => 'test organizer 1', 'homepage' => '']
        );
        $this->addOrganizerRelation(
            ['title' => 'test organizer 2', 'homepage' => '']
        );

        self::assertStringContainsString(
            'test organizer 1',
            $this->subject->getOrganizersRaw()
        );
        self::assertStringContainsString(
            'test organizer 2',
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawForTwoOrganizersWithHomepageReturnsTwoOrganizersWithHomepage(): void
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

        self::assertStringContainsString(
            'test homepage 1',
            $this->subject->getOrganizersRaw()
        );
        self::assertStringContainsString(
            'test homepage 2',
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawSeparatesMultipleOrganizersWithLineFeeds(): void
    {
        $this->addOrganizerRelation(['title' => 'test organizer 1']);
        $this->addOrganizerRelation(['title' => 'test organizer 2']);

        self::assertStringContainsString(
            "test organizer 1\ntest organizer 2",
            $this->subject->getOrganizersRaw()
        );
    }

    /**
     * @test
     */
    public function getOrganizersRawDoesNotSeparateMultipleOrganizersWithCarriageReturnsAndLineFeeds(): void
    {
        $this->addOrganizerRelation(['title' => 'test organizer 1']);
        $this->addOrganizerRelation(['title' => 'test organizer 2']);

        self::assertStringNotContainsString(
            "test organizer 1\r\ntest organizer 2",
            $this->subject->getOrganizersRaw()
        );
    }

    // Tests concerning getOrganizersNameAndEmail

    /**
     * @test
     */
    public function getOrganizersNameAndEmailWithNoOrganizersReturnsEmptyString(): void
    {
        self::assertSame(
            [],
            $this->subject->getOrganizersNameAndEmail()
        );
    }

    /**
     * @test
     */
    public function getOrganizersNameAndEmailWithSingleOrganizerReturnsSingleOrganizer(): void
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
    public function getOrganizersNameAndEmailWithMultipleOrganizersReturnsTwoOrganizers(): void
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
    public function getOrganizersEmailWithNoOrganizersReturnsEmptyString(): void
    {
        self::assertSame(
            [],
            $this->subject->getOrganizersEmail()
        );
    }

    /**
     * @test
     */
    public function getOrganizersEmailWithSingleOrganizerReturnsSingleOrganizer(): void
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
    public function getOrganizersEmailWithMultipleOrganizersReturnsTwoOrganizers(): void
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

    // Tests concerning getOrganizersFooter

    /**
     * @test
     */
    public function getOrganizersFootersWithNoOrganizersReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getOrganizersFooter()
        );
    }

    /**
     * @test
     */
    public function getOrganizersFootersWithSingleOrganizerReturnsSingleOrganizerFooter(): void
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
    public function getOrganizersFootersWithMultipleOrganizersReturnsTwoOrganizerFooters(): void
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
    public function getOrganizersFootersWithSingleOrganizerWithoutEmailFooterReturnsEmptyArray(): void
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
    public function getOrganizersFootersWithTwoOrganizersOneWithFooterOneWithoutrReturnsOnlyTheNonEmptyFooter(): void
    {
        $secondOrganizer = ['email_footer' => 'test email footer'];
        $this->addOrganizerRelation();
        $this->addOrganizerRelation($secondOrganizer);
        self::assertSame(
            [$secondOrganizer['email_footer']],
            $this->subject->getOrganizersFooter()
        );
    }

    // Tests concerning getFirstOrganizer

    /**
     * @test
     */
    public function getFirstOrganizerWithNoOrganizersReturnsNull(): void
    {
        self::assertNull(
            $this->subject->getFirstOrganizer()
        );
    }

    /**
     * @test
     */
    public function getFirstOrganizerForOneOrganizerReturnsThatOrganizer(): void
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
    public function getFirstOrganizerForTwoOrganizerReturnsFirstOrganizer(): void
    {
        $firstOrganizerUid = $this->addOrganizerRelation();
        $this->addOrganizerRelation();

        self::assertSame(
            $firstOrganizerUid,
            $this->subject->getFirstOrganizer()->getUid()
        );
    }

    // Tests concerning getAttendancesPid

    /**
     * @test
     */
    public function getAttendancesPidWithNoOrganizerReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getAttendancesPid()
        );
    }

    /**
     * @test
     */
    public function getAttendancesPidWithSingleOrganizerReturnsPid(): void
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
    public function getAttendancesPidWithMultipleOrganizerReturnsFirstPid(): void
    {
        $this->addOrganizerRelation(['attendances_pid' => 99]);
        $this->addOrganizerRelation(['attendances_pid' => 66]);
        self::assertSame(
            99,
            $this->subject->getAttendancesPid()
        );
    }

    // Tests regarding getOrganizerBag().

    /**
     * @test
     */
    public function getOrganizerBagWithoutOrganizersThrowsException(): void
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
    public function getOrganizerBagWithOrganizerReturnsOrganizerBag(): void
    {
        $this->addOrganizerRelation();

        self::assertInstanceOf(\Tx_Seminars_Bag_Organizer::class, $this->subject->getOrganizerBag());
    }

    // Tests regarding the speakers

    /**
     * @test
     */
    public function getNumberOfSpeakersWithNoSpeakerReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfSpeakers()
        );
    }

    /**
     * @test
     */
    public function getNumberOfSpeakersWithSingleSpeakerReturnsOne(): void
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
    public function getNumberOfSpeakersWithMultipleSpeakersReturnsTwo(): void
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
    public function getNumberOfPartnersWithNoPartnerReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfPartners()
        );
    }

    /**
     * @test
     */
    public function getNumberOfPartnersWithSinglePartnerReturnsOne(): void
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
    public function getNumberOfPartnersWithMultiplePartnersReturnsTwo(): void
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
    public function getNumberOfTutorsWithNoTutorReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfTutors()
        );
    }

    /**
     * @test
     */
    public function getNumberOfTutorsWithSingleTutorReturnsOne(): void
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
    public function getNumberOfTutorsWithMultipleTutorsReturnsTwo(): void
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
    public function getNumberOfLeadersWithNoLeaderReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfLeaders()
        );
    }

    /**
     * @test
     */
    public function getNumberOfLeadersWithSingleLeaderReturnsOne(): void
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
    public function getNumberOfLeadersWithMultipleLeadersReturnsTwo(): void
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
    public function hasSpeakersOfTypeIsInitiallyFalse(): void
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
    public function hasSpeakersOfTypeWithSingleSpeakerOfTypeReturnsTrue(): void
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
    public function hasSpeakersIsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasSpeakers()
        );
    }

    /**
     * @test
     */
    public function canHaveOneSpeaker(): void
    {
        $this->addSpeakerRelation([]);
        self::assertTrue(
            $this->subject->hasSpeakers()
        );
    }

    /**
     * @test
     */
    public function hasPartnersIsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasPartners()
        );
    }

    /**
     * @test
     */
    public function canHaveOnePartner(): void
    {
        $this->addPartnerRelation([]);
        self::assertTrue(
            $this->subject->hasPartners()
        );
    }

    /**
     * @test
     */
    public function hasTutorsIsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasTutors()
        );
    }

    /**
     * @test
     */
    public function canHaveOneTutor(): void
    {
        $this->addTutorRelation([]);
        self::assertTrue(
            $this->subject->hasTutors()
        );
    }

    /**
     * @test
     */
    public function hasLeadersIsInitiallyFalse(): void
    {
        self::assertFalse(
            $this->subject->hasLeaders()
        );
    }

    /**
     * @test
     */
    public function canHaveOneLeader(): void
    {
        $this->addLeaderRelation([]);
        self::assertTrue(
            $this->subject->hasLeaders()
        );
    }

    // Tests concerning getSpeakersWithDescriptionRaw

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawWithNoSpeakersReturnsAnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawReturnsTitleOfSpeaker(): void
    {
        $this->addSpeakerRelation(['title' => 'test speaker']);

        self::assertStringContainsString(
            'test speaker',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationReturnsSpeakerWithOrganization(): void
    {
        $this->addSpeakerRelation(['organization' => 'test organization']);

        self::assertStringContainsString(
            'test organization',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithHomepageReturnsSpeakerWithHomepage(): void
    {
        $this->addSpeakerRelation(['homepage' => 'test homepage']);

        self::assertStringContainsString(
            'test homepage',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationAndHomepageReturnsSpeakerWithOrganizationAndHomepage(): void
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
    public function getSpeakersWithDescriptionRawForSpeakerWithDescriptionReturnsSpeakerWithDescription(): void
    {
        $this->addSpeakerRelation(['description' => 'test description']);

        self::assertStringContainsString(
            'test description',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationAndDescriptionReturnsOrganizationAndDescription(): void
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
    public function getSpeakersWithDescriptionRawForSpeakerWithHomepageAndDescriptionReturnsHomepageAndDescription(): void
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
    public function getSpeakersWithDescriptionRawForTwoSpeakersReturnsTwoSpeakers(): void
    {
        $this->addSpeakerRelation(['title' => 'test speaker 1']);
        $this->addSpeakerRelation(['title' => 'test speaker 2']);

        self::assertStringContainsString(
            'test speaker 1',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
        self::assertStringContainsString(
            'test speaker 2',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawForTwoSpeakersWithOrganizationReturnsTwoSpeakersWithOrganization(): void
    {
        $this->addSpeakerRelation(
            ['organization' => 'test organization 1']
        );
        $this->addSpeakerRelation(
            ['organization' => 'test organization 2']
        );

        self::assertStringContainsString(
            'test organization 1',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
        self::assertStringContainsString(
            'test organization 2',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawOnlyReturnsSpeakersOfGivenType(): void
    {
        $this->addSpeakerRelation(['title' => 'test speaker']);
        $this->addPartnerRelation(['title' => 'test partner']);

        self::assertStringNotContainsString(
            'test partner',
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypePartner(): void
    {
        $this->addPartnerRelation(['title' => 'test partner']);

        self::assertStringContainsString(
            'test partner',
            $this->subject->getSpeakersWithDescriptionRaw('partners')
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypeLeaders(): void
    {
        $this->addLeaderRelation(['title' => 'test leader']);

        self::assertStringContainsString(
            'test leader',
            $this->subject->getSpeakersWithDescriptionRaw('leaders')
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypeTutors(): void
    {
        $this->addTutorRelation(['title' => 'test tutor']);

        self::assertStringContainsString(
            'test tutor',
            $this->subject->getSpeakersWithDescriptionRaw('tutors')
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawSeparatesMultipleSpeakersWithLineFeeds(): void
    {
        $this->addSpeakerRelation(['title' => 'foo']);
        $this->addSpeakerRelation(['title' => 'bar']);

        self::assertStringContainsString(
            "foo\nbar",
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawDoesNotSeparateMultipleSpeakersWithCarriageReturnsAndLineFeeds(): void
    {
        $this->addSpeakerRelation(['title' => 'foo']);
        $this->addSpeakerRelation(['title' => 'bar']);

        self::assertStringNotContainsString(
            "foo\r\nbar",
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawDoesNotSeparateSpeakersDescriptionAndTitleWithCarriageReturnsAndLineFeeds(): void
    {
        $this->addSpeakerRelation(
            [
                'title' => 'foo',
                'description' => 'bar',
            ]
        );

        self::assertNotRegExp(
            "/foo\r\nbar/",
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithDescriptionRawSeparatesSpeakersDescriptionAndTitleWithLineFeeds(): void
    {
        $this->addSpeakerRelation(
            [
                'title' => 'foo',
                'description' => 'bar',
            ]
        );

        self::assertRegExp(
            "/foo\nbar/",
            $this->subject->getSpeakersWithDescriptionRaw()
        );
    }

    // Tests concerning getSpeakersShort

    /**
     * @test
     */
    public function getSpeakersShortWithNoSpeakersReturnsAnEmptyString(): void
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
    public function getSpeakersShortWithSingleSpeakersReturnsSingleSpeaker(): void
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
    public function getSpeakersShortWithMultipleSpeakersReturnsTwoSpeakers(): void
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
    public function getSpeakersShortReturnsSpeakerLinkedToSpeakerHomepage(): void
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
    public function getSpeakersForSpeakerWithoutHomepageReturnsSpeakerNameWithoutLinkTag(): void
    {
        $speaker = [
            'title' => 'test speaker',
        ];

        $this->addSpeakerRelation($speaker);
        $this->createPi1();

        $shortSpeakerOutput
            = $this->subject->getSpeakersShort($this->pi1);

        self::assertStringContainsString(
            'test speaker',
            $shortSpeakerOutput
        );
        self::assertStringNotContainsString(
            '<a',
            $shortSpeakerOutput
        );
    }

    // Test concerning the collision check

    /**
     * @test
     */
    public function isUserBlockForZeroUserUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->isUserBlocked(0);
    }

    /**
     * @test
     */
    public function isUserBlockForNegativeUserUidThrowsException(): void
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
    ): void {
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
    ): void {
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
    public function collidingEventsDoNotCollideIfCollisionSkipIsEnabledInConfiguration(): void
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
    public function collidingEventsDoNoCollideIfCollisionSkipIsEnabledForThisEvent(): void
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
    public function collidingEventsDoNoCollideIfCollisionSkipIsEnabledForAnotherEvent(): void
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
    public function notCollidesWithEventWithSurroundingTimeSlots(): void
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
    public function collidesWithEventWithTimeSlots(int $registrationBegin, int $registrationEnd): void
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
     * @return array<string, array<int, array<int, array<int, int>>>>
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
    public function timeSlotsCollideWithCollidingTimeSlots(array $timeSlotDates): void
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
     * @return array<string, array<int, array<int, array<int, int>>>>
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
    public function timeSlotsDoNotCollideWithCollisionFreeTimeSlots(array $timeSlotDates): void
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

    // Tests for the icons

    /**
     * @test
     */
    public function usesCorrectIconForSingleEvent(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);

        self::assertStringContainsString(
            'EventComplete.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForTopic(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);

        self::assertStringContainsString(
            'EventTopic.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForDateRecord(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);

        self::assertStringContainsString(
            'EventDate.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForHiddenSingleEvent(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);
        $this->subject->setHidden(true);

        self::assertStringContainsString(
            'overlay-hidden.svg',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForHiddenTopic(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);
        $this->subject->setHidden(true);

        self::assertStringContainsString(
            'overlay-hidden.svg',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForHiddenDate(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);
        $this->subject->setHidden(true);

        self::assertStringContainsString(
            'overlay-hidden.svg',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForVisibleTimedSingleEvent(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);
        $this->subject->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertStringContainsString(
            'EventComplete.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForVisibleTimedTopic(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);
        $this->subject->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertStringContainsString(
            'EventTopic.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForVisibleTimedDate(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);
        $this->subject->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertStringContainsString(
            'EventDate.',
            $this->subject->getRecordIcon()
        );
    }

    /**
     * @test
     */
    public function usesCorrectIconForExpiredSingleEvent(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_COMPLETE);
        $this->subject->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertStringContainsString('overlay-endtime.svg', $this->subject->getRecordIcon());
    }

    /**
     * @test
     */
    public function usesCorrectIconForExpiredTimedTopic(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_TOPIC);
        $this->subject->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertStringContainsString('overlay-endtime.svg', $this->subject->getRecordIcon());
    }

    /**
     * @test
     */
    public function usesCorrectIconForExpiredTimedDate(): void
    {
        $this->subject->setRecordType(\Tx_Seminars_Model_Event::TYPE_DATE);
        $this->subject->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

        self::assertStringContainsString('overlay-endtime.svg', $this->subject->getRecordIcon());
    }

    // Tests for hasSeparateDetailsPage

    /**
     * @test
     */
    public function hasSeparateDetailsPageIsFalseByDefault(): void
    {
        self::assertFalse(
            $this->subject->hasSeparateDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasSeparateDetailsPageReturnsTrueForInternalSeparateDetailsPage(): void
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
    public function hasSeparateDetailsPageReturnsTrueForExternalSeparateDetailsPage(): void
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

    // Tests for getDetailsPage

    /**
     * @test
     */
    public function getDetailsPageForNoSeparateDetailsPageSetReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function getDetailsPageForInternalSeparateDetailsPageSetReturnsThisPage(): void
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
    public function getDetailsPageForExternalSeparateDetailsPageSetReturnsThisPage(): void
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

    // Tests concerning getPlaceWithDetails

    /**
     * @test
     */
    public function getPlaceWithDetailsReturnsWillBeAnnouncedForNoPlace(): void
    {
        $this->createPi1();
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsTitleOfOnePlace(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place']);

        self::assertStringContainsString(
            'a place',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsTitleOfAllRelatedPlaces(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertStringContainsString(
            'a place',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
        self::assertStringContainsString(
            'another place',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsListsDuplicateAssociationsOnlyOnce(): void
    {
        $this->createPi1();
        $placeTitle = 'a place';
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites', ['title' => $placeTitle]);
        $eventUid = $this->subject->getUid();

        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $eventUid, $placeUid);
        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $eventUid, $placeUid);
        $this->subject->setNumberOfPlaces(2);

        $result = $this->subject->getPlaceWithDetails($this->pi1);

        self::assertSame(1, \substr_count($result, $placeTitle));
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsAddressOfOnePlace(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(
            ['title' => 'a place', 'address' => 'a street']
        );

        self::assertStringContainsString(
            'a street',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsForNonEmptyZipAndCityContainsZip(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(
            ['title' => 'a place', 'zip' => '12345', 'city' => 'Hamm']
        );

        self::assertStringContainsString(
            '12345',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsForNonEmptyZipAndEmptyCityNotContainsZip(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(
            ['title' => 'a place', 'zip' => '12345', 'city' => '']
        );

        self::assertStringNotContainsString(
            '12345',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsCityOfOnePlace(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place', 'city' => 'Emden']);

        self::assertStringContainsString(
            'Emden',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsCountryOfOnePlace(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(['title' => 'a place', 'country' => 'de']);

        self::assertStringContainsString(
            'Deutschland',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsHomepageLinkOfOnePlace(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(['homepage' => 'www.test.com']);

        self::assertStringContainsString(
            ' href="http://www.test.com',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsContainsDirectionsOfOnePlace(): void
    {
        $this->createPi1();
        $this->addPlaceRelation(['directions' => 'Turn right.']);

        self::assertStringContainsString(
            'Turn right.',
            $this->subject->getPlaceWithDetails($this->pi1)
        );
    }

    // Tests concerning getPlaceWithDetailsRaw

    /**
     * @test
     */
    public function getPlaceWithDetailsRawReturnsWillBeAnnouncedForNoPlace(): void
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsTitleOfOnePlace(): void
    {
        $this->addPlaceRelation(['title' => 'a place']);

        self::assertStringContainsString(
            'a place',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsTitleOfAllRelatedPlaces(): void
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertStringContainsString(
            'a place',
            $this->subject->getPlaceWithDetailsRaw()
        );
        self::assertStringContainsString(
            'another place',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawListsDuplicateAssociationsOnlyOnce(): void
    {
        $placeTitle = 'a place';
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites', ['title' => $placeTitle]);
        $eventUid = $this->subject->getUid();

        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $eventUid, $placeUid);
        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $eventUid, $placeUid);
        $this->subject->setNumberOfPlaces(2);

        $result = $this->subject->getPlaceWithDetailsRaw();

        self::assertSame(1, \substr_count($result, $placeTitle));
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsAddressOfOnePlace(): void
    {
        $this->addPlaceRelation(
            ['title' => 'a place', 'address' => 'a street']
        );

        self::assertStringContainsString(
            'a street',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsCityOfOnePlace(): void
    {
        $this->addPlaceRelation(['title' => 'a place', 'city' => 'Emden']);

        self::assertStringContainsString(
            'Emden',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsCountryOfOnePlace(): void
    {
        $this->addPlaceRelation(['title' => 'a place', 'country' => 'de']);

        self::assertStringContainsString(
            'Deutschland',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsHomepageUrlOfOnePlace(): void
    {
        $this->addPlaceRelation(['homepage' => 'www.test.com']);

        self::assertStringContainsString(
            'www.test.com',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawContainsDirectionsOfOnePlace(): void
    {
        $this->addPlaceRelation(['directions' => 'Turn right.']);

        self::assertStringContainsString(
            'Turn right.',
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawSeparatesMultiplePlacesWithLineFeeds(): void
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertStringContainsString(
            "a place\nanother place",
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithDetailsRawDoesNotSeparateMultiplePlacesWithCarriageReturnsAndLineFeeds(): void
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertStringNotContainsString(
            "another place\r\na place",
            $this->subject->getPlaceWithDetailsRaw()
        );
    }

    // Tests for getPlaceShort

    /**
     * @test
     */
    public function getPlaceShortReturnsWillBeAnnouncedForNoPlaces(): void
    {
        self::assertSame(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getPlaceShort()
        );
    }

    /**
     * @test
     */
    public function getPlaceShortReturnsPlaceNameForOnePlace(): void
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
    public function getPlaceShortReturnsPlaceNamesWithCommaForTwoPlaces(): void
    {
        $this->addPlaceRelation(['title' => 'a place']);
        $this->addPlaceRelation(['title' => 'another place']);

        self::assertStringContainsString(
            'a place',
            $this->subject->getPlaceShort()
        );
        self::assertStringContainsString(
            ', ',
            $this->subject->getPlaceShort()
        );
        self::assertStringContainsString(
            'another place',
            $this->subject->getPlaceShort()
        );
    }

    /**
     * @test
     */
    public function getPlaceShortListsDuplicateAssociationsOnlyOnce(): void
    {
        $placeTitle = 'a place';
        $placeUid = $this->testingFramework->createRecord('tx_seminars_sites', ['title' => $placeTitle]);
        $eventUid = $this->subject->getUid();

        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $eventUid, $placeUid);
        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $eventUid, $placeUid);
        $this->subject->setNumberOfPlaces(2);

        $result = $this->subject->getPlaceShort();

        self::assertSame(1, \substr_count($result, $placeTitle));
    }

    // Tests concerning getPlaces

    /**
     * @test
     */
    public function getPlacesForEventWithNoPlacesReturnsEmptyList(): void
    {
        self::assertInstanceOf(Collection::class, $this->subject->getPlaces());
    }

    /**
     * @test
     */
    public function getPlacesForSeminarWithOnePlacesReturnsListWithPlaceModel(): void
    {
        $this->addPlaceRelation();

        self::assertInstanceOf(\Tx_Seminars_Model_Place::class, $this->subject->getPlaces()->first());
    }

    /**
     * @test
     */
    public function getPlacesForSeminarWithOnePlacesReturnsListWithOnePlace(): void
    {
        $this->addPlaceRelation();

        self::assertCount(1, $this->subject->getPlaces());
    }

    // Tests concerning isOwnerFeUser

    /**
     * @test
     */
    public function isOwnerFeUserForNoOwnerReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->isOwnerFeUser()
        );
    }

    /**
     * @test
     */
    public function isOwnerFeUserForLoggedInUserOtherThanOwnerReturnsFalse(): void
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setOwnerUid($userUid + 1);

        self::assertFalse(
            $this->subject->isOwnerFeUser()
        );
    }

    /**
     * @test
     */
    public function isOwnerFeUserForLoggedInUserOtherThanOwnerReturnsTrue(): void
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setOwnerUid($ownerUid);

        self::assertTrue(
            $this->subject->isOwnerFeUser()
        );
    }

    // Tests concerning getOwner

    /**
     * @test
     */
    public function getOwnerForExistingOwnerReturnsFrontEndUserInstance(): void
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setOwnerUid($ownerUid);

        self::assertInstanceOf(FrontEndUser::class, $this->subject->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerForExistingOwnerReturnsUserWithOwnersUid(): void
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
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
    public function getOwnerForNoOwnerReturnsNull(): void
    {
        self::assertNull(
            $this->subject->getOwner()
        );
    }

    // Tests concerning hasOwner

    /**
     * @test
     */
    public function hasOwnerForExistingOwnerReturnsTrue(): void
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setOwnerUid($ownerUid);

        self::assertTrue(
            $this->subject->hasOwner()
        );
    }

    /**
     * @test
     */
    public function hasOwnerForNoOwnerReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasOwner()
        );
    }

    // Tests concerning getVacanciesString

    /**
     * @test
     */
    public function getVacanciesStringForCanceledEventWithVacanciesReturnsEmptyString(): void
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
    public function getVacanciesStringWithoutRegistrationNeededReturnsEmptyString(): void
    {
        $this->subject->setConfigurationValue('showVacanciesThreshold', 10);
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setNeedsRegistration(false);

        self::assertSame('', $this->subject->getVacanciesString());
    }

    /**
     * @test
     */
    public function getVacanciesStringForNonZeroVacanciesAndPastDeadlineReturnsEmptyString(): void
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
    public function getVacanciesStringForNonZeroVacanciesBelowThresholdReturnsNumberOfVacancies(): void
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
    public function getVacanciesStringForNoVancanciesReturnsFullyBooked(): void
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
    public function getVacanciesStringForVacanciesGreaterThanThresholdReturnsEnough(): void
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
    public function getVacanciesStringForVacanciesEqualToThresholdReturnsEnough(): void
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
    public function getVacanciesStringForUnlimitedVacanciesAndZeroRegistrationsReturnsEnough(): void
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
    public function getVacanciesStringForUnlimitedVacanciesAndOneRegistrationReturnsEnough(): void
    {
        $this->subject->setBeginDate($this->now + 10000);
        $this->subject->setUnlimitedVacancies();
        $this->subject->setNumberOfAttendances(1);

        self::assertSame(
            $this->getLanguageService()->getLL('message_enough'),
            $this->subject->getVacanciesString()
        );
    }

    // Tests for the getImage function

    /**
     * @test
     */
    public function getImageForNonEmptyImageReturnsImageFileName(): void
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
    public function getImageForEmptyImageReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getImage()
        );
    }

    // Tests for the hasImage function

    /**
     * @test
     */
    public function hasImageForNonEmptyImageReturnsTrue(): void
    {
        $this->subject->setImage('foo.gif');

        self::assertTrue(
            $this->subject->hasImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForEmptyImageReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasImage()
        );
    }

    // Tests for getLanguageKeySuffixForType

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeReturnsSpeakerType(): void
    {
        $this->addLeaderRelation([]);

        self::assertStringContainsString(
            'leaders_',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMaleSpeakerReturnsMaleMarkerPart(): void
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertStringContainsString(
            '_male',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForFemaleSpeakerReturnsFemaleMarkerPart(): void
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );

        self::assertStringContainsString(
            '_female',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForSingleSpeakerWithoutGenderReturnsUnknownMarkerPart(): void
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN]
        );

        self::assertStringContainsString(
            '_unknown',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForSingleSpeakerReturnsSingleMarkerPart(): void
    {
        $this->addSpeakerRelation([]);

        self::assertStringContainsString(
            '_single_',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleSpeakersWithoutGenderReturnsSpeakerType(): void
    {
        $this->addSpeakerRelation([]);
        $this->addSpeakerRelation([]);

        self::assertStringContainsString(
            'speakers',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleMaleSpeakerReturnsMultipleAndMaleMarkerPart(): void
    {
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertStringContainsString(
            '_multiple_male',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleFemaleSpeakerReturnsMultipleAndFemaleMarkerPart(): void
    {
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );

        self::assertStringContainsString(
            '_multiple_female',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForMultipleSpeakersWithMixedGendersReturnsSpeakerType(): void
    {
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );
        $this->addSpeakerRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE]
        );

        self::assertStringContainsString(
            'speakers',
            $this->subject->getLanguageKeySuffixForType('speakers')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForOneSpeakerWithoutGenderAndOneWithGenderReturnsSpeakerType(): void
    {
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN]
        );
        $this->addLeaderRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertStringContainsString(
            'leaders',
            $this->subject->getLanguageKeySuffixForType('leaders')
        );
    }

    /**
     * @test
     */
    public function getLanguageKeySuffixForTypeForSingleMaleTutorReturnsCorrespondingMarkerPart(): void
    {
        $this->addTutorRelation(
            ['gender' => \Tx_Seminars_OldModel_Speaker::GENDER_MALE]
        );

        self::assertSame(
            'tutors_single_male',
            $this->subject->getLanguageKeySuffixForType('tutors')
        );
    }

    // Tests concerning hasRequirements

    /**
     * @test
     */
    public function hasRequirementsForTopicWithoutRequirementsReturnsFalse(): void
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
    public function hasRequirementsForDateOfTopicWithoutRequirementsReturnsFalse(): void
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
    public function hasRequirementsForTopicWithOneRequirementReturnsTrue(): void
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
    public function hasRequirementsForDateOfTopicWithOneRequirementReturnsTrue(): void
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
    public function hasRequirementsForTopicWithTwoRequirementsReturnsTrue(): void
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

    // Tests concerning hasDependencies

    /**
     * @test
     */
    public function hasDependenciesForTopicWithoutDependenciesReturnsFalse(): void
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
    public function hasDependenciesForDateOfTopicWithoutDependenciesReturnsFalse(): void
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
    public function hasDependenciesForTopicWithOneDependencyReturnsTrue(): void
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
    public function hasDependenciesForDateOfTopicWithOneDependencyReturnsTrue(): void
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
    public function hasDependenciesForTopicWithTwoDependenciesReturnsTrue(): void
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

    // Tests concerning getRequirements

    /**
     * @test
     */
    public function getRequirementsReturnsSeminarBag(): void
    {
        self::assertInstanceOf(\Tx_Seminars_Bag_Event::class, $this->subject->getRequirements());
    }

    /**
     * @test
     */
    public function getRequirementsForNoRequirementsReturnsEmptyBag(): void
    {
        self::assertTrue(
            $this->subject->getRequirements()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getRequirementsForOneRequirementReturnsBagWithOneTopic(): void
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
    public function getRequirementsForDateOfTopicWithOneRequirementReturnsBagWithOneTopic(): void
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
    public function getRequirementsForTwoRequirementsReturnsBagWithTwoItems(): void
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

    // Tests concerning getDependencies

    /**
     * @test
     */
    public function getDependenciesReturnsSeminarBag(): void
    {
        self::assertInstanceOf(\Tx_Seminars_Bag_Event::class, $this->subject->getDependencies());
    }

    /**
     * @test
     */
    public function getDependenciesForNoDependenciesReturnsEmptyBag(): void
    {
        self::assertTrue(
            $this->subject->getDependencies()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getDependenciesForOneDependencyReturnsBagWithOneTopic(): void
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
    public function getDependenciesForDateOfTopicWithOneDependencyReturnsBagWithOneTopic(): void
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
    public function getDependenciesForTwoDependenciesReturnsBagWithTwoItems(): void
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

    // Tests concerning isConfirmed

    /**
     * @test
     */
    public function isConfirmedForStatusPlannedReturnsFalse(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse(
            $this->subject->isConfirmed()
        );
    }

    /**
     * @test
     */
    public function isConfirmedForStatusConfirmedReturnsTrue(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertTrue(
            $this->subject->isConfirmed()
        );
    }

    /**
     * @test
     */
    public function isConfirmedForStatusCanceledReturnsFalse(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->isConfirmed()
        );
    }

    // Tests concerning isCanceled

    /**
     * @test
     */
    public function isCanceledForPlannedEventReturnsFalse(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse(
            $this->subject->isCanceled()
        );
    }

    /**
     * @test
     */
    public function isCanceledForCanceledEventReturnsTrue(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertTrue(
            $this->subject->isCanceled()
        );
    }

    /**
     * @test
     */
    public function isCanceledForConfirmedEventReturnsFalse(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse(
            $this->subject->isCanceled()
        );
    }

    // Tests concerning isPlanned

    /**
     * @test
     */
    public function isPlannedForStatusPlannedReturnsTrue(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertTrue(
            $this->subject->isPlanned()
        );
    }

    /**
     * @test
     */
    public function isPlannedForStatusConfirmedReturnsFalse(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse(
            $this->subject->isPlanned()
        );
    }

    /**
     * @test
     */
    public function isPlannedForStatusCanceledReturnsFalse(): void
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->isPlanned()
        );
    }

    // Tests concerning setEventTakesPlaceReminderSentFlag

    /**
     * @test
     */
    public function setEventTakesPlaceReminderSentFlagSetsFlagToTrue(): void
    {
        $this->subject->setEventTakesPlaceReminderSentFlag();

        self::assertTrue(
            $this->subject->getRecordPropertyBoolean(
                'event_takes_place_reminder_sent'
            )
        );
    }

    // Tests concerning setCancelationDeadlineReminderSentFlag

    /**
     * @test
     */
    public function setCancellationDeadlineReminderSentFlagToTrue(): void
    {
        $this->subject->setCancelationDeadlineReminderSentFlag();

        self::assertTrue(
            $this->subject->getRecordPropertyBoolean(
                'cancelation_deadline_reminder_sent'
            )
        );
    }

    // Tests concerning getCancelationDeadline

    /**
     * @test
     */
    public function getCancellationDeadlineForEventWithoutSpeakerReturnsBeginDateOfEvent(): void
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
    public function getCancellationDeadlineForEventWithSpeakerWithoutCancellationPeriodReturnsBeginDateOfEvent(): void
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
    public function getCancellationDeadlineForEventWithTwoSpeakersWithoutCancellationPeriodReturnsBeginDateOfEvent(): void
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
    public function getCancellationDeadlineForEventWithOneSpeakersWithCancellationPeriodReturnsBeginDateMinusCancelationPeriod(): void
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
    public function getCancellationDeadlineForEventWithTwoSpeakersWithCancellationPeriodsReturnsBeginDateMinusBiggestCancelationPeriod(): void
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
    public function getCancellationDeadlineForEventWithoutBeginDateThrowsException(): void
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

    // Tests concerning the license expiry

    /**
     * @test
     */
    public function hasExpiryForNoExpiryReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasExpiry()
        );
    }

    /**
     * @test
     */
    public function hasExpiryForNonZeroExpiryReturnsTrue(): void
    {
        $this->subject->setExpiry(42);

        self::assertTrue(
            $this->subject->hasExpiry()
        );
    }

    /**
     * @test
     */
    public function getExpiryForNoExpiryReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getExpiry()
        );
    }

    /**
     * @test
     */
    public function getExpiryForNonZeroExpiryReturnsFormattedDate(): void
    {
        $this->subject->setExpiry(mktime(0, 0, 0, 12, 31, 2000));

        self::assertSame(
            '31.12.2000',
            $this->subject->getExpiry()
        );
    }

    // Tests concerning getEventData

    /**
     * @test
     */
    public function getEventDataReturnsFormattedUnregistrationDeadline(): void
    {
        $this->configuration->setAsBoolean('showTimeOfUnregistrationDeadline', false);
        $this->subject->setUnregistrationDeadline(1893488400);

        self::assertSame(
            '01.01.2030',
            $this->subject->getEventData('deadline_unregistration')
        );
    }

    /**
     * @test
     */
    public function getEventDataForShowTimeOfUnregistrationDeadlineTrueReturnsFormattedUnregistrationDeadlineWithTime(): void
    {
        $this->configuration->setAsBoolean('showTimeOfUnregistrationDeadline', true);
        $this->subject->setUnregistrationDeadline(1893488400);

        self::assertSame('01.01.2030 09:00', $this->subject->getEventData('deadline_unregistration'));
    }

    /**
     * @test
     */
    public function getEventDataForUnregistrationDeadlineZeroReturnsEmptyString(): void
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
    public function getEventDataForEventWithMultipleLodgingsSeparatesLodgingsWithLineFeeds(): void
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

        self::assertStringContainsString(
            "foo\nbar",
            $this->subject->getEventData('lodgings')
        );
    }

    /**
     * @test
     */
    public function getEventDataForEventWithMultipleLodgingsDoesNotSeparateLodgingsWithCarriageReturnsAndLineFeeds(): void
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

        self::assertStringNotContainsString(
            "foo\r\nbar",
            $this->subject->getEventData('lodgings')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithCarriageReturnAndLinefeedGetsConvertedToLineFeedOnly(): void
    {
        $this->subject->setDescription("foo\r\nbar");

        self::assertStringContainsString(
            "foo\nbar",
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithTwoAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed(): void
    {
        $this->subject->setDescription("foo\n\nbar");

        self::assertStringContainsString(
            "foo\nbar",
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithThreeAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed(): void
    {
        $this->subject->setDescription("foo\n\n\nbar");

        self::assertStringContainsString(
            "foo\nbar",
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataDataWithFourAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed(): void
    {
        $this->subject->setDescription("foo\n\n\n\nbar");

        self::assertStringContainsString(
            "foo\nbar",
            $this->subject->getEventData('description')
        );
    }

    /**
     * @test
     */
    public function getEventDataForEventWithDateUsesHyphenAsDateSeparator(): void
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY);

        self::assertStringContainsString(
            '-',
            $this->subject->getEventData('date')
        );
    }

    /**
     * @test
     */
    public function getEventDataForEventWithTimeUsesHyphenAsTimeSeparator(): void
    {
        $this->subject->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
        $this->subject->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

        self::assertStringContainsString(
            '-',
            $this->subject->getEventData('time')
        );
    }

    /**
     * @test
     */
    public function getEventDataSeparatesPlacePartsByCommaAndSpace(): void
    {
        $place = [
            'title' => 'Hotel Ibis',
            'homepage' => '',
            'address' => 'Kaiser-Karl-Ring 91',
            'city' => 'Bonn',
            'country' => '',
            'directions' => '',
        ];

        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
    public function getEventDataSeparatesTwoPlacesByLineFeed(): void
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

        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['getPlacesAsArray', 'hasPlace']);
        $subject->method('getPlacesAsArray')->willReturn([$place1, $place2]);
        $subject->method('hasPlace')->willReturn(true);

        self::assertSame(
            "Hotel Ibis\nWasserwerk",
            $subject->getEventData('place')
        );
    }

    /**
     * @test
     */
    public function getEventDataForPlaceWithoutZipContainsTitleAndAddressAndCity(): void
    {
        $place = [
            'title' => 'Hotel Ibis',
            'address' => 'Kaiser-Karl-Ring 91',
            'zip' => '',
            'city' => 'Bonn',
        ];

        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
    public function getEventDataForPlaceWithZipContainsTitleAndAddressAndZipAndCity(): void
    {
        $place = [
            'title' => 'Hotel Ibis',
            'address' => 'Kaiser-Karl-Ring 91',
            'zip' => '53111',
            'city' => 'Bonn',
        ];

        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['getPlacesAsArray', 'hasPlace']);
        $subject->method('getPlacesAsArray')->willReturn([$place]);
        $subject->method('hasPlace')->willReturn(true);

        self::assertSame(
            'Hotel Ibis, Kaiser-Karl-Ring 91, 53111 Bonn',
            $subject->getEventData('place')
        );
    }

    // Tests concerning dumpSeminarValues

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleGivenReturnsTitle(): void
    {
        self::assertStringContainsString(
            $this->subject->getTitle(),
            $this->subject->dumpSeminarValues('title')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleGivenReturnsLabelForTitle(): void
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_title'),
            $this->subject->dumpSeminarValues('title')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleGivenReturnsTitleWithLineFeedAtEndOfLine(): void
    {
        self::assertRegExp(
            '/\\n$/',
            $this->subject->dumpSeminarValues('title')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForTitleAndDescriptionGivenReturnsTitleAndDescription(): void
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
    public function dumpSeminarValuesForEventWithoutDescriptionAndDescriptionGivenReturnsDescriptionLabelWithColonsAndLineFeed(): void
    {
        $this->subject->setDescription('');

        self::assertSame(
            $this->getLanguageService()->getLL('label_description') . ":\n",
            $this->subject->dumpSeminarValues('description')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForEventWithNoVacanciesAndVacanciesGivenReturnsVacanciesLabelWithNumber(): void
    {
        $this->subject->setNumberOfAttendances(2);
        $this->subject->setAttendancesMax(2);
        $this->subject->setNeedsRegistration(true);

        self::assertSame(
            $this->getLanguageService()->getLL('label_vacancies') . ": 0\n",
            $this->subject->dumpSeminarValues('vacancies')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForEventWithOneVacancyAndVacanciesGivenReturnsNumberOfVacancies(): void
    {
        $this->subject->setNumberOfAttendances(1);
        $this->subject->setAttendancesMax(2);
        $this->subject->setNeedsRegistration(true);

        self::assertSame(
            $this->getLanguageService()->getLL('label_vacancies') . ": 1\n",
            $this->subject->dumpSeminarValues('vacancies')
        );
    }

    /**
     * @test
     */
    public function dumpSeminarValuesForEventWithUnlimitedVacanciesAndVacanciesGivenReturnsVacanciesUnlimitedString(): void
    {
        $this->subject->setUnlimitedVacancies();

        self::assertSame(
            $this->getLanguageService()->getLL('label_vacancies') . ': ' .
            $this->getLanguageService()->getLL('label_unlimited') . "\n",
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
    public function dumpSeminarValuesCreatesNoDoubleColonsAfterLabel(string $fieldName): void
    {
        $this->subject->setRecordPropertyString($fieldName, '1234 some value');

        $result = $this->subject->dumpSeminarValues($fieldName);

        self::assertStringNotContainsString('::', $result);
    }

    // Tests regarding the registration begin date

    /**
     * @test
     */
    public function hasRegistrationBeginForNoRegistrationBeginReturnsFalse(): void
    {
        $this->subject->setRegistrationBeginDate(0);

        self::assertFalse(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationBeginForEventWithRegistrationBeginReturnsTrue(): void
    {
        $this->subject->setRegistrationBeginDate(42);

        self::assertTrue(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithoutRegistrationBeginReturnsZero(): void
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
    public function getRegistrationBeginAsUnixTimestampForEventWithRegistrationBeginReturnsRegistrationBeginAsUnixTimestamp(): void
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
    public function getRegistrationBeginForEventWithoutRegistrationBeginReturnsEmptyString(): void
    {
        $this->subject->setRegistrationBeginDate(0);

        self::assertSame(
            '',
            $this->subject->getRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginForEventWithRegistrationBeginReturnsFormattedRegistrationBegin(): void
    {
        $this->subject->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME']);

        self::assertSame(
            strftime('%d.%m.%Y %H:%M', $GLOBALS['SIM_EXEC_TIME']),
            $this->subject->getRegistrationBegin()
        );
    }

    // Tests regarding the description.

    /**
     * @test
     */
    public function getDescriptionWithoutDescriptionReturnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription(): void
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
    public function hasDescriptionWithoutDescriptionReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue(): void
    {
        $this->subject->setDescription('this is a great event.');

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    // Tests regarding the additional information.

    /**
     * @test
     */
    public function getAdditionalInformationWithoutAdditionalInformationReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function setAdditionalInformationSetsAdditionalInformation(): void
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
    public function hasAdditionalInformationWithoutAdditionalInformationReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationWithAdditionalInformationReturnsTrue(): void
    {
        $this->subject->setAdditionalInformation('this is good to know');

        self::assertTrue(
            $this->subject->hasAdditionalInformation()
        );
    }

    // Tests concerning getLatestPossibleRegistrationTime

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithoutAnyDatesReturnsZero(): void
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
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateReturnsBeginDate(): void
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
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineReturnsRegistrationDeadline(): void
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
    public function getLatestPossibleRegistrationTimeForEventWithBeginAndEndDateAndRegistrationForStartedEventsAllowedReturnsEndDate(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForStartedEvents', true);

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

        self::assertSame(
            $this->now + 1000,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineAndRegistrationForStartedEventsAllowedReturnsRegistrationDeadline(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForStartedEvents', true);

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

        self::assertSame(
            $this->now - 1000,
            $subject->getLatestPossibleRegistrationTime()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndWithoutEndDateAndRegistrationForStartedEventsAllowedReturnsBeginDate(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForStartedEvents', true);

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

    // Tests concerning getTopicInteger

    /**
     * @test
     */
    public function getTopicIntegerForSingleEventReturnsDataFromRecord(): void
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
    public function getTopicIntegerForDateReturnsDataFromTopic(): void
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

    // Tests concerning hasTopicInteger

    /**
     * @test
     */
    public function hasTopicIntegerForSingleEventForZeroReturnsFalse(): void
    {
        $this->subject->setRecordPropertyInteger('credit_points', 0);

        self::assertFalse(
            $this->subject->hasTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function hasTopicIntegerForSingleEventForPositiveIntegerReturnsFalse(): void
    {
        $this->subject->setRecordPropertyInteger('credit_points', 1);

        self::assertTrue(
            $this->subject->hasTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function hasTopicIntegerForSingleEventForNegativeIntegerReturnsFalse(): void
    {
        $this->subject->setRecordPropertyInteger('credit_points', -1);

        self::assertTrue(
            $this->subject->hasTopicInteger('credit_points')
        );
    }

    /**
     * @test
     */
    public function hasTopicIntegerForDateForZeroInTopicReturnsFalse(): void
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
    public function hasTopicIntegerForDateForPositiveIntegerInTopicReturnsTrue(): void
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

    // Tests concerning the publication status

    /**
     * @test
     */
    public function getPublicationHashReturnsPublicationHash(): void
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
    public function setPublicationHashSetsPublicationHash(): void
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
    public function isPublishedForEventWithoutPublicationHashIsTrue(): void
    {
        $this->subject->setPublicationHash('');

        self::assertTrue(
            $this->subject->isPublished()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithPublicationHashIsFalse(): void
    {
        $this->subject->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertFalse(
            $this->subject->isPublished()
        );
    }

    // Tests concerning canViewRegistrationsList

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
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndDefaultAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ): void {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
            $pageUid = $this->testingFramework->createFrontEndPage();
            $this->testingFramework->createFakeFrontEnd($pageUid);
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
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndAttendeesManagersAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ): void {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
            $pageUid = $this->testingFramework->createFrontEndPage();
            $this->testingFramework->createFakeFrontEnd($pageUid);
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
     */
    public function canViewRegistrationsListForCsvExport(
        bool $expected,
        bool $loggedIn,
        bool $isVip,
        bool $allowCsvExportForVips
    ): void {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
        $subject = $this->createPartialMock(\Tx_Seminars_OldModel_Event::class, ['needsRegistration', 'isUserVip']);
        $subject->method('needsRegistration')
            ->willReturn(true);
        $subject->method('isUserVip')
            ->willReturn($isVip);
        $subject->init(
            ['allowCsvExportForVips' => $allowCsvExportForVips]
        );

        if ($loggedIn) {
            $pageUid = $this->testingFramework->createFrontEndPage();
            $this->testingFramework->createFakeFrontEnd($pageUid);
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
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndLoginAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ): void {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
            $pageUid = $this->testingFramework->createFrontEndPage();
            $this->testingFramework->createFakeFrontEnd($pageUid);
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
     */
    public function canViewRegistrationsListWithNeedsRegistrationAndWorldAccess(
        bool $expected,
        bool $loggedIn,
        bool $isRegistered,
        bool $isVip,
        string $whichPlugin,
        int $registrationsListPID,
        int $registrationsVipListPID
    ): void {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
            $pageUid = $this->testingFramework->createFrontEndPage();
            $this->testingFramework->createFakeFrontEnd($pageUid);
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

    // Tests concerning canViewRegistrationsListMessage

    /**
     * @test
     */
    public function canViewRegistrationsListMessageWithoutNeededRegistrationReturnsNoRegistrationMessage(): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
    public function canViewRegistrationsListMessageForListAndNoLoginAndAttendeesAccessReturnsPleaseLoginMessage(): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
    public function canViewRegistrationsListMessageForListAndNoLoginAndLoginAccessReturnsPleaseLoginMessage(): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
    public function canViewRegistrationsListMessageForListAndNoLoginAndWorldAccessReturnsEmptyString(): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
     */
    public function canViewRegistrationsListMessageForVipListAndNoLoginReturnsPleaseLoginMessage(string $accessLevel): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
     */
    public function canViewRegistrationsListMessageForVipListAndWorldAccessAndNoLoginReturnsEmptyString(): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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
    public function canViewRegistrationsListMessageWithLoginRoutesParameters(string $whichPlugin, string $accessLevel): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'canViewRegistrationsList']
        );
        $subject->method('needsRegistration')->willReturn(true);
        $subject->method('canViewRegistrationsList')
            ->with($whichPlugin, 0, 0, 0, $accessLevel)
            ->willReturn(true);

        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        $this->testingFramework->createAndLoginFrontEndUser();

        $subject->canViewRegistrationsListMessage($whichPlugin, $accessLevel);
    }

    /**
     * @test
     */
    public function canViewRegistrationsListMessageWithLoginAndAccessGrantedReturnsEmptyString(): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'canViewRegistrationsList']
        );
        $subject->method('needsRegistration')->willReturn(true);
        $subject->method('canViewRegistrationsList')->willReturn(true);

        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $subject->canViewRegistrationsListMessage('list_registrations')
        );
    }

    /**
     * @test
     */
    public function canViewRegistrationsListMessageWithLoginAndAccessDeniedReturnsAccessDeniedMessage(): void
    {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_OldModel_Event::class,
            ['needsRegistration', 'canViewRegistrationsList']
        );
        $subject->method('needsRegistration')->willReturn(true);
        $subject->method('canViewRegistrationsList')->willReturn(false);

        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            $this->getLanguageService()->getLL('message_accessDenied'),
            $subject->canViewRegistrationsListMessage('list_registrations')
        );
    }

    // Tests concerning hasAnyPrice

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
    ): void {
        /** @var \Tx_Seminars_OldModel_Event&MockObject $subject */
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

    // Tests regarding the flag for organizers having been notified about enough attendees.

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesByDefaultReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesReturnsTrueValueFromDatabase(): void
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
    public function setOrganizersBeenNotifiedAboutEnoughAttendeesMarksItAsTrue(): void
    {
        $this->subject->setOrganizersBeenNotifiedAboutEnoughAttendees();

        self::assertTrue(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    // Tests regarding the flag for organizers having been notified about enough attendees.

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsByDefaultReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsReturnsTrueValueFromDatabase(): void
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
    public function muteNotificationEmailsSetsShouldMute(): void
    {
        $this->subject->muteNotificationEmails();

        self::assertTrue(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    // Tests regarding the flag for automatic cancelation/confirmation

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelByDefaultReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->shouldAutomaticallyConfirmOrCancel()
        );
    }

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelReturnsTrueValueFromDatabase(): void
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

    // Tests regarding the number of associated registration records

    /**
     * @test
     */
    public function getNumberOfAssociatedRegistrationRecordsByDefaultReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getNumberOfAssociatedRegistrationRecords());
    }

    /**
     * @test
     */
    public function getNumberOfAssociatedRegistrationRecordsReturnsValueFromDatabase(): void
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
    public function increaseNumberOfAssociatedRegistrationRecordsCanIncreaseItFromZeroToOne(): void
    {
        $this->subject->increaseNumberOfAssociatedRegistrationRecords();

        self::assertSame(1, $this->subject->getNumberOfAssociatedRegistrationRecords());
    }

    /**
     * @test
     */
    public function increaseNumberOfAssociatedRegistrationRecordsCanIncreaseItFromTwoToThree(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['registrations' => 2]
        );
        $subject = new TestingEvent($uid);

        $subject->increaseNumberOfAssociatedRegistrationRecords();

        self::assertSame(3, $subject->getNumberOfAssociatedRegistrationRecords());
    }

    // Tests concerning the price

    /**
     * @test
     */
    public function getPriceOnRequestByDefaultReturnsFalse(): void
    {
        self::assertFalse($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getPriceOnRequestReturnsPriceOnRequest(): void
    {
        $this->subject->setRecordPropertyInteger('price_on_request', 1);

        self::assertTrue($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function setPriceOnRequestSetsPriceOnRequest(): void
    {
        $this->subject->setPriceOnRequest(true);

        self::assertTrue($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getPriceOnRequestForEventDateReturnsFalseValueFromTopic(): void
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
    public function getPriceOnRequestForEventDateReturnsTrueValueFromTopic(): void
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
    public function getCurrentPriceRegularForZeroPriceReturnsForFree(): void
    {
        $this->subject->setRecordPropertyString('price_regular', '0.00');

        $result = $this->subject->getCurrentPriceRegular();

        self::assertSame($this->getLanguageService()->getLL('message_forFree'), $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceRegularForNonZeroPriceReturnsPrice(): void
    {
        $this->subject->setRecordPropertyString('price_regular', '123.45');

        $result = $this->subject->getCurrentPriceRegular();

        self::assertSame('123.45', $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceRegularForPriceOnRequestReturnsLocalizedString(): void
    {
        $this->subject->setRecordPropertyInteger('price_on_request', 1);
        $this->subject->setRecordPropertyString('price_regular', '123.45');

        $result = $this->subject->getCurrentPriceRegular();

        self::assertSame($this->getLanguageService()->getLL('message_onRequest'), $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceSpecialReturnsRegularNonZeroPrice(): void
    {
        $this->subject->setRecordPropertyString('price_regular', '57');
        $this->subject->setRecordPropertyString('price_special', '123.45');

        $result = $this->subject->getCurrentPriceSpecial();

        self::assertSame('123.45', $result);
    }

    /**
     * @test
     */
    public function getCurrentPriceSpecialForPriceOnRequestReturnsLocalizedString(): void
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
    public function getAvailablePricesForAllPricesAvailableWithoutEarlyBirdDeadlineReturnsAllLatePrices(): void
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
    public function getAvailablePricesForAllPricesAvailableWithPastEarlyBirdDeadlineReturnsAllLatePrices(): void
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
    public function getAvailablePricesForAllPricesAvailableWithFuturEarlyBirdDeadlineReturnsAllEarlyBirdPrices(): void
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
    public function getAvailablePricesForNoPricesSetReturnsRegularPriceOnly(): void
    {
        self::assertSame(['regular'], array_keys($this->subject->getAvailablePrices()));
    }
}
