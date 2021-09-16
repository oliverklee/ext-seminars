<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingEventMailForm;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;

final class AbstractEventMailFormTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var TestingEventMailForm
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * UID of a dummy event record
     *
     * @var int
     */
    private $eventUid;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');

        PageFinder::getInstance()->setPageUid($this->testingFramework->createSystemFolder());

        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Dummy Organizer',
                'email' => 'foo@example.com',
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
            $organizerUid,
            'organizers'
        );

        $this->subject = new TestingEventMailForm($this->eventUid);
        $this->subject->setDateFormat();
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
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('There is no event with this UID.');

        new TestingEventMailForm(
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
        PageFinder::getInstance()->setPageUid(42);

        self::assertStringContainsString(
            '&amp;id=42',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsEventTitleInSubjectFieldForNewForm()
    {
        self::assertStringContainsString(
            'Dummy Event',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsPrefilledBodyField()
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('testForm_prefillField_messageBody'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsBodyFieldWithIntroduction()
    {
        self::assertStringContainsString(
            \sprintf(
                $this->getLanguageService()->getLL('testForm_prefillField_introduction'),
                \htmlspecialchars('"Dummy Event"', ENT_QUOTES | ENT_HTML5)
            ),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotPrefillsSubjectFieldIfEmptyStringWasSentViaPost()
    {
        $this->subject->setPostData(
            [
                'action' => 'cancelEvent',
                'isSubmitted' => '1',
                'subject' => '',
            ]
        );

        self::assertStringNotContainsString(
            'Dummy event',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsEventDateInSubjectFieldForNewFormAndEventWithBeginDate()
    {
        self::assertContains(
            strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderSanitizesPostDataWhenPreFillingAFormField()
    {
        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'messageBody' => '<test>',
            ]
        );
        $formOutput = $this->subject->render();

        self::assertStringContainsString(
            '&lt;test&gt;',
            $formOutput
        );
    }

    /**
     * @test
     */
    public function renderFormContainsCancelButton()
    {
        self::assertStringContainsString(
            '<input type="button" value="' .
            $this->getLanguageService()->getLL('eventMailForm_backButton') .
            '" class="backButton"' .
            ' onclick="window.location=window.location" />',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsErrorMessageIfFormWasSubmittedWithEmptySubjectField()
    {
        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => '',
            ]
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('eventMailForm_error_subjectMustNotBeEmpty'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsErrorMessageIfFormWasSubmittedWithEmptyMessageField()
    {
        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'messageBody' => '',
            ]
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('eventMailForm_error_messageBodyMustNotBeEmpty'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsSubjectFieldPrefilledByUserInputIfFormIsReRendered()
    {
        $this->subject->setPostData(
            [
                'action' => 'sendForm',
                'isSubmitted' => '1',
                'subject' => 'foo bar',
            ]
        );
        $this->subject->markAsIncomplete();

        self::assertStringContainsString(
            'foo bar',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderEncodesHtmlSpecialCharsInSubjectField()
    {
        $this->subject->setPostData(
            [
                'action' => 'sendForm',
                'isSubmitted' => '1',
                'subject' => '<foo> & "bar"',
            ]
        );
        $this->subject->markAsIncomplete();
        self::assertStringContainsString(
            '&lt;foo&gt; &amp; &quot;bar&quot;',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsMessageFieldPrefilledByUserInputIfFormIsReRendered()
    {
        $this->subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'messageBody' => 'foo bar',
            ]
        );
        $this->subject->markAsIncomplete();

        self::assertStringContainsString(
            'foo bar',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsHiddenFieldWithVariableEventUid()
    {
        self::assertStringContainsString(
            '<input type="hidden" name="eventUid" value="' . $this->eventUid . '" />',
            $this->subject->render()
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

        $subject = new TestingEventMailForm(
            $this->eventUid
        );

        self::assertStringContainsString(
            'FooBar',
            $subject->getInitialValue('subject')
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
            $this->subject->getInitialValue('subject')
        );
    }

    /**
     * @test
     */
    public function getInitialValueForFooThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'There is no initial value for the field "foo" defined.'
        );

        $this->subject->getInitialValue('foo');
    }

    ////////////////////////////////////////
    // Tests concerning the error messages
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getErrorMessageForIncompleteFormAndNoStoredMessageReturnsEmptyString()
    {
        $this->subject->markAsIncomplete();

        self::assertEquals(
            '',
            $this->subject->getErrorMessage('subject')
        );
    }

    /**
     * @test
     */
    public function getErrorMessageForCompleteFormAndStoredMessageReturnsStoredMessage()
    {
        $this->subject->setErrorMessage('subject', 'Foo');

        self::assertStringContainsString(
            'Foo',
            $this->subject->getErrorMessage('subject')
        );
    }

    /**
     * @test
     */
    public function getErrorMessageForInCompleteFormAndStoredMessageReturnsThisErrorMessage()
    {
        $this->subject->markAsIncomplete();
        $this->subject->setErrorMessage('subject', 'Foo');

        self::assertStringContainsString(
            'Foo',
            $this->subject->getErrorMessage('subject')
        );
    }

    /**
     * @test
     */
    public function setErrorMessageForAlreadySetErrorMessageAppendsNewMessage()
    {
        $this->subject->markAsIncomplete();
        $this->subject->setErrorMessage('subject', 'Foo');
        $this->subject->setErrorMessage('subject', 'Bar');
        $errorMessage = $this->subject->getErrorMessage('subject');

        self::assertStringContainsString(
            'Foo',
            $errorMessage
        );
        self::assertStringContainsString(
            'Bar',
            $errorMessage
        );
    }
}
