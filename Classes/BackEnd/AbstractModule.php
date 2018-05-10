<?php
namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * This class is the base class for a back-end module.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class AbstractModule extends BaseScriptClass
{
    /**
     * @var string
     */
    const MODULE_NAME = 'web_seminars';

    /**
     * data of the current BE page
     *
     * @var string[]
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
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8007000) {
            $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_common.xlf');
            $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_show_rechis.xlf');
            $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf');
        } else {
            $this->getLanguageService()->includeLLFile('EXT:lang/locallang_common.xlf');
            $this->getLanguageService()->includeLLFile('EXT:lang/locallang_show_rechis.xlf');
            $this->getLanguageService()->includeLLFile('EXT:lang/locallang_mod_web_list.xlf');
        }
        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/BackEnd/locallang.xlf');
        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/Csv/locallang.xlf');

        $this->MCONF = ['name' => static::MODULE_NAME];
    }

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->id = (int)GeneralUtility::_GP('id');
        $this->perms_clause = $this->getBackendUser()->getPagePermsClause(1);
    }

    /**
     * Returns the data of the current BE page.
     *
     * @return string[] the data of the current BE page, may be emtpy
     */
    public function getPageData()
    {
        return $this->pageData;
    }

    /**
     * Sets the data for the current BE page.
     *
     * @param string[] $pageData page data, may be empty
     *
     * @return void
     */
    public function setPageData(array $pageData)
    {
        $this->pageData = $pageData;
    }
}
