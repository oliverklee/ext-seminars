<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures;

/**
 * Invalid test interface to use with the HookProviderTest.
 *
 * Will not be accepted, because it does not extend Hook.
 */
interface TestingHookInterfaceNotExtendsHook
{
    public function testHookMethod(): void;
}
