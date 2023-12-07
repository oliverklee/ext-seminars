<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Tests\Functional\Traits\CollectionHelper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * This test case holds all tests specific to event topics.
 *
 * @covers \OliverKlee\Seminars\Mapper\EventMapper
 */
final class EventTopicMapperTest extends FunctionalTestCase
{
    use CollectionHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var EventMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EventMapper();
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
