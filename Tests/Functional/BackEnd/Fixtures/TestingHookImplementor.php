<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures;

use OliverKlee\Seminars\Hooks\Interfaces\BackEndModule;
use OliverKlee\Seminars\Model\Registration;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Testing implementor for the BE hooks.
 */
final class TestingHookImplementor implements BackEndModule, SingletonInterface
{
    private int $countCallForGeneralEmail = 0;

    public function modifyGeneralEmail(Registration $registration, MailMessage $email): void
    {
        $this->countCallForGeneralEmail++;
    }

    public function getCountCallForGeneralEmail(): int
    {
        return $this->countCallForGeneralEmail;
    }
}
