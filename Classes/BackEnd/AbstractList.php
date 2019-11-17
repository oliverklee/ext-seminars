<?php
declare(strict_types = 1);
namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * This is the base class for lists in the back end.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
abstract class AbstractList
{
    /**
     * @var string
     */
    const MODULE_NAME = 'web_seminars';

    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = '';

    /**
     * @var AbstractModule
     */
    protected $page = null;

    /**
     * @var \Tx_Oelib_Template the template object
     */
    protected $template = null;

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = '';

    /**
     * @var bool[] the access rights to page UIDs
     */
    protected $accessRights = [];

    /**
     * @var int the depth of the recursion for the back-end lists
     */
    const RECURSION_DEPTH = 250;

    /**
     * @var int the page type of a sys-folder
     */
    const SYSFOLDER_TYPE = 254;

    /**
     * The constructor. Sets the table name and the back-end page object.
     *
     * @param AbstractModule $module the current back-end module
     */
    public function __construct(AbstractModule $module)
    {
        $this->page = $module;

        $this->template = \Tx_Oelib_TemplateRegistry::get($this->templateFile);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns the logged-in back-end user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackEndUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Generates an edit record icon which is linked to the edit view of
     * a record.
     *
     * @param int $uid the UID of the record, must be > 0
     * @param int $pageUid the PID of the record, must be >= 0
     *
     * @return string the HTML source code to return
     */
    public function getEditIcon($uid, $pageUid): string
    {
        if (!$this->doesUserHaveAccess($pageUid) || !$GLOBALS['BE_USER']->check('tables_modify', $this->tableName)) {
            return '';
        }

        $params = '&edit[' . $this->tableName . '][' . $uid . ']=edit';
        $langEdit = \htmlspecialchars($this->getLanguageService()->getLL('edit'), ENT_QUOTES | ENT_HTML5);
        $icon = '<img src="/' . ExtensionManagementUtility::siteRelPath('seminars') .
            'Resources/Public/Icons/Edit.gif" alt="' . $langEdit . '" class="icon" />';

        $editOnClick = BackendUtility::editOnClick($params);
        $result = '<a class="btn btn-default" href="#" onclick="' .
            \htmlspecialchars($editOnClick, ENT_QUOTES | ENT_HTML5)
            . '" title="' . $langEdit . '">' . $icon . '</a>';

        return $result;
    }

    /**
     * Generates a linked "delete" record icon with a JavaScript confirmation window.
     *
     * @param int $uid the UID of the record, must be > 0
     * @param int $pageUid the PID of the record, must be >= 0
     *
     * @return string the HTML source code to return
     */
    public function getDeleteIcon($uid, $pageUid): string
    {
        $result = '';

        $languageService = $this->getLanguageService();

        if ($this->doesUserHaveAccess($pageUid) && $this->getBackEndUser()->check('tables_modify', $this->tableName)) {
            $params = '&cmd[' . $this->tableName . '][' . $uid . '][delete]=1';

            $referenceWarning = BackendUtility::referenceCount(
                $this->tableName,
                $uid,
                ' ' . $languageService->getLL('referencesWarning')
            );

            $confirmation = \htmlspecialchars(
                'if (confirm(' . GeneralUtility::quoteJSvalue(
                    $languageService->getLL('deleteWarning') . $referenceWarning
                ) . ')) {return true;} else {return false;}',
                ENT_QUOTES | ENT_HTML5
            );
            $langDelete = $languageService->getLL('delete');
            $result = '<a class="btn btn-default" href="' .
                \htmlspecialchars(BackendUtility::getLinkToDataHandlerAction($params), ENT_QUOTES | ENT_HTML5) .
                '" onclick="' . $confirmation . '">' .
                '<img src="/' . ExtensionManagementUtility::siteRelPath('seminars') .
                'Resources/Public/Icons/Garbage.gif" title="' . \htmlspecialchars($langDelete, ENT_QUOTES | ENT_HTML5) .
                '" alt="' . \htmlspecialchars($langDelete, ENT_QUOTES | ENT_HTML5) . '" class="deleteicon" />' .
                '</a>';
        }

        return $result;
    }

    /**
     * Returns a "create new record" image tag that is linked to the new record view.
     *
     * @param int $pid the page ID where the record should be stored, must be > 0
     *
     * @return string the HTML source code to return
     */
    public function getNewIcon($pid): string
    {
        $result = '';
        $languageService = $this->getLanguageService();

        $newRecordPid = $this->getNewRecordPid();
        $pid = ($newRecordPid > 0) ? $newRecordPid : $pid;
        $pageData = $this->page->getPageData();

        if ((int)$pageData['doktype'] === self::SYSFOLDER_TYPE
            && $this->doesUserHaveAccess($pid)
            && $this->getBackEndUser()->check('tables_modify', $this->tableName)
        ) {
            $params = '&edit[' . $this->tableName . '][';

            if ((int)$pageData['uid'] === $pid) {
                $params .= $pageData['uid'];
                $storageLabel = sprintf(
                    $languageService->getLL('label_create_record_in_current_folder'),
                    $pageData['title'],
                    $pageData['uid']
                );
            } else {
                /** @var array $storagePageData */
                $storagePageData = BackendUtility::readPageAccess($pid, '');
                $params .= $pid;
                $storageLabel = \sprintf(
                    $languageService->getLL('label_create_record_in_foreign_folder'),
                    (string)$storagePageData['title'],
                    $pid
                );
            }
            $params .= ']=new';
            $editOnClick = BackendUtility::editOnClick($params);

            $langNew = $languageService->getLL('newRecordGeneral');

            $result = TAB . TAB .
                '<div id="typo3-newRecordLink">' . LF .
                TAB . TAB . TAB .
                '<a class="btn btn-default" href="#"  onclick="' .
                \htmlspecialchars($editOnClick, ENT_QUOTES | ENT_HTML5) . '">' . LF .
                TAB . TAB . TAB . TAB .
                '<img src="/' . ExtensionManagementUtility::siteRelPath('seminars') .
                'Resources/Public/Icons/New.gif"' .
                // We use an empty alt attribute as we already have a textual
                // representation directly next to the icon.
                ' title="' . $langNew . '" alt="" />' . LF .
                TAB . TAB . TAB . TAB .
                $langNew . LF .
                TAB . TAB . TAB .
                '</a>' . LF .
                TAB . TAB .
                '</div>' . LF;

            /** @var FlashMessage $message */
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $storageLabel,
                '',
                FlashMessage::INFO
            );
            $this->addFlashMessage($message);

            /** @var FlashMessageService $flashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $renderedFlashMessages = $flashMessageService->getMessageQueueByIdentifier()->renderFlashMessages();

            $result .= '<div id="eventsList-clear"></div>' . $renderedFlashMessages;

            if ($this->getBackEndUser()->isAdmin()) {
                $result .= $this->createCrowdfundingMessage();
            }
        }

        return $result;
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
     * Returns a "CSV export" image tag that is linked to the CSV export,
     * corresponding to the list that is visible in the BE.
     *
     * This icon is intended to be used next to the "create new record" icon.
     *
     * @return string the HTML source code of the linked CSV icon
     */
    protected function getCsvIcon(): string
    {
        $pageData = $this->page->getPageData();
        $csvLabel = $this->getLanguageService()->getLL('csvExport');
        $csvUrl = BackendUtility::getModuleUrl(
            self::MODULE_NAME,
            ['id' => $pageData['uid'], 'csv' => '1', 'tx_seminars_pi2[table]' => $this->tableName]
        );

        $result = TAB . TAB .
            '<div id="typo3-csvLink">' . LF .
            TAB . TAB . TAB .
            '<a class="btn btn-default" href="' . \htmlspecialchars($csvUrl, ENT_QUOTES | ENT_HTML5) .
            $this->getAdditionalCsvParameters() . '">' . LF .
            TAB . TAB . TAB . TAB .
            '<img src="/' . ExtensionManagementUtility::siteRelPath('seminars') .
            'Resources/Public/Icons/Csv.gif" title="' . $csvLabel . '" alt="" class="icon" />' .
            // We use an empty alt attribute as we already have a textual
            // representation directly next to the icon.
            TAB . TAB . TAB . TAB .
            $csvLabel . LF .
            TAB . TAB . TAB .
            '</a>' . LF .
            TAB . TAB .
            '</div>' . LF;

        return $result;
    }

    /**
     * Generates a linked hide or unhide icon depending on the record's hidden
     * status.
     *
     * @param int $uid the UID of the record, must be > 0
     * @param int $pageUid the PID of the record, must be >= 0
     * @param bool $hidden
     *        indicates whether the record is hidden (TRUE) or is visible (FALSE)
     *
     * @return string the HTML source code of the linked hide or unhide icon
     */
    protected function getHideUnhideIcon($uid, $pageUid, $hidden): string
    {
        $result = '';

        if ($GLOBALS['BE_USER']->check('tables_modify', $this->tableName)
            && $this->doesUserHaveAccess($pageUid)
        ) {
            if ($hidden) {
                $params = '&data[' . $this->tableName . '][' . $uid . '][hidden]=0';
                $icon = 'Unhide.gif';
                $langHide = $this->getLanguageService()->getLL('unHide');
            } else {
                $params = '&data[' . $this->tableName . '][' . $uid . '][hidden]=1';
                $icon = 'Hide.gif';
                $langHide = $this->getLanguageService()->getLL('hide');
            }

            $result = '<a class="btn btn-default" href="' .
                \htmlspecialchars(BackendUtility::getLinkToDataHandlerAction($params), ENT_QUOTES | ENT_HTML5) . '">' .
                '<img src="/' . ExtensionManagementUtility::siteRelPath('seminars') . 'Resources/Public/Icons/' .
                $icon . '" title="' . $langHide . '" alt="' . $langHide . '" class="hideicon" />' .
                '</a>';
        }

        return $result;
    }

    /**
     * Checks if the currently logged-in BE user has access to records on the
     * given page.
     *
     * @param int $pageUid the page to check the access for, must be >= 0
     *
     * @return bool TRUE if the user has access, FALSE otherwise
     */
    protected function doesUserHaveAccess($pageUid): bool
    {
        if (!isset($this->accessRights[$pageUid])) {
            $this->accessRights[$pageUid] = $GLOBALS['BE_USER']
                ->doesUserHaveAccess(BackendUtility::getRecord('pages', $pageUid), 16);
        }

        return $this->accessRights[$pageUid];
    }

    /**
     * Returns the PID for new records to store.
     *
     * This will be determined by the storage setting of the logged-in BE-user's
     * groups.
     *
     * @return int the PID for the storage of new records, will be >= 0
     */
    abstract protected function getNewRecordPid();

    /**
     * Gets the currently logged in back-end user.
     *
     * @return \Tx_Seminars_Model_BackEndUser the currently logged in back-end user
     */
    protected function getLoggedInUser(): \Tx_Seminars_Model_BackEndUser
    {
        return \Tx_Oelib_BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
    }

    /**
     * Returns the parameters to add to the CSV icon link.
     *
     * @return string the additional link parameters for the CSV icon link, will
     *                always start with an &amp and be htmlspecialchared, may
     *                be empty
     */
    protected function getAdditionalCsvParameters()
    {
        $pageData = $this->page->getPageData();

        return '&amp;tx_seminars_pi2[pid]=' . $pageData['uid'];
    }

    /**
     * @return string
     */
    private function createCrowdfundingMessage(): string
    {
        return '<div class="typo3-messages">
            <div class="alert alert-notice">
                <div class="media">
                    <div class="media-left">
                        <span class="fa-stack fa-lg">
                            <i class="fa fa-circle fa-stack-2x"></i>
                            <i class="fa fa-info fa-stack-1x"></i>
                        </span>
                    </div>
                    <div class="media-body">
                        <p class="alert-message">
                            ' . $this->getLanguageService()->getLL('message.crowdfundingCampaign') . '
                            <br/>
                            <a href="https://coders.care/for/crowdfunding/seminars/" target="_blank">
                                https://coders.care/for/crowdfunding/seminars/
                            </a>
                        </p>
                    </div>
                </div>
            </div>';
    }
}
