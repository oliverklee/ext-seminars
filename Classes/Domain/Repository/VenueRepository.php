<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository;

use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Venue>
 */
class VenueRepository extends Repository
{
    use StoragePageAgnostic;

    protected $defaultOrderings = ['title' => QueryInterface::ORDER_ASCENDING];
}
