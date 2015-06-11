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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Mapper_FrontEndUserTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Mapper_FrontEndUser the object to test
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUser'
		);
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
	public function mapperForGhostReturnsSeminarsFrontEndUserInstance() {
		self::assertTrue(
			$this->fixture->getNewGhost()
				instanceof tx_seminars_Model_FrontEndUser
		);
	}


	///////////////////////////////////
	// Tests concerning the relations
	///////////////////////////////////

	/**
	 * @test
	 */
	public function relationToRegistrationIsReadFromRegistrationMapper() {
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getNewGhost();

		$model = $this->fixture->getLoadedTestingModel(
			array('tx_seminars_registration' => $registration->getUid())
		);

		self::assertSame(
			$registration,
			$model->getRegistration()
		);
	}
}