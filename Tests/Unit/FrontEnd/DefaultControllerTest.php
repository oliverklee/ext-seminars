<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\FrontEnd;

use OliverKlee\Seminars\FrontEnd\DefaultController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\DefaultController
 * @covers \OliverKlee\Seminars\Templating\TemplateHelper
 */
final class DefaultControllerTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    // Tests concerning the single view

    /**
     * @test
     */
    public function singleViewFlavorWithUidCreatesSingleView(): void
    {
        $controller = $this->createPartialMock(
            DefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'createHelperObjects',
            ],
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
        $controller = $this->createPartialMock(
            DefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'createHelperObjects',
            ],
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
        $controller = $this->createPartialMock(
            DefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'createHelperObjects',
            ],
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
        $controller = $this->createPartialMock(
            DefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'createHelperObjects',
            ],
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
        $controller = $this->createPartialMock(
            DefaultController::class,
            [
                'createListView',
                'createSingleView',
                'pi_initPIflexForm',
                'getTemplateCode',
                'setLabels',
                'createHelperObjects',
            ],
        );
        $controller->expects(self::once())->method('createListView')->with('seminar_list');
        $controller->expects(self::never())->method('createSingleView');

        $controller->piVars = ['showUid' => '42'];

        $controller->main('', ['what_to_display' => 'seminar_list']);
    }
}
