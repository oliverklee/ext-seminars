<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Seo;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Seo\SlugGenerator;

/**
 * @covers \OliverKlee\Seminars\Seo\SlugGenerator
 */
final class SlugGeneratorTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var SlugGenerator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SlugGenerator();
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
    public function generateSlugForNonEventDateWithEmptyTitleReturnsEmptyString(int $type): void
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
    public function generateSlugForNonEventDateWithWhitespaceOnlyTitleReturnsEmptyString(int $type): void
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
    public function generateSlugForNonEventDateWithNonTitleReturnsSlugifiedTitle(int $type): void
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
}
