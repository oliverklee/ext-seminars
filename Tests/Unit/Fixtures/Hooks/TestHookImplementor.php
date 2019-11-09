<?php
declare(strict_types = 1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestHookInterface;

/**
 * Test interface implementation to use with the HookProvider
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class TestHookImplementor implements TestHookInterface
{
    /**
     * @var bool
     */
    private $wasCalled = false;

    /**
     * This function will be called during HookProvider tests.
     *
     * @return void
     */
    public function testHookMethod()
    {
        $this->wasCalled = true;
    }

    /**
     * @return bool
     */
    public function getWasCalled()
    {
        return $this->wasCalled;
    }
}
