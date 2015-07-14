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
	 * @var int event begin date
	 */
	private $eventDate = 0;

	/**
	 * @var int UID of the event to create the headline for
	 */
	private $eventId = 0;

	protected function setUp() {
		$pluginConfiguration = new Tx_Oelib_Configuration();
		$pluginConfiguration->setAsString('dateFormatYMD', '%d.%m.%Y');
		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('plugin.tx_seminars', $pluginConfiguration);
		$configurationRegistry->set('config', new Tx_Oelib_Configuration());
		$configurationRegistry->set('page.config', new Tx_Oelib_Configuration());
		$configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new Tx_Oelib_Configuration());

		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

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

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
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

		self::assertContains(
			'Test event',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithUidOfExistingEventReturnsHtmlSpecialCharedTitleOfSelectedEvent() {
		/** @var tx_seminars_Model_Event $event */
		$event = $this->mapper->find($this->eventId);
		$event->setTitle('<test>Test event</test>');
		$this->fixture->piVars['uid'] = $this->eventId;

		self::assertContains(
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

		self::assertContains(
			strftime($dateFormat, $this->eventDate),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfNoUidIsSetInPiVar() {
		unset($this->fixture->piVars['uid']);

		self::assertEquals(
			'',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfUidOfInexistentEventIsSetInPiVar() {
		$this->fixture->piVars['uid'] = $this->testingFramework->getAutoIncrement('tx_seminars_seminars');

		self::assertEquals(
			'',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfNonNumericEventUidIsSetInPiVar() {
		$this->fixture->piVars['uid'] = 'foo';

		self::assertEquals(
			'',
			$this->fixture->render()
		);
	}
}