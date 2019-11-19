<?php

declare(strict_types=1);

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\ConfirmEventMailForm;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_ConfirmEventMailFormTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var ConfirmEventMailForm
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * UID of a dummy system folder
     *
     * @var int
     */
    private $dummySysFolderUid;

    /**
     * UID of a dummy event record
     *
     * @var int
     */
    private $eventUid;

    /**
     * @var \Tx_Oelib_EmailCollector
     */
    private $mailer = null;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        \Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->dummySysFolderUid = $this->testingFramework->createSystemFolder();
        \Tx_Oelib_PageFinder::getInstance()->setPageUid($this->dummySysFolderUid);

        $organizerUid = $this->testingFramework->createRecord(
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
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 86400,
                'organizers' => 0,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->eventUid,
            $organizerUid,
            'organizers'
        );

        $this->subject = new ConfirmEventMailForm($this->eventUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $this->restoreOriginalEnvironment();
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
            $this->getLanguageService()->getLL('confirmMailForm_sendButton') .
            '</p></button>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsPrefilledBodyFieldWithLocalizedSalutation()
    {
        self::assertContains('salutation', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderContainsTheConfirmEventActionForThisForm()
    {
        self::assertContains(
            '<input type="hidden" name="action" value="confirmEvent" />',
            $this->subject->render()
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
        self::assertSame('Events', $this->getLanguageService()->getLL('title'));
    }

    /*
     * Tests for setEventStatus
     */

    /**
     * @test
     */
    public function setEventStatusSetsStatusToConfirmed()
    {
        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->subject->render();

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_seminars',
                'uid = ' . $this->eventUid . ' AND cancelled = ' .
                \Tx_Seminars_Model_Event::STATUS_CONFIRMED
            )
        );
    }

    /**
     * @test
     */
    public function setEventStatusCreatesFlashMessage()
    {
        $this->mockBackEndUser->expects(self::atLeastOnce())->method('setAndSaveSessionData')
            ->with(self::anything(), self::anything());

        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->subject->render();
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

        $messageBody = '%salutation' . $this->getLanguageService()->getLL('cancelMailForm_prefillField_messageBody');

        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );
        $this->subject->render();

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

        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class)->find($registrationUid);
        $hook = $this->createMock(\Tx_Seminars_Interface_Hook_BackEndModule::class);
        $hook->expects(self::once())->method('modifyConfirmEmail')
            ->with($registration, self::anything());

        $hookClass = get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $this->subject->render();
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

        $hook = $this->createMock(\Tx_Seminars_Interface_Hook_BackEndModule::class);
        $hook->expects(self::exactly(2))->method('modifyConfirmEmail');

        $hookClass = get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $this->subject->render();
    }
}
