<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Csv\BackEndEventAccessCheck;
use OliverKlee\Seminars\Csv\Interfaces\CsvAccessCheck;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndEventAccessCheckTest extends TestCase
{
    /**
     * @var BackEndEventAccessCheck
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

    protected function setUp()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        $this->backEndUser = $this->createMock(BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $this->backEndUser;

        $this->subject = new BackEndEventAccessCheck();
    }

    protected function tearDown()
    {
        BackEndLoginManager::purgeInstance();
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
    public function hasAccessForNoAccessToTableAndNoAccessToPageReturnsFalse()
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
    public function hasAccessForNoAccessToTableAndAccessToPageReturnsFalse()
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
    public function hasAccessForAccessToTableAndNoAccessToPageReturnsFalse()
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
    public function hasAccessForAccessToTableAndAccessToPageReturnsTrue()
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
