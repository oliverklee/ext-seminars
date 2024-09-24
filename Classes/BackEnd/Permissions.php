<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Provides access to the BE table permissions for the currently logged-in user.
 */
class Permissions implements SingletonInterface
{
    /**
     * @var non-empty-string
     */
    private const EVENTS_TABLE_NAME = 'tx_seminars_seminars';

    /**
     * @var non-empty-string
     */
    private const REGISTRATIONS_TABLE_NAME = 'tx_seminars_attendances';

    private bool $readAccessToEvents;

    private bool $readAccessToRegistrations;

    private bool $writeAccessToEvents;

    private bool $writeAccessToRegistrations;

    public function __construct()
    {
        $user = $GLOBALS['BE_USER'] ?? null;

        if (!$user instanceof BackendUserAuthentication) {
            throw new \BadMethodCallException('No BE user session found.', 1670069568);
        }

        $this->readAccessToEvents = $user->check('tables_select', self::EVENTS_TABLE_NAME);
        $this->readAccessToRegistrations = $user->check('tables_select', self::REGISTRATIONS_TABLE_NAME);
        $this->writeAccessToEvents = $user->check('tables_modify', self::EVENTS_TABLE_NAME);
        $this->writeAccessToRegistrations = $user->check('tables_modify', self::REGISTRATIONS_TABLE_NAME);
    }

    public function hasReadAccessToEvents(): bool
    {
        return $this->readAccessToEvents;
    }

    public function hasReadAccessToRegistrations(): bool
    {
        return $this->readAccessToRegistrations;
    }

    public function hasWriteAccessToEvents(): bool
    {
        return $this->writeAccessToEvents;
    }

    public function hasWriteAccessToRegistrations(): bool
    {
        return $this->writeAccessToRegistrations;
    }
}
