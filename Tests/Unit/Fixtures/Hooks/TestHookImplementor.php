<?php
declare(strict_types = 1);

namespace OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks;

use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestHookInterface;

/**
 * This class just makes some public stuff for testing purposes.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class TestHookImplementor implements TestHookInterface
{
    public $wasCalled = false;

    public function testHookMethod()
    {
        $this->wasCalled = true;
        return;
    }
}
