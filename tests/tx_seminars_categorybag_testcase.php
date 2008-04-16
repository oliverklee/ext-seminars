<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the categorybag class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_categorybag.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_categorybag_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** the UID of the first event record in the DB */
	private $uidOfFirstCategory = 0;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->uidOfFirstCategory = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'test category 1')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'test category 2')
		);

		$this->fixture = new tx_seminars_categorybag();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic bag functionality.
	///////////////////////////////////////////

	public function testBagCanHaveAtLeastOneElement() {
		$this->assertGreaterThan(
			0, $this->fixture->getObjectCountWithoutLimit()
		);

		$this->assertNotNull(
			$this->fixture->getCurrent()
		);
		$this->assertTrue(
			$this->fixture->getCurrent()->isOk()
		);
	}
}
?>
