<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;

/**
 * This class represents a front-end user.
 */
class FrontEndUser extends OelibFrontEndUser
{
    /**
     * Gets the registration record for which this user is related to as "additional registered person".
     */
    public function getRegistration(): ?Registration
    {
        /** @var Registration|null $registration */
        $registration = $this->getAsModel('tx_seminars_registration');

        return $registration;
    }

    /**
     * Sets the registration record for which this user is related to as "additional registered person".
     */
    public function setRegistration(?Registration $registration = null): void
    {
        $this->set('tx_seminars_registration', $registration);
    }
}
