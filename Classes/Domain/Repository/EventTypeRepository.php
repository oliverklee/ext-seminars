<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\EventTypeInterface;
use OliverKlee\Seminars\Domain\Model\NullEventType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<EventType>
 */
class EventTypeRepository extends Repository
{
    protected $defaultOrderings = ['title' => QueryInterface::ORDER_ASCENDING];

    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Returns a `NullEventType` and all event types after that.
     *
     * This method is intended to provide data for selects with an empty option as the first entry.
     *
     * @return list<EventTypeInterface>
     */
    public function findAllPlusNullEventType(): array
    {
        return \array_merge([new NullEventType()], $this->findAll()->toArray());
    }
}
