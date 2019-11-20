<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\Hook;
use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookInterface;
use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookInterfaceNotExtendsHook;
use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookImplementor;
use OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookImplementor2;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class HookProviderTest extends UnitTestCase
{
    /**
     * @var array
     */
    private $extConfBackup = [];

    protected function setUp()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'];
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = $this->extConfBackup;

        parent::tearDown();
    }

    /*
     * Utility functions
     */

    /**
     * Creates a TestingHookInterface implementor object
     *
     * @return TestingHookImplementor
     */
    private function createTestingHookImplementor(): TestingHookImplementor
    {
        return new TestingHookImplementor();
    }

    /**
     * Creates a second TestingHookInterface implementor object
     *
     * @return TestingHookImplementor2
     */
    private function createTestingHookImplementor2(): TestingHookImplementor2
    {
        return new TestingHookImplementor2();
    }

    /**
     * Creates a TestingHookInterface accepting Hook object
     *
     * @return HookProvider
     */
    private function createHookObject(): HookProvider
    {
        return new HookProvider(TestingHookInterface::class);
    }

    /*
     * Tests concerning the TestingHookImplementor
     */

    /**
     * @test
     */
    public function hookImplementorCanBeCreated()
    {
        self::assertInstanceOf(TestingHookImplementor::class, $this->createTestingHookImplementor());
    }

    /**
     * @test
     */
    public function hookImplementorResetsCallCounterOnCreation()
    {
        TestingHookImplementor::$wasCalled = 1;

        $this->createTestingHookImplementor();

        self::assertSame(0, TestingHookImplementor::$wasCalled);
    }

    /**
     * @test
     */
    public function hookImplementorImplementsHookHierachy()
    {
        $implementor = $this->createTestingHookImplementor();

        self::assertInstanceOf(Hook::class, $implementor);
        self::assertInstanceOf(TestingHookInterface::class, $implementor);
    }

    /**
     * @test
     */
    public function hookImplementorTestHookMethodCanBeCalledAndReportsBeingCalled()
    {
        $implementor = $this->createTestingHookImplementor();

        $implementor->testHookMethod();

        self::assertSame(1, TestingHookImplementor::$wasCalled);
    }

    /*
     * Tests concerning the TestingHookImplementor2
     */

    /**
     * @test
     */
    public function hookImplementor2CanBeCreated()
    {
        self::assertInstanceOf(TestingHookImplementor2::class, $this->createTestingHookImplementor2());
    }

    /**
     * @test
     */
    public function hookImplementor2ResetsCallCounterOnCreation()
    {
        TestingHookImplementor2::$wasCalled = 1;

        $this->createTestingHookImplementor2();

        self::assertSame(0, TestingHookImplementor2::$wasCalled);
    }

    /**
     * @test
     */
    public function hookImplementor2ImplementsHookHierachy()
    {
        $implementor = $this->createTestingHookImplementor2();

        self::assertInstanceOf(Hook::class, $implementor);
        self::assertInstanceOf(TestingHookInterface::class, $implementor);
    }

    /**
     * @test
     */
    public function hookImplementor2TestHookMethodCanBeCalledAndReportsBeingCalled()
    {
        $implementor = $this->createTestingHookImplementor2();

        $implementor->testHookMethod();

        self::assertSame(1, TestingHookImplementor2::$wasCalled);
    }

    /*
     * Tests concerning the Hook object
     */

    /**
     * @test
     */
    public function hookObjectForTestHookCanBeCreated()
    {
        self::assertInstanceOf(HookProvider::class, $this->createHookObject());
    }

    /**
     * @test
     */
    public function hookObjectForNoInterfaceCannotBeCreated()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1565089078);

        $subject = new HookProvider('');
    }

    /**
     * @test
     */
    public function hookObjectForNonexistantInterfaceCannotBeCreated()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1565089078);

        $subject = new HookProvider('\OliverKlee\Seminars\Tests\Unit\Fixtures\Hooks\TestingHookInterfaceDoesNotExist');
    }

    /**
     * @test
     */
    public function hookObjectForInvalidInterfaceCannotBeCreated()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1565088963);

        $subject = new HookProvider(TestingHookInterfaceNotExtendsHook::class);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function hookObjectForTestHookWithNoHookImplementorRegisteredSucceedsForValidMethod()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class]);
        $hookObject = $this->createHookObject();

        $hookObject->executeHook('testHookMethod');
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithNoHookImplementorRegisteredFailsForEmptyMethod()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class]);
        $hookObject = $this->createHookObject();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1573479911);

        $hookObject->executeHook('');
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithNoHookImplementorRegisteredFailsForUnknownMethod()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class]);
        $hookObject = $this->createHookObject();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1573480302);

        $hookObject->executeHook('methodNotImplemented');
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithOneHookImplementorRegisteredSucceedsWithMethodCalledOnce()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class][1565007112] =
            TestingHookImplementor::class;
        $hookObject = $this->createHookObject();

        $hookObject->executeHook('testHookMethod');

        self::assertSame(1, TestingHookImplementor::$wasCalled);
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithTwoHookImplementorsRegisteredResultsInTwoHooksInHookList()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class][1565007112] =
            TestingHookImplementor::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class][1565007113] =
            TestingHookImplementor2::class;
        $hookObject = $this->createHookObject();

        $hookObject->executeHook('testHookMethod');

        self::assertSame(1, TestingHookImplementor::$wasCalled);
        self::assertSame(1, TestingHookImplementor2::$wasCalled);
    }

    /**
     * @test
     */
    public function hookObjectForTestHookCanBeCreatedWithDifferentIndexName()
    {
        self::assertInstanceOf(HookProvider::class, new HookProvider(TestingHookInterface::class, 'anyIndex'));
    }
}
