<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\UpgradeWizards;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\UpgradeWizards\GenerateEventSlugsUpgradeWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\GenerateEventSlugsUpgradeWizard
 */
class GenerateEventSlugsUpgradeWizardTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var GenerateEventSlugsUpgradeWizard
     */
    private $subject;

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
    public function updateNecessaryEventWithoutSlugReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAndWithoutSlug.xml');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function executeUpdateKeepsEventWithSlugUnmodified(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAndWithoutSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 1]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
        self::assertSame('existing-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeUpdateUpdatesSlugOfEventWithoutSlug(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsWithAndWithoutSlug.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => 2]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
        self::assertSame('event-without-slug', $databaseRow['slug']);
        self::assertTrue($wizardResult);
    }
}
