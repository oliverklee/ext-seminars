<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait BackEndTestsTrait
{
    use LanguageHelper;

    /**
     * @var array<string, mixed>
     */
    private $getBackup = [];

    /**
     * @var array<string, mixed>
     */
    private $postBackup = [];

    /**
     * @var BackendUserAuthentication|null
     */
    private $backEndUserBackup;

    /**
     * @var string
     */
    private $languageBackup = '';

    /**
     * @var array<string, mixed>
     */
    private $extConfBackup = [];

    /**
     * @var array<string, mixed>
     */
    private $t3VarBackup = [];

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * @var HeaderCollector
     */
    private $headerProxy;

    /**
     * Replaces the current BE user with a mocked user, sets "default" as the current BE language, clears the
     * seminars extension settings, disables the automatic configuration check, sets the header proxy to test mode,
     * and sets a fixed `SIM_EXEC_TIME`.
     *
     * If you use this method, make sure to call `restoreOriginalEnvironment()` in `tearDown()`.
     */
    private function unifyTestingEnvironment(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        $this->replaceBackEndUserWithMock();
        $this->cleanRequestVariables();
        $this->replaceBackEndUserWithMock();
        $this->unifyBackEndLanguage();
        $this->unifyExtensionSettings();
        $this->setUpExtensionConfiguration();
        $headerProxyFactory = HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerProxy = $headerProxyFactory->getHeaderCollector();
    }

    private function cleanRequestVariables(): void
    {
        GeneralUtility::flushInternalRuntimeCaches();
        unset($GLOBALS['TYPO3_REQUEST']);
        $this->getBackup = $GLOBALS['_GET'];
        $GLOBALS['_GET'] = [];
        $this->postBackup = $GLOBALS['_POST'];
        $GLOBALS['_POST'] = [];
    }

    private function replaceBackEndUserWithMock(): void
    {
        $currentBackEndUser = $GLOBALS['BE_USER'];
        if ($currentBackEndUser instanceof BackendUserAuthentication) {
            $this->backEndUserBackup = $currentBackEndUser;
        }
        $mockBackEndUser = $this->createPartialMock(
            BackendUserAuthentication::class,
            ['check', 'doesUserHaveAccess', 'setAndSaveSessionData', 'writeUC']
        );
        $mockBackEndUser->method('check')->willReturn(true);
        $mockBackEndUser->method('doesUserHaveAccess')->willReturn(true);
        $mockBackEndUser->user['uid'] = 1;
        $GLOBALS['BE_USER'] = $mockBackEndUser;
    }

    private function unifyBackEndLanguage(): void
    {
        $currentLanguageService = $GLOBALS['LANG'];
        if ($currentLanguageService instanceof LanguageService) {
            $this->languageBackup = $GLOBALS['LANG']->lang;
        }

        $languageService = $this->getLanguageService();
        $languageService->lang = 'default';

        $languageService->includeLLFile('EXT:core/Resources/Private/Language/locallang_general.xlf');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
    }

    private function unifyExtensionSettings(): void
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = [];
    }

    private function setUpExtensionConfiguration(): void
    {
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);
    }

    private function restoreOriginalEnvironment(): void
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
        unset($GLOBALS['TYPO3_REQUEST']);
        GeneralUtility::flushInternalRuntimeCaches();
    }

    /**
     * Flushes all flash messages from the queue.
     */
    private function flushAllFlashMessages(): void
    {
        $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->getAllMessagesAndFlush();
    }

    /**
     * Convenience function for `$this->getLanguageService()->getLL()`
     *
     * @param non-empty-string $key
     */
    private function translate(string $key): string
    {
        return $this->getLanguageService()->getLL($key);
    }
}
