<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Second valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted as a second hooked-in class.
 */
final class TestingHookImplementor2 extends TestingHookImplementor
{
    /**
     * @var int
     */
    public static $wasCalled = 0;
}
