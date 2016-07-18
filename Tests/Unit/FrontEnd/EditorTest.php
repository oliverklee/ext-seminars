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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_EditorTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_FrontEnd_Editor
     */
    private $fixture;
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->fixture = new Tx_Seminars_FrontEnd_Editor([], $GLOBALS['TSFE']->cObj);
        $this->fixture->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    //////////////////////////////
    // Testing the test mode flag
    //////////////////////////////

    public function testIsTestModeReturnsTrueForTestModeEnabled()
    {
        self::assertTrue(
            $this->fixture->isTestMode()
        );
    }

    public function testIsTestModeReturnsFalseForTestModeDisabled()
    {
        $fixture = new Tx_Seminars_FrontEnd_Editor([], $GLOBALS['TSFE']->cObj);

        self::assertFalse(
            $fixture->isTestMode()
        );
    }

    /////////////////////////////////////////////////
    // Tests for setting and getting the object UID
    /////////////////////////////////////////////////

    public function testGetObjectUidReturnsTheSetObjectUidForZero()
    {
        $this->fixture->setObjectUid(0);

        self::assertEquals(
            0,
            $this->fixture->getObjectUid()
        );
    }

    public function testGetObjectUidReturnsTheSetObjectUidForExistingObjectUid()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_test');
        $this->fixture->setObjectUid($uid);

        self::assertEquals(
            $uid,
            $this->fixture->getObjectUid()
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests for getting form values and setting faked form values
    ////////////////////////////////////////////////////////////////

    public function testGetFormValueReturnsEmptyStringForRequestedFormValueNotSet()
    {
        self::assertEquals(
            '',
            $this->fixture->getFormValue('title')
        );
    }

    public function testGetFormValueReturnsValueSetViaSetFakedFormValue()
    {
        $this->fixture->setFakedFormValue('title', 'foo');

        self::assertEquals(
            'foo',
            $this->fixture->getFormValue('title')
        );
    }
}
