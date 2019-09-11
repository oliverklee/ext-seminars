<?php

use OliverKlee\Seminars\Interfaces\Hook\TestHook;

/**
 * This class just makes some public stuff for testing purposes.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class Tx_Seminars_Tests_Unit_Fixtures_Service_TestHookImplementor implements TestHook
{
    public $wasCalled = false;

    public function testHookMethod()
    {
        $this->wasCalled = true;
        return;
    }
}
