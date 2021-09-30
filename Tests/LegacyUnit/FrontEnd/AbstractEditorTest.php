<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd\Fixtures\TestingEditor;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\AbstractEditor
 */
final class AbstractEditorTest extends TestCase
{
    /**
     * @var TestingEditor
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->subject = new TestingEditor([], $this->getFrontEndController()->cObj);
        $this->subject->setTestMode();
    }

    protected function tearDown(): void
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

    /**
     * @test
     */
    public function isTestModeReturnsTrueForTestModeEnabled(): void
    {
        self::assertTrue(
            $this->subject->isTestMode()
        );
    }

    /**
     * @test
     */
    public function isTestModeReturnsFalseForTestModeDisabled(): void
    {
        $subject = new TestingEditor([], $this->getFrontEndController()->cObj);

        self::assertFalse(
            $subject->isTestMode()
        );
    }

    /////////////////////////////////////////////////
    // Tests for setting and getting the object UID
    /////////////////////////////////////////////////

    /**
     * @test
     */
    public function getObjectUidReturnsTheSetObjectUidForZero(): void
    {
        $this->subject->setObjectUid(0);

        self::assertEquals(
            0,
            $this->subject->getObjectUid()
        );
    }

    /**
     * @test
     */
    public function getObjectUidReturnsTheSetObjectUidForExistingObjectUid(): void
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

    /**
     * @test
     */
    public function getFormValueReturnsEmptyStringForRequestedFormValueNotSet(): void
    {
        self::assertEquals(
            '',
            $this->subject->getFormValue('title')
        );
    }

    /**
     * @test
     */
    public function getFormValueReturnsValueSetViaSetFakedFormValue(): void
    {
        $this->subject->setFakedFormValue('title', 'foo');

        self::assertEquals(
            'foo',
            $this->subject->getFormValue('title')
        );
    }
}
