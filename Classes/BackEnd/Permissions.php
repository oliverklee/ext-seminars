<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Provides access to the BE table permissions for the currently logged-in user.
 */
class Permissions
{
    /**
     * @var non-empty-string
     */
    private const EVENTS_TABLE_NAME = 'tx_seminars_seminars';

    /**
     * @var non-empty-string
     */
    private const REGISTRATIONS_TABLE_NAME = 'tx_seminars_attendances';

    /**
     * @var bool
     */
    private $readAccessToEvents;

    /**
     * @var bool
     */
    private $readAccessToRegistrations;

    public function __construct()
    {
        $user = $GLOBALS['BE_USER'] ?? null;

        if (!$user instanceof BackendUserAuthentication) {
            throw new \BadMethodCallException('No BE user session found.', 1670069568);
        }

        $this->readAccessToEvents = $user->check('tables_select', self::EVENTS_TABLE_NAME);
        $this->readAccessToRegistrations = $user->check('tables_select', self::REGISTRATIONS_TABLE_NAME);
    }

    public function hasReadAccessToEvents(): bool
    {
        return $this->readAccessToEvents;
    }

    public function hasReadAccessToRegistrations(): bool
    {
        return $this->readAccessToRegistrations;
    }
}
