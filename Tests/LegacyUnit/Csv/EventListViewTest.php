<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Csv\EventListView;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class EventListViewTest extends TestCase
{
    /**
     * @var EventListView
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * PID of the system folder in which we store our test data
     *
     * @var int
     */
    private $pageUid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_general.xlf');

        $this->testingFramework = new TestingFramework('tx_seminars');

        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new \Tx_Oelib_Configuration());
        $this->configuration = new \Tx_Oelib_Configuration();
        $this->configuration->setData(['charsetForCsv' => 'utf-8']);
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);

        $this->subject = new EventListView();
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
     * Creates a folder and an event record in that folder and returns the event UID.
     *
     * The PID and begin_date will be set automatically.
     *
     * @param array $eventData optional data for the event record
     *
     * @return int the UID of the created event record
     */
    protected function createEventInFolderAndSetPageUid(array $eventData = []): int
    {
        $this->pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($this->pageUid);

        $eventData['pid'] = $this->pageUid;
        $eventData['begin_date'] = $GLOBALS['SIM_EXEC_TIME'];

        return $this->testingFramework->createRecord('tx_seminars_seminars', $eventData);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setPageUidWithPositivePageUidNotThrowsException()
    {
        $this->subject->setPageUid($this->testingFramework->createSystemFolder());
    }

    /**
     * @test
     */
    public function setPageUidWithZeroPageUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setPageUid(0);
    }

    /**
     * @test
     */
    public function setPageUidWithNegativePageUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setPageUid(-1);
    }

    /**
     * @test
     */
    public function renderIsEmptyForNoPageUid()
    {
        self::assertSame(
            '',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForZeroRecordsAndSeparatorDisabledReturnsOnlyHeader()
    {
        $pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($pageUid);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid,title');
        $this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', false);

        self::assertSame(
            $this->localizeAndRemoveColon('tx_seminars_seminars.uid') . ';' .
            $this->localizeAndRemoveColon('tx_seminars_seminars.title') . CRLF,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForZeroRecordsAndSeparatorEnabledReturnsOnlySeparatorSpecificationAndHeader()
    {
        $pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($pageUid);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid,title');
        $this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', true);

        self::assertSame(
            'sep=;' . CRLF .
            $this->localizeAndRemoveColon('tx_seminars_seminars.uid') . ';' .
            $this->localizeAndRemoveColon('tx_seminars_seminars.title') . CRLF,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainOneEventUid()
    {
        $eventUid = $this->createEventInFolderAndSetPageUid();

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        self::assertContains(
            (string)$eventUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainEventFromSubFolder()
    {
        $pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($pageUid);

        $subFolderPid = $this->testingFramework->createSystemFolder($pageUid);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $subFolderPid, 'title' => 'another event']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            'another event',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainTwoEventUids()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $firstEventUid = $this->createEventInFolderAndSetPageUid();
        $secondEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->pageUid, 'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600]
        );

        $eventList = $this->subject->render();

        self::assertContains(
            (string)$firstEventUid,
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
    public function renderSeparatesLinesWithCarriageReturnsAndLineFeeds()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $firstEventUid = $this->createEventInFolderAndSetPageUid();
        $secondEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->pageUid, 'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600]
        );

        self::assertContains(
            $this->localizeAndRemoveColon(
                'tx_seminars_seminars.uid'
            ) . CRLF . $firstEventUid . CRLF . $secondEventUid . CRLF,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderHasResultEndingWithCarriageReturnAndLineFeed()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $this->createEventInFolderAndSetPageUid();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->pageUid, 'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600]
        );

        self::assertRegExp(
            '/\\r\\n$/',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotWrapsRegularValuesWithDoubleQuotes()
    {
        $this->createEventInFolderAndSetPageUid(['title' => 'bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertNotContains(
            '"bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderEscapesDoubleQuotes()
    {
        $this->createEventInFolderAndSetPageUid(['description' => 'foo " bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description');

        self::assertContains(
            'foo "" bar',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithLineFeedsInDoubleQuotes()
    {
        $this->createEventInFolderAndSetPageUid(['title' => 'foo' . LF . 'bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            '"foo' . LF . 'bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithDoubleQuotesInDoubleQuotes()
    {
        $this->createEventInFolderAndSetPageUid(['title' => 'foo " bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            '"foo "" bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithSemicolonsInDoubleQuotes()
    {
        $this->createEventInFolderAndSetPageUid(['title' => 'foo ; bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertContains(
            '"foo ; bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderSeparatesValuesWithSemicolons()
    {
        $this->createEventInFolderAndSetPageUid(['description' => 'foo', 'title' => 'bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

        self::assertContains(
            'foo;bar',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotWrapsHeadlineFieldsInDoubleQuotes()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');
        $this->createEventInFolderAndSetPageUid();

        $eventList = $this->subject->render();
        $description = $this->localizeAndRemoveColon('tx_seminars_seminars.description');

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
    public function renderSeparatesHeadlineFieldsWithSemicolons()
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');
        $this->createEventInFolderAndSetPageUid();

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_seminars.description') .
            ';' . $this->localizeAndRemoveColon('tx_seminars_seminars.title'),
            $this->subject->render()
        );
    }
}
