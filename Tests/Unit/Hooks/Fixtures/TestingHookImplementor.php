<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted, because it implements TestingHookInterface.
 */
class TestingHookImplementor implements TestingHookInterface
{
    /**
     * @var int
     */
    public static $wasCalled = 0;

    public function __construct()
    {
        static::$wasCalled = 0;
    }

    /**
     * Gets called during HookProvider tests.
     *
     * @return void
     */
    public function testHookMethod()
    {
        static::$wasCalled++;
    }
}
