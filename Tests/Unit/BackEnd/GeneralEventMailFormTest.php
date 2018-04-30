<?php

use OliverKlee\Seminars\Tests\Unit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_GeneralEventMailFormTest extends \Tx_Phpunit_TestCase
{
    use BackEndTestsTrait;

    /**
     * @var \Tx_Seminars_BackEnd_GeneralEventMailForm
     */
    private $fixture;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * UID of a dummy system folder
     *
     * @var int
     */
    protected $dummySysFolderUid = 0;

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
     * @var \Tx_Oelib_EmailCollector
     */
    protected $mailer = null;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('This test is for the old BE module only.');
        }

        $this->dummySysFolderUid = $this->testingFramework->createSystemFolder();
        \Tx_Oelib_PageFinder::getInstance()->setPageUid($this->dummySysFolderUid);

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
                'organizers' => 1,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'title' => 'Dummy Event',
                'registrations' => 1,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->eventUid,
            $this->organizerUid,
            'organizers'
        );

        $this->fixture = new \Tx_Seminars_BackEnd_GeneralEventMailForm($this->eventUid);
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
            '<button class="submitButton sendEmail"><p>' .
            $GLOBALS['LANG']->getLL('generalMailForm_sendButton') .
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
            '<input type="hidden" name="action" value="sendEmail" />',
            $this->fixture->render()
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

        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class)->find($registrationUid);
        $hook = $this->getMock(\Tx_Seminars_Interface_Hook_BackEndModule::class);
        $hook->expects(self::once())->method('modifyGeneralEmail')
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

        $hook = $this->getMock(\Tx_Seminars_Interface_Hook_BackEndModule::class);
        $hook->expects(self::exactly(2))->method('modifyGeneralEmail');

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
