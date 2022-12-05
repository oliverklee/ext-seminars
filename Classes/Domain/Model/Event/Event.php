<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Abstract base class for different event types (single events, event topics, event dates).
 *
 * Mostly, this class exists in order to get single-table inheritance working. In the code,
 * please use `EventInterface` as much as possible instead.
 */
abstract class Event extends AbstractEntity implements EventInterface
{
    /**
     * @var array<string, string|int|float|null>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
     * @internal
     */
    protected $rawData;

    /**
     * Returns the raw data as it is stored in the database.
     *
     * @return array<string, string|int|float|null>|null
     *
     * @internal
     */
    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * Sets the raw data as it is stored in the database.
     *
     * @param array<string, string|int|float|null> $rawData
     *
     * @internal
     */
    public function setRawData(array $rawData): void
    {
        $this->rawData = $rawData;
    }
}
