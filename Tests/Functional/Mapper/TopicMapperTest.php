<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Seminars\Tests\Functional\Traits\CollectionHelper;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class TopicMapperTest extends FunctionalTestCase
{
    use CollectionHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_Mapper_Event();
    }

    /**
     * @test
     */
    public function getRequirementsForNoRequirementsReturnsEmptyList()
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
    public function getRequirementsReturnsRequirements()
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
    public function getDependenciesForNoDependenciesReturnsEmptyList()
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
    public function getDependenciesReturnsDependencies()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Topics.xml');

        $model = $this->subject->find(3);
        $result = $model->getDependencies();

        self::assertCount(1, $result);
        self::assertContainsModelWithUid($result, 2);
    }
}
