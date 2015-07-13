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
 * Testcase.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_FrontEnd_CountdownTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_seminars_FrontEnd_Countdown
	 */
	private $fixture = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework = NULL;

	/**
	 * @var tx_seminars_Mapper_Event|PHPUnit_Framework_MockObject_MockObject
	 */
	private $mapper = NULL;

	/**
	 * @var tx_seminars_ViewHelper_Countdown|PHPUnit_Framework_MockObject_MockObject
	 */
	private $viewHelper = NULL;

	protected function setUp() {
		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('config', new Tx_Oelib_Configuration());
		$configurationRegistry->set('page.config', new Tx_Oelib_Configuration());
		$configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new Tx_Oelib_Configuration());

		Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
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
		self::assertTrue(
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
		$this->mapper->expects(self::once())
			->method('findNextUpcoming')
			->will(self::throwException(new tx_oelib_Exception_NotFound()));

		self::assertContains(
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

		$this->mapper->expects(self::once())
			->method('findNextUpcoming')
			->will(self::returnValue($event));

		$this->viewHelper = $this->getMock('tx_seminars_ViewHelper_Countdown', array('render'));
		$this->viewHelper->expects(self::once())
			->method('render')
			->with(self::equalTo($event->getBeginDateAsUnixTimeStamp()));

		$this->fixture->injectCountDownViewHelper($this->viewHelper);

		$this->fixture->render();
	}
}