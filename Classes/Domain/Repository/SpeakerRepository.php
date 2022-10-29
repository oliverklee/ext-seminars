<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository;

use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use OliverKlee\Seminars\Domain\Model\Speaker;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Speaker>
 */
class SpeakerRepository extends Repository
{
    use StoragePageAgnostic;

    protected $defaultOrderings = ['name' => QueryInterface::ORDER_ASCENDING];
}
