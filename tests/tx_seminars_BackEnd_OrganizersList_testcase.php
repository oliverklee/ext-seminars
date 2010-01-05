<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the organizers list class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEnd_OrganizersList_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_OrganizersList
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
	 * @var tx_seminars_BackEnd_Module a dummy BE module
	 */
	private $backEndModule;

	/**
	* @var string the original language of the back-end module
	*/
	private $originalLanguage;

	public function setUp() {
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
			'doktype' => tx_seminars_BackEnd_List::SYSFOLDER_TYPE,
		));

		$this->backEndModule->doc = t3lib_div::makeInstance('bigDoc');
		$this->backEndModule->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->backEndModule->doc->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_BackEnd_OrganizersList(
			$this->backEndModule
		);
	}

	public function tearDown() {
		// Resets the language of the interface to the value it had before
		// we set it to "default" for testing.
		$GLOBALS['LANG']->lang = $this->originalLanguage;

		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		$this->backEndModule->__destruct();
		unset(
			$this->backEndModule, $this->fixture, $this->testingFramework
		);
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

		$this->assertContains(
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

		$this->assertContains(
			'edit[tx_seminars_organizers][' . $newOrganizerFolder . ']=new',
			$this->fixture->show()
		);
	}
}
?>