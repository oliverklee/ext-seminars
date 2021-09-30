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
    /**
     * @var int
     */
    private $countCallForGeneralEmail = 0;

    /**
     * @var int
     */
    private $countCallForConfirmEmail = 0;

    /**
     * @var int
     */
    private $countCallForCancelEmail = 0;

    public function modifyGeneralEmail(Registration $registration, MailMessage $eMail): void
    {
        $this->countCallForGeneralEmail++;
    }

    public function modifyConfirmEmail(Registration $registration, MailMessage $eMail): void
    {
        $this->countCallForConfirmEmail++;
    }

    public function modifyCancelEmail(Registration $registration, MailMessage $eMail): void
    {
        $this->countCallForCancelEmail++;
    }

    public function getCountCallForGeneralEmail(): int
    {
        return $this->countCallForGeneralEmail;
    }

    public function getCountCallForConfirmEmail(): int
    {
        return $this->countCallForConfirmEmail;
    }

    public function getCountCallForCancelEmail(): int
    {
        return $this->countCallForCancelEmail;
    }
}
