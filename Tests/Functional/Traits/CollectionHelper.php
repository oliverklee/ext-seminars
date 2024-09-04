<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Traits;

use OliverKlee\Oelib\DataStructures\Collection;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * This trait provides methods useful for testing collections (usually in functional tests).
 *
 * @phpstan-require-extends FunctionalTestCase
 */
trait CollectionHelper
{
    /**
     * @param positive-int $uid
     */
    private static function assertContainsModelWithUid(Collection $models, int $uid): void
    {
        self::assertTrue($models->hasUid($uid));
    }

    /**
     * @param positive-int $uid
     */
    private static function assertNotContainsModelWithUid(Collection $models, int $uid): void
    {
        self::assertFalse($models->hasUid($uid));
    }
}
