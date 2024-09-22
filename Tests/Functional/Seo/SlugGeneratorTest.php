<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Seo;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Seo\SlugGenerator;
use OliverKlee\Seminars\Tests\Unit\Seo\Fixtures\TestingSlugEventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Seo\SlugGenerator
 */
final class SlugGeneratorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private SlugGenerator $subject;

    private TestingSlugEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = new TestingSlugEventDispatcher();

        $this->subject = new SlugGenerator($this->eventDispatcher);
    }

    /**
     * @test
     */
    public function canBeConstructedWithMakeInstanceWithoutArguments(): void
    {
        $subject = GeneralUtility::makeInstance(SlugGenerator::class);

        self::assertInstanceOf(SlugGenerator::class, $subject);
    }

    /**
     * @test
     */
    public function canBeConstructedUsingTheContainer(): void
    {
        $subject = $this->get(SlugGenerator::class);

        self::assertInstanceOf(SlugGenerator::class, $subject);
    }

    /**
     * @test
     */
    public function instanceCreatedWithMakeInstanceCanGenerateSlug(): void
    {
        $subject = GeneralUtility::makeInstance(SlugGenerator::class);
        $record = ['uid' => 1234, 'object_type' => EventInterface::TYPE_SINGLE_EVENT, 'title' => 'There will be cake!'];

        $result = $subject->generateSlug(['record' => $record]);

        self::assertSame('there-will-be-cake', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEmptyRecordReturnsEmptyString(): void
    {
        $result = $this->subject->generateSlug(['record' => []]);

        self::assertSame('', $result);
    }

    /**
     * @return array<string,array{0: EventInterface::TYPE_*}>
     */
    public static function nonDateEventTypeDataProvider(): array
    {
        return [
            'single event' => [EventInterface::TYPE_SINGLE_EVENT],
            'event topic' => [EventInterface::TYPE_EVENT_TOPIC],
        ];
    }

    /**
     * @test
     *
     * @param EventInterface::TYPE_* $type
     * @dataProvider nonDateEventTypeDataProvider
     */
    public function generateSlugForNonDateEventWithEmptyTitleReturnsEmptyString(int $type): void
    {
        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => $type, 'title' => ''];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('', $result);
    }

    /**
     * @test
     *
     * @param EventInterface::TYPE_* $type
     * @dataProvider nonDateEventTypeDataProvider
     */
    public function generateSlugForNonDateEventWithWhitespaceOnlyTitleReturnsEmptyString(int $type): void
    {
        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => $type, 'title' => " \t\n\r"];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('', $result);
    }

    /**
     * @test
     *
     * @param EventInterface::TYPE_* $type
     * @dataProvider nonDateEventTypeDataProvider
     */
    public function generateSlugForNonDateEventWithNonEmptyTitleReturnsSlugifiedTitle(int $type): void
    {
        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => $type, 'title' => 'There will be cake!'];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('there-will-be-cake', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithTopicReturnsSlugFromTopicTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('event-topic', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithTopicWithValuesAsStringReturnsSlugFromTopicTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => (string)EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => (string)2,
            'slug' => 'existing-date-slug',
        ];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('event-topic', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithTopicWithEmptyTitleReturnsEmptyString(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithTopicWithoutTitle.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithoutTopicReturnsEmptyString(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithoutTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithDeletedTopicReturnsSlugFromTopicTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithDeletedTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('deleted-event-topic', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithHiddenTopicReturnsSlugFromTopicTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithHiddenTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('hidden-event-topic', $result);
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithTimedTopicReturnsSlugFromTopicTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithTimedTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('timed-event-topic', $result);
    }

    /**
     * @test
     */
    public function generateSlugAddsSuffixIfEventWithThePossibleSlugAlreadyExists(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithSlug.xml');

        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => EventInterface::TYPE_SINGLE_EVENT, 'title' => 'some-event'];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('some-event-1', $result);
    }

    /**
     * @test
     */
    public function generateSlugKeepsCurrentSlugIfTheGeneratedSlugIsTheSame(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithSlug.xml');

        $record = ['uid' => 1, 'object_type' => EventInterface::TYPE_SINGLE_EVENT, 'title' => 'some-event'];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('some-event', $result);
    }

    /**
     * @test
     */
    public function generateSlugAddsIncreasedSuffixIfEventWithThePossibleSuffixedSlugAlreadyExists(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoSingleEventsWithSlug.xml');

        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => EventInterface::TYPE_SINGLE_EVENT, 'title' => 'some-event'];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame('some-event-2', $result);
    }

    /**
     * @test
     *
     * @param EventInterface::TYPE_* $type
     * @dataProvider nonDateEventTypeDataProvider
     */
    public function generateSlugForNonDateEventDispatchesSlugGeneratedEventWithEventUid(int $type): void
    {
        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => $type, 'title' => ''];

        $this->subject->generateSlug(['record' => $record]);

        self::assertTrue($this->eventDispatcher->isDispatched());
        self::assertSame($uid, $this->eventDispatcher->getEvent()->getSlugContext()->getEventUid());
    }

    /**
     * @test
     *
     * @param EventInterface::TYPE_* $type
     * @dataProvider nonDateEventTypeDataProvider
     */
    public function generateSlugForNonDateEventDispatchesSlugGeneratedEventWithEventDisplayTitle(int $type): void
    {
        $title = 'Tea tasting';
        $record = ['uid' => 1234, 'object_type' => $type, 'title' => $title];

        $this->subject->generateSlug(['record' => $record]);

        self::assertTrue($this->eventDispatcher->isDispatched());
        self::assertSame($title, $this->eventDispatcher->getEvent()->getSlugContext()->getDisplayTitle());
    }

    /**
     * @test
     *
     * @param EventInterface::TYPE_* $type
     * @dataProvider nonDateEventTypeDataProvider
     */
    public function generateSlugForNonDateEventDispatchesSlugGeneratedEventWithGeneratedUniqueSlug(int $type): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithSlug.xml');

        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => $type, 'title' => 'some-event'];

        $this->subject->generateSlug(['record' => $record]);

        self::assertTrue($this->eventDispatcher->isDispatched());
        self::assertSame('some-event-1', $this->eventDispatcher->getEvent()->getSlug());
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithTopicDispatchesSlugGeneratedEventWithGeneratedSlugFromTopic(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $this->subject->generateSlug(['record' => $record]);

        self::assertTrue($this->eventDispatcher->isDispatched());
        self::assertSame('event-topic', $this->eventDispatcher->getEvent()->getSlug());
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithTopicDispatchesSlugGeneratedEventWithTitleFromTopic(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithTopic.xml');

        $record = [
            'uid' => 1234,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $this->subject->generateSlug(['record' => $record]);

        self::assertTrue($this->eventDispatcher->isDispatched());
        self::assertSame('Event topic', $this->eventDispatcher->getEvent()->getSlugContext()->getDisplayTitle());
    }

    /**
     * @test
     */
    public function generateSlugForEventDateWithTopicDispatchesSlugGeneratedEventWithUidFromDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithTopic.xml');

        $eventDateUid = 1234;
        $record = [
            'uid' => $eventDateUid,
            'object_type' => EventInterface::TYPE_EVENT_DATE,
            'title' => 'Event date',
            'topic' => 2,
            'slug' => 'existing-date-slug',
        ];

        $this->subject->generateSlug(['record' => $record]);

        self::assertTrue($this->eventDispatcher->isDispatched());
        self::assertSame($eventDateUid, $this->eventDispatcher->getEvent()->getSlugContext()->getEventUid());
    }

    /**
     * @test
     */
    public function generateSlugPassesNonUniqueSlugifiedTitleToEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithSlug.xml');

        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => EventInterface::TYPE_SINGLE_EVENT, 'title' => 'Some event'];

        $this->subject->generateSlug(['record' => $record]);

        self::assertTrue($this->eventDispatcher->isDispatched());
        self::assertSame('some-event', $this->eventDispatcher->getEvent()->getSlugContext()->getSlugifiedTitle());
    }

    /**
     * @test
     */
    public function generateSlugReturnsSlugModifiedByEvent(): void
    {
        $modifiedSlug = 'there-is-no-spoon/42';
        $this->eventDispatcher->setModifiedSlug($modifiedSlug);
        $uid = 1234;
        $record = ['uid' => $uid, 'object_type' => EventInterface::TYPE_SINGLE_EVENT, 'title' => ''];

        $result = $this->subject->generateSlug(['record' => $record]);

        self::assertSame($modifiedSlug, $result);
    }
}
