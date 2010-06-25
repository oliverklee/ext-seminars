<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_seminars_FrontEnd_PublishEvent class of the "seminars"
 * extension.
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

	public function test_RenderForNoPublicationHashSetInPiVars_ReturnsPublishFailedMessage() {
		$this->assertEquals(
			$this->fixture->translate('message_publishingFailed'),
			$this->fixture->render()
		);
	}

	public function test_RenderForEmptyPublicationHashSetInPiVars_ReturnsPublishFailedMessage() {
		$this->fixture->piVars['hash'] = '';

		$this->assertEquals(
			$this->fixture->translate('message_publishingFailed'),
			$this->fixture->render()
		);
	}

	public function test_RenderForInvalidPublicationHashSetInPiVars_ReturnsPublishFailedMessage() {
		$this->fixture->piVars['hash'] = 'foo';

		$this->assertEquals(
			$this->fixture->translate('message_publishingFailed'),
			$this->fixture->render()
		);
	}

	public function test_RenderForValidPublicationHashAndVisibleEvent_ReturnsPublishFailedMessage() {
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

	public function test_RenderForValidPublicationHashAndHiddenEvent_ReturnsPublishSuccessfulMessage() {
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

	public function test_RenderForValidPublicationHash_UnhidesEventWithPublicationHash() {
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

	public function test_RenderForValidPublicationHash_RemovesPublicationHashFromEvent() {
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
?>