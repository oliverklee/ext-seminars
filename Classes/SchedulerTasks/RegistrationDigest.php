<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\SchedulerTask;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This class takes care of sending the daily registration digest email for events.
 */
class RegistrationDigest
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var Configuration
     */
    private $configuration = null;

    /**
     * @var EventMapper
     */
    private $eventMapper = null;

    public function injectObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Initializes the dependencies that cannot be injected using dependency injection.
     *
     * This method is idempotent, i.e., calling it multiple times will do no harm.
     *
     * @see isInitialized
     */
    public function initializeObject(): void
    {
        if ($this->isInitialized()) {
            return;
        }

        $this->configuration = ConfigurationRegistry::get('plugin.tx_seminars.registrationDigestEmail');
        $this->eventMapper = MapperRegistry::get(EventMapper::class);

        $this->initialized = true;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * This method is intended to be used for automated tests only.
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * This method is intended to be used for automated tests only.
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * This method is intended to be used for automated tests only.
     */
    public function getEventMapper(): EventMapper
    {
        return $this->eventMapper;
    }

    /**
     * This method is intended to be used for automated tests only.
     */
    public function setEventMapper(EventMapper $mapper): void
    {
        $this->eventMapper = $mapper;
    }

    /**
     * Executes this service and sends out a digest email if this is enabled in the configuration and if there
     * is anything to send.
     */
    public function execute(): void
    {
        if (!$this->configuration->getAsBoolean('enable')) {
            return;
        }
        $events = $this->eventMapper->findForRegistrationDigestEmail();
        if ($events->isEmpty()) {
            return;
        }

        $email = $this->buildEmail($events);
        $email->send();

        $this->updateDateOfLastDigest($events);
    }

    /**
     * @param Collection<Event> $events
     */
    private function buildEmail(Collection $events): MailMessage
    {
        $configuration = $this->configuration;
        $email = $this->objectManager->get(MailMessage::class);
        $email->setFrom($configuration->getAsString('fromEmail'), $configuration->getAsString('fromName'));
        $email->setTo($configuration->getAsString('toEmail'), $configuration->getAsString('toName'));
        $subject = LocalizationUtility::translate('registrationDigestEmail_Subject', 'seminars');
        $email->setSubject($subject);

        /** @var non-empty-string $plaintextTemplatePath */
        $plaintextTemplatePath = $this->getConfiguration()->getAsString('plaintextTemplate');
        $plaintextBody = $this->createContent($plaintextTemplatePath, $events);
        $email->setBody($plaintextBody);

        /** @var non-empty-string $htmlTemplatePath */
        $htmlTemplatePath = $this->getConfiguration()->getAsString('htmlTemplate');
        $htmlBody = $this->createContent($htmlTemplatePath, $events);
        $email->addPart($htmlBody, 'text/html');

        return $email;
    }

    /**
     * @param non-empty-string $templatePath in the EXT:... syntax
     * @param Collection<Event> $events
     *
     * @return string
     */
    private function createContent(string $templatePath, Collection $events): string
    {
        $view = $this->objectManager->get(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templatePath));
        $view->assign('events', $events);

        return (string)$view->render();
    }

    /**
     * @param Collection<Event> $events
     */
    private function updateDateOfLastDigest(Collection $events): void
    {
        $now = $GLOBALS['SIM_EXEC_TIME'];

        foreach ($events as $event) {
            $event->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp($now);
            $this->eventMapper->save($event);
        }
    }
}
