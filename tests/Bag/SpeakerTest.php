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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Bag_SpeakerTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Bag_Speaker
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
	}


	///////////////////////////////////////////
	// Tests for the basic bag functionality.
	///////////////////////////////////////////

	public function testBagCanHaveAtLeastOneElement() {
		$this->testingFramework->createRecord('tx_seminars_speakers');

		$this->fixture = new tx_seminars_Bag_Speaker('is_dummy_record=1');

		self::assertEquals(
			1,
			$this->fixture->count()
		);
	}

	/**
	 * @test
	 */
	public function bagContainsVisibleSpeakers() {
		$this->testingFramework->createRecord('tx_seminars_speakers');

		$this->fixture = new tx_seminars_Bag_Speaker('is_dummy_record=1');

		self::assertFalse(
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

		$this->fixture = new tx_seminars_Bag_Speaker('is_dummy_record=1');

		self::assertTrue(
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

		$this->fixture = new tx_seminars_Bag_Speaker(
			'is_dummy_record=1',
			'',
			'',
			'uid',
			'',
			-1
		);

		self::assertTrue(
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

		$this->fixture = new tx_seminars_Bag_Speaker(
			'is_dummy_record=1',
			'',
			'',
			'uid',
			'',
			1
		);

		self::assertTrue(
			$this->fixture->current()->isHidden()
		);
	}
}