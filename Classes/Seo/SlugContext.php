<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Seo;

/**
 * Context for generating a slug for an event.
 */
final class SlugContext
{
    /**
     * @var int<0, max> the UID of the event (which for event dates will be the UID of the date, not the topic),
     *                  will be 0 if the event has not been saved yet
     */
    private int $eventUid;

    /**
     * @var string the title used for the slug (which for event dates will the topic's title)
     */
    private string $displayTitle;

    /**
     * @var string the slugified title, which is not guaranteed to be unique
     */
    public string $slugifiedTitle;

    /**
     * @param int<0, max> $eventUid
     */
    public function __construct(int $eventUid, string $displayTitle, string $slugifiedTitle)
    {
        $this->eventUid = $eventUid;
        $this->displayTitle = $displayTitle;
        $this->slugifiedTitle = $slugifiedTitle;
    }

    /**
     * @return int<0, max> will be 0 if the event has not been saved yet
     */
    public function getEventUid(): int
    {
        return $this->eventUid;
    }

    public function getDisplayTitle(): string
    {
        return $this->displayTitle;
    }

    public function getSlugifiedTitle(): string
    {
        return $this->slugifiedTitle;
    }
}
