<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures;

use OliverKlee\Seminars\Hooks\Interfaces\AlternativeEmailProcessor;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Testing implementor for the BE hooks.
 *
 * @author Oliver Heins <typo3-ext@bitmotion.de>
 */
final class AlternativeEmailProcessorHookImplementor implements AlternativeEmailProcessor, SingletonInterface
{
    /**
     * @var int
     */
    private $countCallForProcessAttendeeEmail = 0;

    /**
     * @var int
     */
    private $countCallForProcessAdditionalEmail = 0;

    /**
     * @var int
     */
    private $countCallForProcessAdditionalReviewerEmail = 0;

    /**
     * @var int
     */
    private $countCallForProcessOrganizerEmail = 0;

    /**
     * @var int
     */
    private $countCallForProcessReminderEmail = 0;

    /**
     * @var int
     */
    private $countCallForProcessReviewerEmail = 0;

    public function processAttendeeEmail(\Tx_Oelib_Mail $email, \Tx_Seminars_Model_Registration $registration)
    {
        $this->countCallForProcessAttendeeEmail++;
    }

    public function processAdditionalEmail(\Tx_Oelib_Mail $email, \Tx_Seminars_Model_Registration $registration)
    {
        $this->countCallForProcessAdditionalEmail++;
    }

    public function processAdditionalReviewerEmail(\Tx_Oelib_Mail $email)
    {
        $this->countCallForProcessAdditionalReviewerEmail++;
    }

    public function processOrganizerEmail(\Tx_Oelib_Mail $email, \Tx_Seminars_Model_Registration $registration)
    {
        $this->countCallForProcessOrganizerEmail++;
    }

    public function processReminderEmail(\Tx_Oelib_Mail $email, \Tx_Seminars_Model_Event $event)
    {
        $this->countCallForProcessReminderEmail++;
    }

    public function processReviewerEmail(\Tx_Oelib_Mail $email, \Tx_Seminars_Model_Event $event)
    {
        $this->countCallForProcessReviewerEmail++;
    }

    public function getCountCallForProcessAttendeeEmail(): int
    {
        return $this->countCallForProcessAttendeeEmail;
    }

    public function getCountCallForProcessAdditionalEmail(): int
    {
        return $this->countCallForProcessAdditionalEmail;
    }

    public function getCountCallForProcessAdditionalReviewerEmail(): int
    {
        return $this->countCallForProcessAdditionalReviewerEmail;
    }

    public function getCountCallForProcessOrganizerEmail(): int
    {
        return $this->countCallForProcessOrganizerEmail;
    }

    public function getCountCallForProcessReminderEmail(): int
    {
        return $this->countCallForProcessReminderEmail;
    }

    public function getCountCallForProcessReviewerEmail(): int
    {
        return $this->countCallForProcessReviewerEmail++;
    }
}
