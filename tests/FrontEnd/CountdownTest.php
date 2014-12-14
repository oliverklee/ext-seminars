<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_seminars_FrontEnd_Countdown class.
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
	 * @var tx_seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject
	 */
	private $mapper;

	/**
	 * @var tx_seminars_ViewHelper_Countdown|PHPUnit_Framework_MockObject_MockObject
	 */
	private $viewHelper;

	protected function setUp() {
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->mapper = $this->getMock('tx_seminars_Mapper_Event', array('findNextUpcoming'));

		$this->fixture = new tx_seminars_FrontEnd_Countdown(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
			),
			$GLOBALS['TSFE']->cObj
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
	}


	//////////////////////////////////////////
	// General tests concerning the fixture.
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function fixtureIsAFrontEndCountdownObject() {
		$this->assertTrue(
			$this->fixture instanceof tx_seminars_FrontEnd_Countdown
		);
	}


	////////////////////////////////
	// Tests for render()
	////////////////////////////////

	/**
	 * @test
	 * @expectedException BadMethodCallException
	 * @expectedExceptionMessage The method injectEventMapper() needs to be called first.
	 * @expectedExceptionCode 1333617194
	 */
	public function renderWithoutCallingInjectEventMapperFirstThrowsBadMethodCallException() {
		$this->fixture->render();
	}

	/**
	 * @test
	 */
	public function renderWithMapperFindNextUpcomingThrowingEmptyQueryResultExceptionReturnsNoEventsFoundMessage() {
		$this->fixture->injectEventMapper($this->mapper);
		$this->mapper->expects($this->once())
			->method('findNextUpcoming')
			->will($this->throwException(new tx_oelib_Exception_NotFound()));

		$this->assertContains(
			'There are no upcoming events. Please come back later.',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCallsRenderMethodOfCountdownViewHelperWithNextUpcomingEventsBeginDateAsUnixTimeStamp() {
		$this->fixture->injectEventMapper($this->mapper);
		$event = $this->mapper->getLoadedTestingModel(array(
			'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
			'pid' => 0,
			'title' => 'Test event',
			'begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000,
		));

		$this->mapper->expects($this->once())
			->method('findNextUpcoming')
			->will($this->returnValue($event));

		$this->viewHelper = $this->getMock('tx_seminars_ViewHelper_Countdown', array('render'));
		$this->viewHelper->expects($this->once())
			->method('render')
			->with($this->equalTo($event->getBeginDateAsUnixTimeStamp()));

		$this->fixture->injectCountDownViewHelper($this->viewHelper);

		$this->fixture->render();
	}
}