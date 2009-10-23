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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars')  . 'tests/fixtures/class.tx_seminars_registrationchild.php');

/**
 * Testcase for the EmailSalutation class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_EmailSalutation_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework the testing framework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_EmailSalutation the fixture the tests relate to
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixture = new tx_seminars_EmailSalutation();
		tx_oelib_ConfigurationRegistry::getInstance()->get(
			'seminars')->setAsString('salutation', 'formal');
	}

	public function tearDown() {
		$this->fixture->__destruct();
		$this->testingFramework->cleanUp();

		unset($this->testingFramework, $this->fixture);
	}

	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates an FE-user with the given gender and the name "Foo".
	 *
	 * @param integer $gender
	 *        the gender for the FE user, must be one of
	 *        "tx_oelib_Model_FrontEndUser::GENDER_MALE",
	 *        "tx_oelib_Model_FrontEndUser::GENDER_FEMALE" or
	 *        "tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN", may be empty
	 *
	 * @return tx_seminars_Model_FrontEndUser the loaded testing model of a
	 *                                        FE user
	 */
	private function createFrontEndUser(
		$gender = tx_oelib_Model_FrontEndUser::GENDER_MALE
	) {
		return tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(
				array('name' => 'Foo', 'gender' => $gender)
		);
	}


	///////////////////////////////////////////
	// Tests concerning the utility functions
	///////////////////////////////////////////

	public function test_createFrontEndUser_ReturnsFeUserModel() {
		$this->assertTrue(
			$this->createFrontEndUser() instanceof tx_seminars_Model_FrontEndUser
		);
	}

	public function test_createFrontEndUserForGivenGender_AssignesGenderToFrontEndUser() {
		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->createFrontEndUser(tx_oelib_Model_FrontEndUser::GENDER_FEMALE)
				->getGender()
		);
	}


	///////////////////////////////////
	// Tests concerning getSalutation
	///////////////////////////////////

	public function test_getSalutation_ReturnsUsernameOfRegistration() {
		$this->assertContains(
			'Foo',
			$this->fixture->getSalutation($this->createFrontEndUser())
		);
	}

	public function test_getSalutationForMaleUser_ReturnsMaleSalutation() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_MALE
		);

		$this->assertEquals(
			sprintf(
				tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
					->translate('email_salutation_formal_0'),
				$user->getLastOrFullName()
			),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForFemaleUser_ReturnsFemaleSalutation() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE
		);

		$this->assertEquals(
			sprintf(
				tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
					->translate('email_salutation_formal_1'),
				$user->getLastOrFullName()
			),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForUserWithUnknownGender_ReturnsGenderUnknownSalutation() {
		$user = $this->createFrontEndUser(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN
		);

		$this->assertEquals(
			sprintf(
				tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
					->translate('email_salutation_formal_2'),
				$user->getLastOrFullName()
			),
			$this->fixture->getSalutation($user)
		);
	}

	public function test_getSalutationForInformalSalutation_ReturnsInformalSalutation() {
		$user = $this->createFrontEndUser();
		tx_oelib_ConfigurationRegistry::getInstance()->get(
			'seminars')->setAsString('salutation', 'informal');

		$this->assertEquals(
			sprintf(
				tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
					->translate('email_salutation_informal'),
				$user->getLastOrFullName()
			),
			$this->fixture->getSalutation($user)
		);
	}
}
?>