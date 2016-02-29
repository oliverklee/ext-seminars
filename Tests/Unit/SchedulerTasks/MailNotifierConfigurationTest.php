<?php
namespace OliverKlee\Seminars\Tests\Unit\SchedulerTasks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use OliverKlee\Seminars\SchedulerTasks\MailNotifier;
use OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MailNotifierConfigurationTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var MailNotifierConfiguration
     */
    protected $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    protected $configuration = null;

    /**
     * @var \Tx_Oelib_EmailCollector
     */
    protected $mailer = null;

    /**
     * @var LanguageService
     */
    private $languageBackup = null;

    /**
     * @var SchedulerModuleController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleController = null;

    /**
     * @var LanguageService
     */
    private $languageService = null;

    protected function setUp()
    {
        $this->languageBackup = isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : null;
        $this->languageService = new LanguageService();
        $this->languageService->init('default');
        $GLOBALS['LANG'] = $this->languageService;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->moduleController = $this->getMock(SchedulerModuleController::class, [], [], '', false);

        $this->subject = new MailNotifierConfiguration();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        $GLOBALS['LANG'] = $this->languageBackup;
        $this->languageBackup = null;
    }

    /**
     * @test
     */
    public function classImplementsAdditionalFieldProvider()
    {
        self::assertInstanceOf(AdditionalFieldProviderInterface::class, $this->subject);
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
                    'code' => '<input type="text" name="tx_scheduler[seminars_configurationPageUid]" id="task-page-uid" value="" size="4" />',
                    'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xml:schedulerTasks.fields.page-uid',
                    'cshKey' => '',
                    'cshLabel' => '',
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
                    'code' => '<input type="text" name="tx_scheduler[seminars_configurationPageUid]" id="task-page-uid" value="' . $uid . '" size="4" />',
                    'label' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xml:schedulerTasks.fields.page-uid',
                    'cshKey' => '',
                    'cshLabel' => '',
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
        $pageUid = $this->testingFramework->createFrontEndPage();
        $submittedData = ['seminars_configurationPageUid' => $pageUid];

        $result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForUidOfExistingPageNotAddsErrorMessage()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $submittedData = ['seminars_configurationPageUid' => $pageUid];

        $this->moduleController->expects(self::never())->method('addMessage');

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForUidOfInexistentPageReturnsFalse()
    {
        $pageUid = $this->testingFramework->getAutoIncrement('pages');
        $submittedData = ['seminars_configurationPageUid' => $pageUid];

        $result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForUidOfInexistentPageAddsErrorMessage()
    {
        $pageUid = $this->testingFramework->getAutoIncrement('pages');
        $submittedData = ['seminars_configurationPageUid' => $pageUid];

        $this->moduleController->expects(self::once())->method('addMessage')->with(
            $this->languageService->sL(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xml:schedulerTasks.errors.page-uid'
            ),
            FlashMessage::ERROR
        );

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForZeroUidReturnsFalse()
    {
        $pageUid = 0;
        $submittedData = ['seminars_configurationPageUid' => $pageUid];

        $result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForZeroUidAddsErrorMessage()
    {
        $pageUid = 0;
        $submittedData = ['seminars_configurationPageUid' => $pageUid];

        $this->moduleController->expects(self::once())->method('addMessage')->with(
            $this->languageService->sL(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xml:schedulerTasks.errors.page-uid'
            ),
            FlashMessage::ERROR
        );

        $this->subject->validateAdditionalFields($submittedData, $this->moduleController);
    }

    /**
     * @test
     */
    public function saveAdditionalFieldsSavesIntegerPageUidToTask()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $submittedData = ['seminars_configurationPageUid' => (string)$pageUid];

        /** @var MailNotifier|\PHPUnit_Framework_MockObject_MockObject $task */
        $task = $this->getMock(MailNotifier::class);
        $task->expects(self::once())->method('setConfigurationPageUid')->with($pageUid);

        $this->subject->saveAdditionalFields($submittedData, $task);
    }
}
