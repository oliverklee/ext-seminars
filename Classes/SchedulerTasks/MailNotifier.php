<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\SchedulerTasks;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Seminars\Csv\EmailRegistrationListView;
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;
use OliverKlee\Seminars\Service\EmailService;
use OliverKlee\Seminars\Service\EventStatusService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This class sends reminders to the organizers.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
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
     * @var \Tx_Seminars_Mapper_Event
     */
    protected $eventMapper = null;

    /**
     * @var \Tx_Oelib_AbstractMailer
     */
    protected $mailer = null;

    /**
     * @var RegistrationDigest
     */
    protected $registrationDigest = null;

    /**
     * Sets up the dependencies (as we cannot use dependency injection on scheduler tasks).
     *
     * @return void
     */
    protected function constituteDependencies()
    {
        // This is necessary so that the configuration is fetched from the provided page UID early.
        $this->getConfiguration();
        $this->eventStatusService = GeneralUtility::makeInstance(EventStatusService::class);
        $this->emailService = GeneralUtility::makeInstance(EmailService::class);
        $this->eventMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $this->mailer = $mailerFactory->getMailer();
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
     *
     * @return void
     */
    protected function executeAfterInitialization()
    {
        $this->sendEventTakesPlaceReminders();
        $this->sendCancellationDeadlineReminders();
        $this->automaticallyChangeEventStatuses();
        $this->registrationDigest->execute();
    }

    /**
     * Sets the UID of the page with the TS configuration for this task.
     *
     * @param int $pageUid
     *
     * @return void
     */
    public function setConfigurationPageUid(int $pageUid)
    {
        $this->configurationPageUid = $pageUid;
    }

    /**
     * @return int
     */
    public function getConfigurationPageUid(): int
    {
        return $this->configurationPageUid;
    }

    /**
     * Sends event-takes-place reminders to the corresponding organizers and
     * commits the flag for this reminder being sent to the database.
     *
     * @return void
     */
    public function sendEventTakesPlaceReminders()
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
     *
     * @return void
     */
    public function sendCancellationDeadlineReminders()
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
     * @param \Tx_Seminars_OldModel_Event $event event for which to send the reminder to its organizers
     * @param string $messageKey locallang key for the message content and the subject for the e-mail to send, must not be empty
     *
     * @return void
     */
    private function sendRemindersToOrganizers(\Tx_Seminars_OldModel_Event $event, string $messageKey)
    {
        $attachment = null;

        /** @var \Tx_Seminars_OldModel_Organizer $replyTo */
        $replyTo = $event->getFirstOrganizer();
        $sender = $event->getEmailSender();
        $subject = $this->customizeMessage($messageKey . 'Subject', $event);
        if ($this->shouldCsvFileBeAdded($event)) {
            $attachment = $this->getCsv($event->getUid());
        }

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($event->getOrganizerBag() as $organizer) {
            /** @var \Tx_Oelib_Mail $eMail */
            $eMail = GeneralUtility::makeInstance(\Tx_Oelib_Mail::class);
            $eMail->setSender($sender);
            $eMail->setReplyTo($replyTo);
            $eMail->setSubject($subject);
            $eMail->addRecipient($organizer);
            $eMail->setMessage($this->customizeMessage($messageKey, $event, $organizer->getName()));
            if ($attachment !== null) {
                $eMail->addAttachment($attachment);
            }

            $this->mailer->send($eMail);
        }
    }

    /**
     * Returns events in confirmed status which are about to take place and for
     * which no reminder has been sent yet.
     *
     * @return \Tx_Seminars_OldModel_Event[] events for which to send the event-takes-place reminder to
     *               their organizers, will be empty if there are none
     */
    private function getEventsToSendEventTakesPlaceReminderFor(): array
    {
        $days = $this->getDaysBeforeBeginDate();
        if ($days == 0) {
            return [];
        }

        $result = [];

        $builder = $this->getSeminarBagBuilder(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);
        $builder->limitToEventTakesPlaceReminderNotSent();
        $builder->limitToDaysBeforeBeginDate($days);
        /** @var \Tx_Seminars_OldModel_Event $event */
        foreach ($builder->build() as $event) {
            $result[] = $event;
        }

        return $result;
    }

    /**
     * Returns events in planned status for which the cancellation deadline has
     * just passed and for which no reminder has been sent yet.
     *
     * @return \Tx_Seminars_OldModel_Event[] events for which to send the cancellation reminder to their
     *               organizers, will be empty if there are none
     */
    private function getEventsToSendCancellationDeadlineReminderFor(): array
    {
        if (!$this->getConfiguration()->getAsBoolean('sendCancelationDeadlineReminder')) {
            return [];
        }

        $result = [];

        /** @var \Tx_Seminars_BagBuilder_Event $builder */
        $builder = $this->getSeminarBagBuilder(\Tx_Seminars_Model_Event::STATUS_PLANNED);
        $builder->limitToCancelationDeadlineReminderNotSent();
        /** @var \Tx_Seminars_Bag_Event $bag */
        $bag = $builder->build();

        /** @var \Tx_Seminars_OldModel_Event $event */
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
     *                 reminder should be send, will be > 0 if this option is
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
     * @param int $status status to limit the builder to, must be either \Tx_Seminars_Model_Event::STATUS_PLANNED or ::CONFIRMED
     *
     * @return \Tx_Seminars_BagBuilder_Event builder for the seminar bag
     */
    private function getSeminarBagBuilder(int $status): \Tx_Seminars_BagBuilder_Event
    {
        /** @var \Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Event::class);
        $builder->setTimeFrame('upcomingWithBeginDate');
        $builder->limitToStatus($status);

        return $builder;
    }

    /**
     * Returns the CSV output for the list of registrations for the event with the provided UID.
     *
     * @param int $eventUid UID of the event to create the output for, must be > 0
     *
     * @return \Tx_Oelib_Attachment CSV list of registrations for the given event
     */
    private function getCsv(int $eventUid): \Tx_Oelib_Attachment
    {
        /** @var EmailRegistrationListView $csvCreator */
        $csvCreator = GeneralUtility::makeInstance(EmailRegistrationListView::class);
        $csvCreator->setEventUid($eventUid);
        $csvString = $csvCreator->render();

        /** @var \Tx_Oelib_Attachment $attachment */
        $attachment = GeneralUtility::makeInstance(\Tx_Oelib_Attachment::class);
        $attachment->setContent($csvString);
        $attachment->setContentType('text/csv');
        $attachment->setFileName($this->getConfiguration()->getAsString('filenameForRegistrationsCsv'));

        return $attachment;
    }

    /**
     * Returns localized e-mail content customized for the provided event and
     * the provided organizer.
     *
     * @param string $locallangKey
     *        locallang key for the text in which to replace key words beginning with "%" by the event's data, must not be empty
     * @param \Tx_Seminars_OldModel_Event $event
     *        event for which to customize the text
     * @param string $organizerName
     *        name of the organizer, may be empty if no organizer name needs to be inserted in the text
     *
     * @return string the localized e-mail content, will not be empty
     */
    private function customizeMessage(string $locallangKey, \Tx_Seminars_OldModel_Event $event, string $organizerName = ''): string
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
     * @return string formatted date according to the TS setup configuration for
     *                'dateFormatYMD', will not be empty
     */
    private function getDate(int $timestamp): string
    {
        return strftime($this->getConfiguration()->getAsString('dateFormatYMD'), $timestamp);
    }

    /**
     * Checks whether the CSV file should be added to the e-mail.
     *
     * @param \Tx_Seminars_OldModel_Event $event the event to send the e-mail for
     *
     * @return bool TRUE if the CSV file should be added, FALSE otherwise
     */
    private function shouldCsvFileBeAdded(\Tx_Seminars_OldModel_Event $event): bool
    {
        return $this->getConfiguration()->getAsBoolean(
            'addRegistrationCsvToOrganizerReminderMail'
        ) && $event->hasAttendances();
    }

    /**
     * Automatically changes the status for events for which this is enabled.
     *
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    public function automaticallyChangeEventStatuses()
    {
        $languageService = $this->getLanguageService();

        /** @var \Tx_Seminars_Model_Event $event */
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

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService|null
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'] ?? null;
    }

    /**
     * Uses the language configured in the current BE user.
     *
     * @return void
     */
    private function useUserConfiguredLanguage()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->getLanguageService()->init($user->getLanguage());
    }

    /**
     * Returns the plugin.tx_seminars configuration.
     *
     * @return \Tx_Oelib_Configuration
     */
    protected function getConfiguration(): \Tx_Oelib_Configuration
    {
        \Tx_Oelib_PageFinder::getInstance()->setPageUid($this->getConfigurationPageUid());

        return \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars');
    }
}
