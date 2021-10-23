<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\RegistrationForm;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\RegistrationForm
 */
final class RegistrationFormTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/oelib',
        'typo3conf/ext/rn_base',
        'typo3conf/ext/mkforms',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var RegistrationForm
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var ContentObjectRenderer
     */
    private $contentObject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();
        $this->contentObject = new ContentObjectRenderer();
        $this->initializeBackEndLanguage();

        $this->subject = new RegistrationForm([], $this->contentObject);
        $this->subject->setTestMode();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();
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
    public function populateCheckboxesForEventWithoutCheckboxesReturnsEmptyArray(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $event = LegacyEvent::fromUid(1);
        $this->subject->setSeminar($event);

        $result = $this->subject->populateCheckboxes();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function populateCheckboxesForEventWithCheckboxesReturnsCheckboxes(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $event = LegacyEvent::fromUid(2);
        $this->subject->setSeminar($event);

        $result = $this->subject->populateCheckboxes();

        $expected = [['caption' => 'Checkbox 1', 'value' => 1]];
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function populateCheckboxesForEventWithCheckboxesReturnsCheckboxesOrderedBySorting(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $event = LegacyEvent::fromUid(3);
        $this->subject->setSeminar($event);

        $result = $this->subject->populateCheckboxes();

        $expected = [['caption' => 'Checkbox 2', 'value' => 2], ['caption' => 'Checkbox 1', 'value' => 1]];
        self::assertSame($expected, $result);
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
    public function hasCheckboxesForEventWithoutCheckboxesAndCheckboxesFieldEnabledReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => 'checkboxes'];
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $event = LegacyEvent::fromUid(1);
        $subject->setSeminar($event);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithCheckboxesAndCheckboxesFieldDisabledReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => ''];
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $event = LegacyEvent::fromUid(2);
        $subject->setSeminar($event);

        self::assertFalse($subject->hasCheckboxes());
    }

    /**
     * @test
     */
    public function hasCheckboxesForEventWithCheckboxesAndCheckboxesFieldEnabledReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationEditor/EventsWithCheckboxes.xml');

        $configuration = ['showRegistrationFields' => 'checkboxes'];
        $subject = new RegistrationForm($configuration, $this->contentObject);

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

    /**
     * @test
     */
    public function processRegistrationWithAdditionalAttendeeCreatesAdditionalUser(): void
    {
        $email = 'max@example.com';
        $configuration = ['createAdditionalAttendeesAsFrontEndUsers' => 1];
        $subject = new RegistrationForm($configuration, $this->contentObject);
        $subject->setTestMode();
        $subject->setFormConfiguration(['form.' => []]);

        $event = LegacyEvent::fromData(['needs_registration' => 1]);
        $subject->setSeminar($event);

        $attendeeData = \json_encode([['Max', 'Maxowski', 'developer', $email]]);
        $subject->setFakedFormValue('structured_attendees_names', $attendeeData);

        $subject->processRegistration([]);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'fe_users', 'username = "' . $email . '"')
        );
    }
}
