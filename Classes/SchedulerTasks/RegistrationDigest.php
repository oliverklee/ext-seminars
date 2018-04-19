<?php
namespace OliverKlee\Seminars\SchedulerTask;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
     * @var \Tx_Oelib_Configuration
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

        $this->configuration = \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars.registrationDigestEmail');
        $this->eventMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);

        $this->initialized = true;
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * This method is intended to be used for automated tests only.
     *
     * @return \Tx_Oelib_Configuration the initialized configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * This method is intended to be used for automated tests only.
     *
     * @param \Tx_Oelib_Configuration $configuration
     *
     * @return void
     */
    public function setConfiguration(\Tx_Oelib_Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * This method is intended to be used for automated tests only.
     *
     * @return \Tx_Seminars_Mapper_Event the initialized event mapper
     */
    public function getEventMapper()
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
     * @param \Tx_Oelib_List $events the \Tx_Oelib_List<\Tx_Seminars_Model_Event> that have new registrations
     *
     * @return MailMessage
     */
    private function buildEmail(\Tx_Oelib_List $events)
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
     * @param \Tx_Oelib_List $events \Tx_Oelib_List<\Tx_Seminars_Model_Event>
     *
     * @return string
     */
    private function createContent($templatePath, \Tx_Oelib_List $events)
    {
        $view = $this->objectManager->get(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templatePath));
        $view->assign('events', $events);

        return $view->render();
    }

    /**
     * @param \Tx_Oelib_List $events the \Tx_Oelib_List<\Tx_Seminars_Model_Event> that have new registrations
     *
     * @return void
     */
    private function updateDateOfLastDigest(\Tx_Oelib_List $events)
    {
        $now = $GLOBALS['SIM_EXEC_TIME'];

        /** @var \Tx_Seminars_Model_Event $event */
        foreach ($events as $event) {
            $event->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp($now);
            $this->eventMapper->save($event);
        }
    }
}
