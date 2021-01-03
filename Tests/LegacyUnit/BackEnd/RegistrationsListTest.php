<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\RegistrationsList;
use OliverKlee\Seminars\Hooks\Interfaces\BackendRegistrationListView;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class RegistrationsListTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var RegistrationsList
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

    /**
     * @var string[]
     */
    private $mockedClassNames = [];

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->testingFramework = new TestingFramework('tx_seminars');

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
        $this->purgeMockedInstances();

        $this->testingFramework->cleanUp();
        \Tx_Seminars_OldModel_Registration::purgeCachedSeminars();
        $this->restoreOriginalEnvironment();
    }

    /*
     * Utility functions
     */

    /**
     * Adds an instance to the Typo3 instance FIFO buffer used by `GeneralUtility::makeInstance()`
     * and registers it for purging in `tearDown()`.
     *
     * In case of a failing test or an exception in the test before the instance is taken
     * from the FIFO buffer, the instance would stay in the buffer and make following tests
     * fail. This function adds it to the list of instances to purge in `tearDown()` in addition
     * to `GeneralUtility::addInstance()`.
     *
     * @param string $className
     * @param mixed $instance
     *
     * @return void
     */
    private function addMockedInstance(string $className, $instance)
    {
        GeneralUtility::addInstance($className, $instance);
        $this->mockedClassNames[] = $className;
    }

    /**
     * Purges possibly leftover instances from the Typo3 instance FIFO buffer used by
     * `GeneralUtility::makeInstance()`.
     *
     * @return void
     */
    private function purgeMockedInstances()
    {
        foreach ($this->mockedClassNames as $className) {
            GeneralUtility::makeInstance($className);
        }

        $this->mockedClassNames = [];
    }

    /*
     * Tests for the utility functions
     */

    /**
     * @test
     */
    public function mockedInstancesListIsEmptyInitially()
    {
        self::assertEmpty($this->mockedClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsClassnameToList()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);

        $this->addMockedInstance($mockedClassName, $mockedInstance);
        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertCount(1, $this->mockedClassNames);
        self::assertSame($mockedClassName, $this->mockedClassNames[0]);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsInstanceToTypo3InstanceBuffer()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesClassnameFromList()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();
        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested for that yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertEmpty($this->mockedClassNames);
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesInstanceFromTypo3InstanceBuffer()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();

        self::assertNotSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
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

    /**
     * @test
     */
    public function showForOneEventCallsBackEndRegistrationListViewHooks()
    {
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $eventUid,
            ]
        );

        $hook = $this->createMock(BackEndRegistrationListView::class);
        $hook->expects(self::once())->method('modifyListRow')->with(
            self::isInstanceOf(\Tx_Seminars_Model_Registration::class),
            self::isInstanceOf(Template::class),
            RegistrationsList::REGULAR_REGISTRATIONS
        );
        $hook->expects(self::exactly(2))->method('modifyListHeader')->withConsecutive(
            [
                self::isInstanceOf(\Tx_Seminars_Bag_Registration::class),
                self::isInstanceOf(Template::class),
                RegistrationsList::REGULAR_REGISTRATIONS,
            ],
            [
                self::isInstanceOf(\Tx_Seminars_Bag_Registration::class),
                self::isInstanceOf(Template::class),
                RegistrationsList::REGISTRATIONS_ON_QUEUE,
            ]
        );
        $hook->expects(self::exactly(2))->method('modifyList')->withConsecutive(
            [
                self::isInstanceOf(\Tx_Seminars_Bag_Registration::class),
                self::isInstanceOf(Template::class),
                RegistrationsList::REGULAR_REGISTRATIONS,
            ],
            [
                self::isInstanceOf(\Tx_Seminars_Bag_Registration::class),
                self::isInstanceOf(Template::class),
                RegistrationsList::REGISTRATIONS_ON_QUEUE,
            ]
        );

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][BackendRegistrationListView::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->show();
    }

    //////////////////////////////////////
    // Tests concerning the "new" button
    //////////////////////////////////////

    public function testNewButtonForRegistrationStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        $newRegistrationFolder = $this->dummySysFolderPid + 1;
        $backEndGroup = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)
            ->getLoadedTestingModel(['tx_seminars_registrations_folder' => $newRegistrationFolder]);
        /** @var \Tx_Seminars_Model_BackEndUser $backEndUser */
        $backEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)
            ->getLoadedTestingModel(['usergroup' => $backEndGroup->getUid()]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertContains((string)$newRegistrationFolder, $this->subject->show());
    }
}
