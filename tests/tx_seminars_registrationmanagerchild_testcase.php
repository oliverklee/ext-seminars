<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the registrationmanager class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars')
	.'tests/fixtures/class.tx_seminars_registrationmanagerchild.php');
require_once(t3lib_extMgm::extPath('seminars')
	.'tests/fixtures/class.tx_seminars_seminarchild.php');

class tx_seminars_registrationmanagerchild_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $seminar;
	private $currentTimestamp;

	protected function setUp() {
		$this->currentTimestamp = time();

		$this->fixture = new tx_seminars_registrationmanagerchild(
			array(
				'unregistrationDeadlineDaysBeforeBeginDate' => 0
			)
		);

		$this->seminar = new tx_seminars_seminarchild(
			array()
		);
		$this->seminar->setEventData(
			array(
				'uid' => 1,
				'begin_date' => $this->currentTimestamp + ONE_WEEK,
				'deadline_unregistration' => 0,
				'attendees_min' => 5,
				'attendees_max' => 10,
				'object_type' => 0
			)
		);
	}

	protected function tearDown() {
		unset($this->fixture);
		unset($this->seminar);
	}


	public function testIsUnregistrationPossibleWithNoDeadlineSet() {
		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->seminar->setUnregistrationDeadline(0);
		$this->seminar->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->seminar->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);
	}

	public function testIsUnregistrationPossibleWithGlobalDeadlineSet() {
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->seminar->setUnregistrationDeadline(0);
		$this->seminar->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->seminar->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);


		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->seminar->setBeginDate($this->currentTimestamp);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);
	}

	public function testIsUnregistrationPossibleWithEventDeadlineSet() {
		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->seminar->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->seminar->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->seminar->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);


		$this->seminar->setBeginDate($this->currentTimestamp);
		$this->seminar->setUnregistrationDeadline(
			($this->currentTimestamp - ONE_DAY)
		);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);
	}

	public function testIsUnregistrationPossibleWithBothDeadlinesSet() {
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->seminar->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->seminar->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->seminar->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);


		$this->seminar->setUnregistrationDeadline(
			($this->currentTimestamp - ONE_DAY)
		);
		$this->seminar->setBeginDate($this->currentTimestamp);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);
	}

	public function testIsUnregistrationPossibleWithNoRegistrationNeeded() {
		$this->seminar->setAttendancesMax(10);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->seminar->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->seminar->setBeginDate(($this->currentTimestamp + ONE_WEEK));

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);


		$this->seminar->setAttendancesMax(0);
		$this->assertFalse(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);
	}

	public function testIsUnregistrationPossibleWithPassedEventUnregistrationDeadlineSet() {
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->seminar->setBeginDate($this->currentTimestamp + (2*ONE_DAY));
		$this->seminar->setUnregistrationDeadline(
			$this->currentTimestamp - ONE_DAY
		);
		$this->seminar->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible($this->seminar)
		);
	}
}

?>
