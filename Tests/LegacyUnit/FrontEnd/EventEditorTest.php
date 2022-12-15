<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\EventEditor;
use OliverKlee\Seminars\Mapper\TargetGroupMapper;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\AbstractEditor
 * @covers \OliverKlee\Seminars\FrontEnd\EventEditor
 */
final class EventEditorTest extends TestCase
{
    use LanguageHelper;
    use EmailTrait;
    use MakeInstanceTrait;

    /**
     * @var array<string, string>
     */
    private const CONFIGURATION = [
        'dateFormatYMD' => '%d.%m.%Y',
    ];

    /**
     * @var EventEditor
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var DummyConfiguration
     */
    private $pluginConfiguration;

    /**
     * @var int
     */
    private $recordsPageUid = 0;

    /** @var ConnectionPool */
    private $connectionPool;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->testingFramework = new TestingFramework('tx_seminars');
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->changeRecord('pages', $rootPageUid, ['slug' => '/home']);
        $this->testingFramework->createFakeFrontEnd($rootPageUid);

        $sharedConfiguration = new DummyConfiguration(self::CONFIGURATION);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $sharedConfiguration);
        $this->recordsPageUid = $this->testingFramework->createSystemFolder();
        $this->pluginConfiguration = new DummyConfiguration(self::CONFIGURATION);
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', $this->recordsPageUid);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars_pi1', $this->pluginConfiguration);

        $this->subject = new EventEditor(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'form.' => ['eventEditor.' => []],
            ],
            $this->getFrontEndController()->cObj
        );
        $this->subject->setTestMode();

        $this->email = $this->createEmailMock();

        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function tearDown(): void
    {
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUp();
        }

        RegistrationManager::purgeInstance();
        ConfigurationProxy::purgeInstances();
    }

    // Utility functions.

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Creates a FE user, adds him/her as a VIP to the seminar with the UID in
     * $this->seminarUid and logs him/her in.
     */
    private function createLogInAndAddFeUserAsVip(): void
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['vips' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_feusers_mm',
            $seminarUid,
            $this->testingFramework->createAndLoginFrontEndUser()
        );
        $this->subject->setObjectUid($seminarUid);
    }

    /**
     * Creates a FE user, adds his/her FE user group as a default VIP group via
     * TS setup and logs him/her in.
     *
     * @return int FE user UID
     */
    private function createLogInAndAddFeUserAsDefaultVip(): int
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->subject->setConfigurationValue(
            'defaultEventVipsFeGroupID',
            $feUserGroupUid
        );
        return $this->testingFramework->createAndLoginFrontEndUser($feUserGroupUid);
    }

    /**
     * Creates a FE user, adds him/her as a owner to the seminar with the UID in
     * $this->seminarUid and logs him/her in.
     */
    private function createLogInAndAddFeUserAsOwner(): void
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                ['owner_feuser' => $this->testingFramework->createAndLoginFrontEndUser()]
            )
        );
    }

    /**
     * Creates a front-end user adds his/her front-end user group as event
     * editor front-end group and logs him/her in.
     *
     * @param array $frontEndUserGroupData front-end user group data to set, may be empty
     *
     * @return int FE user UID
     */
    private function createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(array $frontEndUserGroupData = []): int
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup(
            $frontEndUserGroupData
        );
        $this->subject->setConfigurationValue(
            'eventEditorFeGroupID',
            $feUserGroupUid
        );
        return $this->testingFramework->createAndLoginFrontEndUser($feUserGroupUid);
    }

    /**
     * Creates a fixture with the given field as required field.
     *
     * @param string $requiredField
     *        the field which should be required, may be empty
     *
     * @return EventEditor event editor fixture with the given
     *         field as required field
     */
    private function getFixtureWithRequiredField(string $requiredField): EventEditor
    {
        $result = new EventEditor(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'form.' => ['eventEditor.' => []],
                'requiredFrontEndEditorFields' => $requiredField,
            ],
            $this->getFrontEndController()->cObj
        );
        $result->setTestMode();

        return $result;
    }

    // Tests for the utility functions.

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsVipCreatesFeUser(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('fe_users');

        $this->createLogInAndAddFeUserAsVip();

        self::assertEquals(
            1,
            $connection->count('*', 'fe_users', [])
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsVipLogsInFeUser(): void
    {
        $this->createLogInAndAddFeUserAsVip();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsVipAddsUserAsVip(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars');

        $this->createLogInAndAddFeUserAsVip();

        self::assertEquals(
            1,
            $connection->count('*', 'tx_seminars_seminars', ['uid' => $this->subject->getObjectUid(), 'vips' => 1])
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsOwnerCreatesFeUser(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('fe_users');

        $this->createLogInAndAddFeUserAsOwner();

        self::assertEquals(
            1,
            $connection->count('*', 'fe_users', [])
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsOwnerLogsInFeUser(): void
    {
        $this->createLogInAndAddFeUserAsOwner();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsOwnerAddsUserAsOwner(): void
    {
        $query = $this->connectionPool->getQueryBuilderForTable('tx_seminars_seminars');

        $this->createLogInAndAddFeUserAsOwner();
        $result = $query
            ->count('*')
            ->from('tx_seminars_seminars')
            ->where(
                $query->expr()->eq(
                    'uid',
                    $query->createNamedParameter($this->subject->getObjectUid(), \PDO::PARAM_INT)
                ),
                $query->expr()->gt('owner_feuser', $query->createNamedParameter(0, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);

        self::assertEquals(
            1,
            $result
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsDefaultVipCreatesFeUser(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('fe_users');

        $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertEquals(
            1,
            $connection->count('*', 'fe_users', [])
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsDefaultVipLogsInFeUser(): void
    {
        $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFeUserAsDefaultVipAddsFeUserAsDefaultVip(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('fe_users');

        $userUid = $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertSame(
            1,
            $connection->count(
                '*',
                'fe_users',
                ['uid' => $userUid, 'usergroup' => $this->subject->getConfValueInteger('defaultEventVipsFeGroupID')]
            )
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupCreatesFeUser(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('fe_users');

        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        self::assertEquals(
            1,
            $connection->count('*', 'fe_users', [])
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupLogsInFrontEndUser(): void
    {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupAddsFrontEndUserToEventEditorFrontEndGroup(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('fe_users');

        $userUid = $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        self::assertSame(
            1,
            $connection->count(
                '*',
                'fe_users',
                ['uid' => $userUid, 'usergroup' => $this->subject->getConfValueInteger('eventEditorFeGroupID')]
            )
        );
    }

    ////////////////////////////////////////
    // Tests concerning hasAccessMessage()
    ////////////////////////////////////////

    /**
     * @test
     */
    public function hasAccessMessageWithNoLoggedInFeUserReturnsNotLoggedInMessage(): void
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );

        self::assertStringContainsString(
            $this->translate('message_notLoggedIn'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessMessageWithLoggedInFeUserWhoIsNeitherVipNorOwnerReturnsNoAccessMessage(): void
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertStringContainsString(
            $this->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessMessageWithLoggedInFeUserAsOwnerReturnsEmptyResult(): void
    {
        $this->createLogInAndAddFeUserAsOwner();

        self::assertEquals(
            '',
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessMessageWithLoggedInFeUserNotOwnerReturnsNonEmptyResult(): void
    {
        $this->subject->setObjectUid($this->testingFramework->createRecord('tx_seminars_seminars'));
        $this->createLogInAndAddFeUserAsVip();

        self::assertStringContainsString(
            $this->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInUserInUnauthorizedUserGroupReturnsNonEmptyResult(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertStringContainsString(
            $this->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInUserInAuthorizedUserGroupAndNoUidSetReturnsEmptyResult(): void
    {
        $groupUid = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'test']
        );
        $this->testingFramework->createAndLoginFrontEndUser($groupUid);

        $this->subject->setConfigurationValue('eventEditorFeGroupID', $groupUid);

        self::assertEquals(
            '',
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInNonOwnerInAuthorizedUserGroupReturnsNoAccessMessage(): void
    {
        $groupUid = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'test']
        );
        $this->testingFramework->createAndLoginFrontEndUser($groupUid);

        $this->subject->setConfigurationValue('eventEditorFeGroupID', $groupUid);
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );

        self::assertStringContainsString(
            $this->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInOwnerInAuthorizedUserGroupReturnsEmptyResult(): void
    {
        $groupUid = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'test']
        );
        $userUid = $this->testingFramework->createAndLoginFrontEndUser($groupUid);

        $this->subject->setConfigurationValue('eventEditorFeGroupID', $groupUid);
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                ['owner_feuser' => $userUid]
            )
        );

        self::assertEquals(
            '',
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInUserAndInvalidSeminarUidReturnsWrongSeminarMessage(): void
    {
        $groupUid = $this->testingFramework->createFrontEndUserGroup(['title' => 'test']);
        $this->subject->setConfigurationValue('eventEditorFeGroupID', $groupUid);
        $this->testingFramework->createAndLoginFrontEndUser($groupUid);

        $this->subject->setObjectUid($this->testingFramework->getAutoIncrement('tx_seminars_seminars'));

        self::assertStringContainsString(
            $this->translate('message_wrongSeminarNumber'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessMessageForDeletedSeminarUidAndUserLoggedInReturnsWrongSeminarMessage(): void
    {
        $groupUid = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'test']
        );
        $this->testingFramework->createAndLoginFrontEndUser($groupUid);

        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                ['deleted' => 1]
            )
        );

        self::assertStringContainsString(
            $this->translate('message_wrongSeminarNumber'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessMessageForHiddenSeminarUidAndUserLoggedInReturnsEmptyString(): void
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'hidden' => 1,
                    'owner_feuser' => $this->testingFramework->createAndLoginFrontEndUser(),
                ]
            )
        );

        self::assertEquals(
            '',
            $this->subject->hasAccessMessage()
        );
    }

    ////////////////////////////////////////////
    // Tests concerning populateListCategories
    ////////////////////////////////////////////

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function populateListCategoriesDoesNotCrash(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->populateListCategories();
    }

    /**
     * @test
     */
    public function populateListCategoriesShowsCategory(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => '', 'value' => $categoryUid],
            $this->subject->populateListCategories()
        );
    }

    /**
     * @test
     */
    public function populateListCategoriesForNoSetStoragePageReturnsRecordWithAnyPageId(): void
    {
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $this->testingFramework->createAndLoginFrontEndUser();
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            ['caption' => '', 'value' => $categoryUid],
            $this->subject->populateListCategories()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning populateListEventTypes().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListEventTypesShowsEventType(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => '', 'value' => $eventTypeUid],
            $this->subject->populateListEventTypes()
        );
    }

    /**
     * @test
     */
    public function populateListEventTypesReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            ['caption' => '', 'value' => $eventTypeUid],
            $this->subject->populateListEventTypes()
        );
    }

    /////////////////////////////////////////////
    // Tests concerning populateListLodgings().
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListLodgingsShowsLodging(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $lodgingUid = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => '', 'value' => $lodgingUid],
            $this->subject->populateListLodgings()
        );
    }

    /**
     * @test
     */
    public function populateListLodgingsReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $lodgingUid = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            ['caption' => '', 'value' => $lodgingUid],
            $this->subject->populateListLodgings()
        );
    }

    //////////////////////////////////////////
    // Tests concerning populateListFoods().
    //////////////////////////////////////////

    /**
     * @test
     */
    public function populateListFoodsShowsFood(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $foodUid = $this->testingFramework->createRecord(
            'tx_seminars_foods',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => '', 'value' => $foodUid],
            $this->subject->populateListFoods()
        );
    }

    /**
     * @test
     */
    public function populateListFoodsReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $foodUid = $this->testingFramework->createRecord(
            'tx_seminars_foods',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            ['caption' => '', 'value' => $foodUid],
            $this->subject->populateListFoods()
        );
    }

    ///////////////////////////////////////////////////
    // Tests concerning populateListPaymentMethods().
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListPaymentMethodsShowsPaymentMethod(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => '', 'value' => $paymentMethodUid],
            $this->subject->populateListPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function populateListPaymentMethodsReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            ['caption' => '', 'value' => $paymentMethodUid],
            $this->subject->populateListPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function populateListPlacesShowsPlace(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $placeUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListPlaces([])
        );
    }

    /**
     * @test
     */
    public function populateListPlacesReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $placeUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListPlaces([])
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning populateListCheckboxes().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListCheckboxesShowsCheckbox(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $checkboxUid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $checkboxUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListCheckboxes([])
        );
    }

    /**
     * @test
     */
    public function populateListCheckboxesReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $checkboxUid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $checkboxUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListCheckboxes([])
        );
    }

    /////////////////////////////////////////////////
    // Tests concerning populateListTargetGroups().
    /////////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListTargetGroupsShowsTargetGroup(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $targetGroupUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListTargetGroups([])
        );
    }

    /**
     * @test
     */
    public function populateListTargetGroupsReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $targetGroupUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListTargetGroups([])
        );
    }

    /////////////////////////////////////////////
    // Tests concerning populateListSpeakers().
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListSpeakersShowsSpeaker(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $speakerUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListSpeakers()
        );
    }

    /**
     * @test
     */
    public function populateListSpeakersReturnsRecordWithAnyPageId(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->pluginConfiguration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            [
                'caption' => '',
                'value' => $speakerUid,
                'wrapitem' => '|</td><td>&nbsp;',
            ],
            $this->subject->populateListSpeakers()
        );
    }

    /////////////////////////////////////////
    // Tests concerning validateStringField
    /////////////////////////////////////////

    /**
     * @test
     */
    public function validateStringFieldForNonRequiredFieldAndEmptyStringReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->validateString(
                ['elementName' => 'teaser', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validateStringFieldForRequiredFieldAndEmptyStringReturnsFalse(): void
    {
        $subject = $this->getFixtureWithRequiredField('teaser');

        self::assertFalse(
            $subject->validateString(
                ['elementName' => 'teaser', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validateStringFieldForRequiredFieldAndNonEmptyStringReturnsTrue(): void
    {
        $subject = $this->getFixtureWithRequiredField('teaser');

        self::assertTrue(
            $subject->validateString(
                ['elementName' => 'teaser', 'value' => 'foo']
            )
        );
    }

    //////////////////////////////////////////
    // Tests concerning validateIntegerField
    //////////////////////////////////////////

    /**
     * @test
     */
    public function validateIntegerFieldForNonRequiredFieldAndValueZeroReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->validateInteger(
                ['elementName' => 'attendees_max', 'value' => 0]
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldAndValueZeroReturnsFalse(): void
    {
        $subject = $this->getFixtureWithRequiredField('attendees_max');

        self::assertFalse(
            $subject->validateInteger(
                ['elementName' => 'attendees_max', 'value' => 0]
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldAndValueNonZeroReturnsTrue(): void
    {
        $subject = $this->getFixtureWithRequiredField('attendees_max');

        self::assertTrue(
            $subject->validateInteger(
                ['elementName' => 'attendees_max', 'value' => 15]
            )
        );
    }

    //////////////////////////////////
    // Tests concerning validateDate
    //////////////////////////////////

    /**
     * @test
     */
    public function validateDateForNonRequiredFieldAndEmptyStringReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->validateDate(
                ['elementName' => 'begin_date', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validateDateForRequiredFieldAndEmptyStringReturnsFalse(): void
    {
        $subject = $this->getFixtureWithRequiredField('begin_date');

        self::assertFalse(
            $subject->validateDate(
                ['elementName' => 'begin_date', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validateDateForRequiredFieldAndValidDateReturnsTrue(): void
    {
        $subject = $this->getFixtureWithRequiredField('begin_date');

        self::assertTrue(
            $subject->validateDate(
                [
                    'elementName' => 'begin_date',
                    'value' => '10:52 23-05-2008',
                ]
            )
        );
    }

    /**
     * @test
     */
    public function validateDateForRequiredFieldAndNonValidDateReturnsFalse(): void
    {
        $subject = $this->getFixtureWithRequiredField('begin_date');

        self::assertFalse(
            $subject->validateDate(
                [
                    'elementName' => 'begin_date',
                    'value' => 'foo',
                ]
            )
        );
    }

    ///////////////////////////////////
    // Tests concerning validatePrice
    ///////////////////////////////////

    /**
     * @test
     */
    public function validatePriceForNonRequiredFieldAndEmptyStringReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->validatePrice(
                ['elementName' => 'price_regular', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validatePriceForRequiredFieldAndEmptyStringReturnsFalse(): void
    {
        $subject = $this->getFixtureWithRequiredField('price_regular');

        self::assertFalse(
            $subject->validatePrice(
                ['elementName' => 'price_regular', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validatePriceForRequiredFieldAndValidPriceReturnsTrue(): void
    {
        $subject = $this->getFixtureWithRequiredField('price_regular');

        self::assertTrue(
            $subject->validatePrice(
                ['elementName' => 'price_regular', 'value' => '20,08']
            )
        );
    }

    /**
     * @test
     */
    public function validatePriceForRequiredFieldAndInvalidPriceReturnsFalse(): void
    {
        $subject = $this->getFixtureWithRequiredField('price_regular');

        self::assertFalse(
            $subject->validatePrice(
                ['elementName' => 'price_regular', 'value' => 'foo']
            )
        );
    }

    ///////////////////////////////////////////
    // Tests concerning populateListCountries
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function populateListCountriesContainsGermany(): void
    {
        self::assertContains(
            ['caption' => 'Deutschland', 'value' => 54],
            EventEditor::populateListCountries()
        );
    }

    /**
     * @test
     */
    public function populateListCountriesSortsResultsByLocalCountryName(): void
    {
        $countries = EventEditor::populateListCountries();
        $positionGermany = \array_search(
            ['caption' => 'Deutschland', 'value' => 54],
            $countries,
            true
        );
        $positionGambia = \array_search(
            ['caption' => 'Gambia', 'value' => 81],
            $countries,
            true
        );

        self::assertTrue(
            $positionGermany < $positionGambia
        );
    }

    ///////////////////////////////////////////
    // Tests concerning populateListSkills
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function populateListSkillsHasSkillFromDatabase(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_skills',
            ['title' => 'Juggling', 'pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => 'Juggling', 'value' => $uid],
            EventEditor::populateListSkills()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning makeListToFormidableList
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function makeListToFormidableListForEmptyListGivenReturnsEmptyArray(): void
    {
        self::assertEquals(
            [],
            EventEditor::makeListToFormidableList(new Collection())
        );
    }

    /**
     * @test
     */
    public function makeListToFormidableListForListWithOneElementReturnsModelDataInArray(): void
    {
        $targetGroup = MapperRegistry::get(TargetGroupMapper::class)
            ->getLoadedTestingModel(['title' => 'foo']);

        $list = new Collection();
        $list->add($targetGroup);

        self::assertContains(
            ['caption' => 'foo', 'value' => $targetGroup->getUid()],
            EventEditor::makeListToFormidableList($list)
        );
    }

    /**
     * @test
     */
    public function makeListToFormidableListForListWithTwoElementsReturnsArrayWithTwoModels(): void
    {
        $targetGroup1 = MapperRegistry::get(TargetGroupMapper::class)->getLoadedTestingModel([]);
        $targetGroup2 = MapperRegistry::get(TargetGroupMapper::class)->getLoadedTestingModel([]);

        $list = new Collection();
        $list->add($targetGroup1);
        $list->add($targetGroup2);

        self::assertCount(
            2,
            EventEditor::makeListToFormidableList($list)
        );
    }

    /////////////////////////////////////////////
    // Tests concerning getPreselectedOrganizer
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getPreselectedOrganizerForNoAvailableOrganizerReturnsZero(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertEquals(
            0,
            $this->subject->getPreselectedOrganizer()
        );
    }

    /**
     * @test
     */
    public function getPreselectedOrganizerForOneAvailableOrganizerReturnsTheOrganizersUid(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['pid' => $this->recordsPageUid]
        );

        self::assertEquals(
            $organizerUid,
            $this->subject->getPreselectedOrganizer()
        );
    }

    /**
     * @test
     */
    public function getPreselectedOrganizerForTwoAvailableOrganizersReturnsZero(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->testingFramework->createRecord('tx_seminars_organizers', ['pid' => $this->recordsPageUid]);
        $this->testingFramework->createRecord('tx_seminars_organizers', ['pid' => $this->recordsPageUid]);

        self::assertEquals(
            0,
            $this->subject->getPreselectedOrganizer()
        );
    }
}
