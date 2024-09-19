<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use OliverKlee\Seminars\OldModel\LegacyCategory;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyCategory
 */
final class LegacyCategoryTest extends FunctionalTestCase
{
    use FalHelper;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @test
     */
    public function fromUidMapsDataFromDatabase(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Categories.xml');

        $subject = LegacyCategory::fromUid(1);

        self::assertSame('Remote events', $subject->getTitle());
    }

    /**
     * @test
     */
    public function getIconWithoutIconReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Categories.xml');

        $subject = new LegacyCategory(1);

        self::assertNull($subject->getIcon());
    }

    /**
     * @test
     */
    public function getIconWithNotYetMigratedIconReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Categories.xml');

        $subject = new LegacyCategory(4);

        self::assertNull($subject->getIcon());
    }

    /**
     * @test
     */
    public function getIconWithPositiveIconCountWithoutFileReferenceReturnsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Categories.xml');

        $subject = new LegacyCategory(2);

        self::assertNull($subject->getIcon());
    }

    /**
     * @test
     */
    public function getIconWithFileReferenceReturnsFileReference(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Categories.xml');
        $this->provideAdminBackEndUserForFal();

        $subject = new LegacyCategory(3);

        self::assertInstanceOf(FileReference::class, $subject->getIcon());
    }
}
