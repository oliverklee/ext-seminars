<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Seminars\Csv\BackEndEventAccessCheck;
use OliverKlee\Seminars\Csv\Interfaces\CsvAccessCheck;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

final class BackEndEventAccessCheckTest extends TestCase
{
    /**
     * @var BackEndEventAccessCheck
     */
    private $subject = null;

    /**
     * @var BackendUserAuthentication&MockObject
     */
    private $backEndUser = null;

    /**
     * @var BackendUserAuthentication
     */
    private $backEndUserBackup = null;

    protected function setUp(): void
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        /** @var BackendUserAuthentication&MockObject $backEndUser */
        $backEndUser = $this->createMock(BackendUserAuthentication::class);
        $this->backEndUser = $backEndUser;
        $GLOBALS['BE_USER'] = $backEndUser;

        $this->subject = new BackEndEventAccessCheck();
    }

    protected function tearDown(): void
    {
        BackEndLoginManager::purgeInstance();
        $GLOBALS['BE_USER'] = $this->backEndUserBackup;
    }

    /**
     * @test
     */
    public function subjectImplementsAccessCheck(): void
    {
        self::assertInstanceOf(
            CsvAccessCheck::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoBackEndUserReturnsFalse(): void
    {
        unset($GLOBALS['BE_USER']);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoAccessToTableAndNoAccessToPageReturnsFalse(): void
    {
        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);

        $pageRecord = BackendUtility::getRecord('pages', $pageUid);

        $this->backEndUser->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(false);
        $this->backEndUser->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->willReturn(false);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoAccessToTableAndAccessToPageReturnsFalse(): void
    {
        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);

        $pageRecord = BackendUtility::getRecord('pages', $pageUid);

        $this->backEndUser->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(false);
        $this->backEndUser->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->willReturn(true);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToTableAndNoAccessToPageReturnsFalse(): void
    {
        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);

        $pageRecord = BackendUtility::getRecord('pages', $pageUid);

        $this->backEndUser->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(true);
        $this->backEndUser->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->willReturn(false);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToTableAndAccessToPageReturnsTrue(): void
    {
        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);

        $pageRecord = BackendUtility::getRecord('pages', $pageUid);

        $this->backEndUser->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->willReturn(true);
        $this->backEndUser->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->willReturn(true);

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }
}
