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
 * @author 2009 Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_FrontEnd_PublishEventTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_PublishEvent
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();
		$this->fixture = new tx_seminars_FrontEnd_PublishEvent();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();

		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////
	// Tests concerning the rendering
	///////////////////////////////////

	/**
	 * @test
	 */
	public function renderForNoPublicationHashSetInPiVarsReturnsPublishFailedMessage() {
		$this->assertEquals(
			$this->fixture->translate('message_publishingFailed'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForEmptyPublicationHashSetInPiVarsReturnsPublishFailedMessage() {
		$this->fixture->piVars['hash'] = '';

		$this->assertEquals(
			$this->fixture->translate('message_publishingFailed'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForInvalidPublicationHashSetInPiVarsReturnsPublishFailedMessage() {
		$this->fixture->piVars['hash'] = 'foo';

		$this->assertEquals(
			$this->fixture->translate('message_publishingFailed'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForValidPublicationHashAndVisibleEventReturnsPublishFailedMessage() {
		$this->fixture->init(array());
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('hidden' => 0, 'publication_hash' => '123456ABC')
		);

		$this->fixture->piVars['hash'] = '123456ABC';

		$this->assertEquals(
			$this->fixture->translate('message_publishingFailed'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForValidPublicationHashAndHiddenEventReturnsPublishSuccessfulMessage() {
		$this->fixture->init(array());
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('hidden' => 1, 'publication_hash' => '123456ABC')
		);

		$this->fixture->piVars['hash'] = '123456ABC';

		$this->assertEquals(
			$this->fixture->translate('message_publishingSuccessful'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForValidPublicationHashUnhidesEventWithPublicationHash() {
		$this->fixture->init(array());
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('hidden' => 1, 'publication_hash' => '123456ABC')
		);
		$this->fixture->piVars['hash'] = '123456ABC';

		$this->fixture->render();

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars', 'uid = ' . $eventUid . ' AND hidden = 0'
			)
		);
	}

	/**
	 * @test
	 */
	public function renderForValidPublicationHashRemovesPublicationHashFromEvent() {
		$this->fixture->init(array());
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('hidden' => 1, 'publication_hash' => '123456ABC')
		);
		$this->fixture->piVars['hash'] = '123456ABC';

		$this->fixture->render();

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars', 'uid = ' . $eventUid .
					' AND publication_hash = ""'
			)
		);
	}
}