<?php

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_EditorTest extends TestCase
{
    /**
     * @var \Tx_Seminars_FrontEnd_Editor
     */
    private $subject;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->subject = new \Tx_Seminars_FrontEnd_Editor([], $GLOBALS['TSFE']->cObj);
        $this->subject->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    //////////////////////////////
    // Testing the test mode flag
    //////////////////////////////

    public function testIsTestModeReturnsTrueForTestModeEnabled()
    {
        self::assertTrue(
            $this->subject->isTestMode()
        );
    }

    public function testIsTestModeReturnsFalseForTestModeDisabled()
    {
        $subject = new \Tx_Seminars_FrontEnd_Editor([], $GLOBALS['TSFE']->cObj);

        self::assertFalse(
            $subject->isTestMode()
        );
    }

    /////////////////////////////////////////////////
    // Tests for setting and getting the object UID
    /////////////////////////////////////////////////

    public function testGetObjectUidReturnsTheSetObjectUidForZero()
    {
        $this->subject->setObjectUid(0);

        self::assertEquals(
            0,
            $this->subject->getObjectUid()
        );
    }

    public function testGetObjectUidReturnsTheSetObjectUidForExistingObjectUid()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_test');
        $this->subject->setObjectUid($uid);

        self::assertEquals(
            $uid,
            $this->subject->getObjectUid()
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests for getting form values and setting faked form values
    ////////////////////////////////////////////////////////////////

    public function testGetFormValueReturnsEmptyStringForRequestedFormValueNotSet()
    {
        self::assertEquals(
            '',
            $this->subject->getFormValue('title')
        );
    }

    public function testGetFormValueReturnsValueSetViaSetFakedFormValue()
    {
        $this->subject->setFakedFormValue('title', 'foo');

        self::assertEquals(
            'foo',
            $this->subject->getFormValue('title')
        );
    }
}
