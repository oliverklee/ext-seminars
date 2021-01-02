<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Seminars\Csv\Interfaces\CsvAccessCheck;

/**
 * This class provides the access check for the CSV export of registrations in the front end.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
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
     *
     * @return void
     */
    public function setEvent(\Tx_Seminars_OldModel_Event $event)
    {
        $this->event = $event;
    }

    /**
     * Returns the event for the access check.
     *
     * @return \Tx_Seminars_OldModel_Event|null
     */
    protected function getEvent()
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

        $configuration = \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1');
        if (!$configuration->getAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView')) {
            return false;
        }

        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
        $vipsGroupUid = $configuration->getAsInteger('defaultEventVipsFeGroupID');

        return $this->getEvent()->isUserVip($user->getUid(), $vipsGroupUid);
    }
}
