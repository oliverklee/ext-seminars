<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookInterface;
use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookInterfaceNotExtendsHook;

/**
 * Invalid test interface implementation to use with the HookProviderTest.
 *
 * Will not be accepted, because it does not implement TestingHookInterface.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class TestingHookImplementorNotImplementsInterface implements TestingHookInterfaceNotExtendsHook
{
    /**
     * @return void
     */
    public function testHookMethod()
    {
    }
}
