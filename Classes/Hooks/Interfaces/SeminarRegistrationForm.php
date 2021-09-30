<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\FrontEnd\RegistrationForm;

/**
 * Use this interface for hooks concerning the registration form.
 */
interface SeminarRegistrationForm extends Hook
{
    /**
     * Modifies the header of the seminar registration form.
     *
     * @param DefaultController $controller the calling controller
     */
    public function modifyRegistrationHeader(DefaultController $controller): void;

    /**
     * Modifies the seminar registration form.
     *
     * @param DefaultController $controller the calling controller
     */
    public function modifyRegistrationForm(DefaultController $controller, RegistrationForm $registrationEditor): void;

    /**
     * Modifies the footer of the seminar registration form.
     *
     * @param DefaultController $controller the calling controller
     */
    public function modifyRegistrationFooter(DefaultController $controller): void;
}
