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
class Tx_Seminars_Tests_Unit_Mapper_BackEndUserTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var Tx_Seminars_Mapper_BackEndUser the object to test
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$this->fixture = new Tx_Seminars_Mapper_BackEndUser();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	public function testFindWithUidOfExistingRecordReturnsBackEndUserInstance() {
		self::assertInstanceOf(
			Tx_Seminars_Model_BackEndUser::class,
			$this->fixture->find($this->testingFramework->createBackEndUser())
		);
	}
}