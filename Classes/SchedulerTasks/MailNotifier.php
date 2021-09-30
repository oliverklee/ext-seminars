<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\SchedulerTasks;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\BagBuilder\EventBagBuilder;
use OliverKlee\Seminars\Csv\EmailRegistrationListView;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;
use OliverKlee\Seminars\Service\EmailService;
use OliverKlee\Seminars\Service\EventStatusService;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This class sends reminders to the organizers.
 */
class MailNotifier extends AbstractTask
{
    /**
     * @var int
     */
    protected $configurationPageUid = 0;

    /**
     * @var EventStatusService
     */
    protected $eventStatusService = null;

    /**
     * @var EmailService
     */
    protected $emailService = null;

    /**
     * @var EventMapper
     */
    protected $eventMapper = null;

    /**
     * @var RegistrationDigest
     */
    protected $registrationDigest = null;

    /**
     * Sets up the dependencies (as we cannot use dependency injection on scheduler tasks).
     */
    protected function constituteDependencies(): void
    {
        // This is necessary so that the configuration is fetched from the provided page UID early.
        $this->getConfiguration();
        $this->eventStatusService = GeneralUtility::makeInstance(EventStatusService::class);
        $this->emailService = GeneralUtility::makeInstance(EmailService::class);
        $this->eventMapper = MapperRegistry::get(EventMapper::class);
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->registrationDigest = $objectManager->get(RegistrationDigest::class);

        $this->useUserConfiguredLanguage();
        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
    }

    /**
     * Runs the task.
     *
     * @return bool true on successful execution, false on error
     */
    public function execute(): bool
    {
        if ($this->getConfigurationPageUid() <= 0) {
            return false;
        }

        $this->constituteDependencies();
        $this->executeAfterInitialization();

        return true;
    }

    /**
     * Executes the single steps.
     */
    protected function executeAfterInitialization(): void
    {
        $this->sendEventTakesPlaceReminders();
        $this->sendCancellationDeadlineReminders();
        $this->automaticallyChangeEventStatuses();
        $this->registrationDigest->execute();
    }

    /**
     * Sets the UID of the page with the TS configuration for this task.
     */
    public function setConfigurationPageUid(int $pageUid): void
    {
        $this->configurationPageUid = $pageUid;
    }

    public function getConfigurationPageUid(): int
    {
        return $this->configurationPageUid;
    }

    /**
     * Sends event-takes-place reminders to the corresponding organizers and
     * commits the flag for this reminder being sent to the database.
     */
    public function sendEventTakesPlaceReminders(): void
    {
        foreach ($this->getEventsToSendEventTakesPlaceReminderFor() as $event) {
            $this->sendRemindersToOrganizers(
                $event,
                'email_eventTakesPlaceReminder'
            );
            $event->setEventTakesPlaceReminderSentFlag();
            $event->commitToDatabase();
        }
    }

    /**
     * Sends cancellation deadline reminders to the corresponding organizers and
     * commits the flag for this reminder being sent to the database.
     */
    public function sendCancellationDeadlineReminders(): void
    {
        foreach ($this->getEventsToSendCancellationDeadlineReminderFor() as $event) {
            $this->sendRemindersToOrganizers(
                $event,
                'email_cancelationDeadlineReminder'
            );
            $event->setCancelationDeadlineReminderSentFlag();
            $event->commitToDatabase();
        }
    }

