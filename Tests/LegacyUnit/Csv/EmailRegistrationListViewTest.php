<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Csv\EmailRegistrationListView;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class EmailRegistrationListViewTest extends TestCase
{
    /**
     * @var EmailRegistrationListView
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * UID of a test event record
     *
     * @var int
     */
    private $eventUid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_general.xlf');

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new \Tx_Oelib_Configuration());
        $this->configuration = new \Tx_Oelib_Configuration();
        $this->configuration->setData(['charsetForCsv' => 'utf-8']);
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

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Retrieves the localization for the given locallang key and then strips the trailing colon from the localization.
     *
     * @param string $locallangKey
     *        the locallang key with the localization to remove the trailing colon from, must not be empty and the localization
     *        must have a trailing colon
     *
     * @return string locallang string with the removed trailing colon, will not be empty
     */
    protected function localizeAndRemoveColon(string $locallangKey): string
    {
        return \rtrim($this->getLanguageService()->getLL($locallangKey), ':');
    }

    /**
     * @test
     */
    public function renderCanContainOneRegistrationUid()
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

        self::assertContains(
            (string)$registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotContainsFrontEndUserFieldsForDownload()
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

        self::assertNotContains(
            $firstName,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsFrontEndUserFieldsForEmail()
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

        self::assertContains(
            $lastName,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotContainsRegistrationFieldsForDownload()
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

        self::assertNotContains(
            $knownFrom,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderContainsRegistrationFieldsForEmail()
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

        self::assertContains(
            $notes,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForQueueRegistrationsNotAllowedForEmailNotContainsRegistrationOnQueue()
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

        self::assertNotContains(
            (string)$registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForQueueRegistrationsAllowedForEmailNotContainsRegistrationOnQueue()
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

        self::assertContains(
            (string)$registrationUid,
            $this->subject->render()
        );
    }
}
