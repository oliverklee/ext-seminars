<?php

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Csv_BackEndRegistrationAccessCheckTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Csv_BackEndRegistrationAccessCheck
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

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    protected function setUp()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        $this->backEndUser = $this->getMock(BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $this->backEndUser;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Csv_BackEndRegistrationAccessCheck();
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
    public function hasAccessForNoAccessToEventsTableAndNoAccessToRegistrationsTableReturnsFalse()
    {
        $this->backEndUser->expects(self::at(0))->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->will(self::returnValue(false));

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
            ->will(self::returnValue(false));

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
            ->will(self::returnValue(true));
        $this->backEndUser->expects(self::at(1))->method('check')
            ->with('tables_select', 'tx_seminars_attendances')
            ->will(self::returnValue(false));

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
            ->will(self::returnValue(true));
        $this->backEndUser->expects(self::at(1))->method('check')
            ->with('tables_select', 'tx_seminars_attendances')
            ->will(self::returnValue(true));

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToEventsTableAndAccessToRegistrationsTableAndAccessToSetPageReturnsTrue()
    {
        $this->backEndUser->expects(self::any())->method('check')
            ->with('tables_select', self::anything())
            ->will(self::returnValue(true));

        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);
        $pageRecord = BackendUtility::getRecord('pages', $pageUid);
        $this->backEndUser->expects(self::any())->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->will(self::returnValue(true));

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForAccessToEventsTableAndAccessToRegistrationsTableAndNoAccessToSetPageReturnsFalse()
    {
        $this->backEndUser->expects(self::any())->method('check')
            ->with('tables_select', self::anything())
            ->will(self::returnValue(true));

        $pageUid = 12341;
        $this->subject->setPageUid($pageUid);
        $pageRecord = BackendUtility::getRecord('pages', $pageUid);
        $this->backEndUser->expects(self::any())->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->will(self::returnValue(false));

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }
}
