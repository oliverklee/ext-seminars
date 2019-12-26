<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted, because it implements TestingHookInterfaceReturnsArray.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
final class TestingHookImplementorReturnsArray implements TestingHookInterfaceReturnsArray
{
    /**
     * Gets called during HookProvider tests.
     *
     * @return array
     */
    public function testHookMethodReturnsArray(): array
    {
        return ['me' => 'ok','overwritten' => 'initial',];
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @return array[]
     */
    public function testHookMethodReturnsNestedArray(): array
    {
        return [
            'me' => ['status' => true ],
            'me1' => ['status' => false, 'newValue' => 'new1', ]
        ];
    }
}
