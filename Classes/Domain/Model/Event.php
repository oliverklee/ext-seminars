<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a event.
 */
class Event extends AbstractEntity
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

    public function getInternalTitle(): string
    {
        return $this->internalTitle;
    }

    public function setInternalTitle(string $name): void
    {
        $this->internalTitle = $name;
    }
}
