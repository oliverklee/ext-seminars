<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_seminars_BackEndExtJs_Module class in the "seminars"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEndExtJs_ModuleTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEndExtJs_Module
	 */
	private $fixture;

	/**
	 * back-up of $GLOBALS['BE_USER']
	 *
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackUp;

	public function setUp() {
		$this->fixture = new tx_seminars_tests_fixtures_BackEndExtJs_TestingModule();
		$this->fixture->init();

		$this->backEndUserBackUp = $GLOBALS['BE_USER'];
	}

	public function tearDown() {
		$GLOBALS['BE_USER'] = $this->backEndUserBackUp;
		unset($this->fixture, $this->backEndUserBackUp);
	}


	/**
	 * @test
	 */
	public function mainAddsCssFileToPageRenderer() {
		$pageRenderer = $this->getMock('t3lib_PageRenderer', array('addCssFile'));
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(0))
			->method('addCssFile')
			->with(
				t3lib_extMgm::extRelPath('seminars') . 'Resources/Public/CSS/BackEndExtJs/BackEnd.css',
				'stylesheet',
				'all',
				'',
				FALSE
			);

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainAddsPrintCssFileToPageRenderer() {
		$pageRenderer = $this->getMock('t3lib_PageRenderer', array('addCssFile'));
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(1))
			->method('addCssFile')
			->with(
				t3lib_extMgm::extRelPath('seminars') . 'Resources/Public/CSS/BackEndExtJs/Print.css',
				'stylesheet',
				'print',
				'',
				FALSE
			);

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainAddsFlashMessagesJavaScriptFileFromCoreToPageRendererIfAvailable() {
		$pageRenderer = $this->getMock('t3lib_PageRenderer', array('addJsFile'));
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(0))
			->method('addJsFile')
			->with(
				t3lib_div::getIndpEnv('TYPO3_SITE_PATH') .
					't3lib/js/extjs/ux/flashmessages.js',
				'text/javascript',
				FALSE
			);

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainAddsJavaScriptFileToPageRenderer() {
		$pageRenderer = $this->getMock('t3lib_PageRenderer', array('addJsFile'));
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(1))
			->method('addJsFile')
			->with(
				t3lib_extMgm::extRelPath('seminars') . 'Resources/Public/JavaScript/BackEndExtJs/BackEnd.js',
				'text/javascript',
				FALSE
			);

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainLoadsExtJsViaPageRenderer() {
		$pageRenderer = $this->getMock('t3lib_PageRenderer', array('loadExtJS'));
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->once())
			->method('loadExtJS');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesSelectAndSeminarsTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(0))
			->method('check')
			->with('tables_select', 'tx_seminars_seminars');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesSelectAndAttendancesTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(2))
			->method('check')
			->with('tables_select', 'tx_seminars_attendances');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesSelectAndSpeakersTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(4))
			->method('check')
			->with('tables_select', 'tx_seminars_speakers');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesSelectAndOrganizersTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(6))
			->method('check')
			->with('tables_select', 'tx_seminars_organizers');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesModifyAndSeminarsTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(1))
			->method('check')
			->with('tables_modify', 'tx_seminars_seminars');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesModifyAndAttendancesTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(3))
			->method('check')
			->with('tables_modify', 'tx_seminars_attendances');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesModifyAndSpeakersTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(5))
			->method('check')
			->with('tables_modify', 'tx_seminars_speakers');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainCallsBackEndUserCheckWithTablesModifyAndOrganizersTableName() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('check')
		);
		$GLOBALS['BE_USER']->user = $this->backEndUserBackUp->user;
		$GLOBALS['BE_USER']
			->expects($this->at(7))
			->method('check')
			->with('tables_modify', 'tx_seminars_organizers');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainAddsPidInlineSettingViaPageRenderer() {
		$pageRenderer = $this->getMock(
			't3lib_PageRenderer', array('addInlineSetting')
		);
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(0))
			->method('addInlineSetting')
			->with(FALSE, 'PID');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainAddsUrlInlineSettingsViaPageRenderer() {
		$pageRenderer = $this->getMock(
			't3lib_PageRenderer', array('addInlineSettingArray')
		);
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(0))
			->method('addInlineSettingArray');

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainAddsFoldersFromBackEndUserGroupInlineSettingViaPageRenderer() {
		$backEndUserGroup = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_BackEndUserGroup')
			->getLoadedTestingModel(array(
				'tx_seminars_events_folder' => 1,
				'tx_seminars_registrations_folder' => 2,
				'tx_seminars_auxiliaries_folder' => 3,
			));
		$backEndUser = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_BackEndUser')
			->getLoadedTestingModel(array(
				'usergroup' => $backEndUserGroup->getUid()
			));
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

		$pageRenderer = $this->getMock(
			't3lib_PageRenderer', array('addInlineSettingArray')
		);
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(5))
			->method('addInlineSettingArray')
			->with(
				'Backend.Seminars',
				array(
					'EventsFolder' => 1,
					'RegistrationsFolder' => 2,
					'AuxiliaryRecordsFolder' => 3,
				)
			);

		$this->fixture->main();
	}

	/**
	 * @test
	 */
	public function mainAddsLanguageLabelsFileViaPageRenderer() {
		$pageRenderer = $this->getMock('t3lib_PageRenderer', array('addJsFile'));
		$this->fixture->setPageRenderer($pageRenderer);

		$pageRenderer->expects($this->at(2))
			->method('addJsFile')
			->with(
				'../' . tx_seminars_BackEndExtJs_Module::LANGUAGE_LABELS_CACHE_PATH .
					'seminars_' . $GLOBALS['LANG']->lang . '.js',
				'text/javascript',
				FALSE,
				TRUE,
				'',
				TRUE
			);

		$this->fixture->main();
	}
}
?>