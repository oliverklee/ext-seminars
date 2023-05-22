<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\UpgradeWizards;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\UpgradeWizards\SeparateBillingAddressUpgradeWizard;
use Psr\Log\LoggerAwareInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\SeparateBillingAddressUpgradeWizard
 */
final class SeparateBillingAddressUpgradeWizardTest extends UnitTestCase
{
    /**
     * @var SeparateBillingAddressUpgradeWizard
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SeparateBillingAddressUpgradeWizard();
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
    public function isRepeatable(): void
    {
        self::assertInstanceOf(RepeatableInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function isLoggerAware(): void
    {
        self::assertInstanceOf(LoggerAwareInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function identifierIsForSeparateBillingAddressMigration(): void
    {
        self::assertSame('seminars_migrateSeparateBillingAddress', $this->subject->getIdentifier());
    }

    /**
     * @test
     */
    public function titleIsForSeparateBillingAddressIconMigration(): void
    {
        self::assertSame('Marks the separate billing address in registrations', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function descriptionIsForSeparateBillingAddressMigration(): void
    {
        $expected = 'Checks the "separate billing address" checkbox for all registrations ' .
            'that have a separate billing address';
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
