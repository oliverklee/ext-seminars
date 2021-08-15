<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CsvDownloaderTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var CsvDownloader
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * PID of the system folder in which we store our test data
     *
     * @var int
     */
    private $pid = 0;

    /**
     * UID of a test event record
     *
     * @var int
     */
    private $eventUid = 0;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->pid = $this->testingFramework->createSystemFolder();
        $this->eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        $this->configuration->setData(['charsetForCsv' => 'utf-8']);

        $this->subject = new CsvDownloader();
        $this->subject->init([]);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->restoreOriginalEnvironment();
    }

    // Utility functions

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Retrieves the localization for the given locallang key and then strips the trailing colon from it.
     *
     * @param string $key the locallang key with the localization to remove the trailing colon from
     *
     * @return string locallang string with the removed trailing colon, will not be empty
     */
    private function localizeAndRemoveColon(string $key): string
    {
        return \rtrim($this->getLanguageService()->getLL($key), ':');
    }

    // Tests for the CSV export of events.

    /**
     * @test
     */
    public function createListOfEventsForZeroPidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->createListOfEvents(0);
    }

    /**
     * @test
     */
    public function createListOfEventsForNegativePidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->createListOfEvents(-2);
    }

    /**
     * @test
     */
    public function createListOfEventsForZeroRecordsHasOnlyHeaderLine()
    {
        $pid = $this->testingFramework->createSystemFolder();
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid,title');

        self::assertSame(
            $this->localizeAndRemoveColon('tx_seminars_seminars.uid') . ';' .
            $this->localizeAndRemoveColon('tx_seminars_seminars.title') . CRLF,
            $this->subject->createListOfEvents($pid)
        );
    }

    /**
     * @test
     */
    public function createListOfEventsCanContainOneEventUid()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        self::assertContains(
            (string)$this->eventUid,
            $this->subject->createListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createListOfEventsCanContainEventFromSubFolder()
    {
        $subFolderPid = $this->testingFramework->createSystemFolder($this->pid);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $subFolderPid,
                'title' => 'another event',
            ]
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            'another event',
            $this->subject->createListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function mainCanExportOneEventUid()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $this->subject->piVars['table'] = 'tx_seminars_seminars';
        $this->subject->piVars['pid'] = $this->pid;

        self::assertContains(
            (string)$this->eventUid,
            $this->subject->main()
        );
    }

    /**
     * @test
     */
    public function createListOfEventsCanContainTwoEventUids()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $secondEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
            ]
        );
        $eventList = $this->subject->createListOfEvents($this->pid);

        self::assertContains(
            (string)$this->eventUid,
            $eventList
        );
        self::assertContains(
            (string)$secondEventUid,
            $eventList
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsCanContainTwoEventUids()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $secondEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
            ]
        );

        $output = $this->subject->createAndOutputListOfEvents($this->pid);

        self::assertContains(
            (string)$this->eventUid,
            $output
        );
        self::assertContains(
            (string)$secondEventUid,
            $output
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsSeparatesLinesWithCarriageReturnsAndLineFeeds()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $secondEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
            ]
        );

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_seminars.uid') .
            CRLF . $this->eventUid . CRLF . $secondEventUid . CRLF,
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsHasResultEndingWithCarriageReturnAndLineFeed()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
            ]
        );

        self::assertRegExp(
            '/\\r\\n$/',
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsDoesNotWrapRegularValuesWithDoubleQuotes()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['title' => 'bar']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertNotContains(
            '"bar"',
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsEscapesDoubleQuotes()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['description' => 'foo " bar']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description');

        self::assertContains(
            'foo "" bar',
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsDoesWrapValuesWithLineFeedsInDoubleQuotes()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['title' => 'foo' . LF . 'bar']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            '"foo' . LF . 'bar"',
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsDoesWrapValuesWithDoubleQuotesInDoubleQuotes()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['title' => 'foo " bar']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            '"foo "" bar"',
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsDoesWrapValuesWithSemicolonsInDoubleQuotes()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['title' => 'foo ; bar']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            '"foo ; bar"',
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsSeparatesValuesWithSemicolons()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['description' => 'foo', 'title' => 'bar']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

        self::assertContains(
            'foo;bar',
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsDoesNotWrapHeaderFieldsInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

        $eventList = $this->subject->createAndOutputListOfEvents($this->pid);
        $description = $this->localizeAndRemoveColon(
            'tx_seminars_seminars.description'
        );

        self::assertContains(
            $description,
            $eventList
        );
        self::assertNotContains(
            '"' . $description . '"',
            $eventList
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsSeparatesHeaderFieldsWithSemicolons()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_seminars.description') .
            ';' . $this->localizeAndRemoveColon('tx_seminars_seminars.title'),
            $this->subject->createAndOutputListOfEvents($this->pid)
        );
    }

    // Tests for the CSV export of registrations.

    /**
     * @test
     */
    public function createListOfRegistrationsCanContainOneRegistrationUid()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertContains(
            (string)$registrationUid,
            $this->subject->createListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsCanContainOneRegistrationUidOfHiddenEvent()
    {
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->eventUid, ['hidden' => 1]);

        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertContains(
            (string)$registrationUid,
            $this->subject->createListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsCanContainOneRegistrationUidOfEventWithPastEndTime()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->eventUid,
            ['endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000]
        );

        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertContains(
            (string)$registrationUid,
            $this->subject->createListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsCanContainLocalizedRegisteredThemselves()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'registered_themselves');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'registered_themselves' => 1,
            ]
        );

        self::assertContains(
            $this->localizeAndRemoveColon(
                'tx_seminars_attendances.registered_themselves'
            ),
            $this->subject->createListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsCanContainLocalizedCompanyHeading()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'company');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo',
            ]
        );

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_attendances.company'),
            $this->subject->createListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsCanContainCompanyContent()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'company');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo bar inc.',
            ]
        );

        self::assertContains(
            'foo bar inc.',
            $this->subject->createListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForFrontEndModeCanExportRegistrationsBelongingToAnEvent()
    {
        $this->subject->setTypo3Mode('FE');
        $globalBackEndUser = $GLOBALS['BE_USER'];
        $GLOBALS['BE_USER'] = null;

        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'company');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo bar inc.',
            ]
        );

        $result = $this->subject->createListOfRegistrations($this->eventUid);

        $GLOBALS['BE_USER'] = $globalBackEndUser;

        self::assertContains(
            'foo bar inc.',
            $result
        );
    }

    // Tests concerning the main function

    /**
     * @test
     */
    public function mainCanExportOneRegistrationUid()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->piVars['table'] = 'tx_seminars_attendances';
        $this->subject->piVars['eventUid'] = $this->eventUid;

        self::assertContains(
            (string)$registrationUid,
            $this->subject->main()
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsCanContainTwoRegistrationUids()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $firstRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $secondRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'] + 1,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $registrationsList
            = $this->subject->createListOfRegistrations($this->eventUid);

        self::assertContains(
            (string)$firstRegistrationUid,
            $registrationsList
        );
        self::assertContains(
            (string)$secondRegistrationUid,
            $registrationsList
        );
    }

    /**
     * @test
     */
    public function mainCanKeepEventDataInUtf8()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'title' => 'Schöne Bären führen',
            ]
        );

        $this->subject->piVars['table'] = 'tx_seminars_seminars';
        $this->subject->piVars['pid'] = $this->pid;

        self::assertContains(
            'Schöne Bären führen',
            $this->subject->main()
        );
    }

    /**
     * @test
     */
    public function mainCanChangeEventDataToIso885915()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'title' => 'Schöne Bären führen',
            ]
        );

        $this->subject->piVars['table'] = 'tx_seminars_seminars';
        $this->subject->piVars['pid'] = $this->pid;

        $this->configuration->setAsString('charsetForCsv', 'iso-8859-15');

        self::assertContains(
            'Sch' . chr(246) . 'ne B' . chr(228) . 'ren f' . chr(252) . 'hren',
            $this->subject->main()
        );
    }

    /**
     * @test
     */
    public function mainCanKeepRegistrationDataInUtf8()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'title');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->pid,
                'title' => 'Schöne Bären führen',
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->piVars['table'] = 'tx_seminars_attendances';
        $this->subject->piVars['pid'] = $this->pid;

        self::assertContains(
            'Schöne Bären führen',
            $this->subject->main()
        );
    }

    /**
     * @test
     */
    public function mainCanChangeRegistrationDataToIso885915()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'title');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->pid,
                'title' => 'Schöne Bären führen',
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->piVars['table'] = 'tx_seminars_attendances';
        $this->subject->piVars['pid'] = $this->pid;

        $this->configuration->setAsString('charsetForCsv', 'iso-8859-15');

        self::assertContains(
            'Sch' . chr(246) . 'ne B' . chr(228) . 'ren f' . chr(252) . 'hren',
            $this->subject->main()
        );
    }

    // Tests concerning createAndOutputListOfRegistrations

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsCanContainTwoRegistrationUids()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $firstRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $secondRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'] + 1,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $registrationsList = $this->subject->createAndOutputListOfRegistrations($this->eventUid);
        self::assertContains(
            (string)$firstRegistrationUid,
            $registrationsList
        );
        self::assertContains(
            (string)$secondRegistrationUid,
            $registrationsList
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsCanContainNameOfUser()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', '');

        $frontEndUserUid = $this->testingFramework->createFrontEndUser('', ['name' => 'foo_user']);
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $frontEndUserUid,
            ]
        );

        self::assertContains(
            'foo_user',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsDoesNotContainUidOfRegistrationWithDeletedUser()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $frontEndUserUid = $this->testingFramework->createFrontEndUser('', ['deleted' => 1]);
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $frontEndUserUid,
            ]
        );

        self::assertNotContains(
            (string)$registrationUid,
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsDoesNotContainUidOfRegistrationWithInexistentUser()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->getAutoIncrement('fe_users'),
            ]
        );

        self::assertNotContains(
            (string)$registrationUid,
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsSeparatesLinesWithCarriageReturnAndLineFeed()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $firstRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => 1,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $secondRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => 2,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertContains(
            CRLF . $firstRegistrationUid . CRLF .
            $secondRegistrationUid . CRLF,
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsHasResultThatEndsWithCarriageReturnAndLineFeed()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertRegExp(
            '/\\r\\n$/',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsEscapesDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid,address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo " bar',
            ]
        );

        self::assertContains(
            'foo "" bar',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsDoesNotEscapeRegularValues()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo " bar',
            ]
        );

        self::assertNotContains(
            '"foo bar"',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsWrapsValuesWithSemicolonsInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo ; bar',
            ]
        );

        self::assertContains(
            '"foo ; bar"',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsWrapsValuesWithLineFeedsInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo' . LF . 'bar',
            ]
        );

        self::assertContains(
            '"foo' . LF . 'bar"',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsWrapsValuesWithDoubleQuotesInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo " bar',
            ]
        );

        self::assertContains(
            '"foo "" bar"',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsSeparatesTwoValuesWithSemicolons()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address,title');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'title' => 'test',
            ]
        );

        self::assertContains(
            'foo;test',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsDoesNotWrapHeaderFieldsInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $registrationsList = $this->subject->createAndOutputListOfRegistrations($this->eventUid);
        $localizedAddress = $this->localizeAndRemoveColon('tx_seminars_attendances.address');

        self::assertContains(
            $localizedAddress,
            $registrationsList
        );
        self::assertNotContains(
            '"' . $localizedAddress . '"',
            $registrationsList
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsSeparatesHeaderFieldsWithSemicolons()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address,title');

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_attendances.address') .
            ';' . $this->localizeAndRemoveColon('tx_seminars_attendances.title'),
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForConfigurationAttendanceCsvFieldsEmptyDoesNotAddSemicolonOnEndOfHeader()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', '');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');

        self::assertNotContains(
            'name;',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForConfigurationFeUserCsvFieldsEmptyDoesNotAddSemicolonAtBeginningOfHeader()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');

        self::assertNotContains(
            ';address',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForBothConfigurationFieldsEmptyReturnsCrLf()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', '');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');

        self::assertSame(
            CRLF,
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOuptutListOfRegistrationsForNoEventUidGivenReturnsRegistrationsOnCurrentPage()
    {
        $this->subject->piVars['pid'] = $this->pid;
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'pid' => $this->pid,
            ]
        );

        self::assertContains(
            'foo',
            $this->subject->createAndOutputListOfRegistrations()
        );
    }

    /**
     * @test
     */
    public function createAndOuptutListOfRegistrationsForNoEventUidGivenDoesNotReturnRegistrationsOnOtherPage()
    {
        $this->subject->piVars['pid'] = $this->pid;
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'pid' => $this->pid + 1,
            ]
        );

        self::assertNotContains(
            'foo',
            $this->subject->createAndOutputListOfRegistrations()
        );
    }

    /**
     * @test
     */
    public function createAndOuptutListOfRegistrationsForNoEventUidGivenReturnsRegistrationsOnSubpageOfCurrentPage()
    {
        $this->subject->piVars['pid'] = $this->pid;
        $subpagePid = $this->testingFramework->createSystemFolder($this->pid);
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'pid' => $subpagePid,
            ]
        );

        self::assertContains(
            'foo',
            $this->subject->createAndOutputListOfRegistrations()
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForNonExistingEventUidAddsNotFoundStatusToHeader()
    {
        $this->subject->createAndOutputListOfRegistrations(
            $this->testingFramework->getAutoIncrement('tx_seminars_seminars')
        );

        self::assertContains('404', $this->headerProxy->getLastAddedHeader());
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForNoGivenEventUidAndFeModeAddsAccessForbiddenStatusToHeader()
    {
        $this->subject->setTypo3Mode('FE');
        $this->subject->createAndOutputListOfRegistrations();

        self::assertContains('403', $this->headerProxy->getLastAddedHeader());
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForEventUidGivenSetsPageContentTypeToCsv()
    {
        $this->subject->createAndOutputListOfRegistrations($this->eventUid);

        self::assertContains(
            'Content-type: text/csv; header=present; charset=utf-8',
            $this->headerProxy->getAllAddedHeaders()
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForNoEventUidGivenSetsPageContentTypeToCsv()
    {
        $this->subject->piVars['pid'] = $this->pid;
        $this->subject->createAndOutputListOfRegistrations();

        self::assertContains(
            'Content-type: text/csv; header=present; charset=utf-8',
            $this->headerProxy->getAllAddedHeaders()
        );
    }

    // Tests concerning the export mode and the configuration

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForWebModeNotUsesUserFieldsFromEmailConfiguration()
    {
        $this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'email');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');

        $frontEndUserUid = $this->testingFramework->createFrontEndUser('', ['email' => 'foo@bar.com']);
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $this->eventUid, 'user' => $frontEndUserUid]
        );

        self::assertNotContains(
            'foo@bar.com',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForWebModeNotUsesRegistrationFieldsFromEmailConfiguration()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'bank_name');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', '');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'bank_name' => 'foo bank',
            ]
        );

        self::assertNotContains(
            'foo bank',
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForWebModeNotUsesRegistrationsOnQueueSettingFromConfiguration()
    {
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', true);
        $this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'uid');
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInCsv', false);
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $queueUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'bank_name' => 'foo bank',
                'registration_queue' => 1,
            ]
        );

        self::assertNotContains(
            (string)$queueUid,
            $this->subject->createAndOutputListOfRegistrations($this->eventUid)
        );
    }
}
