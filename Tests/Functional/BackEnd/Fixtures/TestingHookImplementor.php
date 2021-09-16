<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Testing implementor for the BE hooks.
 */
final class TestingHookImplementor implements \Tx_Seminars_Interfaces_Hook_BackEndModule, SingletonInterface
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

    public function modifyGeneralEmail(\Tx_Seminars_Model_Registration $registration, MailMessage $eMail)
    {
        $this->countCallForGeneralEmail++;
    }

    public function modifyConfirmEmail(\Tx_Seminars_Model_Registration $registration, MailMessage $eMail)
    {
        $this->countCallForConfirmEmail++;
    }

    public function modifyCancelEmail(\Tx_Seminars_Model_Registration $registration, MailMessage $eMail)
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
