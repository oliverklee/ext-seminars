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
class MailNotifierConfigurationTest extends \Tx_Phpunit_TestCase {
	/**
	 * @var MailNotifierConfiguration
	 */
	protected $subject = NULL;

	/**
	 * @var \Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var \Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * @var \Tx_Oelib_EmailCollector
	 */
	protected $mailer = NULL;

	/**
	 * @var LanguageService
	 */
	private $languageBackup = NULL;

	/**
	 * @var SchedulerModuleController|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $moduleController = null;

	/**
	 * @var LanguageService
	 */
	private $languageService = null;

	protected function setUp() {
		$this->languageBackup = isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : null;
		$this->languageService = new LanguageService();
		$this->languageService->init('en');
		$GLOBALS['LANG'] = $this->languageService;

		$this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
		$this->moduleController = $this->getMock(SchedulerModuleController::class, [], [], '', false);

		$this->subject = new MailNotifierConfiguration();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		$GLOBALS['LANG'] = $this->languageBackup;
		$this->languageBackup = null;
	}

	/**
	 * @test
	 */
	public function classImplementsAdditionalFieldProvider() {
		self::assertInstanceOf(AdditionalFieldProviderInterface::class, $this->subject);
	}

	/**
	 * @test
	 */
	public function getAdditionalFieldsInitiallyReturnsEmptyField() {
		$taskInfo = [];
		$result = $this->subject->getAdditionalFields($taskInfo, null, $this->moduleController);

		self::assertSame(
			[
				'task-page-uid' => [
					'code' => '<input type="text" name="tx_scheduler[configurationPageUid]" id="task-page-uid" value="" size="4" />',
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
	public function getAdditionalFieldsForNonZeroIntegerUidReturnsFieldWithUid() {
		$taskInfo = ['configurationPageUid' => 42];
		$result = $this->subject->getAdditionalFields($taskInfo, null, $this->moduleController);

		self::assertSame(
			[
				'task-page-uid' => [
					'code' => '<input type="text" name="tx_scheduler[configurationPageUid]" id="task-page-uid" value="42" size="4" />',
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
	public function getAdditionalFieldsForNonZeroStringUidReturnsFieldWithUid() {
		$taskInfo = ['configurationPageUid' => '42'];
		$result = $this->subject->getAdditionalFields($taskInfo, null, $this->moduleController);

		self::assertSame(
			[
				'task-page-uid' => [
					'code' => '<input type="text" name="tx_scheduler[configurationPageUid]" id="task-page-uid" value="42" size="4" />',
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
	public function getAdditionalFieldsForZeroStringUidReturnsEmptyField() {
		$taskInfo = ['configurationPageUid' => '0'];
		$result = $this->subject->getAdditionalFields($taskInfo, null, $this->moduleController);

		self::assertSame(
			[
				'task-page-uid' => [
					'code' => '<input type="text" name="tx_scheduler[configurationPageUid]" id="task-page-uid" value="" size="4" />',
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
	public function getAdditionalFieldsForStringUidReturnsEmptyField() {
		$taskInfo = ['configurationPageUid' => 'hello'];
		$result = $this->subject->getAdditionalFields($taskInfo, null, $this->moduleController);

		self::assertSame(
			[
				'task-page-uid' => [
					'code' => '<input type="text" name="tx_scheduler[configurationPageUid]" id="task-page-uid" value="" size="4" />',
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
	public function validateAdditionalFieldsForUidOfExistingPageReturnsTrue() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$submittedData = ['configurationPageUid' => $pageUid];

		$result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

		self::assertTrue($result);
	}

	/**
	 * @test
	 */
	public function validateAdditionalFieldsForUidOfExistingPageNotAddsErrorMessage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$submittedData = ['configurationPageUid' => $pageUid];

		$this->moduleController->expects(self::never())->method('addMessage');

		$this->subject->validateAdditionalFields($submittedData, $this->moduleController);
	}

	/**
	 * @test
	 */
	public function validateAdditionalFieldsForUidOfInexistentPageReturnsFalse() {
		$pageUid = $this->testingFramework->getAutoIncrement('pages');
		$submittedData = ['configurationPageUid' => $pageUid];

		$result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

		self::assertFalse($result);
	}

	/**
	 * @test
	 */
	public function validateAdditionalFieldsForUidOfInexistentPageAddsErrorMessage() {
		$pageUid = $this->testingFramework->getAutoIncrement('pages');
		$submittedData = ['configurationPageUid' => $pageUid];

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
	public function validateAdditionalFieldsForZeroUidReturnsFalse() {
		$pageUid = 0;
		$submittedData = ['configurationPageUid' => $pageUid];

		$result = $this->subject->validateAdditionalFields($submittedData, $this->moduleController);

		self::assertFalse($result);
	}

	/**
	 * @test
	 */
	public function validateAdditionalFieldsForZeroUidAddsErrorMessage() {
		$pageUid = 0;
		$submittedData = ['configurationPageUid' => $pageUid];

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
	public function saveAdditionalFieldsSavesIntegerPageUidToTask() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$submittedData = ['configurationPageUid' => (string)$pageUid];

		/** @var MailNotifier|\PHPUnit_Framework_MockObject_MockObject $task */
		$task = $this->getMock(MailNotifier::class);
		$task->expects(self::once())->method('setConfigurationPageUid')->with($pageUid);

		$this->subject->saveAdditionalFields($submittedData, $task);
	}

}