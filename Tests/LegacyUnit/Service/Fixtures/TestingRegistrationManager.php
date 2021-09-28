<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

/**
 * Proxy class to make some things public.
 */
class TestingRegistrationManager extends \Tx_Seminars_Service_RegistrationManager
{
    public function setRegistrationData(\Tx_Seminars_Model_Registration $registration, array $formData): void
    {
        parent::setRegistrationData($registration, $formData);
    }
}
