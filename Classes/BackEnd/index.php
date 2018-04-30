<?php

use OliverKlee\Seminars\BackEnd\CancelEventMailForm;
use OliverKlee\Seminars\BackEnd\ConfirmEventMailForm;
use OliverKlee\Seminars\BackEnd\EventsList;
use OliverKlee\Seminars\BackEnd\GeneralEventMailForm;
use OliverKlee\Seminars\BackEnd\Module;
use OliverKlee\Seminars\BackEnd\OrganizersList;
use OliverKlee\Seminars\BackEnd\RegistrationsList;
use OliverKlee\Seminars\BackEnd\SpeakersList;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Back-end module "Events".
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Module2 extends Module
{
    /**
     * @var string
     */
    const MODULE_NAME = 'web_txseminarsM2';

    /**
     * available sub modules
     *
     * @var string[]
     */
    protected $availableSubModules = [];

    /**
     * the ID of the currently selected sub module
     *
     * @var int
     */
    protected $subModule = 0;

    /**
     * Initializes some variables and also starts the initialization of the parent class.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->id = (int)$this->id;
    }

    /**
     * Main function of the module. Writes the content to $this->content.
     *
     * No return value; output is directly written to the page.
     *
     * @return void
     */
    public function main()
    {
        $languageService = $this->getLanguageService();
        $backEndUser = $this->getBackendUser();

        $this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);

        $pageRenderer = $this->getPageRenderer();
        $pageRenderer->addCssFile(
            '../typo3conf/ext/seminars/Resources/Public/CSS/BackEnd/BackEnd.css',
            'stylesheet',
            'all',
            '',
            false
        );
        $pageRenderer->addCssFile(
            '../typo3conf/ext/seminars/Resources/Public/CSS/BackEnd/Print.css',
            'stylesheet',
            'print',
            '',
            false
        );

        // draw the header
        $this->content = $this->doc->startPage($languageService->getLL('title'));
        $this->content .= $this->doc->header($languageService->getLL('title'));

        if ($this->id <= 0) {
            /** @var FlashMessage $message */
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->getLL('message_noPageTypeSelected'),
                '',
                FlashMessage::INFO
            );
            $this->addFlashMessage($message);

            echo $this->content . $this->getRenderedFlashMessages() . $this->doc->endPage();
            return;
        }

        $pageAccess = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        if (!is_array($pageAccess) && !$backEndUser->user['admin']) {
            echo $this->content . $this->getRenderedFlashMessages() . $this->doc->endPage();
            return;
        }

        if (!$this->hasStaticTemplate()) {
            /** @var FlashMessage $message */
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->getLL('message_noStaticTemplateFound'),
                '',
                FlashMessage::WARNING
            );
            $this->addFlashMessage($message);

            echo $this->content . $this->getRenderedFlashMessages() . $this->doc->endPage();
            return;
        }

        $this->setPageData($pageAccess);

        // define the sub modules that should be available in the tab menu
        $this->availableSubModules = [];

        // only show the tabs if the back-end user has access to the
        // corresponding tables
        if ($backEndUser->check('tables_select', 'tx_seminars_seminars')) {
            $this->availableSubModules[1] = $languageService->getLL('subModuleTitle_events');
        }

        if ($backEndUser->check('tables_select', 'tx_seminars_attendances')) {
            $this->availableSubModules[2] = $languageService->getLL('subModuleTitle_registrations');
        }

        if ($backEndUser->check('tables_select', 'tx_seminars_speakers')) {
            $this->availableSubModules[3] = $languageService->getLL('subModuleTitle_speakers');
        }

        if ($backEndUser->check('tables_select', 'tx_seminars_organizers')) {
            $this->availableSubModules[4] = $languageService->getLL('subModuleTitle_organizers');
        }

        // Read the selected sub module (from the tab menu) and make it available within this class.
        $this->subModule = (int)GeneralUtility::_GET('subModule');

        // If $this->subModule is not a key of $this->availableSubModules,
        // set it to the key of the first element in $this->availableSubModules
        // so the first tab is activated.
        if (!array_key_exists($this->subModule, $this->availableSubModules)) {
            reset($this->availableSubModules);
            $this->subModule = key($this->availableSubModules);
        }

        // Only generate the tab menu if the current back-end user has the
        // rights to show any of the tabs.
        if ($this->subModule) {
            $moduleToken = FormProtectionFactory::get()->generateToken('moduleCall', self::MODULE_NAME);
            $this->content .= $this->doc->getTabMenu(
                ['M' => self::MODULE_NAME, 'moduleToken' => $moduleToken, 'id' => $this->id],
                'subModule',
                $this->subModule,
                $this->availableSubModules
            );
        }

        // Select which sub module to display.
        // If no sub module is specified, an empty page will be displayed.
        switch ($this->subModule) {
            case 2:
                /** @var RegistrationsList $registrationsList */
                $registrationsList = GeneralUtility::makeInstance(RegistrationsList::class, $this);
                $this->content .= $registrationsList->show();
                break;
            case 3:
                /** @var SpeakersList $speakersList */
                $speakersList = GeneralUtility::makeInstance(SpeakersList::class, $this);
                $this->content .= $speakersList->show();
                break;
            case 4:
                /** @var OrganizersList $organizersList */
                $organizersList = GeneralUtility::makeInstance(OrganizersList::class, $this);
                $this->content .= $organizersList->show();
                break;
            case 1:
                if ($this->isGeneralEmailFormRequested()) {
                    $this->content .= $this->getGeneralMailForm();
                } elseif ($this->isConfirmEventFormRequested()) {
                    $this->content .= $this->getConfirmEventMailForm();
                } elseif ($this->isCancelEventFormRequested()) {
                    $this->content .= $this->getCancelEventMailForm();
                } else {
                    /** @var EventsList $eventsList */
                    $eventsList = GeneralUtility::makeInstance(EventsList::class, $this);
                    $this->content .= $eventsList->show();
                }
                break;
            default:
        }

        echo $this->content . $this->doc->endPage();
    }

    /**
     * Adds a flash message to the queue.
     *
     * @param FlashMessage $flashMessage
     *
     * @return void
     */
    protected function addFlashMessage(FlashMessage $flashMessage)
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Returns the rendered flash messages.
     *
     * @return string
     */
    protected function getRenderedFlashMessages()
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        return $defaultFlashMessageQueue->renderFlashMessages();
    }

    /**
     * Checks whether the user requested the form for sending an e-mail and
     * whether all pre-conditions for showing the form are met.
     *
     * @return bool TRUE if the form was requested and pre-conditions are met, FALSE otherwise
     */
    private function isGeneralEmailFormRequested()
    {
        if ((int)GeneralUtility::_POST('eventUid') <= 0) {
            return false;
        }

        return GeneralUtility::_POST('action') === 'sendEmail';
    }

    /**
     * Checks whether the user requested the form for confirming an event and
     * whether all pre-conditions for showing the form are met.
     *
     * @return bool TRUE if the form was requested and pre-conditions are met, FALSE otherwise
     */
    private function isConfirmEventFormRequested()
    {
        if ((int)GeneralUtility::_POST('eventUid') <= 0) {
            return false;
        }

        return GeneralUtility::_POST('action') === 'confirmEvent';
    }

    /**
     * Checks whether the user requested the form for canceling an event and
     * whether all pre-conditions for showing the form are met.
     *
     * @return bool TRUE if the form was requested and pre-conditions are
     *                 met, FALSE otherwise
     */
    private function isCancelEventFormRequested()
    {
        if ((int)GeneralUtility::_POST('eventUid') <= 0) {
            return false;
        }

        return GeneralUtility::_POST('action') === 'cancelEvent';
    }

    /**
     * Returns the form to send an e-mail.
     *
     * @return string the HTML source for the form
     */
    private function getGeneralMailForm()
    {
        /** @var GeneralEventMailForm $form */
        $form = GeneralUtility::makeInstance(
            GeneralEventMailForm::class,
            (int)GeneralUtility::_GP('eventUid')
        );
        $form->setPostData(GeneralUtility::_POST());

        return $form->render();
    }

    /**
     * Returns the form to confirm an event.
     *
     * @return string the HTML source for the form
     */
    private function getConfirmEventMailForm()
    {
        /** @var ConfirmEventMailForm $form */
        $form = GeneralUtility::makeInstance(
            ConfirmEventMailForm::class,
            (int)GeneralUtility::_GP('eventUid')
        );
        $form->setPostData(GeneralUtility::_POST());

        return $form->render();
    }

    /**
     * Returns the form to canceling an event.
     *
     * @return string the HTML source for the form
     */
    private function getCancelEventMailForm()
    {
        /** @var CancelEventMailForm $form */
        $form = GeneralUtility::makeInstance(
            CancelEventMailForm::class,
            (int)GeneralUtility::_GP('eventUid')
        );
        $form->setPostData(GeneralUtility::_POST());

        return $form->render();
    }

    /**
     * Checks whether this extension's static template is included on the
     * current page.
     *
     * @return bool TRUE if the static template has been included, FALSE otherwise
     */
    private function hasStaticTemplate()
    {
        return \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->getAsBoolean('isStaticTemplateLoaded');
    }
}

// This checks permissions and exits if the users has no permission for entry.
$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], true);

if (GeneralUtility::_GET('csv') !== '1') {
    /** @var \TYPO3\CMS\Lang\LanguageService $languageService */
    $languageService = $GLOBALS['LANG'];

    $languageService->includeLLFile('EXT:lang/locallang_common.xlf');
    $languageService->includeLLFile('EXT:lang/locallang_show_rechis.xlf');
    $languageService->includeLLFile('EXT:lang/locallang_mod_web_list.xlf');
    $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/BackEnd/locallang.xlf');
    $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/Csv/locallang.xlf');

    /** @var \Tx_Seminars_Module2 $SOBE */
    $SOBE = GeneralUtility::makeInstance(\Tx_Seminars_Module2::class);
    $SOBE->init();

    $SOBE->main();
} else {
    /** @var \Tx_Seminars_Csv_CsvDownloader $csvExporter */
    $csvExporter = GeneralUtility::makeInstance(\Tx_Seminars_Csv_CsvDownloader::class);
    echo $csvExporter->main();
}
