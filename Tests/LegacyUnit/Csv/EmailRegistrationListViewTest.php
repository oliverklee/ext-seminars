<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Csv\EmailRegistrationListView;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class EmailRegistrationListViewTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var EmailRegistrationListView
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * UID of a test event record
     *
     * @var int
     */
    private $eventUid = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->getLanguageService()->includeLLFile('EXT:core/Resources/Private/Language/locallang_general.xlf');
        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');

        $this->testingFramework = new TestingFramework('tx_seminars');

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);

        $pageUid = $this->testingFramework->createSystemFolder();
        $this->eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $pageUid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        $this->subject = new EmailRegistrationListView();
        $this->subject->setEventUid($this->eventUid);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        parent::tearDown();
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @test
     */
    public function renderCanContainOneRegistrationUid(): void
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromFeUserForEmailCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');
        $this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertStringContainsString(
            (string)$registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotContainsFrontEndUserFieldsForDownload(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';

        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'first_name');
        $this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'last_name');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['first_name' => $firstName, 'last_name' => $lastName]
                ),
            ]
        );

        self::assertStringNotContainsString(
            $firstName,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsFrontEndUserFieldsForEmail(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';

        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'first_name');
        $this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'last_name');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['first_name' => $firstName, 'last_name' => $lastName]
                ),
            ]
        );

        self::assertStringContainsString(
            $lastName,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotContainsRegistrationFieldsForDownload(): void
    {
        $knownFrom = 'Google';
        $notes = 'Looking forward to the event!';

        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'known_from');
        $this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'notes');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'known_from' => $knownFrom,
                'notes' => $notes,
            ]
        );

        self::assertStringNotContainsString(
            $knownFrom,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsRegistrationFieldsForEmail(): void
    {
        $knownFrom = 'Google';
        $notes = 'Looking forward to the event!';

        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'known_from');
        $this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'notes');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'known_from' => $knownFrom,
                'notes' => $notes,
            ]
        );

        self::assertStringContainsString(
            $notes,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForQueueRegistrationsNotAllowedForEmailNotContainsRegistrationOnQueue(): void
    {
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', false);
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInCSV', true);

        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');
        $this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'registration_queue' => true,
            ]
        );

        self::assertStringNotContainsString(
            (string)$registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForQueueRegistrationsAllowedForEmailNotContainsRegistrationOnQueue(): void
    {
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', true);
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInCSV', false);

        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');
        $this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'registration_queue' => true,
            ]
        );

        self::assertStringContainsString(
            (string)$registrationUid,
            $this->subject->render()
        );
    }
}
