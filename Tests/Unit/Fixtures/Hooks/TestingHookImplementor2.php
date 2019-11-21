<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookImplementor;

/**
 * Second valid test interface implementation to use with the HookProviderTest.
 *
 * Will be accepted as a second hooked-in class.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class TestingHookImplementor2 extends TestingHookImplementor
{
    /**
     * @var int
     */
    public static $wasCalled = 0;
}
