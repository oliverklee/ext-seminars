<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Seo\Fixtures;

use OliverKlee\Seminars\Seo\Event\AfterSlugGeneratedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TestingSlugEventDispatcher implements EventDispatcherInterface
{
    private ?AfterSlugGeneratedEvent $event = null;

    private bool $dispatched = false;

    private ?string $slugToSet = null;

    public function setModifiedSlug(string $slug): void
    {
        $this->slugToSet = $slug;
    }

    public function dispatch(object $event): object
    {
        if ($event instanceof AfterSlugGeneratedEvent) {
            $this->event = $event;
            if (\is_string($this->slugToSet)) {
                $event->setSlug($this->slugToSet);
            }

            $this->dispatched = true;
        }

        return $event;
    }

    public function isDispatched(): bool
    {
        return $this->dispatched;
    }

    public function getEvent(): AfterSlugGeneratedEvent
    {
        if (!$this->event instanceof AfterSlugGeneratedEvent) {
            throw new \RuntimeException('No event has been dispatched yet.', 1693499752);
        }

        return $this->event;
    }
}
