<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;
use OliverKlee\Seminars\Mapper\BackEndUserMapper;
use OliverKlee\Seminars\Model\BackEndUser;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * This is the base class for lists in the back end.
 */
abstract class AbstractList
{
    /**
     * @var string
     */
    protected const MODULE_NAME = 'web_seminars';

    /**
     * @var int the depth of the recursion for the back-end lists
     */
    protected const RECURSION_DEPTH = 250;

    /**
     * @var int the page type of a sys-folder
     */
    public const SYSFOLDER_TYPE = 254;

    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = '';

    /**
     * @var AbstractModule
     */
    protected $page = null;

    /**
     * @var Template
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
     * The constructor. Sets the table name and the back-end page object.
     *
     * @param AbstractModule $module the current back-end module
     */
    public function __construct(AbstractModule $module)
    {
        $this->page = $module;

        $this->template = TemplateRegistry::get($this->templateFile);
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
     * @param int $recordUid the UID of the record, must be > 0
     * @param int $pageUid the PID of the record, must be >= 0
     *
     * @return string the HTML source code to return
     */
    public function getEditIcon(int $recordUid, int $pageUid): string
    {
        if (
            !$this->doesUserHaveAccess($pageUid)
            || !$this->getBackEndUser()->check('tables_modify', $this->tableName)
        ) {
            return '';
        }

        $langEdit = \htmlspecialchars($this->getLanguageService()->getLL('edit'), ENT_QUOTES | ENT_HTML5);
        $icon = '<img src="/' . PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('seminars')) .
            'Resources/Public/Icons/Edit.gif" alt="' . $langEdit . '" class="icon" />';

        $urlParameters = [
            'edit' => [$this->tableName => [$recordUid => 'edit']],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];
        $actionUrl = $this->getRouteUrl('record_edit', $urlParameters);

        return '<a class="btn btn-default" href="' . \htmlspecialchars($actionUrl, ENT_QUOTES | ENT_HTML5) . '">' .
            $icon . '</a>';
    }

    /**
     * Generates a linked "delete" record icon with a JavaScript confirmation window.
     *
     * @param int $recordUid the UID of the record, must be > 0
     * @param int $pageUid the PID of the record, must be >= 0
     *
     * @return string the HTML source code to return
     */
    public function getDeleteIcon(int $recordUid, int $pageUid): string
    {
        $result = '';

        $languageService = $this->getLanguageService();

        if ($this->doesUserHaveAccess($pageUid) && $this->getBackEndUser()->check('tables_modify', $this->tableName)) {
            $referenceWarning = BackendUtility::referenceCount(
                $this->tableName,
                (string)$recordUid,
                ' ' . $languageService->getLL('referencesWarning')
            );

            $confirmation = \htmlspecialchars(
                'if (confirm(' . GeneralUtility::quoteJSvalue(
                    $languageService->getLL('deleteWarning') . $referenceWarning
                ) . ')) {return true;} else {return false;}',
                ENT_QUOTES | ENT_HTML5
            );
            $urlParameters = [
                'cmd' => [$this->tableName => [$recordUid => ['delete' => 1]]],
                'redirect' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            ];
            $url = $this->getRouteUrl('tce_db', $urlParameters);
            $langDelete = $languageService->getLL('delete');
            $result = '<a class="btn btn-default" href="' .
                \htmlspecialchars($url, ENT_QUOTES | ENT_HTML5) .
                '" onclick="' . $confirmation . '">' .
                '<img src="/' . PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('seminars')) .
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
    public function getNewIcon(int $pid): string
    {
        $result = '';
        $languageService = $this->getLanguageService();

        $newRecordPid = $this->getNewRecordPid();
        $pid = ($newRecordPid > 0) ? $newRecordPid : $pid;
        $pageData = $this->page->getPageData();

        if (
            (int)$pageData['doktype'] === self::SYSFOLDER_TYPE
            && $this->doesUserHaveAccess($pid)
            && $this->getBackEndUser()->check('tables_modify', $this->tableName)
        ) {
            if ((int)$pageData['uid'] === $pid) {
                $storageLabel = sprintf(
                    $languageService->getLL('label_create_record_in_current_folder'),
                    $pageData['title'],
                    $pageData['uid']
                );
            } else {
                /** @var array $storagePageData */
                $storagePageData = BackendUtility::readPageAccess($pid, '');
                $storageLabel = \sprintf(
                    $languageService->getLL('label_create_record_in_foreign_folder'),
                    (string)$storagePageData['title'],
                    $pid
                );
            }
            $urlParameters = [
                'edit' => [$this->tableName => [$pid => 'new']],
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            ];
            $actionUrl = $this->getRouteUrl('record_edit', $urlParameters);

            $langNew = $languageService->getLL('newRecordGeneral');

            $result = '<div id="typo3-newRecordLink">' .
                '<a class="btn btn-default" href="' . \htmlspecialchars($actionUrl, ENT_QUOTES | ENT_HTML5) . '">' .
                '<img src="/' . PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('seminars')) .
                'Resources/Public/Icons/New.gif"' .
                // We use an empty alt attribute as we already have a textual
                // representation directly next to the icon.
                ' title="' . $langNew . '" alt="" />' .
                $langNew .
                '</a>' .
                '</div>';

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
        }

        return $result;
    }

