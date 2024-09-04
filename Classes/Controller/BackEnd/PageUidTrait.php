<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Trait for access to the page UID from the page tree in the backend.
 *
 * @phpstan-require-extends ActionController
 */
trait PageUidTrait
{
    /**
     * This method is only public for unit testing.
     *
     * @return int<0, max>
     */
    public function getPageUid(): int
    {
        $rawUid = $_GET['id'] ?? 0;
        $uid = (\is_string($rawUid) || is_int($rawUid)) ? (int)$rawUid : 0;
        if ($uid < 0) {
            return 0;
        }

        return $uid;
    }
}
