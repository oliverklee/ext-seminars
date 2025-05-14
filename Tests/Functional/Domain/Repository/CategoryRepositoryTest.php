<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository;

use OliverKlee\Seminars\Domain\Model\Category;
use OliverKlee\Seminars\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Category
 * @covers \OliverKlee\Seminars\Domain\Repository\CategoryRepository
 */
final class CategoryRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    private CategoryRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(CategoryRepository::class);
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/CategoryRepository/propertyMapping/CategoryWithAllFields.csv');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Category::class, $result);
        self::assertSame('cooking', $result->getTitle());
    }
}
