<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Test case.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Csv_BackEndEventAccessCheckTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Csv_BackEndEventAccessCheck
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
        $this->backEndUser = $this->getMock(BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $this->backEndUser;

        $this->subject = new Tx_Seminars_Csv_BackEndEventAccessCheck();
    }

    protected function tearDown()
    {
        Tx_Oelib_BackEndLoginManager::purgeInstance();
        $GLOBALS['BE_USER'] = $this->backEndUserBackup;
    }

    /**
     * @test
     */
    public function subjectImplementsAccessCheck()
    {
        self::assertInstanceOf(
            'Tx_Seminars_Interface_CsvAccessCheck',
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

        $this->backEndUser->expects(self::any())->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->will(self::returnValue(false));
        $this->backEndUser->expects(self::any())->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->will(self::returnValue(false));

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

        $this->backEndUser->expects(self::any())->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->will(self::returnValue(false));
        $this->backEndUser->expects(self::any())->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->will(self::returnValue(true));

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

        $this->backEndUser->expects(self::any())->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->will(self::returnValue(true));
        $this->backEndUser->expects(self::any())->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->will(self::returnValue(false));

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

        $this->backEndUser->expects(self::any())->method('check')
            ->with('tables_select', 'tx_seminars_seminars')
            ->will(self::returnValue(true));
        $this->backEndUser->expects(self::any())->method('doesUserHaveAccess')
            ->with($pageRecord, 1)
            ->will(self::returnValue(true));

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }
}
