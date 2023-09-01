<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Seo\Event;

use OliverKlee\Seminars\Seo\SlugContext;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Event that gets fired when a slug for an event has been generated, but not persisted yet.
 */
final class AfterSlugGeneratedEvent implements StoppableEventInterface
{
    /**
     * @var SlugContext
     */
    private $slugContext;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var bool
     */
    private $isPropagationStopped = false;

    public function __construct(SlugContext $slugContext, string $slug)
    {
        $this->slugContext = $slugContext;
        $this->slug = $slug;
    }

    public function getSlugContext(): SlugContext
    {
        return $this->slugContext;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Changes the slug and stops the propagation of the event.
     *
     * Note: The caller is responsible for ensuring that the slug is unique.
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->isPropagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
