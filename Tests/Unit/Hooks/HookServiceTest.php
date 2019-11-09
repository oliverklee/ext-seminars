<?php
declare(strict_types = 1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Hooks\HookService;
use OliverKlee\Seminars\Interfaces\Hook;
use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestHookInterface;
use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestHookImplementor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class HookServiceTest extends UnitTestCase
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
     * @return \OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestHookImplementor
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
     * @return \OliverKlee\Seminars\Hooks\HookService
     */
    protected function createHookObject()
    {
        return GeneralUtility::makeInstance(
            HookService::class,
            TestHookInterface::class
        );
    }

    /*
     * Tests concerning the TestHookImplementor
     */

    /**
     * @test
     */
    public function hookImplementorCanBeCreated()
    {
        self::assertInstanceOf(
            TestHookImplementor::class,
            $this->createTestHookImplementor()
        );
    }

    /**
     * @test
     */
    public function hookImplementorImplementsHookHierachie()
    {
        $implementor = $this->createTestHookImplementor();

        self::assertInstanceOf(
            Hook::class,
            $implementor
        );

        self::assertInstanceOf(
            TestHookInterface::class,
            $implementor
        );
    }

    /**
     * @test
     */
    public function hookImplementorImplementsRequiredTestApi()
    {
        $implementor = $this->createTestHookImplementor();

        self::assertClassHasAttribute('wasCalled', TestHookImplementor::class);
        self::assertObjectHasAttribute('wasCalled', $implementor);
        self::assertFalse($implementor->wasCalled);
    }

    /**
     * @test
     */
    public function hookImplementorTestHookMethodCanBeCalledAndReportsBeingCalled()
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
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHookInterface::class]);
        $hookObject = $this->createHookObject();

        self::assertEmpty($hookObject->getHooks());
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithOneHookImplemetorRegisteredResultsInOneHookInHookList()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHookInterface::class][1565007112] =
            TestHookImplementor::class;
        $hookObject = $this->createHookObject();

        self::assertCount(1, $hookObject->getHooks());
        self::assertContainsOnlyInstancesOf(TestHookInterface::class, $hookObject->getHooks());
        self::assertContainsOnlyInstancesOf(TestHookImplementor::class, $hookObject->getHooks());
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithTwoHookImplemetorsRegisteredResultsInTwoHooksInHookList()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHookInterface::class][1565007112] =
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestHookInterface::class][1565007113] =
            TestHookImplementor::class;
        $hookObject = $this->createHookObject();

        self::assertCount(2, $hookObject->getHooks());
        self::assertContainsOnlyInstancesOf(TestHookInterface::class, $hookObject->getHooks());
        self::assertContainsOnlyInstancesOf(TestHookImplementor::class, $hookObject->getHooks());
    }
}
