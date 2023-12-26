<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use TYPO3\CMS\Extbase\Annotation\Validate;

/**
 * This trait provides methods that are useful for all event classes (`SingleEvent`, `EventDate` and `EventTopic`).
 *
 * @phpstan-require-extends Event
 * @phpstan-require-implements EventInterface
 */
trait EventTrait
{
    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * The title of this event as visible in the backend.
     * In the frontend, the title might be different, e.g., event dates will use the title of their
     * corresponding topic.
     *
     * @var string
     * @Validate("StringLength", options={"maximum": 255})
     * @Validate("NotEmpty")
     */
    protected $internalTitle = '';

    /**
     * the UID of the FE user who has created the event
     *
     * @var int
     */
    protected $ownerUid = 0;

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getInternalTitle(): string
    {
        return $this->internalTitle;
    }

    /**
     * @param non-empty-string $name
     */
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
