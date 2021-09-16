<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\EventsList;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

class EventsListTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var EventsList
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

        $this->subject = new EventsList($backEndModule);

        $backEndGroup = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)
            ->getLoadedTestingModel(['tx_seminars_events_folder' => $this->dummySysFolderPid + 1]);
        $backEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)
            ->getLoadedTestingModel(['usergroup' => $backEndGroup->getUid()]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $this->restoreOriginalEnvironment();
    }

    /////////////////////////////////////////
    // Tests for the events list functions.
    /////////////////////////////////////////

    public function testShowContainsNoBodyHeaderWithEmptySystemFolder()
    {
        self::assertStringNotContainsString(
            '<td class="datecol">',
            $this->subject->show()
        );
    }

    public function testShowContainsTableBodyHeaderForOneEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid]
        );

        self::assertStringContainsString(
            '<td class="datecol">',
            $this->subject->show()
        );
    }

    public function testShowContainsNoBodyHeaderIfEventIsOnOtherPage()
    {
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid + 1]
        );

        self::assertStringNotContainsString(
            '<td class="datecol">',
            $this->subject->show()
        );
    }

    public function testShowContainsEventTitleForOneEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
            ]
        );

        self::assertStringContainsString(
            'event_1',
            $this->subject->show()
        );
    }

    public function testShowContainsEventTitleForTwoEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_2',
            ]
        );

        self::assertStringContainsString(
            'event_1',
            $this->subject->show()
        );
        self::assertStringContainsString(
            'event_2',
            $this->subject->show()
        );
    }

    public function testShowContainsEventTitleForOneHiddenEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'hidden' => 1,
            ]
        );

        self::assertStringContainsString(
            'event_1',
            $this->subject->show()
        );
    }

    public function testShowContainsEventTitleForOneTimedEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
            ]
        );

        self::assertStringContainsString(
            'event_1',
            $this->subject->show()
        );
    }

    public function testShowForOneEventContainsAccreditationNumber()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'accreditation_number' => 'accreditation number 123',
            ]
        );

        self::assertStringContainsString(
            'accreditation number 123',
            $this->subject->show()
        );
    }

    public function testShowForOneEventContainsHtmlSpecialCharedAccreditationNumber()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'accreditation_number' => '&"<>',
            ]
        );

        self::assertStringContainsString(
            '&amp;&quot;&lt;&gt;',
            $this->subject->show()
        );
    }

    public function testShowContainsCanceledStatusIconForCanceledEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED,
            ]
        );

        self::assertStringContainsString(
            '<img src="/typo3conf/ext/seminars/Resources/Public/Icons/Canceled.png" title="canceled" alt="canceled"/>',
            $this->subject->show()
        );
    }

    public function testShowContainsConfirmedStatusIconForConfirmedEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        self::assertStringContainsString(
            '<img src="/typo3conf/ext/seminars/Resources/Public/Icons/Confirmed.png" title="confirmed" alt="confirmed"/>',
            $this->subject->show()
        );
    }

    public function testShowDoesNotContainCanceledOrConfirmedStatusIconForPlannedEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
            ]
        );

        self::assertStringNotContainsString(
            '<img src="/typo3conf/ext/seminars/Resources/Public/Icons/Canceled.png" title="canceled" alt="canceled"/>',
            $this->subject->show()
        );

        self::assertStringNotContainsString(
            '<img src="/typo3conf/ext/seminars/Resources/Public/Icons/Confirmed.png" title="confirmed" alt="confirmed"/>',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showForEventWithRegistrationsContainsEmailButton()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'registrations' => 1,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $eventUid,
            ]
        );

        self::assertStringContainsString(
            '<button><p>E-mail</p></button>',
            $this->subject->show()
        );
    }

    public function testShowDoesNotContainConfirmButtonForEventThatIsAlreadyConfirmed()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Confirm</p></button>',
            $this->subject->show()
        );
    }

    public function testShowDoesNotContainConfirmButtonForPlannedEventThatHasAlreadyBegun()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 42,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Confirm</p></button>',
            $this->subject->show()
        );
    }

    public function testShowContainsConfirmButtonForPlannedEventThatHasNotStartedYet()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringContainsString(
            '<button><p>Confirm</p></button>',
            $this->subject->show()
        );
    }

    public function testShowContainsConfirmButtonForCanceledEventThatHasNotStartedYet()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringContainsString(
            '<button><p>Confirm</p></button>',
            $this->subject->show()
        );
    }

    public function testShowDoesNotContainConfirmButtonForTopicRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Confirm</p></button>',
            $this->subject->show()
        );
    }

    public function testShowContainsConfirmButtonWithVariableEventUidInHiddenField()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringContainsString(
            '<button><p>Confirm</p></button>' .
            '<input type="hidden" name="eventUid" value="' . $uid . '" />',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showDoesNotContainConfirmButtonForHiddenEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'hidden' => 1,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Confirm</p></button>',
            $this->subject->show()
        );
    }

    public function testShowDoesNotContainCancelButtonForAlreadyCanceledEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Cancel</p></button>',
            $this->subject->show()
        );
    }

    public function testShowDoesNotContainCancelButtonPlannedEventThatHasAlreadyBegun()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 42,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Cancel</p></button>',
            $this->subject->show()
        );
    }

    public function testShowContainsCancelButtonForPlannedEventThatHasNotStartedYet()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringContainsString(
            '<button><p>Cancel</p></button>',
            $this->subject->show()
        );
    }

    public function testShowContainsCancelButtonForConfirmedEventThatHasNotStartedYet()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        self::assertStringContainsString(
            '<button><p>Cancel</p></button>',
            $this->subject->show()
        );
    }

    public function testShowDoesNotContainCancelButtonForTopicRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Cancel</p></button>',
            $this->subject->show()
        );
    }

    public function testShowContainsCancelButtonWithVariableEventUidInHiddenField()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
            ]
        );

        self::assertStringContainsString(
            '<button><p>Cancel</p></button>' .
            '<input type="hidden" name="eventUid" value="' . $uid . '" />',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showDoesNotContainCancelButtonForHiddenEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'hidden' => 1,
            ]
        );

        self::assertStringNotContainsString(
            '<button><p>Cancel</p></button>',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showContainsCsvExportButtonForEventWithRegistration()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'needs_registration' => 1,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $eventUid,
            ]
        );

        self::assertStringContainsString(
            '=' . $eventUid,
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showDoesNotContainCsvExportButtonForHiddenEventWithRegistration()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'hidden' => 1,
                'needs_registration' => 1,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'pid' => $this->dummySysFolderPid,
                'seminar' => $eventUid,
            ]
        );

        self::assertStringNotContainsString(
            '<a href="mod.php?M=web_txseminarsM2&amp;csv=1&amp;id=' .
            $this->dummySysFolderPid .
            '&amp;tx_seminars_pi2[table]=tx_seminars_attendances' .
            '&amp;tx_seminars_pi2[eventUid]=' . $eventUid . '">',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showContainsEventFromSubfolder()
    {
        $subfolderPid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'Event in subfolder',
                'pid' => $subfolderPid,
            ]
        );

        self::assertStringContainsString(
            'Event in subfolder',
            $this->subject->show()
        );
    }

    public function testShowForEventWithRegistrationHasShowLink()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid, 'needs_registration' => 1]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['pid' => $this->dummySysFolderPid, 'seminar' => $eventUid]
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_show_event_registrations'),
            $this->subject->show()
        );
    }

    public function testShowLinkLinksToRegistrationsTab()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid, 'needs_registration' => 1]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['pid' => $this->dummySysFolderPid, 'seminar' => $eventUid]
        );

        self::assertStringContainsString(
            '&amp;subModule=2',
            $this->subject->show()
        );
    }

    public function testShowLinkLinksToTheEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['pid' => $this->dummySysFolderPid, 'needs_registration' => 1]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['pid' => $this->dummySysFolderPid, 'seminar' => $eventUid]
        );

        self::assertStringContainsString(
            '&amp;eventUid=' . $eventUid,
            $this->subject->show()
        );
    }

    public function testShowForHiddenEventWithRegistrationDoesNotHaveShowLink()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'needs_registration' => 1,
                'hidden' => 1,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['pid' => $this->dummySysFolderPid, 'seminar' => $eventUid]
        );

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_show_event_registrations'),
            $this->subject->show()
        );
    }

    /////////////////////////
    // Tests for the icons.
    /////////////////////////

    public function testHasEventIcon()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );

        self::assertStringContainsString(
            'EventComplete.gif',
            $this->subject->show()
        );
    }

    ////////////////////////////////
    // Tests for the localization.
    ////////////////////////////////

    public function testLocalizationReturnsLocalizedStringForExistingKey()
    {
        self::assertSame('Events', $this->getLanguageService()->getLL('title'));
    }

    ///////////////////////////////////////////
    // Tests concerning the new record button
    ///////////////////////////////////////////

    public function testEventListCanContainNewButton()
    {
        self::assertStringContainsString(
            'newRecordLink',
            $this->subject->show()
        );
    }

    public function testNewButtonForNoEventStorageSettingInUserGroupsSetsCurrentPageIdAsNewRecordPid()
    {
        $backEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)->getLoadedTestingModel([]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString((string)$this->dummySysFolderPid, $this->subject->show());
    }

    public function testNewButtonForEventStoredOnCurrentPageHasCurrentFolderLabel()
    {
        $backEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)->getLoadedTestingModel([]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString(
            \sprintf(
                $this->getLanguageService()->getLL('label_create_record_in_current_folder'),
                '',
                $this->dummySysFolderPid
            ),
            $this->subject->show()
        );
    }

    public function testNewButtonForEventStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $loggedInUser */
        $loggedInUser = BackEndLoginManager::getInstance()
            ->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $newEventFolder = $loggedInUser->getEventFolderFromGroup();

        self::assertStringContainsString((string)$newEventFolder, $this->subject->show());
    }

    public function testNewButtonForEventStoredInPageDeterminedByGroupHasForeignFolderLabel()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $loggedInUser */
        $loggedInUser = BackEndLoginManager::getInstance()
            ->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $newEventFolder = $loggedInUser->getEventFolderFromGroup();

        self::assertStringContainsString(
            \sprintf(
                $this->getLanguageService()->getLL('label_create_record_in_foreign_folder'),
                '',
                $newEventFolder
            ),
            $this->subject->show()
        );
    }
}
