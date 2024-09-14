<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents an event type, e.g. "workshop" or "lecture".
 */
class EventType extends AbstractEntity implements EventTypeInterface
{
    /**
     * @var string
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $name): void
    {
        $this->title = $name;
    }
}