    /**
     * Adds a flash message to the queue.
     */
    protected function addFlashMessage(FlashMessage $flashMessage): void
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
        $urlParameters = ['id' => (int)$pageData['uid'], 'csv' => '1', 'tx_seminars_pi2[table]' => $this->tableName];
        $csvUrl = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);

        return '<div id="typo3-csvLink">' .
            '<a class="btn btn-default" href="' . \htmlspecialchars($csvUrl, ENT_QUOTES | ENT_HTML5) .
            $this->getAdditionalCsvParameters() . '">' .
            '<img src="/' . PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('seminars')) .
            'Resources/Public/Icons/Csv.gif" title="' . $csvLabel . '" alt="" class="icon" />' .
            // We use an empty alt attribute as we already have a textual
            // representation directly next to the icon.
            $csvLabel .
            '</a>' .
            '</div>';
    }

    /**
     * Generates a linked hide or unhide icon depending on the record's hidden
     * status.
     *
     * @param int $recordUid the UID of the record, must be > 0
     * @param int $pageUid the PID of the record, must be >= 0
     * @param bool $hidden whether the record is hidden (true) or is visible (false)
     *
     * @return string the HTML source code of the linked hide or unhide icon
     */
    protected function getHideUnhideIcon(int $recordUid, int $pageUid, bool $hidden): string
    {
        $result = '';

        if ($this->doesUserHaveAccess($pageUid) && $this->getBackEndUser()->check('tables_modify', $this->tableName)) {
            if ($hidden) {
                $hidden = '0';
                $icon = 'Unhide.gif';
                $langHide = $this->getLanguageService()->getLL('unHide');
            } else {
                $hidden = '1';
                $icon = 'Hide.gif';
                $langHide = $this->getLanguageService()->getLL('hide');
            }

            $urlParameters = [
                'data' => [$this->tableName => [$recordUid => ['hidden' => $hidden]]],
                'redirect' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            ];
            $url = $this->getRouteUrl('tce_db', $urlParameters);
            $result = '<a class="btn btn-default" href="' .
                \htmlspecialchars($url, ENT_QUOTES | ENT_HTML5) . '">' .
                '<img src="/' . PathUtility::stripPathSitePrefix(
                    ExtensionManagementUtility::extPath('seminars')
                ) . 'Resources/Public/Icons/' .
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
    protected function doesUserHaveAccess(int $pageUid): bool
    {
        if (!isset($this->accessRights[$pageUid])) {
            $this->accessRights[$pageUid] = $this->getBackEndUser()
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
    abstract protected function getNewRecordPid(): int;

    /**
     * Gets the currently logged in back-end user.
     *
     * @return BackEndUser the currently logged in back-end user
     */
    protected function getLoggedInUser(): BackEndUser
    {
        /** @var BackEndUser $user */
        $user = BackEndLoginManager::getInstance()->getLoggedInUser(BackEndUserMapper::class);

        return $user;
    }

    /**
     * Returns the parameters to add to the CSV icon link.
     *
     * @return string the additional link parameters for the CSV icon link, will
     *                always start with an &amp and be htmlspecialchared, may
     *                be empty
     */
    protected function getAdditionalCsvParameters(): string
    {
        $pageData = $this->page->getPageData();

        return '&amp;tx_seminars_pi2[pid]=' . $pageData['uid'];
    }

    /**
     * Returns the URL to a given module.
     *
     * @param string $moduleName name of the module
     * @param array $urlParameters URL parameters that should be added as key-value pairs
     *
     * @return string calculated URL
     */
    protected function getRouteUrl(string $moduleName, array $urlParameters = []): string
    {
        $uriBuilder = $this->getUriBuilder();
        try {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
        } catch (RouteNotFoundException $e) {
            // no route registered, use the fallback logic to check for a module
            // @phpstan-ignore-next-line This line is for TYPO3 9LTS only, and we check with 10LTS.
            $uri = $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
        }

        return (string)$uri;
    }

    protected function getUriBuilder(): UriBuilder
    {
        return GeneralUtility::makeInstance(UriBuilder::class);
    }
}
