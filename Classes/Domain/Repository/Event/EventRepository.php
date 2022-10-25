<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository\Event;

use OliverKlee\FeUserExtraFields\Domain\Repository\DirectPersistTrait;
use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Event>
 */
class EventRepository extends Repository implements DirectPersist
{
    use DirectPersistTrait;

    /**
     * @return array<int, Event>
     */
    public function findSingleEventsByOwnerUid(int $ownerUid): array
    {
        if ($ownerUid <= 0) {
            return [];
        }

        $query = $this->createQuery();

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $query->setQuerySettings($querySettings);
        $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);

        $objectTypeMatcher = $query->equals('objectType', EventInterface::TYPE_SINGLE_EVENT);
        $ownerMatcher = $query->equals('ownerUid', $ownerUid);
        $query->matching($query->logicalAnd($objectTypeMatcher, $ownerMatcher));

        return $query->execute()->toArray();
    }
}
