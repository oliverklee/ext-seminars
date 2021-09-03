<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Traits;

use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * This trait provides methods useful when testing FAL output.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
trait FalHelper
{
    /**
     * Creates an admin BE user to allow FAL to access any file.
     *
     * This is necessary to as the nimut testing framework always runs with
     * TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI.
     *
     * @return void
     */
    private function provideAdminBackEndUserForFal()
    {
        /** @var ObjectProphecy $backEndUserProphecy */
        $backEndUserProphecy = $this->prophesize(BackendUserAuthentication::class);

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $backEndUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $backEndUserProphecy->reveal();
    }
}
