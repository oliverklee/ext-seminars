<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\FrontEnd\EventEditor;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\EventEditor
 */
final class EventEditorTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    private const CONFIGURATION = [
        'form.' => ['eventEditor.' => []],
    ];

    /**
     * @var int
     */
    private const CURRENT_PAGE_UID = 1;

    /**
     * @var int
     */
    private const EVENT_UID = 1;

    /**
     * @var int
     */
    private const NOW = 1577285056;

    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var EventEditor
     */
    private $subject = null;

    /**
     * @var TypoScriptFrontendController|null
     */
    private $frontEndController = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/Fixtures/EventEditorTest.xml');

        $GLOBALS['SIM_EXEC_TIME'] = self::NOW;

        $this->subject = $this->buildSubject(self::CONFIGURATION);
    }

    protected function tearDown(): void
    {
        FrontEndLoginManager::purgeInstance();
        MapperRegistry::purgeInstance();

        parent::tearDown();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        if ($this->frontEndController instanceof TypoScriptFrontendController) {
            return $this->frontEndController;
        }

        $contentObject = new ContentObjectRenderer();
        $frontEndController = new TypoScriptFrontendController(null, self::CURRENT_PAGE_UID, 0);
        $frontEndController->fe_user = $this->prophesize(FrontendUserAuthentication::class)->reveal();
        if ($frontEndController instanceof LoggerAwareInterface) {
            $frontEndController->setLogger($this->prophesize(LoggerInterface::class)->reveal());
        }
        $frontEndController->determineId();
        $frontEndController->cObj = $contentObject;

        $this->frontEndController = $frontEndController;
        $GLOBALS['TSFE'] = $frontEndController;

        return $frontEndController;
    }

    /**
     * @param int $uid
     */
    private function logInUser(int $uid): void
    {
        $user = $this->getUserMapper()->find($uid);
        FrontEndLoginManager::getInstance()->logInUser($user);
    }

    private function getUserMapper(): \Tx_Seminars_Mapper_FrontEndUser
    {
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class);

        return $mapper;
    }

    private function buildSubjectWithRequiredField(string $requiredField): EventEditor
    {
        $configuration = self::CONFIGURATION;
        $configuration['requiredFrontEndEditorFields'] = $requiredField;

        return $this->buildSubject($configuration);
    }

    private function buildSubject(array $configuration): EventEditor
    {
        $subject = new EventEditor($configuration, $this->getFrontEndController()->cObj);
        $subject->setTestMode();

        return $subject;
    }

    // Tests concerning getEventSuccessfullySavedUrl

    /**
     * @test
     */
    public function getEventSuccessfullySavedUrlReturnsUrlStartingWithProtocol(): void
    {
        $result = $this->subject->getEventSuccessfullySavedUrl();

        self::assertRegExp('#^https?://#', $result);
    }

    /**
     * @test
     */
    public function getEventSuccessfullySavedUrlReturnsConfiguredTargetPid(): void
    {
        $targetPageUid = 2;
        $this->subject->setConfigurationValue('eventSuccessfullySavedPID', $targetPageUid);

        $result = $this->subject->getEventSuccessfullySavedUrl();

        self::assertStringContainsString('?id=' . $targetPageUid, $result);
    }

    /**
     * @test
     */
    public function getEventSuccessfullySavedUrlForProceedUploadReturnsCurrentPageUidAsTargetUid(): void
    {
        $this->subject->setFakedFormValue('proceed_file_upload', 1);

        $result = $this->subject->getEventSuccessfullySavedUrl();

        self::assertStringContainsString('?id=' . self::CURRENT_PAGE_UID, $result);
    }

    /**
     * @test
     */
    public function getEventSuccessfullySavedUrlForProceedUploadReturnsSeminarToEditAsLinkParameter(): void
    {
        $this->subject->setFakedFormValue('proceed_file_upload', 1);
        $this->subject->setObjectUid(self::EVENT_UID);

        $result = $this->subject->getEventSuccessfullySavedUrl();

        self::assertStringContainsString('tx_seminars_pi1%5Bseminar%5D=' . self::EVENT_UID, $result);
    }

    // Tests concerning populateListOrganizers().

    /**
     * @test
     */
    public function populateListOrganizersShowsOrganizerFromDatabase(): void
    {
        $this->logInUser(1);

        $result = $this->subject->populateListOrganizers();

        self::assertContains(['caption' => 'some organizer', 'value' => 1], $result);
    }

    /**
     * @test
     */
    public function populateListOrganizersShowsDefaultOrganizerFromUserGroup(): void
    {
        $this->logInUser(2);

        $result = $this->subject->populateListOrganizers();

        self::assertContains(['caption' => 'default organizer for FE user group', 'value' => 2], $result);
    }

    /**
     * @test
     */
    public function populateListOrganizersForDefaultOrganizerInUserGroupNotIncludesOtherOrganizer(): void
    {
        $this->logInUser(2);

        $result = $this->subject->populateListOrganizers();

        self::assertNotContains(['caption' => 'some organizer', 'value' => 1], $result);
    }

    // Tests concerning modifyDataToInsert

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingPublishImmediatelyNotHidesCreatedEvent(): void
    {
        $this->logInUser(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['hidden']));
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingPublishImmediatelyNotHidesEditedEvent(): void
    {
        $this->logInUser(1);
        $this->subject->setObjectUid(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['hidden']));
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideNewHidesCreatedEvent(): void
    {
        $this->logInUser(2);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame(1, $result['hidden']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideEditedHidesCreatedEvent(): void
    {
        $this->logInUser(3);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame(1, $result['hidden']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideEditedHidesEditedEvent(): void
    {
        $this->logInUser(3);
        $this->subject->setObjectUid(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame(1, $result['hidden']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForPublishSettingHideNewNotHidesEditedEvent(): void
    {
        $this->logInUser(2);
        $this->subject->setObjectUid(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['hidden']));
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventHiddenOnEditingAddsPublicationHash(): void
    {
        $this->logInUser(3);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertNotEmpty($result['publication_hash']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventHiddenOnCreationAddsPublicationHash(): void
    {
        $this->logInUser(2);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertNotEmpty($result['publication_hash']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventNotHiddenOnEditingNotAddsPublicationHash(): void
    {
        $this->logInUser(2);
        $this->subject->setObjectUid(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['publication_hash']));
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEventNotHiddenOnCreationNotAddsPublicationHash(): void
    {
        $this->logInUser(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['publication_hash']));
    }

    /**
     * @test
     */
    public function modifyDataToInsertForHiddenEventNotAddsPublicationHash(): void
    {
        $this->logInUser(2);
        $this->subject->setObjectUid(2);
        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['publication_hash']));
    }

    /**
     * @test
     */
    public function modifyDataToInsertSetsTimestampToCurrentExecutionTime(): void
    {
        $this->logInUser(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame(self::NOW, $result['tstamp']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertSetsCreationDateToCurrentExecutionTime(): void
    {
        $this->logInUser(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame(
            self::NOW,
            $result['crdate']
        );
    }

    /**
     * @test
     */
    public function modifyDataToInsertSetsOwnerFeUserToCurrentlyLoggedInUser(): void
    {
        $userUid = 1;
        $this->logInUser(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame($userUid, $result['owner_feuser']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForNoUserGroupSpecificEventPidSetsPidFromTsSetupAsEventPid(): void
    {
        $this->logInUser(1);
        $pageUid = 42;
        $this->subject->setConfigurationValue('createEventsPID', $pageUid);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame($pageUid, $result['pid']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForUserGroupSpecificEventPidSetsPidFromUserGroupAsEventPid(): void
    {
        $this->logInUser(2);
        $this->subject->setConfigurationValue('createEventsPID', 42);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame(21, $result['pid']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForNewEventAndUserWithoutDefaultCategoriesNotAddsAnyCategories(): void
    {
        $this->logInUser(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['categories']));
    }

    /**
     * @test
     */
    public function modifyDataToInsertForNewEventAndUserWithOneDefaultCategoryAddsThisCategory(): void
    {
        $this->logInUser(4);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertSame('1', $result['categories']);
    }

    /**
     * @test
     */
    public function modifyDataToInsertForEditedEventAndUserWithOneDefaultCategoryNotAddsTheUsersCategory(): void
    {
        $this->logInUser(4);
        $this->subject->setObjectUid(1);

        $result = $this->subject->modifyDataToInsert([]);

        self::assertFalse(isset($result['categories']));
    }

    // Tests concerning validateCheckboxes

    /**
     * @test
     */
    public function validateCheckboxesForNonRequiredFieldAndEmptyValueReturnsTrue(): void
    {
        $this->logInUser(1);

        $data = ['elementName' => 'categories', 'value' => ''];
        $result = $this->subject->validateCheckboxes($data);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function validateCheckboxesForRequiredFieldAndValueNotArrayReturnsFalse(): void
    {
        $this->logInUser(1);
        $subject = $this->buildSubjectWithRequiredField('categories');

        $data = ['elementName' => 'categories', 'value' => ''];
        $result = $subject->validateCheckboxes($data);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function validateCheckboxesForRequiredFieldAndValueEmptyArrayReturnsFalse(): void
    {
        $this->logInUser(1);
        $subject = $this->buildSubjectWithRequiredField('categories');

        $data = ['elementName' => 'categories', 'value' => []];
        $result = $subject->validateCheckboxes($data);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function validateCheckboxesForRequiredFieldAndValueNonEmptyArrayReturnsTrue(): void
    {
        $this->logInUser(1);
        $subject = $this->buildSubjectWithRequiredField('categories');

        $data = ['elementName' => 'categories', 'value' => [42]];
        $result = $subject->validateCheckboxes($data);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function validateCheckboxesForUserWithDefaultCategoriesAndCategoriesRequiredAndEmptyReturnsTrue(): void
    {
        $this->logInUser(4);
        $subject = $this->buildSubjectWithRequiredField('categories');

        $data = ['elementName' => 'categories', 'value' => '[42]'];
        $result = $subject->validateCheckboxes($data);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function validateCheckboxesForUserWithoutDefaultCategoriesAndCategoriesRequiredAndEmptyReturnsFalse(): void
    {
        $this->logInUser(1);
        $subject = $this->buildSubjectWithRequiredField('categories');

        $data = ['elementName' => 'categories', 'value' => ''];
        $result = $subject->validateCheckboxes($data);

        self::assertFalse($result);
    }
}
