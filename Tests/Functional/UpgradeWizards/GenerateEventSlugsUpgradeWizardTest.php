<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\UpgradeWizards;

use OliverKlee\Seminars\UpgradeWizards\GenerateEventSlugsUpgradeWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\GenerateEventSlugsUpgradeWizard
 */
class GenerateEventSlugsUpgradeWizardTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private GenerateEventSlugsUpgradeWizard $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = GeneralUtility::makeInstance(GenerateEventSlugsUpgradeWizard::class);
    }

    /**
     * @test
     */
    public function updateNecessaryForEmptyDatabaseReturnsFalse(): void
    {
        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForOnlyEventsWithSlugsReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithSlug.xml');

        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForEventWithEmptySlugReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAndWithEmptySlug.xml');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForEventWithNullSlugReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithNullSlug.xml');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForHiddenEventWithNullSlugReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenEventWithNullSlug.xml');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForDeletedEventWithNullSlugReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedEventWithNullSlug.xml');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForTimedEventWithNullSlugReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TimedEventWithNullSlug.xml');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function executeUpdateKeepsEventWithSlugUnmodified(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAndWithEmptySlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 1]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('existing-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeUpdateUpdatesSlugOfEventWithEmptySlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAndWithEmptySlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 2]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-without-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeUpdateUpdatesSlugOfEventWithNullSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventWithNullSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 1]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-without-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeUpdateUpdatesSlugOfHiddenEventWithNullSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenEventWithNullSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 1]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-without-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeUpdateUpdatesSlugOfDeletedEventWithNullSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedEventWithNullSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 1]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-without-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeUpdateUpdatesSlugOfTimedEventWithNullSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TimedEventWithNullSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 1]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-without-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeSuffixesSlugIfSlugAlreadyExistsBeforeWizard(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SlugCollisionWithExistingSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 2]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-title-1', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeSuffixesSlugIfCollidingSlugHasJustBeenCreatedByWizard(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SlugCollisionWithNewlyCreatedSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 2]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-title-1', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeSuffixesSlugWithNextAvailableSuffixIfSuffixAlreadyExists(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SlugCollisionWithSuffixedSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 3]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame('event-title-2', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }
}
