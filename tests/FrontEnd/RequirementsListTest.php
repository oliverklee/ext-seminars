<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2012 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_seminars_FrontEnd_RequirementsList class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_FrontEnd_RequirementsListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_RequirementsList
	 */
	private $fixture;

	/**
	 * @var integer the UID of a seminar to which the plugin relates
	 */
	private $seminarUid;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Test event',
			)
		);

		$this->fixture = new tx_seminars_FrontEnd_RequirementsList(
			array('templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'),
			$GLOBALS['TSFE']->cObj
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////////
	// Tests for the render function
	//////////////////////////////////

	public function testRenderWithoutSetSeminarThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'No event was set, please set an event before calling render'
		);

		$this->fixture->render();
	}

	/**
	 * @test
	 */
	public function renderShowsHtmlspecialcharedTitleOfOneRequirement() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminarUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredEvent = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'required & foo',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $this->seminarUid,
			$requiredEvent, 'requirements'
		);
		$this->fixture->setEvent(new tx_seminars_seminar($this->seminarUid));

		$this->assertContains(
			'required &amp; foo',
			$this->fixture->render()
		);
	}

	public function testRenderLinksOneRequirementToTheSingleView() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminarUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredEvent = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'required_foo',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $this->seminarUid,
			$requiredEvent, 'requirements'
		);
		$this->fixture->setEvent(new tx_seminars_seminar($this->seminarUid));

		$this->assertRegExp(
			'/<a href=.*' . $requiredEvent . '.*>required_foo<\/a>/',
			$this->fixture->render()
		);
	}

	public function testRenderShowsTitleOfTwoRequirements() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminarUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredEvent1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'required_foo',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $this->seminarUid,
			$requiredEvent1, 'requirements'
		);
		$requiredEvent2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'required_bar',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $this->seminarUid,
			$requiredEvent2, 'requirements'
		);
		$this->fixture->setEvent(new tx_seminars_seminar($this->seminarUid));

		$this->assertRegExp(
			'/required_foo.*required_bar/s',
			$this->fixture->render()
		);
	}


	///////////////////////////////////
	// Tests for limiting the results
	///////////////////////////////////

	public function testtLimitToMissingRegistrationsWithNoLoggedInFeUserThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'No FE user is currently logged in. Please call this function only when a FE user is logged in.'
		);

		$this->fixture->limitToMissingRegistrations();
	}

	public function testLimitToMissingRegistrationsLimitsOutputToMissingRegistrationsOnly() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminarUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredEvent1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'required_foo',
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredEvent1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $this->seminarUid,
			$requiredEvent1, 'requirements'
		);
		$requiredEvent2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'required_bar',
			)
		);
		$requiredDate2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredEvent2,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $this->seminarUid,
			$requiredEvent2, 'requirements'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $requiredDate2, 'user' => $userUid)
		);
		$this->fixture->setEvent(new tx_seminars_seminar($this->seminarUid));
		$this->fixture->limitToMissingRegistrations();

		$this->assertNotContains(
			'required_bar',
			$this->fixture->render()
		);
	}
}
?>