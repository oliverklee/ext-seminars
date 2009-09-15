<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_seminars_pi1_frontEndEditor class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_frontEndEditor_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_pi1_frontEndEditor
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->fixture = new tx_seminars_pi1_frontEndEditor(
			array(), $GLOBALS['TSFE']->cObj
		);
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////
	// Testing the testmode flag
	//////////////////////////////

	public function testIsTestModeReturnsTrueForTestModeEnabled() {
		$this->assertTrue(
			$this->fixture->isTestMode()
		);
	}

	public function testIsTestModeReturnsFalseForTestModeDisabled() {
		$fixture = new tx_seminars_pi1_frontEndEditor(array(), $GLOBALS['TSFE']->cObj);

		$this->assertFalse(
			$fixture->isTestMode()
		);

		$fixture->__destruct();
	}


	/////////////////////////////////////////////////
	// Tests for setting and getting the object UID
	/////////////////////////////////////////////////

	public function testGetObjectUidReturnsTheSetObjectUidForZero() {
		$this->fixture->setObjectUid(0);

		$this->assertEquals(
			0,
			$this->fixture->getObjectUid()
		);
	}

	public function testGetObjectUidReturnsTheSetObjectUidForExistingObjectUid() {
		$uid = $this->testingFramework->createRecord('tx_seminars_test');
		$this->fixture->setObjectUid($uid);

		$this->assertEquals(
			$uid,
			$this->fixture->getObjectUid()
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests for getting form values and setting faked form values
	////////////////////////////////////////////////////////////////

	public function testGetFormValueReturnsEmptyStringForRequestedFormValueNotSet() {
		$this->assertEquals(
			'',
			$this->fixture->getFormValue('title')
		);
	}

	public function testGetFormValueReturnsValueSetViaSetFakedFormValue() {
		$this->fixture->setFakedFormValue('title', 'foo');

		$this->assertEquals(
			'foo',
			$this->fixture->getFormValue('title')
		);
	}
}
?>