<?php
declare(strict_types = 1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

/**
 * Interface for building mocks for email salutation hook tests.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface EmailSalutationHookInterface
{
    /**
     * @param string[] $salutationParts
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return void
     */
    public function modifySalutation(array $salutationParts, \Tx_Seminars_Model_FrontEndUser $user);
}
