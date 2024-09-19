<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Repository\FoodOptionRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\FoodOption
 * @covers \OliverKlee\Seminars\Domain\Repository\FoodOptionRepository
 */
final class FoodOptionRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var FoodOptionRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(FoodOptionRepository::class);
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FoodOptionRepository/FoodOptionWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(FoodOption::class, $result);
        self::assertSame('vegetarian', $result->getTitle());
    }
}
