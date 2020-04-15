<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model\Traits;

use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait that adds the method getEmailSender() to get the sender MailRole for the current Event.
 *
 * @author Pascal Rinker <projects@jweiland.net>
 */
trait EventEmailSenderTrait
{
    /**
     * Returns a MailRole with the default email data from TYPO3 if set.
     * It otherwise returns a MailRole with the mail of the first organizer.
     *
     * @return \Tx_Oelib_Interface_MailRole
     */
    public function getEmailSender(): \Tx_Oelib_Interface_MailRole
    {
        $systemEmailFromBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);
        if ($systemEmailFromBuilder->canBuild()) {
            $sender = $systemEmailFromBuilder->build();
        } else {
            $sender = $this->getFirstOrganizer();
        }
        return $sender;
    }
}
