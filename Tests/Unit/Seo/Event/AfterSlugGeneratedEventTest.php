<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Seo\Event\AfterSlugGeneratedEvent;
use OliverKlee\Seminars\Seo\SlugContext;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * @covers \OliverKlee\Seminars\Seo\Event\AfterSlugGeneratedEvent
 */
final class AfterSlugGeneratedEventTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isStoppableEvent(): void
    {
        $slugContext = new SlugContext(42, '', '');
        $subject = new AfterSlugGeneratedEvent($slugContext, '');

        self::assertInstanceOf(StoppableEventInterface::class, $subject);
    }

    /**
     * @test
     */
    public function hasSlugContextFromConstructor(): void
    {
        $slugContext = new SlugContext(42, '', '');
        $subject = new AfterSlugGeneratedEvent($slugContext, '');

        self::assertSame($slugContext, $subject->getSlugContext());
    }

    /**
     * @test
     */
    public function hasSlugFromConstructor(): void
    {
        $slug = 'some-nice-event';
        $subject = new AfterSlugGeneratedEvent(new SlugContext(42, '', ''), $slug);

        self::assertSame($slug, $subject->getSlug());
    }

    /**
     * @test
     */
    public function setSlugSetsSlug(): void
    {
        $subject = new AfterSlugGeneratedEvent(new SlugContext(42, '', ''), '');

        $slug = 'some-nice-event';
        $subject->setSlug($slug);

        self::assertSame($slug, $subject->getSlug());
    }

    /**
     * @test
     */
    public function propagationByDefaultIsNotStopped(): void
    {
        $subject = new AfterSlugGeneratedEvent(new SlugContext(42, '', ''), 'some-event');

        self::assertFalse($subject->isPropagationStopped());
    }

    /**
     * @test
     */
    public function propagationIsStoppedAfterChangingSlug(): void
    {
        $subject = new AfterSlugGeneratedEvent(new SlugContext(42, '', ''), 'some-event');

        $subject->setSlug('some-other-event');

        self::assertTrue($subject->isPropagationStopped());
    }

    /**
     * @test
     */
    public function propagationIsStoppedAfterSettingTheSameSlugAgain(): void
    {
        $slug = 'some-event';
        $subject = new AfterSlugGeneratedEvent(new SlugContext(42, '', ''), $slug);

        $subject->setSlug($slug);

        self::assertTrue($subject->isPropagationStopped());
    }
}
