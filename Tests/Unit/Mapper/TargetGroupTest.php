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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_TargetGroupTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	/**
	 * @var Tx_Seminars_Mapper_TargetGroup
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$this->fixture = new Tx_Seminars_Mapper_TargetGroup();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidReturnsTargetGroupInstance() {
		self::assertTrue(
			$this->fixture->find(1) instanceof Tx_Seminars_Model_TargetGroup
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('title' => 'Housewives')
		);

		/** @var Tx_Seminars_Model_TargetGroup $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			'Housewives',
			$model->getTitle()
		);
	}


	///////////////////////////////
	// Tests regarding the owner.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getOwnerWithoutOwnerReturnsNull() {
		self::assertNull(
			$this->fixture->getLoadedTestingModel(array())->getOwner()
		);
	}

	/**
	 * @test
	 */
	public function getOwnerWithOwnerReturnsOwnerInstance() {
		$frontEndUser = Tx_Oelib_MapperRegistry::
			get(Tx_Seminars_Mapper_FrontEndUser::class)->getLoadedTestingModel(array());

		self::assertInstanceOf(
			Tx_Seminars_Model_FrontEndUser::class,
			$this->fixture->getLoadedTestingModel(
				array('owner' => $frontEndUser->getUid())
			)->getOwner()
		);
	}
}