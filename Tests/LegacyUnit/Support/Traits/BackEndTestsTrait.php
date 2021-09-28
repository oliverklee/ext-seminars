<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

trait BackEndTestsTrait
{
    /**
     * @var array
     */
    private $getBackup = [];

    /**
     * @var array
     */
    private $postBackup = [];

    /**
     * @var BackendUserAuthentication
     */
    private $backEndUserBackup = null;

    /**
     * @var string
     */
    private $languageBackup = '';

    /**
     * @var array
     */
    private $extConfBackup = [];

    /**
     * @var array
     */
    private $t3VarBackup = [];

    /**
     * @var DummyConfiguration
     */
    private $configuration = null;

    /**
     * @var HeaderCollector
     */
    private $headerProxy = null;

    /**
     * Replaces the current BE user with a mocked user, sets "default" as the current BE language, clears the
     * seminars extension settings, disables the automatic configuration check, sets the header proxy to test mode,
     * and sets a fixed SIM_EXEC_TIME.
     *
     * If you use this method, make sure to call restoreOriginalEnvironment() in tearDown().
     *
     * @return void
     */
    private function unifyTestingEnvironment()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        Bootstrap::initializeBackendAuthentication();
        $this->cleanRequestVariables();
        $this->replaceBackEndUserWithMock();
        $this->unifyBackEndLanguage();
        $this->unifyExtensionSettings();
        $this->setUpExtensionConfiguration();
        $headerProxyFactory = HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerProxy = $headerProxyFactory->getHeaderCollector();
    }

    /**
     * @return void
     */
    private function cleanRequestVariables()
    {
        $this->getBackup = $GLOBALS['_GET'];
        $GLOBALS['_GET'] = [];
        $this->postBackup = $GLOBALS['_POST'];
        $GLOBALS['_POST'] = [];
    }

    /**
     * @return void
     */
    private function replaceBackEndUserWithMock()
    {
        /** @var BackendUserAuthentication $currentBackEndUser */
        $currentBackEndUser = $GLOBALS['BE_USER'];
        $this->backEndUserBackup = $currentBackEndUser;
        /** @var BackendUserAuthentication&MockObject $mockBackEndUser */
        $mockBackEndUser = $this->createPartialMock(
            BackendUserAuthentication::class,
            ['check', 'doesUserHaveAccess', 'setAndSaveSessionData', 'writeUC']
        );
        $mockBackEndUser->method('check')->willReturn(true);
        $mockBackEndUser->method('doesUserHaveAccess')->willReturn(true);
        $mockBackEndUser->user['uid'] = (int)$currentBackEndUser->user['uid'];
        $GLOBALS['BE_USER'] = $mockBackEndUser;
    }

    /**
     * @return void
     */
    private function unifyBackEndLanguage()
    {
        $this->languageBackup = $GLOBALS['LANG']->lang;

        $languageService = $this->getLanguageService();
        $languageService->lang = 'default';

        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        $languageService->includeLLFile('EXT:lang/Resources/Private/Language/locallang_general.xlf');
    }

    /**
     * @return void
     */
    private function unifyExtensionSettings()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = [];
    }

    /**
     * @return void
     */
    private function setUpExtensionConfiguration()
    {
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);
    }

    /**
     * @return void
     */
    private function restoreOriginalEnvironment()
    {
        $this->flushAllFlashMessages();
        if ($this->backEndUserBackup !== null) {
            $GLOBALS['BE_USER'] = $this->backEndUserBackup;
        }
        if ($this->languageBackup !== '') {
            $this->getLanguageService()->lang = $this->languageBackup;
        }
        if ($this->extConfBackup !== []) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
        }
        if ($this->t3VarBackup !== []) {
            $GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
        }
        if ($this->getBackup !== [] || $this->postBackup !== []) {
            $GLOBALS['_GET'] = $this->getBackup;
            $GLOBALS['_POST'] = $this->postBackup;
        }
    }

    /**
     * Flushes all flash messages from the queue.
     *
     * @return void
     */
    private function flushAllFlashMessages()
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->getAllMessagesAndFlush();
    }

    /**
     * @return LanguageService
     */
    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
