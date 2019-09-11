<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use OliverKlee\Seminars\Service\HookService;
use OliverKlee\Seminars\Interfaces\Hook;
use OliverKlee\Seminars\Interfaces\Hook\TestHook;
use Tx_Seminars_Tests_Unit_Fixtures_Service_TestHookImplementor as TestHookImplementor;

/**
 * Test case.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class Tx_Seminars_Tests_Unit_Service_HookServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var array backed-up extension configuration of the TYPO3 configuration
     *            variables
     */
    protected $extConfBackup = [];

    protected function setUp()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'];
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = $this->extConfBackup;
    }

    /*
     * Utility functions
     */

    /**
     * Creates a TestHook implementor object.
     *
     * @return \Tx_Seminars_Tests_Unit_Fixtures_Service_TestingHookService
     */
    protected function createTestHookImplementor()
    {
        return GeneralUtility::makeInstance(
            TestHookImplementor::class
        );
    }

    /**
     * Creates a TestHook accepting Hook object.
     *
     * @return \OliverKlee\Seminars\Service\HookService
     */
    protected function createHookObject()
    {
        return GeneralUtility::makeInstance(
            HookService::class,
            TestHook::class
        );
    }

    /*
     * Tests concerning the Hook implementors
     */

    /**
     * @test
     */
    public function testHookImplementorCanBeCreated()
    {
        self::assertInstanceOf(
            TestHookImplementor::class,
            $this->createTestHookImplementor()
        );
    }

    /**
     * @test
     */
    public function testHookImplementorImplementsHookHierachie()
    {
        $implementor = $this->createTestHookImplementor();

        self::assertInstanceOf(
            Hook::class,
            $implementor
        );

        self::assertInstanceOf(
            TestHook::class,
            $implementor
        );
    }

    /**
     * @test
     */
    public function testHookImplementorImplementsRequiredTestApi()
    {
        $implementor = $this->createTestHookImplementor();

        self::assertClassHasAttribute('wasCalled', TestHookImplementor::class);
        self::assertObjectHasAttribute('wasCalled', $implementor);
        self::assertFalse($implementor->wasCalled);
    }

    /**
     * @test
     */
    public function testHookImplementorTestHookMethodCanBeCalledAndReportsBeingCalled()
    {
        $implementor = $this->createTestHookImplementor();

        $implementor->testHookMethod();

        self::assertTrue($implementor->wasCalled);
    }

    /*
     * Tests concerning the Hook object
     */

    /**
     * @test
     */
    public function hookObjectForTestHookCanBeCreated()
    {
        self::assertInstanceOf(
            HookService::class,
            $this->createHookObject()
        );
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithNoHookImplemetorRegisteredResultsInEmptyHookList()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHook::class]);
        $hookObject = $this->createHookObject();

        self::assertEmpty($hookObject->getHooks());
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithOneHookImplemetorRegisteredResultsInOneHookInHookList()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHook::class][1565007112] =
            TestHookImplementor::class;
        $hookObject = $this->createHookObject();

        self::assertCount(1, $hookObject->getHooks());
        self::assertContainsOnlyInstancesOf(TestHook::class, $hookObject->getHooks());
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithTwoHookImplemetorsRegisteredResultsInTwoHooksInHookList()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHook::class][1565007112] =
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHook::class][1565007113] =
            TestHookImplementor::class;
        $hookObject = $this->createHookObject();

        self::assertCount(2, $hookObject->getHooks());
        self::assertContainsOnlyInstancesOf(TestHook::class, $hookObject->getHooks());
    }
}
