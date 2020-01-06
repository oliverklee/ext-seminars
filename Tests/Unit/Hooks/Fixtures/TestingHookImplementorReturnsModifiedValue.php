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
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
final class TestingHookImplementorReturnsModifiedValue implements TestingHookInterfaceReturnsModifiedValue
{
    /**
     * Gets called during HookProvider tests.
     *
     * @return bool
     */
    public function testHookMethodReturnsModifiedBool(bool $value): bool
    {
        return $value === false ? $value : !$value;
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @return int
     */
    public function testHookMethodReturnsModifiedInt(int $value): int
    {
        return $value < 0 ? $value : $value + 1;
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @return string
     */
    public function testHookMethodReturnsModifiedString(string $value): string
    {
        return $value . ' 1';
    }
}
