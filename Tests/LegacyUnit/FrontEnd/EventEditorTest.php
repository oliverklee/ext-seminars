<?php

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_EventEditorTest extends TestCase
{
    /**
     * @var \Tx_Seminars_FrontEnd_EventEditor
     */
    protected $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var \Tx_Oelib_EmailCollector
     */
    protected $mailer = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * @var int
     */
    private $recordsPageUid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();
        \Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->recordsPageUid = $this->testingFramework->createSystemFolder();
        $this->configuration = new \Tx_Oelib_Configuration();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', $this->recordsPageUid);
        \Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars_pi1', $this->configuration);

        $this->subject = new \Tx_Seminars_FrontEnd_EventEditor(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'form.' => ['eventEditor.' => []],
            ],
            $GLOBALS['TSFE']->cObj
        );
        $this->subject->setTestMode();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
        \Tx_Oelib_ConfigurationProxy::purgeInstances();
    }

    /*
     * Utility functions.
     */

    /**
     * Creates a FE user, adds him/her as a VIP to the seminar with the UID in
     * $this->seminarUid and logs him/her in.
     *
     * @return void
     */
    private function createLogInAndAddFeUserAsVip()
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
    private function createLogInAndAddFeUserAsDefaultVip()
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
     *
     * @return void
     */
    private function createLogInAndAddFeUserAsOwner()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                ['owner_feuser' => $this->testingFramework->createAndLoginFrontEndUser()]
            )
        );
    }

    /**
     * Creates a front end user testing model which has a group with the given
     * publish settings.
     *
     * @param int $publishSetting
     *        the publish settings for the user, must be one of the following:
     *        \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY, \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW, or
     *        \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
     *
     * @return int user UID
     */
    private function createAndLoginUserWithPublishSetting($publishSetting)
    {
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup(
            ['tx_seminars_publish_events' => $publishSetting]
        );
        return $this->testingFramework->createAndLoginFrontEndUser($userGroupUid);
    }

    /**
     * Creates a front-end user which has a group with the publish setting
     * \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED and a reviewer.
     *
     * @return int user UID
     */
    private function createAndLoginUserWithReviewer()
    {
        $backendUserUid = $this->testingFramework->createBackEndUser(
            ['email' => 'foo@bar.com', 'realName' => 'Mr. Foo']
        );
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup(
            [
                'tx_seminars_publish_events' => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
                'tx_seminars_reviewer' => $backendUserUid,
            ]
        );

        return $this->testingFramework->createAndLoginFrontEndUser(
            $userGroupUid,
            ['name' => 'Mr. Bar', 'email' => 'mail@foo.com']
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
    private function createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
        array $frontEndUserGroupData = []
    ) {
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
     * @return \Tx_Seminars_FrontEnd_EventEditor event editor fixture with the given
     *         field as required field, will not be NULL.
     */
    private function getFixtureWithRequiredField($requiredField)
    {
        $result = new \Tx_Seminars_FrontEnd_EventEditor(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'form.' => ['eventEditor.' => []],
                'requiredFrontEndEditorFields' => $requiredField,
            ],
            $GLOBALS['TSFE']->cObj
        );
        $result->setTestMode();

        return $result;
    }

    /*
     * Tests for the utility functions.
     */

    public function testCreateLogInAndAddFeUserAsVipCreatesFeUser()
    {
        $this->createLogInAndAddFeUserAsVip();

        self::assertEquals(
            1,
            $this->testingFramework->countRecords('fe_users')
        );
    }

    public function testCreateLogInAndAddFeUserAsVipLogsInFeUser()
    {
        $this->createLogInAndAddFeUserAsVip();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    public function testCreateLogInAndAddFeUserAsVipAddsUserAsVip()
    {
        $this->createLogInAndAddFeUserAsVip();

        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars',
                'uid=' . $this->subject->getObjectUid() . ' AND vips=1'
            )
        );
    }

    public function testCreateLogInAndAddFeUserAsOwnerCreatesFeUser()
    {
        $this->createLogInAndAddFeUserAsOwner();

        self::assertEquals(
            1,
            $this->testingFramework->countRecords('fe_users')
        );
    }

    public function testCreateLogInAndAddFeUserAsOwnerLogsInFeUser()
    {
        $this->createLogInAndAddFeUserAsOwner();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    public function testCreateLogInAndAddFeUserAsOwnerAddsUserAsOwner()
    {
        $this->createLogInAndAddFeUserAsOwner();

        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars',
                'uid=' . $this->subject->getObjectUid() . ' AND owner_feuser>0'
            )
        );
    }

    public function testCreateLogInAndAddFeUserAsDefaultVipCreatesFeUser()
    {
        $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertEquals(
            1,
            $this->testingFramework->countRecords('fe_users')
        );
    }

    public function testCreateLogInAndAddFeUserAsDefaultVipLogsInFeUser()
    {
        $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    public function testCreateLogInAndAddFeUserAsDefaultVipAddsFeUserAsDefaultVip()
    {
        $userUid = $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'fe_users',
                'uid=' . $userUid . ' AND usergroup=' . $this->subject->getConfValueInteger('defaultEventVipsFeGroupID')
            )
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupCreatesFeUser()
    {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        self::assertEquals(
            1,
            $this->testingFramework->countRecords('fe_users')
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupLogsInFrontEndUser()
    {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupAddsFrontEndUserToEventEditorFrontEndGroup()
    {
        $userUid = $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'fe_users',
                'uid=' . $userUid . ' AND usergroup=' . $this->subject->getConfValueInteger('eventEditorFeGroupID')
            )
        );
    }

    ///////////////////////////////////////////////////////
    // Tests for getting the event-successfully-saved URL
    ///////////////////////////////////////////////////////

    public function testGetEventSuccessfullySavedUrlReturnsUrlStartingWithHttp()
    {
        $this->subject->setConfigurationValue(
            'eventSuccessfullySavedPID',
            $this->testingFramework->createFrontEndPage()
        );

        self::assertRegExp(
            '/^http:\\/\\/./',
            $this->subject->getEventSuccessfullySavedUrl()
        );
    }

    public function testGetEventSuccessfullySavedUrlReturnsConfiguredTargetPid()
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->subject->setConfigurationValue(
            'eventSuccessfullySavedPID',
            $frontEndPageUid
        );

        self::assertContains(
            '?id=' . $frontEndPageUid,
            $this->subject->getEventSuccessfullySavedUrl()
        );
    }

    public function testGetEventSuccessfullySavedUrlNotReturnsSeminarToEditAsLinkParameter()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord('tx_seminars_seminars')
        );
        $this->subject->setConfigurationValue(
            'eventSuccessfullySavedPID',
            $this->testingFramework->createFrontEndPage()
        );

        self::assertNotContains(
            'tx_seminars_pi1[seminar]=' . $this->subject->getObjectUid(),
            $this->subject->getEventSuccessfullySavedUrl()
        );
    }

    public function testGetEventSuccessfullySavedUrlReturnsCurrentPidAsTargetPidForProceedUpload()
    {
        $this->subject->setFakedFormValue('proceed_file_upload', 1);

        self::assertContains(
            '?id=' . $GLOBALS['TSFE']->id,
            $this->subject->getEventSuccessfullySavedUrl()
        );
    }

    public function testGetEventSuccessfullySavedUrlReturnsSeminarToEditAsLinkParameterForProceedUpload()
    {
        $this->subject->setFakedFormValue('proceed_file_upload', 1);

        self::assertContains(
            'tx_seminars_pi1%5Bseminar%5D=' . $this->subject->getObjectUid(),
            $this->subject->getEventSuccessfullySavedUrl()
        );
    }

    ////////////////////////////////////////
    // Tests concerning hasAccessMessage()
    ////////////////////////////////////////

    public function testHasAccessMessageWithNoLoggedInFeUserReturnsNotLoggedInMessage()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );

        self::assertContains(
            $this->subject->translate('message_notLoggedIn'),
            $this->subject->hasAccessMessage()
        );
    }

    public function testHasAccessMessageWithLoggedInFeUserWhoIsNeitherVipNorOwnerReturnsNoAccessMessage()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertContains(
            $this->subject->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    public function testHasAccessMessageWithLoggedInFeUserAsOwnerReturnsEmptyResult()
    {
        $this->createLogInAndAddFeUserAsOwner();

        self::assertEquals(
            '',
            $this->subject->hasAccessMessage()
        );
    }

    public function testHasAccessMessageWithLoggedInFeUserAsVipAndVipsMayNotEditTheirEventsReturnsNonEmptyResult()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );
        $this->subject->setConfigurationValue('mayManagersEditTheirEvents', 0);
        $this->createLogInAndAddFeUserAsVip();

        self::assertContains(
            $this->subject->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    public function testHasAccessMessageWithLoggedInFeUserAsVipAndVipsMayEditTheirEventsReturnsEmptyResult()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );
        $this->subject->setConfigurationValue('mayManagersEditTheirEvents', 1);
        $this->createLogInAndAddFeUserAsVip();

        self::assertEquals(
            '',
            $this->subject->hasAccessMessage()
        );
    }

    public function testHasAccessWithLoggedInFeUserAsDefaultVipAndVipsMayNotEditTheirEventsReturnsNonEmptyResult()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );
        $this->subject->setConfigurationValue('mayManagersEditTheirEvents', 0);
        $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertContains(
            $this->subject->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    public function testHasAccessWithLoggedInFeUserAsDefaultVipAndVipsMayEditTheirEventsReturnsEmptyResult()
    {
        $this->subject->setObjectUid(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars'
            )
        );
        $this->subject->setConfigurationValue('mayManagersEditTheirEvents', 1);
        $this->createLogInAndAddFeUserAsDefaultVip();

        self::assertEquals(
            '',
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInUserInUnauthorizedUserGroupReturnsNonEmptyResult()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertContains(
            $this->subject->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInUserInAuthorizedUserGroupAndNoUidSetReturnsEmptyResult()
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
    public function hasAccessForLoggedInNonOwnerInAuthorizedUserGroupReturnsNoAccessMessage()
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

        self::assertContains(
            $this->subject->translate('message_noAccessToEventEditor'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessForLoggedInOwnerInAuthorizedUserGroupReturnsEmptyResult()
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
    public function hasAccessForLoggedInUserAndInvalidSeminarUidReturnsWrongSeminarMessage()
    {
        $groupUid = $this->testingFramework->createFrontEndUserGroup(['title' => 'test']);
        $this->subject->setConfigurationValue('eventEditorFeGroupID', $groupUid);
        $this->testingFramework->createAndLoginFrontEndUser($groupUid);

        $this->subject->setObjectUid($this->testingFramework->getAutoIncrement('tx_seminars_seminars'));

        self::assertContains(
            $this->subject->translate('message_wrongSeminarNumber'),
            $this->subject->hasAccessMessage()
        );
    }

    /**
     * @test
     */
    public function hasAccessMessageForDeletedSeminarUidAndUserLoggedInReturnsWrongSeminarMessage()
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

        self::assertContains(
            $this->subject->translate('message_wrongSeminarNumber'),
            $this->subject->hasAccessMessage()
        );
    }

    public function testHasAccessMessageForHiddenSeminarUidAndUserLoggedInReturnsEmptyString()
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

    public function testPopulateListCategoriesDoesNotCrash()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->populateListCategories();
    }

    /**
     * @test
     */
    public function populateListCategoriesShowsCategory()
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
    public function populateListCategoriesForNoSetStoragePageReturnsRecordWithAnyPageId()
    {
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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
    public function populateListEventTypesShowsEventType()
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
    public function populateListEventTypesReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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
    public function populateListLodgingsShowsLodging()
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
    public function populateListLodgingsReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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
    public function populateListFoodsShowsFood()
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
    public function populateListFoodsReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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
    public function populateListPaymentMethodsShowsPaymentMethod()
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
    public function populateListPaymentMethodsReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            ['caption' => '', 'value' => $paymentMethodUid],
            $this->subject->populateListPaymentMethods()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning populateListOrganizers().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function populateListOrganizersShowsOrganizerFromDatabase()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => '', 'value' => $organizerUid],
            $this->subject->populateListOrganizers()
        );
    }

    /**
     * @test
     */
    public function populateListOrganizersReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['pid' => $this->recordsPageUid + 1]
        );

        self::assertContains(
            ['caption' => '', 'value' => $organizerUid],
            $this->subject->populateListOrganizers()
        );
    }

    /**
     * @test
     */
    public function populateListOrganizersShowsDefaultOrganizerFromUserGroup()
    {
        $organizerUid = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class)
            ->getLoadedTestingModel([])->getUid();
        $frontEndUserGroupUid = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_default_organizer' => $organizerUid]
            )->getUid();

        $this->testingFramework
            ->createAndLoginFrontEndUser($frontEndUserGroupUid);

        self::assertContains(
            ['caption' => '', 'value' => $organizerUid],
            $this->subject->populateListOrganizers()
        );
    }

    /**
     * @test
     */
    public function populateListOrganizersForDefaultOrganizerInUserGroupNotIncludesOtherOrganizer()
    {
        $organizerMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Organizer::class
        );
        $organizerUidFromDatabase = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['pid' => $this->recordsPageUid]
        );
        // makes sure the mapper knows that UID
        $organizerMapper->find($organizerUidFromDatabase);

        $organizerUid = $organizerMapper->getLoadedTestingModel([])->getUid();
        $frontEndUserGroupUid = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_default_organizer' => $organizerUid]
            )->getUid();
        $this->testingFramework
            ->createAndLoginFrontEndUser($frontEndUserGroupUid);

        self::assertNotContains(
            ['caption' => '', 'value' => $organizerUidFromDatabase],
            $this->subject->populateListOrganizers()
        );
    }

    ///////////////////////////////////////////
    // Tests concerning populateListPlaces().
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function populateListPlacesShowsPlaceWithoutOwner()
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
    public function populateListPlacesShowsPlaceWithOwnerIsLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['owner' => $frontEndUserUid, 'pid' => $this->recordsPageUid]
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
    public function populateListPlacesHidesPlaceWithOwnerIsNotLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['owner' => $frontEndUserUid + 1, 'pid' => $this->recordsPageUid]
        );

        self::assertNotContains(
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
    public function populateListPlacesReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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
    public function populateListCheckboxesShowsCheckboxWithoutOwner()
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
    public function populateListCheckboxesShowsCheckboxWithOwnerIsLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $checkboxUid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes',
            ['owner' => $frontEndUserUid, 'pid' => $this->recordsPageUid]
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
    public function populateListCheckboxesHidesCheckboxWithOwnerIsNotLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $checkboxUid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes',
            ['owner' => $frontEndUserUid + 1, 'pid' => $this->recordsPageUid]
        );

        self::assertNotContains(
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
    public function populateListCheckboxesReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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
    public function populateListTargetGroupsShowsTargetGroupWithoutOwner()
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
    public function populateListTargetGroupsShowsTargetGroupWithOwnerIsLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['owner' => $frontEndUserUid, 'pid' => $this->recordsPageUid]
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
    public function populateListTargetGroupsHidesTargetGroupWithOwnerIsNotLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['owner' => $frontEndUserUid + 1, 'pid' => $this->recordsPageUid]
        );

        self::assertNotContains(
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
    public function populateListTargetGroupsReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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
    public function populateListSpeakersShowsSpeakerWithoutOwner()
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
    public function populateListSpeakersShowsSpeakerWithOwnerIsLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['owner' => $frontEndUserUid, 'pid' => $this->recordsPageUid]
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
    public function populateListSpeakersHidesSpeakerWithOwnerIsNotLoggedInFrontEndUser()
    {
        $frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['owner' => $frontEndUserUid + 1, 'pid' => $this->recordsPageUid]
        );

        self::assertNotContains(
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
    public function populateListSpeakersReturnsRecordWithAnyPageId()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
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

    ////////////////////////////////////////
    // Tests concerning modifyDataToInsert
    ////////////////////////////////////////

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingPublishImmediatelyDoesNotHideCreatedEvent()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['hidden'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingPublishImmediatelyDoesNotHideEditedEvent()
    {
        $event = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Event::class
        )->getLoadedTestingModel([]);
        $this->subject->setObjectUid($event->getUid());
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['hidden'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideNewHidesCreatedEvent()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            1,
            $modifiedFormData['hidden']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideEditedHidesCreatedEvent()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            1,
            $modifiedFormData['hidden']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideEditedHidesEditedEvent()
    {
        $event = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Event::class
        )->getLoadedTestingModel([]);
        $this->subject->setObjectUid($event->getUid());
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            1,
            $modifiedFormData['hidden']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideNewDoesNotHideEditedEvent()
    {
        $event = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Event::class
        )->getLoadedTestingModel([]);
        $this->subject->setObjectUid($event->getUid());
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['hidden'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventHiddenOnEditingAddsPublicationHashToEvent()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertTrue(
            isset($modifiedFormData['publication_hash'])
            && !empty($modifiedFormData['publication_hash'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventHiddenOnCreationAddsPublicationHashToEvent()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertTrue(
            isset($modifiedFormData['publication_hash'])
            && !empty($modifiedFormData['publication_hash'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventNotHiddenOnEditingDoesNotAddPublicationHashToEvent()
    {
        $event = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Event::class
        )->getLoadedTestingModel([]);
        $this->subject->setObjectUid($event->getUid());
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['publication_hash'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventNotHiddenOnCreationDoesNotAddPublicationHashToEvent()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['publication_hash'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForHiddenEventDoesNotAddPublicationHashToEvent()
    {
        $event = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Event::class
        )->getLoadedTestingModel(
            ['hidden' => 1]
        );
        $this->subject->setObjectUid($event->getUid());
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
        );
        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['publication_hash'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertAddsTimestampToFormData()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );
        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertTrue(
            isset($modifiedFormData['tstamp'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertSetsTimestampToCurrentExecutionTime()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );
        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            $GLOBALS['SIM_EXEC_TIME'],
            $modifiedFormData['tstamp']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertAddsCreationDateToFormData()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );
        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertTrue(
            isset($modifiedFormData['crdate'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertsetsCreationDateToCurrentExecutionTime()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );
        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            $GLOBALS['SIM_EXEC_TIME'],
            $modifiedFormData['crdate']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertsetsOwnerFeUserToCurrentlyLoggedInUser()
    {
        $userUid = $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );
        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertSame(
            $userUid,
            $modifiedFormData['owner_feuser']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForNoUserGroupSpecificEventPidSetsPidFromTsSetupAsEventPid()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );
        $this->subject->setConfigurationValue('createEventsPID', 42);

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            42,
            $modifiedFormData['pid']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForUserGroupSpecificEventPidSetsPidFromUserGroupAsEventPid()
    {
        $this->subject->setConfigurationValue('createEventsPID', 42);

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 21]
        );

        $user = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUser::class
        )->getLoadedTestingModel(
            ['usergroup' => $userGroup->getUid()]
        );
        $this->testingFramework->loginFrontEndUser($user->getUid());

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            21,
            $modifiedFormData['pid']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForNewEventAndUserWithoutDefaultCategoriesDoesNotAddAnyCategories()
    {
        $this->createAndLoginUserWithPublishSetting(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['categories'])
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForNewEventAndUserWithOneDefaultCategoryAddsThisCategory()
    {
        $categories = new \Tx_Oelib_List();
        $category = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $categories->add($category);

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            [
                'tx_seminars_default_categories' => $categories,
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            ]
        );

        $this->testingFramework->createAndLoginFrontEndUser(
            $userGroup->getUid()
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            $category->getUid(),
            $modifiedFormData['categories']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForNewEventAndUserWithTwoDefaultCategoriesAddsTheseCategories()
    {
        $categoryMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class);
        $category1 = $categoryMapper->getNewGhost();
        $category2 = $categoryMapper->getNewGhost();

        $categories = new \Tx_Oelib_List();
        $categories->add($category1);
        $categories->add($category2);

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            [
                'tx_seminars_default_categories' => $categories,
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            ]
        );

        $this->testingFramework->createAndLoginFrontEndUser(
            $userGroup->getUid()
        );

        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertEquals(
            $category1->getUid() . ',' . $category2->getUid(),
            $modifiedFormData['categories']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEditedEventAndUserWithOneDefaultCategoryDoesNotAddTheUsersCategory()
    {
        $categories = new \Tx_Oelib_List();
        $category = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
            ->getNewGhost();
        $categories->add($category);

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            [
                'tx_seminars_default_categories' => $categories,
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            ]
        );

        $this->testingFramework->createAndLoginFrontEndUser(
            $userGroup->getUid()
        );

        $this->subject->setObjectUid(
            \Tx_Oelib_MapperRegistry::get(
                \Tx_Seminars_Mapper_Event::class
            )->getLoadedTestingModel(
                []
            )->getUid()
        );
        $modifiedFormData = $this->subject->modifyDataToInsert([]);

        self::assertFalse(
            isset($modifiedFormData['categories'])
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests regarding isFrontEndEditingOfRelatedRecordsAllowed().
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function isFrontEndEditingOfRelatedRecordsAllowedWithoutPermissionAndWithoutPidReturnsFalse()
    {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        $this->subject->setConfigurationValue(
            'allowFrontEndEditingOfTest',
            false
        );

        self::assertFalse(
            $this->subject->isFrontEndEditingOfRelatedRecordsAllowed(
                ['relatedRecordType' => 'Test']
            )
        );
    }

    /**
     * @test
     */
    public function isFrontEndEditingOfRelatedRecordsAllowedWithPermissionAndWithoutPidReturnsFalse()
    {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        $this->subject->setConfigurationValue(
            'allowFrontEndEditingOfTest',
            true
        );

        self::assertFalse(
            $this->subject->isFrontEndEditingOfRelatedRecordsAllowed(
                ['relatedRecordType' => 'Test']
            )
        );
    }

    /**
     * @test
     */
    public function isFrontEndEditingOfRelatedRecordsAllowedWithoutPermissionAndWithPidReturnsFalse()
    {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $this->subject->setConfigurationValue(
            'allowFrontEndEditingOfTest',
            false
        );

        self::assertFalse(
            $this->subject->isFrontEndEditingOfRelatedRecordsAllowed(
                ['relatedRecordType' => 'Test']
            )
        );
    }

    /**
     * @test
     */
    public function isFrontEndEditingOfRelatedRecordsAllowedWithPermissionAndWithPidReturnsTrue()
    {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $this->subject->setConfigurationValue(
            'allowFrontEndEditingOfTest',
            true
        );

        self::assertTrue(
            $this->subject->isFrontEndEditingOfRelatedRecordsAllowed(
                ['relatedRecordType' => 'Test']
            )
        );
    }

    /**
     * @test
     */
    public function isFrontEndEditingOfRelatedRecordsAllowedWithPermissionAndWithPidSetInSetupButNotUserGroupReturnsTrue(
    ) {
        $this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

        $this->subject->setConfigurationValue(
            'allowFrontEndEditingOfTest',
            true
        );
        $this->subject->setConfigurationValue(
            'createAuxiliaryRecordsPID',
            42
        );

        self::assertTrue(
            $this->subject->isFrontEndEditingOfRelatedRecordsAllowed(
                ['relatedRecordType' => 'Test']
            )
        );
    }

    /////////////////////////////////////////
    // Tests concerning validateStringField
    /////////////////////////////////////////

    /**
     * @test
     */
    public function validateStringFieldForNonRequiredFieldAndEmptyStringReturnsTrue()
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
    public function validateStringFieldForRequiredFieldAndEmptyStringReturnsFalse()
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
    public function validateStringFieldForRequiredFieldAndNonEmptyStringReturnsTrue()
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
    public function validateIntegerFieldForNonRequiredFieldAndValueZeroReturnsTrue()
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
    public function validateIntegerFieldForRequiredFieldAndValueZeroReturnsFalse()
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
    public function validateIntegerFieldForRequiredFieldAndValueNonZeroReturnsTrue()
    {
        $subject = $this->getFixtureWithRequiredField('attendees_max');

        self::assertTrue(
            $subject->validateInteger(
                ['elementName' => 'attendees_max', 'value' => 15]
            )
        );
    }

    ////////////////////////////////////////
    // Tests concerning validateCheckboxes
    ////////////////////////////////////////

    /**
     * @test
     */
    public function validateCheckboxesForNonRequiredFieldAndEmptyValueReturnsTrue()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        self::assertTrue(
            $this->subject->validateCheckboxes(
                ['elementName' => 'categories', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validateCheckboxesForRequiredFieldAndValueNotArrayReturnsFalse()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $subject = $this->getFixtureWithRequiredField('categories');

        self::assertFalse(
            $subject->validateCheckboxes(
                ['elementName' => 'categories', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validateCheckboxesForRequiredFieldAndValueEmptyArrayReturnsFalse()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $subject = $this->getFixtureWithRequiredField('categories');

        self::assertFalse(
            $subject->validateCheckboxes(
                ['elementName' => 'categories', 'value' => []]
            )
        );
    }

    /**
     * @test
     */
    public function validateCheckboxesForRequiredFieldAndValueNonEmptyArrayReturnsTrue()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $subject = $this->getFixtureWithRequiredField('categories');

        self::assertTrue(
            $subject->validateCheckboxes(
                ['elementName' => 'categories', 'value' => [42]]
            )
        );
    }

    /**
     * @test
     */
    public function validateCheckboxesForUserWithDefaultCategoriesAndCategoriesRequiredAndEmptyReturnsTrue()
    {
        $categories = new \Tx_Oelib_List();
        $categories->add(
            \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
                ->getNewGhost()
        );

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $this->testingFramework->createAndLoginFrontEndUser(
            $userGroup->getUid()
        );

        $subject = $this->getFixtureWithRequiredField('categories');

        self::assertTrue(
            $subject->validateCheckboxes(
                ['elementName' => 'categories', 'value' => '']
            )
        );
    }

    /**
     * @test
     */
    public function validateCheckboxesForUserWithoutDefaultCategoriesAndCategoriesRequiredAndEmptyReturnsFalse()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $subject = $this->getFixtureWithRequiredField('categories');

        self::assertFalse(
            $subject->validateCheckboxes(
                ['elementName' => 'categories', 'value' => '']
            )
        );
    }

    //////////////////////////////////
    // Tests concerning validateDate
    //////////////////////////////////

    /**
     * @test
     */
    public function validateDateForNonRequiredFieldAndEmptyStringReturnsTrue()
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
    public function validateDateForRequiredFieldAndEmptyStringReturnsFalse()
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
    public function validateDateForRequiredFieldAndValidDateReturnsTrue()
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
    public function validateDateForRequiredFieldAndNonValidDateReturnsFalse()
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
    public function validatePriceForNonRequiredFieldAndEmptyStringReturnsTrue()
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
    public function validatePriceForRequiredFieldAndEmptyStringReturnsFalse()
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
    public function validatePriceForRequiredFieldAndValidPriceReturnsTrue()
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
    public function validatePriceForRequiredFieldAndInvalidPriceReturnsFalse()
    {
        $subject = $this->getFixtureWithRequiredField('price_regular');

        self::assertFalse(
            $subject->validatePrice(
                ['elementName' => 'price_regular', 'value' => 'foo']
            )
        );
    }

    ///////////////////////////////////////////
    // Tests concerning the publishing emails
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function eventEditorForNonHiddenEventDoesNotSendMail()
    {
        $this->subject->sendEMailToReviewer();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function eventEditorForEventHiddenBeforeEditingDoesNotSendMail()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1]
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $this->subject->modifyDataToInsert([]);

        $this->subject->sendEMailToReviewer();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function eventEditorForEventHiddenByFormDoesSendMail()
    {
        $seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertGreaterThan(
            0,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerSendsMailToReviewerMailAddress()
    {
        $seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertArrayHasKey(
            'foo@bar.com',
            $this->mailer->getFirstSentEmail()->getTo()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerSetsPublishEventSubjectInMail()
    {
        $seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertSame(
            $this->subject->translate('publish_event_subject'),
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerSendsTheTitleOfTheEvent()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'foo Event']
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertContains(
            'foo Event',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerForEventWithDateSendsTheDateOfTheEvent()
    {
        $this->subject->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertContains(
            strftime(
                $this->subject->getConfValueString('dateFormatYMD'),
                $GLOBALS['SIM_EXEC_TIME']
            ),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerForEventWithoutDateHidesDateMarker()
    {
        $this->subject->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertNotContains(
            '###PUBLISH_EVENT_DATE###',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerForEventWithoutDateDoesNotSendDate()
    {
        $this->subject->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
                'title' => 'foo event',
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertNotContains(
            'foo event,',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerSendsMailWithoutAnyUnreplacedMarkers()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertNotContains(
            '###',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerForEventWithDescriptionShowsDescriptionInMail()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['description' => 'Foo Description']
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertContains(
            'Foo Description',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerSendsPublicationLinkInMail()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertContains(
            'tx_seminars_publication%5Bhash%5D=' . $formData['publication_hash'],
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerUsesFrontEndUserNameAsFromNameForMail()
    {
        $seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertContains(
            'Mr. Bar',
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendEMailToReviewerUsesFrontEndUserMailAddressAsFromAddressForMail()
    {
        $seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->createAndLoginUserWithReviewer();

        $this->subject->setObjectUid($seminarUid);
        $formData = $this->subject->modifyDataToInsert([]);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            [
                'hidden' => 1,
                'publication_hash' => $formData['publication_hash'],
            ]
        );

        $this->subject->sendEMailToReviewer();

        self::assertArrayHasKey(
            'mail@foo.com',
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /*
     * Tests concerning the notification e-mails
     */

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerWithReviewerAndFeatureEnabledSendsEmail()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerWithoutReviewerAndFeatureEnabledNotSendsEmail()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithPublishSetting(\Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY);

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerWithReviewerAndFeatureDisabledNotSendsEmail()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', false);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerSendsEmailToReviewer()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertArrayHasKey(
            'foo@bar.com',
            $this->mailer->getFirstSentEmail()->getTo()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerUsesFrontEndUserAsFromName()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertContains(
            'Mr. Bar',
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerUsesFrontEndUserAsFromAddress()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertArrayHasKey(
            'mail@foo.com',
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerUsesEventSavedSubject()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertSame(
            $this->subject->translate('save_event_subject'),
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerHasIntroductoryText()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertContains(
            $this->subject->translate('label_save_event_text'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerHasOverviewText()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertContains(
            $this->subject->translate('label_save_event_overview'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerHasNoUnreplacedMarkers()
    {
        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertNotContains(
            '###',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerHasEventTitleInBody()
    {
        $title = 'Some nice event';
        $this->subject->setSavedFormValue('title', $title);

        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertContains(
            $title,
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerHasEventDescriptionInBody()
    {
        $description = 'Everybody needs to attend!';
        $this->subject->setSavedFormValue('description', $description);

        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertContains(
            $description,
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationEmailToReviewerHasEventDateInBody()
    {
        $beginDate = mktime(10, 0, 0, 4, 2, 1975);
        $this->subject->setSavedFormValue('begin_date', $beginDate);

        $this->configuration->setAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor', true);
        $this->subject->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
        $this->createAndLoginUserWithReviewer();

        $this->subject->sendAdditionalNotificationEmailToReviewer();

        self::assertNotNull(
            $this->mailer->getFirstSentEmail()
        );
        self::assertContains(
            '02.04.1975',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    ///////////////////////////////////////////
    // Tests concerning populateListCountries
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function populateListCountriesContainsGermany()
    {
        self::assertContains(
            ['caption' => 'Deutschland', 'value' => 54],
            \Tx_Seminars_FrontEnd_EventEditor::populateListCountries()
        );
    }

    /**
     * @test
     */
    public function populateListCountriesSortsResultsByLocalCountryName()
    {
        $countries = \Tx_Seminars_FrontEnd_EventEditor::populateListCountries();
        $positionGermany = array_search(
            ['caption' => 'Deutschland', 'value' => 54],
            $countries
        );
        $positionGambia = array_search(
            ['caption' => 'Gambia', 'value' => 81],
            $countries
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
    public function populateListSkillsHasSkillFromDatabase()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_skills',
            ['title' => 'Juggling', 'pid' => $this->recordsPageUid]
        );

        self::assertContains(
            ['caption' => 'Juggling', 'value' => $uid],
            \Tx_Seminars_FrontEnd_EventEditor::populateListSkills()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning makeListToFormidableList
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function makeListToFormidableListForEmptyListGivenReturnsEmptyArray()
    {
        self::assertEquals(
            [],
            \Tx_Seminars_FrontEnd_EventEditor::makeListToFormidableList(new \Tx_Oelib_List())
        );
    }

    /**
     * @test
     */
    public function makeListToFormidableListForListWithOneElementReturnsModelDataInArray()
    {
        $targetGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_TargetGroup::class
        )->getLoadedTestingModel(
            ['title' => 'foo']
        );

        $list = new \Tx_Oelib_List();
        $list->add($targetGroup);

        self::assertContains(
            ['caption' => 'foo', 'value' => $targetGroup->getUid()],
            \Tx_Seminars_FrontEnd_EventEditor::makeListToFormidableList($list)
        );
    }

    /**
     * @test
     */
    public function makeListToFormidableListForListWithTwoElementsReturnsArrayWithTwoModels()
    {
        $targetGroup1 = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_TargetGroup::class
        )->getLoadedTestingModel([]);
        $targetGroup2 = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_TargetGroup::class
        )->getLoadedTestingModel([]);

        $list = new \Tx_Oelib_List();
        $list->add($targetGroup1);
        $list->add($targetGroup2);

        self::assertCount(
            2,
            \Tx_Seminars_FrontEnd_EventEditor::makeListToFormidableList($list)
        );
    }

    /////////////////////////////////////////////
    // Tests concerning getPreselectedOrganizer
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getPreselectedOrganizerForNoAvailableOrganizerReturnsZero()
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
    public function getPreselectedOrganizerForOneAvailableOrganizerReturnsTheOrganizersUid()
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
    public function getPreselectedOrganizerForTwoAvailableOrganizersReturnsZero()
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
