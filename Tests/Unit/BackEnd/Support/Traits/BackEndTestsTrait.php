<?php
namespace OliverKlee\Seminars\Tests\Unit\BackeEnd\Support\Traits;

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
     * Replaces the current BE user with a mocked user, sets "default" as the current BE language, clears the
     * seminars extension settings, disables the automatic configuration check, and sets a fixed SIM_EXEC_TIME.
     *
     * If you use this method, make sure to call restoreOriginalEnvironment() in tearDown().
     *
     * @return void
     */
    private function unifyTestingEnvironment()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        $this->replaceBackEndUserWithMock();
        $this->unifyBackEndLanguage();
        $this->unifyExtensionSettings();
        \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);
    }

    /**
     * @return void
     */
    private function replaceBackEndUserWithMock()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        $this->mockBackEndUser = $this->getMock(
            BackendUserAuthentication::class,
            ['check', 'doesUserHaveAccess', 'setAndSaveSessionData'],
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
        $this->getLanguageService()->lang = 'default';

        // Loads the locallang file for properly working localization in the tests.
        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/BackEnd/locallang.xlf');
    }

    private function unifyExtensionSettings()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = [];
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
