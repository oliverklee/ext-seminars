<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\SchedulerTasks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\SchedulerTasks\MailNotifier;
use OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MailNotifierConfigurationTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var string
     */
    const LABEL_PREFIX = 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:';

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = ['scheduler'];

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var MailNotifierConfiguration
     */
    private $subject = null;

    /**
     * @var SchedulerModuleController&MockObject
     */
    private $moduleController = null;

    protected function setUp()
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        /** @var SchedulerModuleController&MockObject $moduleController */
        $moduleController = $this->createMock(SchedulerModuleController::class);
        $this->moduleController = $moduleController;
        // We can remove this line once we have moved to PHPUnit 7.5.
        // The reason is that PHPUnit 6.5 creates some deprecation notices in the mock builder with PHP 7.4.
        $this->getFlashMessageQueue()->clear();

        $this->subject = new MailNotifierConfiguration();
    }

    protected function tearDown()
    {
        $this->getFlashMessageQueue()->clear();

        parent::tearDown();
    }

    private function getFlashMessageQueue(): FlashMessageQueue
    {
        /** @var FlashMessageService $service */
        $service = GeneralUtility::makeInstance(FlashMessageService::class);

        return $service->getMessageQueueByIdentifier();
    }

    /**
     * @test
     */
    public function getAdditionalFieldsInitiallyReturnsEmptyField()
    {
        $taskInfo = [];
        $result = $this->subject->getAdditionalFields($taskInfo, null, $this->moduleController);

        self::assertSame(
            [
                'task-page-uid' => [
                    'code' => '<input type="text" name="tx_scheduler[seminars_configurationPageUid]" '
                        . 'id="task-page-uid" value="" size="4" />',
                    'label' => self::LABEL_PREFIX . 'schedulerTasks.fields.page-uid',
                ],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function getAdditionalFieldsForTaskWithPageUidReturnsFieldWithUid()
    {
        $taskInfo = [];
        $uid = 112;
        $task = new MailNotifier();
        $task->setConfigurationPageUid($uid);

        $result = $this->subject->getAdditionalFields($taskInfo, $task, $this->moduleController);

        self::assertSame(
            [
                'task-page-uid' => [
                    'code' => '<input type="text" name="tx_scheduler[seminars_configurationPageUid]" '
                        . 'id="task-page-uid" value="' . $uid . '" size="4" />',
                    'label' => self::LABEL_PREFIX . 'schedulerTasks.fields.page-uid',
                ],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForUidOfExistingPageReturnsTrue()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/MailNotifierConfiguration.xml');
        $submittedData = ['seminars_configurationPageUid' => '1'];

        $result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForUidOfExistingPageNotAddsErrorMessage()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/MailNotifierConfiguration.xml');
        $submittedData = ['seminars_configurationPageUid' => '1'];

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertSame(0, $this->getFlashMessageQueue()->count());
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForUidOfInexistentPageReturnsFalse()
    {
        $submittedData = ['seminars_configurationPageUid' => '2'];

        $result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForUidOfInexistentPageAddsErrorMessage()
    {
        $submittedData = ['seminars_configurationPageUid' => '2'];

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertCount(1, $this->getFlashMessageQueue()->getAllMessages(FlashMessage::ERROR));
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForZeroUidReturnsFalse()
    {
        $submittedData = ['seminars_configurationPageUid' => '0'];

        $result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForZeroUidAddsErrorMessage()
    {
        $submittedData = ['seminars_configurationPageUid' => '0'];

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertCount(1, $this->getFlashMessageQueue()->getAllMessages(FlashMessage::ERROR));
    }

    /**
     * @test
     */
    public function saveAdditionalFieldsSavesIntegerPageUidToTask()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/MailNotifierConfiguration.xml');
        $pageUid = 1;
        $submittedData = ['seminars_configurationPageUid' => (string)$pageUid];

        /** @var MailNotifier&MockObject $task */
        $task = $this->createMock(MailNotifier::class);
        $task->expects(self::once())->method('setConfigurationPageUid')->with($pageUid);

        $this->subject->saveAdditionalFields($submittedData, $task);
    }
}
