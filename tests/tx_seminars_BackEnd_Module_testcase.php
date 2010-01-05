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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
/**
 * Testcase for the tx_seminars_BackEnd_Module class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEnd_Module_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_Module
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_BackEnd_Module();
	}

	public function tearDown() {
		$this->fixture->__destruct();

		unset($this->fixture);
	}


	////////////////////////////////////////////////
	// Tests for getting and setting the page data
	////////////////////////////////////////////////

	public function testGetPageDataInitiallyReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getPageData()
		);
	}

	public function testGetPageDataReturnsCompleteDataSetViaSetPageData() {
		$this->fixture->setPageData(array('foo' => 'bar'));

		$this->assertEquals(
			array('foo' => 'bar'),
			$this->fixture->getPageData()
		);
	}
}
?>