    /**
     * Sends an e-mail to the organizers of the provided event.
     *
     * @param LegacyEvent $event event for which to send the reminder to its organizers
     * @param non-empty-string $messageKey locallang key for the message content and the subject for the e-mail to send
     */
    private function sendRemindersToOrganizers(LegacyEvent $event, string $messageKey): void
    {
        $attachment = null;

        $replyTo = $event->getFirstOrganizer();
        $sender = $event->getEmailSender();
        $subject = $this->customizeMessage($messageKey . 'Subject', $event);
        if ($this->shouldCsvFileBeAdded($event)) {
            $attachment = $this->getCsv($event->getUid());
        }

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($event->getOrganizerBag() as $organizer) {
            /** @var MailMessage $mail */
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $mail->setFrom($sender->getEmailAddress(), $sender->getName());
            $mail->setTo($organizer->getEmailAddress(), $organizer->getName());
            if ($replyTo instanceof \Tx_Seminars_OldModel_Organizer) {
                $mail->setReplyTo($replyTo->getEmailAddress(), $replyTo->getName());
            }
            $mail->setSubject($subject);
            $mail->setBody($this->customizeMessage($messageKey, $event, $organizer->getName()));
            if ($attachment !== null) {
                $mail->attach($attachment);
            }

            $mail->send();
        }
    }

    /**
     * Returns events in confirmed status which are about to take place and for
     * which no reminder has been sent yet.
     *
     * @return array<int, LegacyEvent> events for which to send the event-takes-place reminder to
     *               their organizers, will be empty if there are none
     */
    private function getEventsToSendEventTakesPlaceReminderFor(): array
    {
        $days = $this->getDaysBeforeBeginDate();
        if ($days == 0) {
            return [];
        }

        $result = [];

        $builder = $this->getSeminarBagBuilder(Event::STATUS_CONFIRMED);
        $builder->limitToEventTakesPlaceReminderNotSent();
        $builder->limitToDaysBeforeBeginDate($days);
        /** @var LegacyEvent $event */
        foreach ($builder->build() as $event) {
            $result[] = $event;
        }

        return $result;
    }

    /**
     * Returns events in planned status for which the cancellation deadline has
     * just passed and for which no reminder has been sent yet.
     *
     * @return array<int, LegacyEvent> events for which to send the cancellation reminder to their
     *               organizers, will be empty if there are none
     */
    private function getEventsToSendCancellationDeadlineReminderFor(): array
    {
        if (!$this->getConfiguration()->getAsBoolean('sendCancelationDeadlineReminder')) {
            return [];
        }

        $result = [];

        /** @var EventBagBuilder $builder */
        $builder = $this->getSeminarBagBuilder(Event::STATUS_PLANNED);
        $builder->limitToCancelationDeadlineReminderNotSent();
        /** @var EventBag $bag */
        $bag = $builder->build();

        /** @var LegacyEvent $event */
        foreach ($bag as $event) {
            if ($event->getCancelationDeadline() < $GLOBALS['SIM_EXEC_TIME']) {
                $result[] = $event;
            }
        }

        return $result;
    }

    /**
     * Returns the TS setup configuration value of
     * 'sendEventTakesPlaceReminderDaysBeforeBeginDate'.
     *
     * @return int how many days before an event the event-takes-place
     *                 reminder should be sent, will be > 0 if this option is
     *                 enabled, zero disables sending the reminder
     */
    private function getDaysBeforeBeginDate(): int
    {
        return $this->getConfiguration()->getAsInteger('sendEventTakesPlaceReminderDaysBeforeBeginDate');
    }

    /**
     * Returns a seminar bag builder already limited to upcoming events with a
     * begin date and status $status.
     *
     * @param int $status status to limit the builder to, must be either ::STATUS_PLANNED or ::CONFIRMED
     *
     * @return EventBagBuilder builder for the seminar bag
     */
    private function getSeminarBagBuilder(int $status): EventBagBuilder
    {
        /** @var EventBagBuilder $builder */
        $builder = GeneralUtility::makeInstance(EventBagBuilder::class);
        $builder->setTimeFrame('upcomingWithBeginDate');
        $builder->limitToStatus($status);

        return $builder;
    }

