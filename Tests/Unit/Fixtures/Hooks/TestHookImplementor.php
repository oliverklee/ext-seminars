<?php
declare(strict_types = 1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestHookInterface;

/**
 * Test interface implementation to use with the HookService
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class TestHookImplementor implements TestHookInterface
{
    /**
     * @var bool
     */
    public $wasCalled = false;

    /**
     * This function will be called during HookService tests.
     *
     * @return void
     */
    public function testHookMethod()
    {
        $this->wasCalled = true;
    }
}
