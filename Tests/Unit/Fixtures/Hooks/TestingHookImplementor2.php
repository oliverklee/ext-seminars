<?php
declare(strict_types = 1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookInterface;

/**
 * Second test interface implementation to use with the HookProvider
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class TestingHookImplementor2 implements TestingHookInterface
{
    /**
     * @var bool
     */
    public static $wasCalled = 0;

    public function __construct()
    {
        self::$wasCalled = 0;
    }

    /**
     * This function will be called during HookProvider tests.
     *
     * @return void
     */
    public function testHookMethod()
    {
        self::$wasCalled++;
    }
}
