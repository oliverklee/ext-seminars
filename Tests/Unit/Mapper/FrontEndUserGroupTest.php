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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Mapper_FrontEndUserGroupTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_Mapper_FrontEndUserGroup the object to test
	 */
	private $fixture;

	/**
	 * @var Tx_Oelib_TestingFramework the testing framework	 *
	 */
	private $testingFramework;

	protected function setUp() {
		$this->fixture = new Tx_Seminars_Mapper_FrontEndUserGroup();
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function mapperForGhostReturnsSeminarsFrontEndUserGroupInstance() {
		self::assertTrue(
			$this->fixture->getNewGhost()
				instanceof tx_seminars_Model_FrontEndUserGroup
		);
	}


	//////////////////////////////////
	// Tests concerning the reviewer
	//////////////////////////////////

	/**
	 * @test
	 */
	public function frontEndUserGroupCanReturnBackEndUserModel() {
		$backEndUser = tx_oelib_MapperRegistry::get(
			Tx_Oelib_Mapper_BackEndUser::class)->getNewGhost();
		$frontEndUserGroup = $this->fixture->getLoadedTestingModel(
			array('tx_seminars_reviewer' => $backEndUser->getUid())
		);

		/** @var tx_seminars_Model_FrontEndUserGroup $model */
		$model = $this->fixture->find($frontEndUserGroup->getUid());

		self::assertInstanceOf(Tx_Oelib_Model_BackEndUser::class, $model->getReviewer());
	}


	////////////////////////////////////////////
	// Tests concerning the default categories
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function frontEndUserGroupReturnsListOfCategories() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array()
		);
		$frontEndUserGroupUid = $this->testingFramework->createFrontEndUserGroup();

		$this->testingFramework->createRelationAndUpdateCounter(
			'fe_groups', $frontEndUserGroupUid, $categoryUid, 'tx_seminars_default_categories'
		);

		/** @var tx_seminars_Model_FrontEndUserGroup $model */
		$model = $this->fixture->find($frontEndUserGroupUid);
		self::assertTrue(
			$model->getDefaultCategories()->first() instanceof tx_seminars_Model_Category
		);
	}


	///////////////////////////////////////////
	// Tests concerning the default organizer
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getDefaultOrganizerForExistingOrganizerReturnsOrganizer() {
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);
		$groupUid = $this->testingFramework->createFrontEndUserGroup(
			array('tx_seminars_default_organizer' => $organizerUid)
		);

		/** @var tx_seminars_Model_FrontEndUserGroup $model */
		$model = $this->fixture->find($groupUid);
		self::assertTrue(
			$model->getDefaultOrganizer() instanceof tx_seminars_Model_Organizer
		);
	}
}