<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Seminars\BackEnd\RegistrationsList;
use OliverKlee\Seminars\Bag\RegistrationBag;
use OliverKlee\Seminars\Model\Registration;

/**
 * Use this interface for hooks concerning the backend registration list view.
 */
interface BackendRegistrationListView extends Hook
{
    /**
     * Modifies the list row template content just before it is rendered to HTML.
     *
     * This method is called once per list row, but the row may appear in the list of regular registrations or the
     * list of registrations on queue.
     *
     * @param Registration $registration the registration the row is made from
     * @param Template $template the template that will be used to create the registration list
     * @param RegistrationsList::REGISTRATIONS_ON_QUEUE|RegistrationsList::REGULAR_REGISTRATIONS $registrationsToShow
     */
    public function modifyListRow(Registration $registration, Template $template, int $registrationsToShow): void;

    /**
     * Modifies the list heading template content just before it is rendered to HTML.
     *
     * This method is called twice per list: First for the list of regular registrations, then for the list of
     * registrations on queue.
     *
     * @param RegistrationBag $registrationBag the registrationBag the heading is made for
     * @param Template $template the template that will be used to create the registration list
     * @param RegistrationsList::REGISTRATIONS_ON_QUEUE|RegistrationsList::REGULAR_REGISTRATIONS $registrationsToShow
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
     * registrations on queue.
     *
     * @param RegistrationBag $registrationBag the registrationBag the table is made for
     * @param Template $template the template that will be used to create the registration list
     * @param RegistrationsList::REGISTRATIONS_ON_QUEUE|RegistrationsList::REGULAR_REGISTRATIONS $registrationsToShow
     */
    public function modifyList(
        RegistrationBag $registrationBag,
        Template $template,
        int $registrationsToShow
    ): void;
}
