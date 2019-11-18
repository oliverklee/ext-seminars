<?php

declare(strict_types=1);

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\RegistrationsList;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_RegistrationsListTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var RegistrationsList
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int PID of a dummy system folder
     */
    private $dummySysFolderPid = 0;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $backEndModule = new DummyModule();
        $backEndModule->id = $this->dummySysFolderPid;
        $backEndModule->setPageData(
            [
                'uid' => $this->dummySysFolderPid,
                'doktype' => AbstractList::SYSFOLDER_TYPE,
            ]
        );

        $backEndModule->doc = new DocumentTemplate();

        $this->subject = new RegistrationsList($backEndModule);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        \Tx_Seminars_OldModel_Registration::purgeCachedSeminars();
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
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
            $this->subject->show()
        );
    }

    //////////////////////////////////////
    // Tests concerning the "new" button
    //////////////////////////////////////

    public function testNewButtonForRegistrationStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        $newRegistrationFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_BackEndUserGroup::class
        )->getLoadedTestingModel(
            ['tx_seminars_registrations_folder' => $newRegistrationFolder]
        );
        /** @var \Tx_Seminars_Model_BackEndUser $backEndUser */
        $backEndUser = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)
            ->getLoadedTestingModel(['usergroup' => $backEndGroup->getUid()]);
        \Tx_Oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertContains(
            'edit[tx_seminars_attendances][' . $newRegistrationFolder . ']=new',
            $this->subject->show()
        );
    }
}
