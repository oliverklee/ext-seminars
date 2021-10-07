<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\UpgradeWizards;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\UpgradeWizards\SeminarImageToFalUpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\SeminarImageToFalUpgradeWizard
 */
final class SeminarImageToFalUpgradeWizardTest extends UnitTestCase
{
    /**
     * @var SeminarImageToFalUpgradeWizard
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new SeminarImageToFalUpgradeWizard();
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
        self::assertSame('seminars_migrateSeminarImagesToFal', $this->subject->getIdentifier());
    }

    /**
     * @test
     */
    public function titleIsForCategoryIconMigration(): void
    {
        self::assertSame('Migrate seminar images to FAL', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function descriptionIsForCategoryIconMigration(): void
    {
        $expected = 'The seminars extension used to have a legacy file upload for the seminar images. '
            . 'This wizard now migrates those to FAL.';
        self::assertSame($expected, $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function requiresUpToDateDatabase(): void
    {
        self::assertSame([DatabaseUpdatedPrerequisite::class], $this->subject->getPrerequisites());
    }
}
