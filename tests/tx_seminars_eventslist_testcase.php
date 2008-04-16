<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the events list class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(PATH_t3lib.'class.t3lib_scbase.php');

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'mod2/class.tx_seminars_eventslist.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_eventslist_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** PID of a dummy system folder */
	private $dummySysFolderPid = 0;

	/** a dummy BE page object */
	private $page;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid
			= $this->testingFramework->createSystemFolder();

		$this->page = new t3lib_SCbase();
		$this->page->id = $this->dummySysFolderPid;
		$this->page->pageInfo = array();
		$this->page->pageInfo['uid'] = $this->dummySysFolderPid;

		$this->page->doc = t3lib_div::makeInstance('bigDoc');
		$this->page->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->page->doc->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_eventslist($this->page);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->page, $this->fixture, $this->testingFramework);
	}


	/////////////////////////////////////////
	// Tests for the events list functions.
	/////////////////////////////////////////

	public function testShowContainsNoBodyHeaderWithEmptySystemFolder() {
		$this->assertNotContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsTableBodyHeaderForOneEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid)
		);

		$this->assertContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsNoBodyHeaderIfEventIsOnOtherPage() {
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertNotContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1'
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForTwoEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_2'
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
		$this->assertContains(
			'event_2',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneHiddenEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'hidden' => 1
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneTimedEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'endtime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}


	/////////////////////////
	// Tests for the icons.
	/////////////////////////

	public function testUsesCorrectIconForSingleEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForTopic() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_DATE
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_date.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForHiddenSingleEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'hidden' => 1
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete__h.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForHiddenTopic() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'hidden' => 1
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic__h.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForHiddenDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'hidden' => 1
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_date__h.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForVisibleTimedSingleEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'starttime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForVisibleTimedTopic() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'starttime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForVisibleTimedDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'starttime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_date.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForExpiredSingleEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'endtime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete__t.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForExpiredTimedTopic() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'endtime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic__t.',
			$this->fixture->show()
		);
	}

	public function testUsesCorrectIconForExpiredTimedDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'endtime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_date__t.',
			$this->fixture->show()
		);
	}
}
?>
