<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_seminars_Model_BackEndUserGroup class in the 'seminars'
 * extension.
 *
 * @package TYPO3
 * @subpackage seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Model_BackEndUserGroupTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_BackEndUserGroup
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_BackEndUserGroup();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////
	// Tests concerning getTitle()
	////////////////////////////////

	public function test_GetTitle_ForNonEmptyGroupTitle_ReturnsGroupTitle() {
		$this->fixture->setData(array('title' => 'foo'));

		$this->assertEquals(
			'foo',
			$this->fixture->getTitle()
		);
	}

	public function test_GetTitle_ForEmptyGroupTitle_ReturnsEmptyString() {
		$this->fixture->setData(array('title' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getTitle()
		);
	}

	////////////////////////////////////
	// Tests concerning getEventFolder
	////////////////////////////////////

	public function test_getEventFolderForNoSetEventFolder_ReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getEventFolder()
		);
	}

	public function test_getEventFolderForSetEventFolder_ReturnsEventFolderPid() {
		$this->fixture->setData(array('tx_seminars_events_folder' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getEventFolder()
		);
	}


	///////////////////////////////////////////
	// Tests concerning getRegistrationFolder
	///////////////////////////////////////////


	public function test_getRegistrationFolderForNoSetRegistrationFolder_ReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getRegistrationFolder()
		);
	}

	public function test_getRegistrationFolderForSetRegistrationFolder_ReturnsRegistrationFolderPid() {
		$this->fixture->setData(array('tx_seminars_registrations_folder' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getRegistrationFolder()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning getAuxiliaryRecordsFolder
	///////////////////////////////////////////////


	public function test_getAuxiliaryRecordsFolderForNoSetAuxiliaryRecordsFolder_ReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getAuxiliaryRecordFolder()
		);
	}

	public function test_getAuxiliaryRecordsFolderForSetAuxiliaryRecordsFolder_ReturnsAuxiliaryRecordsFolderPid() {
		$this->fixture->setData(array('tx_seminars_auxiliaries_folder' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getAuxiliaryRecordFolder()
		);
	}
}
?>