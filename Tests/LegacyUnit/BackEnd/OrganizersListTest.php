<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\OrganizersList;
use OliverKlee\Seminars\Mapper\BackEndUserGroupMapper;
use OliverKlee\Seminars\Mapper\BackEndUserMapper;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

final class OrganizersListTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var OrganizersList
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var int PID of a dummy system folder
     */
    private $dummySysFolderPid = 0;

    protected function setUp(): void
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

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
        $this->restoreOriginalEnvironment();
    }

    /**
     * @test
     */
    public function showContainsOrganizerFromSubfolder(): void
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

    /**
     * @test
     */
    public function newButtonForOrganizerStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid(): void
    {
        $newOrganizerFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['tx_seminars_auxiliaries_folder' => $newOrganizerFolder]);
        $backEndUser = MapperRegistry::get(BackEndUserMapper::class)
            ->getLoadedTestingModel(['usergroup' => $backEndGroup->getUid()]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString((string)$newOrganizerFolder, $this->subject->show());
    }
}
