<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is the base class for a back-end module.
 */
abstract class AbstractModule
{
    /**
     * @var string
     */
    protected const MODULE_NAME = 'web_seminars';

    /**
     * The integer value of the GET/POST var, 'id'. Used for submodules to the 'Web' module (page id)
     *
     * @var int
     */
    public $id = 0;

    /**
     * The value of GET/POST var, `CMD`
     *
     * @var string
     */
    protected $CMD = '';

    /**
     * A WHERE clause for selection records from the pages table based on read-permissions of the current backend user.
     *
     * @var string
     */
    protected $perms_clause = '';

    /**
     * @var DocumentTemplate
     */
    public $doc;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * data of the current BE page
     *
     * @var array<string, string|int>
     */
    private $pageData = [];

    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * available sub modules
     *
     * @var array<int, string>
     */
    protected $availableSubModules = [];

    /**
     * the ID of the currently selected submodule
     *
     * @var int
     */
    protected $subModule = 0;

    public function __construct()
    {
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $languageService = $this->getLanguageService();
        $languageService->includeLLFile('EXT:backend/Resources/Private/Language/locallang_show_rechis.xlf');
        $languageService->includeLLFile('EXT:core/Resources/Private/Language/locallang_common.xlf');
        $languageService->includeLLFile('EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
    }

    protected function init(): void
    {
        $this->id = (int)GeneralUtility::_GP('id');
        $this->CMD = (string)GeneralUtility::_GP('CMD');
        $this->perms_clause = $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW);

        $this->id = (int)GeneralUtility::_GP('id');
        $this->perms_clause = $this->getBackendUser()->getPagePermsClause(1);
    }

    /**
     * Returns the data of the current BE page.
     *
     * @return array<string, string|int> the data of the current BE page, may be empty
     */
    public function getPageData(): array
    {
        return $this->pageData;
    }

    /**
     * Sets the data for the current BE page.
     *
     * @param array<string, string|int> $pageData page data, may be empty
     */
    public function setPageData(array $pageData): void
    {
        $this->pageData = $pageData;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getPageRenderer(): PageRenderer
    {
        if (!$this->pageRenderer instanceof PageRenderer) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }

        return $this->pageRenderer;
    }
}
