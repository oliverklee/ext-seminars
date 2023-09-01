<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Seo;

/**
 * Context for generating a slug.
 */
final class SlugContext
{
    /**
     * @var int the UID of the event (which for event dates will be the UID of the date, not the topic)
     */
    private $eventUid;

    /**
     * @var string the title used for the slug (which for event dates will the topic's title)
     */
    private $displayTitle;

    /**
     * @var string the slugified title, which is not guaranteed to be unique
     */
    public $slugifiedTitle;

    /**
     * @param int $eventUid
     */
    public function __construct(int $eventUid, string $displayTitle, string $slugifiedTitle)
    {
        $this->eventUid = $eventUid;
        $this->displayTitle = $displayTitle;
        $this->slugifiedTitle = $slugifiedTitle;
    }

    /**
     * @return int
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
