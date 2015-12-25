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
class Tx_Seminars_Tests_Unit_Mapper_FoodTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	/**
	 * @var Tx_Seminars_Mapper_Food
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$this->fixture = new Tx_Seminars_Mapper_Food();
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
	public function findWithUidReturnsFoodInstance() {
		self::assertInstanceOf(Tx_Seminars_Model_Food::class, $this->fixture->find(1));
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('title' => 'Crunchy crisps')
		);

		/** @var Tx_Seminars_Model_Food $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			'Crunchy crisps',
			$model->getTitle()
		);
	}
}