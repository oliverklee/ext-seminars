<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_seminars_FrontEnd_EventHeadline class.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_FrontEnd_EventHeadlineTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_EventHeadline
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_Mapper_Event
	 */
	private $mapper;

	/**
	 * @var integer event begin date
	 */
	private $eventDate = 0;

	/**
	 * @var integer UID of the event to create the headline for
	 */
	private $eventId = 0;

	public function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		// just picked some random date (2001-01-01 00:00:00)
		$this->eventDate = 978303600;

		$this->mapper = new tx_seminars_Mapper_Event();
		$event = $this->mapper->getLoadedTestingModel(array(
			'pid' => 0,
			'title' => 'Test event',
			'begin_date' => $this->eventDate,
		));
		$this->eventId = $event->getUid();

		$this->fixture = new tx_seminars_FrontEnd_EventHeadline(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
			),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->injectEventMapper($this->mapper);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		$this->mapper->__destruct();
		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->mapper, $this->testingFramework);
	}


	//////////////////////////////////
	// Tests for the render function
	//////////////////////////////////

	/**
	 * @test
	 * @expectedException BadMethodCallException
	 * @expectedExceptionMessage The method injectEventMapper() needs to be called first.
	 * @expectedExceptionCode 1333614794
	 */
	public function renderWithoutCallingInjectEventMapperFirstThrowsBadMethodCallException() {
		$this->fixture->injectEventMapper(NULL);
		$this->fixture->render();
	}

	/**
	 * @test
	 */
	public function renderWithUidOfExistingEventReturnsTitleOfSelectedEvent() {
		$this->fixture->piVars['uid'] = $this->eventId;

		$this->assertContains(
			'Test event',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithUidOfExistingEventReturnsHtmlSpecialCharedTitleOfSelectedEvent() {
		$this->mapper->find($this->eventId)->setTitle('<test>Test event</test>');
		$this->fixture->piVars['uid'] = $this->eventId;

		$this->assertContains(
			htmlspecialchars('<test>Test event</test>'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithUidOfExistingEventReturnsDateOfSelectedEvent() {
		$dateFormat = '%d.%m.%Y';
		$configuration = new tx_oelib_Configuration();
		$configuration->setAsString('dateFormatYMD', $dateFormat);
		tx_oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars_seminars', $configuration);

		$this->fixture->piVars['uid'] = $this->eventId;

		$this->assertContains(
			strftime($dateFormat, $this->eventDate),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfNoUidIsSetInPiVar() {
		unset($this->fixture->piVars['uid']);

		$this->assertEquals(
			'',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfUidOfInexistentEventIsSetInPiVar() {
		$this->fixture->piVars['uid'] = $this->testingFramework->getAutoIncrement('tx_seminars_seminars');

		$this->assertEquals(
			'',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfNonNumericEventUidIsSetInPiVar() {
		$this->fixture->piVars['uid'] = 'foo';

		$this->assertEquals(
			'',
			$this->fixture->render()
		);
	}
}
?>