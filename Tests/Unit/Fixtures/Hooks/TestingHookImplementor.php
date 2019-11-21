<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookInterface;

/**
 * Valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted, because it implements TestingHookInterface.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
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
