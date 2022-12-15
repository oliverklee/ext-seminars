<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\EventsList;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Mapper\BackEndUserGroupMapper;
use OliverKlee\Seminars\Mapper\BackEndUserMapper;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Information\Typo3Version;

final class EventsListTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var EventsList
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var int PID of a dummy system folder
     */
    private $dummySysFolderPid;

    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

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

        $backEndGroup = MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['tx_seminars_events_folder' => $this->dummySysFolderPid + 1]);
        $backEndUser = MapperRegistry::get(BackEndUserMapper::class)
            ->getLoadedTestingModel(['usergroup' => $backEndGroup->getUid()]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);
    }

    protected function tearDown(): void
    {
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUp();
        }
        $this->restoreOriginalEnvironment();
    }

    /////////////////////////////////////////
    // Tests for the events list functions.
    /////////////////////////////////////////

    /**
     * @test
     */
    public function showContainsNoBodyHeaderWithEmptySystemFolder(): void
    {
        self::assertStringNotContainsString(
            '<td class="datecol">',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showContainsTableBodyHeaderForOneEvent(): void
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
    public function showContainsNoBodyHeaderIfEventIsOnOtherPage(): void
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
    public function showContainsEventTitleForOneEvent(): void
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
    public function showContainsEventTitleForTwoEvents(): void
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
    public function showContainsEventTitleForOneHiddenEvent(): void
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
    public function showContainsEventTitleForOneTimedEvent(): void
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
    public function showForOneEventContainsAccreditationNumber(): void
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
    public function showForOneEventContainsHtmlSpecialCharedAccreditationNumber(): void
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
    public function showContainsCanceledStatusIconForCanceledEvent(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => EventInterface::STATUS_CANCELED,
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
    public function showContainsConfirmedStatusIconForConfirmedEvent(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => EventInterface::STATUS_CONFIRMED,
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
    public function showDoesNotContainCanceledOrConfirmedStatusIconForPlannedEvent(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => EventInterface::STATUS_PLANNED,
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
    public function showForEventWithRegistrationsContainsEmailButton(): void
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
    public function showDoesNotContainConfirmButtonForEventThatIsAlreadyConfirmed(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => EventInterface::STATUS_CONFIRMED,
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
    public function showDoesNotContainConfirmButtonForPlannedEventThatHasAlreadyBegun(): void
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
    public function showContainsConfirmButtonForPlannedEventThatHasNotStartedYet(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => EventInterface::STATUS_PLANNED,
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
    public function showContainsConfirmButtonForCanceledEventThatHasNotStartedYet(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => EventInterface::STATUS_CANCELED,
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
    public function showDoesNotContainConfirmButtonForTopicRecords(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'object_type' => EventInterface::TYPE_EVENT_TOPIC,
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
    public function showContainsConfirmButtonWithVariableEventUidInHiddenField(): void
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
    public function showDoesNotContainConfirmButtonForHiddenEvent(): void
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
    public function showDoesNotContainCancelButtonForAlreadyCanceledEvent(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'cancelled' => EventInterface::STATUS_CANCELED,
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
    public function showDoesNotContainCancelButtonPlannedEventThatHasAlreadyBegun(): void
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
    public function showContainsCancelButtonForPlannedEventThatHasNotStartedYet(): void
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
    public function showContainsCancelButtonForConfirmedEventThatHasNotStartedYet(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'cancelled' => EventInterface::STATUS_CONFIRMED,
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
    public function showDoesNotContainCancelButtonForTopicRecords(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
                'object_type' => EventInterface::TYPE_EVENT_TOPIC,
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
    public function showContainsCancelButtonWithVariableEventUidInHiddenField(): void
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
    public function showDoesNotContainCancelButtonForHiddenEvent(): void
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
    public function showContainsEventFromSubfolder(): void
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
    public function showForEventWithRegistrationHasShowLink(): void
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
            $this->translate('label_show_event_registrations'),
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function showLinkLinksToRegistrationsTab(): void
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
    public function showLinkLinksToTheEvent(): void
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
    public function showForHiddenEventWithRegistrationDoesNotHaveShowLink(): void
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
            $this->translate('label_show_event_registrations'),
            $this->subject->show()
        );
    }

    /////////////////////////
    // Tests for the icons.
    /////////////////////////

    /**
     * @test
     */
    public function hasEventIcon(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->dummySysFolderPid,
                'title' => 'event_1',
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function localizationReturnsLocalizedStringForExistingKey(): void
    {
        self::assertSame('Events', $this->translate('title'));
    }

    ///////////////////////////////////////////
    // Tests concerning the new record button
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function eventListCanContainNewButton(): void
    {
        self::assertStringContainsString(
            'newRecordLink',
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function newButtonForNoEventStorageSettingInUserGroupsSetsCurrentPageIdAsNewRecordPid(): void
    {
        $backEndUser = MapperRegistry::get(BackEndUserMapper::class)->getLoadedTestingModel([]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString((string)$this->dummySysFolderPid, $this->subject->show());
    }

    /**
     * @test
     */
    public function newButtonForEventStoredOnCurrentPageHasCurrentFolderLabel(): void
    {
        $backEndUser = MapperRegistry::get(BackEndUserMapper::class)->getLoadedTestingModel([]);
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertStringContainsString(
            \sprintf(
                $this->translate('label_create_record_in_current_folder'),
                '',
                $this->dummySysFolderPid
            ),
            $this->subject->show()
        );
    }

    /**
     * @test
     */
    public function newButtonForEventStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid(): void
    {
        $userUid = BackEndLoginManager::getInstance()->getLoggedInUserUid();
        $loggedInUser = MapperRegistry::get(BackEndUserMapper::class)->find($userUid);
        $newEventFolder = $loggedInUser->getEventFolderFromGroup();

        self::assertStringContainsString((string)$newEventFolder, $this->subject->show());
    }

    /**
     * @test
     */
    public function newButtonForEventStoredInPageDeterminedByGroupHasForeignFolderLabel(): void
    {
        $userUid = BackEndLoginManager::getInstance()->getLoggedInUserUid();
        $loggedInUser = MapperRegistry::get(BackEndUserMapper::class)->find($userUid);
        $newEventFolder = $loggedInUser->getEventFolderFromGroup();

        self::assertStringContainsString(
            \sprintf(
                $this->translate('label_create_record_in_foreign_folder'),
                '',
                $newEventFolder
            ),
            $this->subject->show()
        );
    }
}
