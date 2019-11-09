<?php
declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Interfaces\Hook;

/**
 * Test interface to use with the HookProvider
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface TestHookInterface extends Hook
{
    /**
     * This function will be called during HookProvider tests.
     *
     * @return void
     */
    public function testHookMethod();
}
