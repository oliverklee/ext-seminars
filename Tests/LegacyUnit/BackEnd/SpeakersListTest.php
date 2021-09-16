<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\SpeakersList;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

final class SpeakersListTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var SpeakersList
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

        $this->subject = new SpeakersList($backEndModule);
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

        self::assertStringContainsString(
            'Icons/Hide.gif',
            $this->subject->show()
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

        self::assertStringContainsString(
            'Icons/Unhide.gif',
            $this->subject->show()
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

        self::assertStringContainsString(
            'Speaker in subfolder',
            $this->subject->show()
        );
    }

    //////////////////////////////////////
    // Tests concerning the "new" button
    //////////////////////////////////////

    public function testNewButtonForSpeakerStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        $newSpeakerFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)
            ->getLoadedTestingModel(['tx_seminars_auxiliaries_folder' => $newSpeakerFolder]);
        $backEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)
            ->getLoadedTestingModel(['usergroup' => $backEndGroup->getUid()]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString((string)$newSpeakerFolder, $this->subject->show());
    }
}
