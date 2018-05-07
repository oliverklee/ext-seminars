<?php
namespace OliverKlee\Seminars\Tests\Unit\Support\Traits;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
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
     * @var \PHPUnit_Framework_MockObject_MockObject|BackendUserAuthentication
     */
    private $mockBackEndUser = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * @var \Tx_Oelib_HeaderCollector
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
        $this->cleanRequestVariables();
        $this->replaceBackEndUserWithMock();
        $this->unifyBackEndLanguage();
        $this->unifyExtensionSettings();
        \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);
        $this->setUpExtensionConfiguration();
        $headerProxyFactory = \Tx_Oelib_HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerProxy = $headerProxyFactory->getHeaderProxy();
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
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        $this->mockBackEndUser = $this->getMock(
            BackendUserAuthentication::class,
            ['check', 'doesUserHaveAccess', 'setAndSaveSessionData', 'writeUC'],
            [],
            '',
            false
        );
        $this->mockBackEndUser->expects(self::any())->method('check')->will(self::returnValue(true));
        $this->mockBackEndUser->expects(self::any())->method('doesUserHaveAccess')->will(self::returnValue(true));
        $this->mockBackEndUser->user['uid'] = (int)$GLOBALS['BE_USER']->user['uid'];
        $GLOBALS['BE_USER'] = $this->mockBackEndUser;
    }

    /**
     * @return void
     */
    private function unifyBackEndLanguage()
    {
        $this->languageBackup = $GLOBALS['LANG']->lang;

        $languageService = $this->getLanguageService();
        $languageService->lang = 'default';

        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/BackEnd/locallang.xlf');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        $languageService->includeLLFile('EXT:lang/locallang_general.xlf');
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
        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new \Tx_Oelib_Configuration());
        $this->configuration = new \Tx_Oelib_Configuration();
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
        /** @var $flashMessageService FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->getAllMessagesAndFlush();
    }

    /**
     * @return LanguageService
     */
    private function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
