<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Second valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted as a second hooked-in class.
 *
 * The methods are designed to return a different value when the test is not calling the methods from
 * `TestingHookImplementorReturnsModifiedValue` first.
 */
final class TestingHookImplementorReturnsModifiedValue2 implements TestingHookInterfaceReturnsModifiedValue
{
    /**
     * Gets called during HookProvider tests.
     *
     * @param bool $value the value to be returned modified
     */
    public function testHookMethodReturnsModifiedBool(bool $value): bool
    {
        return !$value;
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @param int $value the value to be returned modified
     */
    public function testHookMethodReturnsModifiedInt(int $value): int
    {
        return $value - 1;
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @param string $value the value to be returned modified
     */
    public function testHookMethodReturnsModifiedString(string $value): string
    {
        return $value . ' 2';
    }
}