    /**
     * Returns the CSV output for the list of registrations for the event with the provided UID.
     *
     * @param int $eventUid UID of the event to create the output for, must be > 0
     *
     * @return \Swift_Attachment CSV list of registrations for the given event
     */
    private function getCsv(int $eventUid): \Swift_Attachment
    {
        /** @var EmailRegistrationListView $csvCreator */
        $csvCreator = GeneralUtility::makeInstance(EmailRegistrationListView::class);
        $csvCreator->setEventUid($eventUid);
        $csvString = $csvCreator->render();

        $fileName = $this->getConfiguration()->getAsString('filenameForRegistrationsCsv');
        /** @var \Swift_Attachment $attachment */
        $attachment = \Swift_Attachment::newInstance($csvString, $fileName, 'text/csv');

        return $attachment;
    }

    /**
     * Returns localized e-mail content customized for the provided event and
     * the provided organizer.
     *
     * @param non-empty-string $locallangKey locallang key for the text in which to replace keywords beginning
     *        with "%" by the event's data
     * @param LegacyEvent $event
     *        event for which to customize the text
     * @param string $organizerName name of the organizer, may be empty if no organizer name needs to be inserted
     *        in the text
     *
     * @return string the localized e-mail content, will not be empty
     */
    private function customizeMessage(string $locallangKey, LegacyEvent $event, string $organizerName = ''): string
    {
        $result = $this->getLanguageService()->getLL($locallangKey);

        foreach (
            [
            '%begin_date' => $this->getDate($event->getBeginDateAsTimestamp()),
            '%days' => $this->getDaysBeforeBeginDate(),
            '%event' => $event->getTitle(),
            '%organizer' => $organizerName,
            '%registrations' => $event->getAttendances(),
            '%uid' => $event->getUid(),
            ] as $search => $replace
        ) {
            $result = str_replace($search, $replace, $result);
        }

        return $result;
    }

    /**
     * Returns a timestamp formatted according to the current configuration.
     *
     * @param int $timestamp timestamp, must be >= 0
     *
     * @return string formatted date according to the TS setup configuration for 'dateFormatYMD', will not be empty
     */
    private function getDate(int $timestamp): string
    {
        return strftime($this->getConfiguration()->getAsString('dateFormatYMD'), $timestamp);
    }

    /**
     * Checks whether the CSV file should be added to the e-mail.
     *
     * @param LegacyEvent $event the event to send the e-mail for
     */
    private function shouldCsvFileBeAdded(LegacyEvent $event): bool
    {
        return $this->getConfiguration()->getAsBoolean('addRegistrationCsvToOrganizerReminderMail')
            && $event->hasAttendances();
    }

    /**
     * Automatically changes the status for events for which this is enabled.
     *
     * @throws \UnexpectedValueException
     */
    public function automaticallyChangeEventStatuses(): void
    {
        $languageService = $this->getLanguageService();

        /** @var Event $event */
        foreach ($this->eventMapper->findForAutomaticStatusChange() as $event) {
            $statusWasChanged = $this->eventStatusService->updateStatusAndSave($event);
            if (!$statusWasChanged) {
                continue;
            }

            if ($event->isConfirmed()) {
                $subject = $languageService->getLL('email-event-confirmed-subject');
                $body = $languageService->getLL('email-event-confirmed-body');
            } elseif ($event->isCanceled()) {
                $subject = $languageService->getLL('email-event-canceled-subject');
                $body = $languageService->getLL('email-event-canceled-body');
            } else {
                throw new \UnexpectedValueException(
                    'Event status for event #' . $event->getUid() . ' was still "planned" after the status change.',
                    1457982810
                );
            }

            $this->emailService->sendEmailToAttendees($event, $subject, $body);
        }
    }

    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }

    /**
     * Uses the language configured in the current BE user.
     */
    private function useUserConfiguredLanguage(): void
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->getLanguageService()->init($user->getLanguage());
    }

    protected function getConfiguration(): Configuration
    {
        PageFinder::getInstance()->setPageUid($this->getConfigurationPageUid());

        return ConfigurationRegistry::get('plugin.tx_seminars');
    }
}
