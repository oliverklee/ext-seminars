<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\SchedulerTasks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\SchedulerTasks\MailNotifier;
use OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Messaging\FlashMessage;
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
     * @var SchedulerModuleController|MockObject
     */
    private $moduleController = null;

    protected function setUp()
    {
        parent::setUp();

        $this->initializeBackEndLanguage();

        $this->moduleController = $this->createMock(SchedulerModuleController::class);
        $this->subject = new MailNotifierConfiguration();
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

        $this->moduleController->expects(self::never())->method('addMessage');

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);
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

        $this->moduleController->expects(self::once())->method('addMessage')->with(
            $this->languageService->sL(self::LABEL_PREFIX . 'schedulerTasks.errors.page-uid'),
            FlashMessage::ERROR
        );

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);
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

        $this->moduleController->expects(self::once())->method('addMessage')->with(
            $this->languageService->sL(self::LABEL_PREFIX . 'schedulerTasks.errors.page-uid'),
            FlashMessage::ERROR
        );

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);
    }

    /**
     * @test
     */
    public function saveAdditionalFieldsSavesIntegerPageUidToTask()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/MailNotifierConfiguration.xml');
        $pageUid = 1;
        $submittedData = ['seminars_configurationPageUid' => (string)$pageUid];

        /** @var MailNotifier|MockObject $task */
        $task = $this->createMock(MailNotifier::class);
        $task->expects(self::once())->method('setConfigurationPageUid')->with($pageUid);

        $this->subject->saveAdditionalFields($submittedData, $task);
    }
}
