<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\ViewHelpers;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Model\TitledTestingModel;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Model\UntitledTestingModel;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CommaSeparatedTitlesViewHelperTest extends TestCase
{
    /**
     * @var \Tx_Seminars_ViewHelper_CommaSeparatedTitles
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var Collection
     */
    private $list;

    /**
     * @var string
     */
    const TIME_FORMAT = '%H:%M';

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->list = new Collection();
        $this->subject = new \Tx_Seminars_ViewHelper_CommaSeparatedTitles();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function renderWithEmptyListReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->render($this->list)
        );
    }

    /**
     * @test
     */
    public function renderWithElementsInListWithoutGetTitleMethodThrowsBadMethodCallException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'All elements in $list must implement the interface Tx_Seminars_Interface_Titled.'
        );
        $this->expectExceptionCode(1333658899);

        $model = new UntitledTestingModel();
        $model->setData([]);

        $this->list->add($model);

        $this->subject->render($this->list);
    }

    /**
     * @test
     */
    public function renderWithOneElementListReturnsOneElementsTitle()
    {
        $model = new TitledTestingModel();
        $model->setData(['title' => 'Testing model']);

        $this->list->add($model);

        self::assertSame(
            $model->getTitle(),
            $this->subject->render($this->list)
        );
    }

    /**
     * @test
     */
    public function renderWithTwoElementsListReturnsTwoElementTitlesSeparatedByComma()
    {
        $firstModel = new TitledTestingModel();
        $firstModel->setData(['title' => 'First testing model']);
        $secondModel = new TitledTestingModel();
        $secondModel->setData(['title' => 'Second testing model']);

        $this->list->add($firstModel);
        $this->list->add($secondModel);

        self::assertSame(
            $firstModel->getTitle() . ', ' . $secondModel->getTitle(),
            $this->subject->render($this->list)
        );
    }

    /**
     * @test
     */
    public function renderWithOneElementListReturnsOneElementsTitleHtmlspecialchared()
    {
        $model = new TitledTestingModel();
        $model->setData(['title' => '<test>Testing model</test>']);

        $this->list->add($model);

        self::assertSame(
            \htmlspecialchars($model->getTitle(), ENT_QUOTES | ENT_HTML5),
            $this->subject->render($this->list)
        );
    }
}
