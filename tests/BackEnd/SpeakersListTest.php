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
class tx_seminars_BackEnd_SpeakersListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_SpeakersList
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var int PID of a dummy system folder
	 */
	private $dummySysFolderPid = 0;

	/**
	 * @var tx_seminars_BackEnd_Module a dummy BE module
	 */
	private $backEndModule;

	/**
	* @var string the original language of the back-end module
	*/
	private $originalLanguage;

	protected function setUp() {
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

		// Sets the localization to the default language so that all tests can
		// run even if the BE user has its interface set to another language.
		$this->originalLanguage = $GLOBALS['LANG']->lang;
		$GLOBALS['LANG']->lang = 'default';

		// Loads the locallang file for properly working localization in the tests.
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/BackEnd/locallang.xml');

		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid
			= $this->testingFramework->createSystemFolder();

		$this->backEndModule = new tx_seminars_BackEnd_Module();
		$this->backEndModule->id = $this->dummySysFolderPid;
		$this->backEndModule->setPageData(array(
			'uid' => $this->dummySysFolderPid,
			'doktype' => tx_seminars_BackEnd_AbstractList::SYSFOLDER_TYPE,
		));

		$document = new bigDoc();
		$this->backEndModule->doc = $document;
		$document->backPath = $GLOBALS['BACK_PATH'];
		$document->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_BackEnd_SpeakersList(
			$this->backEndModule
		);
	}

	protected function tearDown() {
		// Resets the language of the interface to the value it had before
		// we set it to "default" for testing.
		$GLOBALS['LANG']->lang = $this->originalLanguage;

		$this->testingFramework->cleanUp();
	}

	/**
	 * @test
	 */
	public function showContainsHideButtonForVisibleSpeaker() {
		$this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array(
				'pid' => $this->dummySysFolderPid,
				'hidden' => 0,
			)
		);

		self::assertContains(
			'Icons/Hide.gif',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showContainsUnhideButtonForHiddenSpeaker() {
		$this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array(
				'pid' => $this->dummySysFolderPid,
				'hidden' => 1,
			)
		);

		self::assertContains(
			'Icons/Unhide.gif',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showContainsSpeakerFromSubfolder() {
		$subfolderPid = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array(
				'title' => 'Speaker in subfolder',
				'pid' => $subfolderPid,
			)
		);

		self::assertContains(
			'Speaker in subfolder',
			$this->fixture->show()
		);
	}


	//////////////////////////////////////
	// Tests concerning the "new" button
	//////////////////////////////////////

	public function testNewButtonForSpeakerStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid() {
		$newSpeakerFolder = $this->dummySysFolderPid + 1;
		$backEndGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUserGroup')->getLoadedTestingModel(
			array('tx_seminars_auxiliaries_folder' => $newSpeakerFolder)
		);
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUser')->getLoadedTestingModel(
				array('usergroup' => $backEndGroup->getUid())
		);
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
			$backEndUser
		);

		self::assertContains(
			'edit[tx_seminars_speakers][' . $newSpeakerFolder . ']=new',
			$this->fixture->show()
		);
	}
}