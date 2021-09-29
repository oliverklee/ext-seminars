<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Seminars\Tests\Functional\Traits\CollectionHelper;

final class DateMapperTest extends FunctionalTestCase
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
        $this->importDataSet(__DIR__ . '/Fixtures/Dates.xml');

        $model = $this->subject->find(2);
        $result = $model->getRequirements();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function getRequirementsReturnsTopicRequirements(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Dates.xml');

        $model = $this->subject->find(5);
        $result = $model->getRequirements();

        self::assertCount(1, $result);
        self::assertContainsModelWithUid($result, 4);
    }

    /**
     * @test
     */
    public function getDependenciesForNoDependenciesReturnsEmptyList(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Dates.xml');

        $model = $this->subject->find(2);
        $result = $model->getDependencies();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function getDependenciesReturnsTopicDependencies(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Dates.xml');

        $model = $this->subject->find(6);
        $result = $model->getDependencies();

        self::assertCount(1, $result);
        self::assertContainsModelWithUid($result, 3);
    }
}
