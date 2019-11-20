<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Hooks\Interfaces\Hook;

/**
 * Invalid test interface to use with the HookProviderTest
 *
 * Will not be accepted, because it does not extend Hook
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface TestingHookInterfaceNotExtendsHook
{
    /**
     * @return void
     */
    public function testHookMethod();
}
