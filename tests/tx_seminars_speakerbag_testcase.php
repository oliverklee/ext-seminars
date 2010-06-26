<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the speakerbag class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_speakerbag_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_speakerbag
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic bag functionality.
	///////////////////////////////////////////

	public function testBagCanHaveAtLeastOneElement() {
		$this->testingFramework->createRecord('tx_seminars_speakers');

		$this->fixture = new tx_seminars_speakerbag('is_dummy_record=1');

		$this->assertEquals(
			1,
			$this->fixture->count()
		);
	}

	/**
	 * @test
	 */
	public function bagContainsVisibleSpeakers() {
		$this->testingFramework->createRecord('tx_seminars_speakers');

		$this->fixture = new tx_seminars_speakerbag('is_dummy_record=1');

		$this->assertFalse(
			$this->fixture->current()->isHidden()
		);
	}

	/**
	 * @test
	 */
	public function bagIgnoresHiddenSpeakersByDefault() {
		$this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('hidden' => 1)
		);

		$this->fixture = new tx_seminars_speakerbag('is_dummy_record=1');

		$this->assertTrue(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function bagIgnoresHiddenSpeakersWithShowHiddenRecordsSetToMinusOne() {
		$this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('hidden' => 1)
		);

		$this->fixture = new tx_seminars_speakerbag(
			'is_dummy_record=1',
			'',
			'',
			'uid',
			'',
			-1
		);

		$this->assertTrue(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function bagContainsHiddenSpeakersWithShowHiddenRecordsSetToOne() {
		$this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('hidden' => 1)
		);

		$this->fixture = new tx_seminars_speakerbag(
			'is_dummy_record=1',
			'',
			'',
			'uid',
			'',
			1
		);

		$this->assertTrue(
			$this->fixture->current()->isHidden()
		);
	}
}
?>