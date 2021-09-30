<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

use OliverKlee\Seminars\Model\Registration;
use OliverKlee\Seminars\Service\RegistrationManager;

/**
 * Proxy class to make some things public.
 */
class TestingRegistrationManager extends RegistrationManager
{
    public function setRegistrationData(Registration $registration, array $formData): void
    {
        parent::setRegistrationData($registration, $formData);
    }
}
