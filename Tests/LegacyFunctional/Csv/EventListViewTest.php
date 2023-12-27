<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Csv\EventListView;
use OliverKlee\Seminars\Tests\Support\LanguageHelper;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class EventListViewTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var EventListView
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
     * PID of the system folder in which we store our test data
     *
     * @var int
     */
    private $pageUid = 0;

    protected function setUp(): void
    {
        parent::setUp();

        GeneralUtility::makeInstance(Context::class)
            ->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('2018-04-26 12:42:23')));

        $this->getLanguageService()->includeLLFile('EXT:core/Resources/Private/Language/locallang_general.xlf');
        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');

        $this->testingFramework = new TestingFramework('tx_seminars');

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);

        $this->subject = new EventListView();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    /**
     * Retrieves the localization for the given locallang key and then strips the trailing colon from the localization.
     *
     * @param non-empty-string $locallangKey the locallang key with the localization to remove the trailing colon from,
     *        must not be empty and the localization must have a trailing colon
     *
     * @return string locallang string with the removed trailing colon, will not be empty
     */
    private function localizeAndRemoveColon(string $locallangKey): string
    {
        return \rtrim($this->translate($locallangKey), ':');
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
    private function createEventInFolderAndSetPageUid(array $eventData = []): int
    {
        $this->pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($this->pageUid);

        $eventData['pid'] = $this->pageUid;
        $eventData['begin_date'] = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect(
            'date',
            'timestamp'
        );

        return $this->testingFramework->createRecord('tx_seminars_seminars', $eventData);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setPageUidWithPositivePageUidNotThrowsException(): void
    {
        $this->subject->setPageUid($this->testingFramework->createSystemFolder());
    }

    /**
     * @test
     */
    public function setPageUidWithZeroPageUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setPageUid(0);
    }

    /**
     * @test
     */
    public function setPageUidWithNegativePageUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setPageUid(-1);
    }

    /**
     * @test
     */
    public function renderIsEmptyForNoPageUid(): void
    {
        self::assertSame(
            '',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForZeroRecordsAndSeparatorDisabledReturnsOnlyHeader(): void
    {
        $pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($pageUid);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid,title');
        $this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', false);

        self::assertSame(
            $this->localizeAndRemoveColon('tx_seminars_seminars.uid') . ';' .
            $this->localizeAndRemoveColon('tx_seminars_seminars.title') . "\r\n",
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForZeroRecordsAndSeparatorEnabledReturnsOnlySeparatorSpecificationAndHeader(): void
    {
        $pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($pageUid);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid,title');
        $this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', true);

        self::assertSame(
            "sep=;\r\n" .
            $this->localizeAndRemoveColon('tx_seminars_seminars.uid') . ';' .
            $this->localizeAndRemoveColon('tx_seminars_seminars.title') . "\r\n",
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainOneEventUid(): void
    {
        $eventUid = $this->createEventInFolderAndSetPageUid();

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        self::assertStringContainsString(
            (string)$eventUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainEventFromSubFolder(): void
    {
        $pageUid = $this->testingFramework->createSystemFolder();
        $this->subject->setPageUid($pageUid);

        $subFolderPid = $this->testingFramework->createSystemFolder($pageUid);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $subFolderPid, 'title' => 'another event']
        );

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertStringContainsString(
            'another event',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainTwoEventUids(): void
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $firstEventUid = $this->createEventInFolderAndSetPageUid();
        $secondEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pageUid,
                'begin_date' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect(
                    'date',
                    'timestamp'
                ) - 3600,
            ]
        );

        $eventList = $this->subject->render();

        self::assertStringContainsString(
            (string)$firstEventUid,
            $eventList
        );
        self::assertStringContainsString(
            (string)$secondEventUid,
            $eventList
        );
    }

    /**
     * @test
     */
    public function renderSeparatesLinesWithCarriageReturnsAndLineFeeds(): void
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $firstEventUid = $this->createEventInFolderAndSetPageUid();
        $secondEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pageUid,
                'begin_date' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect(
                    'date',
                    'timestamp'
                ) - 3600,
            ]
        );

        self::assertStringContainsString(
            $this->localizeAndRemoveColon(
                'tx_seminars_seminars.uid'
            ) . "\r\n" . $firstEventUid . "\r\n" . $secondEventUid . "\r\n",
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderHasResultEndingWithCarriageReturnAndLineFeed(): void
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

        $this->createEventInFolderAndSetPageUid();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pageUid,
                'begin_date' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect(
                    'date',
                    'timestamp'
                ) - 3600,
            ]
        );

        self::assertRegExp(
            '/\\r\\n$/',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotWrapsRegularValuesWithDoubleQuotes(): void
    {
        $this->createEventInFolderAndSetPageUid(['title' => 'bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertStringNotContainsString(
            '"bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderEscapesDoubleQuotes(): void
    {
        $this->createEventInFolderAndSetPageUid(['description' => 'foo " bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description');

        self::assertStringContainsString(
            'foo "" bar',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithLineFeedsInDoubleQuotes(): void
    {
        $this->createEventInFolderAndSetPageUid(['title' => "foo\nbar"]);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertStringContainsString(
            "\"foo\nbar\"",
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithDoubleQuotesInDoubleQuotes(): void
    {
        $this->createEventInFolderAndSetPageUid(['title' => 'foo " bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertStringContainsString(
            '"foo "" bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithSemicolonsInDoubleQuotes(): void
    {
        $this->createEventInFolderAndSetPageUid(['title' => 'foo ; bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

        self::assertStringContainsString(
            '"foo ; bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderSeparatesValuesWithSemicolons(): void
    {
        $this->createEventInFolderAndSetPageUid(['description' => 'foo', 'title' => 'bar']);

        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

        self::assertStringContainsString(
            'foo;bar',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotWrapsHeadlineFieldsInDoubleQuotes(): void
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');
        $this->createEventInFolderAndSetPageUid();

        $eventList = $this->subject->render();
        $description = $this->localizeAndRemoveColon('tx_seminars_seminars.description');

        self::assertStringContainsString(
            $description,
            $eventList
        );
        self::assertStringNotContainsString(
            '"' . $description . '"',
            $eventList
        );
    }

    /**
     * @test
     */
    public function renderSeparatesHeadlineFieldsWithSemicolons(): void
    {
        $this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');
        $this->createEventInFolderAndSetPageUid();

        self::assertStringContainsString(
            $this->localizeAndRemoveColon('tx_seminars_seminars.description') .
            ';' . $this->localizeAndRemoveColon('tx_seminars_seminars.title'),
            $this->subject->render()
        );
    }
}
