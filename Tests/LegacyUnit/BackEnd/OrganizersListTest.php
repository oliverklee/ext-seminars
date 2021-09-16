<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\OrganizersList;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

class OrganizersListTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var OrganizersList
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int PID of a dummy system folder
     */
    private $dummySysFolderPid = 0;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->dummySysFolderPid = $this->testingFramework->createSystemFolder();

        $backEndModule = new DummyModule();
        $backEndModule->id = $this->dummySysFolderPid;
        $backEndModule->setPageData(
            [
                'uid' => $this->dummySysFolderPid,
                'doktype' => AbstractList::SYSFOLDER_TYPE,
            ]
        );

        $backEndModule->doc = new DocumentTemplate();

        $this->subject = new OrganizersList($backEndModule);
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

        self::assertStringContainsString(
            'Organizer in subfolder',
            $this->subject->show()
        );
    }

    //////////////////////////////////////
    // Tests concerning the "new" button
    //////////////////////////////////////

    public function testNewButtonForOrganizerStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        $newOrganizerFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)
            ->getLoadedTestingModel(['tx_seminars_auxiliaries_folder' => $newOrganizerFolder]);
        $backEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)
            ->getLoadedTestingModel(['usergroup' => $backEndGroup->getUid()]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString((string)$newOrganizerFolder, $this->subject->show());
    }
}
