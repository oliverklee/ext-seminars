<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\BackEndUserGroupMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\EventsList;
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

        $backEndGroup = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
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
}
