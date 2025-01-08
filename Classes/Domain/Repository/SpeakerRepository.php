<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\Speaker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Speaker>
 */
class SpeakerRepository extends Repository
{
    protected $defaultOrderings = ['name' => QueryInterface::ORDER_ASCENDING];

    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
}
