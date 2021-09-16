<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

use OliverKlee\Seminars\Hooks\Interfaces\Hook;

/**
 * Valid test interface returning an array to use with the HookProviderTest.
 *
 * Will be accepted because it extends `Hook`.
 */
interface TestingHookInterfaceReturnsArray extends Hook
{
    /**
     * Gets called during HookProvider tests.
     *
     * @return string[]
     */
    public function testHookMethodReturnsArray(): array;

    /**
     * Gets called during HookProvider tests.
     *
     * @return array[]
     */
    public function testHookMethodReturnsNestedArray(): array;
}
