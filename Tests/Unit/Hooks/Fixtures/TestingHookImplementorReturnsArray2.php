<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Second valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted as a second hooked-in class.
 */
final class TestingHookImplementorReturnsArray2 implements TestingHookInterfaceReturnsArray
{
    /**
     * Gets called during HookProvider tests.
     *
     * @return string[]
     */
    public function testHookMethodReturnsArray(): array
    {
        return ['me2' => 'ok', 'overwritten' => 'replaced'];
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @return array[]
     */
    public function testHookMethodReturnsNestedArray(): array
    {
        return [
            'me1' => ['status' => true],
            'me2' => ['status' => false, 'newValue' => 'new2'],
        ];
    }
}
