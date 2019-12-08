<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Traits;

/**
 * This trait provides methods useful for testing lists (usually in functional tests).
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
trait ListHelper
{
    /**
     * @return void
     */
    private static function assertContainsModelWithUid(\Tx_Oelib_List $models, int $uid)
    {
        self::assertTrue($models->hasUid($uid));
    }

    /**
     * @return void
     */
    private static function assertNotContainsModelWithUid(\Tx_Oelib_List $models, int $uid)
    {
        self::assertFalse($models->hasUid($uid));
    }
}
