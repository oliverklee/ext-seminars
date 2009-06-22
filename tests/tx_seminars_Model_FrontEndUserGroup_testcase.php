<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_seminars_Model_FrontEndUserGroup class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_FrontEndUserGroup_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_FrontEndUserGroup the object to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_FrontEndUserGroup();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	///////////////////////////////////////
	// Tests concerning getPublishSetting
	///////////////////////////////////////

	public function test_getPublishSetting_WithoutPublishSetting_ReturnsPublishAll() {
		$this->fixture->setData(array());

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSetting_WithPublishSettingSetToZero_ReturnsPublishAll() {
		$this->fixture->setData(array('tx_seminars_publish_events' => 0));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSetting_WithPublishSettingSetToOne_ReturnsHideNew() {
		$this->fixture->setData(array('tx_seminars_publish_events' => 1));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSetting_WithPublishSettingSetToTwo_ReturnsHideEdited() {
		$this->fixture->setData(array('tx_seminars_publish_events' => 2));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning getAuxiliaryRecordsPid().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithoutPidReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithPidReturnsPid() {
		$this->fixture->setData(array('tx_seminars_auxiliary_records_pid' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning hasAuxiliaryRecordsPid().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasAuxiliaryRecordsPidWithoutPidReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function hasAuxiliaryRecordsPidWithPidReturnsTrue() {
		$this->fixture->setData(array('tx_seminars_auxiliary_records_pid' => 42));

		$this->assertTrue(
			$this->fixture->hasAuxiliaryRecordsPid()
		);
	}
}
?>