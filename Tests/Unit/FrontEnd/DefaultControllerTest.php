<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\FrontEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd\Fixtures\TestingDefaultController;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\DefaultController
 */
final class DefaultControllerTest extends UnitTestCase
{
    // Tests concerning the single view

    /**
     * @test
     */
    public function singleViewFlavorWithUidCreatesSingleView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createSingleView');
        $controller->expects(self::never())->method('createListView');

        $controller->piVars = ['showUid' => '42'];

        $controller->main('', ['what_to_display' => 'single_view']);
    }

    /**
     * @test
     */
    public function singleViewFlavorWithUidFromShowSingleEventConfigurationCreatesSingleView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createSingleView');
        $controller->expects(self::never())->method('createListView');

        $controller->piVars = [];

        $controller->main('', ['what_to_display' => 'single_view', 'showSingleEvent' => 42]);
    }

    /**
     * @test
     */
    public function singleViewFlavorWithoutUidCreatesSingleView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createSingleView');
        $controller->expects(self::never())->method('createListView');

        $controller->piVars = [];

        $controller->main('', ['what_to_display' => 'single_view']);
    }

    // Tests concerning the basic functions of the list view

    /**
     * @test
     */
    public function eventListFlavorWithoutUidCreatesListView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createListView')->with('seminar_list');
        $controller->expects(self::never())->method('createSingleView');

        $controller->piVars = [];

        $controller->main('', ['what_to_display' => 'seminar_list']);
    }

    /**
     * @test
     */
    public function eventListFlavorWithUidCreatesListView(): void
    {
        /** @var TestingDefaultController&MockObject $controller */
        $controller = $this->createPartialMock(
            TestingDefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'setCSS',
                'createHelperObjects',
                'setErrorMessage',
            ]
        );
        $controller->expects(self::once())->method('createListView')->with('seminar_list');
        $controller->expects(self::never())->method('createSingleView');

        $controller->piVars = ['showUid' => '42'];

        $controller->main('', ['what_to_display' => 'seminar_list']);
    }
}
