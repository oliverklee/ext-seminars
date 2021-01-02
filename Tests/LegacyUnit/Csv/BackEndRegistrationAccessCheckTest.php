<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Csv\BackEndRegistrationAccessCheck;
use OliverKlee\Seminars\Csv\Interfaces\CsvAccessCheck;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndRegistrationAccessCheckTest extends TestCase
{
    /**
     * @var BackEndRegistrationAccessCheck
     */
    private $subject = null;

    /**
     * @var MockObject|BackendUserAuthentication
     */
    private $backEndUser = null;

    /**
     * @var BackendUserAuthentication
     */
    private $backEndUserBackup = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        $this->backEndUser = $this->createMock(BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $this->backEndUser;

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new BackEndRegistrationAccessCheck();
    }

    protected function tearDown()
    {
        \Tx_Oelib_BackEndLoginManager::purgeInstance();

        $this->testingFramework->cleanUp();
        $GLOBALS['BE_USER'] = $this->backEndUserBackup;
    }

    /**
     * @test
     */
    public function subjectImplementsAccessCheck()
    {
        self::assertInstanceOf(
            CsvAccessCheck::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoBackEndUserReturnsFalse()
    {
        unset($GLOBALS['BE_USER']);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoAccessToEventsTableAndNoAccessToRegistrationsTableReturnsFalse()
    {
        $this->backEndUser->expects(self::at(0))->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(false);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoAccessToEventsTableAndAccessToRegistrationsTableReturnsFalse()
    {
        $this->backEndUser->expects(self::at(0))->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(false);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToEventsTableAndNoAccessToRegistrationsTableReturnsFalse()
    {
        $this->backEndUser->expects(self::at(0))->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(true);
        $this->backEndUser->expects(self::at(1))->method('check')
            ->with('tables_select', 'tx_seminars_attendances')
            ->willReturn(false);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToEventsTableAndAccessToRegistrationsTableReturnsTrue()
    {
        $this->backEndUser->expects(self::at(0))->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(true);
        $this->backEndUser->expects(self::at(1))->method('check')
            ->with('tables_select', 'tx_seminars_attendances')
            ->willReturn(true);

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToEventsTableAndAccessToRegistrationsTableAndAccessToSetPageReturnsTrue()
    {
        $this->backEndUser->method('check')
            ->with('tables_select', self::anything())
            ->willReturn(true);

        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);
        $pageRecord = BackendUtility::getRecord('pages', $pageUid);
        $this->backEndUser->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->willReturn(true);

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToEventsTableAndAccessToRegistrationsTableAndNoAccessToSetPageReturnsFalse()
    {
        $this->backEndUser->method('check')
            ->with('tables_select', self::anything())
            ->willReturn(true);

        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);
        $pageRecord = BackendUtility::getRecord('pages', $pageUid);
        $this->backEndUser->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->willReturn(false);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }
}
