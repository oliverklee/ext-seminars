<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Model;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * This test case holds all tests specific to event dates.
 *
 * @covers \OliverKlee\Seminars\Model\AbstractTimeSpan
 * @covers \OliverKlee\Seminars\Model\Event
 */
final class EventDateTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected bool $initializeDatabase = false;

    private Event $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Event();
    }

    // Tests concerning the title.

    /**
     * @test
     */
    public function getTitleWithNonEmptyTopicTitleReturnsTopicTitle(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['title' => 'Superhero']);
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
                'title' => 'Supervillain',
            ]
        );

        self::assertSame(
            'Superhero',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getRawTitleWithNonEmptyTopicTitleReturnsDateTitle(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel(['title' => 'Superhero']);
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
                'title' => 'Supervillain',
            ]
        );

        self::assertSame(
            'Supervillain',
            $this->subject->getRawTitle()
        );
    }

    // Tests regarding the teaser.

    /**
     * @test
     */
    public function getTeaserForEventDateWithoutTeaserReturnsAnEmptyString(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getTeaser()
        );
    }

    /**
     * @test
     */
    public function getTeaserForEventDateWithTeaserReturnsTeaser(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['teaser' => 'wow, this is teasing']);
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            'wow, this is teasing',
            $this->subject->getTeaser()
        );
    }

    // Tests regarding the description.

    /**
     * @test
     */
    public function hasDescriptionForEventDateWithoutDescriptionReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForEventDateWithDescriptionReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(
                ['description' => 'this is a great event.']
            );
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }
}
