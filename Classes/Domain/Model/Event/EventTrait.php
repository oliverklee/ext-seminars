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
    protected bool $hidden = false;

    /**
     * The title of this event as visible in the backend.
     * In the frontend, the title might be different, e.g., event dates will use the title of their
     * corresponding topic.
     *
     * @Validate("StringLength", options={"maximum": 255})
     * @Validate("NotEmpty")
     */
    protected string $internalTitle = '';

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
}
