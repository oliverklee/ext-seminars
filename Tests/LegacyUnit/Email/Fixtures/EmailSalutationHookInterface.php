<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Email\Fixtures;

use OliverKlee\Seminars\Model\FrontEndUser;

/**
 * Interface for building mocks for email salutation hook tests.
 */
interface EmailSalutationHookInterface
{
    /**
     * @param string[] $salutationParts
     */
    public function modifySalutation(array $salutationParts, FrontEndUser $user): void;
}
