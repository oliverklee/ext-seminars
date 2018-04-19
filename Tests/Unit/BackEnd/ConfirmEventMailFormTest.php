<?php
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
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_ConfirmEventMailFormTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_BackEnd_ConfirmEventMailForm
     */
    private $fixture;
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

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
     * backup of the BE user's language
     *
     * @var string
     */
    private $languageBackup;

    /**
     * UID of a dummy system folder
     *
     * @var int
     */
    private $dummySysFolderUid;

    /**
     * UID of a dummy organizer record
     *
     * @var int
     */
    private $organizerUid;

    /**
     * UID of a dummy event record
     *
     * @var int
     */
    private $eventUid;

    /**
     * @var Tx_Oelib_EmailCollector
     */
    protected $mailer = null;

    protected function setUp()
    {
        $configuration = new Tx_Oelib_Configuration();
        Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = [];

        $this->languageBackup = $GLOBALS['LANG']->lang;
        $GLOBALS['LANG']->lang = 'default';

        // Loads the locallang file for properly working localization in the tests.
        $GLOBALS['LANG']->includeLLFile('EXT:seminars/Resources/Private/Language/BackEnd/locallang.xlf');

        Tx_Oelib_HeaderProxyFactory::getInstance()->enableTestMode();
        /** @var Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->dummySysFolderUid = $this->testingFramework->createSystemFolder();
        Tx_Oelib_PageFinder::getInstance()->setPageUid($this->dummySysFolderUid);

        $this->organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Dummy Organizer',
                'email' => 'foo@example.org',
            ]
        );
        $this->eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderUid,
                'title' => 'Dummy event',
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 86400,
                'organizers' => 0,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->eventUid,
            $this->organizerUid,
            'organizers'
        );

        $this->fixture = new Tx_Seminars_BackEnd_ConfirmEventMailForm($this->eventUid);
    }

    protected function tearDown()
    {
        $GLOBALS['LANG']->lang = $this->languageBackup;

        $this->testingFramework->cleanUp();

        $this->flushAllFlashMessages();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
        $GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
    }

    /**
     * Returns the rendered flash messages.
     *
     * @return string
     */
    protected function getRenderedFlashMessages()
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $renderedFlashMessages = $defaultFlashMessageQueue->renderFlashMessages();

        return $renderedFlashMessages;
    }

    /**
     * Flushes all flash messages from the queue.
     *
     * @return void
     */
    protected function flushAllFlashMessages()
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->getAllMessagesAndFlush();
    }

    ///////////////////////////////////////////////
    // Tests regarding the rendering of the form.
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function renderContainsSubmitButton()
    {
        self::assertContains(
            '<button class="submitButton confirmEvent"><p>' .
                $GLOBALS['LANG']->getLL('confirmMailForm_sendButton') .
                '</p></button>',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsPrefilledBodyFieldWithLocalizedSalutation()
    {
        self::assertContains('salutation', $this->fixture->render());
    }

    /**
     * @test
     */
    public function renderContainsTheConfirmEventActionForThisForm()
    {
        self::assertContains(
            '<input type="hidden" name="action" value="confirmEvent" />',
            $this->fixture->render()
        );
    }

    ////////////////////////////////
    // Tests for the localization.
    ////////////////////////////////

    /**
     * @test
     */
    public function localizationReturnsLocalizedStringForExistingKey()
    {
        self::assertEquals(
            'Events',
            $GLOBALS['LANG']->getLL('title')
        );
    }

    /*
     * Tests for setEventStatus
     */

    /**
     * @test
     */
    public function setEventStatusSetsStatusToConfirmed()
    {
        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_seminars',
                'uid = ' . $this->eventUid . ' AND cancelled = ' .
                    Tx_Seminars_Model_Event::STATUS_CONFIRMED
            )
        );
    }

    /**
     * @test
     */
    public function setEventStatusCreatesFlashMessage()
    {
        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertContains(
            $GLOBALS['LANG']->getLL('message_eventConfirmed'),
            $this->getRenderedFlashMessages()
        );
    }

    /////////////////////////////////
    // Tests concerning the e-mails
    /////////////////////////////////

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithNameOfRegisteredUserOnSubmitOfValidForm()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com', 'name' => 'foo User']
                ),
            ]
        );

        $messageBody = '%salutation' . $GLOBALS['LANG']->getLL('cancelMailForm_prefillField_messageBody');

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );
        $this->fixture->render();

        self::assertContains(
            'foo User',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailCallsHookWithRegistration()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com', 'name' => 'foo User']
                ),
            ]
        );

        /** @var Tx_Seminars_Model_Registration $registration */
        $registration = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Registration::class)->find($registrationUid);
        $hook = $this->getMock(Tx_Seminars_Interface_Hook_BackEndModule::class);
        $hook->expects(self::once())->method('modifyConfirmEmail')
            ->with($registration, self::anything());

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClass] = $hookClass;

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $this->fixture->render();
    }

    /**
     * @test
     */
    public function sendEmailForTwoRegistrationsCallsHookTwice()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com', 'name' => 'foo User']
                ),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'bar@example.com', 'name' => 'foo User']
                ),
            ]
        );

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_BackEndModule::class);
        $hook->expects(self::exactly(2))->method('modifyConfirmEmail');

        $hookClass = get_class($hook);
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClass] = $hookClass;

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $this->fixture->render();
    }
}
