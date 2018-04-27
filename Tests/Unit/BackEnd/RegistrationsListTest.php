<?php

use OliverKlee\Seminars\Tests\Unit\BackeEnd\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_RegistrationsListTest extends Tx_Phpunit_TestCase
{
    use BackEndTestsTrait;

    /**
     * @var Tx_Seminars_BackEnd_RegistrationsList
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
     * @var Tx_Seminars_BackEnd_Module a dummy back-end module
     */
    private $backEndModule;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->backEndModule = new Tx_Seminars_BackEnd_Module();
        $this->backEndModule->id = $this->dummySysFolderPid;
        $this->backEndModule->setPageData([
            'uid' => $this->dummySysFolderPid,
            'doktype' => Tx_Seminars_BackEnd_AbstractList::SYSFOLDER_TYPE,
        ]);

        $document = new DocumentTemplate();
        $this->backEndModule->doc = $document;
        $document->backPath = $GLOBALS['BACK_PATH'];

        $this->fixture = new Tx_Seminars_BackEnd_RegistrationsList(
            $this->backEndModule
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        Tx_Seminars_OldModel_Registration::purgeCachedSeminars();
        $this->restoreOriginalEnvironment();
    }

    ////////////////////////////////////////////////
    // Tests for the registrations list functions.
    ////////////////////////////////////////////////

    public function testShowForOneEventContainsAccreditationNumber()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'accreditation_number' => 'accreditation number 123',
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
            ]
        );

        self::assertContains(
            'accreditation number 123',
            $this->fixture->show()
        );
    }

    public function testShowForOneEventContainsHtmlSpecialCharedAccreditationNumber()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'accreditation_number' => '&"<>',
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
            ]
        );

        self::assertContains(
            '&amp;&quot;&lt;&gt;',
            $this->fixture->show()
        );
    }

    public function testShowShowsUserName()
    {
        $userUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'foo_user']
        );
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
                'user' => $userUid,
            ]
        );

        self::assertContains(
            'foo_user',
            $this->fixture->show()
        );
    }

    public function testShowWithRegistrationForDeletedUserDoesNotShowUserName()
    {
        $userUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'foo_user', 'deleted' => 1]
        );
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
                'user' => $userUid,
            ]
        );

        self::assertNotContains(
            'foo_user',
            $this->fixture->show()
        );
    }

    public function testShowWithRegistrationForInexistentUserDoesNotShowUserName()
    {
        $userUid = $this->testingFramework->getAutoIncrement('fe_users');
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
                'user' => $userUid,
            ]
        );

        self::assertNotContains(
            'foo_user',
            $this->fixture->show()
        );
    }

    public function testShowForOneEventContainsEventTitle()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
            ]
        );

        self::assertContains(
            'event_1',
            $this->fixture->show()
        );
    }

    public function testShowForOneDeletedEventDoesNotContainEventTitle()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'deleted' => 1,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
            ]
        );

        self::assertNotContains(
            'event_1',
            $this->fixture->show()
        );
    }

    public function testShowForOneInexistentEventShowsUserName()
    {
        $userUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'user_foo']
        );
        $seminarUid = $this->testingFramework->getAutoIncrement(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
                'user' => $userUid,
            ]
        );

        self::assertContains(
            'user_foo',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showContainsRegistrationFromSubfolder()
    {
        $subfolderPid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event for registration in subfolder',
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $subfolderPid,
                'seminar' => $seminarUid,
            ]
        );

        self::assertContains(
            'event for registration in subfolder',
            $this->fixture->show()
        );
    }

    public function testShowForNonEmptyRegularRegistrationsListContainsCsvExportButton()
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $seminarUid,
            ]
        );

        self::assertContains(
            'csv=1',
            $this->fixture->show()
        );
    }

    public function testShowForEmptyRegularRegistrationsListContainsCsvExportButton()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
            ]
        );

        self::assertNotContains(
            'mod.php?M=web_txseminarsM2&amp;csv=1',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showForEventUidSetShowsTitleOfThisEvent()
    {
        $_GET['eventUid'] = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
            ]
        );

        self::assertContains(
            'event_1',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showForEventUidSetShowsUidOfThisEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
            ]
        );
        $_GET['eventUid'] = $eventUid;

        self::assertContains(
            '(UID ' . $eventUid . ')',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showForEventUidSetShowsRegistrationOfThisEvent()
    {
        $userUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'user_foo']
        );
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $eventUid,
                'user' => $userUid,
            ]
        );

        $_GET['eventUid'] = $eventUid;

        self::assertContains(
            'user_foo',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showForEventUidSetDoesNotShowRegistrationOfAnotherEvent()
    {
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(
                    '',
                    ['name' => 'user_foo']
                ),
            ]
        );

        $_GET['eventUid'] = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        self::assertNotContains(
            'user_foo',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showForEventUidAddsEventUidToCsvExportIcon()
    {
        $userUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'user_foo']
        );
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $eventUid,
                'user' => $userUid,
            ]
        );

        $_GET['eventUid'] = $eventUid;

        self::assertContains(
            'tx_seminars_pi2[eventUid]=' . $eventUid,
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showForEventUidDoesNotAddPidToCsvExportIcon()
    {
        $userUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'user_foo']
        );
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $eventUid,
                'user' => $userUid,
            ]
        );

        $_GET['eventUid'] = $eventUid;

        self::assertNotContains(
            'tx_seminars_pi2[pid]=',
            $this->fixture->show()
        );
    }

    /**
     * @test
     */
    public function showForNoEventUidDoesNotAddEventUidToCsvExportIcon()
    {
        $userUid = $this->testingFramework->createFrontEndUser(
            '',
            ['name' => 'user_foo']
        );
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $eventUid,
                'user' => $userUid,
            ]
        );

        self::assertNotContains(
            'tx_seminars_pi2[eventUid]=',
            $this->fixture->show()
        );
    }

    //////////////////////////////////////
    // Tests concerning the "new" button
    //////////////////////////////////////

    public function testNewButtonForRegistrationStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        $newRegistrationFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_BackEndUserGroup::class
        )->getLoadedTestingModel(
            ['tx_seminars_registrations_folder' => $newRegistrationFolder]
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
            'edit[tx_seminars_attendances][' . $newRegistrationFolder . ']=new',
            $this->fixture->show()
        );
    }
}
