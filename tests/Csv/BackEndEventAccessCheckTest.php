<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Csv_BackEndEventAccessCheckTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_Csv_BackEndEventAccessCheck
	 */
	protected $subject = NULL;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|t3lib_beUserAuth
	 */
	protected $backEndUser = NULL;

	/**
	 * @var t3lib_beUserAuth
	 */
	protected $backEndUserBackup = NULL;

	protected function setUp() {
		$this->backEndUserBackup = $GLOBALS['BE_USER'];
		$this->backEndUser = $this->getMock('t3lib_beUserAuth');
		$GLOBALS['BE_USER'] = $this->backEndUser;

		$this->subject = new Tx_Seminars_Csv_BackEndEventAccessCheck();
	}

	protected function tearDown() {
		Tx_Oelib_BackEndLoginManager::purgeInstance();
		$GLOBALS['BE_USER'] = $this->backEndUserBackup;

		unset($this->subject, $this->backEndUser, $this->backEndUserBackup);
	}

	/**
	 * @test
	 */
	public function subjectImplementsAccessCheck() {
		$this->assertInstanceOf(
			'Tx_Seminars_Interface_CsvAccessCheck',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForNoBackEndUserReturnsFalse() {
		unset($GLOBALS['BE_USER']);

		$this->assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForNoAccessToTableAndNoAccessToPageReturnsFalse() {
		$pageUid = 12341;
		$this->subject->setPageUid($pageUid);

		$pageRecord = t3lib_BEfunc::getRecord('pages', $pageUid);

		$this->backEndUser->expects($this->any())->method('check')
			->with('tables_select', 'tx_seminars_seminars')
			->will($this->returnValue(FALSE));
		$this->backEndUser->expects($this->any())->method('doesUserHaveAccess')
			->with($pageRecord, 1)
			->will($this->returnValue(FALSE));

		$this->assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForNoAccessToTableAndAccessToPageReturnsFalse() {
		$pageUid = 12341;
		$this->subject->setPageUid($pageUid);

		$pageRecord = t3lib_BEfunc::getRecord('pages', $pageUid);

		$this->backEndUser->expects($this->any())->method('check')
			->with('tables_select', 'tx_seminars_seminars')
			->will($this->returnValue(FALSE));
		$this->backEndUser->expects($this->any())->method('doesUserHaveAccess')
			->with($pageRecord, 1)
			->will($this->returnValue(TRUE));

		$this->assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForAccessToTableAndNoAccessToPageReturnsFalse() {
		$pageUid = 12341;
		$this->subject->setPageUid($pageUid);

		$pageRecord = t3lib_BEfunc::getRecord('pages', $pageUid);

		$this->backEndUser->expects($this->any())->method('check')
			->with('tables_select', 'tx_seminars_seminars')
			->will($this->returnValue(TRUE));
		$this->backEndUser->expects($this->any())->method('doesUserHaveAccess')
			->with($pageRecord, 1)
			->will($this->returnValue(FALSE));

		$this->assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForAccessToTableAndAccessToPageReturnsTrue() {
		$pageUid = 12341;
		$this->subject->setPageUid($pageUid);

		$pageRecord = t3lib_BEfunc::getRecord('pages', $pageUid);

		$this->backEndUser->expects($this->any())->method('check')
			->with('tables_select', 'tx_seminars_seminars')
			->will($this->returnValue(TRUE));
		$this->backEndUser->expects($this->any())->method('doesUserHaveAccess')
			->with($pageRecord, 1)
			->will($this->returnValue(TRUE));

		$this->assertTrue(
			$this->subject->hasAccess()
		);
	}
}