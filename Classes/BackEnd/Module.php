<?php
namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Backend\Module\BaseScriptClass;

/**
 * This class is the base class for a back-end module.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Module extends BaseScriptClass
{
    /**
     * data of the current BE page
     *
     * @var string[]
     */
    private $pageData = [];

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
