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
 */
class tx_seminars_BackEnd_FlexFormsTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_flexForms
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var string name of the table which is used to test the flexforms
	 *      settings
	 */
	private $testingTable = 'tx_seminars_organizers';

	/**
	 * @var array a backup of the TCA setting for the testing table
	 */
	private $tcaBackup;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixture = new tx_seminars_flexForms();
		$this->tcaBackup = $GLOBALS['TCA'][$this->testingTable]['ctrl'];
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsBoolean('useStoragePid', FALSE);
		$GLOBALS['TCA'][$this->testingTable]['ctrl']['iconfile'] = 'fooicon';
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
		$GLOBALS['TCA'][$this->testingTable]['ctrl'] = $this->tcaBackup;
	}


	//////////////////////////////////////////////////////
	// Tests concerning getEntriesFromGeneralStoragePage
	//////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEntriesFromGeneralStoragePageForUseStoragePidAndStoragePidSetFindsRecordWithThisPid() {
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsBoolean('useStoragePid', TRUE);

		$storagePageUid = $this->testingFramework->createSystemFolder();
		$sysFolderUid = $this->testingFramework->createSystemFolder(
			0, array('storage_pid' => $storagePageUid)
		);

		$recordUid = $this->testingFramework->createRecord(
			$this->testingTable,
			array('title' => 'foo record', 'pid' => $storagePageUid)
		);

		$configuration = $this->fixture->getEntriesFromGeneralStoragePage(
			array(
				'config' => array('itemTable' => $this->testingTable),
				'row' => array('pid' => $sysFolderUid),
				'items' => array(),
			)
		);

		self::assertTrue(
			in_array(
				array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
				$configuration['items']
			)
		);
	}

	/**
	 * @test
	 */
	public function getEntriesFromGeneralStoragePageForUseStoragePidAndStoragePidSetDoesNotFindRecordWithOtherPid() {
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsBoolean('useStoragePid', TRUE);

		$sysFolderUid = $this->testingFramework->createSystemFolder(
			0,
			array('storage_pid' => $this->testingFramework->createSystemFolder())
		);
		$recordUid = $this->testingFramework->createRecord(
			$this->testingTable,
			array('title' => 'foo record', 'pid' => 42)
		);

		$configuration = $this->fixture->getEntriesFromGeneralStoragePage(
			array(
				'config' => array('itemTable' => $this->testingTable),
				'row' => array('pid' => $sysFolderUid),
				'items' => array(),
			)
		);

		self::assertFalse(
			in_array(
				array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
				$configuration['items']
			)
		);
	}

	/**
	 * @test
	 */
	public function getEntriesFromGeneralStoragePageForUseStoragePidSetAndStoragePidSetOnParentPageFindsRecordWithThisPid() {
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsBoolean('useStoragePid', TRUE);

		$storagePage = $this->testingFramework->createSystemFolder();
		$parentPageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => $storagePage));
		$sysFolderUid = $this->testingFramework->createFrontEndPage(
			$parentPageUid
		);

		$recordUid = $this->testingFramework->createRecord(
			$this->testingTable,
			array('title' => 'foo record', 'pid' => $storagePage)
		);

		$configuration = $this->fixture->getEntriesFromGeneralStoragePage(
			array(
				'config' => array('itemTable' => $this->testingTable),
				'row' => array('pid' => $sysFolderUid),
				'items' => array(),
			)
		);

		self::assertTrue(
			in_array(
				array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
				$configuration['items']
			)
		);
	}

	/**
	 * @test
	 */
	public function getEntriesFromGeneralStoragePageForUseStoragePidSetAndNoStoragePidSetFindsRecordWithAnyPid() {
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsBoolean('useStoragePid', TRUE);

		$recordUid = $this->testingFramework->createRecord(
			$this->testingTable, array('title' => 'foo record', 'pid' => 42)
		);

		$configuration = $this->fixture->getEntriesFromGeneralStoragePage(
			array(
				'config' => array('itemTable' => $this->testingTable),
				'row' => array('pid'
					=> $this->testingFramework->createFrontEndPage()
				),
				'items' => array(),
			)
		);

		self::assertTrue(
			in_array(
				array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
				$configuration['items']
			)
		);
	}

	/**
	 * @test
	 */
	public function getEntriesFromGeneralStoragePageForUseStoragePidNotAndStoragePidSetFindsRecordWithPidOtherThanStoragePid() {
		$sysFolderUid = $this->testingFramework->createSystemFolder(
			0,
			array('storage_pid' => $this->testingFramework->createSystemFolder())
		);
		$recordUid = $this->testingFramework->createRecord(
			$this->testingTable,
			array('title' => 'foo record', 'pid' => 42)
		);

		$configuration = $this->fixture->getEntriesFromGeneralStoragePage(
			array(
				'config' => array('itemTable' => $this->testingTable),
				'row' => array('pid' => $sysFolderUid),
				'items' => array(),
			)
		);

		self::assertTrue(
			in_array(
				array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
				$configuration['items']
			)
		);
	}
}