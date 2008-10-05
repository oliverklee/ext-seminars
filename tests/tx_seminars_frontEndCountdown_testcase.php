<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_frontEndCountdown.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the 'frontEndCountdown' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_frontEndCountdown_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_frontEndCountdown
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	public function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'Test event',
			)
		);

		$this->fixture = new tx_seminars_frontEndCountdown(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
			),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();

		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////////////////
	// General tests concerning the fixture.
	//////////////////////////////////////////

	public function testFixtureIsAFrontEndCountdownObject() {
		$this->assertTrue(
			$this->fixture instanceof tx_seminars_frontEndCountdown
		);
	}


	////////////////////////////////
	// Tests for createCountdown()
	////////////////////////////////

	public function testCreateCountdownInitiallyReturnsNoEventsFoundMessage() {
		$this->assertContains(
			'There are no upcoming events. Please come back later.',
			$this->fixture->createCountdown()
		);
	}

	public function testCreateCountdownForPastEventReturnsNoEventsFoundMessage() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] - 1000,
			)
		);

		$this->assertContains(
			'There are no upcoming events. Please come back later.',
			$this->fixture->createCountdown()
		);
	}

	public function testCreateCountdownForUpcomingEventReturnsCountdownMessage() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000,
			)
		);

		$this->assertContains(
			'left until the next event starts',
			$this->fixture->createCountdown()
		);
	}
}
?>