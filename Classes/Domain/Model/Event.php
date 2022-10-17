<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Abstract base class for different event types (single events, event topics, event dates).
 *
 * Mostly, this class exists in order to get single-table inheritance working. In the code,
 * please use `EventInterface` as much as possible instead.
 */
abstract class Event extends AbstractEntity implements EventInterface
{
}
