<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

use OliverKlee\Seminars\Hooks\Interfaces\Hook;

/**
 * Valid test interface returning an array to use with the HookProviderTest.
 *
 * Will be accepted because it extends `Hook`.
 */
interface TestingHookInterfaceReturnsModifiedValue extends Hook
{
    /**
     * Gets called during HookProvider tests.
     *
     * @param bool $value the value to be returned modified
     *
     * @return bool
     */
    public function testHookMethodReturnsModifiedBool(bool $value): bool;

    /**
     * Gets called during HookProvider tests.
     *
     * @param int $value the value to be returned modified
     *
     * @return int
     */
    public function testHookMethodReturnsModifiedInt(int $value): int;

    /**
     * Gets called during HookProvider tests.
     *
     * @param string $value the value to be returned modified
     *
     * @return string
     */
    public function testHookMethodReturnsModifiedString(string $value): string;
}
