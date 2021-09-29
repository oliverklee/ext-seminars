<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Seminars\Csv\Interfaces\CsvAccessCheck;

/**
 * This class provides the access check for the CSV export of registrations in the front end.
 */
class FrontEndRegistrationAccessCheck implements CsvAccessCheck
{
    /**
     * @var \Tx_Seminars_OldModel_Event
     */
    protected $event = null;

    /**
     * Sets the event for the access check.
     *
     * @param \Tx_Seminars_OldModel_Event $event
     */
    public function setEvent(\Tx_Seminars_OldModel_Event $event): void
    {
        $this->event = $event;
    }

    /**
     * Returns the event for the access check.
     */
    protected function getEvent(): ?\Tx_Seminars_OldModel_Event
    {
        return $this->event;
    }

    /**
     * Checks whether the logged-in user (if any) in the current environment has access to a CSV export.
     *
     * @return bool whether the logged-in user (if any) in the current environment has access to a CSV export.
     *
     * @throws \BadMethodCallException
     */
    public function hasAccess(): bool
    {
        if ($this->getEvent() === null) {
            throw new \BadMethodCallException('Please set an event first.', 1389096647);
        }
        if (!FrontEndLoginManager::getInstance()->isLoggedIn()) {
            return false;
        }

        $configuration = ConfigurationRegistry::get('plugin.tx_seminars_pi1');
        if (!$configuration->getAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView')) {
            return false;
        }

        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
        $vipsGroupUid = $configuration->getAsInteger('defaultEventVipsFeGroupID');

        return $this->getEvent()->isUserVip($user->getUid(), $vipsGroupUid);
    }
}
