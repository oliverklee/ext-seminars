<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;
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
    protected $page;

    /**
     * @var Template
     */
    protected $template;

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
     * Adds a flash message to the queue.
     */
    protected function addFlashMessage(FlashMessage $flashMessage): void
    {
        $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
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
