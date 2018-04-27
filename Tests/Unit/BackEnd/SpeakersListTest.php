<?php

use OliverKlee\Seminars\Tests\Unit\BackeEnd\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_SpeakersListTest extends Tx_Phpunit_TestCase
{
    use BackEndTestsTrait;

    /**
     * @var Tx_Seminars_BackEnd_SpeakersList
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

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->dummySysFolderPid = $this->testingFramework->createSystemFolder();

        $this->backEndModule = new Tx_Seminars_BackEnd_Module();
        $this->backEndModule->id = $this->dummySysFolderPid;
        $this->backEndModule->setPageData([
            'uid' => $this->dummySysFolderPid,
            'doktype' => Tx_Seminars_BackEnd_AbstractList::SYSFOLDER_TYPE,
        ]);

        $document = new DocumentTemplate();
        $this->backEndModule->doc = $document;
        $document->backPath = $GLOBALS['BACK_PATH'];

        $this->fixture = new Tx_Seminars_BackEnd_SpeakersList(
            $this->backEndModule
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $this->restoreOriginalEnvironment();
    }

    /**
     * @test
     */
    public function showContainsHideButtonForVisibleSpeaker()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'pid' => $this->dummySysFolderPid,
                'hidden' => 0,
            ]
        );

        self::assertContains(
            'Icons/Hide.gif',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showContainsUnhideButtonForHiddenSpeaker()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'pid' => $this->dummySysFolderPid,
                'hidden' => 1,
            ]
        );

        self::assertContains(
            'Icons/Unhide.gif',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showContainsSpeakerFromSubfolder()
    {
        $subfolderPid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'Speaker in subfolder',
                'pid' => $subfolderPid,
            ]
        );

        self::assertContains(
            'Speaker in subfolder',
            $this->fixture->show()
        );
    }

    //////////////////////////////////////
    // Tests concerning the "new" button
    //////////////////////////////////////

    public function testNewButtonForSpeakerStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        $newSpeakerFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_BackEndUserGroup::class
        )->getLoadedTestingModel(
            ['tx_seminars_auxiliaries_folder' => $newSpeakerFolder]
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
            'edit[tx_seminars_speakers][' . $newSpeakerFolder . ']=new',
            $this->fixture->show()
        );
    }
}
