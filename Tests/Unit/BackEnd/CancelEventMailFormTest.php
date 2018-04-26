<?php

use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_CancelEventMailFormTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_BackEnd_CancelEventMailForm
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
     * backup of the BE user's language
     *
     * @var string
     */
    private $languageBackup;

    /**
     * @var Tx_Oelib_EmailCollector
     */
    protected $mailer = null;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

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

        $this->fixture = new Tx_Seminars_BackEnd_CancelEventMailForm($this->eventUid);
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
        /** @var  FlashMessageService $flashMessageService */
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
            '<button class="submitButton cancelEvent"><p>' .
            $GLOBALS['LANG']->getLL('cancelMailForm_sendButton') .
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
    public function renderContainsTheCancelEventActionForThisForm()
    {
        self::assertContains(
            '<input type="hidden" name="action" value="cancelEvent" />',
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
    public function setEventStatusSetsStatusToCanceled()
    {
        $this->fixture->setPostData(
            [
                'action' => 'cancelEvent',
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
                    Tx_Seminars_Model_Event::STATUS_CANCELED
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
                'action' => 'cancelEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertContains(
            $GLOBALS['LANG']->getLL('message_eventCanceled'),
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

        $messageBody = '%salutation' . $GLOBALS['LANG']->getLL('confirmMailForm_prefillField_messageBody');
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
        $hook->expects(self::once())->method('modifyCancelEmail')
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
        $hook->expects(self::exactly(2))->method('modifyCancelEmail');

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
