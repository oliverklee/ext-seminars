<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Traits;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * This trait provides methods useful when testing FAL output.
 *
 * @mixin TestCase
 */
trait FalHelper
{
    /**
     * Creates an admin BE user to allow FAL to access any file.
     *
     * This is necessary to as the nimut testing framework always runs with
     * TYPO3_REQUESTTYPE_BE | TYPO3_REQUESTTYPE_CLI.
     */
    private function provideAdminBackEndUserForFal(): void
    {
        $backEndUserMock = $this->createMock(BackendUserAuthentication::class);

        $backEndUserMock->method('isAdmin')->willReturn(true);
        $GLOBALS['BE_USER'] = $backEndUserMock;
    }
}
