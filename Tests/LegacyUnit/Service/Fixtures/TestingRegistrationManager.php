<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

use OliverKlee\Seminars\Model\Registration;

/**
 * Proxy class to make some things public.
 */
class TestingRegistrationManager extends \Tx_Seminars_Service_RegistrationManager
{
    public function setRegistrationData(Registration $registration, array $formData): void
    {
        parent::setRegistrationData($registration, $formData);
    }
}
