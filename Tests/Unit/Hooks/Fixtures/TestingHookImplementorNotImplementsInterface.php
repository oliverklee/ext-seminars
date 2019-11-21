<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Invalid test interface implementation to use with the HookProviderTest.
 *
 * Will not be accepted, because it does not implement TestingHookInterface.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
final class TestingHookImplementorNotImplementsInterface implements TestingHookInterfaceNotExtendsHook
{
    /**
     * @return void
     */
    public function testHookMethod()
    {
    }
}
