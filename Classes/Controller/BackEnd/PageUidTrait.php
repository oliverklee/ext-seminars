<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait for access to the page UID from the page tree in the backend.
 */
trait PageUidTrait
{
    /**
     * This method is only public for unit testing.
     *
     * @return 0|positive-int
     */
    public function getPageUid(): int
    {
        return (int)(GeneralUtility::_GP('id') ?? 0);
    }
}
