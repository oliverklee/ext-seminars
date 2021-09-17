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

final class EventsListTest extends TestCase
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

    /**
     * @test
     */
    public function showContainsNoBodyHeaderWithEmptySystemFolder()
    {
        self::assertStringNotContainsString(
            '<td class="datecol">',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showContainsTableBodyHeaderForOneEvent()
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

    /**
     * @test
     */
    public function showContainsNoBodyHeaderIfEventIsOnOtherPage()
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

    /**
     * @test
     */
    public function showContainsEventTitleForOneEvent()
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

    /**
     * @test
     */
    public function showContainsEventTitleForTwoEvents()
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

    /**
     * @test
     */
    public function showContainsEventTitleForOneHiddenEvent()
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

    /**
     * @test
     */
    public function showContainsEventTitleForOneTimedEvent()
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

    /**
     * @test
     */
    public function showForOneEventContainsAccreditationNumber()
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

    /**
     * @test
     */
    public function showForOneEventContainsHtmlSpecialCharedAccreditationNumber()
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

    /**
     * @test
     */
    public function showContainsCanceledStatusIconForCanceledEvent()
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

    /**
     * @test
     */
    public function showContainsConfirmedStatusIconForConfirmedEvent()
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

    /**
     * @test
     */
    public function showDoesNotContainCanceledOrConfirmedStatusIconForPlannedEvent()
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

    /**
     * @test
     */
    public function showDoesNotContainConfirmButtonForEventThatIsAlreadyConfirmed()
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

    /**
     * @test
     */
    public function showDoesNotContainConfirmButtonForPlannedEventThatHasAlreadyBegun()
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

    /**
     * @test
     */
    public function showContainsConfirmButtonForPlannedEventThatHasNotStartedYet()
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

    /**
     * @test
     */
    public function showContainsConfirmButtonForCanceledEventThatHasNotStartedYet()
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

    /**
     * @test
     */
    public function showDoesNotContainConfirmButtonForTopicRecords()
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

    /**
     * @test
     */
    public function showContainsConfirmButtonWithVariableEventUidInHiddenField()
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

    /**
     * @test
     */
    public function showDoesNotContainCancelButtonForAlreadyCanceledEvent()
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

    /**
     * @test
     */
    public function showDoesNotContainCancelButtonPlannedEventThatHasAlreadyBegun()
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

    /**
     * @test
     */
    public function showContainsCancelButtonForPlannedEventThatHasNotStartedYet()
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

    /**
     * @test
     */
    public function showContainsCancelButtonForConfirmedEventThatHasNotStartedYet()
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

    /**
     * @test
     */
    public function showDoesNotContainCancelButtonForTopicRecords()
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

    /**
     * @test
     */
    public function showContainsCancelButtonWithVariableEventUidInHiddenField()
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

    /**
     * @test
     */
    public function showForEventWithRegistrationHasShowLink()
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

    /**
     * @test
     */
    public function showLinkLinksToRegistrationsTab()
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

    /**
     * @test
     */
    public function showLinkLinksToTheEvent()
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

    /**
     * @test
     */
    public function showForHiddenEventWithRegistrationDoesNotHaveShowLink()
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

    /**
     * @test
     */
    public function hasEventIcon()
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

    /**
     * @test
     */
    public function localizationReturnsLocalizedStringForExistingKey()
    {
        self::assertSame('Events', $this->getLanguageService()->getLL('title'));
    }

    ///////////////////////////////////////////
    // Tests concerning the new record button
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function eventListCanContainNewButton()
    {
        self::assertStringContainsString(
            'newRecordLink',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function newButtonForNoEventStorageSettingInUserGroupsSetsCurrentPageIdAsNewRecordPid()
    {
        $backEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUser::class)->getLoadedTestingModel([]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString((string)$this->dummySysFolderPid, $this->subject->show());
    }

    /**
     * @test
     */
    public function newButtonForEventStoredOnCurrentPageHasCurrentFolderLabel()
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

    /**
     * @test
     */
    public function newButtonForEventStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $loggedInUser */
        $loggedInUser = BackEndLoginManager::getInstance()
            ->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $newEventFolder = $loggedInUser->getEventFolderFromGroup();

        self::assertStringContainsString((string)$newEventFolder, $this->subject->show());
    }

    /**
     * @test
     */
    public function newButtonForEventStoredInPageDeterminedByGroupHasForeignFolderLabel()
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
