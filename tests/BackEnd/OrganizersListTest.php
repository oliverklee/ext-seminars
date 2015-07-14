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
class tx_seminars_BackEnd_OrganizersListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_OrganizersList
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

		$this->fixture = new tx_seminars_BackEnd_OrganizersList(
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
	public function showContainsOrganizerFromSubfolder() {
		$subfolderPid = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'Organizer in subfolder',
				'pid' => $subfolderPid,
			)
		);

		self::assertContains(
			'Organizer in subfolder',
			$this->fixture->show()
		);
	}


	//////////////////////////////////////
	// Tests concerning the "new" button
	//////////////////////////////////////

	public function testNewButtonForOrganizerStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid() {
		$newOrganizerFolder = $this->dummySysFolderPid + 1;
		$backEndGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUserGroup')->getLoadedTestingModel(
			array('tx_seminars_auxiliaries_folder' => $newOrganizerFolder)
		);
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUser')->getLoadedTestingModel(
				array('usergroup' => $backEndGroup->getUid())
		);
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
			$backEndUser
		);

		self::assertContains(
			'edit[tx_seminars_organizers][' . $newOrganizerFolder . ']=new',
			$this->fixture->show()
		);
	}
}