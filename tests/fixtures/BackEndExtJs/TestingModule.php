<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2012 Niels Pardon (mail@niels-pardon.de)
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

/**
 * Testing version of the module 'Events' for the 'seminars' extension
 * (the ExtJS version).
 *
 * Fixture for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_tests_fixtures_BackEndExtJs_TestingModule extends tx_seminars_BackEndExtJs_Module {
	/**
	 * @var t3lib_PageRenderer
	 */
	private $pageRenderer = NULL;

	public function init() {
		parent::init();

		$this->pageRenderer = $this->doc->getPageRenderer();
	}

	/**
	 * Returns always TRUE.
	 *
	 * @return boolean always TRUE
	 */
	protected function hasStaticTemplate() {
		return TRUE;
	}

	/**
	 * Returns always TRUE.
	 *
	 * @return boolean always TRUE
	 */
	protected function isPageIdValid() {
		return TRUE;
	}

	/**
	 * Returns always TRUE.
	 *
	 * @return boolean always TRUE
	 */
	protected function isAdminOrHasPageShowAccess() {
		return TRUE;
	}

	/**
	 * Sets the page renderer for testing purposes.
	 *
	 * @param t3lib_PageRenderer $pageRenderer
	 *        the page renderer for testing purposes
	 */
	public function setPageRenderer(t3lib_PageRenderer $pageRenderer) {
		$this->pageRenderer = $pageRenderer;
	}

	/**
	 * Returns the page renderer for this back-end module.
	 *
	 * @return t3lib_PageRenderer the page renderer for this back-end module
	 */
	protected function getPageRenderer() {
		return $this->pageRenderer;
	}
}
?>