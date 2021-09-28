<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Traits;

use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * This trait provides methods useful when testing bags and bag builders.
 */
trait BagHelper
{
    private static function assertBagHasUid(AbstractBag $bag, int $uid): void
    {
        self::assertTrue(self::bagHasUid($bag, $uid), 'The bag does not have this UID: ' . $uid);
    }

    private static function assertBagNotHasUid(AbstractBag $bag, int $uid): void
    {
        self::assertFalse(self::bagHasUid($bag, $uid), 'The bag has this UID, but was expected not to: ' . $uid);
    }

    private static function bagHasUid(AbstractBag $bag, int $uid): bool
    {
        $found = false;

        /** @var AbstractModel $element */
        foreach ($bag as $element) {
            if ($element->getUid() === $uid) {
                $found = true;
                break;
            }
        }

        return $found;
    }
}
