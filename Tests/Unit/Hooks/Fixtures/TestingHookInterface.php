<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

use OliverKlee\Seminars\Hooks\Interfaces\Hook;

/**
 * Valid test interface to use with the HookProviderTest.
 *
 * Will be accepted, because it extends Hook.
 */
interface TestingHookInterface extends Hook
{
    /**
     * Gets called during HookProvider tests.
     */
    public function testHookMethod();
}
