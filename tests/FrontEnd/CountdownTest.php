<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2011 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_seminars_FrontEnd_Countdown class in the "seminars"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_FrontEnd_CountdownTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_Countdown
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
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'Test event',
			)
		);

		$this->fixture = new tx_seminars_FrontEnd_Countdown(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
			),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();

		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////////////////
	// General tests concerning the fixture.
	//////////////////////////////////////////

	public function testFixtureIsAFrontEndCountdownObject() {
		$this->assertTrue(
			$this->fixture instanceof tx_seminars_FrontEnd_Countdown
		);
	}


	////////////////////////////////
	// Tests for render()
	////////////////////////////////

	public function testRenderInitiallyReturnsNoEventsFoundMessage() {
		$this->assertContains(
			'There are no upcoming events. Please come back later.',
			$this->fixture->render()
		);
	}

	public function testRenderForPastEventReturnsNoEventsFoundMessage() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->seminarUid,
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] - 1000,
			)
		);

		$this->assertContains(
			'There are no upcoming events. Please come back later.',
			$this->fixture->render()
		);
	}

	public function testRenderForUpcomingEventReturnsCountdownMessage() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->seminarUid,
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000,
			)
		);

		$this->assertContains(
			'left until the next event starts',
			$this->fixture->render()
		);
	}
}
?>