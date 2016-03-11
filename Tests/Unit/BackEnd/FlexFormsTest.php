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

/**
 * Test case.
 *
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_FlexFormsTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_FlexForms
     */
    private $fixture;
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var string name of the table which is used to test the flexforms
     *      settings
     */
    private $testingTable = 'tx_seminars_organizers';

    /**
     * @var array a backup of the TCA setting for the testing table
     */
    private $tcaBackup;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->fixture = new Tx_Seminars_FlexForms();
        $this->tcaBackup = $GLOBALS['TCA'][$this->testingTable]['ctrl'];
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsBoolean('useStoragePid', false);
        $GLOBALS['TCA'][$this->testingTable]['ctrl']['iconfile'] = 'fooicon';
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $GLOBALS['TCA'][$this->testingTable]['ctrl'] = $this->tcaBackup;
    }

    //////////////////////////////////////////////////////
    // Tests concerning getEntriesFromGeneralStoragePage
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getEntriesFromGeneralStoragePageForUseStoragePidAndStoragePidSetFindsRecordWithThisPid()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsBoolean('useStoragePid', true);

        $storagePageUid = $this->testingFramework->createSystemFolder();
        $sysFolderUid = $this->testingFramework->createSystemFolder(
            0, array('storage_pid' => $storagePageUid)
        );

        $recordUid = $this->testingFramework->createRecord(
            $this->testingTable,
            array('title' => 'foo record', 'pid' => $storagePageUid)
        );

        $configuration = $this->fixture->getEntriesFromGeneralStoragePage(
            array(
                'config' => array('itemTable' => $this->testingTable),
                'row' => array('pid' => $sysFolderUid),
                'items' => array(),
            )
        );

        self::assertTrue(
            in_array(
                array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
                $configuration['items']
            )
        );
    }

    /**
     * @test
     */
    public function getEntriesFromGeneralStoragePageForUseStoragePidAndStoragePidSetDoesNotFindRecordWithOtherPid()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsBoolean('useStoragePid', true);

        $sysFolderUid = $this->testingFramework->createSystemFolder(
            0,
            array('storage_pid' => $this->testingFramework->createSystemFolder())
        );
        $recordUid = $this->testingFramework->createRecord(
            $this->testingTable,
            array('title' => 'foo record', 'pid' => 42)
        );

        $configuration = $this->fixture->getEntriesFromGeneralStoragePage(
            array(
                'config' => array('itemTable' => $this->testingTable),
                'row' => array('pid' => $sysFolderUid),
                'items' => array(),
            )
        );

        self::assertFalse(
            in_array(
                array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
                $configuration['items']
            )
        );
    }

    /**
     * @test
     */
    public function getEntriesFromGeneralStoragePageForUseStoragePidSetAndStoragePidSetOnParentPageFindsRecordWithThisPid()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsBoolean('useStoragePid', true);

        $storagePage = $this->testingFramework->createSystemFolder();
        $parentPageUid = $this->testingFramework->createFrontEndPage(
            0, array('storage_pid' => $storagePage));
        $sysFolderUid = $this->testingFramework->createFrontEndPage(
            $parentPageUid
        );

        $recordUid = $this->testingFramework->createRecord(
            $this->testingTable,
            array('title' => 'foo record', 'pid' => $storagePage)
        );

        $configuration = $this->fixture->getEntriesFromGeneralStoragePage(
            array(
                'config' => array('itemTable' => $this->testingTable),
                'row' => array('pid' => $sysFolderUid),
                'items' => array(),
            )
        );

        self::assertTrue(
            in_array(
                array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
                $configuration['items']
            )
        );
    }

    /**
     * @test
     */
    public function getEntriesFromGeneralStoragePageForUseStoragePidSetAndNoStoragePidSetFindsRecordWithAnyPid()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsBoolean('useStoragePid', true);

        $recordUid = $this->testingFramework->createRecord(
            $this->testingTable, array('title' => 'foo record', 'pid' => 42)
        );

        $configuration = $this->fixture->getEntriesFromGeneralStoragePage(
            array(
                'config' => array('itemTable' => $this->testingTable),
                'row' => array('pid'
                    => $this->testingFramework->createFrontEndPage()
                ),
                'items' => array(),
            )
        );

        self::assertTrue(
            in_array(
                array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
                $configuration['items']
            )
        );
    }

    /**
     * @test
     */
    public function getEntriesFromGeneralStoragePageForUseStoragePidNotAndStoragePidSetFindsRecordWithPidOtherThanStoragePid()
    {
        $sysFolderUid = $this->testingFramework->createSystemFolder(
            0,
            array('storage_pid' => $this->testingFramework->createSystemFolder())
        );
        $recordUid = $this->testingFramework->createRecord(
            $this->testingTable,
            array('title' => 'foo record', 'pid' => 42)
        );

        $configuration = $this->fixture->getEntriesFromGeneralStoragePage(
            array(
                'config' => array('itemTable' => $this->testingTable),
                'row' => array('pid' => $sysFolderUid),
                'items' => array(),
            )
        );

        self::assertTrue(
            in_array(
                array(0 => 'foo record', 1 => $recordUid, 2 => 'fooicon'),
                $configuration['items']
            )
        );
    }
}
