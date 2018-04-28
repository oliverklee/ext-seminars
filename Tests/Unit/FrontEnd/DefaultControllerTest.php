<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_DefaultControllerTest extends Tx_Phpunit_TestCase
{
    /**
     * @var string
     */
    const BLANK_GIF = 'R0lGODlhAQABAJH/AP///wAAAMDAwAAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==';

    /**
     * @var Tx_Seminars_FrontEnd_DefaultController
     */
    private $fixture;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Oelib_Translator
     */
    private $translator = null;

    /**
     * backup of the BE user's language
     *
     * @var string
     */
    private $languageBackup = '';

    /**
     * @var int the UID of a seminar to which the fixture relates
     */
    private $seminarUid;

    /**
     * @var int PID of a dummy system folder
     */
    private $systemFolderPid = 0;

    /**
     * @var int the number of target groups for the current event record
     */
    private $numberOfTargetGroups = 0;

    /**
     * @var int the number of categories for the current event record
     */
    private $numberOfCategories = 0;

    /**
     * @var int the number of organizers for the current event record
     */
    private $numberOfOrganizers = 0;

    /**
     * backed-up extension configuration of the TYPO3 configuration variables
     *
     * @var array
     */
    private $extConfBackup = [];

    /**
     * backed-up T3_VAR configuration
     *
     * @var array
     */
    private $t3VarBackup = [];

    /**
     * @var Tx_Seminars_Service_SingleViewLinkBuilder
     */
    private $linkBuilder = null;

    /**
     * @var Tx_Oelib_HeaderCollector
     */
    private $headerCollector = null;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);

        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = [];

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();
        Tx_Oelib_HeaderProxyFactory::getInstance()->enableTestMode();
        $this->headerCollector = Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy();

        $configuration = new Tx_Oelib_Configuration();
        $configuration->setAsString('currency', 'EUR');
        Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 7006000) {
            $GLOBALS['TSFE']->config['config']['uniqueLinkVars'] = 1;
        }

        $this->languageBackup = $GLOBALS['LANG']->lang;
        $GLOBALS['LANG']->lang = 'default';
        $GLOBALS['LANG']->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        Tx_Oelib_TranslatorRegistry::getInstance()->setLanguageKey('default');
        $this->translator = Tx_Oelib_TranslatorRegistry::get('seminars');

        $this->systemFolderPid = $this->testingFramework->createSystemFolder();
        $this->seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Test & event',
                'subtitle' => 'Something for you & me',
                'accreditation_number' => '1 & 1',
                'room' => 'Rooms 2 & 3',
            ]
        );

        $this->fixture = new Tx_Seminars_FrontEnd_DefaultController();
        $this->fixture->init(
            [
                'isStaticTemplateLoaded' => 1,
                'enableRegistration' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'what_to_display' => 'seminar_list',
                'pidList' => $this->systemFolderPid,
                'pages' => $this->systemFolderPid,
                'recursive' => 1,
                'listView.' => [
                    'orderBy' => 'data',
                    'descFlag' => 0,
                    'results_at_a_time' => 999,
                    'maxPages' => 5,
                ],
                'eventFieldsOnRegistrationPage' => 'title,price_regular,price_special,vacancies,accreditation_number',
                'linkToSingleView' => 'always',
            ]
        );
        $this->fixture->getTemplateCode();
        $this->fixture->setLabels();
        $this->fixture->createHelperObjects();
        Tx_Oelib_TemplateHelper::setCachedConfigurationValue(
            'dateFormatYMD',
            '%d.%m.%Y'
        );
        Tx_Oelib_TemplateHelper::setCachedConfigurationValue(
            'timeFormat',
            '%H:%M'
        );

        $this->linkBuilder = $this->getMock(
            Tx_Seminars_Service_SingleViewLinkBuilder::class,
            ['createRelativeUrlForEvent']
        );
        $this->linkBuilder->expects(self::any())
            ->method('createRelativeUrlForEvent')
            ->will(self::returnValue(
                'index.php?id=42&tx_seminars_pi1%5BshowUid%5D=1337'
            ));
        $this->fixture->injectLinkBuilder($this->linkBuilder);

        /** @var $content ContentObjectRenderer|PHPUnit_Framework_MockObject_MockObject */
        $content = $this->getMock(ContentObjectRenderer::class, ['IMAGE', 'cObjGetSingle']);
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7006000) {
            $content->expects(self::any())->method('cObjGetSingle')->will(self::returnValue('<img src="foo.jpg" alt="bar"/>'));
        } else {
            $content->expects(self::any())->method('IMAGE')->will(self::returnValue('<img src="foo.jpg" alt="bar"/>'));
        }
        $this->fixture->cObj = $content;
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        Tx_Seminars_Service_RegistrationManager::purgeInstance();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
        $GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
        $GLOBALS['LANG']->lang = $this->languageBackup;
    }

    ///////////////////////
    // Utility functions.
    ///////////////////////

    /**
     * Inserts a target group record into the database and creates a relation to
     * it from the event with the UID store in $this->seminarUid.
     *
     * @param array $targetGroupData data of the target group to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addTargetGroupRelation(array $targetGroupData = [])
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            $targetGroupData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_target_groups_mm',
            $this->seminarUid,
            $uid
        );

        $this->numberOfTargetGroups++;
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['target_groups' => $this->numberOfTargetGroups]
        );

        return $uid;
    }

    /**
     * Creates a FE user, registers him/her to the seminar with the UID in
     * $this->seminarUid and logs him/her in.
     *
     * @return int the UID of the created registration record, will be > 0
     */
    private function createLogInAndRegisterFeUser()
    {
        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        return $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $feUserUid,
            ]
        );
    }

    /**
     * Creates a FE user, adds him/her as a VIP to the seminar with the UID in
     * $this->seminarUid and logs him/her in.
     *
     * @return void
     */
    private function createLogInAndAddFeUserAsVip()
    {
        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_feusers_mm',
            $this->seminarUid,
            $feUserUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['vips' => 1]
        );
    }

    /**
     * Inserts a category record into the database and creates a relation to
     * it from the event with the UID stored in $this->seminarUid.
     *
     * @param array $categoryData data of the category to add, may be empty
     *
     * @return int the UID of the created record, will be > 0
     */
    private function addCategoryRelation(array $categoryData = [])
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            $categoryData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $this->seminarUid,
            $uid
        );

        $this->numberOfCategories++;
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['categories' => $this->numberOfCategories]
        );

        return $uid;
    }

    /**
     * Inserts an organizer record into the database and creates a relation to
     * to the seminar with the UID stored in $this->seminarUid.
     *
     * @param array $organizerData data of the organizer to add, may be empty
     *
     * @return void
     */
    private function addOrganizerRelation(array $organizerData = [])
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            $organizerData
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->seminarUid,
            $organizerUid
        );

        $this->numberOfOrganizers++;
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['organizers' => $this->numberOfOrganizers]
        );
    }

    /**
     * Creates a subclass of the fixture class that makes protected methods
     * public where necessary.
     *
     * @return string the class name of the subclass, will not be empty
     */
    private function createAccessibleProxyClass()
    {
        $testingClassName = 'tx_seminars_FrontEnd_TestingDefaultController';

        if (!class_exists($testingClassName, false)) {
            eval(
                'class ' . $testingClassName . ' extends Tx_Seminars_FrontEnd_DefaultController {' .
                'public function setSeminar(Tx_Seminars_OldModel_Event $event = NULL) {' .
                '  parent::setSeminar($event);' .
                '}' .
                'public function createAllEditorLinks() {' .
                '  return parent::createAllEditorLinks();' .
                '}' .
                'public function mayCurrentUserEditCurrentEvent() {' .
                '  return parent::mayCurrentUserEditCurrentEvent();' .
                '}' .
                'public function processEventEditorActions() {' .
                '  parent::processEventEditorActions();' .
                '}' .
                'public function hideEvent(Tx_Seminars_Model_Event $event) {' .
                '  parent::hideEvent($event);' .
                '}' .
                'public function unhideEvent(Tx_Seminars_Model_Event $event) {' .
                '  parent::unhideEvent($event);' .
                '}' .
                'public function copyEvent(Tx_Seminars_Model_Event $event) {' .
                '  parent::copyEvent($event);' .
                '}' .
                '}'
            );
        }

        return $testingClassName;
    }

    /**
     * Creates a mock content object that can create links in the following
     * form:
     *
     * <a href="index.php?id=42&amp;...parameters">link title</a>
     *
     * The page ID isn't checked for existence. So any page ID can be used.
     *
     * @return ContentObjectRenderer|\PHPUnit_Framework_MockObject_MockObject a mock content object
     */
    private function createContentMock()
    {
        $mock = $this->getMock(ContentObjectRenderer::class, ['getTypoLink']);
        $mock->expects(self::any())->method('getTypoLink')
            ->will(self::returnCallback([$this, 'getTypoLink']));

        return $mock;
    }

    /**
     * Callback function for creating mock typolinks.
     *
     * @param string $label the link text
     * @param int $pageId the page ID to link to, must be >= 0
     * @param string[] $urlParameters
     *        URL parameters to set as key/value pairs, not URL-encoded yet
     *
     * @return string faked link tag, will not be empty
     */
    public function getTypoLink($label, $pageId, array $urlParameters = [])
    {
        $encodedParameters = '';
        foreach ($urlParameters as $key => $value) {
            $encodedParameters .= '&amp;' . $key . '=' . $value;
        }

        return '<a href="index.php?id=' . $pageId . $encodedParameters . '">' . $label . '</a>';
    }

    /////////////////////////////////////
    // Tests for the utility functions.
    /////////////////////////////////////

    public function testAddTargetGroupRelationReturnsUid()
    {
        self::assertTrue(
            $this->addTargetGroupRelation([]) > 0
        );
    }

    public function testAddTargetGroupRelationCreatesNewUids()
    {
        $this->addTargetGroupRelation([]);
        self::assertNotEquals(
            $this->addTargetGroupRelation([]),
            $this->addTargetGroupRelation([])
        );
    }

    public function testAddTargetGroupRelationIncreasesTheNumberOfTargetGroups()
    {
        self::assertEquals(
            0,
            $this->numberOfTargetGroups
        );

        $this->addTargetGroupRelation([]);
        self::assertEquals(
            1,
            $this->numberOfTargetGroups
        );

        $this->addTargetGroupRelation([]);
        self::assertEquals(
            2,
            $this->numberOfTargetGroups
        );
    }

    public function testAddTargetGroupRelationCreatesRelations()
    {
        self::assertEquals(
            0,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars_target_groups_mm',
                'uid_local=' . $this->seminarUid
            )

        );

        $this->addTargetGroupRelation([]);
        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars_target_groups_mm',
                'uid_local=' . $this->seminarUid
            )
        );

        $this->addTargetGroupRelation([]);
        self::assertEquals(
            2,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars_target_groups_mm',
                'uid_local=' . $this->seminarUid
            )
        );
    }

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
                'uid=' . $this->seminarUid . ' AND vips=1'
            )
        );
    }

    public function testAddCategoryRelationReturnsPositiveUid()
    {
        self::assertTrue(
            $this->addCategoryRelation([]) > 0
        );
    }

    public function testAddCategoryRelationCreatesNewUids()
    {
        self::assertNotEquals(
            $this->addCategoryRelation([]),
            $this->addCategoryRelation([])
        );
    }

    public function testAddCategoryRelationIncreasesTheNumberOfCategories()
    {
        self::assertEquals(
            0,
            $this->numberOfCategories
        );

        $this->addCategoryRelation([]);
        self::assertEquals(
            1,
            $this->numberOfCategories
        );

        $this->addCategoryRelation([]);
        self::assertEquals(
            2,
            $this->numberOfCategories
        );
    }

    public function testAddCategoryRelationCreatesRelations()
    {
        self::assertEquals(
            0,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars_categories_mm',
                'uid_local=' . $this->seminarUid
            )

        );

        $this->addCategoryRelation([]);
        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars_categories_mm',
                'uid_local=' . $this->seminarUid
            )
        );

        $this->addCategoryRelation([]);
        self::assertEquals(
            2,
            $this->testingFramework->countRecords(
                'tx_seminars_seminars_categories_mm',
                'uid_local=' . $this->seminarUid
            )
        );
    }

    /**
     * @test
     */
    public function createAccessibleProxyClassCreatesFixtureSubclass()
    {
        $className = $this->createAccessibleProxyClass();
        $instance = new $className();

        self::assertInstanceOf(Tx_Seminars_FrontEnd_DefaultController::class, $instance);
    }

    /**
     * @test
     */
    public function createContentMockCreatesContentObjectRenderer()
    {
        self::assertInstanceOf(ContentObjectRenderer::class, $this->createContentMock());
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockCreatesLinkToPageId()
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            '<a href="index.php?id=42',
            $contentMock->getTypoLink('link label', 42)
        );
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockUsesLinkTitle()
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            '>link label</a>',
            $contentMock->getTypoLink('link label', 42)
        );
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockNotHtmlspecialcharsLinkTitle()
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            '>foo & bar</a>',
            $contentMock->getTypoLink('foo & bar', []),
            42
        );
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockAddsParameters()
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            'tx_seminars_pi1%5Bseminar%5D=42',
            $contentMock->getTypoLink(
                'link label',
                1,
                ['tx_seminars_pi1%5Bseminar%5D' => 42]
            )
        );
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockCanAddTwoParameters()
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            'tx_seminars_pi1%5Bseminar%5D=42&amp;foo=bar',
            $contentMock->getTypoLink(
                'link label',
                1,
                [
                    'tx_seminars_pi1%5Bseminar%5D' => 42,
                    'foo' => 'bar',
                ]
            )
        );
    }

    ////////////////////////////////////////////
    // Test concerning the base functionality.
    ////////////////////////////////////////////

    public function testPi1MustBeInitialized()
    {
        self::assertNotNull(
            $this->fixture
        );
        self::assertTrue(
            $this->fixture->isInitialized()
        );
    }

    public function testGetSeminarReturnsSeminarIfSet()
    {
        $this->fixture->createSeminar($this->seminarUid);

        self::assertInstanceOf(
            Tx_Seminars_OldModel_Event::class,
            $this->fixture->getSeminar()
        );
    }

    public function testGetRegistrationReturnsRegistrationIfSet()
    {
        $this->fixture->createRegistration(
            $this->testingFramework->createRecord(
                'tx_seminars_attendances',
                ['seminar' => $this->seminarUid]
            )
        );

        self::assertInstanceOf(
            Tx_Seminars_OldModel_Registration::class,
            $this->fixture->getRegistration()
        );
    }

    public function testGetRegistrationManagerReturnsRegistrationManager()
    {
        self::assertInstanceOf(
            Tx_Seminars_Service_RegistrationManager::class,
            $this->fixture->getRegistrationManager()
        );
    }

    //////////////////////////////////////
    // Tests concerning the single view.
    //////////////////////////////////////

    /**
     * @test
     */
    public function singleViewFlavorWithUidCreatesSingleView()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            [
                'createListView', 'createSingleView', 'pi_initPIflexForm', 'getTemplateCode', 'setLabels',
                'setCSS', 'createHelperObjects', 'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createSingleView');
        $controller->expects(self::never())->method('createListView');

        $controller->piVars = ['showUid' => 42];

        $controller->main('', ['what_to_display' => 'single_view']);
    }

    /**
     * @test
     */
    public function singleViewFlavorWithUidFromShowSingleEventConfigurationCreatesSingleView()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            [
                'createListView', 'createSingleView', 'pi_initPIflexForm', 'getTemplateCode', 'setLabels',
                'setCSS', 'createHelperObjects', 'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createSingleView');
        $controller->expects(self::never())->method('createListView');

        $controller->piVars = [];

        $controller->main('', ['what_to_display' => 'single_view', 'showSingleEvent' => 42]);
    }

    /**
     * @test
     */
    public function singleViewFlavorWithoutUidCreatesSingleView()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            [
                'createListView', 'createSingleView', 'pi_initPIflexForm', 'getTemplateCode', 'setLabels',
                'setCSS', 'createHelperObjects', 'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createSingleView');
        $controller->expects(self::never())->method('createListView');

        $controller->piVars = [];

        $controller->main('', ['what_to_display' => 'single_view']);
    }

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedEventTitle()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedEventSubtitle()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'Something for you &amp; me',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedEventRoom()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'Rooms 2 &amp; 3',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedAccreditationNumber()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            '1 &amp; 1',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function otherDatesListInSingleViewContainsOtherDateWithDateLinkedToSingleView()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + Tx_Oelib_Time::SECONDS_PER_DAY,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 2 * Tx_Oelib_Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 3 * Tx_Oelib_Time::SECONDS_PER_DAY,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $dateUid1;

        self::assertContains(
            'tx_seminars_pi1%5BshowUid%5D=1337',
            $this->fixture->main('', [])
        );
    }

    public function testOtherDatesListInSingleViewDoesNotContainSingleEventRecordWithTopicSet()
    {
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue(
            'hideFields',
            'eventsnextday'
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + Tx_Oelib_Time::SECONDS_PER_DAY,
            ]
        );
        $singleEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'topic' => $topicUid,
                'title' => 'Test single 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 2 * Tx_Oelib_Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 3 * Tx_Oelib_Time::SECONDS_PER_DAY,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $dateUid;

        $result = $this->fixture->main('', []);

        self::assertNotContains(
            'tx_seminars_pi1%5BshowUid%5D=' . $singleEventUid,
            $result
        );
    }

    /**
     * @test
     */
    public function otherDatesListInSingleViewByDefaultShowsBookedOutEvents()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + Tx_Oelib_Time::SECONDS_PER_DAY,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 2 * Tx_Oelib_Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 3 * Tx_Oelib_Time::SECONDS_PER_DAY,
                'needs_registration' => 1,
                'attendees_max' => 5,
                'offline_attendees' => 5,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $dateUid1;

        self::assertContains(
            'tx_seminars_pi1%5BshowUid%5D=1337',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function otherDatesListInSingleViewForShowOnlyEventsWithVacanciesSetHidesBookedOutEvents()
    {
        $this->fixture->setConfigurationValue(
            'showOnlyEventsWithVacancies',
            true
        );
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + Tx_Oelib_Time::SECONDS_PER_DAY,
            ]
        );
        $dateUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 2 * Tx_Oelib_Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_WEEK + 3 * Tx_Oelib_Time::SECONDS_PER_DAY,
                'needs_registration' => 1,
                'attendees_max' => 5,
                'offline_attendees' => 5,
            ]
        );

        $this->fixture->piVars['showUid'] = $dateUid1;

        self::assertNotContains(
            'tx_seminars_pi1%5BshowUid%5D=' . $dateUid2,
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSpeakerWithoutHomepageContainsHtmlspecialcharedSpeakerName()
    {
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue('showSpeakerDetails', true);
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'foo & bar',
                'organization' => 'baz',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $this->seminarUid,
            $speakerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['speakers' => '1']
        );

        self::assertContains(
            'foo &amp; bar',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForContainsHtmlspecialcharedSpeakerOrganization()
    {
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue('showSpeakerDetails', true);
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'John Doe',
                'organization' => 'foo & bar',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $this->seminarUid,
            $speakerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['speakers' => '1']
        );

        self::assertContains(
            'foo &amp; bar',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithSpeakerDetailsLinksHtmlspecialcharedSpeakersName()
    {
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue('showSpeakerDetails', true);
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'foo & bar',
                'organization' => 'baz',
                'homepage' => 'www.foo.com',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $this->seminarUid,
            $speakerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['speakers' => '1']
        );

        self::assertRegExp(
            '#<a href="http://www.foo.com".*>foo &amp; bar</a>#',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithoutSpeakerDetailsLinksHtmlspecialcharedSpeakersName()
    {
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue('showSpeakerDetails', false);
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'foo & bar',
                'organization' => 'baz',
                'homepage' => 'www.foo.com',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $this->seminarUid,
            $speakerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['speakers' => '1']
        );

        self::assertRegExp(
            '#<a href="http://www.foo.com".*>foo &amp; bar</a>#',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithoutImageDoesNotDisplayImage()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue(
            'seminarImageSingleViewWidth',
            260
        );
        $this->fixture->setConfigurationValue(
            'seminarImageSingleViewHeight',
            160
        );
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            'style="background-image:',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewDisplaysSeminarImage()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue(
            'seminarImageSingleViewWidth',
            260
        );
        $this->fixture->setConfigurationValue(
            'seminarImageSingleViewHeight',
            160
        );

        $this->testingFramework->createDummyFile('test_foo.gif', base64_decode(self::BLANK_GIF, true));
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['image' => 'test_foo.gif']
        );
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $seminarWithImage = $this->fixture->main('', []);

        $this->testingFramework->deleteDummyFile('test_foo.gif');

        self::assertContains(
            'style="background-image:',
            $seminarWithImage
        );
    }

    public function testSingleViewForHideFieldsContainingImageHidesSeminarImage()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->fixture->setConfigurationValue('hideFields', 'image');
        $this->fixture->setConfigurationValue(
            'seminarImageSingleViewWidth',
            260
        );
        $this->fixture->setConfigurationValue(
            'seminarImageSingleViewHeight',
            160
        );

        $this->testingFramework->createDummyFile('test_foo.gif', base64_decode(self::BLANK_GIF, true));
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['image' => 'test_foo.gif']
        );
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $seminarWithImage = $this->fixture->main('', []);

        $this->testingFramework->deleteDummyFile('test_foo.gif');

        self::assertNotContains(
            'style="background-image:',
            $seminarWithImage
        );
    }

    /**
     * @test
     */
    public function singleViewCallsModifyEventSingleViewHook()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->find($this->seminarUid);
        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventSingleView::class);
        $hook->expects(self::once())->method('modifyEventSingleView')
            ->with($event, self::anything());
        // We don't test for the second parameter (the template instance here)
        // because we cannot access it from the outside.

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['singleView'][$hookClass] = $hookClass;

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
    }

    ///////////////////////////////////////////////////////
    // Tests concerning attached files in the single view
    ///////////////////////////////////////////////////////

    public function testSingleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFile()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertContains(
            $dummyFileName,
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFileLinkedToFile()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#<a href="https?://[\\w\\d_\\-/\\.]+' . $dummyFileName . '" *>' . $dummyFileName . '</a>#',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileInSubfolderOfUploadFolderAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFileLinkedToFile()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFolder = $this->testingFramework->createDummyFolder('test_folder');
        $dummyFile = $this->testingFramework->createDummyFile(
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFolder) .
                '/test.txt'
        );

        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '#<a href="https?://[\\w\\d_\\-/\\.]+' . $dummyFileName . '" *>' . basename($dummyFile) . '</a>#',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewWithTwoAttachedFilesAndDisabledLimitFileDownloadToAttendeesContainsBothFileNames()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $dummyFile2 = $this->testingFramework->createDummyFile();
        $dummyFileName2 =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName . ',' . $dummyFileName2]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $result = $this->fixture->main('', []);
        self::assertContains(
            $dummyFileName,
            $result
        );
        self::assertContains(
            $dummyFileName2,
            $result
        );
    }

    public function testSingleViewWithTwoAttachedFilesAndDisabledLimitFileDownloadToAttendeesContainsTwoAttachedFilesWithSortingSetInBackEnd()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $dummyFile2 = $this->testingFramework->createDummyFile();
        $dummyFileName2 =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName . ',' . $dummyFileName2]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '/.*(' . preg_quote($dummyFileName, '/') . ').*\\s*' .
                '.*(' . preg_quote($dummyFileName2, '/') . ').*/',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewWithOneAttachedFileAndLoggedInFeUserAndRegisteredContainsFileNameOfFile()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertContains(
            $dummyFileName,
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndLoggedInFeUserAndRegisteredContainsFileNameOfFileLinkedToFile()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#<a href="https?://[\\w\\d_\\-/\\.]+' . $dummyFileName . '" *>' . $dummyFileName . '</a>#',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileInSubfolderOfUploadFolderAndLoggedInFeUserAndRegisteredContainsFileNameOfFileLinkedToFile()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFolder = $this->testingFramework->createDummyFolder('test_folder');
        $dummyFile = $this->testingFramework->createDummyFile(
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFolder) .
                '/test.txt'
        );

        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#<a href="https?://[\\w\\d_\\-/\\.]+' . $dummyFileName . '" *>' . basename($dummyFile) . '</a>#',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewWithTwoAttachedFilesAndLoggedInFeUserAndRegisteredContainsBothFileNames()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $dummyFile2 = $this->testingFramework->createDummyFile();
        $dummyFileName2 =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName . ',' . $dummyFileName2]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $result = $this->fixture->main('', []);
        self::assertContains(
            $dummyFileName,
            $result
        );
        self::assertContains(
            $dummyFileName2,
            $result
        );
    }

    public function testSingleViewWithTwoAttachedFilesAndLoggedInFeUserAndRegisteredContainsTwoAttachedFilesWithSortingSetInBackEnd()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $dummyFile2 = $this->testingFramework->createDummyFile();
        $dummyFileName2 =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName . ',' . $dummyFileName2]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '/.*(' . preg_quote($dummyFileName, '/') . ').*\\s*' .
                '.*(' . preg_quote($dummyFileName2, '/') . ').*/',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsCSSClassWithFileType()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName = $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $matches = [];
        preg_match('/\\.(\\w+)$/', $dummyFileName, $matches);

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#class="filetype-' . $matches[1] . '"><a href="https?://[\\w\\d_\\-/\\.]+' . $dummyFileName . '" *>' .
                basename($dummyFile) . '</a>#',
            $this->fixture->main('', [])
        );
    }

    public function testAttachedFilesSubpartIsHiddenInSingleViewWithoutAttachedFilesAndWithLoggedInAndRegisteredFeUser()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    public function testAttachedFilesSubpartIsHiddenInSingleViewWithAttachedFilesAndLoggedInAndUnregisteredFeUser()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->testingFramework->createAndLoginFrontEndUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    public function testAttachedFilesSubpartIsHiddenInSingleViewWithAttachedFilesAndNoLoggedInFeUser()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    public function testAttachedFilesSubpartIsVisibleInSingleViewWithAttachedFilesAndLoggedInAndRegisteredFeUser()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    public function testAttachedFilesSubpartIsVisibleInSingleViewWithAttachedFilesAndDisabledLimitFileDownloadToAttendees()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    public function testAttachedFilesSubpartIsHiddenInSingleViewWithoutAttachedFilesAndWithDisabledLimitFileDownloadToAttendees()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning places in the single view
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForNoSiteDetailsContainsHtmlSpecialcharedTitleOfEventPlace()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('showSiteDetails', false);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a & place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->fixture->piVars['showUid'] = $eventUid;

        self::assertContains(
            'a &amp; place',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedTitleOfEventPlace()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('showSiteDetails', true);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a & place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->fixture->piVars['showUid'] = $eventUid;

        self::assertContains(
            'a &amp; place',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedAddressOfEventPlace()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('showSiteDetails', true);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'over & the rainbow']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->fixture->piVars['showUid'] = $eventUid;

        self::assertContains(
            'over &amp; the rainbow',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedCityOfEventPlace()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('showSiteDetails', true);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'Knödlingen & Großwürsteling']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->fixture->piVars['showUid'] = $eventUid;

        self::assertContains(
            'Knödlingen &amp; Großwürsteling',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedZipOfEventPlace()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->setConfigurationValue('showSiteDetails', true);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'Bonn', 'zip' => '12 & 45']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->fixture->piVars['showUid'] = $eventUid;

        self::assertContains(
            '12 &amp; 45',
            $this->fixture->main('', [])
        );
    }

    ////////////////////////////////////////////////////
    // Tests concerning time slots in the single view.
    ////////////////////////////////////////////////////

    public function testTimeSlotsSubpartIsHiddenInSingleViewWithoutTimeSlots()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
        );
    }

    public function testTimeSlotsSubpartIsVisibleInSingleViewWithOneTimeSlot()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $this->seminarUid]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => (string)$timeSlotUid]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
        );
    }

    /**
     * @test
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=4483
     */
    public function singleViewDisplaysTimeSlotTimesWithDash()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'begin_date' => mktime(9, 45, 0, 4, 2, 2020),
                'end_date' => mktime(18, 30, 0, 4, 2, 2020),
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => (string)$timeSlotUid]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertContains(
            '9:45&#8211;18:30',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewCanContainOneHtmlspecialcharedTimeSlotRoom()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room & 1',
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => (string)$timeSlotUid]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertContains(
            'room &amp; 1',
            $this->fixture->main('', [])
        );
    }

    public function testTimeSlotsSubpartIsVisibleInSingleViewWithTwoTimeSlots()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $timeSlotUid1 = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $this->seminarUid]
        );
        $timeSlotUid2 = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $this->seminarUid]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => $timeSlotUid1 . ',' . $timeSlotUid2]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
        );
    }

    public function testSingleViewCanContainTwoTimeSlotRooms()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $timeSlotUid1 = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room 1',
            ]
        );
        $timeSlotUid2 = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room 2',
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => $timeSlotUid1 . ',' . $timeSlotUid2]
        );

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $result = $this->fixture->main('', []);
        self::assertContains(
            'room 1',
            $result
        );
        self::assertContains(
            'room 2',
            $result
        );
    }

    /**
     * @test
     */
    public function timeSlotHookForEventWithoutTimeslotsNotGetsCalled()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventSingleView::class);
        $hook->expects(self::never())->method('modifyTimeSlotListRow');

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['singleView'][$hookClass] = $hookClass;

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
    }

    /**
     * @test
     */
    public function timeSlotHookForEventWithOneTimeslotGetsCalledOnceWithTimeSlot()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room 1',
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => $timeSlotUid]
        );

        /** @var Tx_Seminars_Model_TimeSlot $timeSlot */
        $timeSlot = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_TimeSlot::class)->find($timeSlotUid);
        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventSingleView::class);
        $hook->expects(self::once())->method('modifyTimeSlotListRow')
            ->with($timeSlot, self::anything());
        // We don't test for the second parameter (the template instance here)
        // because we cannot access it from the outside.

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['singleView'][$hookClass] = $hookClass;

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
    }

    /**
     * @test
     */
    public function timeSlotHookForEventWithTwoTimeslotGetsCalledTwice()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $timeSlotUid1 = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room 1',
            ]
        );
        $timeSlotUid2 = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room 2',
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => $timeSlotUid1 . ',' . $timeSlotUid2]
        );

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventSingleView::class);
        $hook->expects(self::exactly(2))->method('modifyTimeSlotListRow');

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['singleView'][$hookClass] = $hookClass;

        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
    }

    ///////////////////////////////////////////////////////
    // Tests concerning target groups in the single view.
    ///////////////////////////////////////////////////////

    public function testTargetGroupsSubpartIsHiddenInSingleViewWithoutTargetGroups()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
        );
    }

    public function testTargetGroupsSubpartIsVisibleInSingleViewWithOneTargetGroup()
    {
        $this->addTargetGroupRelation();

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
        );
    }

    /**
     * @test
     */
    public function singleViewCanContainOneHtmlSpecialcharedTargetGroupTitle()
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1 & 2']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'group 1 &amp; 2',
            $this->fixture->main('', [])
        );
    }

    public function testTargetGroupsSubpartIsVisibleInSingleViewWithTwoTargetGroups()
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1']
        );
        $this->addTargetGroupRelation(
            ['title' => 'group 2']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
        );
    }

    public function testSingleViewCanContainTwoTargetGroupTitles()
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1']
        );
        $this->addTargetGroupRelation(
            ['title' => 'group 2']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $result = $this->fixture->main('', []);

        self::assertContains(
            'group 1',
            $result
        );
        self::assertContains(
            'group 2',
            $result
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning requirements in the single view.
    ///////////////////////////////////////////////////////

    public function testSingleViewForSeminarWithoutRequirementsHidesRequirementsSubpart()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
        );
    }

    public function testSingleViewForSeminarWithOneRequirementDisplaysRequirementsSubpart()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent,
            'requirements'
        );
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
        );
    }

    public function testSingleViewForSeminarWithOneRequirementLinksRequirementToItsSingleView()
    {
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent,
            'requirements'
        );
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '/<a href=.*' . $requiredEvent . '.*>required_foo<\\/a>/',
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning dependencies in the single view.
    ///////////////////////////////////////////////////////

    public function testSingleViewForSeminarWithoutDependenciesHidesDependenciesSubpart()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_DEPENDENCIES')
        );
    }

    public function testSingleViewForSeminarWithOneDependencyDisplaysDependenciesSubpart()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $this->seminarUid
        );
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_DEPENDENCIES')
        );
    }

    public function testSingleViewForSeminarWithOneDependenciesShowsTitleOfDependency()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'depending_foo',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $this->seminarUid
        );
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'depending_foo',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOneDependencyContainsLinkToDependency()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'depending_foo',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $this->seminarUid
        );
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            '>depending_foo</a>',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithTwoDependenciesShowsTitleOfBothDependencies()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 2,
            ]
        );
        $dependingEventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'depending_foo',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid1,
            $this->seminarUid
        );
        $dependingEventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'depending_bar',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid2,
            $this->seminarUid
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $renderedOutput = $this->fixture->main('', []);
        self::assertContains(
            'depending_bar',
            $renderedOutput
        );
        self::assertContains(
            'depending_foo',
            $renderedOutput
        );
    }

    //////////////////////////////////////////////////////
    // Test concerning the event type in the single view
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedEventTypeTitleAndColonIfEventHasEventType()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'event_type' => $this->testingFramework->createRecord(
                    'tx_seminars_event_types',
                    ['title' => 'foo & type']
                ),
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'foo &amp; type:',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewNotContainsColonBeforeEventTitleIfEventHasNoEventType()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotRegExp(
            '/: *Test &amp; event/',
            $this->fixture->main('', [])
        );
    }

    //////////////////////////////////////////////////////
    // Test concerning the categories in the single view
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewCanContainOneHtmlSpecialcharedCategoryTitle()
    {
        $this->addCategoryRelation(
            ['title' => 'category & 1']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        self::assertContains(
            'category &amp; 1',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewCanContainTwoCategories()
    {
        $this->addCategoryRelation(
            ['title' => 'category 1']
        );
        $this->addCategoryRelation(
            ['title' => 'category 2']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;
        $result = $this->fixture->main('', []);

        self::assertContains(
            'category 1',
            $result
        );
        self::assertContains(
            'category 2',
            $result
        );
    }

    public function testSingleViewShowsCategoryIcon()
    {
        $this->testingFramework->createDummyFile('foo_test.gif', base64_decode(self::BLANK_GIF, true));
        $this->addCategoryRelation(
            [
                'title' => 'category 1',
                'icon' => 'foo_test.gif',
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $singleCategoryWithIcon = $this->fixture->main('', []);

        $this->testingFramework->deleteDummyFile('foo_test.gif');

        self::assertContains(
            'category 1 <img src="',
            $singleCategoryWithIcon
        );
    }

    public function testSingleViewShowsMultipleCategoriesWithIcons()
    {
        $this->testingFramework->createDummyFile('foo_test.gif', base64_decode(self::BLANK_GIF, true));
        $this->testingFramework->createDummyFile('foo_test2.gif', base64_decode(self::BLANK_GIF, true));
        $this->addCategoryRelation(
            [
                'title' => 'category 1',
                'icon' => 'foo_test.gif',
            ]
        );
        $this->addCategoryRelation(
            [
                'title' => 'category 2',
                'icon' => 'foo_test2.gif',
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $multipleCategoriesWithIcons = $this->fixture->main('', []);

        $this->testingFramework->deleteDummyFile('foo_test.gif');

        self::assertContains(
            'category 1 <img src="',
            $multipleCategoriesWithIcons
        );

        self::assertContains(
            'category 2 <img src="',
            $multipleCategoriesWithIcons
        );
    }

    public function testSingleViewForCategoryWithoutIconDoesNotShowCategoryIcon()
    {
        $this->addCategoryRelation(
            ['title' => 'category 1']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            'category 1 <img src="',
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////////////
    // Tests concerning the expiry in the single view
    ///////////////////////////////////////////////////

    public function testSingleViewForDateRecordWithExpiryContainsExpiryDate()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $this->seminarUid,
                'expiry' => mktime(0, 0, 0, 1, 1, 2008),
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $uid;

        self::assertContains(
            '01.01.2008',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForDateRecordWithoutExpiryNotContainsExpiryLabel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $this->seminarUid,
                'expiry' => 0,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $uid;

        self::assertNotContains(
            $this->fixture->translate('label_expiry'),
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////
    // Tests concerning the payment methods in the single view.
    /////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForEventWithoutPaymentMethodsNotContainsLabelForPaymentMethods()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            $this->fixture->translate('label_paymentmethods'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOnePaymentMethodContainsLabelForPaymentMethods()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'Payment Method']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['payment_methods' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            $this->fixture->translate('label_paymentmethods'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOnePaymentMethodContainsOnePaymentMethod()
    {
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'Payment Method']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['payment_methods' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'Payment Method',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithTwoPaymentMethodsContainsTwoPaymentMethods()
    {
        $paymentMethodUid1 = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'Payment Method 1']
        );
        $paymentMethodUid2 = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => 'Payment Method 2']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'payment_methods' => 2,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid2
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $result = $this->fixture->main('', []);
        self::assertContains(
            'Payment Method 1',
            $result
        );
        self::assertContains(
            'Payment Method 2',
            $result
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOnePaymentMethodContainsPaymentMethodTitleProcessedByHtmlspecialchars()
    {
        $paymentMethodTitle = '<b>Payment & Method</b>';
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods',
            ['title' => $paymentMethodTitle]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['payment_methods' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_payment_methods_mm',
            $this->seminarUid,
            $paymentMethodUid
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            htmlspecialchars($paymentMethodTitle),
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning the organizers in the single view
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForEventWithOrganzierShowsHtmlspecialcharedOrganizerTitle()
    {
        $this->addOrganizerRelation(['title' => 'foo & organizer']);

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'foo &amp; organizer',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithDescriptionShowsOrganizerDescription()
    {
        $this->addOrganizerRelation(
            ['title' => 'foo', 'description' => 'organizer description']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'organizer description',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithHomepageLinksHtmlSpecialcharedOrganizerNameToTheirHomepage()
    {
        $this->addOrganizerRelation(
            ['title' => 'foo & bar', 'homepage' => 'http://www.orgabar.com']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '#<a href="http://www.orgabar.com".*>foo &amp; bar</a>#',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewDoesNotHaveUnreplacedMarkers()
    {
        $this->addOrganizerRelation(['title' => 'foo organizer']);

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            '###',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithTwoOrganizersShowsBothOrganizers()
    {
        $this->addOrganizerRelation(['title' => 'organizer 1']);
        $this->addOrganizerRelation(['title' => 'organizer 2']);

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '/organizer 1.*organizer 2/s',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithHomepageHtmlSpecialcharsTitleOfOrganizer()
    {
        $this->addOrganizerRelation(
            ['title' => 'foo<bar']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'foo&lt;bar',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithoutHomepageHtmlSpecialCharsTitleOfOrganizer()
    {
        $this->addOrganizerRelation(
            ['title' => 'foo<bar']
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            htmlspecialchars('foo<bar'),
            $this->fixture->main('', [])
        );
    }

    //////////////////////////////////////////////////
    // Tests concerning hidden events in single view
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForZeroEventUidNoLoggedInUserReturnsWrongSeminarNumberMessage()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = 0;

        self::assertContains(
            $this->fixture->translate('message_missingSeminarNumber'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForHiddenRecordAndNoLoggedInUserReturnsWrongSeminarNumberMessage()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['hidden' => 1]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            $this->fixture->translate('message_wrongSeminarNumber'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForHiddenRecordAndLoggedInUserNotOwnerOfHiddenRecordReturnsWrongSeminarNumberMessage()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['hidden' => 1]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            $this->fixture->translate('message_wrongSeminarNumber'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForHiddenRecordAndLoggedInUserOwnerOfHiddenRecordShowsHiddenEvent()
    {
        $ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'hidden' => 1,
                'title' => 'hidden event',
                'owner_feuser' => $ownerUid,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'hidden event',
            $this->fixture->main('', [])
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the basic functions of the list view
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function eventListFlavorWithoutUidCreatesListView()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            [
                'createListView', 'createSingleView', 'pi_initPIflexForm', 'getTemplateCode', 'setLabels',
                'setCSS', 'createHelperObjects', 'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createListView')->with('seminar_list');
        $controller->expects(self::never())->method('createSingleView');

        $controller->piVars = [];

        $controller->main('', ['what_to_display' => 'seminar_list']);
    }

    /**
     * @test
     */
    public function eventListFlavorWithUidCreatesListView()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            [
                'createListView', 'createSingleView', 'pi_initPIflexForm', 'getTemplateCode', 'setLabels',
                'setCSS', 'createHelperObjects', 'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createListView')->with('seminar_list');
        $controller->expects(self::never())->method('createSingleView');

        $controller->piVars = ['showUid' => 42];

        $controller->main('', ['what_to_display' => 'seminar_list']);
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedSingleEventTitle()
    {
        self::assertContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedEventSubtitle()
    {
        self::assertContains(
            'Something for you &amp; me',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedEventTypeTitle()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'event_type' => $this->testingFramework->createRecord(
                    'tx_seminars_event_types',
                    ['title' => 'foo & type']
                ),
            ]
        );

        self::assertContains(
            'foo &amp; type',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedAccreditationNumber()
    {
        self::assertContains(
            '1 &amp; 1',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedPlaceTitle()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['place' => 1]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a & place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $this->seminarUid,
            $placeUid
        );

        self::assertContains(
            'a &amp; place',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedCityTitle()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['place' => 1]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'Bonn & Köln']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $this->seminarUid,
            $placeUid
        );

        self::assertContains(
            'Bonn &amp; Köln',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedOrganizerTitle()
    {
        $this->addOrganizerRelation(['title' => 'foo & organizer']);

        self::assertContains(
            'foo &amp; organizer',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlSpecialcharedTargetGroupTitle()
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1 & 2']
        );

        self::assertContains(
            'group 1 &amp; 2',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewContainsEventDatesUsingTopicTitle()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
            ]
        );

        $result = $this->fixture->main('', []);
        self::assertContains(
            'Test topic',
            $result
        );
        self::assertNotContains(
            'Test date',
            $result
        );
    }

    public function testListViewHidesHiddenSingleEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'title' => 'Test single event',
                'hidden' => 1,
            ]
        );

        self::assertNotContains(
            'Test single event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewHidesDeletedSingleEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'title' => 'Test single event',
                'deleted' => 1,
            ]
        );

        self::assertNotContains(
            'Test single event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewHidesHiddenEventDates()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'hidden' => 1,
            ]
        );

        self::assertNotContains(
            'Test topic',
            $this->fixture->main('', [])
        );
    }

    public function testListViewDisplaysSeminarImage()
    {
        $this->testingFramework->createDummyFile('test_foo.gif', base64_decode(self::BLANK_GIF, true));

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['image' => 'test_foo.gif']
        );
        $listViewWithImage = $this->fixture->main('', []);
        $this->testingFramework->deleteDummyFile('test_foo.gif');

        self::assertContains(
            '<img src="',
            $listViewWithImage
        );
    }

    public function testListViewForSeminarWithoutImageDoesNotDisplayImage()
    {
        self::assertNotContains(
            '<img src="',
            $this->fixture->main('', [])
        );
    }

    public function testListViewForSeminarWithoutImageRemovesImageMarker()
    {
        self::assertNotContains(
            '###IMAGE###',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewUsesTopicImage()
    {
        $fileName = 'test_foo.gif';
        $topicTitle = 'Test topic';

        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => $topicTitle,
                'image' => $fileName,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
            ]
        );

        /** @var $content ContentObjectRenderer|PHPUnit_Framework_MockObject_MockObject */
        $content = $this->getMock(ContentObjectRenderer::class, ['IMAGE', 'cObjGetSingle']);
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7006000) {
            $content->expects(self::any())->method('cObjGetSingle')
                ->with(
                    'IMAGE',
                    [
                        'file' => 'uploads/tx_seminars/' . $fileName,
                        'file.' => ['width' => '0c', 'height' => '0c'],
                        'altText' => $topicTitle,
                        'titleText' => $topicTitle,
                    ]
                )
                ->will(self::returnValue('<img src="foo.jpg" alt="' . $topicTitle . '" title="' . $topicTitle . '"/>'));
        } else {
            $content->expects(self::any())->method('IMAGE')
                ->with(
                    [
                        'file' => 'uploads/tx_seminars/' . $fileName,
                        'file.' => ['width' => '0c', 'height' => '0c'],
                        'altText' => $topicTitle,
                        'titleText' => $topicTitle,
                    ]
                )
                ->will(self::returnValue('<img src="foo.jpg" alt="' . $topicTitle . '" title="' . $topicTitle . '"/>'));
        }
        $this->fixture->cObj = $content;

        self::assertRegExp(
            '/<img src="[^"]*"[^>]*title="' . $topicTitle . '"/',
            $this->fixture->main('', [])
        );
    }

    public function testListViewNotContainsExpiryLabel()
    {
        self::assertNotContains(
            $this->fixture->translate('label_expiry'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewHidesStatusColumnByDefault()
    {
        $this->fixture->main('', []);

        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function listViewShowsBookedOutEventByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'needs_registration' => 1,
                'attendees_max' => 5,
                'offline_attendees' => 5,
            ]
        );

        self::assertContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForShowOnlyEventsWithVacanciesSetHidesBookedOutEvent()
    {
        $this->fixture->setConfigurationValue(
            'showOnlyEventsWithVacancies',
            true
        );

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'needs_registration' => 1,
                'attendees_max' => 5,
                'offline_attendees' => 5,
            ]
        );

        self::assertNotContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////
    // Tests concerning the result counter in the list view
    /////////////////////////////////////////////////////////

    public function testResultCounterIsZeroForNoResults()
    {
        $this->fixture->setConfigurationValue(
            'pidList',
            $this->testingFramework->createSystemFolder()
        );
        $this->fixture->main('', []);

        self::assertEquals(
            0,
            $this->fixture->internal['res_count']
        );
    }

    public function testResultCounterIsOneForOneResult()
    {
        $this->fixture->main('', []);

        self::assertEquals(
            1,
            $this->fixture->internal['res_count']
        );
    }

    public function testResultCounterIsTwoForTwoResultsOnOnePage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Another event',
            ]
        );
        $this->fixture->main('', []);

        self::assertEquals(
            2,
            $this->fixture->internal['res_count']
        );
    }

    public function testResultCounterIsSixForSixResultsOnTwoPages()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'pid' => $this->systemFolderPid,
                    'title' => 'Another event',
                ]
            );
        }
        $this->fixture->main('', []);

        self::assertEquals(
            6,
            $this->fixture->internal['res_count']
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the list view, filtered by category.
    //////////////////////////////////////////////////////////

    public function testListViewContainsEventsWithoutCategoryByDefault()
    {
        self::assertContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewContainsEventsWithCategoryByDefault()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithCategoryExcludesEventsWithoutCategory()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->fixture->piVars['category'] = $categoryUid;

        self::assertNotContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithCategoryContainsEventsWithSelectedCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );
        $this->fixture->piVars['category'] = $categoryUid;

        self::assertContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithCategoryExcludesHiddenEventWithSelectedCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                'hidden' => 1,
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );
        $this->fixture->piVars['category'] = $categoryUid;

        self::assertNotContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithCategoryExcludesDeletedEventWithSelectedCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                'deleted' => 1,
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );
        $this->fixture->piVars['category'] = $categoryUid;

        self::assertNotContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithCategoryExcludesEventsWithNotSelectedCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'another category']
        );
        $this->fixture->piVars['category'] = $categoryUid2;

        self::assertNotContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithCategoryContainsEventsWithSelectedAndOtherCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 2,
            ]
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'another category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid2
        );
        $this->fixture->piVars['category'] = $categoryUid2;

        self::assertContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewWithCategoryContainsEventsWithOneOfTwoSelectedCategories()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid = (string)$this->testingFramework->createRecord('tx_seminars_categories', ['title' => 'a category']);
        $this->testingFramework->createRelation('tx_seminars_seminars_categories_mm', $eventUid, $categoryUid);
        $this->fixture->piVars['categories'][] = $categoryUid;

        self::assertContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewWithCategoryExcludesEventsWithNotSelectedCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid1 = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => 'a category']);
        $this->testingFramework->createRelation('tx_seminars_seminars_categories_mm', $eventUid, $categoryUid1);

        $categoryUid2 = (string)$this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'another category']
        );
        $this->fixture->piVars['categories'][] = $categoryUid2;

        self::assertNotContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests concerning the list view, filtered by event type
    ///////////////////////////////////////////////////////////

    public function testListViewContainsEventsWithoutEventTypeByDefault()
    {
        self::assertContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewContainsEventsWithEventTypeByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'event_type' => $this->testingFramework->createRecord(
                    'tx_seminars_event_types',
                    ['title' => 'foo type']
                ),
            ]
        );

        self::assertContains(
            'Event with type',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithEventTypeExcludesEventsWithoutEventType()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo type']
        );
        $this->fixture->piVars['event_type'] = [$eventTypeUid];

        self::assertNotContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithEventTypeCanContainOneEventWithSelectedEventType()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'event_type' => $eventTypeUid,
            ]
        );
        $this->fixture->piVars['event_type'] = [$eventTypeUid];

        self::assertContains(
            'Event with type',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithEventTypeCanContainTwoEventsWithTwoDifferentSelectedEventTypes()
    {
        $eventTypeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type 1',
                'event_type' => $eventTypeUid1,
            ]
        );
        $eventTypeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type 2',
                'event_type' => $eventTypeUid2,
            ]
        );
        $this->fixture->piVars['event_type'] = [
            $eventTypeUid1, $eventTypeUid2,
        ];

        $result = $this->fixture->main('', []);

        self::assertContains(
            'Event with type 1',
            $result
        );
        self::assertContains(
            'Event with type 2',
            $result
        );
    }

    public function testListViewWithEventTypeExcludesHiddenEventWithSelectedEventType()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'hidden' => 1,
                'event_type' => $eventTypeUid,
            ]
        );
        $this->fixture->piVars['event_type'] = [$eventTypeUid];

        self::assertNotContains(
            'Event with type',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithEventTypeExcludesDeletedEventWithSelectedEventType()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'deleted' => 1,
                'event_type' => $eventTypeUid,
            ]
        );
        $this->fixture->piVars['event_type'] = [$eventTypeUid];

        self::assertNotContains(
            'Event with type',
            $this->fixture->main('', [])
        );
    }

    public function testListViewWithEventTypeExcludesEventsWithNotSelectedEventType()
    {
        $eventTypeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'event_type' => $eventTypeUid1,
            ]
        );

        $eventTypeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'another eventType']
        );
        $this->fixture->piVars['event_type'] = [$eventTypeUid2];

        self::assertNotContains(
            'Event with type',
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////
    // Tests concerning the the list view, filtered by date
    /////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenFromDateShowsEventWithBeginDateAfterFromDate()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $fromTime = $simTime - 86400;
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event From',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $fromTime);
        $this->fixture->piVars['from_month'] = date('n', $fromTime);
        $this->fixture->piVars['from_year'] = date('Y', $fromTime);

        self::assertContains(
            'Foo Event From',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateDoesNotShowEventWithBeginDateBeforeFromDate()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $fromTime = $simTime + 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event From',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $fromTime);
        $this->fixture->piVars['from_month'] = date('n', $fromTime);
        $this->fixture->piVars['from_year'] = date('Y', $fromTime);

        self::assertNotContains(
            'Foo Event From',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingDayShowsEventWithBeginDateOnFirstDayOfMonth()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event From',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_month'] = date('n', $simTime);
        $this->fixture->piVars['from_year'] = date('Y', $simTime);

        self::assertContains(
            'Foo Event From',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingYearShowsEventWithBeginDateInCurrentYearAfterFromDate()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $fromTime = $simTime - 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event From',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $fromTime);
        $this->fixture->piVars['from_month'] = date('n', $fromTime);

        self::assertContains(
            'Foo Event From',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingMonthShowsEventWithBeginDateOnFirstMonthOfYear()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event From',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $simTime);
        $this->fixture->piVars['from_year'] = date('Y', $simTime);

        self::assertContains(
            'Foo Event From',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingMonthAndDayShowsEventWithBeginDateOnFirstDayOfGivenYear()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event From',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_year'] = date('Y', $simTime);

        self::assertContains(
            'Foo Event From',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateShowsEventWithBeginDateBeforeToDate()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $toTime = $simTime + 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['to_day'] = date('j', $toTime);
        $this->fixture->piVars['to_month'] = date('n', $toTime);
        $this->fixture->piVars['to_year'] = date('Y', $toTime);

        self::assertContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateHidesEventWithBeginDateAfterToDate()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $toTime = $simTime - 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['to_day'] = date('j', $toTime);
        $this->fixture->piVars['to_month'] = date('n', $toTime);
        $this->fixture->piVars['to_year'] = date('Y', $toTime);

        self::assertNotContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingDayShowsEventWithBeginDateOnEndOfGivenMonth()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['to_month'] = date('n', $simTime);
        $this->fixture->piVars['to_year'] = date('Y', $simTime);

        self::assertContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingYearShowsEventWithBeginDateOnThisYearBeforeToDate()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $toTime = $simTime + 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['to_day'] = date('j', $toTime);
        $this->fixture->piVars['to_month'] = date('n', $toTime);

        self::assertContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingMonthShowsEventWithBeginDateOnDayOfLastMonthOfGivenYear()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['to_day'] = date('j', $simTime);
        $this->fixture->piVars['to_year'] = date('Y', $simTime);

        self::assertContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingMonthAndDayShowsEventWithBeginDateOnEndOfGivenYear()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['to_year'] = date('Y', $simTime);

        self::assertContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesShowsEventWithBeginDateWithinTimespan()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $fromTime = $simTime - 86400;
        $toTime = $simTime + 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $fromTime);
        $this->fixture->piVars['from_month'] = date('n', $fromTime);
        $this->fixture->piVars['from_year'] = date('Y', $fromTime);
        $this->fixture->piVars['to_day'] = date('j', $toTime);
        $this->fixture->piVars['to_month'] = date('n', $toTime);
        $this->fixture->piVars['to_year'] = date('Y', $toTime);

        self::assertContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesCanShowTwoEventsWithBeginDateWithinTimespan()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $fromTime = $simTime - 86400;
        $toTime = $simTime + 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $simTime,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Bar Event To',
                'begin_date' => $simTime,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $fromTime);
        $this->fixture->piVars['from_month'] = date('n', $fromTime);
        $this->fixture->piVars['from_year'] = date('Y', $fromTime);
        $this->fixture->piVars['to_day'] = date('j', $toTime);
        $this->fixture->piVars['to_month'] = date('n', $toTime);
        $this->fixture->piVars['to_year'] = date('Y', $toTime);

        $output = $this->fixture->main('', []);

        self::assertContains(
            'Foo Event To',
            $output
        );
        self::assertContains(
            'Bar Event To',
            $output
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesDoesNotShowEventWithBeginDateBeforeTimespan()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $toTime = $simTime + 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'begin_date' => $simTime - 86000,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $simTime);
        $this->fixture->piVars['from_month'] = date('n', $simTime);
        $this->fixture->piVars['from_year'] = date('Y', $simTime);
        $this->fixture->piVars['to_day'] = date('j', $toTime);
        $this->fixture->piVars['to_month'] = date('n', $toTime);
        $this->fixture->piVars['to_year'] = date('Y', $toTime);

        self::assertNotContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesDoesNotShowEventWithBeginDateAfterTimespan()
    {
        $simTime = $GLOBALS['SIM_EXEC_TIME'];
        $fromTime = $simTime - 86400;

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'begin_date' => $simTime + 86400,
            ]
        );

        $this->fixture->piVars['from_day'] = date('j', $fromTime);
        $this->fixture->piVars['from_month'] = date('n', $fromTime);
        $this->fixture->piVars['from_year'] = date('Y', $fromTime);
        $this->fixture->piVars['to_day'] = date('j', $simTime);
        $this->fixture->piVars['to_month'] = date('n', $simTime);
        $this->fixture->piVars['to_year'] = date('Y', $simTime);

        self::assertNotContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForSentDateButAllDatesZeroShowsEventWithoutBeginDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
            ]
        );

        $this->fixture->piVars['from_day'] = 0;
        $this->fixture->piVars['from_month'] = 0;
        $this->fixture->piVars['from_year'] = 0;
        $this->fixture->piVars['to_day'] = 0;
        $this->fixture->piVars['to_month'] = 0;
        $this->fixture->piVars['to_year'] = 0;

        self::assertContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests concerning the filtering by age in the list view
    ///////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenAgeShowsEventWithTargetgroupWithinAge()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 20]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 50,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->fixture->piVars['age'] = 15;

        self::assertContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenAgeAndEventAgespanHigherThanAgeDoesNotShowThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 20]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event To',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 50,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->fixture->piVars['age'] = 4;

        self::assertNotContains(
            'Foo Event To',
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests concerning the filtering by organizer in the list view
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenOrganizerShowsEventWithOrganizer()
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'Foo Event', 'pid' => $this->systemFolderPid]
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->fixture->piVars['organizer'][] = $organizerUid;

        self::assertContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenOrganizerDoesNotShowEventWithOtherOrganizer()
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'Foo Event', 'pid' => $this->systemFolderPid]
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->fixture->piVars['organizer'][]
            = $this->testingFramework->createRecord('tx_seminars_organizers');

        self::assertNotContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////
    // Tests concerning the filtering by price in the list view
    /////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenPriceFromShowsEventWithRegularPriceHigherThanPriceFrom()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 21,
            ]
        );

        $this->fixture->piVars['price_from'] = 20;

        self::assertContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenPriceToShowsEventWithRegularPriceLowerThanPriceTo()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 19,
            ]
        );

        $this->fixture->piVars['price_to'] = 20;

        self::assertContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenPriceRangeShowsEventWithRegularPriceWithinRange()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 21,
            ]
        );

        $this->fixture->piVars['price_from'] = 20;
        $this->fixture->piVars['price_to'] = 22;

        self::assertContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenPriceRangeHidesEventWithRegularPriceOutsideRange()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 23,
            ]
        );

        $this->fixture->piVars['price_from'] = 20;
        $this->fixture->piVars['price_to'] = 22;

        self::assertNotContains(
            'Foo Event',
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////////////
    // Tests concerning the sorting in the list view.
    ///////////////////////////////////////////////////

    public function testListViewCanBeSortedByTitleAscending()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event B',
            ]
        );

        $this->fixture->piVars['sort'] = 'title:0';
        $output = $this->fixture->main('', []);

        self::assertTrue(
            strpos($output, 'Event A') < strpos($output, 'Event B')
        );
    }

    public function testListViewCanBeSortedByTitleDescending()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event B',
            ]
        );

        $this->fixture->piVars['sort'] = 'title:1';
        $output = $this->fixture->main('', []);

        self::assertTrue(
            strpos($output, 'Event B') < strpos($output, 'Event A')
        );
    }

    public function testListViewSortedByCategoryWithoutStaticTemplateDoesNotCrash()
    {
        $fixture = new Tx_Seminars_FrontEnd_DefaultController();
        $fixture->init(
            ['sortListViewByCategory' => 1]
        );

        $fixture->main('', []);
    }

    public function testListViewCanBeSortedByTitleAscendingWithinOneCategory()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );

        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event B',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid
        );

        $this->fixture->setConfigurationValue('sortListViewByCategory', 1);
        $this->fixture->piVars['sort'] = 'title:0';
        $output = $this->fixture->main('', []);

        self::assertTrue(
            strpos($output, 'Event A') < strpos($output, 'Event B')
        );
    }

    public function testListViewCanBeSortedByTitleDescendingWithinOneCategory()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );

        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event B',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid
        );

        $this->fixture->setConfigurationValue('sortListViewByCategory', 1);
        $this->fixture->piVars['sort'] = 'title:1';
        $output = $this->fixture->main('', []);

        self::assertTrue(
            strpos($output, 'Event B') < strpos($output, 'Event A')
        );
    }

    public function testListViewCategorySortingComesBeforeSortingByTitle()
    {
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Category Y']
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Category X']
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event B',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->fixture->setConfigurationValue('sortListViewByCategory', 1);
        $this->fixture->piVars['sort'] = 'title:0';
        $output = $this->fixture->main('', []);

        self::assertTrue(
            strpos($output, 'Event B') < strpos($output, 'Event A')
        );
    }

    public function testListViewCategorySortingHidesRepeatedCategoryNames()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Category X']
        );

        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event B',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid
        );

        $this->fixture->setConfigurationValue('sortListViewByCategory', 1);
        $this->fixture->piVars['sort'] = 'title:0';

        self::assertEquals(
            1,
            mb_substr_count(
                $this->fixture->main('', []),
                'Category X'
            )
        );
    }

    public function testListViewCategorySortingListsDifferentCategoryNames()
    {
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Category Y']
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Category X']
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                // the number of categories
                'categories' => 1,
                'title' => 'Event B',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->fixture->setConfigurationValue('sortListViewByCategory', 1);
        $this->fixture->piVars['sort'] = 'title:0';
        $output = $this->fixture->main('', []);

        self::assertContains(
            'Category X',
            $output
        );
        self::assertContains(
            'Category Y',
            $output
        );
    }

    ////////////////////////////////////////////////////////////////////
    // Tests concerning the links to the single view in the list view.
    ////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function teaserGetsLinkedToSingleView()
    {
        $this->fixture->setConfigurationValue('hideColumns', '');

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with teaser',
                'teaser' => 'Test Teaser',
            ]
        );

        self::assertContains(
            '>Test Teaser</a>',
            $this->fixture->main('', [])
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the category links in the list view.
    //////////////////////////////////////////////////////////

    public function testCategoryIsLinkedToTheFilteredListView()
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->fixture->setConfigurationValue('listPID', $frontEndPageUid);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertContains(
            'tx_seminars_pi1%5Bcategory%5D=' . $categoryUid,
            $this->fixture->main('', [])
        );
    }

    public function testCategoryIsNotLinkedFromSpecializedListView()
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->fixture->setConfigurationValue('listPID', $frontEndPageUid);
        $this->fixture->setConfigurationValue('what_to_display', 'events_next_day');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                'end_date' => Tx_Oelib_Time::SECONDS_PER_WEEK,
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );
        $this->fixture->createSeminar($eventUid);

        self::assertNotContains(
            'tx_seminars_pi1[category%5D=' . $categoryUid,
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning omitDateIfSameAsPrevious.
    ///////////////////////////////////////////////

    public function testOmitDateIfSameAsPreviousOnDifferentDatesWithActiveConfig()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event title',
                'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
                'end_date' => mktime(18, 0, 0, 1, 1, 2020),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event title',
                'begin_date' => mktime(10, 0, 0, 1, 1, 2021),
                'end_date' => mktime(18, 0, 0, 1, 1, 2021),
            ]
        );

        $this->fixture->piVars['sort'] = 'date:0';
        $this->fixture->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            1
        );

        $output = $this->fixture->main('', []);
        self::assertContains(
            '2020',
            $output
        );
        self::assertContains(
            '2021',
            $output
        );
    }

    public function testOmitDateIfSameAsPreviousOnDifferentDatesWithInactiveConfig()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event title',
                'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
                'end_date' => mktime(18, 0, 0, 1, 1, 2020),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event title',
                'begin_date' => mktime(10, 0, 0, 1, 1, 2021),
                'end_date' => mktime(18, 0, 0, 1, 1, 2021),
            ]
        );

        $this->fixture->piVars['sort'] = 'date:0';
        $this->fixture->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            0
        );

        $output = $this->fixture->main('', []);
        self::assertContains(
            '2020',
            $output
        );
        self::assertContains(
            '2021',
            $output
        );
    }

    public function testOmitDateIfSameAsPreviousOnSameDatesWithActiveConfig()
    {
        $eventData = [
                'pid' => $this->systemFolderPid,
                'title' => 'Event title',
                'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
                'end_date' => mktime(18, 0, 0, 1, 1, 2020),
        ];
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            $eventData
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            $eventData
        );

        $this->fixture->piVars['sort'] = 'date:0';
        $this->fixture->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            1
        );

        self::assertEquals(
            1,
            mb_substr_count(
                $this->fixture->main('', []),
                '2020'
            )
        );
    }

    public function testOmitDateIfSameAsPreviousOnSameDatesWithInactiveConfig()
    {
        $eventData = [
                'pid' => $this->systemFolderPid,
                'title' => 'Event title',
                'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
                'end_date' => mktime(18, 0, 0, 1, 1, 2020),
        ];
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            $eventData
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            $eventData
        );

        $this->fixture->piVars['sort'] = 'date:0';
        $this->fixture->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            0
        );

        self::assertEquals(
            2,
            mb_substr_count(
                $this->fixture->main('', []),
                '2020'
            )
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests concerning limiting the list view to event types
    ///////////////////////////////////////////////////////////

    public function testListViewLimitedToEventTypesIgnoresEventsWithoutEventType()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'an event type']
        );
        $this->fixture->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid
        );

        self::assertNotContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewLimitedToEventTypesContainsEventsWithMultipleSelectedEventTypes()
    {
        $eventTypeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'an event type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'event_type' => $eventTypeUid1,
            ]
        );

        $eventTypeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'an event type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with another type',
                'event_type' => $eventTypeUid2,
            ]
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid1 . ',' . $eventTypeUid2
        );

        $result = $this->fixture->main('', []);
        self::assertContains(
            'Event with type',
            $result
        );
        self::assertContains(
            'Event with another type',
            $result
        );
    }

    public function testListViewLimitedToEventTypesIgnoresEventsWithNotSelectedEventType()
    {
        $eventTypeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'an event type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'event_type' => $eventTypeUid1,
            ]
        );

        $eventTypeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'another eventType']
        );
        $this->fixture->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid2
        );

        self::assertNotContains(
            'Event with type',
            $this->fixture->main('', [])
        );
    }

    public function testListViewForSingleEventTypeOverridesLimitToEventTypes()
    {
        $eventTypeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'an event type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with type',
                'event_type' => $eventTypeUid1,
            ]
        );

        $eventTypeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'an event type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with another type',
                'event_type' => $eventTypeUid2,
            ]
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid1
        );
        $this->fixture->piVars['event_type'] = [$eventTypeUid2];

        $result = $this->fixture->main('', []);
        self::assertNotContains(
            'Event with type',
            $result
        );
        self::assertContains(
            'Event with another type',
            $result
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning limiting the list view to categories
    //////////////////////////////////////////////////////////

    public function testListViewLimitedToCategoriesIgnoresEventsWithoutCategory()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->fixture->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid
        );

        self::assertNotContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewLimitedToCategoriesContainsEventsWithMultipleSelectedCategories()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with another category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid1 . ',' . $categoryUid2
        );

        $result = $this->fixture->main('', []);
        self::assertContains(
            'Event with category',
            $result
        );
        self::assertContains(
            'Event with another category',
            $result
        );
    }

    public function testListViewLimitedToCategoriesIgnoresEventsWithNotSelectedCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'another category']
        );
        $this->fixture->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid2
        );

        self::assertNotContains(
            'Event with category',
            $this->fixture->main('', [])
        );
    }

    public function testListViewForSingleCategoryOverridesLimitToCategories()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with another category',
                // the number of categories
                'categories' => 1,
            ]
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid1
        );
        $this->fixture->piVars['category'] = $categoryUid2;

        $result = $this->fixture->main('', []);
        self::assertNotContains(
            'Event with category',
            $result
        );
        self::assertContains(
            'Event with another category',
            $result
        );
    }

    //////////////////////////////////////////////////////
    // Tests concerning limiting the list view to places
    //////////////////////////////////////////////////////

    public function testListViewLimitedToPlacesExcludesEventsWithoutPlace()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->fixture->setConfigurationValue(
            'limitListViewToPlaces',
            $placeUid
        );

        self::assertNotContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testListViewLimitedToPlacesContainsEventsWithMultipleSelectedPlaces()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with place',
                // the number of places
                'place' => 1,
            ]
        );
        $placeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $placeUid1
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with another place',
                // the number of places
                'place' => 1,
            ]
        );
        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid2,
            $placeUid2
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToPlaces',
            $placeUid1 . ',' . $placeUid2
        );

        $result = $this->fixture->main('', []);
        self::assertContains(
            'Event with place',
            $result
        );
        self::assertContains(
            'Event with another place',
            $result
        );
    }

    public function testListViewLimitedToPlacesExcludesEventsWithNotSelectedPlace()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with place',
                // the number of places
                'place' => 1,
            ]
        );
        $placeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid1
        );

        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'another place']
        );
        $this->fixture->setConfigurationValue(
            'limitListViewToPlaces',
            $placeUid2
        );

        self::assertNotContains(
            'Event with place',
            $this->fixture->main('', [])
        );
    }

    public function testListViewLimitedToPlacesExcludesHiddenEventWithSelectedPlace()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with place',
                'hidden' => 1,
                // the number of places
                'place' => 1,
            ]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToPlaces',
            $placeUid
        );

        $result = $this->fixture->main('', []);
        self::assertNotContains(
            'Event with place',
            $result
        );
    }

    public function testListViewLimitedToPlacesExcludesDeletedEventWithSelectedPlace()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with place',
                'deleted' => 1,
                // the number of places
                'place' => 1,
            ]
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToPlaces',
            $placeUid
        );

        $result = $this->fixture->main('', []);
        self::assertNotContains(
            'Event with place',
            $result
        );
    }

    public function testListViewLimitedToPlacesFromSelectorWidgetIgnoresFlexFormsValues()
    {
        // TODO: This needs to be changed when bug 2304 gets fixed.
        // @see https://bugs.oliverklee.com/show_bug.cgi?id=2304
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with place',
                // the number of places
                'place' => 1,
            ]
        );
        $placeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $placeUid1
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with another place',
                // the number of places
                'place' => 1,
            ]
        );
        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'a place']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid2,
            $placeUid2
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToPlaces',
            $placeUid1
        );
        $this->fixture->piVars['place'] = [$placeUid2];

        $result = $this->fixture->main('', []);
        self::assertNotContains(
            'Event with place',
            $result
        );
        self::assertContains(
            'Event with another place',
            $result
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning limiting the list view to organizers
    //////////////////////////////////////////////////////////

    public function testListViewLimitedToOrganizersContainsEventsWithSelectedOrganizer()
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
            );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with organizer 1',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid
        );

        $result = $this->fixture->main('', []);

        self::assertContains(
            'Event with organizer 1',
            $result
        );
    }

    public function testListViewLimitedToOrganizerExcludesEventsWithNotSelectedOrganizer()
    {
        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
            );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with organizer 1',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid1,
            $organizerUid1,
            'organizers'
        );

        $organizerUid2 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
            );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with organizer 2',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid2,
            $organizerUid2,
            'organizers'
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid1
        );

        self::assertNotContains(
            'Event with organizer 2',
            $this->fixture->main('', [])
        );
    }

    public function testListViewLimitedToOrganizersFromSelectorWidgetIgnoresFlexFormsValues()
    {
        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
            );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with organizer 1',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid1,
            $organizerUid1,
            'organizers'
        );

        $organizerUid2 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
            );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with organizer 2',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid2,
            $organizerUid2,
            'organizers'
        );

        $this->fixture->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid1
        );
        $this->fixture->piVars['organizer'] = [$organizerUid2];

        $result = $this->fixture->main('', []);

        self::assertNotContains(
            'Event with organizer 1',
            $result
        );
        self::assertContains(
            'Event with organizer 2',
            $result
        );
    }

    ////////////////////////////////////////////////////////////
    // Tests concerning the registration link in the list view
    ////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForEventWithUnlimitedVacanciesShowsRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithNoVacanciesAndQueueShowsRegisterOnQueueLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
                'queue_size' => 1,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertContains(
            sprintf(
                $this->fixture->translate('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithNoVacanciesAndNoQueueDoesNotShowRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
                'queue_size' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertNotContains(
            sprintf(
                $this->fixture->translate('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithVacanciesAndNoDateShowsPrebookNowString()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
                'queue_size' => 0,
                'begin_date' => '',
            ]
        );

        self::assertNotContains(
            $this->fixture->translate('label_onlinePrebooking'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithRegistrationBeginInFutureHidesRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'queue_size' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => $GLOBALS['SIM_EXEC_TIME'] + 20,
            ]
        );

        self::assertNotContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithRegistrationBeginInFutureShowsRegistrationOpenOnMessage()
    {
        $registrationBegin = $GLOBALS['SIM_EXEC_TIME'] + 20;
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'queue_size' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => $registrationBegin,
            ]
        );

        self::assertContains(
            sprintf(
                $this->fixture->translate('message_registrationOpensOn'),
                strftime('%d.%m.%Y %H:%M', $registrationBegin)
            ),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithRegistrationBeginInPastShowsRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'queue_size' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => $GLOBALS['SIM_EXEC_TIME'] - 42,
            ]
        );

        self::assertContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithoutRegistrationBeginShowsRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => 0,
            ]
        );

        self::assertContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    //////////////////////////////////////////
    // Tests concerning the "my events" view
    //////////////////////////////////////////

    public function testMyEventsContainsTitleOfEventWithRegistrationForLoggedInUser()
    {
        $this->createLogInAndRegisterFeUser();
        $this->fixture->setConfigurationValue('what_to_display', 'my_events');

        self::assertContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testMyEventsNotContainsTitleOfEventWithoutRegistrationForLoggedInUser()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('what_to_display', 'my_events');

        self::assertNotContains(
            'Test &amp; event',
            $this->fixture->main('', [])
        );
    }

    public function testMyEventsContainsExpiryOfEventWithExpiryAndRegistrationForLoggedInUser()
    {
        $this->createLogInAndRegisterFeUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['expiry' => mktime(0, 0, 0, 1, 1, 2008)]
        );
        $this->fixture->setConfigurationValue('what_to_display', 'my_events');

        self::assertContains(
            '01.01.2008',
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////////////////////
    // Tests concerning mayManagersEditTheirEvents in the "my vip events" list view
    /////////////////////////////////////////////////////////////////////////////////

    public function testEditSubpartWithMayManagersEditTheirEventsSetToFalseIsHiddenInMyVipEventsListView()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('mayManagersEditTheirEvents', 0);
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->fixture->main('', []);
        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTITEM_WRAPPER_EDIT')
        );
    }

    public function testEditSubpartWithMayManagersEditTheirEventsSetToTrueIsVisibleInMyVipEventsListView()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('mayManagersEditTheirEvents', 1);
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTITEM_WRAPPER_EDIT')
        );
    }

    public function testManagedEventsViewWithMayManagersEditTheirEventsSetToTrueContainsEditLink()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('mayManagersEditTheirEvents', 1);
        $editorPid = $this->testingFramework->createFrontEndPage();
        $this->fixture->setConfigurationValue('eventEditorPID', $editorPid);
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertContains(
            '?id=' . $editorPid,
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////
    // Tests concerning allowCsvExportOfRegistrationsInMyVipEventsView in the "my vip events" list view
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function registrationsSubpartWithAllowCsvExportOfRegistrationsInMyVipEventsViewSetToFalseIsHiddenInMyVipEventsListView()
    {
        $this->createLogInAndAddFeUserAsVip();

        $this->fixture->main(
            '',
            [
                'allowCsvExportOfRegistrationsInMyVipEventsView' => 0,
                'what_to_display' => 'my_vip_events',
            ]
        );
        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTITEM_WRAPPER_REGISTRATIONS')
        );
    }

    /**
     * @test
     */
    public function registrationsSubpartWithAllowCsvExportOfRegistrationsInMyVipEventsViewSetToTrueIsVisibleInMyVipEventsListView()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue(
            'allowCsvExportOfRegistrationsInMyVipEventsView',
            1
        );
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->fixture->main('', []);
        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTITEM_WRAPPER_REGISTRATIONS')
        );
    }

    /**
     * @test
     */
    public function myVipEventsViewForAllowCsvExportOfRegistrationsInTrueHasEventUidPiVarInRegistrationLink()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue(
            'allowCsvExportOfRegistrationsInMyVipEventsView',
            1
        );
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertContains(
            'tx_seminars_pi2%5BeventUid%5D',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsViewForAllowCsvExportOfRegistrationsInTrueHasTablePiVarInRegistrationLink()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue(
            'allowCsvExportOfRegistrationsInMyVipEventsView',
            1
        );
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertContains(
            'tx_seminars_pi2%5Btable%5D=tx_seminars_attendances',
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests concerning the category list in the my vip events view
    /////////////////////////////////////////////////////////////////

    public function testMyVipEventsViewShowsCategoryTitleOfEvent()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'category_foo']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $categoryUid,
            'categories'
        );

        self::assertContains(
            'category_foo',
            $this->fixture->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////////
    // Tests concerning the displaying of events in the VIP events view
    /////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function myVipEventsViewWithTimeFrameSetToCurrentShowsCurrentEvent()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');
        $this->fixture->setConfigurationValue('timeframeInList', 'current');
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'title' => 'currentEvent',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 20,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 20,
            ]
        );

        self::assertContains(
            'currentEvent',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsViewWithTimeFrameSetToCurrentNotShowsEventInFuture()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');
        $this->fixture->setConfigurationValue('timeframeInList', 'current');
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'title' => 'futureEvent',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 21,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertNotContains(
            'futureEvent',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsShowsStatusColumnByDefault()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function myVipEventsForStatusColumnHiddenByTsSetupHidesStatusColumn()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');
        $this->fixture->setConfigurationValue('hideColumns', 'status');

        $this->fixture->main('', []);

        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function myVipEventsForVisibleEventShowsPublishedStatus()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertContains(
            $this->fixture->translate('visibility_status_published'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsHidesRegistrationColumn()
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->fixture->main('', []);

        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_REGISTRATION')
        );
    }

    /*
     * Tests concerning getFieldHeader
     */

    /**
     * @test
     */
    public function getFieldHeaderContainsLabelOfKey()
    {
        self::assertContains(
            $this->fixture->translate('label_date'),
            $this->fixture->getFieldHeader('date')
        );
    }

    /**
     * @test
     */
    public function getFieldHeaderForSortableFieldAndSortingEnabledContainsLink()
    {
        $this->fixture->setConfigurationValue('enableSortingLinksInListView', true);

        self::assertContains(
            '<a',
            $this->fixture->getFieldHeader('date')
        );
    }

    /**
     * @test
     */
    public function getFieldHeaderForSortableFieldAndSortingDisabledNotContainsLink()
    {
        $this->fixture->setConfigurationValue('enableSortingLinksInListView', false);

        self::assertNotContains(
            '<a',
            $this->fixture->getFieldHeader('date')
        );
    }

    /**
     * @test
     */
    public function getFieldHeaderForNonSortableFieldAndSortingEnabledNotContainsLink()
    {
        $this->fixture->setConfigurationValue('enableSortingLinksInListView', true);

        self::assertNotContains(
            '<a',
            $this->fixture->getFieldHeader('register')
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning the getLoginLink function.
    ////////////////////////////////////////////////

    public function testGetLoginLinkWithLoggedOutUserAddsUidPiVarToUrl()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'foo',
            ]
        );
        $this->testingFramework->logoutFrontEndUser();

        $this->fixture->setConfigurationValue(
            'loginPID',
            $this->testingFramework->createFrontEndPage()
        );

        self::assertContains(
            rawurlencode('tx_seminars_pi1[uid]') . '=' . $eventUid,
            $this->fixture->getLoginLink(
                'foo',
                $this->testingFramework->createFrontEndPage(),
                $eventUid
            )
        );
    }

    //////////////////////////////////////////////////////
    // Tests concerning the pagination of the list view.
    //////////////////////////////////////////////////////

    public function testListViewCanContainOneItemOnTheFirstPage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        self::assertContains(
            'Event A',
            $this->fixture->main('', [])
        );
    }

    public function testListViewCanContainTwoItemsOnTheFirstPage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event B',
            ]
        );

        $output = $this->fixture->main('', []);
        self::assertContains(
            'Event A',
            $output
        );
        self::assertContains(
            'Event B',
            $output
        );
    }

    public function testFirstPageOfListViewNotContainsItemForTheSecondPage()
    {
        $this->fixture->setConfigurationValue(
            'listView.',
            [
                'orderBy' => 'title',
                'descFlag' => 0,
                'results_at_a_time' => 1,
                'maxPages' => 5,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event B',
            ]
        );

        self::assertNotContains(
            'Event B',
            $this->fixture->main('', [])
        );
    }

    public function testSecondPageOfListViewContainsItemForTheSecondPage()
    {
        $this->fixture->setConfigurationValue(
            'listView.',
            [
                'orderBy' => 'title',
                'descFlag' => 0,
                'results_at_a_time' => 1,
                'maxPages' => 5,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event B',
            ]
        );

        $this->fixture->piVars['pointer'] = 1;
        self::assertContains(
            'Event B',
            $this->fixture->main('', [])
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests concerning the attached files column in the list view
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesTrueHidesAttachedFilesHeader()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->fixture->main('', []);

        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesFalseShowsAttachedFilesHeader()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesTrueHidesAttachedFilesListRowItem()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->fixture->main('', []);

        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTITEM_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesFalseShowsAttachedFilesListRowItem()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTITEM_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserShowsAttachedFilesHeader()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserShowsAttachedFilesListRowItem()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTITEM_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesFalseShowsAttachedFile()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        self::assertContains(
            $dummyFileName,
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesFalseShowsMultipleAttachedFiles()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
        $dummyFile2 = $this->testingFramework->createDummyFile();
        $dummyFileName2 =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName . ',' . $dummyFileName2]
        );

        $output = $this->fixture->main('', []);

        self::assertContains(
            $dummyFileName,
            $output
        );
        self::assertContains(
            $dummyFileName2,
            $output
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesTrueAndUserNotAttendeeHidesAttachedFile()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        self::assertNotContains(
            $dummyFileName,
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesTrueAndUserAttendeeShowsAttachedFile()
    {
        $this->fixture->setConfigurationValue('hideColumns', '');
        $this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        self::assertContains(
            $dummyFileName,
            $this->fixture->main('', [])
        );
    }

    public function testListViewEnsuresPlacePiVarArray()
    {
        $this->fixture->piVars['place'] = ['foo'];
        $this->fixture->main('', []);

        self::assertEmpty(
            $this->fixture->piVars['place']
        );
    }

    public function testListViewEnsuresOrganizerPiVarArray()
    {
        $this->fixture->piVars['organizer'] = ['foo'];
        $this->fixture->main('', []);

        self::assertEmpty(
            $this->fixture->piVars['organizer']
        );
    }

    public function testListViewEnsuresEventTypePiVarArray()
    {
        $this->fixture->piVars['event_type'] = ['foo'];
        $this->fixture->main('', []);

        self::assertEmpty(
            $this->fixture->piVars['event_type']
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning the owner data in the single view
    ///////////////////////////////////////////////////////

    public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledContainsOwnerDataHeading()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            $this->fixture->translate('label_owner'),
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledNotContainsEmptyLines()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotRegExp(
            '/(<p>|<br \\/>)\\s*<br \\/>\\s*(<br \\/>|<\\/p>)/m',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithoutOwnerAndOwnerDataEnabledNotContainsOwnerDataHeading()
    {
        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            $this->fixture->translate('label_owner'),
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithOwnerAndOwnerDataDisabledNotContainsOwnerDataHeading()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            0
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            $this->fixture->translate('label_owner'),
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledContainsOwnerName()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'John Doe']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'John Doe',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithOwnerHtmlSpecialCharsOwnerName()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'Tom & Jerry']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'Tom &amp; Jerry',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithOwnerAndOwnerDataDisabledNotContainsOwnerName()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'Jon Doe']
        );

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            0
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            'Jon Doe',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledCanContainOwnerPhone()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser(
            '',
            ['telephone' => '0123 4567']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            '0123 4567',
            $this->fixture->main('', [])
        );
    }

    public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledCanContainOwnerEMailAddress()
    {
        $ownerUid = $this->testingFramework->createFrontEndUser(
            '',
            ['email' => 'foo@bar.com']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->fixture->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            'foo@bar.com',
            $this->fixture->main('', [])
        );
    }

    //////////////////////////////////////////////////////////////
    // Tests concerning the registration link in the single view
    //////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForEventWithUnlimitedVacanciesShowsRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithNoVacanciesAndQueueShowsRegisterOnQueueLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
                'queue_size' => 1,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            sprintf(
                $this->fixture->translate('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithNoVacanciesAndNoQueueDoesNotShowRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
                'queue_size' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            sprintf(
                $this->fixture->translate('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithVacanciesAndNoDateShowsPrebookNowString()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
                'queue_size' => 0,
                'begin_date' => '',
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            $this->fixture->translate('label_onlinePrebooking'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithRegistrationBeginInFutureDoesNotShowRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => $GLOBALS['SIM_EXEC_TIME'] + 40,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertNotContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithRegistrationBeginInFutureShowsRegistrationOpensOnMessage()
    {
        $registrationBegin = $GLOBALS['SIM_EXEC_TIME'] + 40;
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => $registrationBegin,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            sprintf(
                $this->fixture->translate('message_registrationOpensOn'),
                strftime('%d.%m.%Y %H:%M', $registrationBegin)
            ),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithRegistrationBeginInPastShowsRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => $GLOBALS['SIM_EXEC_TIME'] - 42,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithoutRegistrationBeginShowsRegistrationLink()
    {
        $this->fixture->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'begin_date_registration' => 0,
            ]
        );

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        self::assertContains(
            $this->fixture->translate('label_onlineRegistration'),
            $this->fixture->main('', [])
        );
    }

    ///////////////////////////////////////////
    // Tests concerning the registration form
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function registrationFormHtmlspecialcharsEventTitle()
    {
        $registrationFormMock = $this->getMock(Tx_Seminars_FrontEnd_RegistrationForm::class, [], [], '', false);
        GeneralUtility::addInstance(Tx_Seminars_FrontEnd_RegistrationForm::class, $registrationFormMock);

        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'title' => 'foo & bar',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'needs_registration' => 1,
                'attendees_max' => 10,
            ]
        );

        $this->fixture->piVars['seminar'] = $eventUid;

        self::assertContains(
            'foo &amp; bar',
            $this->fixture->main('', [])
        );
    }

    public function testRegistrationFormForEventWithOneNotFullfilledRequirementIsHidden()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');

        $requiredTopic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'attendees_max' => 10,
                'topic' => $topic,
            ]
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topic,
            $requiredTopic,
            'requirements'
        );
        $this->fixture->piVars['seminar'] = $date;

        self::assertNotContains(
            $this->fixture->translate('label_your_user_data'),
            $this->fixture->main('', [])
        );
    }

    public function testListOfRequirementsForEventWithOneNotFulfilledRequirementListIsShown()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');

        $requiredTopic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'attendees_max' => 10,
                'topic' => $topic,
            ]
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topic,
            $requiredTopic,
            'requirements'
        );
        $this->fixture->piVars['seminar'] = $date;
        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
        );
    }

    /**
     * @test
     */
    public function listOfRequirementsForEventWithOneNotFulfilledRequirementLinksHtmlspecialcharedTitleOfRequirement()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');
        $this->fixture->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );

        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'attendees_max' => 10,
                'topic' => $topic,
                'needs_registration' => 1,
            ]
        );

        $requiredTopic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required & foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topic,
            $requiredTopic,
            'requirements'
        );
        $this->fixture->piVars['seminar'] = $date;

        self::assertRegExp(
            '/<a href=.*' . $requiredTopic . '.*>required &amp; foo<\\/a>/',
            $this->fixture->main('', [])
        );
    }

    public function testListOfRequirementsForEventWithTwoNotFulfilledRequirementsShownsTitlesOfBothRequirements()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');

        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'attendees_max' => 10,
                'topic' => $topic,
                'needs_registration' => 1,
            ]
        );

        $requiredTopic1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topic,
            $requiredTopic1,
            'requirements'
        );
        $requiredTopic2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_bar',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topic,
            $requiredTopic2,
            'requirements'
        );

        $this->fixture->piVars['seminar'] = $date;

        self::assertRegExp(
            '/required_foo.*required_bar/s',
            $this->fixture->main('', [])
        );
    }

    /*
     * Tests concerning getVacanciesClasses
     */

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithEnoughVacanciesReturnsAvailableClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(0);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            'tx-seminars-pi1-vacancies-available',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithOneVacancyReturnsVacancyOneClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(9);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            'tx-seminars-pi1-vacancies-1',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithTwoVacanciesReturnsVacancyTwoClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(8);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            'tx-seminars-pi1-vacancies-2',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithNoVacanciesReturnsVacancyZeroClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(10);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            'tx-seminars-pi1-vacancies-0',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithUnlimitedVacanciesReturnsVacanciesAvailableClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setUnlimitedVacancies();
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            'tx-seminars-pi1-vacancies-available',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithUnlimitedVacanciesDoesNotReturnZeroVacancyClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setUnlimitedVacancies();
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertNotContains(
            'tx-seminars-pi1-vacancies-0',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithUnlimitedVacanciesReturnsVacanciesUnlimitedClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setUnlimitedVacancies();
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            'tx-seminars-pi1-vacancies-unlimited',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForRegistrationDeadlineInPastReturnsDeadlineOverClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setNeedsRegistration(true);
        $event->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] - 45);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);

        self::assertContains(
            'tx-seminars-pi1-registration-deadline-over',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForBeginDateInPastReturnsBeginDateOverClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 45);

        self::assertContains(
            'tx-seminars-pi1-event-begin-date-over',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForBeginDateInPastAndRegistrationForStartedEventsAllowedReturnsVacanciesAvailableClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 45);
        $this->fixture->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForStartedEvents',
            1
        );

        self::assertContains(
            'tx-seminars-pi1-vacancies-available',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithNoVacanciesAndRegistrationQueueReturnsRegistrationQueueClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(10);
        $event->setNeedsRegistration(true);
        $event->setRegistrationQueue(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            'tx-seminars-pi1-has-registration-queue',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithNoVacanciesAndNoRegistrationQueueDoesNotReturnRegistrationQueueClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid, []);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(10);
        $event->setNeedsRegistration(true);
        $event->setRegistrationQueue(false);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertNotContains(
            'tx-seminars-pi1-has-registration-queue',
            $this->fixture->getVacanciesClasses($event)
        );
    }

    //////////////////////////////////////////////////////////////////////////
    // Tests concerning getVacanciesClasses for events without date and with
    // configuration variable 'allowRegistrationForEventsWithoutDate' TRUE.
    //////////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithEnoughVacanciesReturnsAvailableClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(0);
        $this->fixture->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForEventsWithoutDate',
            1
        );

        $output = $this->fixture->getVacanciesClasses($event);

        self::assertContains(
            $this->fixture->pi_getClassName('vacancies-available'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithOneVacancyReturnsVacancyOneClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(9);
        $this->fixture->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForEventsWithoutDate',
            1
        );

        $output = $this->fixture->getVacanciesClasses($event);

        self::assertContains(
            $this->fixture->pi_getClassName('vacancies-1'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithTwoVacanciesReturnsVacancyTwoClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(8);
        $this->fixture->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForEventsWithoutDate',
            1
        );

        $output = $this->fixture->getVacanciesClasses($event);

        self::assertContains(
            $this->fixture->pi_getClassName('vacancies-2'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithNoVacanciesReturnsVacancyZeroClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(10);
        $this->fixture->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForEventsWithoutDate',
            1
        );

        $output = $this->fixture->getVacanciesClasses($event);

        self::assertContains(
            $this->fixture->pi_getClassName('vacancies-0'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithUnlimitedVacanciesReturnsAvailableClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setUnlimitedVacancies();
        $event->setNumberOfAttendances(0);
        $this->fixture->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForEventsWithoutDate',
            1
        );

        $output = $this->fixture->getVacanciesClasses($event);

        self::assertContains(
            $this->fixture->pi_getClassName('vacancies-available'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithUnlimitedVacanciesDoesNotReturnDeadlineOverClass()
    {
        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($this->seminarUid);
        $event->setUnlimitedVacancies();
        $event->setNumberOfAttendances(0);
        $this->fixture->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForEventsWithoutDate',
            1
        );

        $output = $this->fixture->getVacanciesClasses($event);

        self::assertNotContains(
            $this->fixture->pi_getClassName('registration-deadline-over'),
            $output
        );
    }

    ////////////////////////////////////////////
    // Tests concerning my_entered_events view
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function myEnteredEventViewShowsHiddenRecords()
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->fixture->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->fixture->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );

        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
            $editorGroupUid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'owner_feuser' => $feUserUid,
                'hidden' => 1,
                'title' => 'hiddenEvent',
            ]
        );

        self::assertContains(
            'hiddenEvent',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewShowsStatusColumnByDefault()
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->fixture->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->fixture->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );

        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
            $editorGroupUid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'owner_feuser' => $feUserUid,
                'hidden' => 1,
                'title' => 'hiddenEvent',
            ]
        );

        $this->fixture->main('', []);

        self::assertTrue(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewForHiddenEventShowsStatusPendingLabel()
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->fixture->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->fixture->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );
        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
            $editorGroupUid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'owner_feuser' => $feUserUid,
                'hidden' => 1,
            ]
        );

        self::assertContains(
            $this->fixture->translate('visibility_status_pending'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewForVisibleEventShowsStatusPublishedLabel()
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->fixture->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->fixture->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );
        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
            $editorGroupUid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'owner_feuser' => $feUserUid,
            ]
        );

        self::assertContains(
            $this->fixture->translate('visibility_status_published'),
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewForTimeFrameSetToCurrentShowsEventEndedInPast()
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->fixture->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->fixture->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );
        $this->fixture->setConfigurationValue('timeframeInList', 'current');

        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
            $editorGroupUid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'owner_feuser' => $feUserUid,
                'hidden' => 1,
                'title' => 'pastEvent',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 30,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - 20,
            ]
        );

        self::assertContains(
            'pastEvent',
            $this->fixture->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventsViewHidesRegistrationColumn()
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->fixture->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->fixture->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );

        $this->testingFramework->createAndLoginFrontEndUser($editorGroupUid);

        $this->fixture->main('', []);

        self::assertFalse(
            $this->fixture->isSubpartVisible('LISTHEADER_WRAPPER_REGISTRATION')
        );
    }

    ////////////////////////////////////////////////////
    // Tests concerning mayCurrentUserEditCurrentEvent
    ////////////////////////////////////////////////////

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserAsOwnerIsTrue()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var \Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isUserVip', 'isOwnerFeUser'], [], '', false);
        $event->expects(self::any())->method('isUserVip')
            ->will(self::returnValue(false));
        $event->expects(self::any())->method('isOwnerFeUser')
            ->will(self::returnValue(true));
        $fixture->setSeminar($event);

        self::assertTrue(
            $fixture->mayCurrentUserEditCurrentEvent()
        );
    }

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserAsVipAndVipEditorAccessIsTrue()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var \Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = ['mayManagersEditTheirEvents' => true];
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isUserVip', 'isOwnerFeUser'], [], '', false);
        $event->expects(self::any())->method('isUserVip')
            ->will(self::returnValue(true));
        $event->expects(self::any())->method('isOwnerFeUser')
            ->will(self::returnValue(false));
        $fixture->setSeminar($event);

        self::assertTrue(
            $fixture->mayCurrentUserEditCurrentEvent()
        );
    }

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserAsVipAndNoVipEditorAccessIsFalse()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var \Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = ['mayManagersEditTheirEvents' => false];
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isUserVip', 'isOwnerFeUser'], [], '', false);
        $event->expects(self::any())->method('isUserVip')
            ->will(self::returnValue(true));
        $event->expects(self::any())->method('isOwnerFeUser')
            ->will(self::returnValue(false));
        $fixture->setSeminar($event);

        self::assertFalse(
            $fixture->mayCurrentUserEditCurrentEvent()
        );
    }

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserNeitherVipNorOwnerIsFalse()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var \Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [
            'eventEditorPID' => 42,
            'mayManagersEditTheirEvents' => true,
        ];
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isUserVip', 'isOwnerFeUser'], [], '', false);
        $event->expects(self::any())->method('getUid')
            ->will(self::returnValue(91));
        $event->expects(self::any())->method('isUserVip')
            ->will(self::returnValue(false));
        $event->expects(self::any())->method('isOwnerFeUser')
            ->will(self::returnValue(false));
        $fixture->setSeminar($event);

        self::assertFalse(
            $fixture->mayCurrentUserEditCurrentEvent()
        );
    }

    /*
     * Tests concerning the "edit", "hide", "unhide" and "copy" links
     */

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessDeniedReturnsEmptyString()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = ['eventEditorPID' => 42];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(false));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $fixture->setSeminar($event);

        self::assertEquals(
            '',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedCreatesLinkToEditPageWithSeminarUid()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [
            'eventEditorPID' => 42,
        ];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $fixture->setSeminar($event);

        self::assertContains(
            '<a href="index.php?id=42&amp;tx_seminars_pi1[seminar]=91">' .
                $fixture->translate('label_edit') . '</a>',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndPublishedVisibleEventCreatesHideLinkToCurrentPageWithSeminarUid()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $event->expects(self::any())->method('isPublished')->will(self::returnValue(true));
        $event->expects(self::any())->method('isHidden')->will(self::returnValue(false));
        $fixture->setSeminar($event);

        $currentPageId = $GLOBALS['TSFE']->id;

        self::assertContains(
            '<a href="index.php?id=' . $currentPageId .
                '" data-method="post" data-post-tx_seminars_pi1-action="hide" data-post-tx_seminars_pi1-seminar="91">' .
                $fixture->translate('label_hide') . '</a>',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndPublishedHiddenEventCreatesUnhideLinkToCurrentPageWithSeminarUid()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $event->expects(self::any())->method('isPublished')->will(self::returnValue(true));
        $event->expects(self::any())->method('isHidden')->will(self::returnValue(true));
        $fixture->setSeminar($event);

        $currentPageId = $GLOBALS['TSFE']->id;

        self::assertContains(
            '<a href="index.php?id=' . $currentPageId .
                '" data-method="post" data-post-tx_seminars_pi1-action="unhide" data-post-tx_seminars_pi1-seminar="91">' .
                $fixture->translate('label_unhide') . '</a>',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedVisibleEventNotCreatesHideLink()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $event->expects(self::any())->method('isPublished')->will(self::returnValue(false));
        $event->expects(self::any())->method('isHidden')->will(self::returnValue(false));
        $fixture->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=hide',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedHiddenEventNotCreatesUnhideLink()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $event->expects(self::any())->method('isPublished')->will(self::returnValue(false));
        $event->expects(self::any())->method('isHidden')->will(self::returnValue(true));
        $fixture->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=unhide',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedHiddenEventNotCreatesCopyLink()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $event->expects(self::any())->method('isPublished')->will(self::returnValue(false));
        $event->expects(self::any())->method('isHidden')->will(self::returnValue(true));
        $fixture->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=copy',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedVisibleEventNotCreatesCopyLink()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $event->expects(self::any())->method('isPublished')->will(self::returnValue(false));
        $event->expects(self::any())->method('isHidden')->will(self::returnValue(false));
        $fixture->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=copy',
            $fixture->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndPublishedHiddenEventCreatesCopyLinkToCurrentPageWithSeminarUid()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['mayCurrentUserEditCurrentEvent']
        );
        $fixture->cObj = $this->createContentMock();
        $fixture->conf = [];
        $fixture->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->will(self::returnValue(true));

        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, ['getUid', 'isPublished', 'isHidden'], [], '', false);
        $event->expects(self::any())->method('getUid')->will(self::returnValue(91));
        $event->expects(self::any())->method('isPublished')->will(self::returnValue(true));
        $event->expects(self::any())->method('isHidden')->will(self::returnValue(true));
        $fixture->setSeminar($event);

        $currentPageId = $GLOBALS['TSFE']->id;

        self::assertContains(
            '<a href="index.php?id=' . $currentPageId .
            '" data-method="post" data-post-tx_seminars_pi1-action="copy" data-post-tx_seminars_pi1-seminar="91">' .
            $fixture->translate('label_copy') . '</a>',
            $fixture->createAllEditorLinks()
        );
    }

    /*
     * Tests concerning the hide/unhide and copy functionality
     */

    /**
     * @test
     */
    public function eventsListNotCallsProcessEventEditorActions()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(Tx_Seminars_FrontEnd_DefaultController::class, ['processEventEditorActions']);
        $fixture->expects(self::never())->method('processEventEditorActions');

        $fixture->main(
            '',
            ['what_to_display' => 'seminar_list']
        );
    }

    /**
     * @test
     */
    public function myEnteredEventsListCallsProcessEventEditorActions()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(Tx_Seminars_FrontEnd_DefaultController::class, ['processEventEditorActions']);
        $fixture->expects(self::once())->method('processEventEditorActions');

        $fixture->main(
            '',
            ['what_to_display' => 'my_entered_events']
        );
    }

    /**
     * @test
     */
    public function myManagedEventsListCallsProcessEventEditorActions()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(Tx_Seminars_FrontEnd_DefaultController::class, ['processEventEditorActions']);
        $fixture->expects(self::once())->method('processEventEditorActions');

        $fixture->main(
            '',
            ['what_to_display' => 'my_vip_events']
        );
    }

    /**
     * @test
     */
    public function processEventEditorActionsIntvalsSeminarPivar()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['ensureIntegerPiVars', 'createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('ensureIntegerPiVars')
            ->with(['seminar']);

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithZeroSeminarPivarNotCreatesEventEditor()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::never())->method('createEventEditorInstance');

        $fixture->piVars['seminar'] = 0;
        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithNegativeSeminarPivarNotCreatesEventEditor()
    {
        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::never())->method('createEventEditorInstance');

        $fixture->piVars['seminar'] = -1;
        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithPositiveSeminarPivarCreatesEventEditor()
    {
        Tx_Oelib_MapperRegistry::denyDatabaseAccess();

        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::once())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));

        $fixture->piVars['seminar'] = 1;
        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithUidOfExistingEventChecksPermissions()
    {
        Tx_Oelib_MapperRegistry::denyDatabaseAccess();

        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::once())->method('hasAccessMessage');

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));

        $fixture->piVars['seminar'] = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Event::class)->getNewGhost()->getUid();

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForHideActionWithAccessGrantedCallsHideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::once())->method('hideEvent')->with($event);

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'hide';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForHideActionWithUnpublishedEventAndAccessGrantedNotCallsHideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(['publication_hash' => 'foo']);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('hideEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'hide';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForHideActionWithAccessDeniedNotCallsHideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->will(self::returnValue('access denied'));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('hideEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'hide';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForUnhideActionWithAccessGrantedCallsUnhideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::once())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::once())->method('unhideEvent')->with($event);

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'unhide';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForUnhideActionWithUnpublishedEventAccessGrantedNotCallsUnhideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(['publication_hash' => 'foo']);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::once())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('unhideEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'unhide';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForUnhideActionWithAccessDeniedNotCallsUnhideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->will(self::returnValue('access denied'));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::once())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('unhideEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'unhide';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForCopyActionWithAccessGrantedCallsCopyEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent', 'copyEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::once())->method('copyEvent')->with($event);

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'copy';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForCopyActionWithUnpublishedEventAndAccessGrantedNotCallsCopyEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(['publication_hash' => 'foo']);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent', 'copyEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('copyEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'copy';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForCopyActionWithAccessDeniedNotCallsCopyEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->will(self::returnValue('access denied'));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent', 'copyEvent']
        );
        $fixture->expects(self::atLeastOnce())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('copyEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'copy';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForEmptyActionWithPublishedEventAndAccessGrantedNotCallsHideEventOrUnhideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::once())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('hideEvent');
        $fixture->expects(self::never())->method('unhideEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = '';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForInvalidActionWithPublishedEventAndAccessGrantedNotCallsHideEventOrUnhideEvent()
    {
        /** @var Tx_Seminars_FrontEnd_EventEditor|PHPUnit_Framework_MockObject_MockObject $eventEditor */
        $eventEditor = $this->getMock(Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage'], [], '', false);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->will(self::returnValue(''));

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var Tx_Seminars_FrontEnd_DefaultController|PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            $this->createAccessibleProxyClass(),
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $fixture->expects(self::once())->method('createEventEditorInstance')->will(self::returnValue($eventEditor));
        $fixture->expects(self::never())->method('hideEvent');
        $fixture->expects(self::never())->method('unhideEvent');

        $fixture->piVars['seminar'] = $event->getUid();
        $fixture->piVars['action'] = 'foo';

        $fixture->processEventEditorActions();
    }

    /**
     * @test
     */
    public function hideEventMarksVisibleEventAsHidden()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel([]);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->hideEvent($event);

        self::assertTrue(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function hideEventKeepsHiddenEventAsHidden()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel(['hidden' => 1]);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->hideEvent($event);

        self::assertTrue(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function hideEventSavesEvent()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel([]);
        $mapper->expects(self::once())->method('save')->with($event);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->hideEvent($event);
    }

    /**
     * @test
     */
    public function hideEventRedirectsToRequestUrl()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel([]);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->hideEvent($event);

        $currentUrl = GeneralUtility::locationHeaderUrl(GeneralUtility::getIndpEnv('REQUEST_URI'));
        self::assertSame(
            'Location: ' . $currentUrl,
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function unhideEventMarksHiddenEventAsVisible()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel(['hidden' => 1]);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->unhideEvent($event);

        self::assertFalse(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function unhideEventKeepsVisibleEventAsVisible()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel([]);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->unhideEvent($event);

        self::assertFalse(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function unhideEventSavesEvent()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel([]);
        $mapper->expects(self::once())->method('save')->with($event);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->unhideEvent($event);
    }

    /**
     * @test
     */
    public function unhideEventRedirectsToRequestUrl()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel([]);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->unhideEvent($event);

        $currentUrl = GeneralUtility::locationHeaderUrl(GeneralUtility::getIndpEnv('REQUEST_URI'));
        self::assertSame(
            'Location: ' . $currentUrl,
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function copySavesHiddenCloneOfEvent()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel(['title' => 'TDD for starters']);

        $hiddenClone = clone $event;
        $hiddenClone->markAsHidden();
        $mapper->expects(self::once())->method('save')->with($hiddenClone);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->copyEvent($event);
    }

    /**
     * @test
     */
    public function copyRemovesRegistrationsFromEvent()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel(['title' => 'TDD for starters']);
        $registrations = new Tx_Oelib_List();
        $registrations->add(new Tx_Seminars_Model_Registration());
        $event->setRegistrations($registrations);

        $hiddenClone = clone $event;
        $hiddenClone->markAsHidden();
        $hiddenClone->setRegistrations(new Tx_Oelib_List());
        $mapper->expects(self::once())->method('save')->with($hiddenClone);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->copyEvent($event);
    }

    /**
     * @test
     */
    public function copyEventRedirectsToRequestUrl()
    {
        /** @var Tx_Seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMock(Tx_Seminars_Mapper_Event::class, ['save']);
        Tx_Oelib_MapperRegistry::set(Tx_Seminars_Mapper_Event::class, $mapper);

        /** @var Tx_Seminars_Model_Event $event */
        $event = $mapper->getLoadedTestingModel([]);

        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_FrontEnd_DefaultController $fixture */
        $fixture = new $className();
        $fixture->copyEvent($event);

        $currentUrl = GeneralUtility::locationHeaderUrl(GeneralUtility::getIndpEnv('REQUEST_URI'));
        self::assertSame(
            'Location: ' . $currentUrl,
            $this->headerCollector->getLastAddedHeader()
        );
    }

    //////////////////////////////////
    // Tests concerning initListView
    //////////////////////////////////

    /**
     * @test
     */
    public function initListViewForDefaultListLimitsListByAdditionalParameters()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            ['limitForAdditionalParameters']
        );
        $fixture->expects(self::once())->method('limitForAdditionalParameters');

        $fixture->initListView('');
    }

    /**
     * @test
     */
    public function initListViewForTopicListLimitsListByAdditionalParameters()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            ['limitForAdditionalParameters']
        );
        $fixture->expects(self::once())->method('limitForAdditionalParameters');

        $fixture->initListView('topic_list');
    }

    /**
     * @test
     */
    public function initListViewForMyEventsListNotLimitsListByAdditionalParameters()
    {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            ['limitForAdditionalParameters']
        );
        $fixture->expects(self::never())->method('limitForAdditionalParameters');

        $fixture->initListView('my_events');
    }

    ////////////////////////////////////////////////////////////
    // Tests concerning hideListRegistrationsColumnIfNecessary
    ////////////////////////////////////////////////////////////

    /**
     * Data provider for the tests concerning
     * hideListRegistrationsColumnIfNecessary.
     *
     * @return array[] nested array with the following inner keys:
     *               [getsHidden] boolean: whether the registration column is
     *                            expected to be hidden
     *               [whatToDisplay] string: the value for what_to_display
     *               [listPid] integer: the PID of the registration list page
     *               [vipListPid] integer: the PID of the VIP registration list
     *                            page
     */
    public function hideListRegistrationsColumnIfNecessaryDataProvider()
    {
        return [
            'notHiddenForListForBothPidsSet' => [
                'getsHidden' => false,
                'whatToDisplay' => 'seminar_list',
                'listPid' => 1,
                'vipListPid' => 1,
            ],
            'hiddenForListForNoPidsSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'seminar_list',
                'listPid' => 0,
                'vipListPid' => 0,
            ],
            'notHiddenForListForListPidSet' => [
                'getsHidden' => false,
                'whatToDisplay' => 'seminar_list',
                'listPid' => 1,
                'vipListPid' => 0,
            ],
            'notHiddenForListForVipListPidSet' => [
                'getsHidden' => false,
                'whatToDisplay' => 'seminar_list',
                'listPid' => 0,
                'vipListPid' => 1,
            ],
            'hiddenForOtherDatesForAndBothPidsSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'other_dates',
                'listPid' => 1,
                'vipListPid' => 1,
            ],
            'hiddenForEventsNextDayForBothPidsSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'events_next_day',
                'listPid' => 1,
                'vipListPid' => 1,
            ],
            'notHiddenForMyEventsForBothPidsSet' => [
                'getsHidden' => false,
                'whatToDisplay' => 'my_events',
                'listPid' => 1,
                'vipListPid' => 1,
            ],
            'hiddenForMyEventsForNoPidsSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'my_events',
                'listPid' => 0,
                'vipListPid' => 0,
            ],
            'notHiddenForMyEventsForListPidSet' => [
                'getsHidden' => false,
                'whatToDisplay' => 'my_events',
                'listPid' => 1,
                'vipListPid' => 0,
            ],
            'hiddenForMyEventsForVipListPidSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'my_events',
                'listPid' => 0,
                'vipListPid' => 1,
            ],
            'notHiddenForMyVipEventsForBothPidsSet' => [
                'getsHidden' => false,
                'whatToDisplay' => 'my_vip_events',
                'listPid' => 1,
                'vipListPid' => 1,
            ],
            'hiddenForMyVipEventsForNoPidsSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'my_vip_events',
                'listPid' => 0,
                'vipListPid' => 0,
            ],
            'hiddenForMyVipEventsForListPidSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'my_vip_events',
                'listPid' => 1,
                'vipListPid' => 0,
            ],
            'notHiddenForMyVipEventsForVipListPidSet' => [
                'getsHidden' => false,
                'whatToDisplay' => 'my_vip_events',
                'listPid' => 0,
                'vipListPid' => 1,
            ],
            'hiddenForTopicListForBothPidsSet' => [
                'getsHidden' => true,
                'whatToDisplay' => 'topic_list',
                'listPid' => 1,
                'vipListPid' => 1,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider hideListRegistrationsColumnIfNecessaryDataProvider
     *
     * @param bool $getsHidden
     * @param string $whatToDisplay
     * @param int $listPid
     * @param int $vipListPid
     */
    public function hideListRegistrationsColumnIfNecessaryWithRegistrationEnabledAndLoggedIn(
        $getsHidden,
        $whatToDisplay,
        $listPid,
        $vipListPid
    ) {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            ['isRegistrationEnabled', 'isLoggedIn', 'hideColumns']
        );
        $fixture->expects(self::any())->method('isRegistrationEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('isLoggedIn')
            ->will(self::returnValue(true));

        if ($getsHidden) {
            $fixture->expects(self::once())->method('hideColumns')
                ->with(['list_registrations']);
        } else {
            $fixture->expects(self::never())->method('hideColumns');
        }

        $fixture->init([
            'registrationsListPID' => $listPid,
            'registrationsVipListPID' => $vipListPid,
        ]);

        $fixture->hideListRegistrationsColumnIfNecessary($whatToDisplay);
    }

    /**
     * @test
     *
     * @dataProvider hideListRegistrationsColumnIfNecessaryDataProvider
     *
     * @param bool $getsHidden (ignored)
     * @param string $whatToDisplay
     * @param int $listPid
     * @param int $vipListPid
     */
    public function hideListRegistrationsColumnIfNecessaryWithRegistrationEnabledAndNotLoggedInAlwaysHidesColumn(
        $getsHidden,
        $whatToDisplay,
        $listPid,
        $vipListPid
    ) {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            ['isRegistrationEnabled', 'isLoggedIn', 'hideColumns']
        );
        $fixture->expects(self::any())->method('isRegistrationEnabled')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('isLoggedIn')
            ->will(self::returnValue(false));

        $fixture->expects(self::once())->method('hideColumns')
            ->with(['list_registrations']);

        $fixture->init([
            'registrationsListPID' => $listPid,
            'registrationsVipListPID' => $vipListPid,
        ]);

        $fixture->hideListRegistrationsColumnIfNecessary($whatToDisplay);
    }

    /**
     * @test
     *
     * @dataProvider hideListRegistrationsColumnIfNecessaryDataProvider
     *
     * @param bool $getsHidden (ignored)
     * @param string $whatToDisplay
     * @param int $listPid
     * @param int $vipListPid
     */
    public function hideListRegistrationsColumnIfNecessaryWithRegistrationDisabledAndLoggedInAlwaysHidesColumn(
        $getsHidden,
        $whatToDisplay,
        $listPid,
        $vipListPid
    ) {
        /** @var \Tx_Seminars_FrontEnd_DefaultController|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_FrontEnd_DefaultController::class,
            ['isRegistrationEnabled', 'isLoggedIn', 'hideColumns']
        );
        $fixture->expects(self::any())->method('isRegistrationEnabled')
            ->will(self::returnValue(false));
        $fixture->expects(self::any())->method('isLoggedIn')
            ->will(self::returnValue(true));

        $fixture->expects(self::once())->method('hideColumns')
            ->with(['list_registrations']);

        $fixture->init([
            'registrationsListPID' => $listPid,
            'registrationsVipListPID' => $vipListPid,
        ]);

        $fixture->hideListRegistrationsColumnIfNecessary($whatToDisplay);
    }

    ///////////////////////////////////////////////////
    // Tests concerning the hooks for the event lists
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function eventsListCallsModifyListRowHook()
    {
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->find($this->seminarUid);

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventListView::class);
        $hook->expects(self::once())->method('modifyListRow')->with($event, self::anything());
        // We don't test for the second parameter (the template instance here)
        // because we cannot access it from the outside.

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['listView'][$hookClass] = $hookClass;

        $this->fixture->main('', []);
    }

    /**
     * @test
     *
     * @expectedException \UnexpectedValueException
     */
    public function eventsListForModifyListRowHookWithoutInterfaceThrowsException()
    {
        $hookClass = uniqid('myEventsListRowHook');
        $hook = $this->getMock($hookClass);

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['listView'][$hookClass] = $hookClass;

        $this->fixture->main('', []);
    }

    /**
     * @test
     */
    public function myEventsListCallsModifyMyEventsListRowHook()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'my_events');

        $registrationUid = $this->createLogInAndRegisterFeUser();
        /** @var Tx_Seminars_Model_Registration $registration */
        $registration = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Registration::class)->find($registrationUid);

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventListView::class);
        $hook->expects(self::once())->method('modifyMyEventsListRow')->with($registration, self::anything());
        // We don't test for the second parameter (the template instance here)
        // because we cannot access it from the outside.

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['listView'][$hookClass] = $hookClass;

        $this->fixture->main('', []);
    }

    /**
     * @test
     */
    public function myEventsListCallsModifyListRowHook()
    {
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->find($this->seminarUid);

        $this->testingFramework->createAndLoginFrontEndUser();

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventListView::class);
        $hook->expects(self::once())->method('modifyListRow')->with($event, self::anything());
        // We don't test for the second parameter (the template instance here)
        // because we cannot access it from the outside.

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['listView'][$hookClass] = $hookClass;

        $this->fixture->main('', []);
    }

    /**
     * @test
     */
    public function eventListNotCallsModifyMyEventsListRowHook()
    {
        $hook = $this->getMock(Tx_Seminars_Interface_Hook_EventListView::class);
        $hook->expects(self::never())->method('modifyMyEventsListRow');

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['listView'][$hookClass] = $hookClass;

        $this->fixture->main('', []);
    }

    /**
     * @test
     *
     * @expectedException \UnexpectedValueException
     */
    public function myEventsListForModifyMyEventsListRowHookWithoutInterfaceThrowsException()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'my_events');

        $this->createLogInAndRegisterFeUser();

        $hookClass = uniqid('myEventsListRowHook');
        $hook = $this->getMock($hookClass);

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['listView'][$hookClass] = $hookClass;

        $this->fixture->main('', []);
    }

    /*
     * Tests concerning createSingleViewLink
     */

    /**
     * @test
     */
    public function createSingleViewLinkCreatesLinkToSingleViewPage()
    {
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertContains(
            'href="index.php?id=42&amp;tx_seminars_pi1%5BshowUid%5D=1337"',
            $this->fixture->createSingleViewLink($event, 'foo')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithoutDescriptionWithAlwaysLinkSettingLinkUsesLinkText()
    {
        $this->fixture->setConfigurationValue('linkToSingleView', 'always');
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(
            ['description' => '']
        );

        self::assertContains(
            '>foo</a>',
            $this->fixture->createSingleViewLink($event, 'foo')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithDescriptionWithAlwaysLinkSettingLinkUsesLinkText()
    {
        $this->fixture->setConfigurationValue('linkToSingleView', 'always');
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(
            ['description' => 'Hello world!']
        );

        self::assertContains(
            '>foo</a>',
            $this->fixture->createSingleViewLink($event, 'foo')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithoutDescriptionWithNeverLinkSettingReturnsOnlyLabel()
    {
        $this->fixture->setConfigurationValue('linkToSingleView', 'never');
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(
            ['description' => '']
        );

        self::assertSame(
            'foo &amp; bar',
            $this->fixture->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithDescriptionWithConditionalLinkSettingLinkUsesLinkText()
    {
        $this->fixture->setConfigurationValue('linkToSingleView', 'onlyForNonEmptyDescription');
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(
            ['description' => 'Hello world!']
        );

        self::assertContains(
            '>foo &amp; bar</a>',
            $this->fixture->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithoutDescriptionWithConditionalLinkSettingReturnsOnlyLabel()
    {
        $this->fixture->setConfigurationValue('linkToSingleView', 'onlyForNonEmptyDescription');
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(
            ['description' => '']
        );

        self::assertSame(
            'foo &amp; bar',
            $this->fixture->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithDescriptionWithNeverLinkSettingReturnsOnlyLabel()
    {
        $this->fixture->setConfigurationValue('linkToSingleView', 'never');
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(
            ['description' => 'Hello world!']
        );

        self::assertSame(
            'foo &amp; bar',
            $this->fixture->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewLinkByDefaultHtmlSpecialCharsLinkText()
    {
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertContains(
            'Chaos &amp; Confusion',
            $this->fixture->createSingleViewLink($event, 'Chaos & Confusion')
        );
    }

    /**
     * @test
     */
    public function createSingleViewLinkByWithHtmlSpecialCharsTrueHtmlSpecialCharsLinkText()
    {
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertContains(
            'Chaos &amp; Confusion',
            $this->fixture->createSingleViewLink($event, 'Chaos & Confusion', true)
        );
    }

    /**
     * @test
     */
    public function createSingleViewLinkByWithHtmlSpecialCharsFalseNotHtmlSpecialCharsLinkText()
    {
        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertContains(
            'Chaos & Confusion',
            $this->fixture->createSingleViewLink($event, 'Chaos & Confusion', false)
        );
    }

    /*
     * Tests concerning the price in the single view
     */

    /**
     * @test
     */
    public function singleViewForNoStandardPriceDisplaysForFree()
    {
        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $result = $this->fixture->main('', []);

        self::assertContains($this->translator->translate('message_forFree'), $result);
    }

    /**
     * @test
     */
    public function singleViewForPriceOnRequestDisplaysOnRequest()
    {
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->seminarUid, ['price_on_request' => 1]);

        $this->fixture->setConfigurationValue('what_to_display', 'single_view');
        $this->fixture->piVars['showUid'] = $this->seminarUid;

        $result = $this->fixture->main('', []);

        self::assertContains($this->translator->translate('message_onRequest'), $result);
    }
}
