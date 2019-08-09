<?php

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Csv_BackEndEventAccessCheckTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Csv_BackEndEventAccessCheck
     */
    protected $subject = null;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|BackendUserAuthentication
     */
    protected $backEndUser = null;

    /**
     * @var BackendUserAuthentication
     */
    protected $backEndUserBackup = null;

    protected function setUp()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        $this->backEndUser = $this->createMock(BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $this->backEndUser;

        $this->subject = new \Tx_Seminars_Csv_BackEndEventAccessCheck();
    }

    protected function tearDown()
    {
        \Tx_Oelib_BackEndLoginManager::purgeInstance();
        $GLOBALS['BE_USER'] = $this->backEndUserBackup;
    }

    /**
     * @test
     */
    public function subjectImplementsAccessCheck()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Interface_CsvAccessCheck::class,
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
