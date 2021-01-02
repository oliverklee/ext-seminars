<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EditorTest extends TestCase
{
    /**
     * @var \Tx_Seminars_FrontEnd_Editor
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->subject = new \Tx_Seminars_FrontEnd_Editor([], $this->getFrontEndController()->cObj);
        $this->subject->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
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
        $subject = new \Tx_Seminars_FrontEnd_Editor([], $this->getFrontEndController()->cObj);

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
