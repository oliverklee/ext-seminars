<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is the base class for a back-end module.
 */
abstract class AbstractModule extends BaseScriptClass
{
    /**
     * @var string
     */
    protected const MODULE_NAME = 'web_seminars';

    /**
     * data of the current BE page
     *
     * @var array<string, string|int>
     */
    private $pageData = [];

    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate = null;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $languageService = $this->getLanguageService();
        $languageService->includeLLFile('EXT:lang/Resources/Private/Language/locallang_common.xlf');
        $languageService->includeLLFile('EXT:lang/Resources/Private/Language/locallang_show_rechis.xlf');
        $languageService->includeLLFile('EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf');
        $languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');

        $this->MCONF = ['name' => static::MODULE_NAME];
    }

    public function init(): void
    {
        parent::init();
        $this->id = (int)GeneralUtility::_GP('id');
        $this->perms_clause = $this->getBackendUser()->getPagePermsClause(1);
    }

    /**
     * Returns the data of the current BE page.
     *
     * @return string[] the data of the current BE page, may be empty
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
}
