<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Testcase for the category class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_category_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** UID of the fixture's data in the DB */
	private $fixtureUid = 0;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixtureUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'Test category')
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}

	public function testCreateFromUid() {
		$this->fixture = new tx_seminars_category($this->fixtureUid);

		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromUidFailsForInvalidUid() {
		$this->fixture = new tx_seminars_category($this->fixtureUid + 99);

		$this->assertFalse(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromUidFailsForZeroUid() {
		$this->fixture = new tx_seminars_category(0);

		$this->assertFalse(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromDbResult() {
		$dbResult = tx_oelib_db::select(
			'*',
			'tx_seminars_categories',
			'uid = '.$this->fixtureUid
		);

		$this->fixture = new tx_seminars_category(0, $dbResult);

		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromDbResultFailsForNull() {
		$this->fixture = new tx_seminars_category(0, null);

		$this->assertFalse(
			$this->fixture->isOk()
		);
	}

	public function testGetTitle() {
		$this->fixture = new tx_seminars_category($this->fixtureUid);

		$this->assertEquals(
			'Test category',
			$this->fixture->getTitle()
		);
	}

	public function testGetIconReturnsIcon() {
		$this->fixture = new tx_seminars_category(
			$this->testingFramework->createRecord(
				'tx_seminars_categories',
				array(
					'title' => 'Test category',
					'icon' => 'foo.gif',
				)
			)
		);

		$this->assertEquals(
			'foo.gif',
			$this->fixture->getIcon()
		);
	}

	public function testGetIconReturnsEmptyStringIfCategoryHasNoIcon() {
		$this->fixture = new tx_seminars_category($this->fixtureUid);

		$this->assertEquals(
			'',
			$this->fixture->getIcon()
		);
	}


	///////////////////////////////
	// Tests regarding the owner.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getOwnerWithoutOwnerReturnsNull() {
		$this->fixture = new tx_seminars_category($this->fixtureUid);

		$this->assertNull(
			$this->fixture->getOwner()
		);
	}

	/**
	 * @test
	 */
	public function getOwnerWithOwnerReturnsOwner() {
		$this->fixture = new tx_seminars_category($this->fixtureUid);

		$frontEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUser'
		)->getNewGhost();
		$this->fixture->setOwner($frontEndUser);

		$this->assertSame(
			$frontEndUser,
			$this->fixture->getOwner()
		);
	}
}
?>