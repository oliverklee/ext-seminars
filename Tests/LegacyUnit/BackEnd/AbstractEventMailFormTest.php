<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingEventMailForm;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Information\Typo3Version;

final class AbstractEventMailFormTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var TestingEventMailForm
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * UID of a dummy event record
     *
     * @var int
     */
    private $eventUid;

    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');
        PageFinder::getInstance()->setPageUid($this->testingFramework->createSystemFolder());

        $configuration = new DummyConfiguration(['dateFormatYMD' => '%d.%m.%Y']);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

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
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUp();
        }
        $this->restoreOriginalEnvironment();
    }

    ///////////////////////////////////////////////////
    // Tests regarding the error handling of the form
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderThrowsExceptionForInvalidEventUid(): void
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
    public function formActionContainsCurrentPage(): void
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
    public function renderContainsEventTitleInSubjectFieldForNewForm(): void
    {
        self::assertStringContainsString(
            'Dummy Event',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsPrefilledBodyField(): void
    {
        self::assertStringContainsString(
            $this->translate('testForm_prefillField_messageBody'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsBodyFieldWithIntroduction(): void
    {
        self::assertStringContainsString(
            \sprintf(
                $this->translate('testForm_prefillField_introduction'),
                \htmlspecialchars('"Dummy Event"', ENT_QUOTES | ENT_HTML5)
            ),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotPrefillsSubjectFieldIfEmptyStringWasSentViaPost(): void
    {
        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
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
    public function renderContainsEventDateInSubjectFieldForNewFormAndEventWithBeginDate(): void
    {
        self::assertStringContainsString(
            strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderSanitizesPostDataWhenPreFillingAFormField(): void
    {
        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
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
    public function renderContainsErrorMessageIfFormWasSubmittedWithEmptySubjectField(): void
    {
        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => '',
            ]
        );

        self::assertStringContainsString(
            $this->translate('eventMailForm_error_subjectMustNotBeEmpty'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsErrorMessageIfFormWasSubmittedWithEmptyMessageField(): void
    {
        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'messageBody' => '',
            ]
        );

        self::assertStringContainsString(
            $this->translate('eventMailForm_error_messageBodyMustNotBeEmpty'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsSubjectFieldPrefilledByUserInputIfFormIsReRendered(): void
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
    public function renderEncodesHtmlSpecialCharsInSubjectField(): void
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
    public function renderContainsMessageFieldPrefilledByUserInputIfFormIsReRendered(): void
    {
        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
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
    public function renderContainsHiddenFieldWithVariableEventUid(): void
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
    public function getInitialValueForSubjectAppendsEventTitle(): void
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
    public function getInitialValueForSubjectAppendsEventDate(): void
    {
        $beginDate = strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertStringContainsString(
            $beginDate,
            $this->subject->getInitialValue('subject')
        );
    }

    /**
     * @test
     */
    public function getInitialValueForFooThrowsException(): void
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
    public function getErrorMessageForIncompleteFormAndNoStoredMessageReturnsEmptyString(): void
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
    public function getErrorMessageForCompleteFormAndStoredMessageReturnsStoredMessage(): void
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
    public function getErrorMessageForInCompleteFormAndStoredMessageReturnsThisErrorMessage(): void
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
    public function setErrorMessageForAlreadySetErrorMessageAppendsNewMessage(): void
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
