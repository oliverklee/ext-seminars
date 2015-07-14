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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEnd_ModuleTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_Module
	 */
	private $fixture;

	protected function setUp() {
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

		$this->fixture = new tx_seminars_BackEnd_Module();
	}

	////////////////////////////////////////////////
	// Tests for getting and setting the page data
	////////////////////////////////////////////////

	public function testGetPageDataInitiallyReturnsEmptyArray() {
		self::assertEquals(
			array(),
			$this->fixture->getPageData()
		);
	}

	public function testGetPageDataReturnsCompleteDataSetViaSetPageData() {
		$this->fixture->setPageData(array('foo' => 'bar'));

		self::assertEquals(
			array('foo' => 'bar'),
			$this->fixture->getPageData()
		);
	}
}