<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_t3lib . 'class.t3lib_scbase.php');

/**
 * Module 'tx_seminars_BackEnd_Module' for the 'seminars' extension.
 *
 * This class is the base class for a back-end module.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEnd_Module extends t3lib_SCbase {
	/**
	 * data of the current BE page
	 *
	 * @var array
	 */
	private $pageData = array();

	/**
	 * Frees as much memory used by this object as possible.
	 */
	public function __destruct() {
		unset($this->doc, $this->extObj, $this->pageData);
	}

	/**
	 * Returns the data of the current BE page.
	 *
	 * @return array the data of the current BE page, may be emtpy
	 */
	public function getPageData() {
		return $this->pageData;
	}

	/**
	 * Sets the data for the current BE page.
	 *
	 * @param array page data, may be empty
	 */
	public function setPageData(array $pageData) {
		$this->pageData = $pageData;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_Module.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_Module.php']);
}
?>