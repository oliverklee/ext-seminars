<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted because it implements TestingHookInterfaceReturnsModifiedValue.
 *
 * The methods are designed to return a different value when the test is calling the methods from
 * `TestingHookImplementorReturnsModifiedValue2` first.
 */
final class TestingHookImplementorReturnsModifiedValue implements TestingHookInterfaceReturnsModifiedValue
{
    /**
     * Gets called during HookProvider tests.
     *
     * @param bool $value the value to be returned modified
     */
    public function testHookMethodReturnsModifiedBool(bool $value): bool
    {
        return false;
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @param int $value the value to be returned modified
     */
    public function testHookMethodReturnsModifiedInt(int $value): int
    {
        return $value < 0 ? $value : $value + 1;
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @param string $value the value to be returned modified
     */
    public function testHookMethodReturnsModifiedString(string $value): string
    {
        return $value . ' 1';
    }
}
