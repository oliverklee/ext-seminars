<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Hooks;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\Hook;
use OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures\TestingHookImplementor;
use OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures\TestingHookImplementor2;
use OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures\TestingHookImplementorNotImplementsInterface;
use OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures\TestingHookInterface;
use OliverKlee\Seminars\Tests\Unit\Hooks\Fixtures\TestingHookInterfaceNotExtendsHook;

/**
 * Test case.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
final class HookProviderTest extends UnitTestCase
{
    /**
     * @var array
     */
    private $extConfBackup = [];

    protected function setUp()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'];
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']);
        TestingHookImplementor::$wasCalled = 0;
        TestingHookImplementor2::$wasCalled = 0;
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = $this->extConfBackup;

        parent::tearDown();
    }

    /*
     * Utility functions.
     */

    /**
     * Creates a TestingHookInterface implementor object.
     *
     * @return TestingHookImplementor
     */
    private function createTestingHookImplementor(): TestingHookImplementor
    {
        return new TestingHookImplementor();
    }

    /**
     * Creates a second TestingHookInterface implementor object.
     *
     * @return TestingHookImplementor2
     */
    private function createTestingHookImplementor2(): TestingHookImplementor2
    {
        return new TestingHookImplementor2();
    }

    /**
     * Creates a TestingHookInterface accepting Hook object.
     *
     * @param string $index
     *
     * @return HookProvider
     */
    private function createHookObject(string $index = ''): HookProvider
    {
        if ($index === '') {
            return new HookProvider(TestingHookInterface::class);
        }

        return new HookProvider(TestingHookInterface::class, $index);
    }

    /*
     * Tests concerning the TestingHookImplementor.
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
     * Tests concerning the TestingHookImplementor2.
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
     * Tests concerning the Hook object.
     */

    /**
     * @test
     */
    public function hookObjectForTestHookCanBeCreatedWithoutIndex()
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

        $subject = new HookProvider(__NAMESPACE__ . '\\TestingHookInterfaceDoesNotExist');
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
        $hookObject = $this->createHookObject();

        $hookObject->executeHook('testHookMethod');
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithNoHookImplementorRegisteredFailsForEmptyMethod()
    {
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
    public function hookObjectForTestHookWithTwoHookImplementorsRegisteredSucceedsWithEachMethodCalledOnce()
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
    public function hookObjectForTestHookWithInvalidHookImplementorRegisteredFailsForValidMethod()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class][1565007112] =
            TestingHookImplementorNotImplementsInterface::class;
        $hookObject = $this->createHookObject();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1448901897);

        $hookObject->executeHook('testHookMethod');
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithIndexCanBeCreated()
    {
        self::assertInstanceOf(HookProvider::class, $this->createHookObject('anyIndex'));
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithIndexWithOneHookImplementorRegisteredSucceedsWithMethodCalledOnce()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['anyIndex'][1574270061] =
            TestingHookImplementor::class;
        $hookObject = $this->createHookObject('anyIndex');

        $hookObject->executeHook('testHookMethod');

        self::assertSame(1, TestingHookImplementor::$wasCalled);
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithIndexWithHookImplementorRegisteredByClassnameSucceedsWithMethodNotCalled()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class][1574270061] =
            TestingHookImplementor::class;
        $hookObject = $this->createHookObject('anyIndex');

        $hookObject->executeHook('testHookMethod');

        self::assertSame(0, TestingHookImplementor::$wasCalled);
    }

    /**
     * @test
     */
    public function hookObjectForTestHookWithIndexWithTwoHookImplementorsRegisteredSucceedsWithOneMethodCalledOnce()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][TestingHookInterface::class][1565007112] =
            TestingHookImplementor::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['anyIndex'][1574270061] =
            TestingHookImplementor2::class;
        $hookObject = $this->createHookObject('anyIndex');

        $hookObject->executeHook('testHookMethod');

        self::assertSame(0, TestingHookImplementor::$wasCalled);
        self::assertSame(1, TestingHookImplementor2::$wasCalled);
    }
}
