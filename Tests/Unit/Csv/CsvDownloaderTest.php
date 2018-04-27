<?php

use OliverKlee\Seminars\Tests\Unit\Support\Traits\BackEndTestsTrait;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Csv_CsvDownloaderTest extends Tx_Phpunit_TestCase
{
    use BackEndTestsTrait;

    /**
     * @var Tx_Seminars_Csv_CsvDownloader
     */
    protected $fixture = null;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * PID of the system folder in which we store our test data
     *
     * @var int
     */
    protected $pid = 0;

    /**
     * UID of a test event record
     *
     * @var int
     */
    protected $eventUid = 0;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->pid = $this->testingFramework->createSystemFolder();
        $this->eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        $this->configuration->setData(['charsetForCsv' => 'utf-8']);

        $this->fixture = new Tx_Seminars_Csv_CsvDownloader();
        $this->fixture->init([]);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->restoreOriginalEnvironment();
    }

    /*
     * Utility functions
     */

    /**
     * Retrieves the localization for the given locallang key and then strips
     * the trailing colon from the localization
     *
     * @param string $locallangKey
     *        the locallang key with the localization to remove the trailing
     *        colon from, must not be empty and the localization must have a
     *        trailing colon
     *
     * @return string locallang string with the removed trailing colon, will not
     *                be empty
     */
    private function localizeAndRemoveColon($locallangKey)
    {
        return substr($GLOBALS['LANG']->getLL($locallangKey), 0, -1);
    }

    /*
     * Tests for the CSV export of events.
     */

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createListOfEventsForZeroPidThrowsException()
    {
        $this->fixture->createListOfEvents(0);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createListOfEventsForNegativePidThrowsException()
    {
        $this->fixture->createListOfEvents(-2);
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
            $this->fixture->createListOfEvents($pid)
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
            $this->fixture->createListOfEvents($this->pid)
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
            $this->fixture->createListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function mainCanExportOneEventUid()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $this->fixture->piVars['table'] = 'tx_seminars_seminars';
        $this->fixture->piVars['pid'] = $this->pid;

        self::assertContains(
            (string)$this->eventUid,
            $this->fixture->main()
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
        $eventList = $this->fixture->createListOfEvents($this->pid);

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

        $output = $this->fixture->createAndOutputListOfEvents($this->pid);

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
            $this->fixture->createAndOutputListOfEvents($this->pid)
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
            $this->fixture->createAndOutputListOfEvents($this->pid)
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
            $this->fixture->createAndOutputListOfEvents($this->pid)
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
            $this->fixture->createAndOutputListOfEvents($this->pid)
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
            $this->fixture->createAndOutputListOfEvents($this->pid)
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
            $this->fixture->createAndOutputListOfEvents($this->pid)
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
            $this->fixture->createAndOutputListOfEvents($this->pid)
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
            $this->fixture->createAndOutputListOfEvents($this->pid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfEventsDoesNotWrapHeadlineFieldsInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

        $eventList = $this->fixture->createAndOutputListOfEvents($this->pid);
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
    public function createAndOutputListOfEventsSeparatesHeadlineFieldsWithSemicolons()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_seminars.description') .
                ';' . $this->localizeAndRemoveColon('tx_seminars_seminars.title'),
            $this->fixture->createAndOutputListOfEvents($this->pid)
        );
    }

    /*
     * Tests for the CSV export of registrations.
     */

    /**
     * @test
     */
    public function createListOfRegistrationsIsEmptyForNonExistentEvent()
    {
        self::assertSame(
            '',
            $this->fixture->createListOfRegistrations($this->eventUid + 9999)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForZeroRecordsHasOnlyHeaderLine()
    {
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        self::assertSame(
            $this->localizeAndRemoveColon('LGL.name') . ';' .
                $this->localizeAndRemoveColon('tx_seminars_attendances.uid') . CRLF,
            $this->fixture->createListOfRegistrations($this->eventUid)
        );
    }

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
            $this->fixture->createListOfRegistrations($this->eventUid)
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
            $this->fixture->createListOfRegistrations($this->eventUid)
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
            $this->fixture->createListOfRegistrations($this->eventUid)
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
            $this->fixture->createListOfRegistrations($this->eventUid)
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
            $this->fixture->createListOfRegistrations($this->eventUid)
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
            $this->fixture->createListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForFrontEndModeCanExportRegistrationsBelongingToAnEvent()
    {
        $this->fixture->setTypo3Mode('FE');
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

        $result = $this->fixture->createListOfRegistrations($this->eventUid);

        $GLOBALS['BE_USER'] = $globalBackEndUser;

        self::assertContains(
            'foo bar inc.',
            $result
        );
    }

    /*
     * Tests concerning the main function
     */

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

        $this->fixture->piVars['table'] = 'tx_seminars_attendances';
        $this->fixture->piVars['eventUid'] = $this->eventUid;

        self::assertContains(
            (string)$registrationUid,
            $this->fixture->main()
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
                'crdate' => ($GLOBALS['SIM_EXEC_TIME'] + 1),
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $registrationsList
            = $this->fixture->createListOfRegistrations($this->eventUid);

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

        $this->fixture->piVars['table'] = 'tx_seminars_seminars';
        $this->fixture->piVars['pid'] = $this->pid;

        self::assertContains(
            'Schöne Bären führen',
            $this->fixture->main()
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

        $this->fixture->piVars['table'] = 'tx_seminars_seminars';
        $this->fixture->piVars['pid'] = $this->pid;

        $this->configuration->setAsString('charsetForCsv', 'iso-8859-15');

        self::assertContains(
            'Sch' . chr(246) . 'ne B' . chr(228) . 'ren f' . chr(252) . 'hren',
            $this->fixture->main()
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

        $this->fixture->piVars['table'] = 'tx_seminars_attendances';
        $this->fixture->piVars['pid'] = $this->pid;

        self::assertContains(
            'Schöne Bären führen',
            $this->fixture->main()
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

        $this->fixture->piVars['table'] = 'tx_seminars_attendances';
        $this->fixture->piVars['pid'] = $this->pid;

        $this->configuration->setAsString('charsetForCsv', 'iso-8859-15');

        self::assertContains(
            'Sch' . chr(246) . 'ne B' . chr(228) . 'ren f' . chr(252) . 'hren',
            $this->fixture->main()
        );
    }

    /*
     * Tests concerning createAndOutputListOfRegistrations
     */

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForInexistentEventUidReturn404()
    {
        $this->fixture->createAndOutputListOfRegistrations(
            $this->testingFramework->getAutoIncrement('tx_seminars_attendances')
        );

        self::assertContains('404', $this->headerProxy->getLastAddedHeader());
    }

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
                'crdate' => ($GLOBALS['SIM_EXEC_TIME'] + 1),
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $registrationsList = $this->fixture->createAndOutputListOfRegistrations($this->eventUid);
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsDoesNotWrapHeadlineFieldsInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $registrationsList = $this->fixture->createAndOutputListOfRegistrations($this->eventUid);
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
    public function createAndOutputListOfRegistrationsSeparatesHeadlineFieldsWithSemicolons()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address,title');

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_attendances.address') .
                ';' . $this->localizeAndRemoveColon('tx_seminars_attendances.title'),
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForConfigurationAttendanceCsvFieldsEmptyDoesNotAddSemicolonOnEndOfHeadline()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', '');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');

        self::assertNotContains(
            'name;',
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForConfigurationFeUserCsvFieldsEmptyDoesNotAddSemicolonAtBeginningOfHeadline()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', '');

        self::assertNotContains(
            ';address',
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForBothConfigurationFieldsNotEmptyAddsSemicolonBetweenConfigurationFields()
    {
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');

        self::assertContains(
            $this->localizeAndRemoveColon('LGL.name') . ';' . $this->localizeAndRemoveColon('tx_seminars_attendances.address'),
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOuptutListOfRegistrationsForNoEventUidGivenReturnsRegistrationsOnCurrentPage()
    {
        $this->fixture->piVars['pid'] = $this->pid;
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
            $this->fixture->createAndOutputListOfRegistrations()
        );
    }

    /**
     * @test
     */
    public function createAndOuptutListOfRegistrationsForNoEventUidGivenDoesNotReturnRegistrationsOnOtherPage()
    {
        $this->fixture->piVars['pid'] = $this->pid;
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
            $this->fixture->createAndOutputListOfRegistrations()
        );
    }

    /**
     * @test
     */
    public function createAndOuptutListOfRegistrationsForNoEventUidGivenReturnsRegistrationsOnSubpageOfCurrentPage()
    {
        $this->fixture->piVars['pid'] = $this->pid;
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
            $this->fixture->createAndOutputListOfRegistrations()
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForNonExistingEventUidAddsNotFoundStatusToHeader()
    {
        $this->fixture->createAndOutputListOfRegistrations(
            $this->testingFramework->getAutoIncrement('tx_seminars_seminars')
        );

        self::assertContains('404', $this->headerProxy->getLastAddedHeader());
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForNoGivenEventUidAndFeModeAddsAccessForbiddenStatusToHeader()
    {
        $this->fixture->setTypo3Mode('FE');
        $this->fixture->createAndOutputListOfRegistrations();

        self::assertContains('403', $this->headerProxy->getLastAddedHeader());
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForEventUidGivenSetsPageContentTypeToCsv()
    {
        $this->fixture->createAndOutputListOfRegistrations($this->eventUid);

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
        $this->fixture->piVars['pid'] = $this->pid;
        $this->fixture->createAndOutputListOfRegistrations();

        self::assertContains(
            'Content-type: text/csv; header=present; charset=utf-8',
            $this->headerProxy->getAllAddedHeaders()
        );
    }

    /*
     * Tests concerning the export mode and the configuration
     */

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForWebModeNotUsesFeUserFieldsFromEmailConfiguration()
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
        );
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForWebModeNotUsesRegistrationsOnQueueSettingFromEmailConfiguration()
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
            $this->fixture->createAndOutputListOfRegistrations($this->eventUid)
        );
    }
}
