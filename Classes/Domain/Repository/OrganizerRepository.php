<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository;

use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use OliverKlee\Seminars\Domain\Model\Organizer;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Organizer>
 */
class OrganizerRepository extends Repository
{
    use StoragePageAgnostic;

    protected $defaultOrderings = ['name' => QueryInterface::ORDER_ASCENDING];
}
