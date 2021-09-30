<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Hooks\Interfaces\SeminarListView;
use OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm;
use OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingLegacyEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd\Fixtures\TestingDefaultController;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\DefaultController
 */
final class DefaultControllerTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var string
     */
    private const BLANK_GIF = 'R0lGODlhAQABAJH/AP///wAAAMDAwAAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==';

    /**
     * @var array<string, string>
     */
    private const CONFIGURATION = [
        'dateFormatYMD' => '%d.%m.%Y',
        'timeFormat' => '%H:%M',
        'currency' => 'EUR',
    ];

    /**
     * @var TestingDefaultController
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int the UID of a seminar to which the fixture relates
     */
    private $seminarUid = 0;

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
     * @var HeaderCollector
     */
    private $headerCollector = null;

    /** @var ConnectionPool */
    private $connectionPool = null;

    /**
     * @var DummyConfiguration
     */
    private $sharedConfiguration;

    /**
     * @var DummyConfiguration
     */
    private $extensionConfiguration;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = [];

        $this->testingFramework = new TestingFramework('tx_seminars');
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        HeaderProxyFactory::getInstance()->enableTestMode();
        $headerCollector = HeaderProxyFactory::getInstance()->getHeaderCollector();
        $this->headerCollector = $headerCollector;

        $this->extensionConfiguration = new DummyConfiguration();
        ConfigurationProxy::setInstance('seminars', $this->extensionConfiguration);
        $this->sharedConfiguration = new DummyConfiguration(self::CONFIGURATION);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->sharedConfiguration);

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

        $this->subject = new TestingDefaultController();
        $this->subject->init(
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
        $this->subject->getTemplateCode();
        $this->subject->setLabels();
        $this->subject->createHelperObjects();

        /** @var \Tx_Seminars_Service_SingleViewLinkBuilder&MockObject $linkBuilder */
        $linkBuilder = $this->createPartialMock(
            \Tx_Seminars_Service_SingleViewLinkBuilder::class,
            ['createRelativeUrlForEvent']
        );
        $linkBuilder->method('createRelativeUrlForEvent')
            ->willReturn('index.php?id=42&tx_seminars_pi1%5BshowUid%5D=1337');
        $this->subject->injectLinkBuilder($linkBuilder);

        /** @var ContentObjectRenderer&MockObject $content */
        $content = $this->createPartialMock(ContentObjectRenderer::class, ['IMAGE', 'cObjGetSingle']);
        $content->method('cObjGetSingle')->willReturn(
            '<img src="foo.jpg" alt="bar"/>'
        );
        $this->subject->cObj = $content;

        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        ConfigurationRegistry::purgeInstance();
        ConfigurationProxy::purgeInstances();
        \Tx_Seminars_Service_RegistrationManager::purgeInstance();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
    }

    ///////////////////////
    // Utility functions.
    ///////////////////////

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Inserts a target group record into the database and creates a relation to
     * it from the event with the UID store in $this->seminarUid.
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
    private function createLogInAndRegisterFeUser(): int
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
     */
    private function createLogInAndAddFeUserAsVip(): void
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
    private function addCategoryRelation(array $categoryData = []): int
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
     */
    private function addOrganizerRelation(array $organizerData = []): void
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
     * Creates a mock content object that can create links in the following
     * form:
     *
     * <a href="index.php?id=42&amp;...parameters">link title</a>
     *
     * The page ID isn't checked for existence. So any page ID can be used.
     *
     * @return ContentObjectRenderer&MockObject a mock content object
     */
    private function createContentMock(): ContentObjectRenderer
    {
        /** @var ContentObjectRenderer&MockObject $mock */
        $mock = $this->createPartialMock(ContentObjectRenderer::class, ['getTypoLink']);
        $mock->method('getTypoLink')->willReturnCallback([$this, 'getTypoLink']);

        return $mock;
    }

    /**
     * Callback function for creating mock typolinks.
     *
     * @param string $label the link text
     * @param string $pageId the page UID to link to as a string, must be >= 0
     * @param string[] $urlParameters
     *        URL parameters to set as key/value pairs, not URL-encoded yet
     *
     * @return string faked link tag, will not be empty
     */
    public function getTypoLink(string $label, string $pageId, array $urlParameters = []): string
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
        $this->addTargetGroupRelation();
        self::assertNotEquals(
            $this->addTargetGroupRelation(),
            $this->addTargetGroupRelation()
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationIncreasesTheNumberOfTargetGroups(): void
    {
        self::assertEquals(
            0,
            $this->numberOfTargetGroups
        );

        $this->addTargetGroupRelation();
        self::assertEquals(
            1,
            $this->numberOfTargetGroups
        );

        $this->addTargetGroupRelation();
        self::assertEquals(
            2,
            $this->numberOfTargetGroups
        );
    }

    /**
     * @test
     */
    public function addTargetGroupRelationCreatesRelations(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_target_groups_mm');

        self::assertEquals(
            0,
            $connection->count('*', 'tx_seminars_seminars_target_groups_mm', ['uid_local' => $this->seminarUid])
        );

        $this->addTargetGroupRelation();
        self::assertEquals(
            1,
            $connection->count('*', 'tx_seminars_seminars_target_groups_mm', ['uid_local' => $this->seminarUid])
        );

        $this->addTargetGroupRelation();
        self::assertEquals(
            2,
            $connection->count('*', 'tx_seminars_seminars_target_groups_mm', ['uid_local' => $this->seminarUid])
        );
    }

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
            $connection->count('*', 'tx_seminars_seminars', ['uid' => $this->seminarUid, 'vips' => 1])
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationReturnsPositiveUid(): void
    {
        self::assertTrue(
            $this->addCategoryRelation() > 0
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationCreatesNewUids(): void
    {
        self::assertNotEquals(
            $this->addCategoryRelation(),
            $this->addCategoryRelation()
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationIncreasesTheNumberOfCategories(): void
    {
        self::assertEquals(
            0,
            $this->numberOfCategories
        );

        $this->addCategoryRelation();
        self::assertEquals(
            1,
            $this->numberOfCategories
        );

        $this->addCategoryRelation();
        self::assertEquals(
            2,
            $this->numberOfCategories
        );
    }

    /**
     * @test
     */
    public function addCategoryRelationCreatesRelations(): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_seminars_categories_mm');

        self::assertEquals(
            0,
            $connection->count('*', 'tx_seminars_seminars_categories_mm', ['uid_local' => $this->seminarUid])
        );

        $this->addCategoryRelation();
        self::assertEquals(
            1,
            $connection->count('*', 'tx_seminars_seminars_categories_mm', ['uid_local' => $this->seminarUid])
        );

        $this->addCategoryRelation();
        self::assertEquals(
            2,
            $connection->count('*', 'tx_seminars_seminars_categories_mm', ['uid_local' => $this->seminarUid])
        );
    }

    /**
     * @test
     */
    public function createContentMockCreatesContentObjectRenderer(): void
    {
        self::assertInstanceOf(ContentObjectRenderer::class, $this->createContentMock());
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockCreatesLinkToPageId(): void
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            '<a href="index.php?id=42',
            $contentMock->getTypoLink('link label', '42')
        );
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockUsesLinkTitle(): void
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            '>link label</a>',
            $contentMock->getTypoLink('link label', '42')
        );
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockNotHtmlspecialcharedLinkTitle(): void
    {
        $contentMock = $this->createContentMock();

        $linkTitle = 'foo & bar';
        $result = $contentMock->getTypoLink($linkTitle, '');

        self::assertContains($linkTitle . '</a>', $result);
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockAddsParameters(): void
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            'tx_seminars_pi1%5Bseminar%5D=42',
            $contentMock->getTypoLink(
                'link label',
                '1',
                ['tx_seminars_pi1%5Bseminar%5D' => 42]
            )
        );
    }

    /**
     * @test
     */
    public function createTypoLinkInContentMockCanAddTwoParameters(): void
    {
        $contentMock = $this->createContentMock();

        self::assertContains(
            'tx_seminars_pi1%5Bseminar%5D=42&amp;foo=bar',
            $contentMock->getTypoLink(
                'link label',
                '1',
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

    /**
     * @test
     */
    public function pi1MustBeInitialized(): void
    {
        self::assertNotNull(
            $this->subject
        );
        self::assertTrue(
            $this->subject->isInitialized()
        );
    }

    /**
     * @test
     */
    public function getSeminarReturnsSeminarIfSet(): void
    {
        $this->subject->createSeminar($this->seminarUid);

        self::assertInstanceOf(
            LegacyEvent::class,
            $this->subject->getSeminar()
        );
    }

    /**
     * @test
     */
    public function getRegistrationReturnsRegistrationIfSet(): void
    {
        $this->subject->createRegistration(
            $this->testingFramework->createRecord(
                'tx_seminars_attendances',
                ['seminar' => $this->seminarUid]
            )
        );

        self::assertInstanceOf(
            \Tx_Seminars_OldModel_Registration::class,
            $this->subject->getRegistration()
        );
    }

    /**
     * @test
     */
    public function getRegistrationManagerReturnsRegistrationManager(): void
    {
        self::assertInstanceOf(
            \Tx_Seminars_Service_RegistrationManager::class,
            $this->subject->getRegistrationManager()
        );
    }

    //////////////////////////////////////
    // Tests concerning the single view.
    //////////////////////////////////////

    /**
     * @test
     */
    public function singleViewFlavorWithUidCreatesSingleView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
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
    public function singleViewFlavorWithUidFromShowSingleEventConfigurationCreatesSingleView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
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
    public function singleViewFlavorWithoutUidCreatesSingleView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
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
    public function singleViewContainsHtmlspecialcharedEventTitle(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'Test &amp; event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedEventSubtitle(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'Something for you &amp; me',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedEventRoom(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'Rooms 2 &amp; 3',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewContainsHtmlspecialcharedAccreditationNumber(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            '1 &amp; 1',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function otherDatesListInSingleViewContainsOtherDateWithDateLinkedToSingleView(): void
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + Time::SECONDS_PER_DAY,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 2 * Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 3 * Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $dateUid1;

        self::assertStringContainsString(
            'tx_seminars_pi1%5BshowUid%5D=1337',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function otherDatesListInSingleViewDoesNotContainSingleEventRecordWithTopicSet(): void
    {
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->subject->setConfigurationValue(
            'hideFields',
            'eventsnextday'
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + Time::SECONDS_PER_DAY,
            ]
        );
        $singleEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'topic' => $topicUid,
                'title' => 'Test single 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 2 * Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 3 * Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $dateUid;

        $result = $this->subject->main('', []);

        self::assertStringNotContainsString(
            'tx_seminars_pi1%5BshowUid%5D=' . $singleEventUid,
            $result
        );
    }

    /**
     * @test
     */
    public function otherDatesListInSingleViewByDefaultShowsBookedOutEvents(): void
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + Time::SECONDS_PER_DAY,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 2 * Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 3 * Time::SECONDS_PER_DAY,
                'needs_registration' => 1,
                'attendees_max' => 5,
                'offline_attendees' => 5,
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $dateUid1;

        self::assertStringContainsString(
            'tx_seminars_pi1%5BshowUid%5D=1337',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function otherDatesListInSingleViewForShowOnlyEventsWithVacanciesSetHidesBookedOutEvents(): void
    {
        $this->subject->setConfigurationValue(
            'showOnlyEventsWithVacancies',
            true
        );
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'Test topic',
            ]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + Time::SECONDS_PER_DAY,
            ]
        );
        $dateUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date 2',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 2 * Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK + 3 * Time::SECONDS_PER_DAY,
                'needs_registration' => 1,
                'attendees_max' => 5,
                'offline_attendees' => 5,
            ]
        );

        $this->subject->piVars['showUid'] = $dateUid1;

        self::assertStringNotContainsString(
            'tx_seminars_pi1%5BshowUid%5D=' . $dateUid2,
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSpeakerWithoutHomepageContainsHtmlspecialcharedSpeakerName(): void
    {
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->subject->setConfigurationValue('showSpeakerDetails', true);
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

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

        self::assertStringContainsString(
            'foo &amp; bar',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForContainsHtmlspecialcharedSpeakerOrganization(): void
    {
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->subject->setConfigurationValue('showSpeakerDetails', true);
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

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

        self::assertStringContainsString(
            'foo &amp; bar',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithSpeakerDetailsLinksHtmlspecialcharedSpeakersName(): void
    {
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->subject->setConfigurationValue('showSpeakerDetails', true);
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

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
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithoutSpeakerDetailsLinksHtmlspecialcharedSpeakersName(): void
    {
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->subject->setConfigurationValue('showSpeakerDetails', false);
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

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
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithoutImageNotDisplaysImage(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('detailPID', $this->testingFramework->createFrontEndPage());
        $this->subject->setConfigurationValue('seminarImageSingleViewWidth', 260);
        $this->subject->setConfigurationValue('seminarImageSingleViewHeight', 160);

        $this->subject->piVars['showUid'] = (string)$this->seminarUid;
        $result = $this->subject->main('', []);

        self::assertStringNotContainsString('<p class="tx-seminars-pi1-image">', $result);
        self::assertStringNotContainsString('<img', $result);
    }

    /**
     * @test
     */
    public function singleViewForEventWithImageDisplaysEventImage(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('detailPID', $this->testingFramework->createFrontEndPage());
        $this->subject->setConfigurationValue('seminarImageSingleViewWidth', 260);
        $this->subject->setConfigurationValue('seminarImageSingleViewHeight', 160);

        $this->testingFramework->createDummyFile('test_foo.gif', base64_decode(self::BLANK_GIF, true));
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['image' => 'test_foo.gif']
        );

        $this->subject->piVars['showUid'] = (string)$this->seminarUid;
        $result = $this->subject->main('', []);

        $this->testingFramework->deleteDummyFile('test_foo.gif');

        self::assertStringContainsString('<p class="tx-seminars-pi1-image">', $result);
        self::assertStringContainsString('<img', $result);
    }

    /**
     * @test
     */
    public function singleViewForHideFieldsContainingImageHidesEventImage(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('detailPID', $this->testingFramework->createFrontEndPage());
        $this->subject->setConfigurationValue('hideFields', 'image');
        $this->subject->setConfigurationValue('seminarImageSingleViewWidth', 260);
        $this->subject->setConfigurationValue('seminarImageSingleViewHeight', 160);

        $this->testingFramework->createDummyFile('test_foo.gif', base64_decode(self::BLANK_GIF, true));
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['image' => 'test_foo.gif']
        );

        $this->subject->piVars['showUid'] = (string)$this->seminarUid;
        $result = $this->subject->main('', []);

        $this->testingFramework->deleteDummyFile('test_foo.gif');

        self::assertStringNotContainsString('<p class="tx-seminars-pi1-image">', $result);
        self::assertStringNotContainsString('<img', $result);
    }

    /**
     * @test
     */
    public function singleViewCallsHookSeminarSingleViewModifySingleView(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');

        $hookedInObject = $this->createMock(SeminarSingleView::class);
        $hookedInObject->expects(self::once())->method('modifySingleView')->with($this->subject);

        $hookedInClass = \get_class($hookedInObject);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarSingleView::class][] = $hookedInClass;
        GeneralUtility::addInstance($hookedInClass, $hookedInObject);

        $this->subject->piVars['showUid'] = (string)$this->seminarUid;
        $this->subject->main('', []);
    }

    // Tests concerning attached files in the single view

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFile(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertStringContainsString(
            $dummyFileName,
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFileLinkedToFile(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName = $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#<a href="[^"]+' . $dummyFileName . '" *>' . $dummyFileName . '</a>#',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileInSubfolderOfUploadFolderAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFileLinkedToFile(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFolder = $this->testingFramework->createDummyFolder('test_folder');
        $dummyFile = $this->testingFramework->createDummyFile(
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFolder) . '/test.txt'
        );

        $dummyFileName = $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '#<a href="[^"]+' . $dummyFileName . '" *>' . basename($dummyFile) . '</a>#',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithTwoAttachedFilesAndDisabledLimitFileDownloadToAttendeesContainsBothFileNames(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

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

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $result = $this->subject->main('', []);
        self::assertStringContainsString(
            $dummyFileName,
            $result
        );
        self::assertStringContainsString(
            $dummyFileName2,
            $result
        );
    }

    /**
     * @test
     */
    public function singleViewWithTwoAttachedFilesAndDisabledLimitFileDownloadToAttendeesContainsTwoAttachedFilesWithSortingSetInBackEnd(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

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

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '/.*(' . preg_quote($dummyFileName, '/') . ').*\\s*' .
            '.*(' . preg_quote($dummyFileName2, '/') . ').*/',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndLoggedInFeUserAndRegisteredContainsFileNameOfFile(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertStringContainsString(
            $dummyFileName,
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndLoggedInFeUserAndRegisteredContainsFileNameOfFileLinkedToFile(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName = $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#<a href="[^"]+' . $dummyFileName . '" *>' . $dummyFileName . '</a>#',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileInSubfolderOfUploadFolderAndLoggedInFeUserAndRegisteredContainsFileNameOfFileLinkedToFile(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFolder = $this->testingFramework->createDummyFolder('test_folder');
        $dummyFile = $this->testingFramework->createDummyFile(
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFolder) . '/test.txt'
        );

        $dummyFileName = $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#<a href="[^"]+' . $dummyFileName . '" *>' . basename($dummyFile) . '</a>#',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithTwoAttachedFilesAndLoggedInFeUserAndRegisteredContainsBothFileNames(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
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

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $result = $this->subject->main('', []);
        self::assertStringContainsString(
            $dummyFileName,
            $result
        );
        self::assertStringContainsString(
            $dummyFileName2,
            $result
        );
    }

    /**
     * @test
     */
    public function singleViewWithTwoAttachedFilesAndLoggedInFeUserAndRegisteredContainsTwoAttachedFilesWithSortingSetInBackEnd(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
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

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '/.*(' . preg_quote($dummyFileName, '/') . ').*\\s*' .
            '.*(' . preg_quote($dummyFileName2, '/') . ').*/',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsCSSClassWithFileType(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName = $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $matches = [];
        preg_match('/\\.(\\w+)$/', $dummyFileName, $matches);

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertRegExp(
            '#class="filetype-' . $matches[1] . '"><a href="[^"]+' . $dummyFileName . '" *>' .
            basename($dummyFile) . '</a>#',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function attachedFilesSubpartIsHiddenInSingleViewWithoutAttachedFilesAndWithLoggedInAndRegisteredFeUser(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function attachedFilesSubpartIsHiddenInSingleViewWithAttachedFilesAndLoggedInAndUnregisteredFeUser(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->testingFramework->createAndLoginFrontEndUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function attachedFilesSubpartIsHiddenInSingleViewWithAttachedFilesAndNoLoggedInFeUser(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function attachedFilesSubpartIsVisibleInSingleViewWithAttachedFilesAndLoggedInAndRegisteredFeUser(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function attachedFilesSubpartIsVisibleInSingleViewWithAttachedFilesAndDisabledLimitFileDownloadToAttendees(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function attachedFilesSubpartIsHiddenInSingleViewWithoutAttachedFilesAndWithDisabledLimitFileDownloadToAttendees(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning places in the single view
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForNoSiteDetailsContainsHtmlSpecialcharedTitleOfEventPlace(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('showSiteDetails', false);

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
        $this->subject->piVars['showUid'] = $eventUid;

        self::assertStringContainsString(
            'a &amp; place',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedTitleOfEventPlace(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('showSiteDetails', true);

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
        $this->subject->piVars['showUid'] = $eventUid;

        self::assertStringContainsString(
            'a &amp; place',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedAddressOfEventPlace(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('showSiteDetails', true);

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
        $this->subject->piVars['showUid'] = $eventUid;

        self::assertStringContainsString(
            'over &amp; the rainbow',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedCityOfEventPlace(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('showSiteDetails', true);

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
        $this->subject->piVars['showUid'] = $eventUid;

        self::assertStringContainsString(
            'Knödlingen &amp; Großwürsteling',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSiteDetailsContainsHtmlSpecialcharedZipOfEventPlace(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->setConfigurationValue('showSiteDetails', true);

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
        $this->subject->piVars['showUid'] = $eventUid;

        self::assertStringContainsString(
            '12 &amp; 45',
            $this->subject->main('', [])
        );
    }

    ////////////////////////////////////////////////////
    // Tests concerning time slots in the single view.
    ////////////////////////////////////////////////////

    /**
     * @test
     */
    public function timeSlotsSubpartIsHiddenInSingleViewWithoutTimeSlots(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
        );
    }

    /**
     * @test
     */
    public function timeSlotsSubpartIsVisibleInSingleViewWithOneTimeSlot(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $this->seminarUid]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => 1]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
        );
    }

    /**
     * @test
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=4483
     */
    public function singleViewDisplaysTimeSlotTimesWithDash(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->testingFramework->createRecord(
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
            ['timeslots' => 1]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertStringContainsString(
            '9:45&#8211;18:30',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewCanContainOneHtmlspecialcharedTimeSlotRoom(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room & 1',
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => 1]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertStringContainsString(
            'room &amp; 1',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function timeSlotsSubpartIsVisibleInSingleViewWithTwoTimeSlots(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $this->seminarUid]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $this->seminarUid]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => 2]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
        );
    }

    /**
     * @test
     */
    public function singleViewCanContainTwoTimeSlotRooms(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room 1',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            [
                'seminar' => $this->seminarUid,
                'room' => 'room 2',
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['timeslots' => 2]
        );

        $this->subject->piVars['showUid'] = $this->seminarUid;
        $result = $this->subject->main('', []);
        self::assertStringContainsString(
            'room 1',
            $result
        );
        self::assertStringContainsString(
            'room 2',
            $result
        );
    }

    // Tests concerning target groups in the single view.

    /**
     * @test
     */
    public function targetGroupsSubpartIsHiddenInSingleViewWithoutTargetGroups(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
        );
    }

    /**
     * @test
     */
    public function targetGroupsSubpartIsVisibleInSingleViewWithOneTargetGroup(): void
    {
        $this->addTargetGroupRelation();

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
        );
    }

    /**
     * @test
     */
    public function singleViewCanContainOneHtmlSpecialcharedTargetGroupTitle(): void
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1 & 2']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'group 1 &amp; 2',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function targetGroupsSubpartIsVisibleInSingleViewWithTwoTargetGroups(): void
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1']
        );
        $this->addTargetGroupRelation(
            ['title' => 'group 2']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
        );
    }

    /**
     * @test
     */
    public function singleViewCanContainTwoTargetGroupTitles(): void
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1']
        );
        $this->addTargetGroupRelation(
            ['title' => 'group 2']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $result = $this->subject->main('', []);

        self::assertStringContainsString(
            'group 1',
            $result
        );
        self::assertStringContainsString(
            'group 2',
            $result
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning requirements in the single view.
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForSeminarWithoutRequirementsHidesRequirementsSubpart(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOneRequirementDisplaysRequirementsSubpart(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent,
            'requirements'
        );
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOneRequirementLinksRequirementToItsSingleView(): void
    {
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredEvent = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $requiredEvent,
            'requirements'
        );
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '/<a href=.*' . $requiredEvent . '.*>required_foo<\\/a>/',
            $this->subject->main('', [])
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning dependencies in the single view.
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForSeminarWithoutDependenciesHidesDependenciesSubpart(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_DEPENDENCIES')
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOneDependencyDisplaysDependenciesSubpart(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $this->seminarUid
        );
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_DEPENDENCIES')
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOneDependenciesShowsTitleOfDependency(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'depending_foo',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $this->seminarUid
        );
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'depending_foo',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOneDependencyContainsLinkToDependency(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 1,
            ]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'depending_foo',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $this->seminarUid
        );
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            '>depending_foo</a>',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithTwoDependenciesShowsTitleOfBothDependencies(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'dependencies' => 2,
            ]
        );
        $dependingEventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'depending_bar',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid2,
            $this->seminarUid
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        $renderedOutput = $this->subject->main('', []);
        self::assertStringContainsString(
            'depending_bar',
            $renderedOutput
        );
        self::assertStringContainsString(
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
    public function singleViewContainsHtmlspecialcharedEventTypeTitleAndColonIfEventHasEventType(): void
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'foo &amp; type:',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewNotContainsColonBeforeEventTitleIfEventHasNoEventType(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertNotRegExp(
            '/: *Test &amp; event/',
            $this->subject->main('', [])
        );
    }

    //////////////////////////////////////////////////////
    // Test concerning the categories in the single view
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewCanContainOneHtmlSpecialcharedCategoryTitle(): void
    {
        $this->addCategoryRelation(
            ['title' => 'category & 1']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        self::assertStringContainsString(
            'category &amp; 1',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewCanContainTwoCategories(): void
    {
        $this->addCategoryRelation(
            ['title' => 'category 1']
        );
        $this->addCategoryRelation(
            ['title' => 'category 2']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;
        $result = $this->subject->main('', []);

        self::assertStringContainsString(
            'category 1',
            $result
        );
        self::assertStringContainsString(
            'category 2',
            $result
        );
    }

    /**
     * @test
     */
    public function singleViewShowsCategoryIcon(): void
    {
        $this->testingFramework->createDummyFile('foo_test.gif', base64_decode(self::BLANK_GIF, true));
        $this->addCategoryRelation(
            [
                'title' => 'category 1',
                'icon' => 'foo_test.gif',
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        $singleCategoryWithIcon = $this->subject->main('', []);

        $this->testingFramework->deleteDummyFile('foo_test.gif');

        self::assertStringContainsString(
            'category 1 <img src="',
            $singleCategoryWithIcon
        );
    }

    /**
     * @test
     */
    public function singleViewShowsMultipleCategoriesWithIcons(): void
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        $multipleCategoriesWithIcons = $this->subject->main('', []);

        $this->testingFramework->deleteDummyFile('foo_test.gif');

        self::assertStringContainsString(
            'category 1 <img src="',
            $multipleCategoriesWithIcons
        );

        self::assertStringContainsString(
            'category 2 <img src="',
            $multipleCategoriesWithIcons
        );
    }

    /**
     * @test
     */
    public function singleViewForCategoryWithoutIconDoesNotShowCategoryIcon(): void
    {
        $this->addCategoryRelation(
            ['title' => 'category 1']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            'category 1 <img src="',
            $this->subject->main('', [])
        );
    }

    ///////////////////////////////////////////////////
    // Tests concerning the expiry in the single view
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForDateRecordWithExpiryContainsExpiryDate(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $this->seminarUid,
                'expiry' => mktime(0, 0, 0, 1, 1, 2008),
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $uid;

        self::assertStringContainsString(
            '01.01.2008',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForDateRecordWithoutExpiryNotContainsExpiryLabel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $this->seminarUid,
                'expiry' => 0,
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $uid;

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_expiry'),
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////
    // Tests concerning the payment methods in the single view.
    /////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForEventWithoutPaymentMethodsNotContainsLabelForPaymentMethods(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_paymentmethods'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOnePaymentMethodContainsLabelForPaymentMethods(): void
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_paymentmethods'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOnePaymentMethodContainsOnePaymentMethod(): void
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'Payment Method',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithTwoPaymentMethodsContainsTwoPaymentMethods(): void
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        $result = $this->subject->main('', []);
        self::assertStringContainsString(
            'Payment Method 1',
            $result
        );
        self::assertStringContainsString(
            'Payment Method 2',
            $result
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOnePaymentMethodContainsPaymentMethodTitleProcessedByHtmlspecialchars(): void
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            \htmlspecialchars($paymentMethodTitle, ENT_QUOTES | ENT_HTML5),
            $this->subject->main('', [])
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning the organizers in the single view
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForEventWithOrganzierShowsHtmlspecialcharedOrganizerTitle(): void
    {
        $this->addOrganizerRelation(['title' => 'foo & organizer']);

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'foo &amp; organizer',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithDescriptionShowsOrganizerDescription(): void
    {
        $this->addOrganizerRelation(
            ['title' => 'foo', 'description' => 'organizer description']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'organizer description',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithHomepageLinksHtmlSpecialcharedOrganizerNameToTheirHomepage(): void
    {
        $this->addOrganizerRelation(
            ['title' => 'foo & bar', 'homepage' => 'http://www.orgabar.com']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '#<a href="http://www.orgabar.com".*>foo &amp; bar</a>#',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewDoesNotHaveUnreplacedMarkers(): void
    {
        $this->addOrganizerRelation(['title' => 'foo organizer']);

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            '###',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithTwoOrganizersShowsBothOrganizers(): void
    {
        $this->addOrganizerRelation(['title' => 'organizer 1']);
        $this->addOrganizerRelation(['title' => 'organizer 2']);

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertRegExp(
            '/organizer 1.*organizer 2/s',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithHomepageHtmlSpecialcharsTitleOfOrganizer(): void
    {
        $this->addOrganizerRelation(
            ['title' => 'foo<bar']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'foo&lt;bar',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithOrganizerWithoutHomepageHtmlSpecialCharsTitleOfOrganizer(): void
    {
        $this->addOrganizerRelation(
            ['title' => 'foo<bar']
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            \htmlspecialchars('foo<bar', ENT_QUOTES | ENT_HTML5),
            $this->subject->main('', [])
        );
    }

    //////////////////////////////////////////////////
    // Tests concerning hidden events in single view
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForZeroEventUidNoLoggedInUserReturnsWrongSeminarNumberMessage(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = 0;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_missingSeminarNumber'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForHiddenRecordAndNoLoggedInUserReturnsWrongSeminarNumberMessage(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['hidden' => 1]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_wrongSeminarNumber'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForHiddenRecordAndLoggedInUserNotOwnerOfHiddenRecordReturnsWrongSeminarNumberMessage(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['hidden' => 1]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_wrongSeminarNumber'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForHiddenRecordAndLoggedInUserOwnerOfHiddenRecordShowsHiddenEvent(): void
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'hidden event',
            $this->subject->main('', [])
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the basic functions of the list view
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function eventListFlavorWithoutUidCreatesListView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
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
    public function eventListFlavorWithUidCreatesListView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
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
    public function listViewShowsHtmlspecialcharedEventSubtitle(): void
    {
        self::assertStringContainsString(
            'Something for you &amp; me',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedEventTypeTitle(): void
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

        self::assertStringContainsString(
            'foo &amp; type',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedAccreditationNumber(): void
    {
        self::assertStringContainsString(
            '1 &amp; 1',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedPlaceTitle(): void
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

        self::assertStringContainsString(
            'a &amp; place',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedCityTitle(): void
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

        self::assertStringContainsString(
            'Bonn &amp; Köln',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedOrganizerTitle(): void
    {
        $this->addOrganizerRelation(['title' => 'foo & organizer']);

        self::assertStringContainsString(
            'foo &amp; organizer',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewShowsHtmlspecialcharedTargetGroupTitle(): void
    {
        $this->addTargetGroupRelation(
            ['title' => 'group 1 & 2']
        );

        self::assertStringContainsString(
            'group 1 &amp; 2',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewDisplaysSeminarImage(): void
    {
        $this->testingFramework->createDummyFile('test_foo.gif', base64_decode(self::BLANK_GIF, true));

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['image' => 'test_foo.gif']
        );
        $listViewWithImage = $this->subject->main('', []);
        $this->testingFramework->deleteDummyFile('test_foo.gif');

        self::assertStringContainsString(
            '<img src="',
            $listViewWithImage
        );
    }

    /**
     * @test
     */
    public function listViewForSeminarWithoutImageDoesNotDisplayImage(): void
    {
        self::assertStringNotContainsString(
            '<img src="',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForSeminarWithoutImageRemovesImageMarker(): void
    {
        self::assertStringNotContainsString(
            '###IMAGE###',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewUsesTopicImage(): void
    {
        $fileName = 'test_foo.gif';
        $topicTitle = 'Test topic';

        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => $topicTitle,
                'image' => $fileName,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'title' => 'Test date',
            ]
        );

        /** @var ContentObjectRenderer&MockObject $content */
        $content = $this->createPartialMock(ContentObjectRenderer::class, ['IMAGE', 'cObjGetSingle']);
        $content->method('cObjGetSingle')
            ->with(
                'IMAGE',
                [
                    'file' => 'uploads/tx_seminars/' . $fileName,
                    'file.' => ['width' => '0c', 'height' => '0c'],
                    'altText' => $topicTitle,
                    'titleText' => $topicTitle,
                ]
            )
            ->willReturn('<img src="foo.jpg" alt="' . $topicTitle . '" title="' . $topicTitle . '"/>');
        $this->subject->cObj = $content;

        self::assertRegExp(
            '/<img src="[^"]*"[^>]*title="' . $topicTitle . '"/',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewNotContainsExpiryLabel(): void
    {
        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_expiry'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewHidesStatusColumnByDefault(): void
    {
        $this->subject->main('', []);

        self::assertFalse(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function listViewShowsBookedOutEventByDefault(): void
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

        self::assertStringContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForShowOnlyEventsWithVacanciesSetHidesBookedOutEvent(): void
    {
        $this->subject->setConfigurationValue(
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

        self::assertStringNotContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////
    // Tests concerning the result counter in the list view
    /////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function resultCounterIsZeroForNoResults(): void
    {
        $this->subject->setConfigurationValue(
            'pidList',
            $this->testingFramework->createSystemFolder()
        );
        $this->subject->main('', []);

        self::assertEquals(
            0,
            $this->subject->internal['res_count']
        );
    }

    /**
     * @test
     */
    public function resultCounterIsOneForOneResult(): void
    {
        $this->subject->main('', []);

        self::assertEquals(
            1,
            $this->subject->internal['res_count']
        );
    }

    /**
     * @test
     */
    public function resultCounterIsTwoForTwoResultsOnOnePage(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Another event',
            ]
        );
        $this->subject->main('', []);

        self::assertEquals(
            2,
            $this->subject->internal['res_count']
        );
    }

    /**
     * @test
     */
    public function resultCounterIsSixForSixResultsOnTwoPages(): void
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
        $this->subject->main('', []);

        self::assertEquals(
            6,
            $this->subject->internal['res_count']
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the list view, filtered by category.
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewWithCategoryContainsEventsWithSelectedAndOtherCategory(): void
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
        $this->subject->piVars['category'] = $categoryUid2;

        self::assertStringContainsString(
            'Event with category',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewWithCategoryContainsEventsWithOneOfTwoSelectedCategories(): void
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
        $this->testingFramework->createRelation('tx_seminars_seminars_categories_mm', $eventUid, $categoryUid);
        $this->subject->piVars['categories'][] = (string)$categoryUid;

        self::assertStringContainsString(
            'Event with category',
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////
    // Tests concerning the the list view, filtered by date
    /////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenFromDateShowsEventWithBeginDateAfterFromDate(): void
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

        $this->subject->piVars['from_day'] = date('j', $fromTime);
        $this->subject->piVars['from_month'] = date('n', $fromTime);
        $this->subject->piVars['from_year'] = date('Y', $fromTime);

        self::assertStringContainsString(
            'Foo Event From',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateDoesNotShowEventWithBeginDateBeforeFromDate(): void
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

        $this->subject->piVars['from_day'] = date('j', $fromTime);
        $this->subject->piVars['from_month'] = date('n', $fromTime);
        $this->subject->piVars['from_year'] = date('Y', $fromTime);

        self::assertStringNotContainsString(
            'Foo Event From',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingDayShowsEventWithBeginDateOnFirstDayOfMonth(): void
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

        $this->subject->piVars['from_month'] = date('n', $simTime);
        $this->subject->piVars['from_year'] = date('Y', $simTime);

        self::assertStringContainsString(
            'Foo Event From',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingYearShowsEventWithBeginDateInCurrentYearAfterFromDate(): void
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

        $this->subject->piVars['from_day'] = date('j', $fromTime);
        $this->subject->piVars['from_month'] = date('n', $fromTime);

        self::assertStringContainsString(
            'Foo Event From',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingMonthShowsEventWithBeginDateOnFirstMonthOfYear(): void
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

        $this->subject->piVars['from_day'] = date('j', $simTime);
        $this->subject->piVars['from_year'] = date('Y', $simTime);

        self::assertStringContainsString(
            'Foo Event From',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromDateWithMissingMonthAndDayShowsEventWithBeginDateOnFirstDayOfGivenYear(): void
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

        $this->subject->piVars['from_year'] = date('Y', $simTime);

        self::assertStringContainsString(
            'Foo Event From',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateShowsEventWithBeginDateBeforeToDate(): void
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

        $this->subject->piVars['to_day'] = date('j', $toTime);
        $this->subject->piVars['to_month'] = date('n', $toTime);
        $this->subject->piVars['to_year'] = date('Y', $toTime);

        self::assertStringContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateHidesEventWithBeginDateAfterToDate(): void
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

        $this->subject->piVars['to_day'] = date('j', $toTime);
        $this->subject->piVars['to_month'] = date('n', $toTime);
        $this->subject->piVars['to_year'] = date('Y', $toTime);

        self::assertStringNotContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingDayShowsEventWithBeginDateOnEndOfGivenMonth(): void
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

        $this->subject->piVars['to_month'] = date('n', $simTime);
        $this->subject->piVars['to_year'] = date('Y', $simTime);

        self::assertStringContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingYearShowsEventWithBeginDateOnThisYearBeforeToDate(): void
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

        $this->subject->piVars['to_day'] = date('j', $toTime);
        $this->subject->piVars['to_month'] = date('n', $toTime);

        self::assertStringContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingMonthShowsEventWithBeginDateOnDayOfLastMonthOfGivenYear(): void
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

        $this->subject->piVars['to_day'] = date('j', $simTime);
        $this->subject->piVars['to_year'] = date('Y', $simTime);

        self::assertStringContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenToDateWithMissingMonthAndDayShowsEventWithBeginDateOnEndOfGivenYear(): void
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

        $this->subject->piVars['to_year'] = date('Y', $simTime);

        self::assertStringContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesShowsEventWithBeginDateWithinTimespan(): void
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

        $this->subject->piVars['from_day'] = date('j', $fromTime);
        $this->subject->piVars['from_month'] = date('n', $fromTime);
        $this->subject->piVars['from_year'] = date('Y', $fromTime);
        $this->subject->piVars['to_day'] = date('j', $toTime);
        $this->subject->piVars['to_month'] = date('n', $toTime);
        $this->subject->piVars['to_year'] = date('Y', $toTime);

        self::assertStringContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesCanShowTwoEventsWithBeginDateWithinTimespan(): void
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

        $this->subject->piVars['from_day'] = date('j', $fromTime);
        $this->subject->piVars['from_month'] = date('n', $fromTime);
        $this->subject->piVars['from_year'] = date('Y', $fromTime);
        $this->subject->piVars['to_day'] = date('j', $toTime);
        $this->subject->piVars['to_month'] = date('n', $toTime);
        $this->subject->piVars['to_year'] = date('Y', $toTime);

        $output = $this->subject->main('', []);

        self::assertStringContainsString(
            'Foo Event To',
            $output
        );
        self::assertStringContainsString(
            'Bar Event To',
            $output
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesDoesNotShowEventWithBeginDateBeforeTimespan(): void
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

        $this->subject->piVars['from_day'] = date('j', $simTime);
        $this->subject->piVars['from_month'] = date('n', $simTime);
        $this->subject->piVars['from_year'] = date('Y', $simTime);
        $this->subject->piVars['to_day'] = date('j', $toTime);
        $this->subject->piVars['to_month'] = date('n', $toTime);
        $this->subject->piVars['to_year'] = date('Y', $toTime);

        self::assertStringNotContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenFromAndToDatesDoesNotShowEventWithBeginDateAfterTimespan(): void
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

        $this->subject->piVars['from_day'] = date('j', $fromTime);
        $this->subject->piVars['from_month'] = date('n', $fromTime);
        $this->subject->piVars['from_year'] = date('Y', $fromTime);
        $this->subject->piVars['to_day'] = date('j', $simTime);
        $this->subject->piVars['to_month'] = date('n', $simTime);
        $this->subject->piVars['to_year'] = date('Y', $simTime);

        self::assertStringNotContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForSentDateButAllDatesZeroShowsEventWithoutBeginDate(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
            ]
        );

        $this->subject->piVars['from_day'] = 0;
        $this->subject->piVars['from_month'] = 0;
        $this->subject->piVars['from_year'] = 0;
        $this->subject->piVars['to_day'] = 0;
        $this->subject->piVars['to_month'] = 0;
        $this->subject->piVars['to_year'] = 0;

        self::assertStringContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests concerning the filtering by age in the list view
    ///////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenAgeShowsEventWithTargetgroupWithinAge(): void
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

        $this->subject->piVars['age'] = 15;

        self::assertStringContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenAgeAndEventAgespanHigherThanAgeDoesNotShowThisEvent(): void
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

        $this->subject->piVars['age'] = 4;

        self::assertStringNotContainsString(
            'Foo Event To',
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests concerning the filtering by organizer in the list view
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenOrganizerShowsEventWithOrganizer(): void
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

        $this->subject->piVars['organizer'][] = $organizerUid;

        self::assertStringContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenOrganizerDoesNotShowEventWithOtherOrganizer(): void
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

        $this->subject->piVars['organizer'][]
            = $this->testingFramework->createRecord('tx_seminars_organizers');

        self::assertStringNotContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////
    // Tests concerning the filtering by price in the list view
    /////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForGivenPriceFromShowsEventWithRegularPriceHigherThanPriceFrom(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 21,
            ]
        );

        $this->subject->piVars['price_from'] = 20;

        self::assertStringContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenPriceToShowsEventWithRegularPriceLowerThanPriceTo(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 19,
            ]
        );

        $this->subject->piVars['price_to'] = 20;

        self::assertStringContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenPriceRangeShowsEventWithRegularPriceWithinRange(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 21,
            ]
        );

        $this->subject->piVars['price_from'] = 20;
        $this->subject->piVars['price_to'] = 22;

        self::assertStringContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForGivenPriceRangeHidesEventWithRegularPriceOutsideRange(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Foo Event',
                'price_regular' => 23,
            ]
        );

        $this->subject->piVars['price_from'] = 20;
        $this->subject->piVars['price_to'] = 22;

        self::assertStringNotContainsString(
            'Foo Event',
            $this->subject->main('', [])
        );
    }

    ///////////////////////////////////////////////////
    // Tests concerning the sorting in the list view.
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewCanBeSortedByTitleAscending(): void
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

        $this->subject->piVars['sort'] = 'title:0';
        $output = $this->subject->main('', []);

        self::assertTrue(
            strpos($output, 'Event A') < strpos($output, 'Event B')
        );
    }

    /**
     * @test
     */
    public function listViewCanBeSortedByTitleDescending(): void
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

        $this->subject->piVars['sort'] = 'title:1';
        $output = $this->subject->main('', []);

        self::assertTrue(
            strpos($output, 'Event B') < strpos($output, 'Event A')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function listViewSortedByCategoryWithoutStaticTemplateAndEnabledConfigurationCheckDoesNotCrash(): void
    {
        $this->extensionConfiguration->setAsBoolean('enableConfigCheck', true);

        $subject = new TestingDefaultController();
        $subject->init(['sortListViewByCategory' => 1]);

        $subject->main('', []);
    }

    /**
     * @test
     */
    public function listViewCanBeSortedByTitleAscendingWithinOneCategory(): void
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

        $this->subject->setConfigurationValue('sortListViewByCategory', 1);
        $this->subject->piVars['sort'] = 'title:0';
        $output = $this->subject->main('', []);

        self::assertTrue(
            strpos($output, 'Event A') < strpos($output, 'Event B')
        );
    }

    /**
     * @test
     */
    public function listViewCanBeSortedByTitleDescendingWithinOneCategory(): void
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

        $this->subject->setConfigurationValue('sortListViewByCategory', 1);
        $this->subject->piVars['sort'] = 'title:1';
        $output = $this->subject->main('', []);

        self::assertTrue(
            strpos($output, 'Event B') < strpos($output, 'Event A')
        );
    }

    /**
     * @test
     */
    public function listViewCategorySortingComesBeforeSortingByTitle(): void
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

        $this->subject->setConfigurationValue('sortListViewByCategory', 1);
        $this->subject->piVars['sort'] = 'title:0';
        $output = $this->subject->main('', []);

        self::assertTrue(
            strpos($output, 'Event B') < strpos($output, 'Event A')
        );
    }

    /**
     * @test
     */
    public function listViewCategorySortingHidesRepeatedCategoryNames(): void
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

        $this->subject->setConfigurationValue('sortListViewByCategory', 1);
        $this->subject->piVars['sort'] = 'title:0';

        self::assertEquals(
            1,
            mb_substr_count(
                $this->subject->main('', []),
                'Category X'
            )
        );
    }

    /**
     * @test
     */
    public function listViewCategorySortingListsDifferentCategoryNames(): void
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

        $this->subject->setConfigurationValue('sortListViewByCategory', 1);
        $this->subject->piVars['sort'] = 'title:0';
        $output = $this->subject->main('', []);

        self::assertStringContainsString(
            'Category X',
            $output
        );
        self::assertStringContainsString(
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
    public function teaserGetsLinkedToSingleView(): void
    {
        $this->subject->setConfigurationValue('hideColumns', '');

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with teaser',
                'teaser' => 'Test Teaser',
            ]
        );

        self::assertStringContainsString(
            '>Test Teaser</a>',
            $this->subject->main('', [])
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the category links in the list view.
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function categoryIsLinkedToTheFilteredListView(): void
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->subject->setConfigurationValue('listPID', $frontEndPageUid);

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

        self::assertStringContainsString(
            'tx_seminars_pi1%5Bcategory%5D=' . $categoryUid,
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function categoryIsNotLinkedFromSpecializedListView(): void
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->subject->setConfigurationValue('listPID', $frontEndPageUid);
        $this->subject->setConfigurationValue('what_to_display', 'events_next_day');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event with category',
                'end_date' => Time::SECONDS_PER_WEEK,
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
        $this->subject->createSeminar($eventUid);

        self::assertStringNotContainsString(
            'tx_seminars_pi1[category%5D=' . $categoryUid,
            $this->subject->main('', [])
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning omitDateIfSameAsPrevious.
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function omitDateIfSameAsPreviousOnDifferentDatesWithActiveConfig(): void
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

        $this->subject->piVars['sort'] = 'date:0';
        $this->subject->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            1
        );

        $output = $this->subject->main('', []);
        self::assertStringContainsString(
            '2020',
            $output
        );
        self::assertStringContainsString(
            '2021',
            $output
        );
    }

    /**
     * @test
     */
    public function omitDateIfSameAsPreviousOnDifferentDatesWithInactiveConfig(): void
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

        $this->subject->piVars['sort'] = 'date:0';
        $this->subject->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            0
        );

        $output = $this->subject->main('', []);
        self::assertStringContainsString(
            '2020',
            $output
        );
        self::assertStringContainsString(
            '2021',
            $output
        );
    }

    /**
     * @test
     */
    public function omitDateIfSameAsPreviousOnSameDatesWithActiveConfig(): void
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

        $this->subject->piVars['sort'] = 'date:0';
        $this->subject->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            1
        );

        self::assertEquals(
            1,
            mb_substr_count(
                $this->subject->main('', []),
                '2020'
            )
        );
    }

    /**
     * @test
     */
    public function omitDateIfSameAsPreviousOnSameDatesWithInactiveConfig(): void
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

        $this->subject->piVars['sort'] = 'date:0';
        $this->subject->setConfigurationValue(
            'omitDateIfSameAsPrevious',
            0
        );

        self::assertEquals(
            2,
            mb_substr_count(
                $this->subject->main('', []),
                '2020'
            )
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests concerning limiting the list view to event types
    ///////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewLimitedToEventTypesIgnoresEventsWithoutEventType(): void
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'an event type']
        );
        $this->subject->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid
        );

        self::assertStringNotContainsString(
            'Test &amp; event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewLimitedToEventTypesContainsEventsWithMultipleSelectedEventTypes(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid1 . ',' . $eventTypeUid2
        );

        $result = $this->subject->main('', []);
        self::assertStringContainsString(
            'Event with type',
            $result
        );
        self::assertStringContainsString(
            'Event with another type',
            $result
        );
    }

    /**
     * @test
     */
    public function listViewLimitedToEventTypesIgnoresEventsWithNotSelectedEventType(): void
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
        $this->subject->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid2
        );

        self::assertStringNotContainsString(
            'Event with type',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForSingleEventTypeOverridesLimitToEventTypes(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid1
        );
        $this->subject->piVars['event_type'] = [$eventTypeUid2];

        $result = $this->subject->main('', []);
        self::assertStringNotContainsString(
            'Event with type',
            $result
        );
        self::assertStringContainsString(
            'Event with another type',
            $result
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning limiting the list view to categories
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewLimitedToCategoriesIgnoresEventsWithoutCategory(): void
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'a category']
        );
        $this->subject->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid
        );

        self::assertStringNotContainsString(
            'Test &amp; event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewLimitedToCategoriesContainsEventsWithMultipleSelectedCategories(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid1 . ',' . $categoryUid2
        );

        $result = $this->subject->main('', []);
        self::assertStringContainsString(
            'Event with category',
            $result
        );
        self::assertStringContainsString(
            'Event with another category',
            $result
        );
    }

    /**
     * @test
     */
    public function listViewLimitedToCategoriesIgnoresEventsWithNotSelectedCategory(): void
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
        $this->subject->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid2
        );

        self::assertStringNotContainsString(
            'Event with category',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForSingleCategoryOverridesLimitToCategories(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToCategories',
            $categoryUid1
        );
        $this->subject->piVars['category'] = $categoryUid2;

        $result = $this->subject->main('', []);
        self::assertStringNotContainsString(
            'Event with category',
            $result
        );
        self::assertStringContainsString(
            'Event with another category',
            $result
        );
    }

    // Tests concerning limiting the list view to places

    /**
     * @test
     */
    public function listViewLimitedToPlacesFromSelectorWidgetIgnoresFlexFormsValues(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToPlaces',
            $placeUid1
        );
        $this->subject->piVars['place'] = [$placeUid2];

        $result = $this->subject->main('', []);
        self::assertStringNotContainsString(
            'Event with place',
            $result
        );
        self::assertStringContainsString(
            'Event with another place',
            $result
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning limiting the list view to organizers
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewLimitedToOrganizersContainsEventsWithSelectedOrganizer(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid
        );

        $result = $this->subject->main('', []);

        self::assertStringContainsString(
            'Event with organizer 1',
            $result
        );
    }

    /**
     * @test
     */
    public function listViewLimitedToOrganizerExcludesEventsWithNotSelectedOrganizer(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid1
        );

        self::assertStringNotContainsString(
            'Event with organizer 2',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewLimitedToOrganizersFromSelectorWidgetIgnoresFlexFormsValues(): void
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

        $this->subject->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid1
        );
        $this->subject->piVars['organizer'] = [$organizerUid2];

        $result = $this->subject->main('', []);

        self::assertStringNotContainsString(
            'Event with organizer 1',
            $result
        );
        self::assertStringContainsString(
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
    public function listViewForEventWithUnlimitedVacanciesShowsRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithNoVacanciesAndQueueShowsRegisterOnQueueLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithNoVacanciesAndNoQueueDoesNotShowRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        self::assertStringNotContainsString(
            sprintf(
                $this->getLanguageService()->getLL('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithVacanciesAndNoDateShowsPrebookNowString(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
            ]
        );

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_onlinePrebooking'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithRegistrationBeginInFutureHidesRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithRegistrationBeginInFutureShowsRegistrationOpenOnMessage(): void
    {
        $registrationBegin = $GLOBALS['SIM_EXEC_TIME'] + 20;
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('message_registrationOpensOn'),
                strftime('%d.%m.%Y %H:%M', $registrationBegin)
            ),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithRegistrationBeginInPastShowsRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForEventWithoutRegistrationBeginShowsRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    //////////////////////////////////////////
    // Tests concerning the "my events" view
    //////////////////////////////////////////

    /**
     * @test
     */
    public function myEventsContainsTitleOfEventWithRegistrationForLoggedInUser(): void
    {
        $this->createLogInAndRegisterFeUser();
        $this->subject->setConfigurationValue('what_to_display', 'my_events');

        self::assertStringContainsString(
            'Test &amp; event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEventsNotContainsTitleOfEventWithoutRegistrationForLoggedInUser(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('what_to_display', 'my_events');

        self::assertStringNotContainsString(
            'Test &amp; event',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEventsContainsExpiryOfEventWithExpiryAndRegistrationForLoggedInUser(): void
    {
        $this->createLogInAndRegisterFeUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['expiry' => mktime(0, 0, 0, 1, 1, 2008)]
        );
        $this->subject->setConfigurationValue('what_to_display', 'my_events');

        self::assertStringContainsString(
            '01.01.2008',
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////////////////////
    // Tests concerning mayManagersEditTheirEvents in the "my vip events" list view
    /////////////////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function editSubpartWithMayManagersEditTheirEventsSetToFalseIsHiddenInMyVipEventsListView(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('mayManagersEditTheirEvents', 0);
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->subject->main('', []);
        self::assertFalse(
            $this->subject->isSubpartVisible('LISTITEM_WRAPPER_EDIT')
        );
    }

    /**
     * @test
     */
    public function editSubpartWithMayManagersEditTheirEventsSetToTrueIsVisibleInMyVipEventsListView(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('mayManagersEditTheirEvents', 1);
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('LISTITEM_WRAPPER_EDIT')
        );
    }

    /**
     * @test
     */
    public function managedEventsViewWithMayManagersEditTheirEventsSetToTrueContainsEditLink(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('mayManagersEditTheirEvents', 1);
        $editorPid = $this->testingFramework->createFrontEndPage();
        $this->subject->setConfigurationValue('eventEditorPID', $editorPid);
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertStringContainsString(
            '?id=' . $editorPid,
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////
    // Tests concerning allowCsvExportOfRegistrationsInMyVipEventsView in the "my vip events" list view
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function registrationsSubpartWithAllowCsvExportOfRegistrationsInMyVipEventsViewSetToFalseIsHiddenInMyVipEventsListView(): void
    {
        $this->createLogInAndAddFeUserAsVip();

        $this->subject->main(
            '',
            [
                'allowCsvExportOfRegistrationsInMyVipEventsView' => 0,
                'what_to_display' => 'my_vip_events',
            ]
        );
        self::assertFalse(
            $this->subject->isSubpartVisible('LISTITEM_WRAPPER_REGISTRATIONS')
        );
    }

    /**
     * @test
     */
    public function registrationsSubpartWithAllowCsvExportOfRegistrationsInMyVipEventsViewSetToTrueIsVisibleInMyVipEventsListView(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue(
            'allowCsvExportOfRegistrationsInMyVipEventsView',
            1
        );
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->subject->main('', []);
        self::assertTrue(
            $this->subject->isSubpartVisible('LISTITEM_WRAPPER_REGISTRATIONS')
        );
    }

    /**
     * @test
     */
    public function myVipEventsViewForAllowCsvExportOfRegistrationsInTrueHasEventUidPiVarInRegistrationLink(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue(
            'allowCsvExportOfRegistrationsInMyVipEventsView',
            1
        );
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertStringContainsString(
            'tx_seminars_pi2%5BeventUid%5D',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsViewForAllowCsvExportOfRegistrationsInTrueHasTablePiVarInRegistrationLink(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue(
            'allowCsvExportOfRegistrationsInMyVipEventsView',
            1
        );
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertStringContainsString(
            'tx_seminars_pi2%5Btable%5D=tx_seminars_attendances',
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests concerning the category list in the my vip events view
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function myVipEventsViewShowsCategoryTitleOfEvent(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

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

        self::assertStringContainsString(
            'category_foo',
            $this->subject->main('', [])
        );
    }

    /////////////////////////////////////////////////////////////////////
    // Tests concerning the displaying of events in the VIP events view
    /////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function myVipEventsViewWithTimeFrameSetToCurrentShowsCurrentEvent(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');
        $this->subject->setConfigurationValue('timeframeInList', 'current');
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'title' => 'currentEvent',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 20,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 20,
            ]
        );

        self::assertStringContainsString(
            'currentEvent',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsViewWithTimeFrameSetToCurrentNotShowsEventInFuture(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');
        $this->subject->setConfigurationValue('timeframeInList', 'current');
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'title' => 'futureEvent',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 21,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringNotContainsString(
            'futureEvent',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsShowsStatusColumnByDefault(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function myVipEventsForStatusColumnHiddenByTsSetupHidesStatusColumn(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');
        $this->subject->setConfigurationValue('hideColumns', 'status');

        $this->subject->main('', []);

        self::assertFalse(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function myVipEventsForVisibleEventShowsPublishedStatus(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('visibility_status_published'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myVipEventsHidesRegistrationColumn(): void
    {
        $this->createLogInAndAddFeUserAsVip();
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->subject->main('', []);

        self::assertFalse(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_REGISTRATION')
        );
    }

    // Tests concerning getFieldHeader

    /**
     * @test
     */
    public function getFieldHeaderContainsLabelOfKey(): void
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_date'),
            $this->subject->getFieldHeader('date')
        );
    }

    /**
     * @test
     */
    public function getFieldHeaderForSortableFieldAndSortingEnabledContainsLink(): void
    {
        $this->subject->setConfigurationValue('enableSortingLinksInListView', true);

        self::assertStringContainsString(
            '<a',
            $this->subject->getFieldHeader('date')
        );
    }

    /**
     * @test
     */
    public function getFieldHeaderForSortableFieldAndSortingDisabledNotContainsLink(): void
    {
        $this->subject->setConfigurationValue('enableSortingLinksInListView', false);

        self::assertStringNotContainsString(
            '<a',
            $this->subject->getFieldHeader('date')
        );
    }

    /**
     * @test
     */
    public function getFieldHeaderForNonSortableFieldAndSortingEnabledNotContainsLink(): void
    {
        $this->subject->setConfigurationValue('enableSortingLinksInListView', true);

        self::assertStringNotContainsString(
            '<a',
            $this->subject->getFieldHeader('register')
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning the getLoginLink function.
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function getLoginLinkWithLoggedOutUserAddsUidPiVarToUrl(): void
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'foo',
            ]
        );
        $this->testingFramework->logoutFrontEndUser();

        $this->subject->setConfigurationValue(
            'loginPID',
            $this->testingFramework->createFrontEndPage()
        );

        self::assertStringContainsString(
            rawurlencode('tx_seminars_pi1[uid]') . '=' . $eventUid,
            $this->subject->getLoginLink(
                'foo',
                $this->testingFramework->createFrontEndPage(),
                $eventUid
            )
        );
    }

    //////////////////////////////////////////////////////
    // Tests concerning the pagination of the list view.
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewCanContainOneItemOnTheFirstPage(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        self::assertStringContainsString(
            'Event A',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewCanContainTwoItemsOnTheFirstPage(): void
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

        $output = $this->subject->main('', []);
        self::assertStringContainsString(
            'Event A',
            $output
        );
        self::assertStringContainsString(
            'Event B',
            $output
        );
    }

    /**
     * @test
     */
    public function firstPageOfListViewNotContainsItemForTheSecondPage(): void
    {
        $this->subject->setConfigurationValue(
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

        self::assertStringNotContainsString(
            'Event B',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function secondPageOfListViewContainsItemForTheSecondPage(): void
    {
        $this->subject->setConfigurationValue(
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

        $this->subject->piVars['pointer'] = 1;
        self::assertStringContainsString(
            'Event B',
            $this->subject->main('', [])
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests concerning the attached files column in the list view
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesTrueHidesAttachedFilesHeader(): void
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->subject->main('', []);

        self::assertFalse(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesFalseShowsAttachedFilesHeader(): void
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesTrueHidesAttachedFilesListRowItem(): void
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->subject->main('', []);

        self::assertFalse(
            $this->subject->isSubpartVisible('LISTITEM_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedOutUserAndLimitFileDownloadToAttendeesFalseShowsAttachedFilesListRowItem(): void
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('LISTITEM_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserShowsAttachedFilesHeader(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserShowsAttachedFilesListRowItem(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'Event A',
            ]
        );

        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('LISTITEM_WRAPPER_ATTACHED_FILES')
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesFalseShowsAttachedFile(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        self::assertStringContainsString(
            $dummyFileName,
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesFalseShowsMultipleAttachedFiles(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 0);

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

        $output = $this->subject->main('', []);

        self::assertStringContainsString(
            $dummyFileName,
            $output
        );
        self::assertStringContainsString(
            $dummyFileName2,
            $output
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesTrueAndUserNotAttendeeHidesAttachedFile(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        self::assertStringNotContainsString(
            $dummyFileName,
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewForLoggedInUserAndLimitFileDownloadToAttendeesTrueAndUserAttendeeShowsAttachedFile(): void
    {
        $this->subject->setConfigurationValue('hideColumns', '');
        $this->subject->setConfigurationValue('limitFileDownloadToAttendees', 1);
        $this->createLogInAndRegisterFeUser();

        $dummyFile = $this->testingFramework->createDummyFile();
        $dummyFileName =
            $this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attached_files' => $dummyFileName]
        );

        self::assertStringContainsString(
            $dummyFileName,
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listViewEnsuresPlacePiVarArray(): void
    {
        $this->subject->piVars['place'] = ['foo'];
        $this->subject->main('', []);

        self::assertEmpty(
            $this->subject->piVars['place']
        );
    }

    /**
     * @test
     */
    public function listViewEnsuresOrganizerPiVarArray(): void
    {
        $this->subject->piVars['organizer'] = ['foo'];
        $this->subject->main('', []);

        self::assertEmpty(
            $this->subject->piVars['organizer']
        );
    }

    /**
     * @test
     */
    public function listViewEnsuresEventTypePiVarArray(): void
    {
        $this->subject->piVars['event_type'] = ['foo'];
        $this->subject->main('', []);

        self::assertEmpty(
            $this->subject->piVars['event_type']
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning the owner data in the single view
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerAndOwnerDataEnabledContainsOwnerDataHeading(): void
    {
        $ownerUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_owner'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerAndOwnerDataEnabledNotContainsEmptyLines(): void
    {
        $ownerUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertNotRegExp(
            '/(<p>|<br \\/>)\\s*<br \\/>\\s*(<br \\/>|<\\/p>)/m',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithoutOwnerAndOwnerDataEnabledNotContainsOwnerDataHeading(): void
    {
        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_owner'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerAndOwnerDataDisabledNotContainsOwnerDataHeading(): void
    {
        $ownerUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['owner_feuser' => $ownerUid]
        );

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            0
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_owner'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerAndOwnerDataEnabledContainsOwnerName(): void
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

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'John Doe',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerHtmlSpecialCharsOwnerName(): void
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

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'Tom &amp; Jerry',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerAndOwnerDataDisabledNotContainsOwnerName(): void
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

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            0
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            'Jon Doe',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerAndOwnerDataEnabledCanContainOwnerPhone(): void
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

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            '0123 4567',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForSeminarWithOwnerAndOwnerDataEnabledCanContainOwnerEmailAddress(): void
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

        $this->subject->setConfigurationValue(
            'showOwnerDataInSingleView',
            1
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            'foo@bar.com',
            $this->subject->main('', [])
        );
    }

    //////////////////////////////////////////////////////////////
    // Tests concerning the registration link in the single view
    //////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function singleViewForEventWithUnlimitedVacanciesShowsRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 0,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithNoVacanciesAndQueueShowsRegisterOnQueueLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithNoVacanciesAndNoQueueDoesNotShowRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            sprintf(
                $this->getLanguageService()->getLL('label_onlineRegistrationOnQueue'),
                0
            ),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithVacanciesAndNoDateShowsPrebookNowString(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
            ]
        );

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_onlinePrebooking'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithRegistrationBeginInFutureDoesNotShowRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithRegistrationBeginInFutureShowsRegistrationOpensOnMessage(): void
    {
        $registrationBegin = $GLOBALS['SIM_EXEC_TIME'] + 40;
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('message_registrationOpensOn'),
                strftime('%d.%m.%Y %H:%M', $registrationBegin)
            ),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithRegistrationBeginInPastShowsRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function singleViewForEventWithoutRegistrationBeginShowsRegistrationLink(): void
    {
        $this->subject->setConfigurationValue('enableRegistration', true);
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

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->main('', [])
        );
    }

    ///////////////////////////////////////////
    // Tests concerning the registration form
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function registrationFormHtmlspecialcharsEventTitle(): void
    {
        $registrationFormMock = $this->createMock(\Tx_Seminars_FrontEnd_RegistrationForm::class);
        GeneralUtility::addInstance(\Tx_Seminars_FrontEnd_RegistrationForm::class, $registrationFormMock);

        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('what_to_display', 'seminar_registration');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'title' => 'foo & bar',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'needs_registration' => 1,
                'attendees_max' => 10,
            ]
        );

        $this->subject->piVars['seminar'] = $eventUid;

        self::assertStringContainsString(
            'foo &amp; bar',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function registrationFormForEventWithOneNotFullfilledRequirementIsHidden(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('what_to_display', 'seminar_registration');

        $requiredTopic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
        $this->subject->piVars['seminar'] = $date;

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_your_user_data'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listOfRequirementsForEventWithOneNotFulfilledRequirementListIsShown(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('what_to_display', 'seminar_registration');

        $requiredTopic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
        $this->subject->piVars['seminar'] = $date;
        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
        );
    }

    /**
     * @test
     */
    public function listOfRequirementsForEventWithOneNotFulfilledRequirementLinksHtmlspecialcharedTitleOfRequirement(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('what_to_display', 'seminar_registration');
        $this->subject->setConfigurationValue(
            'detailPID',
            $this->testingFramework->createFrontEndPage()
        );

        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required & foo',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topic,
            $requiredTopic,
            'requirements'
        );
        $this->subject->piVars['seminar'] = $date;

        self::assertRegExp(
            '/<a href=.*' . $requiredTopic . '.*>required &amp; foo<\\/a>/',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function listOfRequirementsForEventWithTwoNotFulfilledRequirementsShownsTitlesOfBothRequirements(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('what_to_display', 'seminar_registration');

        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $date = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'title' => 'required_bar',
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topic,
            $requiredTopic2,
            'requirements'
        );

        $this->subject->piVars['seminar'] = $date;

        self::assertRegExp(
            '/required_foo.*required_bar/s',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function registrationFormCallsRegistrationFormHooks(): void
    {
        $registrationFormMock = $this->createMock(\Tx_Seminars_FrontEnd_RegistrationForm::class);
        GeneralUtility::addInstance(\Tx_Seminars_FrontEnd_RegistrationForm::class, $registrationFormMock);

        $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setConfigurationValue('what_to_display', 'seminar_registration');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'title' => 'Registration form test',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'needs_registration' => 1,
                'attendees_max' => 10,
            ]
        );

        $this->subject->piVars['seminar'] = (string)$eventUid;

        $hook = $this->createMock(SeminarRegistrationForm::class);
        $hook->expects(self::once())->method('modifyRegistrationHeader')->with($this->subject);
        $hook->expects(self::once())->method('modifyRegistrationForm')->with($this->subject, $registrationFormMock);
        $hook->expects(self::once())->method('modifyRegistrationFooter')->with($this->subject);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarRegistrationForm::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->main('', []);
    }

    // Tests concerning getVacanciesClasses

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithEnoughVacanciesReturnsAvailableClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(0);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            'tx-seminars-pi1-vacancies-available',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithOneVacancyReturnsVacancyOneClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(9);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            'tx-seminars-pi1-vacancies-1',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithTwoVacanciesReturnsVacancyTwoClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(8);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            'tx-seminars-pi1-vacancies-2',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithNoVacanciesReturnsVacancyZeroClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(10);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            'tx-seminars-pi1-vacancies-0',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithUnlimitedVacanciesReturnsVacanciesAvailableClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setUnlimitedVacancies();
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            'tx-seminars-pi1-vacancies-available',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithUnlimitedVacanciesDoesNotReturnZeroVacancyClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setUnlimitedVacancies();
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringNotContainsString(
            'tx-seminars-pi1-vacancies-0',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithUnlimitedVacanciesReturnsVacanciesUnlimitedClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setUnlimitedVacancies();
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            'tx-seminars-pi1-vacancies-unlimited',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForRegistrationDeadlineInPastReturnsDeadlineOverClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setNeedsRegistration(true);
        $event->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] - 45);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);

        self::assertStringContainsString(
            'tx-seminars-pi1-registration-deadline-over',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForBeginDateInPastReturnsBeginDateOverClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 45);

        self::assertStringContainsString(
            'tx-seminars-pi1-event-begin-date-over',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForBeginDateInPastAndRegistrationForStartedEventsAllowedReturnsVacanciesAvailableClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setNeedsRegistration(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 45);
        $this->subject->getConfigurationService()->setConfigurationValue(
            'allowRegistrationForStartedEvents',
            1
        );

        self::assertStringContainsString(
            'tx-seminars-pi1-vacancies-available',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithNoVacanciesAndRegistrationQueueReturnsRegistrationQueueClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(10);
        $event->setNeedsRegistration(true);
        $event->setRegistrationQueue(true);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            'tx-seminars-pi1-has-registration-queue',
            $this->subject->getVacanciesClasses($event)
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithNoVacanciesAndNoRegistrationQueueDoesNotReturnRegistrationQueueClass(): void
    {
        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNumberOfAttendances(10);
        $event->setNeedsRegistration(true);
        $event->setRegistrationQueue(false);
        $event->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringNotContainsString(
            'tx-seminars-pi1-has-registration-queue',
            $this->subject->getVacanciesClasses($event)
        );
    }

    //////////////////////////////////////////////////////////////////////////
    // Tests concerning getVacanciesClasses for events without date and with
    // configuration variable 'allowRegistrationForEventsWithoutDate' TRUE.
    //////////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithEnoughVacanciesReturnsAvailableClass(): void
    {
        $this->sharedConfiguration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(0);

        $output = $this->subject->getVacanciesClasses($event);

        self::assertStringContainsString(
            $this->subject->pi_getClassName('vacancies-available'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithOneVacancyReturnsVacancyOneClass(): void
    {
        $this->sharedConfiguration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(9);

        $output = $this->subject->getVacanciesClasses($event);

        self::assertStringContainsString(
            $this->subject->pi_getClassName('vacancies-1'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithTwoVacanciesReturnsVacancyTwoClass(): void
    {
        $this->sharedConfiguration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(8);

        $output = $this->subject->getVacanciesClasses($event);

        self::assertStringContainsString(
            $this->subject->pi_getClassName('vacancies-2'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithNoVacanciesReturnsVacancyZeroClass(): void
    {
        $this->sharedConfiguration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setAttendancesMax(10);
        $event->setNeedsRegistration(true);
        $event->setNumberOfAttendances(10);

        $output = $this->subject->getVacanciesClasses($event);

        self::assertStringContainsString(
            $this->subject->pi_getClassName('vacancies-0'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithUnlimitedVacanciesReturnsAvailableClass(): void
    {
        $this->sharedConfiguration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setUnlimitedVacancies();
        $event->setNumberOfAttendances(0);

        $output = $this->subject->getVacanciesClasses($event);

        self::assertStringContainsString(
            $this->subject->pi_getClassName('vacancies-available'),
            $output
        );
    }

    /**
     * @test
     */
    public function getVacanciesClassesForEventWithoutDateAndWithUnlimitedVacanciesDoesNotReturnDeadlineOverClass(): void
    {
        $this->sharedConfiguration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $event = new TestingLegacyEvent($this->seminarUid);
        $event->setUnlimitedVacancies();
        $event->setNumberOfAttendances(0);

        $output = $this->subject->getVacanciesClasses($event);

        self::assertStringNotContainsString(
            $this->subject->pi_getClassName('registration-deadline-over'),
            $output
        );
    }

    ////////////////////////////////////////////
    // Tests concerning my_entered_events view
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function myEnteredEventViewShowsHiddenRecords(): void
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->subject->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->subject->setConfigurationValue(
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

        self::assertStringContainsString(
            'hiddenEvent',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewShowsStatusColumnByDefault(): void
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->subject->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->subject->setConfigurationValue(
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

        $this->subject->main('', []);

        self::assertTrue(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_STATUS')
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewForHiddenEventShowsStatusPendingLabel(): void
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->subject->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->subject->setConfigurationValue(
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

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('visibility_status_pending'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewForVisibleEventShowsStatusPublishedLabel(): void
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->subject->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->subject->setConfigurationValue(
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

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('visibility_status_published'),
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventViewForTimeFrameSetToCurrentShowsEventEndedInPast(): void
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->subject->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->subject->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );
        $this->subject->setConfigurationValue('timeframeInList', 'current');

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

        self::assertStringContainsString(
            'pastEvent',
            $this->subject->main('', [])
        );
    }

    /**
     * @test
     */
    public function myEnteredEventsViewHidesRegistrationColumn(): void
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->subject->setConfigurationValue(
            'what_to_display',
            'my_entered_events'
        );
        $this->subject->setConfigurationValue(
            'eventEditorFeGroupID',
            $editorGroupUid
        );

        $this->testingFramework->createAndLoginFrontEndUser($editorGroupUid);

        $this->subject->main('', []);

        self::assertFalse(
            $this->subject->isSubpartVisible('LISTHEADER_WRAPPER_REGISTRATION')
        );
    }

    ////////////////////////////////////////////////////
    // Tests concerning mayCurrentUserEditCurrentEvent
    ////////////////////////////////////////////////////

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserAsOwnerIsTrue(): void
    {
        $subject = new TestingDefaultController();
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isUserVip', 'isOwnerFeUser']);
        $event->method('isUserVip')
            ->willReturn(false);
        $event->method('isOwnerFeUser')
            ->willReturn(true);
        $subject->setSeminar($event);

        self::assertTrue(
            $subject->mayCurrentUserEditCurrentEvent()
        );
    }

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserAsVipAndVipEditorAccessIsTrue(): void
    {
        $subject = new TestingDefaultController();

        $subject->cObj = $this->createContentMock();
        $subject->conf = ['mayManagersEditTheirEvents' => true];
        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isUserVip', 'isOwnerFeUser']);
        $event->method('isUserVip')
            ->willReturn(true);
        $event->method('isOwnerFeUser')
            ->willReturn(false);
        $subject->setSeminar($event);

        self::assertTrue(
            $subject->mayCurrentUserEditCurrentEvent()
        );
    }

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserAsVipAndNoVipEditorAccessIsFalse(): void
    {
        $subject = new TestingDefaultController();

        $subject->cObj = $this->createContentMock();
        $subject->conf = ['mayManagersEditTheirEvents' => false];
        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isUserVip', 'isOwnerFeUser']);
        $event->method('isUserVip')
            ->willReturn(true);
        $event->method('isOwnerFeUser')
            ->willReturn(false);
        $subject->setSeminar($event);

        self::assertFalse(
            $subject->mayCurrentUserEditCurrentEvent()
        );
    }

    /**
     * @test
     */
    public function mayCurrentUserEditCurrentEventForLoggedInUserNeitherVipNorOwnerIsFalse(): void
    {
        $subject = new TestingDefaultController();

        $subject->cObj = $this->createContentMock();
        $subject->conf = [
            'eventEditorPID' => 42,
            'mayManagersEditTheirEvents' => true,
        ];
        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isUserVip', 'isOwnerFeUser']);
        $event->method('getUid')
            ->willReturn(91);
        $event->method('isUserVip')
            ->willReturn(false);
        $event->method('isOwnerFeUser')
            ->willReturn(false);
        $subject->setSeminar($event);

        self::assertFalse(
            $subject->mayCurrentUserEditCurrentEvent()
        );
    }

    // Tests concerning the "edit", "hide", "unhide" and "copy" links

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessDeniedReturnsEmptyString(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = ['eventEditorPID' => 42];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(false);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $subject->setSeminar($event);

        self::assertEquals(
            '',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedCreatesLinkToEditPageWithSeminarUid(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [
            'eventEditorPID' => 42,
        ];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $subject->setSeminar($event);

        self::assertContains(
            '<a href="index.php?id=42&amp;tx_seminars_pi1[seminar]=91">' .
            $this->getLanguageService()->getLL('label_edit') . '</a>',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndPublishedVisibleEventCreatesHideLinkToCurrentPageWithSeminarUid(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $event->method('isPublished')->willReturn(true);
        $event->method('isHidden')->willReturn(false);
        $subject->setSeminar($event);

        $currentPageId = (int)$this->getFrontEndController()->id;

        self::assertContains(
            '<a href="index.php?id=' . $currentPageId .
            '" data-method="post" data-post-tx_seminars_pi1-action="hide" data-post-tx_seminars_pi1-seminar="91">' .
            $this->getLanguageService()->getLL('label_hide') . '</a>',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndPublishedHiddenEventCreatesUnhideLinkToCurrentPageWithSeminarUid(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $event->method('isPublished')->willReturn(true);
        $event->method('isHidden')->willReturn(true);
        $subject->setSeminar($event);

        $currentPageId = (int)$this->getFrontEndController()->id;

        self::assertContains(
            '<a href="index.php?id=' . $currentPageId .
            '" data-method="post" data-post-tx_seminars_pi1-action="unhide" data-post-tx_seminars_pi1-seminar="91">' .
            $this->getLanguageService()->getLL('label_unhide') . '</a>',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedVisibleEventNotCreatesHideLink(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $event->method('isPublished')->willReturn(false);
        $event->method('isHidden')->willReturn(false);
        $subject->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=hide',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedHiddenEventNotCreatesUnhideLink(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $event->method('isPublished')->willReturn(false);
        $event->method('isHidden')->willReturn(true);
        $subject->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=unhide',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedHiddenEventNotCreatesCopyLink(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $event->method('isPublished')->willReturn(false);
        $event->method('isHidden')->willReturn(true);
        $subject->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=copy',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndUnpublishedVisibleEventNotCreatesCopyLink(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $event->method('isPublished')->willReturn(false);
        $event->method('isHidden')->willReturn(false);
        $subject->setSeminar($event);

        self::assertNotContains(
            'tx_seminars_pi1[action%5D=copy',
            $subject->createAllEditorLinks()
        );
    }

    /**
     * @test
     */
    public function createAllEditorLinksForEditAccessGrantedAndPublishedHiddenEventCreatesCopyLinkToCurrentPageWithSeminarUid(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(TestingDefaultController::class, ['mayCurrentUserEditCurrentEvent']);
        $subject->cObj = $this->createContentMock();
        $subject->conf = [];
        $subject->expects(self::once())->method('mayCurrentUserEditCurrentEvent')
            ->willReturn(true);

        /** @var LegacyEvent&MockObject $event */
        $event = $this->createPartialMock(LegacyEvent::class, ['getUid', 'isPublished', 'isHidden']);
        $event->method('getUid')->willReturn(91);
        $event->method('isPublished')->willReturn(true);
        $event->method('isHidden')->willReturn(true);
        $subject->setSeminar($event);

        $currentPageId = (int)$this->getFrontEndController()->id;

        self::assertContains(
            '<a href="index.php?id=' . $currentPageId .
            '" data-method="post" data-post-tx_seminars_pi1-action="copy" data-post-tx_seminars_pi1-seminar="91">' .
            $this->getLanguageService()->getLL('label_copy') . '</a>',
            $subject->createAllEditorLinks()
        );
    }

    // Tests concerning the hide/unhide and copy functionality

    /**
     * @test
     */
    public function eventsListNotCallsProcessEventEditorActions(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['processEventEditorActions']
        );
        $subject->expects(self::never())->method('processEventEditorActions');

        $subject->main(
            '',
            [
                'what_to_display' => 'seminar_list',
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ]
        );
    }

    /**
     * @test
     */
    public function myEnteredEventsListCallsProcessEventEditorActions(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['processEventEditorActions']
        );
        $subject->expects(self::once())->method('processEventEditorActions');

        $subject->main(
            '',
            [
                'what_to_display' => 'my_entered_events',
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ]
        );
    }

    /**
     * @test
     */
    public function myManagedEventsListCallsProcessEventEditorActions(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['processEventEditorActions']
        );
        $subject->expects(self::once())->method('processEventEditorActions');

        $subject->main(
            '',
            [
                'what_to_display' => 'my_vip_events',
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ]
        );
    }

    /**
     * @test
     */
    public function processEventEditorActionsIntvalsSeminarPivar(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['ensureIntegerPiVars', 'createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::atLeastOnce())->method('ensureIntegerPiVars')
            ->with(['seminar']);

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithZeroSeminarPivarNotCreatesEventEditor(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::never())->method('createEventEditorInstance');

        $subject->piVars['seminar'] = 0;
        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithNegativeSeminarPivarNotCreatesEventEditor(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::never())->method('createEventEditorInstance');

        $subject->piVars['seminar'] = -1;
        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithPositiveSeminarPivarCreatesEventEditor(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');

        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::once())->method('createEventEditorInstance')->willReturn($eventEditor);

        $subject->piVars['seminar'] = (string)$uid;
        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsWithUidOfExistingEventChecksPermissions(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');

        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::once())->method('hasAccessMessage');

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::atLeastOnce())->method('createEventEditorInstance')->willReturn(
            $eventEditor
        );

        $subject->piVars['seminar'] = (string)$uid;

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForHideActionWithAccessGrantedCallsHideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::atLeastOnce())->method('createEventEditorInstance')->willReturn(
            $eventEditor
        );
        $subject->expects(self::once())->method('hideEvent')->with($event);

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'hide';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForHideActionWithUnpublishedEventAndAccessGrantedNotCallsHideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['publication_hash' => 'foo']);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::atLeastOnce())->method('createEventEditorInstance')->willReturn(
            $eventEditor
        );
        $subject->expects(self::never())->method('hideEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'hide';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForHideActionWithAccessDeniedNotCallsHideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->willReturn(
            'access denied'
        );

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::atLeastOnce())->method('createEventEditorInstance')->willReturn(
            $eventEditor
        );
        $subject->expects(self::never())->method('hideEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'hide';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForUnhideActionWithAccessGrantedCallsUnhideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::once())->method('createEventEditorInstance')->willReturn($eventEditor);
        $subject->expects(self::once())->method('unhideEvent')->with($event);

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'unhide';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForUnhideActionWithUnpublishedEventAccessGrantedNotCallsUnhideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['publication_hash' => 'foo']);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::once())->method('createEventEditorInstance')->willReturn($eventEditor);
        $subject->expects(self::never())->method('unhideEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'unhide';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForUnhideActionWithAccessDeniedNotCallsUnhideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->willReturn('access denied');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::once())->method('createEventEditorInstance')->willReturn($eventEditor);
        $subject->expects(self::never())->method('unhideEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'unhide';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForCopyActionWithAccessGrantedCallsCopyEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent', 'copyEvent']
        );
        $subject->expects(self::atLeastOnce())->method('createEventEditorInstance')->willReturn(
            $eventEditor
        );
        $subject->expects(self::once())->method('copyEvent')->with($event);

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'copy';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForCopyActionWithUnpublishedEventAndAccessGrantedNotCallsCopyEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['publication_hash' => 'foo']);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent', 'copyEvent']
        );
        $subject->expects(self::atLeastOnce())->method('createEventEditorInstance')->willReturn(
            $eventEditor
        );
        $subject->expects(self::never())->method('copyEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'copy';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForCopyActionWithAccessDeniedNotCallsCopyEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::atLeastOnce())->method('hasAccessMessage')->willReturn(
            'access denied'
        );

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent', 'copyEvent']
        );
        $subject->expects(self::atLeastOnce())->method('createEventEditorInstance')->willReturn(
            $eventEditor
        );
        $subject->expects(self::never())->method('copyEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'copy';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForEmptyActionWithPublishedEventAndAccessGrantedNotCallsHideEventOrUnhideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::once())->method('createEventEditorInstance')->willReturn($eventEditor);
        $subject->expects(self::never())->method('hideEvent');
        $subject->expects(self::never())->method('unhideEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = '';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function processEventEditorActionsForInvalidActionWithPublishedEventAndAccessGrantedNotCallsHideEventOrUnhideEvent(): void
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor&MockObject $eventEditor */
        $eventEditor = $this->createPartialMock(\Tx_Seminars_FrontEnd_EventEditor::class, ['hasAccessMessage']);
        $eventEditor->expects(self::once())->method('hasAccessMessage')->willReturn('');

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['createEventEditorInstance', 'hideEvent', 'unhideEvent']
        );
        $subject->expects(self::once())->method('createEventEditorInstance')->willReturn($eventEditor);
        $subject->expects(self::never())->method('hideEvent');
        $subject->expects(self::never())->method('unhideEvent');

        $subject->piVars['seminar'] = $event->getUid();
        $subject->piVars['action'] = 'foo';

        $subject->processEventEditorActions();
    }

    /**
     * @test
     */
    public function hideEventMarksVisibleEventAsHidden(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel([]);

        $subject = new TestingDefaultController();

        $subject->hideEvent($event);

        self::assertTrue(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function hideEventKeepsHiddenEventAsHidden(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel(['hidden' => 1]);

        $subject = new TestingDefaultController();

        $subject->hideEvent($event);

        self::assertTrue(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function hideEventSavesEvent(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel([]);
        $mapper->expects(self::once())->method('save')->with($event);

        $subject = new TestingDefaultController();

        $subject->hideEvent($event);
    }

    /**
     * @test
     */
    public function hideEventRedirectsToRequestUrl(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel([]);

        $subject = new TestingDefaultController();

        $subject->hideEvent($event);

        $currentUrl = GeneralUtility::locationHeaderUrl(GeneralUtility::getIndpEnv('REQUEST_URI'));
        self::assertSame(
            'Location: ' . $currentUrl,
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function unhideEventMarksHiddenEventAsVisible(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel(['hidden' => 1]);

        $subject = new TestingDefaultController();

        $subject->unhideEvent($event);

        self::assertFalse(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function unhideEventKeepsVisibleEventAsVisible(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel([]);

        $subject = new TestingDefaultController();

        $subject->unhideEvent($event);

        self::assertFalse(
            $event->isHidden()
        );
    }

    /**
     * @test
     */
    public function unhideEventSavesEvent(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel([]);
        $mapper->expects(self::once())->method('save')->with($event);

        $subject = new TestingDefaultController();

        $subject->unhideEvent($event);
    }

    /**
     * @test
     */
    public function unhideEventRedirectsToRequestUrl(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel([]);

        $subject = new TestingDefaultController();

        $subject->unhideEvent($event);

        $currentUrl = GeneralUtility::locationHeaderUrl(GeneralUtility::getIndpEnv('REQUEST_URI'));
        self::assertSame(
            'Location: ' . $currentUrl,
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function copySavesHiddenCloneOfEvent(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel(['title' => 'TDD for starters']);

        $hiddenClone = clone $event;
        $hiddenClone->markAsHidden();
        $mapper->expects(self::once())->method('save')->with($hiddenClone);

        $subject = new TestingDefaultController();

        $subject->copyEvent($event);
    }

    /**
     * @test
     */
    public function copyRemovesRegistrationsFromEvent(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel(['title' => 'TDD for starters']);
        /** @var Collection<\Tx_Seminars_Model_Registration> $registrations */
        $registrations = new Collection();
        $registrations->add(new \Tx_Seminars_Model_Registration());
        $event->setRegistrations($registrations);

        /** @var Collection<\Tx_Seminars_Model_Registration> $cloneRegistrations */
        $cloneRegistrations = new Collection();
        $hiddenClone = clone $event;
        $hiddenClone->markAsHidden();
        $hiddenClone->setRegistrations($cloneRegistrations);
        $mapper->expects(self::once())->method('save')->with($hiddenClone);

        $subject = new TestingDefaultController();

        $subject->copyEvent($event);
    }

    /**
     * @test
     */
    public function copyEventRedirectsToRequestUrl(): void
    {
        /** @var \Tx_Seminars_Mapper_Event&MockObject $mapper */
        $mapper = $this->getMockBuilder(\Tx_Seminars_Mapper_Event::class)->setMethods(['save'])->getMock();
        MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $mapper);

        $event = $mapper->getLoadedTestingModel([]);

        $subject = new TestingDefaultController();

        $subject->copyEvent($event);

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
    public function initListViewForDefaultListLimitsListByAdditionalParameters(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['limitForAdditionalParameters']
        );
        $subject->expects(self::once())->method('limitForAdditionalParameters');

        $subject->initListView();
    }

    /**
     * @test
     */
    public function initListViewForTopicListLimitsListByAdditionalParameters(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['limitForAdditionalParameters']
        );
        $subject->expects(self::once())->method('limitForAdditionalParameters');

        $subject->initListView('topic_list');
    }

    /**
     * @test
     */
    public function initListViewForMyEventsListNotLimitsListByAdditionalParameters(): void
    {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['limitForAdditionalParameters']
        );
        $subject->expects(self::never())->method('limitForAdditionalParameters');

        $subject->initListView('my_events');
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
    public function hideListRegistrationsColumnIfNecessaryDataProvider(): array
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
        bool $getsHidden,
        string $whatToDisplay,
        int $listPid,
        int $vipListPid
    ): void {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['isRegistrationEnabled', 'isLoggedIn', 'hideColumns']
        );
        $subject->method('isRegistrationEnabled')
            ->willReturn(true);
        $subject->method('isLoggedIn')
            ->willReturn(true);

        if ($getsHidden) {
            $subject->expects(self::once())->method('hideColumns')
                ->with(['list_registrations']);
        } else {
            $subject->expects(self::never())->method('hideColumns');
        }

        $subject->init(
            [
                'registrationsListPID' => $listPid,
                'registrationsVipListPID' => $vipListPid,
            ]
        );

        $subject->hideListRegistrationsColumnIfNecessary($whatToDisplay);
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
        bool $getsHidden,
        string $whatToDisplay,
        int $listPid,
        int $vipListPid
    ): void {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['isRegistrationEnabled', 'isLoggedIn', 'hideColumns']
        );
        $subject->method('isRegistrationEnabled')
            ->willReturn(true);
        $subject->method('isLoggedIn')
            ->willReturn(false);

        $subject->expects(self::once())->method('hideColumns')
            ->with(['list_registrations']);

        $subject->init(
            [
                'registrationsListPID' => $listPid,
                'registrationsVipListPID' => $vipListPid,
            ]
        );

        $subject->hideListRegistrationsColumnIfNecessary($whatToDisplay);
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
        bool $getsHidden,
        string $whatToDisplay,
        int $listPid,
        int $vipListPid
    ): void {
        /** @var TestingDefaultController&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingDefaultController::class,
            ['isRegistrationEnabled', 'isLoggedIn', 'hideColumns']
        );
        $subject->method('isRegistrationEnabled')
            ->willReturn(false);
        $subject->method('isLoggedIn')
            ->willReturn(true);

        $subject->expects(self::once())->method('hideColumns')
            ->with(['list_registrations']);

        $subject->init(
            [
                'registrationsListPID' => $listPid,
                'registrationsVipListPID' => $vipListPid,
            ]
        );

        $subject->hideListRegistrationsColumnIfNecessary($whatToDisplay);
    }

    // Tests concerning the hooks for the event lists

    /**
     * @test
     */
    public function listViewCallsSeminarListViewHookMethodsForTopicList(): void
    {
        $topic = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
            ]
        );
        $this->subject->setConfigurationValue('what_to_display', 'topic_list');

        $hook = $this->createMock(SeminarListView::class);
        $hook->expects(self::once())->method('modifyListHeader')->with($this->subject);
        $hook->expects(self::once())->method('modifyListRow')->with($this->subject);
        $hook->expects(self::never())->method('modifyMyEventsListRow');
        $hook->expects(self::once())->method('modifyListFooter')->with($this->subject);
        $hook->expects(self::once())->method('modifyEventBagBuilder')
            ->with($this->subject, self::anything(), 'topic_list');
        $hook->expects(self::never())->method('modifyRegistrationBagBuilder');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarListView::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->main('', []);
    }

    /**
     * @test
     */
    public function listViewCallsSeminarListViewHookMethodsForSeminarList(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'seminar_list');

        $hook = $this->createMock(SeminarListView::class);
        $hook->expects(self::once())->method('modifyListHeader')->with($this->subject);
        $hook->expects(self::once())->method('modifyListRow')->with($this->subject);
        $hook->expects(self::never())->method('modifyMyEventsListRow');
        $hook->expects(self::once())->method('modifyListFooter')->with($this->subject);
        $hook->expects(self::once())->method('modifyEventBagBuilder')
            ->with($this->subject, self::anything(), 'seminar_list');
        $hook->expects(self::never())->method('modifyRegistrationBagBuilder');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarListView::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->main('', []);
    }

    /**
     * @test
     */
    public function singleViewCallsSeminarListViewHookMethodsForOtherDates(): void
    {
        $topicUId = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUId,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUId,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 11000, // > 1 day after first date
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 12000,
            ]
        );
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = (string)$dateUid;

        $hook = $this->createMock(SeminarListView::class);
        $hook->expects(self::exactly(2))->method('modifyListHeader')->with($this->subject);
        $hook->expects(self::exactly(2))->method('modifyListRow')->with($this->subject);
        $hook->expects(self::never())->method('modifyMyEventsListRow');
        $hook->expects(self::exactly(2))->method('modifyListFooter')->with($this->subject);
        $hook->expects(self::exactly(2))->method('modifyEventBagBuilder')->withConsecutive(
            [$this->subject, self::anything(), 'events_next_day'],
            [$this->subject, self::anything(), 'other_dates']
        );
        $hook->expects(self::never())->method('modifyRegistrationBagBuilder');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarListView::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->main('', []);
    }

    /**
     * @test
     */
    public function listViewCallsSeminarListViewHookMethodsForMyEventsList(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'my_events');

        $this->createLogInAndRegisterFeUser();

        $hook = $this->createMock(SeminarListView::class);
        $hook->expects(self::once())->method('modifyListHeader')->with($this->subject);
        $hook->expects(self::once())->method('modifyListRow')->with($this->subject);
        $hook->expects(self::once())->method('modifyMyEventsListRow')->with($this->subject);
        $hook->expects(self::once())->method('modifyListFooter')->with($this->subject);
        $hook->expects(self::never())->method('modifyEventBagBuilder');
        $hook->expects(self::once())->method('modifyRegistrationBagBuilder')
            ->with($this->subject, self::anything(), 'my_events');
        // We don't test for the second parameter (the bag builder instance here)
        // because we cannot access it from the outside.

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarListView::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->main('', []);
    }

    /**
     * @test
     */
    public function listViewCallsSeminarListViewHookMethodsForMyVipEventsList(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'my_vip_events');

        $this->createLogInAndAddFeUserAsVip();

        $hook = $this->createMock(SeminarListView::class);
        $hook->expects(self::once())->method('modifyListHeader')->with($this->subject);
        $hook->expects(self::once())->method('modifyListRow')->with($this->subject);
        $hook->expects(self::never())->method('modifyMyEventsListRow');
        $hook->expects(self::once())->method('modifyListFooter')->with($this->subject);
        $hook->expects(self::once())->method('modifyEventBagBuilder')
            ->with($this->subject, self::anything(), 'my_vip_events');
        $hook->expects(self::never())->method('modifyRegistrationBagBuilder');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarListView::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->main('', []);
    }

    /**
     * @test
     */
    public function listViewCallsSeminarListViewHookMethodsForMyEnteredEventsList(): void
    {
        $editorGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->subject->setConfigurationValue('what_to_display', 'my_entered_events');
        $this->subject->setConfigurationValue('eventEditorFeGroupID', $editorGroupUid);
        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser($editorGroupUid);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'owner_feuser' => $feUserUid,
            ]
        );

        $hook = $this->createMock(SeminarListView::class);
        $hook->expects(self::once())->method('modifyListHeader')->with($this->subject);
        $hook->expects(self::once())->method('modifyListRow')->with($this->subject);
        $hook->expects(self::never())->method('modifyMyEventsListRow');
        $hook->expects(self::once())->method('modifyListFooter')->with($this->subject);
        $hook->expects(self::once())->method('modifyEventBagBuilder')
            ->with($this->subject, self::anything(), 'my_entered_events');
        $hook->expects(self::never())->method('modifyRegistrationBagBuilder');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarListView::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->main('', []);
    }

    // Tests concerning createSingleViewLink

    /**
     * @test
     */
    public function createSingleViewLinkCreatesLinkToSingleViewPage(): void
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertStringContainsString(
            'href="index.php?id=42&amp;tx_seminars_pi1%5BshowUid%5D=1337"',
            $this->subject->createSingleViewLink($event, 'foo')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithoutDescriptionWithAlwaysLinkSettingLinkUsesLinkText(): void
    {
        $this->subject->setConfigurationValue('linkToSingleView', 'always');
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(['description' => '']);

        self::assertStringContainsString(
            '>foo</a>',
            $this->subject->createSingleViewLink($event, 'foo')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithDescriptionWithAlwaysLinkSettingLinkUsesLinkText(): void
    {
        $this->subject->setConfigurationValue('linkToSingleView', 'always');
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['description' => 'Hello world!']);

        self::assertStringContainsString(
            '>foo</a>',
            $this->subject->createSingleViewLink($event, 'foo')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithoutDescriptionWithNeverLinkSettingReturnsOnlyLabel(): void
    {
        $this->subject->setConfigurationValue('linkToSingleView', 'never');
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(['description' => '']);

        self::assertSame(
            'foo &amp; bar',
            $this->subject->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithDescriptionWithConditionalLinkSettingLinkUsesLinkText(): void
    {
        $this->subject->setConfigurationValue('linkToSingleView', 'onlyForNonEmptyDescription');
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['description' => 'Hello world!']);

        self::assertStringContainsString(
            '>foo &amp; bar</a>',
            $this->subject->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithoutDescriptionWithConditionalLinkSettingReturnsOnlyLabel(): void
    {
        $this->subject->setConfigurationValue('linkToSingleView', 'onlyForNonEmptyDescription');
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(['description' => '']);

        self::assertSame(
            'foo &amp; bar',
            $this->subject->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewForEventWithDescriptionWithNeverLinkSettingReturnsOnlyLabel(): void
    {
        $this->subject->setConfigurationValue('linkToSingleView', 'never');
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['description' => 'Hello world!']);

        self::assertSame(
            'foo &amp; bar',
            $this->subject->createSingleViewLink($event, 'foo & bar')
        );
    }

    /**
     * @test
     */
    public function createSingleViewLinkByDefaultHtmlSpecialCharsLinkText(): void
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertStringContainsString(
            'Chaos &amp; Confusion',
            $this->subject->createSingleViewLink($event, 'Chaos & Confusion')
        );
    }

    /**
     * @test
     */
    public function createSingleViewLinkByWithHtmlSpecialCharsTrueHtmlSpecialCharsLinkText(): void
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertStringContainsString(
            'Chaos &amp; Confusion',
            $this->subject->createSingleViewLink($event, 'Chaos & Confusion')
        );
    }

    /**
     * @test
     */
    public function createSingleViewLinkByWithHtmlSpecialCharsFalseNotHtmlSpecialCharsLinkText(): void
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);

        self::assertStringContainsString(
            'Chaos & Confusion',
            $this->subject->createSingleViewLink($event, 'Chaos & Confusion', false)
        );
    }

    // Tests concerning the price in the single view

    /**
     * @test
     */
    public function singleViewForNoStandardPriceDisplaysForFree(): void
    {
        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        $result = $this->subject->main('', []);

        self::assertStringContainsString($this->getLanguageService()->getLL('message_forFree'), $result);
    }

    /**
     * @test
     */
    public function singleViewForPriceOnRequestDisplaysOnRequest(): void
    {
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->seminarUid, ['price_on_request' => 1]);

        $this->subject->setConfigurationValue('what_to_display', 'single_view');
        $this->subject->piVars['showUid'] = $this->seminarUid;

        $result = $this->subject->main('', []);

        self::assertStringContainsString($this->getLanguageService()->getLL('message_onRequest'), $result);
    }
}
