<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

use OliverKlee\Seminars\Hooks\Interfaces\Hook;

/**
 * Valid test interface to use with the HookProviderTest.
 *
 * Will be accepted, because it extends Hook.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface TestingHookInterface extends Hook
{
    /**
     * Gets called during HookProvider tests.
     *
     * @return void
     */
    public function testHookMethod();
}
