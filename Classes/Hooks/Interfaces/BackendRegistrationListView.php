<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Seminars\Bag\RegistrationBag;

/**
 * Use this interface for hooks concerning the backend registration list view.
 */
interface BackendRegistrationListView extends Hook
{
    /**
     * Modifies the list row template content just before it is rendered to HTML.
     *
     * This method is called once per list row, but the row may appear in the list of regular registrations or the
     * list of registrations on queue. Check $registrationsToShow (can be one of
     * `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGISTRATIONS_ON_QUEUE`
     * and `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGULAR_REGISTRATIONS`) to distinguish.
     *
     * @param \Tx_Seminars_Model_Registration $registration the registration the row is made from
     * @param Template $template the template that will be used to create the registration list
     * @param int $registrationsToShow the type of registration shown in the list
     */
    public function modifyListRow(
        \Tx_Seminars_Model_Registration $registration,
        Template $template,
        int $registrationsToShow
    ): void;

    /**
     * Modifies the list heading template content just before it is rendered to HTML.
     *
     * This method is called twice per list: First for the list of regular registrations, then for the list of
     * registrations on queue. Check $registrationsToShow (can be one of
     * `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGISTRATIONS_ON_QUEUE`
     * and `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGULAR_REGISTRATIONS`) to distinguish.
     *
     * @param RegistrationBag $registrationBag the registrationBag the heading is made for
     * @param Template $template the template that will be used to create the registration list
     * @param int $registrationsToShow the type of registration shown in the list
     */
    public function modifyListHeader(
        RegistrationBag $registrationBag,
        Template $template,
        int $registrationsToShow
    ): void;

    /**
     * Modifies the complete list template content just before it is rendered to HTML.
     *
     * This method is called twice per list: First for the list of regular registrations, then for the list of
     * registrations on queue. Check $registrationsToShow (can be one of
     * `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGISTRATIONS_ON_QUEUE`
     * and `\OliverKlee\Seminars\BackEnd\RegistrationsList::REGULAR_REGISTRATIONS`) to distinguish.
     *
     * @param RegistrationBag $registrationBag the registrationBag the table is made for
     * @param Template $template the template that will be used to create the registration list
     * @param int $registrationsToShow the type of registration shown in the list
     */
    public function modifyList(
        RegistrationBag $registrationBag,
        Template $template,
        int $registrationsToShow
    ): void;
}
