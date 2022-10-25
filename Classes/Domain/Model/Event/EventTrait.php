<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * This trait provides methods that are useful for all event classes (`SingleEvent`, `EventDate` and `EventTopic`).
 *
 * @mixin Event
 */
trait EventTrait
{
    /**
     * The title of this event as visible in the backend.
     * In the frontend, the title might be different, e.g., event dates will use the title of their
     * corresponding topic.
     *
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 255})
     */
    protected $internalTitle = '';

    /**
     * the UID of the FE user who has created the event
     *
     * @var int
     */
    protected $ownerUid = 0;

    public function getInternalTitle(): string
    {
        return $this->internalTitle;
    }

    public function setInternalTitle(string $name): void
    {
        $this->internalTitle = $name;
    }

    public function getOwnerUid(): int
    {
        return $this->ownerUid;
    }

    public function setOwnerUid(int $ownerUid): void
    {
        $this->ownerUid = $ownerUid;
    }
}
