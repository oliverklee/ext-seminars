<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Session\FakeSession;
use OliverKlee\Oelib\Session\Session;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\RegistrationForm;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\AbstractEditor
 * @covers \OliverKlee\Seminars\FrontEnd\RegistrationForm
 */
final class RegistrationFormTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var array<string, positive-int>
     */
    private const CONFIGURATION = [
        'thankYouAfterRegistrationPID' => 3,
        'pageToShowAfterUnregistrationPID' => 4,
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/rn_base',
        'typo3conf/ext/mkforms',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var RegistrationForm
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var ContentObjectRenderer
     */
    private $contentObject;

    /**
     * @var FakeSession
     */
    private $session;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/Pages.xml');

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd(1);
        $this->contentObject = new ContentObjectRenderer();
        $this->initializeBackEndLanguage();

        $this->session = new FakeSession();
        Session::setInstance(Session::TYPE_USER, $this->session);

        $this->subject = new RegistrationForm([], $this->contentObject);
        $this->subject->setTestMode();
    }

    protected function tearDown(): void
    {
        Session::purgeInstances();
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUpWithoutDatabase();
        }
    }

    /**
     * @test
     */
    public function getSeminarWithoutEventThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Please set a proper seminar object via $this->setSeminar().');
        $this->expectExceptionCode(1333293187);

        $this->subject->getSeminar();
    }

    /**
     * @test
     */
    public function setSeminarSetsSeminar(): void
    {
        $event = new LegacyEvent();
        $this->subject->setSeminar($event);

        self::assertSame($event, $this->subject->getSeminar());
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithoutCheckboxesAndCheckboxesFieldDisabledReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => ''];
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $event = LegacyEvent::fromUid(1);
        $subject->setSeminar($event);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithCheckboxesReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $subject = new RegistrationForm([], $this->contentObject);

        $event = LegacyEvent::fromUid(2);
        $subject->setSeminar($event);

        self::assertTrue($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function processRegistrationWithoutEventThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Please set a proper seminar object via $this->setSeminar().');
        $this->expectExceptionCode(1333293187);

        $this->subject->processRegistration([]);
    }

    /**
     * @test
     */
    public function processRegistrationWithoutAdditionalAttendeesNotCreatesAdditionalUsers(): void
    {
        $configuration = ['createAdditionalAttendeesAsFrontEndUsers' => 1];
        $subject = new RegistrationForm($configuration, $this->contentObject);
        $subject->setTestMode();
        $subject->setFormConfiguration(['form.' => []]);

        $event = LegacyEvent::fromData(['needs_registration' => 1]);
        $subject->setSeminar($event);

        $subject->processRegistration([]);

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'fe_users')
        );
    }

    // Tests concerning getThankYouAfterRegistrationUrl

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlReturnsUrlStartingWithHttp(): void
    {
        $subject = new RegistrationForm(self::CONFIGURATION, $this->contentObject);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertRegExp('/^http:\\/\\/./', $result);
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithoutSendParametersNotContainsShowSeminarUid(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToThankYouAfterRegistrationPageUrl'] = false;
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertStringNotContainsString('showUid', $result);
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithSendParametersContainsShowSeminarUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/SingleEvent.xml');

        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToThankYouAfterRegistrationPageUrl'] = true;
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 1;
        $event = LegacyEvent::fromUid($eventUid);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $subject->setSeminar($event);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertStringContainsString('showUid', $result);
        self::assertStringContainsString('=' . $eventUid, $result);
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithSendParametersEncodesBracketsInUrl(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/SingleEvent.xml');

        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToThankYouAfterRegistrationPageUrl'] = true;
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 1;
        $event = LegacyEvent::fromUid($eventUid);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $subject->setSeminar($event);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertStringContainsString('%5BshowUid%5D', $result);
        self::assertStringNotContainsString('[showUid]', $result);
    }

    // Tests concerning getPageToShowAfterUnregistrationUrl

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlReturnsUrlStartingWithHttp(): void
    {
        $subject = new RegistrationForm(self::CONFIGURATION, $this->contentObject);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertRegExp('/^http:\\/\\/./', $result);
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlWithoutSendParametersNotContainsShowSeminarUid(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToPageToShowAfterUnregistrationUrl'] = false;
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertStringNotContainsString('showUid', $result);
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlWithSendParametersContainsShowSeminarUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/SingleEvent.xml');

        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToPageToShowAfterUnregistrationUrl'] = true;
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 1;
        $event = LegacyEvent::fromUid($eventUid);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $subject->setSeminar($event);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertStringContainsString('showUid', $result);
        self::assertStringContainsString('=' . $eventUid, $result);
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlWithSendParametersEncodesBracketsInUrl(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/SingleEvent.xml');

        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToPageToShowAfterUnregistrationUrl'] = true;
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 1;
        $event = LegacyEvent::fromUid($eventUid);
        self::assertInstanceOf(LegacyEvent::class, $event);
        $subject->setSeminar($event);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertStringContainsString('%5BshowUid%5D', $result);
        self::assertStringNotContainsString('[showUid]', $result);
    }
}
