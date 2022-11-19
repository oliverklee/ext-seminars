<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\EventEditor;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\EventEditor
 */
final class EventEditorTest extends FunctionalTestCase
{
    /**
     * @var array{'form.': array{'eventEditor.': array<string, string>}}
     */
    private const CONFIGURATION = [
        'form.' => ['eventEditor.' => []],
    ];

    /**
     * @var int
     */
    private const NOW = 1577285056;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var EventEditor
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['SIM_EXEC_TIME'] = self::NOW;
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->importDataSet(__DIR__ . '/Fixtures/EventEditorTest.xml');
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = $this->buildSubject(self::CONFIGURATION);
    }

    protected function tearDown(): void
    {
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUpWithoutDatabase();
        }
        FrontEndLoginManager::purgeInstance();
        MapperRegistry::purgeInstance();

        parent::tearDown();
    }

    private function logInUser(int $uid): void
    {
        $user = MapperRegistry::get(FrontEndUserMapper::class)->find($uid);
        FrontEndLoginManager::getInstance()->logInUser($user);
    }

    private function buildSubjectWithRequiredField(string $requiredField): EventEditor
    {
        $configuration = self::CONFIGURATION;
        $configuration['requiredFrontEndEditorFields'] = $requiredField;

        return $this->buildSubject($configuration);
    }

    private function buildSubject(array $configuration): EventEditor
    {
        $this->testingFramework->createFakeFrontEnd(1);

        /** @var TypoScriptFrontendController $frontEndController */
        $frontEndController = $GLOBALS['TSFE'];

        $subject = new EventEditor($configuration, $frontEndController->cObj);
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
        $this->subject->setConfigurationValue('eventSuccessfullySavedPID', 2);

        $result = $this->subject->getEventSuccessfullySavedUrl();

        self::assertStringContainsString('/afterSave', $result);
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

        $data = ['elementName' => 'categories', 'value' => ['42']];
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

        $data = ['elementName' => 'categories', 'value' => ['42']];
        $result = $subject->validateCheckboxes($data);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function validateCheckboxesForUserWithoutDefaultCategoriesAndCategoriesRequiredAndEmptyArrayReturnsFalse(): void
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
    public function validateCheckboxesForUserWithoutDefaultCategoriesAndCategoriesRequiredAndEmptyStringReturnsFalse(): void
    {
        $this->logInUser(1);
        $subject = $this->buildSubjectWithRequiredField('categories');

        $data = ['elementName' => 'categories', 'value' => ''];
        $result = $subject->validateCheckboxes($data);

        self::assertFalse($result);
    }
}
