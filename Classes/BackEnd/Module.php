<?php

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * This class is the base class for a back-end module.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_BackEnd_Module extends BaseScriptClass
{
    /**
     * data of the current BE page
     *
     * @var string[]
     */
    private $pageData = [];

    /**
     * Frees as much memory used by this object as possible.
     */
    public function __destruct()
    {
        unset($this->doc, $this->extObj, $this->pageData);
    }

    /**
     * Returns the logged-in back-end user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackEndUser()
    {
        return $GLOBALS['BE_USER'];
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
