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
use TYPO3\CMS\Backend\Template\DocumentTemplate;

/**
 * Test case.
 *
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_OrganizersListTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_BackEnd_OrganizersList
     */
    private $fixture;
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var int PID of a dummy system folder
     */
    private $dummySysFolderPid = 0;

    /**
     * @var Tx_Seminars_BackEnd_Module a dummy BE module
     */
    private $backEndModule;

    /**
     * @var string the original language of the back-end module
     */
    private $originalLanguage;

    protected function setUp()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);

        // Sets the localization to the default language so that all tests can
        // run even if the BE user has its interface set to another language.
        $this->originalLanguage = $GLOBALS['LANG']->lang;
        $GLOBALS['LANG']->lang = 'default';

        // Loads the locallang file for properly working localization in the tests.
        $GLOBALS['LANG']->includeLLFile('EXT:seminars/Resources/Private/Language/BackEnd/locallang.xlf');

        $this->testingFramework
            = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->dummySysFolderPid
            = $this->testingFramework->createSystemFolder();

        $this->backEndModule = new Tx_Seminars_BackEnd_Module();
        $this->backEndModule->id = $this->dummySysFolderPid;
        $this->backEndModule->setPageData([
            'uid' => $this->dummySysFolderPid,
            'doktype' => Tx_Seminars_BackEnd_AbstractList::SYSFOLDER_TYPE,
        ]);

        $document = new DocumentTemplate();
        $this->backEndModule->doc = $document;
        $document->backPath = $GLOBALS['BACK_PATH'];
        $document->docType = 'xhtml_strict';

        $this->fixture = new Tx_Seminars_BackEnd_OrganizersList(
            $this->backEndModule
        );
    }

    protected function tearDown()
    {
        // Resets the language of the interface to the value it had before
        // we set it to "default" for testing.
        $GLOBALS['LANG']->lang = $this->originalLanguage;

        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function showContainsOrganizerFromSubfolder()
    {
        $subfolderPid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'Organizer in subfolder',
                'pid' => $subfolderPid,
            ]
        );

        self::assertContains(
            'Organizer in subfolder',
            $this->fixture->show()
        );
    }

    //////////////////////////////////////
    // Tests concerning the "new" button
    //////////////////////////////////////

    public function testNewButtonForOrganizerStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        $newOrganizerFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_BackEndUserGroup::class
        )->getLoadedTestingModel(
            ['tx_seminars_auxiliaries_folder' => $newOrganizerFolder]
        );
        $backEndUser = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_BackEndUser::class
        )->getLoadedTestingModel(
                ['usergroup' => $backEndGroup->getUid()]
        );
        Tx_Oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
            $backEndUser
        );

        self::assertContains(
            'edit[tx_seminars_organizers][' . $newOrganizerFolder . ']=new',
            $this->fixture->show()
        );
    }
}
