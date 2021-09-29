<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\ViewHelpers;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Model\Interfaces\Titled;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Model\TitledTestingModel;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Model\UntitledTestingModel;
use OliverKlee\Seminars\ViewHelpers\CommaSeparatedTitlesViewHelper;

/**
 * @covers \OliverKlee\Seminars\ViewHelpers\CommaSeparatedTitlesViewHelper
 */
final class CommaSeparatedTitlesViewHelperTest extends TestCase
{
    /**
     * @var CommaSeparatedTitlesViewHelper
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var Collection<Titled>
     */
    private $list;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        /** @var Collection<Titled> $items */
        $items = new Collection();
        $this->list = $items;
        $this->subject = new CommaSeparatedTitlesViewHelper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function renderWithEmptyListReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->render($this->list)
        );
    }

    /**
     * @test
     */
    public function renderWithElementsInListWithoutGetTitleMethodThrowsBadMethodCallException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'All elements in $list must implement the interface OliverKlee\\Seminars\\Model\\Interfaces\\Titled.'
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
    public function renderWithOneElementListReturnsOneElementsTitle(): void
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
    public function renderWithTwoElementsListReturnsTwoElementTitlesSeparatedByComma(): void
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
    public function renderWithOneElementListReturnsOneElementsTitleHtmlspecialchared(): void
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
