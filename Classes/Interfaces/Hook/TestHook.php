<?php

namespace OliverKlee\Seminars\Interfaces\Hook;

use OliverKlee\Seminars\Interfaces\Hook;

/**
 * Test interface to use with the HookService
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface TestHook extends Hook
{
    /**
     * Test Hook Method
     *
     * This function will be called during HookService tests.
     *
     * @return void
     */
    public function testHookMethod();
}
