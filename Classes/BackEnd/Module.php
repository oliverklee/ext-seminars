<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Backend\Module\BaseScriptClass;

/**
 * This class is the base class for a back-end module.
 *
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
