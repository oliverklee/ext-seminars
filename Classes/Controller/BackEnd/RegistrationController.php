<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;

/**
 * Controller for the registration list in the BE module.
 */
class RegistrationController extends AbstractController
{
    /**
     * @var non-empty-string
     */
    private const CSV_FILENAME = 'registrations.csv';

    /**
     * @var non-empty-string
     */
    private const TABLE_NAME = 'tx_seminars_attendances';

    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    public function injectRegistrationRepository(RegistrationRepository $repository): void
    {
        $this->registrationRepository = $repository;
    }
}
