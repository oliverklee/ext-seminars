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
 * @subpackage seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Model_BackEndUserGroupTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_BackEndUserGroup
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_BackEndUserGroup();
	}

	////////////////////////////////
	// Tests concerning getTitle()
	////////////////////////////////

	/**
	 * @test
	 */
	public function getTitleForNonEmptyGroupTitleReturnsGroupTitle() {
		$this->fixture->setData(array('title' => 'foo'));

		self::assertEquals(
			'foo',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForEmptyGroupTitleReturnsEmptyString() {
		$this->fixture->setData(array('title' => ''));

		self::assertEquals(
			'',
			$this->fixture->getTitle()
		);
	}

	////////////////////////////////////
	// Tests concerning getEventFolder
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getEventFolderForNoSetEventFolderReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getEventFolder()
		);
	}

	/**
	 * @test
	 */
	public function getEventFolderForSetEventFolderReturnsEventFolderPid() {
		$this->fixture->setData(array('tx_seminars_events_folder' => 42));

		self::assertEquals(
			42,
			$this->fixture->getEventFolder()
		);
	}


	///////////////////////////////////////////
	// Tests concerning getRegistrationFolder
	///////////////////////////////////////////


	/**
	 * @test
	 */
	public function getRegistrationFolderForNoSetRegistrationFolderReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getRegistrationFolder()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationFolderForSetRegistrationFolderReturnsRegistrationFolderPid() {
		$this->fixture->setData(array('tx_seminars_registrations_folder' => 42));

		self::assertEquals(
			42,
			$this->fixture->getRegistrationFolder()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning getAuxiliaryRecordsFolder
	///////////////////////////////////////////////


	/**
	 * @test
	 */
	public function getAuxiliaryRecordsFolderForNoSetAuxiliaryRecordsFolderReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getAuxiliaryRecordFolder()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsFolderForSetAuxiliaryRecordsFolderReturnsAuxiliaryRecordsFolderPid() {
		$this->fixture->setData(array('tx_seminars_auxiliaries_folder' => 42));

		self::assertEquals(
			42,
			$this->fixture->getAuxiliaryRecordFolder()
		);
	}
}