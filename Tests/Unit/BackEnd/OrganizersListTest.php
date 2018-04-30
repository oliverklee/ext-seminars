<?php

use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\Module;
use OliverKlee\Seminars\BackEnd\OrganizersList;
use OliverKlee\Seminars\Tests\Unit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_OrganizersListTest extends \Tx_Phpunit_TestCase
{
    use BackEndTestsTrait;

    /**
     * @var OrganizersList
     */
    private $fixture;
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var int PID of a dummy system folder
     */
    private $dummySysFolderPid = 0;

    /**
     * @var Module a dummy BE module
     */
    private $backEndModule;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('This test is for the old BE module only.');
        }

        $this->dummySysFolderPid = $this->testingFramework->createSystemFolder();

        $this->backEndModule = new Module();
        $this->backEndModule->id = $this->dummySysFolderPid;
        $this->backEndModule->setPageData([
            'uid' => $this->dummySysFolderPid,
            'doktype' => AbstractList::SYSFOLDER_TYPE,
        ]);

        $document = new DocumentTemplate();
        $this->backEndModule->doc = $document;

        $this->fixture = new OrganizersList($this->backEndModule);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $this->restoreOriginalEnvironment();
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
        $backEndGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_BackEndUserGroup::class
        )->getLoadedTestingModel(
            ['tx_seminars_auxiliaries_folder' => $newOrganizerFolder]
        );
        $backEndUser = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_BackEndUser::class
        )->getLoadedTestingModel(
                ['usergroup' => $backEndGroup->getUid()]
        );
        \Tx_Oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
            $backEndUser
        );

        self::assertContains(
            'edit[tx_seminars_organizers][' . $newOrganizerFolder . ']=new',
            $this->fixture->show()
        );
    }
}
