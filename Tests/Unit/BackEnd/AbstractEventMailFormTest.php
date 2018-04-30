<?php

use OliverKlee\Seminars\Tests\Unit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class Tx_Seminars_Tests_Unit_BackEnd_AbstractEventMailFormTest extends Tx_Phpunit_TestCase
{
    use BackEndTestsTrait;

    /**
     * @var \Tx_Seminars_Tests_Unit_Fixtures_BackEnd_TestingEventMailForm
     */
    private $fixture;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

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
        $this->unifyTestingEnvironment();

        /** @var Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('This test is for the old BE module only.');
        }

        $this->dummySysFolderUid = $this->testingFramework->createSystemFolder();
        Tx_Oelib_PageFinder::getInstance()->setPageUid($this->dummySysFolderUid);

        $this->organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Dummy Organizer',
                'email' => 'foo@example.org',
                'email_footer' => 'organizer footer',
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

        $this->fixture = new \Tx_Seminars_Tests_Unit_Fixtures_BackEnd_TestingEventMailForm($this->eventUid);
        $this->fixture->setDateFormat();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $this->restoreOriginalEnvironment();
    }

    ///////////////////////////////////////////////////
    // Tests regarding the error handling of the form
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderThrowsExceptionForInvalidEventUid()
    {
        $this->setExpectedException(
            \Tx_Oelib_Exception_NotFound::class,
            'There is no event with this UID.'
        );

        new Tx_Seminars_Tests_Unit_Fixtures_BackEnd_TestingEventMailForm(
            $this->testingFramework->getAutoIncrement('tx_seminars_seminars')
        );
    }

    //////////////////////////////////////////////
    // Tests regarding the rendering of the form
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function formActionContainsCurrentPage()
    {
        Tx_Oelib_PageFinder::getInstance()->setPageUid(42);

        self::assertContains(
            '&amp;id=42',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsEventTitleInSubjectFieldForNewForm()
    {
        self::assertContains(
            'Dummy Event',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsPrefilledBodyField()
    {
        self::assertContains(
            $GLOBALS['LANG']->getLL('testForm_prefillField_messageBody'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsBodyFieldWithIntroduction()
    {
        self::assertContains(
            sprintf(
                $GLOBALS['LANG']->getLL('testForm_prefillField_introduction'),
                htmlspecialchars('"Dummy Event"')
            ),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderNotPrefillsSubjectFieldIfEmptyStringWasSentViaPost()
    {
        $this->fixture->setPostData(
            [
                'action' => 'cancelEvent',
                'isSubmitted' => '1',
                'subject' => '',
            ]
        );

        self::assertNotContains(
            'Dummy event',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsEventDateInSubjectFieldForNewFormAndEventWithBeginDate()
    {
        self::assertContains(
            strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderSanitizesPostDataWhenPreFillingAFormField()
    {
        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'messageBody' => '<test>',
            ]
        );
        $formOutput = $this->fixture->render();

        self::assertContains(
            '&lt;test&gt;',
            $formOutput
        );
    }

    /**
     * @test
     */
    public function renderFormContainsCancelButton()
    {
        self::assertContains(
            '<input type="button" value="' .
            $GLOBALS['LANG']->getLL('eventMailForm_backButton') .
            '" class="backButton"' .
            ' onclick="window.location=window.location" />',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsErrorMessageIfFormWasSubmittedWithEmptySubjectField()
    {
        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => '',
            ]
        );

        self::assertContains(
            $GLOBALS['LANG']->getLL('eventMailForm_error_subjectMustNotBeEmpty'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsErrorMessageIfFormWasSubmittedWithEmptyMessageField()
    {
        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'messageBody' => '',
            ]
        );

        self::assertContains(
            $GLOBALS['LANG']->getLL('eventMailForm_error_messageBodyMustNotBeEmpty'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsSubjectFieldPrefilledByUserInputIfFormIsReRendered()
    {
        $this->fixture->setPostData(
            [
                'action' => 'sendForm',
                'isSubmitted' => '1',
                'subject' => 'foo bar',
            ]
        );
        $this->fixture->markAsIncomplete();

        self::assertContains(
            'foo bar',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderEncodesHtmlSpecialCharsInSubjectField()
    {
        $this->fixture->setPostData(
            [
                'action' => 'sendForm',
                'isSubmitted' => '1',
                'subject' => '<foo> & "bar"',
            ]
        );
        $this->fixture->markAsIncomplete();
        self::assertContains(
            '&lt;foo&gt; &amp; &quot;bar&quot;',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsMessageFieldPrefilledByUserInputIfFormIsReRendered()
    {
        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->markAsIncomplete();

        self::assertContains(
            'foo bar',
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsHiddenFieldWithVariableEventUid()
    {
        self::assertContains(
            '<input type="hidden" name="eventUid" value="' . $this->eventUid . '" />',
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

    ///////////////////////////////////
    // Tests for sendEmailToAttendees
    ///////////////////////////////////

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithSubjectOnSubmitOfValidForm()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com']
                ),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertSame(
            'foo',
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForAttendeeWithoutEMailAddressDoesNotSendMail()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsUserNameIntoMailText()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    [
                        'email' => 'foo@example.com',
                        'name' => 'test user',
                    ]
                ),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar %salutation',
            ]
        );
        $this->fixture->render();

        self::assertContains(
            'test user',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesWithoutReplacementMarkerInBodyDoesNotCrash()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    [
                        'email' => 'foo@example.com',
                        'name' => 'test user',
                    ]
                ),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar foo',
            ]
        );

        $this->fixture->render();
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesFirstOrganizerAsSender()
    {
        Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Organizer::class)->getLoadedTestingModel(
                [
                'title' => 'Second Organizer',
                'email' => 'bar@example.org',
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com']
                ),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertArrayHasKey(
            'foo@example.org',
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForEventWithTwoRegistrationsSendsTwoEmails()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com']
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
                    ['email' => 'foo@example.com']
                ),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertSame(
            2,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesAppendsFirstOrganizersFooterToMessageBodyIfSet()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com']
                ),
            ]
        );

        $organizerFooter = 'organizer footer';
        Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Organizer::class)->getLoadedTestingModel(
                [
                'title' => 'Second Organizer',
                'email' => 'bar@example.org',
                'email_footer' => 'oasdfasrganizer footer',
            ]
        );

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
            LF . '-- ' . LF . $organizerFooter,
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForOrganizerWithoutFooterDoesNotAppendFooterMarkersToMessageBody()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_organizers',
            $this->organizerUid,
            ['email_footer' => '']
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com']
                ),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();

        self::assertNotContains(
            LF . '-- ' . LF,
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForExistingRegistrationAddsEmailSentFlashMessage()
    {
        $this->mockBackEndUser->expects(self::atLeastOnce())->method('setAndSaveSessionData')
            ->with(self::anything(), self::anything());

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderUid,
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['email' => 'foo@example.com']
                ),
            ]
        );

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForNoRegistrationsNotAddsEmailSentFlashMessage()
    {
        $this->mockBackEndUser->expects(self::never())->method('setAndSaveSessionData');

        $this->fixture->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'foo bar',
            ]
        );
        $this->fixture->render();
    }

    /////////////////////////////////
    // Tests for redirectToListView
    /////////////////////////////////

    /**
     * @test
     */
    public function redirectToListViewSendsTheRedirectHeader()
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

        self::assertSame(
            'Location: ' . BackendUtility::getModuleUrl(
                Tx_Seminars_BackEnd_AbstractEventMailForm::MODULE_NAME,
                ['id' => Tx_Oelib_PageFinder::getInstance()->getPageUid()],
                false,
                true
            ),
            $this->headerProxy->getLastAddedHeader()
        );
    }

    /////////////////////////////////////
    // Tests concerning getInitialValue
    /////////////////////////////////////

    /**
     * @test
     */
    public function getInitialValueForSubjectAppendsEventTitle()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['title' => 'FooBar']
        );

        $fixture = new Tx_Seminars_Tests_Unit_Fixtures_BackEnd_TestingEventMailForm(
            $this->eventUid
        );

        self::assertContains(
            'FooBar',
            $fixture->getInitialValue('subject')
        );
    }

    /**
     * @test
     */
    public function getInitialValueForSubjectAppendsEventDate()
    {
        $beginDate = strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertContains(
            $beginDate,
            $this->fixture->getInitialValue('subject')
        );
    }

    /**
     * @test
     */
    public function getInitialValueForFooThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'There is no initial value for the field "foo" defined.'
        );

        $this->fixture->getInitialValue('foo');
    }

    ////////////////////////////////////////
    // Tests concerning the error messages
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getErrorMessageForIncompleteFormAndNoStoredMessageReturnsEmptyString()
    {
        $this->fixture->markAsIncomplete();

        self::assertEquals(
            '',
            $this->fixture->getErrorMessage('subject')
        );
    }

    /**
     * @test
     */
    public function getErrorMessageForCompleteFormAndStoredMessageReturnsStoredMessage()
    {
        $this->fixture->setErrorMessage('subject', 'Foo');

        self::assertContains(
            'Foo',
            $this->fixture->getErrorMessage('subject')
        );
    }

    /**
     * @test
     */
    public function getErrorMessageForInCompleteFormAndStoredMessageReturnsThisErrorMessage()
    {
        $this->fixture->markAsIncomplete();
        $this->fixture->setErrorMessage('subject', 'Foo');

        self::assertContains(
            'Foo',
            $this->fixture->getErrorMessage('subject')
        );
    }

    /**
     * @test
     */
    public function setErrorMessageForAlreadySetErrorMessageAppendsNewMessage()
    {
        $this->fixture->markAsIncomplete();
        $this->fixture->setErrorMessage('subject', 'Foo');
        $this->fixture->setErrorMessage('subject', 'Bar');
        $errorMessage = $this->fixture->getErrorMessage('subject');

        self::assertContains(
            'Foo',
            $errorMessage
        );
        self::assertContains(
            'Bar',
            $errorMessage
        );
    }
}
