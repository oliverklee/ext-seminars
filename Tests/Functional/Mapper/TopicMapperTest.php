<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Seminars\Tests\Functional\Traits\CollectionHelper;

final class TopicMapperTest extends FunctionalTestCase
{
    use CollectionHelper;

    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_Mapper_Event();
    }

    /**
     * @test
     */
    public function getRequirementsForNoRequirementsReturnsEmptyList(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Topics.xml');

        $model = $this->subject->find(1);
        $result = $model->getRequirements();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function getRequirementsReturnsRequirements(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Topics.xml');

        $model = $this->subject->find(2);
        $result = $model->getRequirements();

        self::assertCount(1, $result);
        self::assertContainsModelWithUid($result, 3);
    }

    /**
     * @test
     */
    public function getDependenciesForNoDependenciesReturnsEmptyList(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Topics.xml');

        $model = $this->subject->find(1);
        $result = $model->getDependencies();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function getDependenciesReturnsDependencies(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Topics.xml');

        $model = $this->subject->find(3);
        $result = $model->getDependencies();

        self::assertCount(1, $result);
        self::assertContainsModelWithUid($result, 2);
    }
}
