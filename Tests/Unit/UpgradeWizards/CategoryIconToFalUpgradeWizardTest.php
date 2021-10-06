<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\UpgradeWizards;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\UpgradeWizards\CategoryIconToFalUpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\CategoryIconToFalUpgradeWizard
 */
final class CategoryIconToFalUpgradeWizardTest extends UnitTestCase
{
    /**
     * @var CategoryIconToFalUpgradeWizard
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new CategoryIconToFalUpgradeWizard();
    }

    /**
     * @test
     */
    public function isUpgradeWizard(): void
    {
        self::assertInstanceOf(UpgradeWizardInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function identifierIsForCategoryIconMigration(): void
    {
        self::assertSame('seminars_migrateCategoryIconsToFal', $this->subject->getIdentifier());
    }

    /**
     * @test
     */
    public function titleIsForCategoryIconMigration(): void
    {
        self::assertSame('Migrate seminars category icons to FAL', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function descriptionIsForCategoryIconMigration(): void
    {
        $expected = 'The seminars extension used to have a legacy file upload for the category icons. '
            . 'This wizard now migrates those to FAL.';
        self::assertSame($expected, $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function requiredUpToDateDatabase(): void
    {
        self::assertSame([DatabaseUpdatedPrerequisite::class], $this->subject->getPrerequisites());
    }
}
