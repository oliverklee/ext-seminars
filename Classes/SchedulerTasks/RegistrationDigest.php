<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\SchedulerTask;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This class takes care of sending the daily registration digest email for events.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
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
     * @var \Tx_Seminars_Mapper_Event
     */
    private $eventMapper = null;

    /**
     * @param ObjectManagerInterface $objectManager
     *
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Initializes the dependencies that cannot be injected using dependency injection.
     *
     * This method is idempotent, i.e., calling it multiple times will do no harm.
     *
     * @see isInitialized
     *
     * @return void
     */
    public function initializeObject()
    {
        if ($this->isInitialized()) {
            return;
        }

        $this->configuration = ConfigurationRegistry::get('plugin.tx_seminars.registrationDigestEmail');
        $this->eventMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);

        $this->initialized = true;
    }

    /**
     * @return bool
     */
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
     *
     * @return void
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * This method is intended to be used for automated tests only.
     *
     * @return \Tx_Seminars_Mapper_Event the initialized event mapper
     */
    public function getEventMapper(): \Tx_Seminars_Mapper_Event
    {
        return $this->eventMapper;
    }

    /**
     * This method is intended to be used for automated tests only.
     *
     * @param \Tx_Seminars_Mapper_Event $mapper
     *
     * @return void
     */
    public function setEventMapper(\Tx_Seminars_Mapper_Event $mapper)
    {
        $this->eventMapper = $mapper;
    }

    /**
     * Executes this service and sends out a digest email if this is enabled in the configuration and if there
     * is anything to send.
     */
    public function execute()
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
     * @param Collection $events the Collection<\Tx_Seminars_Model_Event> that have new registrations
     *
     * @return MailMessage
     */
    private function buildEmail(Collection $events): MailMessage
    {
        $configuration = $this->configuration;
        $email = $this->objectManager->get(MailMessage::class);
        $email->setFrom($configuration->getAsString('fromEmail'), $configuration->getAsString('fromName'));
        $email->setTo($configuration->getAsString('toEmail'), $configuration->getAsString('toName'));
        $subject = LocalizationUtility::translate('registrationDigestEmail_Subject', 'seminars');
        $email->setSubject($subject);

        $plaintextTemplatePath = $this->getConfiguration()->getAsString('plaintextTemplate');
        $plaintextBody = $this->createContent($plaintextTemplatePath, $events);
        $email->setBody($plaintextBody);

        $htmlTemplatePath = $this->getConfiguration()->getAsString('htmlTemplate');
        $htmlBody = $this->createContent($htmlTemplatePath, $events);
        $email->addPart($htmlBody, 'text/html');

        return $email;
    }

    /**
     * @param string $templatePath in the EXT:... syntax
     * @param Collection $events Collection<\Tx_Seminars_Model_Event>
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
     * @param Collection $events the Collection<\Tx_Seminars_Model_Event> that have new registrations
     *
     * @return void
     */
    private function updateDateOfLastDigest(Collection $events)
    {
        $now = $GLOBALS['SIM_EXEC_TIME'];

        /** @var \Tx_Seminars_Model_Event $event */
        foreach ($events as $event) {
            $event->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp($now);
            $this->eventMapper->save($event);
        }
    }
}
