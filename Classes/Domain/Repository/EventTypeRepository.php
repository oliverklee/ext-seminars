<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository;

use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\EventTypeInterface;
use OliverKlee\Seminars\Domain\Model\NullEventType;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<EventType>
 */
class EventTypeRepository extends Repository
{
    use StoragePageAgnostic;

    /**
     * Returns a `NullEventType` and all event types after that.
     *
     * This method is intended to provide data for selects with an empty option as the first entry.
     *
     * @return array<int, EventTypeInterface>
     */
    public function findAllPlusNullEventType(): array
    {
        return \array_merge([new NullEventType()], $this->findAll()->toArray());
    }
}
