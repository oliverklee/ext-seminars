<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(PATH_t3lib . 'class.t3lib_scbase.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'mod2/class.tx_seminars_registrationslist.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the registrations list class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registrationslist_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_registrationslist
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer PID of a dummy system folder
	 */
	private $dummySysFolderPid = 0;

	/**
	 * @var t3lib_SCbase a dummy BE page object
	 */
	private $page;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid = $this->testingFramework->createSystemFolder();

		$this->page = new t3lib_SCbase();
		$this->page->id = $this->dummySysFolderPid;
		$this->page->pageInfo = array();
		$this->page->pageInfo['uid'] = $this->dummySysFolderPid;

		$this->page->doc = t3lib_div::makeInstance('bigDoc');
		$this->page->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->page->doc->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_registrationslist($this->page);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset(
			$this->page->doc, $this->page, $this->fixture,
			$this->testingFramework
		);
	}


	////////////////////////////////////////////////
	// Tests for the registrations list functions.
	////////////////////////////////////////////////

	public function testShowForOneEventContainsAccreditationNumber() {
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => 'accreditation number 123',
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertContains(
			'accreditation number 123',
			$this->fixture->show()
		);
	}

	public function testShowForOneEventContainsHtmlSpecialCharedAccreditationNumber() {
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => '&"<>',
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertContains(
			'&amp;&quot;&lt;&gt;',
			$this->fixture->show()
		);
	}
}
?>