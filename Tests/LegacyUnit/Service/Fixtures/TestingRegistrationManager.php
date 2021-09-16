<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

/**
 * Proxy class to make some things public.
 */
class TestingRegistrationManager extends \Tx_Seminars_Service_RegistrationManager
{
    /**
     * @param \Tx_Seminars_Model_Registration $registration
     * @param array $formData
     *
     * @return void
     */
    public function setRegistrationData(\Tx_Seminars_Model_Registration $registration, array $formData)
    {
        parent::setRegistrationData($registration, $formData);
    }
}
