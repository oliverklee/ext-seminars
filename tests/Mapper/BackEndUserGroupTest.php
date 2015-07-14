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
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Mapper_BackEndUserGroupTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_seminars_Mapper_BackEndUserGroup the object to test
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		$this->fixture = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_BackEndUserGroup');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	/////////////////////////////////////////
	// Tests concerning the basic functions
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function findReturnsBackEndUserGroupInstance() {
		$uid = $this->fixture->getNewGhost()->getUid();

		self::assertTrue(
			$this->fixture->find($uid)
				instanceof tx_seminars_Model_BackEndUserGroup
		);
	}

	/**
	 * @test
	 */
	public function loadForExistingUserGroupCanLoadUserGroupData() {
		/** @var tx_seminars_Model_BackEndUserGroup $userGroup */
		$userGroup = $this->fixture->find(
			$this->testingFramework->createBackEndUserGroup(
				array('title' => 'foo')
			)
		);

		$this->fixture->load($userGroup);

		self::assertEquals(
			'foo',
			$userGroup->getTitle()
		);
	}
}